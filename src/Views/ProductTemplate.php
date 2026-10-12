<?php
namespace App\Views;

class ProductTemplate extends BaseTemplate {
    public static function getAllTemplate(array $arr, array $ratings = []): string {
        $template = parent::getTemplate();
        $str= <<<HTML
        <section class="catalog-page py-4">
            <div class="catalog-head mb-4">
                <h1 class="catalog-title">Каталог обуви</h1>
                <p class="catalog-subtitle">Выберите пару под стиль и сезон.</p>
            </div>
            <div class="catalog-filters" id="catalogFilters">
                <button type="button" class="catalog-filter is-active" data-filter="all">Все</button>
                <button type="button" class="catalog-filter" data-filter="sport-men">Спорт мужская</button>
                <button type="button" class="catalog-filter" data-filter="sport-women">Спорт женская</button>
                <button type="button" class="catalog-filter" data-filter="men">Мужская</button>
                <button type="button" class="catalog-filter" data-filter="women">Женская</button>
                <button type="button" class="catalog-filter" data-filter="classic-men">Классика мужская</button>
                <button type="button" class="catalog-filter" data-filter="classic-women">Классика женская</button>
                <button type="button" class="catalog-filter" data-filter="kids">Детская</button>
            </div>
            <div class="catalog-tools mb-4">
                <input type="text" id="catalogSearch" class="form-control" placeholder="Поиск по названию или описанию...">
                <select id="catalogSort" class="form-control">
                    <option value="default">Сортировка: по умолчанию</option>
                    <option value="price-asc">Цена: по возрастанию</option>
                    <option value="price-desc">Цена: по убыванию</option>
                    <option value="name-asc">Название: А-Я</option>
                </select>
                <div class="catalog-counter" id="catalogCounter"></div>
            </div>
            <div class="catalog-grid">
        HTML;

        // для каждого товара
        foreach( $arr as $key => $item ) {
            $rawDescription = (string)$item['description'];
            if (str_contains($rawDescription, '#deleted:1')) {
                continue;
            }
            $cleanDescription = preg_replace('/\s*#cat:[\w-]+\s*#gender:[\w-]+\s*#age:[\w-]+\s*/u', ' ', $rawDescription);
            $cleanDescription = str_replace('#deleted:1', '', (string)$cleanDescription);
            $cleanDescription = trim((string)$cleanDescription);
            $sourceText = mb_strtolower($item['name'] . ' ' . $rawDescription);
            $category = 'classic';
            $categoryLabel = 'Классика';
            $gender = 'unisex';
            $ageGroup = 'adult';

            if (preg_match('/#cat:([\w-]+)/u', $rawDescription, $catMatch)) {
                $category = $catMatch[1];
            }
            if (preg_match('/#gender:([\w-]+)/u', $rawDescription, $genderMatch)) {
                $gender = $genderMatch[1];
            }
            if (preg_match('/#age:([\w-]+)/u', $rawDescription, $ageMatch)) {
                $ageGroup = $ageMatch[1];
            }

            if (str_contains($sourceText, 'дет') || str_contains($sourceText, 'школ') || str_contains($sourceText, 'подрост')) {
                $ageGroup = 'kids';
            }

            if (str_contains($sourceText, 'муж')) {
                $gender = 'men';
            } elseif (str_contains($sourceText, 'жен')) {
                $gender = 'women';
            }

            if ($category === 'sport' || str_contains($sourceText, 'спорт') || str_contains($sourceText, 'sneaker') || str_contains($sourceText, 'крос')) {
                $category = 'sport';
                $categoryLabel = 'Спорт';
            } elseif ($gender === 'men') {
                $category = 'men';
                $categoryLabel = 'Мужская';
            } elseif ($gender === 'women') {
                $category = 'women';
                $categoryLabel = 'Женская';
            }

            $ratingText = self::formatProductRating($ratings, (int)$item['id']);
            $element_template= <<<END
            <article class="main product-item" data-category="{$category}" data-gender="{$gender}" data-age="{$ageGroup}" data-name="{$item['name']}" data-description="{$cleanDescription}" data-price="{$item['price']}">
                <div class="shoe-card">
                    <a href="/products/{$item['id']}" class="product-image-wrap">
                        <img src="{$item['image']}" class="product-image" alt="{$item['name']}">
                        <span class="product-tag">{$categoryLabel}</span>
                    </a>
                    <div class="card_content p-3">
                        <div class="text">
                            <a href="/products/{$item['id']}" class="text_m product-name">{$item['name']}</a>
                            <span class="text_s product-description">{$cleanDescription}</span>
                        </div>
                        <div class="product-bottom">
                            <h3 class="product-price"><span>{$item['price']}</span> ₽</h3>
                            <p class="product-rating">★ {$ratingText}</p>
                            <form class="mt-2" action="/basket" method="POST">
                                <input type="hidden" name="id" value="{$item['id']}">
                                <input type="hidden" name="product_name" value="{$item['name']}">
                                <button type="submit" class="product-cart-btn">В корзину</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card_back"></div>
            </article>
            END;

            $str.= $element_template;
        }
        $str.= <<<HTML
            </div>
            <div class="catalog-empty" id="catalogEmpty">По вашему запросу ничего не найдено.</div>
        </section>
        <script>
            (function () {
                const filterWrap = document.getElementById('catalogFilters');
                if (!filterWrap) return;
                const cards = Array.from(document.querySelectorAll('.product-item'));
                const grid = document.querySelector('.catalog-grid');
                const searchInput = document.getElementById('catalogSearch');
                const sortSelect = document.getElementById('catalogSort');
                const counter = document.getElementById('catalogCounter');
                const emptyState = document.getElementById('catalogEmpty');
                let selectedFilter = 'all';

                const refresh = () => {
                    const query = (searchInput?.value || '').trim().toLowerCase();
                    cards.forEach((card) => {
                        const category = card.dataset.category || 'classic';
                        const gender = card.dataset.gender || 'unisex';
                        const age = card.dataset.age || 'adult';
                        const title = (card.dataset.name || '').toLowerCase();
                        const description = (card.dataset.description || '').toLowerCase();
                        const byFilter =
                            selectedFilter === 'all' ||
                            selectedFilter === category ||
                            (selectedFilter === 'men' && gender === 'men') ||
                            (selectedFilter === 'women' && gender === 'women') ||
                            (selectedFilter === 'kids' && age === 'kids') ||
                            (selectedFilter === 'sport-men' && category === 'sport' && gender === 'men') ||
                            (selectedFilter === 'sport-women' && category === 'sport' && gender === 'women') ||
                            (selectedFilter === 'classic-men' && category === 'classic' && gender === 'men') ||
                            (selectedFilter === 'classic-women' && category === 'classic' && gender === 'women');
                        const bySearch = !query || title.includes(query) || description.includes(query);
                        card.style.display = byFilter && bySearch ? 'block' : 'none';
                    });

                    const visibleCards = cards.filter(card => card.style.display !== 'none');
                    if (counter) counter.textContent = 'Найдено: ' + visibleCards.length;
                    if (emptyState) emptyState.style.display = visibleCards.length ? 'none' : 'block';

                    if (sortSelect && grid) {
                        const mode = sortSelect.value;
                        const sorted = [...visibleCards].sort((a, b) => {
                            const ap = Number(a.dataset.price || 0);
                            const bp = Number(b.dataset.price || 0);
                            const an = (a.dataset.name || '').toLowerCase();
                            const bn = (b.dataset.name || '').toLowerCase();
                            if (mode === 'price-asc') return ap - bp;
                            if (mode === 'price-desc') return bp - ap;
                            if (mode === 'name-asc') return an.localeCompare(bn, 'ru');
                            return 0;
                        });
                        sorted.forEach((card) => grid.appendChild(card));
                    }
                };

                filterWrap.addEventListener('click', function (event) {
                    const target = event.target.closest('.catalog-filter');
                    if (!target) return;
                    filterWrap.querySelectorAll('.catalog-filter').forEach(btn => btn.classList.remove('is-active'));
                    target.classList.add('is-active');
                    selectedFilter = target.dataset.filter || 'all';
                    refresh();
                });
                searchInput?.addEventListener('input', refresh);
                sortSelect?.addEventListener('change', refresh);
                refresh();
            })();
        </script>
        HTML;
        $resultTemplate = sprintf($template, 'Каталог продукции', $str);
        return $resultTemplate;
    }

