<?php 
namespace App\Views;

use App\Views\BaseTemplate;
use App\Configs\Config;

class UserTemplate extends BaseTemplate
{
    /*
        Формирование страница "Регистрация"
    */
    public static function getUserTemplate(): string {
        $template = parent::getTemplate();
        $title= 'Авторизация';
        $content = <<<HTML
        <section class="auth-login-wrap">
            <div class="auth-login-card">
                <h2 class="auth-login-heading">Вход</h2>
                <p class="morphing-text" id="morphingText">Стиль</p>
        HTML;
        $content .= self::getFormLogin();
        $content .= <<<HTML
            </div>
        </section>
        <script>
            (function () {
                const words = ['WalkArt', 'Комфорт', 'Стиль', 'Качество', 'Новая коллекция'];
                const el = document.getElementById('morphingText');
                if (!el) return;
                let index = 0;
                setInterval(function () {
                    el.classList.remove('show');
                    setTimeout(function () {
                        index = (index + 1) % words.length;
                        el.textContent = words[index];
                        el.classList.add('show');
                    }, 230);
                }, 1800);
                el.classList.add('show');
            })();
        </script>
        HTML;

        $resultTemplate =  sprintf($template, $title, $content);
        return $resultTemplate;
    }

    /* 
        Форма входа (логин, пароль)
    */
    public static function getFormLogin(): string {
        $html= <<<FORMA
                <form action="/login" method="POST" class="auth-login-form">
                    <input type="text" name="username" class="auth-login-input" id="nameInput" placeholder="Логин или email" required>
                    <input type="password" name="password" class="auth-login-input" id="passwordInput" placeholder="Пароль" required>
                    <span class="auth-forgot-password">
                        <a href="/register">Еще нет аккаунта? Регистрация</a>
                    </span>
                    <button class="auth-login-button" type="submit" aria-label="Войти">Войти</button>
                </form>
FORMA;
        return $html;
    }

    /*
        Формирование страница "Профиль"
    */
    public static function getProfileTemplate(?array $data, ?array $balance = null): string {
        $template = parent::getTemplate();
        $title= 'Профиль пользователя';
        $content = <<<CORUSEL
        <main class="page-card mt-4">
            <div>
                <h3 class="mb-5">Профиль пользователя</h3>
        CORUSEL;
        $content .= self::getFormProfile($data, $balance);
        $content .= "</div></main>";

        $resultTemplate =  sprintf($template, $title, $content);
        return $resultTemplate;
    }

    /* 
        Форма входа (логин, пароль)
    */
    public static function getFormProfile(?array $data, ?array $balance = null): string {

        $fio = isset($data['fio']) ? (string)$data['fio'] : "";
        $email = isset($data['email']) ? (string)$data['email'] : "";
        $address = isset($data['address']) ? (string)$data['address'] : "";
        $phone = isset($data['phone']) ? (string)$data['phone'] : "";
        $avatar = isset($data['avatar']) && $data['avatar'] ? (string)$data['avatar'] : (isset($_SESSION['avatar']) ? (string)$_SESSION['avatar'] : '/../../asserts/img/walkart-logo.png');
        $bonusBalance = isset($balance['bonus_balance']) ? (float)$balance['bonus_balance'] : 0.0;
        $ordersCount = isset($balance['orders_count']) ? (int)$balance['orders_count'] : 0;
        $loyaltyLevel = isset($balance['loyalty_level']) ? (string)$balance['loyalty_level'] : 'Start';

        $html= <<<FORMA
                <section class="profile-shell">
                    <aside class="profile-side">
                        <img src="$avatar" alt="Текущий аватар" class="profile-avatar-img">
                        <h4>Личный кабинет</h4>
                        <p>Обновите контактные данные, чтобы оформление заказов было быстрее и удобнее.</p>
                        <div class="profile-balance-card">
                            <span>Баланс бонусов</span>
                            <strong>{$bonusBalance} ₽</strong>
                            <small>Уровень: {$loyaltyLevel} | Заказов: {$ordersCount}</small>
                        </div>
                    </aside>
                    <form action="/profile" method="POST" class="profile-form" enctype="multipart/form-data">
                        <div class="profile-field">
                            <label for="fioInput" class="form-label">Ваше ФИО</label>
                            <input type="text" name="fio" class="form-control" id="fioInput" value="$fio" placeholder="Иван Иванов">
                        </div>
                        <div class="profile-field">
                            <label for="emailInput" class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" id="emailInput" disabled value="$email">
                        </div>
                        <div class="profile-field">
                            <label for="addressInput" class="form-label">Адрес доставки</label>
                            <input type="text" name="address" class="form-control" id="addressInput" value="$address" placeholder="Кемерово, пр. Ленина, д. 7, кв. 5">
                        </div>
                        <div class="profile-field">
                            <label for="phoneInput" class="form-label">Телефон</label>
                            <input type="text" name="phone" class="form-control" id="phoneInput" value="$phone" placeholder="+7 (999) 000-00-00">
                        </div>
                        <div class="profile-field">
                            <label for="avatarPhoto" class="form-label">Загрузить фото (jpg/png/webp)</label>
                            <input type="file" name="avatar_photo" id="avatarPhoto" class="form-control" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        </div>
                        <button type="submit" class="profile-submit-btn">Сохранить изменения</button>
                    </form>
                </section>
        FORMA;
        return $html;
    }

