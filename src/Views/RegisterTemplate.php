<?php
namespace App\Views;

class RegisterTemplate extends BaseTemplate {
    public static function getRegisterTemplate(): string {
        $template = parent::getTemplate();
        $title= 'Регистрация пользователя';
        $content = "<section class='register-wrap'>
            <h1 class='register-title'>$title</h1>";
        $content .= self::formRegister();
        $content .= "</section>";

        $resultTemplate = sprintf($template, $title, $content);
        return $resultTemplate;
    }

    public static function formRegister(){
        $form = <<<ORDERFORM
        <form action="/register" method="POST" class="register-form">
                <p class="register-message">Заполните данные, чтобы создать аккаунт и отслеживать заказы.</p>
                <label>
                    <input class="register-input" type="text" name="username" id="unameId" placeholder=" " required>
                    <span>Имя пользователя</span>
                </label>
                <label>
                    <input class="register-input" type="email" name="email" id="emailId" placeholder=" " required>
                    <span>Email</span>
                </label>
                <label>
                    <input class="register-input" type="password" name="password" id="passwordId" placeholder=" " required>
                    <span>Пароль</span>
                </label>
                <label>
                    <input class="register-input" type="password" name="confirm_password" id="confirm_passwordId" placeholder=" " required>
                    <span>Подтверждение пароля</span>
                </label>
                <button type="submit" class="register-submit">Зарегистрироваться</button>
                <p class="register-signin">Уже есть аккаунт? <a href="/login">Войти</a></p>
            </form>
        ORDERFORM;
        
        return $form;
    }

    public static function getVerifyTemplate(): string {
        $template = parent::getTemplate();
        $title= 'Подтверждение нового пользователя';
        $content = <<<VERY
        <main class="page-card mt-4">
            <div>
                <h3 class="mb-5">Успешное завершение регистрации</h3>
        VERY;
        $content .= "Ваш email успешно подтвержден!<br>
        Теперь вы можете войти на сайт";
        $content .= "</div></main>";

        $resultTemplate =  sprintf($template, $title, $content);
        return $resultTemplate;
    }

}
