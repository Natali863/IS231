<?php
namespace App\Services;

use PDO;

class OrderDBStorage extends DBStorage implements ISaveStorage
{
    public function saveData(string $name, array $data): bool
    {
        global $user_id;
        // Динамическая сборка полей INSERT в зависимости от наличия столбцов
        // Определяем имя столбца пользователя в заказах (user_id или user)
        $userCol = null;
        if ($this->columnExistsInTable($name, 'user_id')) {
            $userCol = 'user_id';
        } elseif ($this->columnExistsInTable($name, 'user')) {
            $userCol = 'user';
        }

        $fields = [];
        $placeholders = [];
        $params = [];

        if ($this->columnExistsInTable($name, 'fio')) {
            $fields[] = '`fio`';
            $placeholders[] = ':fio';
            $params[':fio'] = $data['fio'] ?? '';
        }
        if ($this->columnExistsInTable($name, 'address')) {
            $fields[] = '`address`';
            $placeholders[] = ':address';
            $params[':address'] = $data['address'] ?? '';
        }
        if ($this->columnExistsInTable($name, 'phone')) {
            $fields[] = '`phone`';
            $placeholders[] = ':phone';
            $params[':phone'] = $data['phone'] ?? '';
        }
        if ($this->columnExistsInTable($name, 'email')) {
            $fields[] = '`email`';
            $placeholders[] = ':email';
            $params[':email'] = $data['email'] ?? '';
        }
        // all_sum может называться по-разному; используем 'all_sum' как в изначальном коде
        if ($this->columnExistsInTable($name, 'all_sum')) {
            $fields[] = '`all_sum`';
            $placeholders[] = ':sum';
            $params[':sum'] = $data['all_sum'] ?? 0;
        }
        // пользователь (если колонка предусмотрена схемой)
        if ($userCol !== null) {
            $fields[] = "`$userCol`";
            $placeholders[] = ':userid';
            $params[':userid'] = $user_id;
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "INSERT INTO `$name` (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";

        $sth = $this->connection->prepare($sql);
        $result = $sth->execute($params);
        
        // получаем идентификатор добавленного заказа
        $idOrder = $this->connection->lastInsertId();
        // добавляем позиции заказа (заказанные товары)
        $this->saveItems($idOrder, $data['products']);

        return $result;
    }

    /*
    добавляет позиции заказа в таблицу order_item
    */
    public function saveItems(int $idOrder, array $products): bool 
    {
        foreach ($products as $product) {
            $id = $product['id'];
            $price = $product['price'];
            $quantity = $product['quantity'];
            $sum = $price * $quantity;
            // SQL запрос на вставку данных в таблицу  order_item
            $sql = "INSERT INTO `order_item`
            (`order_id`, `product_id`, `count_item`, 
            `price_item`, `sum_item`) 
            VALUES 
            (:id_order, :id_product, :count, :price, :sum)";

            $sth = $this->connection->prepare($sql);

            $sth->execute( [
                'id_order' => $idOrder,
                'id_product' => $id,
                'count' => $quantity,
                'price' => $price,
                'sum' => $sum
            ] );
        }
        return true;
    }

    // Проверка наличия столбца в указанной таблице
    private function columnExistsInTable(string $table, string $column): bool {
        try {
            $stmt = $this->connection->prepare(
                "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?"
            );
            $stmt->execute([$table, $column]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
}