    private static function formatProductRating(array $ratings, int $productId): string
    {
        if (!isset($ratings[$productId])) {
            return 'нет отзывов';
        }
        $avg = (float)$ratings[$productId]['avg_rating'];
        $count = (int)$ratings[$productId]['reviews_count'];
        return number_format($avg, 1, '.', '') . " ({$count})";
    }

    /* получает ассоциативный массив с данными о продукте
    и выводить их в виде карточки (шаблон Card из фреймворка Bootstrap)
    (слева картинка, справа - название, описание и цена).
    */
    public static function getCardTemplate($data, ?array $ratingInfo = null, array $reviews = [], bool $canReview = false):string {
        $cleanDescription = preg_replace('/\s*#cat:[\w-]+\s*#gender:[\w-]+\s*#age:[\w-]+\s*/u', ' ', (string)$data['description']);
        $cleanDescription = trim((string)$cleanDescription);
        $avgText = $ratingInfo ? number_format((float)$ratingInfo['avg_rating'], 1, '.', '') : '—';
        $countText = $ratingInfo ? (int)$ratingInfo['reviews_count'] : 0;
        $reviewsHtml = '';
        foreach ($reviews as $row) {
            $uname = htmlspecialchars((string)$row['username'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $rtext = htmlspecialchars((string)$row['review_text'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $rdate = date("d-m-Y H:i", strtotime((string)$row['created_at']));
            $rrating = (int)$row['rating'];
            $reviewsHtml .= <<<HTML
            <article class="review-item">
                <div class="review-head">
                    <strong>{$uname}</strong>
                    <span>★ {$rrating}</span>
                </div>
                <p>{$rtext}</p>
                <small>{$rdate}</small>
            </article>
            HTML;
        }
        if ($reviewsHtml === '') {
            $reviewsHtml = '<p class="review-empty">Пока нет отзывов. Оставьте первый отзыв после входа в аккаунт.</p>';
        }

        $reviewFormHtml = $canReview ? <<<HTML
                            <form action="/reviews" method="POST" class="review-form">
                                <input type="hidden" name="product_id" value="{$data['id']}">
                                <label class="form-label" for="ratingSelect">Рейтинг</label>
                                <select class="form-control" id="ratingSelect" name="rating" required>
                                    <option value="">Выберите оценку</option>
                                    <option value="5">5 - Отлично</option>
                                    <option value="4">4 - Хорошо</option>
                                    <option value="3">3 - Нормально</option>
                                    <option value="2">2 - Слабо</option>
                                    <option value="1">1 - Плохо</option>
                                </select>
                                <label class="form-label mt-2" for="reviewText">Отзыв</label>
                                <textarea class="form-control" id="reviewText" name="review_text" required placeholder="Поделитесь впечатлениями о товаре"></textarea>
                                <button type="submit" class="product-detail-btn mt-3">Оставить отзыв</button>
                            </form>
        HTML : '<p class="review-empty">Оставить отзыв можно после покупки этого товара.</p>';

        $card= <<<CARD
            <section class="product-detail-wrap mt-4">
            <div class="card mb-3 product-detail-card">
                <div class="row g-0 align-items-stretch">
                    <div class="col-md-4 product-detail-image-col">
                        <img src="{$data['image']}" class="img-fluid rounded-start product-detail-image" alt="{$data['name']}">
                    </div>
                    <div class="col-md-8">
                    <div class="card-body product-detail-body">
                        <h5 class="card-title product-detail-title">{$data['name']}</h5>
                        <p class="card-text product-detail-price"><span>{$data['price']}</span> ₽</p>
                        <p class="product-detail-rating">Рейтинг: <strong>{$avgText}</strong> ({$countText} отзывов)</p>
                        <form class="mb-4" action="/basket" method="POST">
                            <input type="hidden" name="id" value="{$data['id']}">
                            <input type="hidden" name="product_name" value="{$data['name']}">
                            <button type="submit" class="product-detail-btn">Добавить в корзину</button>
                        </form>
                        <p class="card-text product-detail-description">{$cleanDescription}</p>
                        <section class="reviews-block mt-4">
                            <h4>Отзывы пользователей</h4>
                            {$reviewFormHtml}
                            <div class="reviews-list mt-3">
                                {$reviewsHtml}
                            </div>
                        </section>
                    </div>
                    </div>
                </div>
            </div>
            </section>
        CARD;
        $template = parent::getTemplate();
        $title= 'Карточка товара: ' . $data['name'];
        $resultTemplate = sprintf($template, $title, $card);
        return $resultTemplate;
    }

    public static function getAdminTemplate(array $products, array $users = []): string
    {
        $template = parent::getTemplate();
        $title = 'Админ-панель товаров';
        $content = <<<HTML
        <main class="admin-page mt-4">
            <section class="admin-head">
                <h1>Настройки администратора</h1>
                <p>Добавляйте новые товары в каталог: название, описание, цена, фото и нужная категория.</p>
            </section>
            <section class="admin-layout">
                <div class="admin-form-card">
                    <h3>Добавить товар</h3>
                    <form action="/admin" method="POST" class="admin-product-form" enctype="multipart/form-data">
                        <input type="hidden" name="admin_action" value="add_product">
                        <div class="admin-field">
                            <label class="form-label" for="productName">Название</label>
                            <input class="form-control" type="text" id="productName" name="name" required placeholder="Nike Air Max 270">
                        </div>
                        <div class="admin-field">
                            <label class="form-label" for="productDescription">Описание</label>
                            <textarea class="form-control" id="productDescription" name="description" required placeholder="Легкая и удобная модель для города"></textarea>
                        </div>
                        <div class="admin-field admin-grid-2">
                            <div>
                                <label class="form-label" for="productPrice">Цена</label>
                                <input class="form-control" type="number" min="1" step="1" id="productPrice" name="price" required placeholder="5490">
                            </div>
                            <div>
                                <label class="form-label" for="productImage">Ссылка на фото (необязательно)</label>
                                <input class="form-control" type="text" id="productImage" name="image" placeholder="/../../asserts/img/pizza01.jpg">
                            </div>
                        </div>
                        <div class="admin-field">
                            <label class="form-label" for="productImageFile">Или загрузить фото</label>
                            <input class="form-control" type="file" id="productImageFile" name="image_file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        </div>
                        <div class="admin-field admin-grid-3">
                            <div>
                                <label class="form-label" for="productCategory">Раздел</label>
                                <select class="form-control" id="productCategory" name="category">
                                    <option value="classic">Классика</option>
                                    <option value="sport">Спорт</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label" for="productGender">Пол</label>
                                <select class="form-control" id="productGender" name="gender">
                                    <option value="men">Мужская</option>
                                    <option value="women">Женская</option>
                                    <option value="unisex">Универсальная</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label" for="productAge">Возраст</label>
                                <select class="form-control" id="productAge" name="age">
                                    <option value="adult">Взрослая</option>
                                    <option value="kids">Детская</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="admin-submit-btn">Добавить товар</button>
                    </form>
                </div>
                <div class="admin-form-card">
                    <h3>Редактировать товар</h3>
                    <form action="/admin" method="POST" class="admin-product-form" enctype="multipart/form-data">
                        <input type="hidden" name="admin_action" value="edit_product">
                        <div class="admin-field">
                            <label class="form-label" for="editProductId">ID товара</label>
                            <input class="form-control" type="number" min="1" id="editProductId" name="id" required placeholder="Например: 3">
                        </div>
                        <div class="admin-field">
                            <label class="form-label" for="editProductName">Название</label>
                            <input class="form-control" type="text" id="editProductName" name="name" required>
                        </div>
                        <div class="admin-field">
                            <label class="form-label" for="editProductDescription">Описание</label>
                            <textarea class="form-control" id="editProductDescription" name="description" required></textarea>
                        </div>
                        <div class="admin-field admin-grid-2">
                            <div>
                                <label class="form-label" for="editProductPrice">Цена</label>
                                <input class="form-control" type="number" min="1" step="1" id="editProductPrice" name="price" required>
                            </div>
                            <div>
                                <label class="form-label" for="editProductImage">Ссылка на фото</label>
                                <input class="form-control" type="text" id="editProductImage" name="image" placeholder="Оставьте пустым чтобы не менять">
                            </div>
                        </div>
                        <div class="admin-field">
                            <label class="form-label" for="editProductImageFile">Или загрузить новое фото</label>
                            <input class="form-control" type="file" id="editProductImageFile" name="image_file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        </div>
                        <div class="admin-field admin-grid-3">
                            <div>
                                <label class="form-label" for="editProductCategory">Раздел</label>
                                <select class="form-control" id="editProductCategory" name="category">
                                    <option value="classic">Классика</option>
                                    <option value="sport">Спорт</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label" for="editProductGender">Пол</label>
                                <select class="form-control" id="editProductGender" name="gender">
                                    <option value="men">Мужская</option>
                                    <option value="women">Женская</option>
                                    <option value="unisex">Универсальная</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label" for="editProductAge">Возраст</label>
                                <select class="form-control" id="editProductAge" name="age">
                                    <option value="adult">Взрослая</option>
                                    <option value="kids">Детская</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="admin-submit-btn">Сохранить изменения</button>
                    </form>
                    <form action="/admin" method="POST" class="admin-product-form admin-delete-form">
                        <input type="hidden" name="admin_action" value="delete_product">
                        <div class="admin-field">
                            <label class="form-label" for="deleteProductId">Удалить товар по ID</label>
                            <input class="form-control" type="number" min="1" id="deleteProductId" name="id" required placeholder="Например: 3">
                        </div>
                        <button type="submit" class="admin-delete-btn">Удалить товар</button>
                    </form>
                </div>
                <div class="admin-list-card">
                    <h3>Последние товары</h3>
        HTML;

        $items = array_slice(array_reverse($products), 0, 8);
        if (empty($items)) {
            $content .= '<p class="admin-empty">Пока товаров нет.</p>';
        } else {
            foreach ($items as $item) {
                $rawDesc = (string)$item['description'];
                $cat = 'classic';
                $gen = 'unisex';
                $age = 'adult';
                if (preg_match('/#cat:([\w-]+)/u', $rawDesc, $m1)) { $cat = $m1[1]; }
                if (preg_match('/#gender:([\w-]+)/u', $rawDesc, $m2)) { $gen = $m2[1]; }
                if (preg_match('/#age:([\w-]+)/u', $rawDesc, $m3)) { $age = $m3[1]; }
                $desc = preg_replace('/\s*#cat:[\w-]+\s*#gender:[\w-]+\s*#age:[\w-]+\s*/u', ' ', $rawDesc);
                $desc = str_replace('#deleted:1', '', (string)$desc);
                $desc = trim((string)$desc);
                $name = htmlspecialchars((string)$item['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $descSafe = htmlspecialchars($desc, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $price = htmlspecialchars((string)$item['price'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $image = htmlspecialchars((string)$item['image'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $content .= <<<HTML
                    <article class="admin-item" data-id="{$item['id']}" data-name="{$name}" data-description="{$descSafe}" data-price="{$price}" data-image="{$image}" data-cat="{$cat}" data-gender="{$gen}" data-age="{$age}">
                        <img src="{$image}" alt="{$name}">
                        <div>
                            <h4>#{$item['id']} {$name}</h4>
                            <p>{$descSafe}</p>
                            <strong>{$price} ₽</strong>
                        </div>
                    </article>
                HTML;
            }
        }

        $content .= '</div></section>';

        $content .= <<<HTML
            <section class="admin-users-card">
                <h3>Назначение администраторов</h3>
                <p>Выберите пользователя и задайте ему роль: администратор или обычный пользователь.</p>
                <form action="/admin" method="POST" class="admin-users-form">
                    <input type="hidden" name="admin_action" value="set_admin">
                    <div class="admin-grid-2">
                        <div>
                            <label class="form-label" for="targetUser">Пользователь</label>
                            <select id="targetUser" name="target_user_id" class="form-control" required>
                                <option value="">Выберите пользователя</option>
        HTML;

        foreach ($users as $user) {
            $uid = (int)($user['id'] ?? 0);
            $uname = htmlspecialchars((string)($user['username'] ?? 'user'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $uemail = htmlspecialchars((string)($user['email'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $role = ((int)($user['is_admin'] ?? 0) === 1) ? 'admin' : 'user';
            $content .= '<option value="' . $uid . '">' . $uname . ' (' . $uemail . ') [' . $role . ']</option>';
        }

        $content .= <<<HTML
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="setAdmin">Роль</label>
                            <select id="setAdmin" name="set_admin" class="form-control">
                                <option value="1">Сделать администратором</option>
                                <option value="0">Сделать обычным пользователем</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="admin-submit-btn">Обновить права</button>
                </form>
                <form action="/admin" method="POST" class="admin-users-form mt-3">
                    <input type="hidden" name="admin_action" value="set_balance">
                    <div class="admin-grid-2">
                        <div>
                            <label class="form-label" for="targetUserBalance">Пользователь</label>
                            <select id="targetUserBalance" name="target_user_id" class="form-control" required>
                                <option value="">Выберите пользователя</option>
        HTML;

        foreach ($users as $user) {
            $uid = (int)($user['id'] ?? 0);
            $uname = htmlspecialchars((string)($user['username'] ?? 'user'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $uemail = htmlspecialchars((string)($user['email'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $content .= '<option value="' . $uid . '">' . $uname . ' (' . $uemail . ')</option>';
        }

        $content .= <<<HTML
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="setBalanceValue">Баланс (₽)</label>
                            <input id="setBalanceValue" name="set_balance_value" type="number" class="form-control" step="0.01" min="0" value="0">
                        </div>
                    </div>
                    <button type="submit" class="admin-submit-btn">Выдать баланс</button>
                </form>
            </section>
            <script>
                (function () {
                    const items = Array.from(document.querySelectorAll('.admin-item'));
                    const idInput = document.getElementById('editProductId');
                    const nameInput = document.getElementById('editProductName');
                    const descInput = document.getElementById('editProductDescription');
                    const priceInput = document.getElementById('editProductPrice');
                    const imageInput = document.getElementById('editProductImage');
                    const catSelect = document.getElementById('editProductCategory');
                    const genderSelect = document.getElementById('editProductGender');
                    const ageSelect = document.getElementById('editProductAge');
                    if (!items.length || !idInput) {
                        return;
                    }
                    items.forEach(function (item) {
                        item.addEventListener('click', function () {
                            idInput.value = item.dataset.id || '';
                            nameInput.value = item.dataset.name || '';
                            descInput.value = item.dataset.description || '';
                            priceInput.value = item.dataset.price || '';
                            imageInput.value = item.dataset.image || '';
                            if (catSelect && item.dataset.cat) catSelect.value = item.dataset.cat;
                            if (genderSelect && item.dataset.gender) genderSelect.value = item.dataset.gender;
                            if (ageSelect && item.dataset.age) ageSelect.value = item.dataset.age;
                            window.scrollTo({ top: idInput.getBoundingClientRect().top + window.scrollY - 120, behavior: 'smooth' });
                        });
                    });
                })();
            </script>
        </main>
        HTML;
        return sprintf($template, $title, $content);
    }
}