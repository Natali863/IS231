<?php
namespace App\Views;

class AboutTemplate extends BaseTemplate {
    public static function getTemplate(): string {
        $template = parent::getTemplate();
        $title= 'О нас';
        $content = <<<LINE2
        <main class="about-page about-modern">
            <section class="about-hero">
                <div class="about-hero-copy">
                    <p class="about-eyebrow">О бренде WalkArt</p>
                    <h1 class="mb-3">Мы делаем обувь, в которой хочется жить каждый день</h1>
                    <p>WalkArt - это интернет-магазин, где стиль встречается с комфортом. Мы отбираем модели, которые подходят для активного города, работы, прогулок и путешествий.</p>
                    <p>Наша идея простая: качественная пара должна быть красивой, удобной и доступной. Поэтому в каталоге вы найдете как трендовые новинки, так и универсальную классику на каждый сезон.</p>
                </div>
                <div class="about-hero-badge">
                    <strong>2019</strong>
                    <span>год основания</span>
                    <strong>10 000+</strong>
                    <span>довольных клиентов</span>
                </div>
            </section>
            <section class="about-scene">
                <div class="img">
                    <div class="img-container">
                        <img src="/../../asserts/img/store-interior.png" alt="Интерьер магазина WalkArt">
                    </div>
                </div>
            </section>
            <section class="about-values mt-4">
                <article class="about-value-card">
                    <h3>Качество</h3>
                    <p>Работаем только с проверенными поставщиками и брендами, чтобы каждая покупка радовала не один сезон.</p>
                </article>
                <article class="about-value-card">
                    <h3>Поддержка</h3>
                    <p>Помогаем с подбором размера, подсказываем по материалам и остаемся на связи после заказа.</p>
                </article>
                <article class="about-value-card">
                    <h3>Скорость</h3>
                    <p>Быстро обрабатываем заявки и организуем доставку так, чтобы вы получили заказ в удобное время.</p>
                </article>
            </section>
            <div class="map-wrap mt-4 about-map">
                <img class="map-image" src="/../../asserts/img/map-ya.png" alt="Карта магазина">
            </div>
        </main>
        LINE2;
        $resultTemplate = sprintf($template, $title, $content);
        return $resultTemplate;
    }
}