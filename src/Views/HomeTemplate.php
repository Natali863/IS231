<?php
namespace App\Views;

class HomeTemplate extends BaseTemplate {
    public static function getTemplate(): string {
        $template = parent::getTemplate();
        $title= 'Главная страница';
        $content = <<<LINE
        <section class="home-slider-section mt-4">
            <div class="slider" id="walkartSlider">
                <button class="slider--btn slider--btn-prev" aria-label="Предыдущий слайд">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                        <path d="M15 18L9 12L15 6"></path>
                    </svg>
                </button>
                <div class="slides__wrapper">
                    <div class="slide__bg" data-bg-index="0"></div>
                    <div class="slide__bg" data-bg-index="1"></div>
                    <div class="slide__bg" data-bg-index="2"></div>
                    <div class="slides">
                        <article class="slide" data-title="Urban Move" data-subtitle="Новая коллекция" data-description="Стильные пары для города и активного ритма." data-bg="url('/../../asserts/img/home-card-1.png')">
                            <div class="slide__inner">
                                <div class="slide--image__wrapper">
                                    <img class="slide--image" src="/../../asserts/img/home-card-1.png" alt="Новая коллекция WalkArt">
                                </div>
                            </div>
                        </article>
                        <article class="slide" data-title="Street Pulse" data-subtitle="Комфорт каждый день" data-description="Мягкая посадка и легкость на целый день." data-bg="url('/../../asserts/img/home-card-2.png')">
                            <div class="slide__inner">
                                <div class="slide--image__wrapper">
                                    <img class="slide--image" src="/../../asserts/img/home-card-2.png" alt="Комфортная обувь WalkArt">
                                </div>
                            </div>
                        </article>
                        <article class="slide" data-title="Night Run" data-subtitle="Выразительный стиль" data-description="Контрастные силуэты и современный дизайн." data-bg="url('/../../asserts/img/home-card-3.png')">
                            <div class="slide__inner">
                                <div class="slide--image__wrapper">
                                    <img class="slide--image" src="/../../asserts/img/home-card-3.png" alt="Модели WalkArt для города">
                                </div>
                            </div>
                        </article>
                    </div>
                    <div class="slides--infos"></div>
                </div>
                <button class="slider--btn slider--btn-next" aria-label="Следующий слайд">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                        <path d="M9 18L15 12L9 6"></path>
                    </svg>
                </button>
            </div>
        </section>
        <section class="home-features-wrap mt-4 mb-4">
            <div class="features">
                <article class="feature">
                    <a href="/products" class="feature-content">
                        <strong>Большой выбор</strong>
                        <span>Кроссовки, кеды, классическая и повседневная обувь для любого сезона.</span>
                    </a>
                </article>
                <article class="feature">
                    <a href="/order" class="feature-content">
                        <strong>Удобный заказ</strong>
                        <span>Простой каталог, фильтры и быстрое добавление товаров в корзину.</span>
                    </a>
                </article>
                <article class="feature">
                    <a href="/about" class="feature-content">
                        <strong>Быстрая доставка</strong>
                        <span>Доставляем по городу и помогаем с подбором размера перед покупкой.</span>
                    </a>
                </article>
                <article class="feature">
                    <a href="/products" class="feature-content">
                        <strong>Тренды сезона</strong>
                        <span>Легкие кроссовки, премиальная кожа и минималистичный дизайн в новых коллекциях.</span>
                    </a>
                </article>
                <article class="feature">
                    <a href="/products" class="feature-content">
                        <strong>Смотреть новинки</strong>
                        <span>Перейти в каталог актуальных моделей WalkArt.</span>
                    </a>
                </article>
                <article class="feature">
                    <a href="/about" class="feature-content">
                        <strong>Почему выбирают нас</strong>
                        <span>Гарантия качества на каждую пару. Быстрый обмен по размеру. Поддержка и консультация в чате.</span>
                    </a>
                </article>
            </div>
        </section>
        <script>
            (function () {
                const slider = document.getElementById('walkartSlider');
                if (!slider) {
                    return;
                }
                document.body.classList.add('home-slider-active');

                const slides = Array.from(slider.querySelectorAll('.slide'));
                const infos = slider.querySelector('.slides--infos');
                const bgs = Array.from(slider.querySelectorAll('.slide__bg'));
                const prevBtn = slider.querySelector('.slider--btn-prev');
                const nextBtn = slider.querySelector('.slider--btn-next');
                let current = 0;

                infos.innerHTML = slides.map((slide) => {
                    return '<article class="slide-info">' +
                        '<div class="slide-info__inner">' +
                        '<div class="slide-info--text__wrapper">' +
                        '<p class="slide-info--text" data-title><span>' + (slide.dataset.title || '') + '</span></p>' +
                        '<p class="slide-info--text" data-subtitle><span>' + (slide.dataset.subtitle || '') + '</span></p>' +
                        '<p class="slide-info--text" data-description><span>' + (slide.dataset.description || '') + '</span></p>' +
                        '</div>' +
                        '</div>' +
                        '</article>';
                }).join('');

                const slideInfos = Array.from(slider.querySelectorAll('.slide-info'));

                function setState(index) {
                    current = (index + slides.length) % slides.length;
                    slides.forEach((slide, i) => {
                        slide.removeAttribute('data-current');
                        slide.removeAttribute('data-next');
                        slide.removeAttribute('data-previous');
                        slide.style.setProperty('--bg', slide.dataset.bg || 'none');
                        if (i === current) {
                            slide.setAttribute('data-current', '');
                        } else if (i === (current + 1) % slides.length) {
                            slide.setAttribute('data-next', '');
                        } else if (i === (current - 1 + slides.length) % slides.length) {
                            slide.setAttribute('data-previous', '');
                        }
                    });
                    slideInfos.forEach((info, i) => {
                        info.removeAttribute('data-current');
                        if (i === current) {
                            info.setAttribute('data-current', '');
                        }
                    });
                    bgs.forEach((bg, i) => {
                        bg.removeAttribute('data-current');
                        bg.removeAttribute('data-next');
                        bg.removeAttribute('data-previous');
                        bg.style.backgroundImage = slides[i].dataset.bg || 'none';
                        if (i === current) {
                            bg.setAttribute('data-current', '');
                        } else if (i === (current + 1) % slides.length) {
                            bg.setAttribute('data-next', '');
                        } else if (i === (current - 1 + slides.length) % slides.length) {
                            bg.setAttribute('data-previous', '');
                        }
                    });
                }

                prevBtn.addEventListener('click', function () {
                    setState(current - 1);
                });

                nextBtn.addEventListener('click', function () {
                    setState(current + 1);
                });

                setState(0);
            })();

            (function () {
                const features = Array.from(document.querySelectorAll('.feature'));
                if (!features.length) {
                    return;
                }

                features.forEach((feature) => {
                    feature.addEventListener('mousemove', function (event) {
                        const rect = feature.getBoundingClientRect();
                        const x = event.clientX - rect.left;
                        const y = event.clientY - rect.top;
                        feature.style.setProperty('--x', x.toFixed(2));
                        feature.style.setProperty('--y', y.toFixed(2));
                    });
                });
            })();
        </script>
        LINE;
        $resultTemplate = sprintf($template, $title, $content);
        return $resultTemplate;
    }
}