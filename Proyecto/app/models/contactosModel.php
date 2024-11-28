<?php

namespace App\Models;

use App\Database\Database;
use Exception;

class ContactosModel
{
    private $db;

    public function __construct()
    {
        // Crear instancia de la clase Database
        $this->db = new Database();
    }

    public function listarContactos()
    {
        try {
            $sql = "SELECT * FROM contactos";
            $params = [];
            return $this->db->executeReadQuery($sql, $params);
        } catch (Exception $e) {
            // Lanzar una excepción con el mensaje de error
            throw new Exception('Error al listar los contactos: ' . $e->getMessage());
        }
    }

    public function obtenerContactos($search = '', $start = 0, $limit = 10, $orderBy = 'id', $orderDir = 'desc')
    {
        try {
            // Construir la consulta básica
            $sql = "SELECT * FROM contactos WHERE nombre LIKE :search OR telefono LIKE :search OR descripcion LIKE :search ORDER BY $orderBy $orderDir LIMIT $start, $limit";
            
            // Preparar los parámetros
            $params = [
                ':search' => '%' . $search . '%'
            ];
    
            // Ejecutar la consulta
            return $this->db->executeReadQuery($sql, $params);
        } catch (Exception $e) {
            // Lanzar una excepción con el mensaje de error
            throw new Exception('Error al obtener los contactos: ' . $e->getMessage());
        }
    }

    public function contarContactos($search = '')
    {
        try {
            // Construir la consulta para contar el total de contactos
            $sql = "SELECT COUNT(*) FROM contactos WHERE nombre LIKE :search OR telefono LIKE :search OR descripcion LIKE :search";

            // Preparar los parámetros
            $params = [
                ':search' => '%' . $search . '%'
            ];

            // Ejecutar la consulta
            $result = $this->db->executeReadQuery($sql, $params);

            // Retornar el total de contactos
            return $result[0]['COUNT(*)'] ?? 0;
        } catch (Exception $e) {
            // Lanzar una excepción con el mensaje de error
            throw new Exception('Error al contar los contactos: ' . $e->getMessage());
        }
    }

    public function obtenerContactoPorId($id)
    {
        try {
            $sql = "SELECT * FROM contactos WHERE id = :id";
            $params = [':id' => $id];
            return $this->db->executeReadQuery($sql, $params);
        } catch (Exception $e) {
            throw new Exception('Error al obtener el contacto: ' . $e->getMessage());
        }
    }

    public function crearContacto($data)
    {
        try {
            $sql = "INSERT INTO contactos (nombre, telefono, edad, descripcion, imagen) 
                    VALUES (?,?,?,?,?)";
            $params = [
                $data["nombre"],
                $data["telefono"],
                $data["edad"],
                $data["descripcion"],
                $data["imagen"]
            ];
            return $this->db->executeWriteQuery($sql, $params);
        } catch (Exception $e) {
            throw new Exception('Error al crear el contacto: ' . $e->getMessage());
        }
    }

    public function actualizarContacto($data)
    {
        try {
            $sql = "UPDATE contactos 
                    SET nombre = ? , telefono = ?, edad = ?, descripcion = ?
                    WHERE id = ?";
            $params = [
                $data["nombre"],
                $data["telefono"],
                $data["edad"],
                $data["descripcion"],
                $data["id"]
            ];
            return $this->db->executeWriteQuery($sql, $params);
        } catch (Exception $e) {
            // Lanzamos una excepción personalizada con el mensaje de error
            throw new Exception('Error al actualizar el contacto: ' . $e->getMessage());
        }
    }

    public function actualizarImagenContacto($data)
    {
        try {
            $sql = "UPDATE contactos 
                    SET imagen = ?
                    WHERE id = ?";
            $params = [
                $data["imagen"],
                $data["id"]
            ];
            return $this->db->executeWriteQuery($sql, $params);
        } catch (Exception $e) {
            // Lanzamos una excepción personalizada con el mensaje de error
            throw new Exception('Error al actualizar el contacto: ' . $e->getMessage());
        }
    }

    public function eliminarContacto($id)
    {
        try {
            $sql = "DELETE FROM contactos WHERE id = :id";
            $params = [':id' => $id];
            return $this->db->executeWriteQuery($sql, $params);
        } catch (Exception $e) {
            throw new Exception('Error al eliminar el contacto: ' . $e->getMessage());
        }
    }
}
