<?php
namespace App\Controllers;

use App\Models\Product;
use App\Views\ProductTemplate;
use App\Config\Config;
use App\Services\ProductDBStorage;
use App\Services\UserDBStorage;
use App\Services\FileStorage;

class ProductController {
    public function get($id = null): string 
    {
        global $user_id;
        if (Config::STORAGE_TYPE == Config::TYPE_FILE) {
            $serviceStorage = new FileStorage();
            $model = new Product($serviceStorage, Config::FILE_PRODUCTS, Config::FILE_ORDERS);
        } 
        if (Config::STORAGE_TYPE == Config::TYPE_DB) {
            $serviceStorage = new ProductDBStorage();
            $model = new Product($serviceStorage, Config::TABLE_PRODUCTS, Config::TABLE_ORDERS);
        }

        $data = $model->loadData();
        $ratings = (Config::STORAGE_TYPE == Config::TYPE_DB) ? $serviceStorage->getRatingsSummary() : [];
        if ($id) {
            $data = $data[$id-1];
            $reviews = (Config::STORAGE_TYPE == Config::TYPE_DB) ? $serviceStorage->getReviewsByProduct((int)$data['id']) : [];
            $canReview = false;
            if (Config::STORAGE_TYPE == Config::TYPE_DB && $user_id > 0) {
                $userService = new UserDBStorage();
                $canReview = $userService->hasPurchasedProduct((int)$user_id, (int)$data['id']);
            }
            return ProductTemplate::getCardTemplate($data, $ratings[(int)$data['id']] ?? null, $reviews, $canReview);
        } else {
            return ProductTemplate::getAllTemplate($data, $ratings);            
        }
    }

    public function admin(): string
    {
        global $user_id, $username;
        if (!$this->isAdmin($user_id, $username ?? '')) {
            $_SESSION['flash'] = 'Доступ к админке только для администратора';
            header('Location: /');
            return '';
        }

        $serviceStorage = new ProductDBStorage();
        $model = new Product($serviceStorage, Config::TABLE_PRODUCTS, Config::TABLE_ORDERS);
        $data = $model->loadData() ?? [];
        $userService = new UserDBStorage();
        $users = $userService->getUsersForAdminPanel();
        return ProductTemplate::getAdminTemplate($data, $users);
    }

