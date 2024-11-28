<?php

// /app/database/Database.php
namespace App\Database;  // El namespace debe coincidir con la carpeta "database"

use PDO;
use PDOException;

class Database
{
    public $last_result;
    private $host = 'localhost';
    private $dbname = 'agenda_contactos';
    private $username = 'root';
    private $password = '';
    private $connection;

    public function __construct()
    {
        try {
            // Crear la conexión
            $this->connection = new PDO("mysql:host={$this->host};dbname={$this->dbname}", $this->username, $this->password);
            // Configurar el modo de error de PDO
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function executeReadQuery($sql, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);

            // Asignar el resultado de la consulta a la propiedad result
            $this->last_result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->last_result;  // Retorna los resultados de la consulta
        } catch (PDOException $e) {
            echo "Error en la consulta de lectura: " . $e->getMessage();
            return false;
        }
    }

    public function executeWriteQuery($sql, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);

            // Si la consulta afecta filas, obtenemos el ID insertado
            if ($stmt->rowCount() > 0) {
                $insertId = $this->connection->lastInsertId();
                $this->last_result = $insertId;  // Asignamos el ID insertado a la propiedad result
                return ($insertId ? $insertId : $stmt->rowCount());
            } else {
                $this->last_result = $stmt->rowCount();  // Asignamos el número de filas afectadas a la propiedad result
                return 1;  // Retorna el número de filas afectadas
            }
        } catch (PDOException $e) {
            echo "Error en la consulta de escritura: " . $e->getMessage();
            return false;
        }
    }
}
