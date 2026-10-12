<?php
namespace App\Views;

class BaseTemplate {
    public static function getTemplate(): string {
    global $user_id, $username;
    $isAdmin = (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) || ((int)$user_id === 1) || (isset($username) && mb_strtolower((string)$username) === 'admin');

        $html = <<<LINE
        <!DOCTYPE html>
        <html lang="ru">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="preconnect" href="https://api.fontshare.com">
            <link href="/../../asserts/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
            <link href="/../../asserts/css/ui.css" rel="stylesheet">
            <link href="/../../asserts/css/dark-theme.css" rel="stylesheet">
            <link href="/../../asserts/css/buttons.css" rel="stylesheet">
            <link href="/../../asserts/css/gradient-button.css" rel="stylesheet">
            <title>%s</title>
        </head>
        <body>
            <header class="site-header">
                <nav class="navbar navbar-expand-lg bg-body-tertiary walkart-nav">
                <div class="container-fluid">
                    <a class="navbar-brand walkart-brand" href="/">
                    <img src="/../../asserts/img/walkart-logo.png" alt="WalkArt logo" width="52" height="52" class="d-inline-block align-text-top">
                    </a>
                    <a class="navbar-brand logo-font walkart-wordmark" href="/">WalkArt</a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                        aria-controls="navbarNav" aria-expanded="false" aria-label="Переключить навигацию">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="/">Главная</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="/about">О нас</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="/products">Каталог</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="/order">Заказ</a>
                        </li>
        LINE;
        if ($isAdmin) {
            $html .= <<<LINE
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="/admin">Админка</a>
                        </li>
            LINE;
        }
        if ($user_id == 0) {
                $html .= <<<LINE
                                <li class="nav-item">
                                <a class="nav-link active" href="/register">Регистрация</a>
                                </li>
                LINE;
        }
                $html .= <<<LINE
                            </ul>
                        <div class="nav-actions">
                LINE;

        if ($user_id > 0) {
                $avatar = isset($_SESSION['avatar']) && $_SESSION['avatar'] ? $_SESSION['avatar'] : '/../../asserts/img/walkart-logo.png';
                $bonusBalance = isset($_SESSION['bonus_balance']) ? (float)$_SESSION['bonus_balance'] : 0.0;
                $html .= <<<LINE
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{$avatar}" alt="Avatar" class="nav-avatar">
                        {$username}
                        <span class="nav-balance">{$bonusBalance} ₽</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end account-dropdown input" aria-labelledby="navbarDropdown">
                            <div class="menu-balance-chip">Бонусы: {$bonusBalance} ₽</div>
                            <a class="value" href="/profile">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM4 20a8 8 0 0 1 16 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                Профиль
                            </a>
                            <a class="value" href="/history">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M12 8v5l3 2M22 12A10 10 0 1 1 12 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                История заказов
                            </a>
                            <a class="value" href="/logout">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M16 17l5-5-5-5M21 12H9M12 21H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                Выход
                            </a>
                        </div>
                    </li>
                </ul>
                LINE;
        } else {
            $html .= <<<LINE
                <a class="auth-button nav-link p-2 px-3" href="/login">
                    Вход
                    <span class="hoverEffect"><span></span></span>
                </a>
            LINE;    
        }
        $html .= <<<LINE
                </div>
                </div>
                </div>
            </nav>
        </header>
        LINE;
   
        // Добавим flash сообщение
        if (isset($_SESSION['flash'])) {
            $html .= <<<FLASH
            <section>
                <div id="liveAlertBtn" class="alert alert-info alert-dismissible" role="alert">
                    <div>{$_SESSION['flash']}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
                    onclick="this.parentNode.style.display='none';"></button>
                </div>
            </section>
            FLASH;
            unset($_SESSION['flash']);
        }

        $html .= <<<LINE2
            <main class="container site-content">
                %s
            </main>
            <footer class="p-5">
                © 2026 WalkArt - интернет-магазин обуви
            </footer>
            <script src="https://unpkg.com/lenis@1.1.16/dist/lenis.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
            <script>
                (function () {
                    const currentPath = window.location.pathname;
                    document.querySelectorAll('.navbar .nav-link').forEach((link) => {
                        const href = link.getAttribute('href');
                        if (href && href === currentPath) {
                            link.classList.add('is-current');
                        }
                    });
                })();

                (function () {
                    const dropdownToggle = document.getElementById('navbarDropdown');
                    const dropdownMenu = document.querySelector('.account-dropdown');
                    if (!dropdownToggle || !dropdownMenu) {
                        return;
                    }
                    const dropdownInstance = (typeof bootstrap !== 'undefined' && bootstrap.Dropdown)
                        ? bootstrap.Dropdown.getOrCreateInstance(dropdownToggle)
                        : null;
                    document.addEventListener('click', function (event) {
                        const clickedInside = dropdownToggle.contains(event.target) || dropdownMenu.contains(event.target);
                        if (!clickedInside) {
                            if (dropdownInstance) {
                                dropdownInstance.hide();
                            } else {
                                dropdownMenu.classList.remove('show');
                                dropdownToggle.setAttribute('aria-expanded', 'false');
                            }
                        }
                    });
                })();

                (function () {
                    if (typeof Lenis !== 'function') {
                        return;
                    }

                    const lenis = new Lenis({
                        duration: 1.15,
                        smoothWheel: true,
                        wheelMultiplier: 0.95,
                        touchMultiplier: 1.1
                    });

                    function raf(time) {
                        lenis.raf(time);
                        requestAnimationFrame(raf);
                    }

                    requestAnimationFrame(raf);
                })();

                (function () {
                    document.querySelectorAll('form[action=\"/basket\"]').forEach((form) => {
                        form.addEventListener('submit', async function (event) {
                            event.preventDefault();
                            const submitBtn = form.querySelector('button[type=\"submit\"]');
                            const originalText = submitBtn ? submitBtn.textContent : '';
                            if (submitBtn) {
                                submitBtn.disabled = true;
                                submitBtn.textContent = 'Добавляем...';
                            }
                            try {
                                const response = await fetch('/basket', {
                                    method: 'POST',
                                    body: new FormData(form),
                                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                });
                                if (!response.ok) {
                                    throw new Error('request failed');
                                }
                                if (submitBtn) {
                                    submitBtn.textContent = 'Добавлено';
                                    setTimeout(() => {
                                        submitBtn.textContent = originalText || 'В корзину';
                                        submitBtn.disabled = false;
                                    }, 1000);
                                }
                            } catch (e) {
                                if (submitBtn) {
                                    submitBtn.textContent = 'Ошибка';
                                    setTimeout(() => {
                                        submitBtn.textContent = originalText || 'В корзину';
                                        submitBtn.disabled = false;
                                    }, 1200);
                                }
                            }
                        });
                    });
                })();
            </script>
        </body>
        </html>
        LINE2;

        return $html;
    }
}
