<?php
namespace App\Services;

use PDO;

class UserDBStorage extends DBStorage implements ISaveStorage
{
    public function saveData(string $name, array $data): bool
    {
        $sql = "INSERT INTO `$name`
        (`username`, `email`, `password`, `token`) 
        VALUES (:name, :email, :pass, :token)";

        $sth = $this->connection->prepare($sql);
// var_dump( $data );
// exit();
        $result= $sth->execute( [
            'name' => $data['username'],
            'email' => $data['email'],
            'pass' => $data['password'],
            'token' => $data['token']
        ] );
        return $result;
    }

    public function uniqueEmail($email): bool 
    {
        $stmt = $this->connection->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) 
            return false;
        return true;
    }

    public function saveVerified($token): bool
    {
        $stmt = $this->connection->prepare(
            "SELECT id FROM users WHERE token = ? 
            AND is_verified = 0");
        $stmt->execute([$token]);

        if ($stmt->rowCount() > 0) {

            $user = $stmt->fetch();
            $update = $this->connection->prepare(
                "UPDATE users SET is_verified = 1, 
                token = '' 
                WHERE id = ?");
            $update->execute([$user['id']]);

            return true;
        }
        return false;
    }

    /**
     * Аутентификация пользователя
     */
    public function loginUser($username, $password) {   
        $this->ensureDefaultAdminAccount();

        // Поиск пользователя
        $stmt = $this->connection->prepare(
            "SELECT * FROM users WHERE username = ? OR email = ?"
        );
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        // проверка пароля
        if ($user === false) 
            return false;
        if (!password_verify($password, $user['password']))
            return false;
        
        // Установка переменных сессии
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['avatar'] = $this->resolveAvatarForSession((int)$user['id'], $user);
        $balance = $this->getBalanceSummary((int)$user['id']);
        $_SESSION['bonus_balance'] = $balance['total_balance'] ?? 0;
        $_SESSION['is_admin'] = $this->resolveAdminAccess((int)$user['id'], $user);
        
        return true;
    }

    public function getUserData($user_id) {
        // Динамически собираем список столбцов в зависимости от наличия в таблице
        $columns = [];
        if ($this->columnExists('fio')) {
            $columns[] = 'fio';
        } else {
            // Алиасим username в fio для совместимости
            $columns[] = 'username AS fio';
        }
        $columns[] = 'email';
        if ($this->columnExists('address')) {
            $columns[] = 'address';
        }
        if ($this->columnExists('phone')) {
            $columns[] = 'phone';
        }
        if ($this->columnExists('avatar')) {
            $columns[] = 'avatar';
        }
        if ($this->columnExists('is_admin')) {
            $columns[] = 'is_admin';
        }

        $sql = 'SELECT ' . implode(', ', $columns) . ' FROM users WHERE id=?';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    }

    public function updateProfile($data):bool {
        global $user_id;
        try {
            $hasAddress = $this->columnExists('address');
            $hasPhone = $this->columnExists('phone');
            $hasFio = $this->columnExists('fio');

            $setParts = [];
            $params = [];

            if ($hasAddress) {
                $setParts[] = 'address = ?';
                $params[] = $data['address'];
            }
            if ($hasPhone) {
                $setParts[] = 'phone = ?';
                $params[] = $data['phone'];
            }
            if ($hasFio) {
                $setParts[] = 'fio = ?';
                $params[] = $data['fio'] ?? '';
            } else {
                $setParts[] = 'username = ?';
                $params[] = $data['fio'] ?? $data['username'] ?? '';
            }
            if ($this->columnExists('avatar')) {
                $setParts[] = 'avatar = ?';
                $params[] = $data['avatar'] ?? '';
            }

            if (empty($setParts)) {
                return false;
            }

            $update = $this->connection->prepare(
                'UPDATE users SET ' . implode(', ', $setParts) . ' WHERE id = ?'
            );
            $params[] = $user_id;
            $update->execute($params);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    // Вспомогательный метод: проверка наличия колонки в таблице
    private function columnExists(string $column): bool {
        try {
            $stmt = $this->connection->prepare(
                "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = ?"
            );
            $stmt->execute([$column]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function resolveAvatarForSession(int $userId, array $user): string {
        if (!empty($user['avatar'])) {
            return (string)$user['avatar'];
        }

        $dir = __DIR__ . '/../../asserts/img/avatars';
        $files = glob($dir . '/user-' . $userId . '.*') ?: [];
        if (!empty($files)) {
            $name = basename($files[0]);
            return '/../../asserts/img/avatars/' . $name;
        }

        return '/../../asserts/img/walkart-logo.png';
    }

    private function resolveAdminAccess(int $userId, array $user): bool
    {
        if ($this->columnExists('is_admin') && array_key_exists('is_admin', $user)) {
            return (int)$user['is_admin'] === 1;
        }
        return $userId === 1 || mb_strtolower((string)($user['username'] ?? '')) === 'admin';
    }

    private function ensureAdminColumn(): void
    {
        if ($this->columnExists('is_admin')) {
            return;
        }
        $this->connection->exec("ALTER TABLE users ADD COLUMN is_admin TINYINT(1) NOT NULL DEFAULT 0");
        $this->connection->exec("UPDATE users SET is_admin = 1 WHERE id = 1 OR LOWER(username) = 'admin'");
    }

    private function ensureBalanceColumn(): void
    {
        if ($this->columnExists('balance')) {
            return;
        }
        $this->connection->exec("ALTER TABLE users ADD COLUMN balance DECIMAL(10,2) NOT NULL DEFAULT 0");
    }

    private function ensureDefaultAdminAccount(): void
    {
        try {
            $this->ensureAdminColumn();

            $checkStmt = $this->connection->prepare("SELECT id FROM users WHERE LOWER(username) = 'admin' LIMIT 1");
            $checkStmt->execute();
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                $promoteStmt = $this->connection->prepare("UPDATE users SET is_admin = 1 WHERE id = ?");
                $promoteStmt->execute([(int)$existing['id']]);
                return;
            }

            $columnsStmt = $this->connection->query(
                "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'"
            );
            $availableColumns = array_column($columnsStmt->fetchAll(PDO::FETCH_ASSOC), 'COLUMN_NAME');

            $dataMap = [
                'username' => 'admin',
                'email' => 'admin@walkart.local',
                'password' => password_hash('admin', PASSWORD_DEFAULT),
                'token' => '',
                'is_verified' => 1,
                'is_admin' => 1,
                'fio' => 'Administrator',
                'address' => '',
                'phone' => '',
            ];

            $insertColumns = [];
            $insertValues = [];
            $params = [];
            foreach ($dataMap as $column => $value) {
                if (in_array($column, $availableColumns, true)) {
                    $insertColumns[] = $column;
                    $insertValues[] = '?';
                    $params[] = $value;
                }
            }

            if (empty($insertColumns)) {
                return;
            }

            $sql = "INSERT INTO users (" . implode(', ', $insertColumns) . ") VALUES (" . implode(', ', $insertValues) . ")";
            $insertStmt = $this->connection->prepare($sql);
            $insertStmt->execute($params);
        } catch (\Throwable $e) {
            // silent fallback: do not break login flow
        }
    }

    public function getUsersForAdminPanel(): array
    {
        try {
            $this->ensureAdminColumn();
            $this->ensureBalanceColumn();
            $stmt = $this->connection->query("SELECT id, username, email, is_admin, balance FROM users ORDER BY id DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function setAdminStatus(int $targetUserId, bool $isAdmin): bool
    {
        try {
            $this->ensureAdminColumn();
            $stmt = $this->connection->prepare("UPDATE users SET is_admin = ? WHERE id = ?");
            return $stmt->execute([$isAdmin ? 1 : 0, $targetUserId]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function setUserBalance(int $targetUserId, float $balance): bool
    {
        try {
            $this->ensureBalanceColumn();
            $stmt = $this->connection->prepare("UPDATE users SET balance = ? WHERE id = ?");
            return $stmt->execute([$balance, $targetUserId]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getDataHistory(int $idUser): ?array 
    {
        // Определяем корректное имя столбца в orders: user_id или user
        $col = null;
        if ($this->columnExistsInTable('orders', 'user_id')) {
            $col = 'user_id';
        } elseif ($this->columnExistsInTable('orders', 'user')) {
            $col = 'user';
        }
        $dateColumn = 'created';
        if (!$this->columnExistsInTable('orders', 'created') && $this->columnExistsInTable('orders', 'created_at')) {
            $dateColumn = 'created_at';
        }
        if ($col !== null) {
            $stmt = $this->connection->prepare(
                "SELECT id, {$dateColumn} AS created, all_sum FROM orders WHERE {$col} = ? ORDER BY id DESC"
            );
            $stmt->execute([$idUser]);
        } else {
            // Fallback для старой схемы без user_id/user: привязка по email
            $user = $this->getUserData($idUser);
            $email = (string)($user['email'] ?? '');
            if ($email === '') {
                return null;
            }
            $stmt = $this->connection->prepare(
                "SELECT id, {$dateColumn} AS created, all_sum FROM orders WHERE email = ? ORDER BY id DESC"
            );
            $stmt->execute([$email]);
        }

        if ($stmt->rowCount() > 0) {
            $orders = $stmt->fetchAll();
            return $orders;
        }
        return null;
    }

    public function hasPurchasedProduct(int $userId, int $productId): bool
    {
        try {
            $orderUserCol = null;
            if ($this->columnExistsInTable('orders', 'user_id')) {
                $orderUserCol = 'user_id';
            } elseif ($this->columnExistsInTable('orders', 'user')) {
                $orderUserCol = 'user';
            }
            if ($orderUserCol !== null) {
                $sql = "SELECT 1
                        FROM order_item oi
                        INNER JOIN orders o ON o.id = oi.order_id
                        WHERE o.{$orderUserCol} = ? AND oi.product_id = ?
                        LIMIT 1";
                $stmt = $this->connection->prepare($sql);
                $stmt->execute([$userId, $productId]);
            } else {
                $user = $this->getUserData($userId);
                $email = (string)($user['email'] ?? '');
                if ($email === '') {
                    return false;
                }
                $sql = "SELECT 1
                        FROM order_item oi
                        INNER JOIN orders o ON o.id = oi.order_id
                        WHERE o.email = ? AND oi.product_id = ?
                        LIMIT 1";
                $stmt = $this->connection->prepare($sql);
                $stmt->execute([$email, $productId]);
            }
            return (bool)$stmt->fetchColumn();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getPurchasedProductsForUser(int $userId): array
    {
        try {
            $orderUserCol = null;
            if ($this->columnExistsInTable('orders', 'user_id')) {
                $orderUserCol = 'user_id';
            } elseif ($this->columnExistsInTable('orders', 'user')) {
                $orderUserCol = 'user';
            }
            if ($orderUserCol !== null) {
                $sql = "SELECT DISTINCT p.id, p.name, p.image
                        FROM order_item oi
                        INNER JOIN orders o ON o.id = oi.order_id
                        INNER JOIN products p ON p.id = oi.product_id
                        WHERE o.{$orderUserCol} = ?
                        ORDER BY p.id DESC";
                $stmt = $this->connection->prepare($sql);
                $stmt->execute([$userId]);
            } else {
                $user = $this->getUserData($userId);
                $email = (string)($user['email'] ?? '');
                if ($email === '') {
                    return [];
                }
                $sql = "SELECT DISTINCT p.id, p.name, p.image
                        FROM order_item oi
                        INNER JOIN orders o ON o.id = oi.order_id
                        INNER JOIN products p ON p.id = oi.product_id
                        WHERE o.email = ?
                        ORDER BY p.id DESC";
                $stmt = $this->connection->prepare($sql);
                $stmt->execute([$email]);
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getBalanceSummary(int $idUser): array
    {
        $orders = $this->getDataHistory($idUser) ?? [];
        $ordersCount = count($orders);
        $totalSpent = 0.0;
        foreach ($orders as $order) {
            $totalSpent += (float)($order['all_sum'] ?? 0);
        }

        // Простая бонусная система: 5% от всех покупок
        $bonusEarned = round($totalSpent * 0.05, 2);
        $walletBalance = $this->getUserWalletBalance($idUser);
        $totalBalance = round($bonusEarned + $walletBalance, 2);
        $loyaltyLevel = 'Start';
        if ($totalSpent >= 60000) {
            $loyaltyLevel = 'Gold';
        } elseif ($totalSpent >= 25000) {
            $loyaltyLevel = 'Silver';
        }

        return [
            'orders_count' => $ordersCount,
            'total_spent' => $totalSpent,
            'bonus_balance' => $bonusEarned,
            'wallet_balance' => $walletBalance,
            'total_balance' => $totalBalance,
            'loyalty_level' => $loyaltyLevel,
        ];
    }

    private function getUserWalletBalance(int $userId): float
    {
        try {
            $this->ensureBalanceColumn();
            $stmt = $this->connection->prepare("SELECT balance FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return isset($row['balance']) ? (float)$row['balance'] : 0.0;
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    // Вспомогательный метод: проверка наличия колонки в произвольной таблице
    private function columnExistsInTable(string $table, string $column): bool {
        try {
            $stmt = $this->connection->prepare(
                "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?"
            );
            $stmt->execute([$table, $column]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
}
