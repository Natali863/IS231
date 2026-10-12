<?php
namespace App\Views;

class OrderTemplate extends BaseTemplate {
    public static function getOrderTemplate(array $arr, array $dataProfile = null): string {
        $template = parent::getTemplate();

        $title= 'Оформление заказа';
        $content = '<main class="order-modern mt-4">
            <section class="order-head">
                <h1>Оформление заказа</h1>
                <p>Проверьте товары в корзине и заполните данные для доставки.</p>
            </section>
            <section class="order-layout">
                <div class="order-summary">
                    <h3>Корзина</h3>';

        $all_sum = 0;
        foreach ($arr as $product) {
		    $name = $product['name'];
            $price = $product['price'];
		    $quantity = $product['quantity'];

            $sum = $price * $quantity;
            $all_sum += $sum;

            $content .= <<<LINE
                <div class="order-row">
                    <div class="order-row-name">{$name}</div>
                    <div class="order-row-meta">{$quantity} ед. x {$price} руб.</div>
                    <div class="order-row-sum">{$sum} ₽</div>
                </div>
                LINE;
	    }            
        $content .= '</div>';
        if ($all_sum == 0) {
            $content .= <<<LINE3
            <div class="order-empty">- нет добавленных товаров -</div>
            LINE3;
        } else {
            $content .= <<<LINE3
            <div class="order-total">
                <span>Итоговая сумма заказа:</span>
                <strong>{$all_sum} ₽</strong>
            </div>
            <div class="order-actions">
                <form action="/basket_clear" method="POST">
                    <button type="submit" class="order-clear-btn">Очистить корзину</button>
                </form>
            </div>
            LINE3;
        }

        $content .= '</div><div class="order-form-wrap">';
        if ($all_sum > 0) {
            /*
    ФИО покупателя (input) c label "Ваше ФИО:"
    Адрес покупателя (input) c label "Адрес доставки:"
    Телефон покупателя (input) c label "Телефон:"
    Кнопка (submit) "Создать заказ"
            */
            $content .= self::formOrder($dataProfile);
        } else {
            $content .= '<div class="order-form-card"><h3>Данные для доставки</h3><p class="order-empty">Добавьте товары в корзину, чтобы оформить заказ.</p></div>';
        }
        $content .= '</div></section></main>';

        $resultTemplate = sprintf($template, $title, $content);
        return $resultTemplate;
    }

    public static function formOrder($dataProfile = null){
        $fio = (isset($dataProfile) && isset($dataProfile['fio'])) ? $dataProfile['fio'] : '';
        $address = (isset($dataProfile) && isset($dataProfile['address'])) ? $dataProfile['address'] : '';
        $phone = (isset($dataProfile) && isset($dataProfile['phone'])) ? $dataProfile['phone'] : '';
        $email = (isset($dataProfile) && isset($dataProfile['email'])) ? $dataProfile['email'] : '';

        $form = <<<ORDERFORM
        <div class="order-form-card">
            <h3>Данные для доставки</h3>
            <form action="/order" method="POST" class="order-form">
                <div class="order-field">
                    <label for="fioId" class="form-label">Ваше ФИО</label>
                    <input type="text" name="fio" class="form-control" id="fioId" placeholder="Иван Иванов" value="{$fio}">
                </div>
                <div class="order-field">
                    <label for="addressId" class="form-label">Адрес доставки</label>
                    <input type="text" name="address" class="form-control" id="addressId"
                     placeholder="Кемерово, пр.Ленина, д.7, кв.5" value="{$address}">
                </div>
                <div class="order-field">
                    <label class="form-label" for="phoneId">Телефон</label>
                    <input type="text" name="phone" id="phoneId" placeholder="+7 (999) 000-00-00" value="{$phone}" class="form-control">
                </div>
                <div class="order-field">
                    <label for="emailId" class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" id="emailId"
                     placeholder="ivan@mail.ru" value="{$email}">
                </div>
                <button type="submit" class="order-submit-btn">Создать заказ</button>
            </form>
        </div>
        ORDERFORM;
        
        return $form;
    }
}
