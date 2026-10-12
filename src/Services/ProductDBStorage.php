<?php
namespace App\Services;

use PDO;

class ProductDBStorage extends DBStorage implements ILoadStorage
{
    public function loadData($nameFile): ?array
    {
        $sql = "SELECT * FROM products";
        $result = $this->connection->query($sql, PDO::FETCH_ASSOC);
        $rows = $result->fetchAll();
        return $rows; 
    }

    public function createProduct(array $data): bool
    {
        try {
            $sql = "INSERT INTO products (name, description, price, image) VALUES (?, ?, ?, ?)";
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute([
                $data['name'] ?? '',
                $data['description'] ?? '',
                $data['price'] ?? 0,
                $data['image'] ?? '',
            ]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function updateProduct(array $data): bool
    {
        try {
            $sql = "UPDATE products SET name = ?, description = ?, price = ?, image = ? WHERE id = ?";
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute([
                $data['name'] ?? '',
                $data['description'] ?? '',
                $data['price'] ?? 0,
                $data['image'] ?? '',
                $data['id'] ?? 0,
            ]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getProductById(int $id): ?array
    {
        $stmt = $this->connection->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function deleteProduct(int $id): bool
    {
        try {
            $stmt = $this->connection->prepare("DELETE FROM products WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function archiveProduct(int $id): bool
    {
        try {
            $product = $this->getProductById($id);
            if (!$product) {
                return false;
            }
            $description = (string)($product['description'] ?? '');
            if (!str_contains($description, '#deleted:1')) {
                $description = trim($description . ' #deleted:1');
            }
            $stmt = $this->connection->prepare("UPDATE products SET description = ? WHERE id = ?");
            return $stmt->execute([$description, $id]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function ensureReviewsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS product_reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            user_id INT NOT NULL,
            rating TINYINT NOT NULL,
            review_text TEXT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_product_user (product_id, user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->connection->exec($sql);
    }

    public function addReview(int $productId, int $userId, int $rating, string $text): bool
    {
        try {
            $this->ensureReviewsTable();
            $stmt = $this->connection->prepare(
                "INSERT INTO product_reviews (product_id, user_id, rating, review_text) VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE rating = VALUES(rating), review_text = VALUES(review_text), created_at = CURRENT_TIMESTAMP"
            );
            return $stmt->execute([$productId, $userId, $rating, $text]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getReviewsByProduct(int $productId): array
    {
        try {
            $this->ensureReviewsTable();
            $stmt = $this->connection->prepare(
                "SELECT r.rating, r.review_text, r.created_at, u.username
                 FROM product_reviews r
                 JOIN users u ON u.id = r.user_id
                 WHERE r.product_id = ?
                 ORDER BY r.created_at DESC"
            );
            $stmt->execute([$productId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getRatingsSummary(): array
    {
        try {
            $this->ensureReviewsTable();
            $stmt = $this->connection->query(
                "SELECT product_id, ROUND(AVG(rating), 1) AS avg_rating, COUNT(*) AS reviews_count
                 FROM product_reviews GROUP BY product_id"
            );
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $result = [];
            foreach ($rows as $row) {
                $result[(int)$row['product_id']] = [
                    'avg_rating' => (float)$row['avg_rating'],
                    'reviews_count' => (int)$row['reviews_count'],
                ];
            }
            return $result;
        } catch (\Throwable $e) {
            return [];
        }
    }
}