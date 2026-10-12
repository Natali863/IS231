-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Апр 29 2026 г., 17:41
-- Версия сервера: 10.4.32-MariaDB
-- Версия PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `is231`
--

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `fio` varchar(120) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(120) NOT NULL,
  `all_sum` float NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id`, `fio`, `address`, `phone`, `email`, `all_sum`, `created`) VALUES
(1, 'Петрова М.И.', 'г. Кемерово, ул. Тухачевского, 32', '89059000088', 'petrova@yahoo.com', 0, '2025-04-08 10:52:01'),
(2, 'Петрова М.И.', 'г. Кемерово, ул. Тухачевского, 32', '89059000088', 'jorkazomli@gufum.com', 550, '2025-04-08 10:53:11'),
(3, 'Иванов И.И.', 'г. Кемерово, ул. Ленинский 132', '89059000080', 'ivanov@yahoo.com', 1200, '2025-04-08 11:12:11'),
(4, 'Петрова М.И.', 'г. Кемерово, ул. Тухачевского, 32', '89059000088', 'petrova@yahoo.com', 3050, '2025-04-08 11:13:44'),
(5, 'Bafysdfsdf', 'г. Кемерово, ул. Тухачевского, 32', '89059000088', 'jorkazomli@gufum.com', 1200, '2025-04-11 12:13:05'),
(6, 'Милевский Владислав Анатольевич', 'Кемерово, ул.Тухачевского 32 (оставить на вахте Коляну)', '+79059000687', 'iylzepttcdtfczw-12159@binitrax.com', 1750, '2026-04-13 11:14:16'),
(7, 'kortez', '', '', 'dantezecode@gmail.com', 2500, '2026-04-29 17:55:36'),
(8, 'kortez', '', '', 'dantezecode@gmail.com', 3756, '2026-04-29 17:57:54');

-- --------------------------------------------------------

--
-- Структура таблицы `order_item`
--

CREATE TABLE `order_item` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `count_item` int(11) NOT NULL,
  `price_item` float NOT NULL,
  `sum_item` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Дамп данных таблицы `order_item`
--

INSERT INTO `order_item` (`id`, `order_id`, `product_id`, `count_item`, `price_item`, `sum_item`) VALUES
(1, 3, 1, 1, 550, 550),
(2, 3, 2, 1, 650, 650),
(3, 4, 1, 2, 550, 1100),
(4, 4, 2, 3, 650, 1950),
(5, 5, 1, 1, 550, 550),
(6, 5, 2, 1, 650, 650),
(7, 6, 1, 2, 550, 1100),
(8, 6, 2, 1, 650, 650),
(9, 7, 3, 1, 1000, 1000),
(10, 7, 4, 1, 1500, 1500),
(11, 8, 3, 1, 1000, 1000),
(12, 8, 4, 1, 1500, 1500),
(13, 8, 5, 1, 1256, 1256);

-- --------------------------------------------------------

--
-- Структура таблицы `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(120) NOT NULL,
  `price` float NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `updated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Дамп данных таблицы `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `image`, `price`, `created`, `updated`) VALUES
(1, 'Пицца Маргарита', 'Это наша фирменная пицца, она состоит из куриного мяса, сосисок, грибов и 2 сортов сыра (пармезана и моцареллы), декорирована веточкой петрушки! #deleted:1', 'https://localhost/asserts/img/pizza01.jpg', 550, '2025-04-07 13:00:54', '2025-04-07 13:00:54'),
(2, 'Пицца Пипперони', 'Это знаменитая пицца с колбасой и сыром #deleted:1', 'https://localhost/asserts/img/pizza02.jpg', 650, '2025-04-07 13:00:54', '2025-04-07 13:00:54'),
(3, 'Кроссовки женские дышащие для фитнеса и зала', 'Материал верха\r\nтекстиль; ПУ (полиуретан)\r\nПол\r\nЖенский\r\nСезон\r\nКруглогодичный\r\nМатериал стельки\r\nтекстиль #cat:sport #gender:women #age:adult', '/../../asserts/img/products/product-1777473501-1569.webp', 1000, '2026-04-29 17:38:21', '2026-04-29 17:38:21'),
(4, 'Классические кроссовки демисезонные', 'Пол\r\nМужской\r\nМатериал верха\r\nэкокожа; искусственная кожа\r\nСезон\r\nДемисезон\r\nМатериал стельки\r\nтекстиль #cat:sport #gender:men #age:adult', '/../../asserts/img/products/product-1777473608-5274.webp', 1500, '2026-04-29 17:40:08', '2026-04-29 17:40:08'),
(5, 'Джинсовые летние кеды тканевые', 'Состав\r\nтекстиль\r\nПол\r\nМужской\r\nСезон\r\nЛето\r\nМатериал стельки\r\nтекстиль #cat:classic #gender:men #age:adult', '/../../asserts/img/products/product-1777473907-3884.webp', 1256, '2026-04-29 17:45:07', '2026-04-29 17:45:07'),
(6, 'Лоферы женские замшевые', 'Состав\r\nэкозамша; экокожа\r\nПол\r\nЖенский\r\nСезон\r\nКруглогодичный\r\nМатериал стельки\r\nЭкокожа #cat:classic #gender:women #age:adult', '/../../asserts/img/products/product-1777473960-9822.webp', 4566, '2026-04-29 17:46:00', '2026-04-29 17:46:00'),
(7, 'Весенние ботинки детские теплые на липучках', 'Состав\r\nискусственная кожа; экокожа; эко кожа; бамбук\r\nПол\r\nДевочки\r\nСезон\r\nДемисезон\r\nМатериал подкладки обуви\r\nтекстиль натуральный #cat:classic #gender:women #age:kids', '/../../asserts/img/products/product-1777474017-1109.webp', 700, '2026-04-29 17:46:57', '2026-04-29 17:46:57'),
(8, 'Ботинки утепленные демисезонные для мальчиков', 'Состав\r\nэкокожа; текстиль\r\nПол\r\nМальчики\r\nСезон\r\nДемисезон\r\nМатериал подкладки обуви\r\nнатуральный шерстяной мех #cat:classic #gender:men #age:kids', '/../../asserts/img/products/product-1777474094-9519.webp', 931, '2026-04-29 17:48:14', '2026-04-29 17:48:14'),
(9, 'Демисезонные спортивные кроссовки', 'Состав\r\nтекстиль; хлопок; ПВХ; натуральная кожа\r\nПол\r\nМальчики\r\nСезон\r\nКруглогодичный\r\nМатериал стельки\r\nнатуральная кожа #cat:sport #gender:men #age:kids', '/../../asserts/img/products/product-1777474156-7057.webp', 600, '2026-04-29 17:49:16', '2026-04-29 17:49:16');

