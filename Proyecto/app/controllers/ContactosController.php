<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\ContactosModel as DatabaseContactosModel;
use App\Controllers\GeneralController;
use Exception;

class ContactosController
{
    // Propiedad para almacenar la instancia del modelo
    private $contactosModel;

    // Constructor de la clase para inicializar la instancia del modelo
    public function __construct()
    {
        // Inicializar la instancia del modelo
        $this->contactosModel = new DatabaseContactosModel();
    }

    // Método index
    public function index()
    {
        GeneralController::checkRequestMethod('GET');

        // Usamos la propiedad $contactosModel en lugar de crear una nueva instancia
        $data = $this->contactosModel->listarContactos();

        View::render('home.index', $data);
    }

    public function listar()
    {
        try {
            // Recibir los parámetros de DataTable (search, limit, start, order)
            $search = $_GET['search']['value'] ?? '';  // Término de búsqueda
            $limit = $_GET['length'] ?? 10;            // Número de resultados por página (limit)
            $start = $_GET['start'] ?? 0;              // Página actual (offset)
            $orderColumn = $_GET['order'][0]['column'] ?? 0;  // Columna por la que ordenar
            $orderDir = $_GET['order'][0]['dir'] ?? 'asc';    // Dirección de ordenación ('asc' o 'desc')

            // Definir las columnas que se pueden ordenar
            $columns = ['id', 'nombre', 'telefono', 'edad', 'descripcion'];

            // Obtener la columna por la que ordenar (nombre de la columna en la base de datos)
            $orderBy = $columns[$orderColumn] ?? 'id';

            // Usar el modelo para obtener los contactos con filtros y paginación
            $data = $this->contactosModel->obtenerContactos($search, $start, $limit, $orderBy, $orderDir);

            // Obtener el total de contactos para la paginación
            $totalContactos = $this->contactosModel->contarContactos($search);

            // Preparar la respuesta para DataTable
            $response = [
                "draw" => intval($_GET['draw'] ?? 0),  // Para la respuesta de DataTable
                "recordsTotal" => $totalContactos,      // Total de registros sin filtros
                "recordsFiltered" => $totalContactos,   // Total de registros después del filtro
                "data" => $data                         // Los datos de los contactos
            ];

            // Devolver la respuesta en formato JSON
            header('Content-Type: application/json');
            http_response_code(200);  // Establecer código de estado HTTP 200 (OK)
            echo json_encode($response);
            exit();
        } catch (Exception $e) {
            // Si ocurre un error, respondemos con un código 500 (Error interno del servidor)
            header('Content-Type: application/json');
            http_response_code(500);  // Establecer código de estado HTTP 500 (Error interno del servidor)
            echo json_encode([
                "status" => false,
                "message" => "Error al listar los contactos: " . $e->getMessage()
            ]);
            exit();
        }
    }
    public function buscar($id)
    {
        try {
            $contacto = $this->contactosModel->obtenerContactoPorId($id);
            if (count($contacto) == 0) {

                // Respuesta de error
                header('Content-Type: application/json');
                http_response_code(404);
                echo json_encode([
                    "status" => 404,
                    "message" => "El contacto no se encuentra registrado."
                ]);
                exit();
            } else {
                // Respuesta de error
                header('Content-Type: application/json');
                http_response_code(200);
                echo json_encode([
                    "status" => true,
                    "message" => "El contacto se encuentra disponible.",
                    "data" => $contacto
                ]);
                exit();
            }
        } catch (Exception $e) {
            // Si ocurre un error, respondemos con un código 500 (Error interno del servidor)
            header('Content-Type: application/json');
            http_response_code(500);  // Establecer código de estado HTTP 500 (Error interno del servidor)
            echo json_encode([
                "status" => false,
                "message" => "Error al listar los contactos: " . $e->getMessage()
            ]);
            exit();
        }
    }