    public function adminSave(): string
    {
        global $user_id, $username;
        if (!$this->isAdmin($user_id, $username ?? '')) {
            $_SESSION['flash'] = 'Доступ к админке только для администратора';
            header('Location: /');
            return '';
        }

        $action = trim((string)($_POST['admin_action'] ?? 'add_product'));
        if ($action === 'set_admin') {
            $targetUserId = (int)($_POST['target_user_id'] ?? 0);
            $setAdmin = (int)($_POST['set_admin'] ?? 0) === 1;
            if ($targetUserId <= 0) {
                $_SESSION['flash'] = 'Выберите пользователя для изменения прав';
                header('Location: /admin');
                return '';
            }

            $userService = new UserDBStorage();
            $ok = $userService->setAdminStatus($targetUserId, $setAdmin);
            $_SESSION['flash'] = $ok ? 'Права администратора обновлены' : 'Ошибка обновления прав';
            header('Location: /admin');
            return '';
        }
        if ($action === 'set_balance') {
            $targetUserId = (int)($_POST['target_user_id'] ?? 0);
            $balance = (float)($_POST['set_balance_value'] ?? 0);
            $userService = new UserDBStorage();
            $ok = $targetUserId > 0 ? $userService->setUserBalance($targetUserId, $balance) : false;
            $_SESSION['flash'] = $ok ? 'Баланс пользователя обновлен' : 'Ошибка обновления баланса';
            header('Location: /admin');
            return '';
        }
        if ($action === 'edit_product') {
            return $this->adminUpdateProduct();
        }
        if ($action === 'delete_product') {
            return $this->adminDeleteProduct();
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $price = (float)($_POST['price'] ?? 0);
        $image = $this->resolveProductImagePath();
        $category = trim((string)($_POST['category'] ?? 'classic'));
        $gender = trim((string)($_POST['gender'] ?? 'unisex'));
        $age = trim((string)($_POST['age'] ?? 'adult'));

        if ($name === '' || $description === '' || $price <= 0 || $image === '') {
            $_SESSION['flash'] = 'Заполните все обязательные поля товара';
            header('Location: /admin');
            return '';
        }

        $tags = '#cat:' . $category . ' #gender:' . $gender . ' #age:' . $age;
        $fullDescription = trim($description . ' ' . $tags);

        $serviceStorage = new ProductDBStorage();
        $ok = $serviceStorage->createProduct([
            'name' => $name,
            'description' => $fullDescription,
            'price' => $price,
            'image' => $image,
        ]);

        $_SESSION['flash'] = $ok ? 'Товар успешно добавлен' : 'Ошибка добавления товара';
        header('Location: /admin');
        return '';
    }

    public function addReview(): string
    {
        global $user_id;
        $productId = (int)($_POST['product_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0);
        $text = trim((string)($_POST['review_text'] ?? ''));

        if ($user_id <= 0) {
            $_SESSION['flash'] = 'Только авторизованные пользователи могут оставлять отзывы';
            header('Location: /login');
            return '';
        }
        $userService = new UserDBStorage();
        if (!$userService->hasPurchasedProduct((int)$user_id, $productId)) {
            $_SESSION['flash'] = 'Оставить отзыв можно только после покупки этого товара';
            header('Location: /products/' . $productId);
            return '';
        }
        if ($productId <= 0 || $rating < 1 || $rating > 5 || $text === '') {
            $_SESSION['flash'] = 'Заполните корректно рейтинг и текст отзыва';
            header('Location: /products/' . $productId);
            return '';
        }

        $service = new ProductDBStorage();
        $ok = $service->addReview($productId, (int)$user_id, $rating, $text);
        $_SESSION['flash'] = $ok ? 'Отзыв сохранен' : 'Ошибка сохранения отзыва';
        header('Location: /products/' . $productId);
        return '';
    }

    private function adminUpdateProduct(): string
    {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim((string)($_POST['name'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $price = (float)($_POST['price'] ?? 0);
        $category = trim((string)($_POST['category'] ?? 'classic'));
        $gender = trim((string)($_POST['gender'] ?? 'unisex'));
        $age = trim((string)($_POST['age'] ?? 'adult'));

        if ($id <= 0 || $name === '' || $description === '' || $price <= 0) {
            $_SESSION['flash'] = 'Для редактирования товара заполните все обязательные поля';
            header('Location: /admin');
            return '';
        }

        $serviceStorage = new ProductDBStorage();
        $existing = $serviceStorage->getProductById($id);
        if (!$existing) {
            $_SESSION['flash'] = 'Товар для редактирования не найден';
            header('Location: /admin');
            return '';
        }

        $image = $this->resolveProductImagePath((string)$existing['image']);
        $tags = '#cat:' . $category . ' #gender:' . $gender . ' #age:' . $age;
        $fullDescription = trim($description . ' ' . $tags);
        $ok = $serviceStorage->updateProduct([
            'id' => $id,
            'name' => $name,
            'description' => $fullDescription,
            'price' => $price,
            'image' => $image,
        ]);
        $_SESSION['flash'] = $ok ? 'Товар обновлен' : 'Ошибка обновления товара';
        header('Location: /admin');
        return '';
    }

    private function resolveProductImagePath(string $fallback = ''): string
    {
        $uploaded = $_FILES['image_file'] ?? null;
        if (!is_array($uploaded) || ($uploaded['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $url = trim((string)($_POST['image'] ?? ''));
            return $url !== '' ? $url : $fallback;
        }

        $ext = strtolower((string)pathinfo((string)$uploaded['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return $fallback;
        }

        $dir = __DIR__ . '/../../asserts/img/products';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $fileName = 'product-' . time() . '-' . mt_rand(1000, 9999) . '.' . $ext;
        $target = $dir . '/' . $fileName;
        if (!@move_uploaded_file((string)$uploaded['tmp_name'], $target)) {
            return $fallback;
        }
        return '/../../asserts/img/products/' . $fileName;
    }

    private function adminDeleteProduct(): string
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['flash'] = 'Укажите корректный ID товара для удаления';
            header('Location: /admin');
            return '';
        }

        $serviceStorage = new ProductDBStorage();
        $existing = $serviceStorage->getProductById($id);
        if (!$existing) {
            $_SESSION['flash'] = 'Товар не найден';
            header('Location: /admin');
            return '';
        }

        $ok = $serviceStorage->deleteProduct($id);
        $archived = false;
        if (!$ok) {
            // Если товар связан внешними ключами (история заказов), удаляем "мягко"
            $archived = $serviceStorage->archiveProduct($id);
        }
        if ($ok) {
            $image = (string)($existing['image'] ?? '');
            if (str_starts_with($image, '/../../asserts/img/products/')) {
                $abs = __DIR__ . '/../../asserts/img/products/' . basename($image);
                if (is_file($abs)) {
                    @unlink($abs);
                }
            }
        }

        $_SESSION['flash'] = $ok ? 'Товар удален' : ($archived ? 'Товар архивирован и скрыт из каталога' : 'Ошибка удаления товара');
        header('Location: /admin');
        return '';
    }

    private function isAdmin($userId, string $username): bool
    {
        if (isset($_SESSION['is_admin'])) {
            return (bool)$_SESSION['is_admin'];
        }
        return ((int)$userId === 1) || mb_strtolower($username) === 'admin';
    }
}