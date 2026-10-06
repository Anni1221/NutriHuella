<?php
    // Conexión PDO a MySQL (WAMP). La estructura de la base está en database/nutrihuella.sql.
    $host = getenv('NH_HOST') ?: 'localhost';
    $db   = getenv('NH_DB')   ?: 'nutrihuella';
    $user = getenv('NH_USER') ?: 'root';
    $pass = getenv('NH_PASS') !== false ? getenv('NH_PASS') : '';

    $pdo = null;
    $db_error = null;
    $db_aviso = null;   // lo usan auth.php y contenido.php para mostrar avisos
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        $db_error = $e->getMessage();
        $pdo = null;
    }