    // Acción de crear un contacto
    public function crear()
    {
        try {
            GeneralController::checkRequestMethod('POST');

            $data = [
                "string" => [
                    "nombre" => [
                        "value" => $_POST["nombre"],
                        "referencia" => "El nombre",
                        "max-length" => 50,
                        "query-sql-false" => [
                            "sql" => "SELECT id FROM contactos WHERE nombre = ? ",
                            "data" => [$_POST["nombre"]],
                        ]
                    ],
                ],
                "normal" => [
                    "descripcion" => [
                        "value" => $_POST["descripcion"],
                        "referencia" => "La descripcion",
                        "max-length" => 255,
                        "null" => true
                    ],
                    "imagen" => [
                        "value" => $_FILES['imagen']['tmp_name'],
                        "referencia" => "La imagen",
                        "max-length" => 255,
                        "null" => true
                    ],
                ],
                "int" => [
                    "edad" => [
                        "value" => $_POST["edad"],
                        "min" => 1,
                        "referencia" => "La edad",
                    ],
                    "telefono" => [
                        "value" => $_POST["telefono"],
                        "referencia" => "El teléfono",
                        "lengths" => [10],
                        "query-sql-false" => [
                            "sql" => "SELECT id FROM contactos WHERE telefono = ? ",
                            "data" => [$_POST["telefono"]],
                        ]
                    ],
                ]
            ];

            $validations = GeneralController::validateInputs($data);

            if ($validations["status"] == false) {
                $json["message"] = "Error de validación";
                $json["errors"] = $validations["errors"];
                $json["type"] = "doble";
                $json["type234"] = $_POST;

                header('Content-Type: application/json');
                echo json_encode($json);
                exit();
            }

            // Usamos la propiedad $contactosModel en lugar de crear una nueva instancia
            $insert = $this->contactosModel->crearContacto($_POST);



            if ($insert) {
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                    // Definir la carpeta donde se guardará la imagen

                    $uploadDir = 'public/contactos/' . $insert . '/imagen/';

                    // Verificar si la carpeta de destino existe, si no, crearla
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true); // Crear la carpeta con permisos adecuados
                    }

                    // Obtener la información del archivo
                    $tmpName = $_FILES['imagen']['tmp_name'];
                    $originalName = $_FILES['imagen']['name'];
                    $fileExtension = pathinfo($originalName, PATHINFO_EXTENSION);

                    // Asegurarse de que el nombre de archivo sea único
                    $fileName = pathinfo($originalName, PATHINFO_FILENAME); // Obtener el nombre sin la extensión
                    $fileName = preg_replace("/[^a-zA-Z0-9_-]/", "", $fileName); // Limpiar el nombre del archivo
                    $newFileName = $fileName . '.' . $fileExtension;

                    // Lógica para comprobar si el archivo ya existe
                    $counter = 1;
                    while (file_exists($uploadDir . $newFileName)) {
                        $newFileName = $fileName . "({$counter})." . $fileExtension;
                        $counter++;
                    }

                    // Mover el archivo cargado a la carpeta de destino
                    if (move_uploaded_file($tmpName, $uploadDir . $newFileName)) {
                        $this->contactosModel->actualizarImagenContacto(["id" => $insert, "imagen" => $uploadDir . $newFileName]);
                    } else {
                        // Manejar el error si la carga de la imagen falla
                        $json["error"] = "Hubo un error al cargar la imagen.";
                        echo json_encode($json);
                        exit();
                    }
                }