-- --------------------------------------------------------

--
-- Структура таблицы `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `review_text` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `product_reviews`
--

INSERT INTO `product_reviews` (`id`, `product_id`, `user_id`, `rating`, `review_text`, `created_at`) VALUES
(1, 3, 10, 5, 'хуйня', '2026-04-29 17:58:15');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `is_verified` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `token`, `is_verified`, `created_at`, `is_admin`, `balance`) VALUES
(1, 'Петров', 'petrov@mail.ru', '$2y$10$XXTBpBCTj8oPoaA4nPUsne9qI1FGsgDtUdxK.KPiKR89KV13zUAbm', '448d3a0327b90b1bf583370a491ec5dde9cab0e2c9bb54623d01428531336b4b', 0, '2025-04-15 10:01:31', 1, 0.00),
(2, 'Сидорова', 'sidorova@mail.ru', '$2y$10$A17hjhtd7S1/bZ1dNS2ZseDnhMkAFcQM25iQY3hH4j29IYFBEAs6.', '359b71ac6c3137a13d55791222aba24a', 0, '2025-04-15 10:04:53', 0, 0.00),
(3, 'Иван', 'iamjptnkculjmmm-19966@ke.furvionx.com', '$2y$10$RJQPjk8YOsWYvx/9pxqVX.Uz.G6PCL3y22iS8RuttmRZAoDIl13oO', '4aac10d7475a851cf348ae4ef16c28d16947fd17e258a0ef2e6580a255c327de', 0, '2026-04-14 17:22:14', 0, 0.00),
(4, 'Иван Иванов', 'sriptmmrarhcnfs-92953@up.tomicorp.com', '$2y$10$HPuPsLuz.6W9xOAwcA2Mw.UZqsQ3jHkAZ13DBqrVJFHFIzJOoKNs2', '', 1, '2026-04-14 18:02:42', 0, 0.00),
(6, '+79009800-33-55', 'iamjptnkculjmmm-1996sefwefw6@ke.furvionx.com', '$2y$10$KcbnaG5/JcKZzXlsfzc3nOdOJlzkFTqfWQ642wZICy8o5BKMmCZpO', '63c97853464856bbcb892b94e740ff92bc20b67ab3a60acbcb97a606fa720ee7', 0, '2026-04-15 13:59:20', 0, 0.00),
(7, '+790009204455', 'iamjptnkculjmmm-199aaaa66@ke.furvionx.com', '$2y$10$7UtKdS47bcEfYdFaB5/zBeWQPYYWxBFX2gAt/x3KPJyTllZ6kjD/a', 'b1f6da767ee197ae125afdd2354fae6ce77212838cb10ee83dba16bb0d86b0cf', 0, '2026-04-15 14:02:24', 0, 0.00),
(8, '+790023', 'iylzepttcdtfczw-12dds159@binitrax.com', '$2y$10$DLUrxflllmt2gD/z/DbTbOWRVb9guCSUvWgqP.CTBJ15WniqGLycy', 'c4727eb4b2ddc9859166ac025e118773d8417f62bb71a4e9b951876ef9693d4f', 0, '2026-04-15 14:10:29', 0, 0.00),
(9, ' <p>\"Ivan\"<br></p>', 'iylzepttcdtfczw-1246545159@binitrax.com', '$2y$10$vp0j0KdUCd/KC1adTT7Qi.ZNtcCwJwhOqigkMGPnaveR/xA.6keri', 'ad302b5ac6cbea0c8d0b6145be150ad2bb896effbd9c46ba15e942052776fcc4', 0, '2026-04-15 15:50:14', 0, 0.00),
(10, 'kortez', 'dantezecode@gmail.com', '$2y$10$Jp0XPadBS2jpxehKWVWFjeCDunJSJFYQCkJAD4.gKWpx./HPcyqXK', '', 1, '2026-04-28 17:32:06', 1, 99999999.99),
(11, 'admin', 'admin@walkart.local', '$2y$10$V/qisPW13XT/qJlSO0Mp2u5DG0xPUZ1ZHez8BGz2ghlldHmnXs4ii', '', 1, '2026-04-29 17:23:43', 1, 99999999.99),
(12, 'test333', 'dantezecode3@gmail.com', '$2y$10$aiRk6Kq5kNLyExv/EtzlyOKkVFLgVFlk2FPcUmEV3hPpCVfGJo7hW', '', 1, '2026-04-29 18:35:14', 0, 0.00);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `order_item`
--
ALTER TABLE `order_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Индексы таблицы `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_product_user` (`product_id`,`user_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_email` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `order_item`
--
ALTER TABLE `order_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT для таблицы `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT для таблицы `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `order_item`
--
ALTER TABLE `order_item`
  ADD CONSTRAINT `order_item_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `order_item_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