    /*
        Формирование страница "История заказов"
    */
    public static function getHistoryTemplate(?array $data, ?array $balance = null, array $purchasedProducts = []): string {
        $template = parent::getTemplate();
        $title= 'История  заказов';
        $ordersCount = isset($balance['orders_count']) ? (int)$balance['orders_count'] : 0;
        $totalSpent = isset($balance['total_spent']) ? (float)$balance['total_spent'] : 0.0;
        $bonusBalance = isset($balance['bonus_balance']) ? (float)$balance['bonus_balance'] : 0.0;
        $loyaltyLevel = isset($balance['loyalty_level']) ? (string)$balance['loyalty_level'] : 'Start';
        $content = <<<CORUSEL
        <main class="history-page mt-4">
            <section class="history-summary">
                <div class="history-summary-item">
                    <span>Всего заказов</span>
                    <strong>{$ordersCount}</strong>
                </div>
                <div class="history-summary-item">
                    <span>Потрачено</span>
                    <strong>{$totalSpent} ₽</strong>
                </div>
                <div class="history-summary-item">
                    <span>Баланс бонусов</span>
                    <strong>{$bonusBalance} ₽</strong>
                </div>
                <div class="history-summary-item">
                    <span>Уровень</span>
                    <strong>{$loyaltyLevel}</strong>
                </div>
            </section>
            <section class="history-list">
                <h3 class="mb-3">История заказов</h3>
        CORUSEL;
        
        if (!empty($data)) {
            foreach($data as $row) {
                $rawDate = isset($row['created']) ? (string)$row['created'] : '';
                $ts = strtotime($rawDate);
                $orderDate = $ts ? date("d-m-Y H:i", $ts) : 'Дата не указана';
                $rowSum = (float)$row['all_sum'];
                $bonus = round($rowSum * 0.05, 2);
                $content .= <<<TABLE
                <article class="history-order-card">
                    <div class="history-order-top">
                        <h4>Заказ #{$row['id']}</h4>
                        <span class="history-status">Завершен</span>
                    </div>
                    <p class="history-date">{$orderDate}</p>
                    <div class="history-order-metrics">
                        <span>Сумма: <strong>{$rowSum} ₽</strong></span>
                        <span>Начислено бонусов: <strong>{$bonus} ₽</strong></span>
                    </div>
                </article>
                TABLE;
            }
        } else {
            $content .= '<p class="history-empty">Пока заказов нет. Оформите первый заказ, чтобы начать копить бонусы.</p>';
        }
        $content .= '</section>';
        if (!empty($purchasedProducts)) {
            $content .= '<section class="history-review-prompt"><h3 class="mb-3">Не хотите оставить отзыв?</h3><div class="history-review-grid">';
            foreach ($purchasedProducts as $product) {
                $id = (int)($product['id'] ?? 0);
                $name = htmlspecialchars((string)($product['name'] ?? 'Товар'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $image = htmlspecialchars((string)($product['image'] ?? '/../../asserts/img/walkart-logo.png'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $content .= <<<HTML
                    <article class="history-review-card">
                        <img src="{$image}" alt="{$name}">
                        <div>
                            <h4>{$name}</h4>
                            <p>Вы уже покупали этот товар. Поделитесь впечатлением о качестве, удобстве и размере.</p>
                            <a href="/products/{$id}" class="catalog-filter">Оставить отзыв</a>
                        </div>
                    </article>
                HTML;
            }
            $content .= '</div></section>';
        }
        $content .= "</main>";

        $resultTemplate =  sprintf($template, $title, $content);
        return $resultTemplate;
    }
}
