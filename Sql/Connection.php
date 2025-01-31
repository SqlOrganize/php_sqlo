<?php

namespace SqlOrganize\Sql;

use Exception;
use PDO;
use PDOException;

/**
 * Clase de conexión a la base de datos
 * Ejemplo
 * Uso: cambiar entre 'mysql' y 'pgsql'
 * $connection = new Connection('pgsql'); // Cambia a 'mysql' o 'pgsql' según necesites
 * Ejemplo de consulta
 * $stmt = $connection->getPdo()->query("SELECT NOW()");
 * echo "Hora actual: " . $stmt->fetchColumn();
 */
class Connection {
    private $pdo;

    public function __construct($dbType = 'mysql') {
        // Configuración de conexión
        $config = [
            'mysql' => [
                'dsn' => 'mysql:host=localhost;dbname=testdb;charset=utf8mb4',
                'username' => 'root',
                'password' => '1234',
            ],
            'pgsql' => [
                'dsn' => 'pgsql:host=localhost;dbname=testdb',
                'username' => 'postgres',
                'password' => '1234',
            ]
        ];

        try {
            if (!isset($config[$dbType])) {
                throw new Exception("Tipo de base de datos no soportado: $dbType");
            }

            $this->pdo = new PDO(
                $config[$dbType]['dsn'],
                $config[$dbType]['username'],
                $config[$dbType]['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            echo "Conectado a $dbType exitosamente.\n";
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public function getPdo() {
        return $this->pdo;
    }
}
