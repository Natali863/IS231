<?php
namespace App\Controllers;

use App\Services\UserDBStorage;
use App\Config\Config;
use App\Services\ValidateBase;
use App\Views\UserTemplate;

class UserController {

    /* Форма входа на сайт */
    public function get(): string {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "POST")
            return $this->login();

        return UserTemplate::getUserTemplate();
    }
    
    public function login():string {      

        $arr = [];
        $arr['username'] =  strip_tags($_POST['username']);
        $arr['password'] = strip_tags($_POST['password']);

        // проверка логина и пароля
        if (Config::STORAGE_TYPE == Config::TYPE_DB) {
            $serviceDB = new UserDBStorage();
            if (!$serviceDB->loginUser($arr['username'], $arr['password'])) {
                $_SESSION['flash'] = "Ошибка ввода логина или пароля";
                return UserTemplate::getUserTemplate();
            }
        }
        // переадресация на Главную
	    header("Location: /");
        return "";
    }

    public function logout(){
        // очистка сессионных переменных аутентификации
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['is_admin']);
        // переадресация на Главную
	    header("Location: /");
        return "";
    }

    /* Форма профиля пользователя */
    public function profile(): string {
        global $user_id;

        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "POST")
            return $this->updateProfile();
        
        $data = null;
        $balance = null;
        // проверка логина и пароля
        if (Config::STORAGE_TYPE == Config::TYPE_DB) {
            $serviceDB = new UserDBStorage();
            $data = $serviceDB->getUserData($user_id);
            $balance = $serviceDB->getBalanceSummary($user_id);
            $_SESSION['bonus_balance'] = $balance['total_balance'] ?? 0;
            if (! $data) {
                $_SESSION['flash'] = "Ошибка получения данных пользователя";
            }
        }
        return UserTemplate::getProfileTemplate($data, $balance);
    }

    public function updateProfile(): string {
        global $user_id;
        $arr = [];
        $arr['fio'] = $_POST['fio'];
        $arr['address'] = $_POST['address'];
        $arr['phone'] = $_POST['phone'];
        $arr['avatar'] = $this->resolveAvatarPath($user_id);
        // санитизация значений
        ValidateBase::sanitationArray($arr);

        // сохранение в БД
        if (Config::STORAGE_TYPE == Config::TYPE_DB) {
            $serviceDB = new UserDBStorage();
            if (!$serviceDB->updateProfile($arr)) {
                $_SESSION['flash'] = "Ошибка сохранения данных";
            }
        }

        $_SESSION['flash'] = "Данные профиля обновлены";
        // Переадресация в профиль
	    header("Location: /profile");
        return "";
    }

    private function resolveAvatarPath(int $userId): string {
        $avatarDir = __DIR__ . '/../../asserts/img/avatars';
        if (!is_dir($avatarDir)) {
            @mkdir($avatarDir, 0777, true);
        }

        if (!isset($_FILES['avatar_photo']) || !is_array($_FILES['avatar_photo'])) {
            return isset($_SESSION['avatar']) ? (string)$_SESSION['avatar'] : '';
        }

        $upload = $_FILES['avatar_photo'];
        if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || empty($upload['tmp_name'])) {
            return isset($_SESSION['avatar']) ? (string)$_SESSION['avatar'] : '';
        }

        $ext = strtolower((string)pathinfo((string)$upload['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return isset($_SESSION['avatar']) ? (string)$_SESSION['avatar'] : '';
        }

        foreach (glob($avatarDir . '/user-' . $userId . '.*') ?: [] as $oldFile) {
            @unlink($oldFile);
        }

        $fileName = 'user-' . $userId . '.' . $ext;
        $target = $avatarDir . '/' . $fileName;
        if (!@move_uploaded_file((string)$upload['tmp_name'], $target)) {
            return isset($_SESSION['avatar']) ? (string)$_SESSION['avatar'] : '';
        }

        $publicPath = '/../../asserts/img/avatars/' . $fileName;
        $_SESSION['avatar'] = $publicPath;
        return $publicPath;
    }

    public function history(): string 
    {
        global $user_id;
        
        $data = null;
        $balance = null;
        // получение данных по заказам для юзера
        if ($user_id > 0)
            if (Config::STORAGE_TYPE == Config::TYPE_DB) {
                $serviceDB = new UserDBStorage();
                $data = $serviceDB->getDataHistory($user_id);
                $balance = $serviceDB->getBalanceSummary($user_id);
                $purchasedProducts = $serviceDB->getPurchasedProductsForUser($user_id);
                $_SESSION['bonus_balance'] = $balance['total_balance'] ?? 0;
                if (empty($data)) {
                    $_SESSION['flash'] = "Нет заказов";
                }
            }

        return UserTemplate::getHistoryTemplate($data, $balance, $purchasedProducts ?? []);
    }

}