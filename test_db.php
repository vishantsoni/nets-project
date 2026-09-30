<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=nets", "root", "");
    echo "Connected\n";
    $stmt = $pdo->query("SELECT 1");
    echo "Query works\n";
} catch(Exception $e) {
    echo $e->getMessage() . "\n";
}