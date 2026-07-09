<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=inventory_db', 'root', '');
    $stmt = $pdo->query('SHOW COLUMNS FROM products');
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $col) {
        echo $col['Field'] . "\n";
    }
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
}