                header('Content-Type: application/json');
                http_response_code(200);
                echo json_encode([
                    "status" => true,
                    "message" => "Se creó el contacto exitosamente."
                ]);
                exit();
            } else {
                header('Content-Type: application/json');
                http_response_code(400);
                echo json_encode([
                    "status" => 400,
                    "message" => "Error al crear elcontacto."
                ]);
                exit();
            }

            echo json_encode($insert);
            exit();
        } catch (\Throwable $th) {
            echo json_encode(["message" => "Error interno del sistema", "Error:" => $th->getMessage()]);
        }
    }

    // Acción de editar un contacto
    public function editar($id)
    {
        try {



            $contacto = $this->contactosModel->obtenerContactoPorId($id);
            if (count($contacto) == 0) {

                // Respuesta de error
                header('Content-Type: application/json');
                http_response_code(404);
                echo json_encode([
                    "status" => 404,
                    "message" => "El contacto no se encuentra registrado."
                ]);
                exit();
            }

            $data = [
                "string" => [
                    "nombre" => [
                        "value" => $_POST["nombre"],
                        "referencia" => "El nombre",
                        "max-length" => 50,
                        "query-sql-false" => [
                            "sql" => "SELECT id FROM contactos WHERE nombre = ? AND id != ? ",
                            "data" => [$_POST["nombre"], $id],
                        ]
                    ],

                ],
                "normal" => [
                    "descripcion" => [
                        "value" => $_POST["descripcion"],
                        "referencia" => "La descripcion",
                        "max-length" => 255,
                        "null" => true
                    ]
                ],
                "int" => [
                    "edad" => [
                        "value" => $_POST["edad"],
                        "min" => 1,
                        "referencia" => "La edad",
                    ],
                    "telefono" => [
                        "value" => $_POST["telefono"],
                        "referencia" => "El teléfono",
                        "lengths" => [10],
                        "query-sql-false" => [
                            "sql" => "SELECT id FROM contactos WHERE telefono = ? AND id != ? ",
                            "data" => [$_POST["telefono"], $id],
                        ]
                    ],
                ]
            ];

            $validations = GeneralController::validateInputs($data);

            if ($validations["status"] == false) {
                $json["message"] = "Error de validación";
                $json["errors"] = $validations["errors"];
                $json["type"] = "doble";

                header('Content-Type: application/json');
                echo json_encode($json);
                exit();
            }

            // Usamos la propiedad $contactosModel en lugar de crear una nueva instancia
            $_POST["id"] = $id;
            $edit = $this->contactosModel->actualizarContacto($_POST);

            if ($edit) {
                http_response_code(200);
                header('Content-Type: application/json');
                echo json_encode(["status" => true, "message" => "Se actualizó el contacto exitosamente."]);
            } else {
                http_response_code(400); // Bad Request - Sin cambios realizados
                header('Content-Type: application/json');
                echo json_encode([
                    "status" => false,
                    "message" => "No se realizaron cambios. El contacto no fue actualizado."
                ]);
            }

            exit();
        } catch (\Throwable $th) {
            echo json_encode(["message" => "Error interno del sistema", "Error:" => $th->getMessage()]);
        }
    }

    // Acción de eliminar un contacto
    public function eliminar($id)
    {
        /* GeneralController::checkRequestMethod('POST'); */

        $contacto = $this->contactosModel->obtenerContactoPorId($id);
        if (count($contacto) == 0) {
            // Respuesta de error
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode([
                "status" => false,
                "message" => "El contacto no se encuentra registrado."
            ]);
            exit();
        }

        // Usamos la propiedad $contactosModel en lugar de crear una nueva instancia
        $eliminar = $this->contactosModel->eliminarContacto($id);

        if ($eliminar) {
            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode([
                "status" => true,
                "message" => "Se eliminó el contacto exitosamente."
            ]);
            exit();
        } else {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode([
                "status" => false,
                "message" => "Error al eliminar el contacto."
            ]);
            exit();
        }

        header('Location: /contactos');
        exit();
    }

    public function eliminarImagen($id)
    {
        try {
            // Obtener el contacto por ID
            $contacto = $this->contactosModel->obtenerContactoPorId($id);

            // Verificar si el contacto existe
            if (count($contacto) == 0) {
                header('Content-Type: application/json');
                http_response_code(404);
                echo json_encode([
                    "status" => false,
                    "message" => "El contacto no se encuentra registrado."
                ]);
                exit();
            }

            // Obtener la ruta de la imagen del contacto
            $imagenPath = $contacto[0]['imagen'];  // Asumimos que 'imagen' es la columna que contiene la ruta

            // Verificar si existe la imagen en el sistema de archivos
            if (file_exists($imagenPath)) {
                // Eliminar la imagen
                unlink($imagenPath);
            }

            // Actualizar la base de datos para quitar la referencia de la imagen
            $this->contactosModel->actualizarImagenContacto(["id" => $id, "imagen" => null]);

            // Responder con éxito
            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode([
                "status" => true,
                "message" => "La imagen del contacto fue eliminada exitosamente."
            ]);
            exit();
        } catch (\Throwable $th) {
            // Manejar errores
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                "status" => false,
                "message" => "Error interno del sistema: " . $th->getMessage()
            ]);
            exit();
        }
    }

    public function actualizarImagen($id)
    {
        try {

            $data = [
                "normal" => [
                    "imagen" => [
                        "value" => $_FILES['imagen']['tmp_name'],
                        "referencia" => "La imagen",
                        "max-length" => 255,
                    ],
                ],
            ];

            $validations = GeneralController::validateInputs($data);

            if ($validations["status"] == false) {
                $json["message"] = "Error de validación";
                $json["errors"] = $validations["errors"];
                $json["type"] = "doble";
                $json["type234"] = $_POST;

                header('Content-Type: application/json');
                echo json_encode($json);
                exit();
            }

            // Obtener el contacto por ID
            $contacto = $this->contactosModel->obtenerContactoPorId($id);

            // Verificar si el contacto existe
            if (count($contacto) == 0) {
                header('Content-Type: application/json');
                http_response_code(404);
                echo json_encode([
                    "status" => false,
                    "message" => "El contacto no se encuentra registrado."
                ]);
                exit();
            }

            // Comprobar si se subió una nueva imagen
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                // Obtener la ruta de la imagen anterior
                $imagenAnterior = $contacto[0]['imagen'];  // Asumimos que 'imagen' es la columna que contiene la ruta

                // Si existe una imagen anterior, eliminarla
                if (file_exists($imagenAnterior)) {
                    unlink($imagenAnterior);
                }

                // Definir la carpeta donde se guardará la nueva imagen
                $uploadDir = 'public/contactos/' . $id . '/imagen/';

                // Verificar si la carpeta de destino existe, si no, crearla
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                // Obtener la información del archivo
                $tmpName = $_FILES['imagen']['tmp_name'];
                $originalName = $_FILES['imagen']['name'];
                $fileExtension = pathinfo($originalName, PATHINFO_EXTENSION);

                // Asegurarse de que el nombre de archivo sea único
                $fileName = pathinfo($originalName, PATHINFO_FILENAME);
                $fileName = preg_replace("/[^a-zA-Z0-9_-]/", "", $fileName); // Limpiar el nombre del archivo
                $newFileName = $fileName . '.' . $fileExtension;

                // Lógica para comprobar si el archivo ya existe
                $counter = 1;
                while (file_exists($uploadDir . $newFileName)) {
                    $newFileName = $fileName . "({$counter})." . $fileExtension;
                    $counter++;
                }

                // Mover el archivo cargado a la carpeta de destino
                if (move_uploaded_file($tmpName, $uploadDir . $newFileName)) {
                    // Actualizar la base de datos con la nueva ruta de la imagen
                    $this->contactosModel->actualizarImagenContacto(["id" => $id, "imagen" => $uploadDir . $newFileName]);

                    // Responder con éxito
                    header('Content-Type: application/json');
                    http_response_code(200);
                    echo json_encode([
                        "status" => true,
                        "message" => "La imagen del contacto fue actualizada exitosamente."
                    ]);
                    exit();
                } else {
                    // Manejar el error si la carga de la imagen falla
                    header('Content-Type: application/json');
                    http_response_code(500);
                    echo json_encode([
                        "status" => false,
                        "message" => "Hubo un error al cargar la nueva imagen."
                    ]);
                    exit();
                }
            } else {
                // Si no se ha subido una nueva imagen
                header('Content-Type: application/json');
                http_response_code(400);
                echo json_encode([
                    "status" => false,
                    "message" => "No se ha subido una nueva imagen.",
                    "errors" => ["imagen" => "La imagen no puede estar vacía"],
                ]);
                exit();
            }
        } catch (\Throwable $th) {
            // Manejar errores
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                "status" => false,
                "message" => "Error interno del sistema: " . $th->getMessage()
            ]);
            exit();
        }
    }
}
