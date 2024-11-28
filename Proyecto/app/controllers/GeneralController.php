<?php

namespace App\Controllers;

use App\Core\View;
use App\Database\Database;

class GeneralController
{
    public $db;

    public function __construct()
    {
        // Asegúrate de que la instancia de la clase Database esté correctamente creada
        $this->db = new Database();
    }

    public static function getRequestData()
    {
        // Creamos un array vacío para almacenar los datos
        $data = [];

        // Si el método es GET, agregamos las variables de la URL a $data
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $data = array_merge($data, $_GET); // Fusionamos los datos GET
        }

        // Si el método es POST, agregamos las variables de POST a $data
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = array_merge($data, $_POST); // Fusionamos los datos POST
        }

        // Si el método es PUT o PATCH, intentamos leer el cuerpo de la solicitud
        if (in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'PATCH'])) {
            // Leemos el cuerpo de la solicitud (php://input) para verificar si hay JSON
            $input = file_get_contents('php://input');

            // Si hay contenido en el cuerpo de la solicitud
            if (!empty($input)) {
                // Intentamos decodificar el JSON recibido
                $decodedJson = json_decode($input, true);



                // Si la decodificación fue exitosa
                if (json_last_error() === JSON_ERROR_NONE) {
                    // Fusionamos los datos JSON en $data
                    $data = array_merge($data, $decodedJson); // Fusionamos los datos JSON
                }
            }
        }

        // Retornamos todos los datos combinados (GET, POST, JSON)
        return $data;
    }

    // Verificar si la solicitud es del tipo esperado (GET, POST, etc.)
    public static function checkRequestMethod($method)
    {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
            // Si el método no coincide, redirigir o manejar el error
            // Por ejemplo, enviar una respuesta 405 Method Not Allowed
            /* header('HTTP/1.1 405 Method Not Allowed');
            echo "Método no permitido. Se esperaba {$method}."; */

            View::render('errors.error_404');

            exit();  // Detener la ejecución
        }
    }


    public static  function validateInputs($data)
    {


        $db = new Database();

        $keys = array_keys($data);
        $errors = [];

        for ($x = 0; $x < count($keys); $x++) {
            $attributes = array_keys($data[$keys[$x]]);

            for ($a = 0; $a < count($attributes); $a++) {
                if ($keys[$x] == "string") {
                    $nullValue = false;

                    if (isset($data[$keys[$x]][$attributes[$a]]["null"]) && $data[$keys[$x]][$attributes[$a]]["null"] == true) {
                        $nullValue = true;
                    }

                    if (
                        $nullValue == true && (
                            (!is_string($data[$keys[$x]][$attributes[$a]]["value"]) && !is_numeric($data[$keys[$x]][$attributes[$a]]["value"])) ||
                            $data[$keys[$x]][$attributes[$a]]["value"] === null ||
                            $data[$keys[$x]][$attributes[$a]]["value"] === ""
                        )
                    ) {
                        // No error message needed if the value is allowed to be null and is null or empty
                    } else {
                        $elementKey = array_keys($data[$keys[$x]][$attributes[$a]]);
                        $referencia = "";

                        if (isset($data[$keys[$x]][$attributes[$a]]["referencia"])) {
                            $referencia = $data[$keys[$x]][$attributes[$a]]["referencia"];
                        } else {
                            $referencia = "El campo " . $attributes[$a];
                        }

                        if (!isset($data[$keys[$x]][$attributes[$a]]["value"]) || trim($data[$keys[$x]][$attributes[$a]]["value"]) === "") {
                            $errors[$attributes[$a]] = $referencia . " no puede estar vacío.";
                        } elseif (!preg_match('/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s.\/]+$/', $data[$keys[$x]][$attributes[$a]]["value"])) {
                            $errors[$attributes[$a]] = $referencia . " no puede tener números ni caracteres especiales.";
                        } else {
                            $queryfalse = false;
                            if (isset($data[$keys[$x]][$attributes[$a]]['query-sql-true'])) {
                                if (isset($data[$keys[$x]][$attributes[$a]]['query-sql-true']['sql'])) {
                                    $query_sql = $db->executeReadQuery($data[$keys[$x]][$attributes[$a]]['query-sql-true']['sql'] . " LIMIT 1;", $data[$keys[$x]][$attributes[$a]]['query-sql-true']['data'], true, true, true);
                                } else {
                                    $query_sql = $db->executeReadQuery($data[$keys[$x]][$attributes[$a]]['query-sql-true'] . " LIMIT 1;", true, true, true);
                                }

                                if (!$query_sql[0]) {
                                    $errors[$attributes[$a]] = ucfirst($referencia) . " no se encuentra disponible.";
                                    $queryfalse = true;
                                }
                            } else if (isset($data[$keys[$x]][$attributes[$a]]['query-sql-false'])) {
                                if (isset($data[$keys[$x]][$attributes[$a]]['query-sql-false']['sql'])) {
                                    $query_sql = $db->executeReadQuery($data[$keys[$x]][$attributes[$a]]['query-sql-false']['sql'] . " LIMIT 1;", $data[$keys[$x]][$attributes[$a]]['query-sql-false']['data'], true, true, true);
                                } else {
                                    $query_sql = $db->executeReadQuery($data[$keys[$x]][$attributes[$a]]['query-sql-false'] . " LIMIT 1;", true, true, true);
                                }

                                if ($query_sql[0]) {
                                    $errors[$attributes[$a]] = ucfirst($referencia) . " ya se encuentra registrado.";
                                    $queryfalse = true;
                                }
                            }
                            if (!$queryfalse) {
                                $trimmedValue = preg_replace('/\s+/', ' ', trim($data[$keys[$x]][$attributes[$a]]["value"]));
                                $lowercaseValue = strtolower($trimmedValue);

                                if (isset($data[$keys[$x]][$attributes[$a]]["lengths"]) && is_array($data[$keys[$x]][$attributes[$a]]["lengths"])) {
                                    $valueLength = strlen(str_replace(' ', '', trim((string) $data[$keys[$x]][$attributes[$a]]["value"])));

                                    if (!in_array($valueLength, $data[$keys[$x]][$attributes[$a]]["lengths"])) {
                                        $lengths = "";
                                        if (count($data[$keys[$x]][$attributes[$a]]["lengths"]) > 1) {
                                            $lengths .= "entre ";
                                        }
                                        foreach ($data[$keys[$x]][$attributes[$a]]["lengths"] as $index => $length) {
                                            if ($index == 0) {
                                                $lengths .= $length;
                                            } else if ($index == (count($data[$keys[$x]][$attributes[$a]]["lengths"]) - 1)) {
                                                $lengths .= " y " . $length;
                                            } else {
                                                $lengths .= " , " . $length;
                                            }
                                        }
                                        $errors[$attributes[$a]] = $referencia . " debe tener " . $lengths . " carácteres.";
                                    }
                                } else {
                                    $valueLength = strlen((string) $data[$keys[$x]][$attributes[$a]]["value"]);
                                    if (is_numeric($data[$keys[$x]][$attributes[$a]]["max-length"]) &&  isset($data[$keys[$x]][$attributes[$a]]["max-length"]) && $valueLength > $data[$keys[$x]][$attributes[$a]]["max-length"]) {
                                        $errors[$attributes[$a]] = $referencia . " no puede tener más de " . $data[$keys[$x]][$attributes[$a]]["max-length"] . " carácteres.";
                                    } else if (isset($data[$keys[$x]][$attributes[$a]]["min-length"]) && $valueLength < $data[$keys[$x]][$attributes[$a]]["min-length"]) {
                                        $errors[$attributes[$a]] = $referencia . " no puede tener menos de " . $data[$keys[$x]][$attributes[$a]]["min-length"] . " carácteres.";
                                    } else if (isset($data[$keys[$x]][$attributes[$a]]["max"]) && $valueLength > $data[$keys[$x]][$attributes[$a]]["max"]) {
                                        $errors[$attributes[$a]] = $referencia . " no puede tener más de " . $data[$keys[$x]][$attributes[$a]]["max"] . " carácteres.";
                                    } else if (isset($data[$keys[$x]][$attributes[$a]]["min"]) && $valueLength < $data[$keys[$x]][$attributes[$a]]["min"]) {
                                        $errors[$attributes[$a]] = $referencia . " no puede tener menos de " . $data[$keys[$x]][$attributes[$a]]["min"] . " carácteres.";
                                    }
                                }
                                if (!$errors[$attributes[$a]]) {
                                    $result[$attributes[$a]] = $trimmedValue;
                                }
                            }
                        }
                    }
                }
                if ($keys[$x] == "int") {
                    for ($a = 0; $a < count($attributes); $a++) {
                        $nullValue = false;

                        if (isset($data[$keys[$x]][$attributes[$a]]["null"]) && $data[$keys[$x]][$attributes[$a]]["null"] == true) {
                            $nullValue = true;
                        }

                        if (
                            $nullValue == true && (
                                (!is_string($data[$keys[$x]][$attributes[$a]]["value"]) && !is_numeric($data[$keys[$x]][$attributes[$a]]["value"])) ||
                                $data[$keys[$x]][$attributes[$a]]["value"] === null ||
                                $data[$keys[$x]][$attributes[$a]]["value"] === ""
                            )
                        ) {
                            // No error message needed if the value is allowed to be null and is null or empty
                        } else {
                            $referencia = isset($data[$keys[$x]][$attributes[$a]]["referencia"]) ?
                                $data[$keys[$x]][$attributes[$a]]["referencia"] :
                                "El campo " . $attributes[$a];

                            if (
                                (!is_string($data[$keys[$x]][$attributes[$a]]["value"]) && !is_numeric($data[$keys[$x]][$attributes[$a]]["value"])) ||
                                $data[$keys[$x]][$attributes[$a]]["value"] === null ||
                                $data[$keys[$x]][$attributes[$a]]["value"] === ""
                            ) {
                                $errors[$attributes[$a]] = $referencia . " no puede estar vacío.";
                            } else if (!preg_match('/^\s*-?\d+\s*$|^\s*-?\d+\s*-?\d+\s*$/', (string) $data[$keys[$x]][$attributes[$a]]["value"])) {
                                if (is_numeric($data[$keys[$x]][$attributes[$a]]["value"])) {
                                    if (strpos($data[$keys[$x]][$attributes[$a]]["value"], '.') !== false) {
                                        $errors[$attributes[$a]] = $referencia . " debe ser un número entero.";
                                    }
                                } else {
                                    $errors[$attributes[$a]] = $referencia . " no puede tener letras ni caracteres especiales.";
                                }
                            } else {
                                $queryfalse = false;
                                if (isset($data[$keys[$x]][$attributes[$a]]['query-sql-true'])) {
                                    if (isset($data[$keys[$x]][$attributes[$a]]['query-sql-true']['sql'])) {
                                        $query_sql = $db->executeReadQuery($data[$keys[$x]][$attributes[$a]]['query-sql-true']['sql'] . " LIMIT 1;", $data[$keys[$x]][$attributes[$a]]['query-sql-true']['data'], true, true, true);
                                    } else {
                                        $query_sql = $db->executeReadQuery($data[$keys[$x]][$attributes[$a]]['query-sql-true'] . " LIMIT 1;", true, true, true);
                                    }

                                    if (!$query_sql[0]) {
                                        $errors[$attributes[$a]] = ucfirst($referencia) . " no se encuentra disponible.";
                                        $queryfalse = true;
                                    }
                                } else if (isset($data[$keys[$x]][$attributes[$a]]['query-sql-false'])) {
                                    if (isset($data[$keys[$x]][$attributes[$a]]['query-sql-false']['sql'])) {
                                        $query_sql = $db->executeReadQuery($data[$keys[$x]][$attributes[$a]]['query-sql-false']['sql'] . " LIMIT 1;", $data[$keys[$x]][$attributes[$a]]['query-sql-false']['data'], true, true, true);
                                    } else {
                                        $query_sql = $db->executeReadQuery($data[$keys[$x]][$attributes[$a]]['query-sql-false'] . " LIMIT 1;", true, true, true);
                                    }

                                    if ($query_sql[0]) {
                                        $errors[$attributes[$a]] = ucfirst($referencia) . " ya se encuentra registrado.";
                                        $queryfalse = true;
                                    }
                                }

                                if (!$queryfalse) {
                                    if (isset($data[$keys[$x]][$attributes[$a]]["lengths"]) && is_array($data[$keys[$x]][$attributes[$a]]["lengths"])) {
                                        $valueLength = strlen(str_replace(' ', '', trim((string) $data[$keys[$x]][$attributes[$a]]["value"])));

                                        if (!in_array($valueLength, $data[$keys[$x]][$attributes[$a]]["lengths"])) {
                                            $lengths = "";
                                            if (count($data[$keys[$x]][$attributes[$a]]["lengths"]) > 1) {
                                                $lengths .= "entre ";
                                            }
                                            foreach ($data[$keys[$x]][$attributes[$a]]["lengths"] as $index => $length) {
                                                if ($index == 0) {
                                                    $lengths .= $length;
                                                } else if ($index == (count($data[$keys[$x]][$attributes[$a]]["lengths"]) - 1)) {
                                                    $lengths .= " y " . $length;
                                                } else {
                                                    $lengths .= " , " . $length;
                                                }
                                            }
                                            $errors[$attributes[$a]] = $referencia . " debe tener " . $lengths . " carácteres.";
                                        }
                                    } else {
                                        $valueLength = strlen((string) $data[$keys[$x]][$attributes[$a]]["value"]);
                                        if (is_numeric($data[$keys[$x]][$attributes[$a]]["max-length"]) &&  isset($data[$keys[$x]][$attributes[$a]]["max-length"]) && $valueLength > $data[$keys[$x]][$attributes[$a]]["max-length"]) {
                                            $errors[$attributes[$a]] = $referencia . " no puede tener más de " . $data[$keys[$x]][$attributes[$a]]["max-length"] . " carácteres.";
                                        } else if (isset($data[$keys[$x]][$attributes[$a]]["min-length"]) && $valueLength < $data[$keys[$x]][$attributes[$a]]["min-length"]) {
                                            $errors[$attributes[$a]] = $referencia . " no puede tener menos de " . $data[$keys[$x]][$attributes[$a]]["min-length"] . " carácteres.";
                                        } else if (isset($data[$keys[$x]][$attributes[$a]]["max"]) && $data[$keys[$x]][$attributes[$a]]["value"] > $data[$keys[$x]][$attributes[$a]]["max"]) {
                                            $errors[$attributes[$a]] = $referencia . " no puede ser mayor a " . $data[$keys[$x]][$attributes[$a]]["max"];
                                        } else if (isset($data[$keys[$x]][$attributes[$a]]["min"]) && $data[$keys[$x]][$attributes[$a]]["value"] < $data[$keys[$x]][$attributes[$a]]["min"]) {
                                            $errors[$attributes[$a]] = $referencia . " no puede ser menor a " . $data[$keys[$x]][$attributes[$a]]["min"];
                                        }
                                    }

                                    if (!isset($errors[$attributes[$a]])) {
                                        $result[$attributes[$a]] = trim(preg_replace('/\s+/', '', strtolower((string) $data[$keys[$x]][$attributes[$a]]["value"])));
                                    }
                                }
                            }
                        }
                    }
                }

                if ($keys[$x] == "float") {
                    for ($a = 0; $a < count($attributes); $a++) {
                        $nullValue = false;

                        if (isset($data[$keys[$x]][$attributes[$a]]["null"]) && $data[$keys[$x]][$attributes[$a]]["null"] == true) {
                            $nullValue = true;
                        }

                        if (
                            $nullValue == true && (
                                (!is_string($data[$keys[$x]][$attributes[$a]]["value"]) && !is_numeric($data[$keys[$x]][$attributes[$a]]["value"])) ||
                                $data[$keys[$x]][$attributes[$a]]["value"] === null ||
                                $data[$keys[$x]][$attributes[$a]]["value"] === ""
                            )
                        ) {
                            // No error message needed if the value is allowed to be null and is null or empty
                        } else {
                            $elementKey = array_keys($data[$keys[$x]][$attributes[$a]]);
                            $referencia = "";

                            if (isset($data[$keys[$x]][$attributes[$a]]["referencia"])) {
                                $referencia = $data[$keys[$x]][$attributes[$a]]["referencia"];
                            } else {
                                $referencia = "El campo " . $attributes[$a];
                            }

                            if (!isset($data[$keys[$x]][$attributes[$a]]["value"]) || $data[$keys[$x]][$attributes[$a]]["value"] == "") {
                                $errors[$attributes[$a]] = $referencia . " no puede estar vacío.";
                            } elseif (!preg_match('/^-?\d+(\s*\.\s*\d+)?(\s+-?\d+(\s*\.\s*\d+)?)*\s*$/', $data[$keys[$x]][$attributes[$a]]["value"])) {
                                $errors[$attributes[$a]] = $referencia . " no puede tener letras, caracteres especiales o más de dos puntos.";
                            } else {
                                $queryfalse = false;
                                if (isset($data[$keys[$x]][$attributes[$a]]['query-sql-true'])) {
                                    if (isset($data[$keys[$x]][$attributes[$a]]['query-sql-true']['sql'])) {
                                        $query_sql = $db->executeReadQuery($data[$keys[$x]][$attributes[$a]]['query-sql-true']['sql'] . " LIMIT 1;", $data[$keys[$x]][$attributes[$a]]['query-sql-true']['data'], true, true, true);
                                    } else {
                                        $query_sql = $db->executeReadQuery($data[$keys[$x]][$attributes[$a]]['query-sql-true'] . " LIMIT 1;", true, true, true);
                                    }

                                    if (!$query_sql[0]) {
                                        $errors[$attributes[$a]] = ucfirst($referencia) . " no se encuentra disponible.";
                                        $queryfalse = true;
                                    }
                                } else if (isset($data[$keys[$x]][$attributes[$a]]['query-sql-false'])) {
                                    if (isset($data[$keys[$x]][$attributes[$a]]['query-sql-false']['sql'])) {
                                        $query_sql = $db->executeReadQuery($data[$keys[$x]][$attributes[$a]]['query-sql-false']['sql'] . " LIMIT 1;", $data[$keys[$x]][$attributes[$a]]['query-sql-false']['data'], true, true, true);
                                    } else {
                                        $query_sql = $db->executeReadQuery($data[$keys[$x]][$attributes[$a]]['query-sql-false'] . " LIMIT 1;", true, true, true);
                                    }

                                    if ($query_sql[0]) {
                                        $errors[$attributes[$a]] = ucfirst($referencia) . " ya se encuentra registrado.";
                                        $queryfalse = true;
                                    }
                                }

                                if (!$queryfalse) {
                                    $indicePunto = strpos($data[$keys[$x]][$attributes[$a]]["value"], '.');

                                    if (isset($data[$keys[$x]][$attributes[$a]]["lengths"]) && is_array($data[$keys[$x]][$attributes[$a]]["lengths"])) {
                                        $valueLength = strlen(str_replace(' ', '', trim((string) $data[$keys[$x]][$attributes[$a]]["value"])));

                                        if (!in_array($valueLength, $data[$keys[$x]][$attributes[$a]]["lengths"])) {
                                            $lengths = "";
                                            if (count($data[$keys[$x]][$attributes[$a]]["lengths"]) > 1) {
                                                $lengths .= "entre ";
                                            }
                                            foreach ($data[$keys[$x]][$attributes[$a]]["lengths"] as $index => $length) {
                                                if ($index == 0) {
                                                    $lengths .= $length;
                                                } else if ($index == (count($data[$keys[$x]][$attributes[$a]]["lengths"]) - 1)) {
                                                    $lengths .= " y " . $length;
                                                } else {
                                                    $lengths .= " , " . $length;
                                                }
                                            }
                                            $errors[$attributes[$a]] = $referencia . " debe tener " . $lengths . " carácteres.";
                                        }
                                    } else {
                                        $valueLength = strlen((string) $data[$keys[$x]][$attributes[$a]]["value"]);
                                        if (is_numeric($data[$keys[$x]][$attributes[$a]]["max-length"]) &&  isset($data[$keys[$x]][$attributes[$a]]["max-length"]) && $valueLength > $data[$keys[$x]][$attributes[$a]]["max-length"]) {
                                            $errors[$attributes[$a]] = $referencia . " no puede tener más de " . $data[$keys[$x]][$attributes[$a]]["max-length"] . " carácteres.";
                                        } else if (isset($data[$keys[$x]][$attributes[$a]]["min-length"]) && $valueLength < $data[$keys[$x]][$attributes[$a]]["min-length"]) {
                                            $errors[$attributes[$a]] = $referencia . " no puede tener menos de " . $data[$keys[$x]][$attributes[$a]]["min-length"] . " carácteres.";
                                        } else if (isset($data[$keys[$x]][$attributes[$a]]["max"]) && $data[$keys[$x]][$attributes[$a]]["value"] > $data[$keys[$x]][$attributes[$a]]["max"]) {
                                            $errors[$attributes[$a]] = $referencia . " no puede ser mayor a " . $data[$keys[$x]][$attributes[$a]]["max"];
                                        } else if (isset($data[$keys[$x]][$attributes[$a]]["min"]) && $data[$keys[$x]][$attributes[$a]]["value"] < $data[$keys[$x]][$attributes[$a]]["min"]) {
                                            $errors[$attributes[$a]] = $referencia . " no puede ser menor a " . $data[$keys[$x]][$attributes[$a]]["min"];
                                        }
                                    }
                                }

                                if (!$errors[$attributes[$a]]) {
                                    $result[$attributes[$a]] = strtolower(trim($data[$keys[$x]][$attributes[$a]]["value"]));
                                }
                            }
                        }
                    }
                }
                if ($keys[$x] == "email") {
                    for ($a = 0; $a < count($attributes); $a++) {
                        $elementKey = array_keys($data[$keys[$x]][$attributes[$a]]);
                        $referencia = "";

                        if (isset($data[$keys[$x]][$attributes[$a]]["referencia"])) {
                            $referencia = $data[$keys[$x]][$attributes[$a]]["referencia"];
                        } else {
                            $referencia = "El campo " . $attributes[$a];
                        }

                        if (!isset($data[$keys[$x]][$attributes[$a]]["value"]) || $data[$keys[$x]][$attributes[$a]]["value"] == "") {
                            $errors[$attributes[$a]] = $referencia . " no puede estar vacío.";
                        } elseif (!preg_match('/^[^@]+@[^@.]+(\.[a-zA-Z0-9]+)+$/', $data[$keys[$x]][$attributes[$a]]["value"])) {
                            $errors[$attributes[$a]] = $referencia . " debe ser un correo válido.";
                        } else {
                            $queryfalse = false;
                            if (isset($data[$keys[$x]][$attributes[$a]]['query-sql-true'])) {
                                if (isset($data[$keys[$x]][$attributes[$a]]['query-sql-true']['sql'])) {
                                    $query_sql = $db->executeReadQuery($data[$keys[$x]][$attributes[$a]]['query-sql-true']['sql'] . " LIMIT 1;", $data[$keys[$x]][$attributes[$a]]['query-sql-true']['data'], true, true, true);
                                } else {
                                    $query_sql = $db->executeReadQuery($data[$keys[$x]][$attributes[$a]]['query-sql-true'] . " LIMIT 1;", true, true, true);
                                }

                                if (!$query_sql[0]) {
                                    $errors[$attributes[$a]] = ucfirst($referencia) . " no se encuentra disponible.";
                                    $queryfalse = true;
                                }
                            } else if (isset($data[$keys[$x]][$attributes[$a]]['query-sql-false'])) {
                                if (isset($data[$keys[$x]][$attributes[$a]]['query-sql-false']['sql'])) {
                                    $query_sql = $db->executeReadQuery($data[$keys[$x]][$attributes[$a]]['query-sql-false']['sql'] . " LIMIT 1;", $data[$keys[$x]][$attributes[$a]]['query-sql-false']['data'], true, true, true);
                                } else {
                                    $query_sql = $db->executeReadQuery($data[$keys[$x]][$attributes[$a]]['query-sql-false'] . " LIMIT 1;", true, true, true);
                                }

                                if ($query_sql[0]) {
                                    $errors[$attributes[$a]] = ucfirst($referencia) . " ya se encuentra registrado.";
                                    $queryfalse = true;
                                }
                            }

                            if (!$queryfalse) {
                                $trimmedValue = strtolower(trim($data[$keys[$x]][$attributes[$a]]["value"]));

                                if (isset($data[$keys[$x]][$attributes[$a]]["lengths"]) && is_array($data[$keys[$x]][$attributes[$a]]["lengths"])) {
                                    $valueLength = strlen(str_replace(' ', '', trim((string) $data[$keys[$x]][$attributes[$a]]["value"])));

                                    if (!in_array($valueLength, $data[$keys[$x]][$attributes[$a]]["lengths"])) {
                                        $lengths = "";
                                        if (count($data[$keys[$x]][$attributes[$a]]["lengths"]) > 1) {
                                            $lengths .= "entre ";
                                        }
                                        foreach ($data[$keys[$x]][$attributes[$a]]["lengths"] as $index => $length) {
                                            if ($index == 0) {
                                                $lengths .= $length;
                                            } else if ($index == (count($data[$keys[$x]][$attributes[$a]]["lengths"]) - 1)) {
                                                $lengths .= " y " . $length;
                                            } else {
                                                $lengths .= " , " . $length;
                                            }
                                        }
                                        $errors[$attributes[$a]] = $referencia . " debe tener " . $lengths . " carácteres.";
                                    }
                                } else {
                                    $valueLength = strlen((string) $data[$keys[$x]][$attributes[$a]]["value"]);

                                    if (is_numeric($data[$keys[$x]][$attributes[$a]]["max-length"]) &&  isset($data[$keys[$x]][$attributes[$a]]["max-length"]) && $valueLength > $data[$keys[$x]][$attributes[$a]]["max-length"]) {
                                        $errors[$attributes[$a]] = $referencia . " no puede tener más de " . $data[$keys[$x]][$attributes[$a]]["max-length"] . " carácteres.";
                                    } else if (isset($data[$keys[$x]][$attributes[$a]]["min-length"]) && $valueLength < $data[$keys[$x]][$attributes[$a]]["min-length"]) {
                                        $errors[$attributes[$a]] = $referencia . " no puede tener menos de " . $data[$keys[$x]][$attributes[$a]]["min-length"] . " carácteres.";
                                    } else if (isset($data[$keys[$x]][$attributes[$a]]["max"]) && $valueLength > $data[$keys[$x]][$attributes[$a]]["max"]) {
                                        $errors[$attributes[$a]] = $referencia . " no puede tener más de " . $data[$keys[$x]][$attributes[$a]]["max"] . " carácteres.";
                                    } else if (isset($data[$keys[$x]][$attributes[$a]]["min"]) && $valueLength < $data[$keys[$x]][$attributes[$a]]["min"]) {
                                        $errors[$attributes[$a]] = $referencia . " no puede tener menos de " . $data[$keys[$x]][$attributes[$a]]["min"] . " carácteres.";
                                    }
                                }

                                if (!$errors[$attributes[$a]]) {
                                    $result[$attributes[$a]] = str_replace(' ', '', $trimmedValue);
                                }
                            }
                        }
                    }
                }

                if ($keys[$x] == "password") {
                    for ($a = 0; $a < count($attributes); $a++) {
                        $elementKey = array_keys($data[$keys[$x]][$attributes[$a]]);
                        $referencia = "";

                        if (isset($data[$keys[$x]][$attributes[$a]]["referencia"])) {
                            $referencia = $data[$keys[$x]][$attributes[$a]]["referencia"];
                        } else {
                            $referencia = "El campo " . $attributes[$a];
                        }

                        if (!isset($data[$keys[$x]][$attributes[$a]]["value"]) || $data[$keys[$x]][$attributes[$a]]["value"] == "") {
                            $errors[$attributes[$a]] = $referencia . " no puede estar vacío.";
                        } elseif (preg_match('/\s/', $data[$keys[$x]][$attributes[$a]]["value"])) {
                            $errors[$attributes[$a]] = $referencia . " no puede tener espacios.";
                        } else {
                            $queryfalse = false;
                            if (isset($data[$keys[$x]][$attributes[$a]]['query-sql-true'])) {
                                if (isset($data[$keys[$x]][$attributes[$a]]['query-sql-true']['sql'])) {
                                    $query_sql = $db->executeReadQuery($data[$keys[$x]][$attributes[$a]]['query-sql-true']['sql'] . " LIMIT 1;", $data[$keys[$x]][$attributes[$a]]['query-sql-true']['data'], true, true, true);
                                } else {
                                    $query_sql = $db->executeReadQuery($data[$keys[$x]][$attributes[$a]]['query-sql-true'] . " LIMIT 1;", true, true, true);
                                }

                                if (!$query_sql[0]) {
                                    $errors[$attributes[$a]] = ucfirst($referencia) . " no se encuentra disponible.";
                                    $queryfalse = true;
                                }
                            } else if (isset($data[$keys[$x]][$attributes[$a]]['query-sql-false'])) {
                                if (isset($data[$keys[$x]][$attributes[$a]]['query-sql-false']['sql'])) {
                                    $query_sql = $db->executeReadQuery($data[$keys[$x]][$attributes[$a]]['query-sql-false']['sql'] . " LIMIT 1;", $data[$keys[$x]][$attributes[$a]]['query-sql-false']['data'], true, true, true);
                                } else {
                                    $query_sql = $db->executeReadQuery($data[$keys[$x]][$attributes[$a]]['query-sql-false'] . " LIMIT 1;", true, true, true);
                                }

                                if ($query_sql[0]) {
                                    $errors[$attributes[$a]] = ucfirst($referencia) . " ya se encuentra registrado.";
                                    $queryfalse = true;
                                }
                            }

                            if (!$queryfalse) {
                                $trimmedValue = strtolower(trim($data[$keys[$x]][$attributes[$a]]["value"]));

                                if (isset($data[$keys[$x]][$attributes[$a]]["lengths"]) && is_array($data[$keys[$x]][$attributes[$a]]["lengths"])) {
                                    $valueLength = strlen(str_replace(' ', '', trim((string) $data[$keys[$x]][$attributes[$a]]["value"])));

                                    if (!in_array($valueLength, $data[$keys[$x]][$attributes[$a]]["lengths"])) {
                                        $lengths = "";
                                        if (count($data[$keys[$x]][$attributes[$a]]["lengths"]) > 1) {
                                            $lengths .= "entre ";
                                        }
                                        foreach ($data[$keys[$x]][$attributes[$a]]["lengths"] as $index => $length) {
                                            if ($index == 0) {
                                                $lengths .= $length;
                                            } else if ($index == (count($data[$keys[$x]][$attributes[$a]]["lengths"]) - 1)) {
                                                $lengths .= " y " . $length;
                                            } else {
                                                $lengths .= " , " . $length;
                                            }
                                        }
                                        $errors[$attributes[$a]] = $referencia . " debe tener " . $lengths . " carácteres.";
                                    }
                                } else {
                                    $valueLength = strlen((string) $data[$keys[$x]][$attributes[$a]]["value"]);

                                    if (is_numeric($data[$keys[$x]][$attributes[$a]]["max-length"]) &&  isset($data[$keys[$x]][$attributes[$a]]["max-length"]) && $valueLength > $data[$keys[$x]][$attributes[$a]]["max-length"]) {
                                        $errors[$attributes[$a]] = $referencia . " no puede tener más de " . $data[$keys[$x]][$attributes[$a]]["max-length"] . " carácteres.";
                                    } else if (isset($data[$keys[$x]][$attributes[$a]]["min-length"]) && $valueLength < $data[$keys[$x]][$attributes[$a]]["min-length"]) {
                                        $errors[$attributes[$a]] = $referencia . " no puede tener menos de " . $data[$keys[$x]][$attributes[$a]]["min-length"] . " carácteres.";
                                    } else if (isset($data[$keys[$x]][$attributes[$a]]["max"]) && $valueLength > $data[$keys[$x]][$attributes[$a]]["max"]) {
                                        $errors[$attributes[$a]] = $referencia . " no puede tener más de " . $data[$keys[$x]][$attributes[$a]]["max"] . " carácteres.";
                                    } else if (isset($data[$keys[$x]][$attributes[$a]]["min"]) && $valueLength < $data[$keys[$x]][$attributes[$a]]["min"]) {
                                        $errors[$attributes[$a]] = $referencia . " no puede tener menos de " . $data[$keys[$x]][$attributes[$a]]["min"] . " carácteres.";
                                    }
                                }

                                if (!$errors[$attributes[$a]]) {
                                    $result[$attributes[$a]] = str_replace(' ', '', $trimmedValue);
                                }
                            }
                        }
                    }
                }
                if ($keys[$x] == "select") {
                    $nullValue = false;

                    if (isset($data[$keys[$x]][$attributes[$a]]["null"]) && $data[$keys[$x]][$attributes[$a]]["null"] == true) {
                        $nullValue = true;
                    }

                    if (
                        $nullValue == true && (
                            (!is_string($data[$keys[$x]][$attributes[$a]]["value"]) && !is_numeric($data[$keys[$x]][$attributes[$a]]["value"])) ||
                            $data[$keys[$x]][$attributes[$a]]["value"] === null ||
                            $data[$keys[$x]][$attributes[$a]]["value"] === ""
                        )
                    ) {
                        // No error message needed if the value is allowed to be null and is null or empty
                    } else {
                        $options = 0;
                        if (isset($data[$keys[$x]][$attributes[$a]]["options"])) {
                            $options = count($data[$keys[$x]][$attributes[$a]]["options"]);
                        }
                        $referencia = "";

                        if (isset($data[$keys[$x]][$attributes[$a]]["referencia"])) {
                            $referencia = $data[$keys[$x]][$attributes[$a]]["referencia"];
                        } else {
                            $referencia = "El campo " . $attributes[$a];
                        }

                        if ($options == 0 && (!isset($data[$keys[$x]][$attributes[$a]]["value"]) || $data[$keys[$x]][$attributes[$a]]["value"] == "")) {

                            $message_error =  ($data[$keys[$x]][$attributes[$a]]['messages_errors']['null'] ? $data[$keys[$x]][$attributes[$a]]['messages_errors']['null'] : ("Debe seleccionar una opción para " . $referencia . "."));


                            $errors[$attributes[$a]] = $message_error;
                        } else {
                            $queryfalse = false;
                            if (isset($data[$keys[$x]][$attributes[$a]]['query-sql-true'])) {
                                if (isset($data[$keys[$x]][$attributes[$a]]['query-sql-true']['sql'])) {
                                    $query_sql = $db->executeReadQuery($data[$keys[$x]][$attributes[$a]]['query-sql-true']['sql'] . " LIMIT 1;", $data[$keys[$x]][$attributes[$a]]['query-sql-true']['data'], true, true, true);
                                } else {
                                    $query_sql = $db->executeReadQuery($data[$keys[$x]][$attributes[$a]]['query-sql-true'] . " LIMIT 1;", true, true, true);
                                }

                                if (!$query_sql[0]) {
                                    $message_error =  ($data[$keys[$x]][$attributes[$a]]['messages_errors']['query-sql-true'] ? $data[$keys[$x]][$attributes[$a]]['messages_errors']['query-sql-true'] : (ucfirst($referencia) . " no se encuentra disponible."));

                                    $errors[$attributes[$a]] = $message_error;
                                    $queryfalse = true;
                                }
                            } else if (isset($data[$keys[$x]][$attributes[$a]]['query-sql-false'])) {
                                if (isset($data[$keys[$x]][$attributes[$a]]['query-sql-false']['sql'])) {
                                    $query_sql = $db->executeReadQuery($data[$keys[$x]][$attributes[$a]]['query-sql-false']['sql'] . " LIMIT 1;", $data[$keys[$x]][$attributes[$a]]['query-sql-false']['data'], true, true, true);
                                } else {
                                    $query_sql = $db->executeReadQuery($data[$keys[$x]][$attributes[$a]]['query-sql-false'] . " LIMIT 1;", true, true, true);
                                }

                                if ($query_sql[0]) {

                                    $message_error =  ($data[$keys[$x]][$attributes[$a]]['messages_errors']['query-sql-false'] ? $data[$keys[$x]][$attributes[$a]]['messages_errors']['query-sql-false'] : (ucfirst($referencia) . " ya se encuentra registrado."));

                                    $errors[$attributes[$a]] = $message_error;
                                    $queryfalse = true;
                                }
                            }

                            if (!$queryfalse) {
                                if (!$options) {
                                    $result[$attributes[$a]] = $data[$keys[$x]][$attributes[$a]]["value"];
                                }
                                for ($u = 0; $u < $options; $u++) {
                                    if (!isset($data[$keys[$x]][$attributes[$a]]["value"]) || $data[$keys[$x]][$attributes[$a]]["value"] == "") {
                                        $message_error =  ($data[$keys[$x]][$attributes[$a]]['messages_errors']['null'] ? $data[$keys[$x]][$attributes[$a]]['messages_errors']['null'] : ("Debe seleccionar una opción para " . $referencia . "."));

                                        $errors[$attributes[$a]] = $message_error;
                                        break;
                                    } elseif (strtolower($data[$keys[$x]][$attributes[$a]]["options"][$u]) === strtolower($data[$keys[$x]][$attributes[$a]]["value"])) {
                                        $result[$attributes[$a]] = strtolower(trim($data[$keys[$x]][$attributes[$a]]["options"][$u]));
                                        break;
                                    } elseif ($u == $options - 1) {
                                        $message_error =  ($data[$keys[$x]][$attributes[$a]]['messages_errors']['options'] ? $data[$keys[$x]][$attributes[$a]]['messages_errors']['options'] : ("Debe seleccionar una opción válida para " . $referencia . "."));

                                        $errors[$attributes[$a]] = $message_error;
                                    }
                                }
                            }
                        }
                    }
                }

                if ($keys[$x] == "normal") {
                    for ($a = 0; $a < count($attributes); $a++) {
                        $nullValue = false;

                        if (isset($data[$keys[$x]][$attributes[$a]]["null"]) && $data[$keys[$x]][$attributes[$a]]["null"] == true) {
                            $nullValue = true;
                        }

                        if (
                            $nullValue == true && (
                                (!is_string($data[$keys[$x]][$attributes[$a]]["value"]) && !is_numeric($data[$keys[$x]][$attributes[$a]]["value"])) ||
                                $data[$keys[$x]][$attributes[$a]]["value"] === null ||
                                $data[$keys[$x]][$attributes[$a]]["value"] === ""
                            )
                        ) {
                            // No error message needed if the value is allowed to be null and is null or empty
                        } else {

                            $elementKey = array_keys($data[$keys[$x]][$attributes[$a]]);
                            $referencia = "";


                            if (isset($data[$keys[$x]][$attributes[$a]]["referencia"])) {
                                $referencia = $data[$keys[$x]][$attributes[$a]]["referencia"];
                            } else {
                                $referencia = "El campo " . $attributes[$a];
                            }

                            if (!isset($data[$keys[$x]][$attributes[$a]]["value"]) || $data[$keys[$x]][$attributes[$a]]["value"] == "") {
                                $errors[$attributes[$a]] = $referencia . " no puede estar vacío.";
                            } else {
                                $queryfalse = false;
                                if (isset($data[$keys[$x]][$attributes[$a]]['query-sql-true'])) {
                                    if (isset($data[$keys[$x]][$attributes[$a]]['query-sql-true']['sql'])) {
                                        $query_sql = $db->executeReadQuery($data[$keys[$x]][$attributes[$a]]['query-sql-true']['sql'] . " LIMIT 1;", $data[$keys[$x]][$attributes[$a]]['query-sql-true']['data'], true, true, true);
                                    } else {
                                        $query_sql = $db->executeReadQuery($data[$keys[$x]][$attributes[$a]]['query-sql-true'] . " LIMIT 1;", true, true, true);
                                    }

                                    if (!$query_sql[0]) {
                                        $errors[$attributes[$a]] = ucfirst($referencia) . " no se encuentra disponible.";
                                        $queryfalse = true;
                                    }
                                } else if (isset($data[$keys[$x]][$attributes[$a]]['query-sql-false'])) {
                                    if (isset($data[$keys[$x]][$attributes[$a]]['query-sql-false']['sql'])) {
                                        $query_sql = $db->executeReadQuery($data[$keys[$x]][$attributes[$a]]['query-sql-false']['sql'] . " LIMIT 1;", $data[$keys[$x]][$attributes[$a]]['query-sql-false']['data'], true, true, true);
                                    } else {
                                        $query_sql = $db->executeReadQuery($data[$keys[$x]][$attributes[$a]]['query-sql-false'] . " LIMIT 1;", true, true, true);
                                    }

                                    if ($query_sql[0]) {
                                        $errors[$attributes[$a]] = ucfirst($referencia) . " ya se encuentra registrado.";
                                        $queryfalse = true;
                                    }
                                }

                                if (!$queryfalse) {
                                    $trimmedValue = trim($data[$keys[$x]][$attributes[$a]]["value"]);
                                    $lowercaseValue = strtolower($trimmedValue);
                                    if (isset($data[$keys[$x]][$attributes[$a]]["lengths"]) && is_array($data[$keys[$x]][$attributes[$a]]["lengths"])) {
                                        $valueLength = strlen(str_replace(' ', '', trim((string) $data[$keys[$x]][$attributes[$a]]["value"])));

                                        if (!in_array($valueLength, $data[$keys[$x]][$attributes[$a]]["lengths"])) {
                                            $lengths = "";
                                            if (count($data[$keys[$x]][$attributes[$a]]["lengths"]) > 1) {
                                                $lengths .= "entre ";
                                            }
                                            foreach ($data[$keys[$x]][$attributes[$a]]["lengths"] as $index => $length) {
                                                if ($index == 0) {
                                                    $lengths .= $length;
                                                } else if ($index == (count($data[$keys[$x]][$attributes[$a]]["lengths"]) - 1)) {
                                                    $lengths .= " y " . $length;
                                                } else {
                                                    $lengths .= " , " . $length;
                                                }
                                            }
                                            $errors[$attributes[$a]] = $referencia . " debe tener " . $lengths . " carácteres.";
                                        }
                                    } else {
                                        $valueLength = strlen((string) $data[$keys[$x]][$attributes[$a]]["value"]);

                                        if (is_numeric($data[$keys[$x]][$attributes[$a]]["max-length"]) && isset($data[$keys[$x]][$attributes[$a]]["max-length"]) && $valueLength > $data[$keys[$x]][$attributes[$a]]["max-length"]) {
                                            $errors[$attributes[$a]] = $referencia . " no puede tener más de " . $data[$keys[$x]][$attributes[$a]]["max-length"] . " carácteres.";
                                        } else if (isset($data[$keys[$x]][$attributes[$a]]["min-length"]) && $valueLength < $data[$keys[$x]][$attributes[$a]]["min-length"]) {
                                            $errors[$attributes[$a]] = $referencia . " no puede tener menos de " . $data[$keys[$x]][$attributes[$a]]["min-length"] . " carácteres.";
                                        } else if (is_numeric($data[$keys[$x]][$attributes[$a]]["max"]) && isset($data[$keys[$x]][$attributes[$a]]["max"]) && $valueLength > $data[$keys[$x]][$attributes[$a]]["max"]) {
                                            $errors[$attributes[$a]] = $referencia . " no puede tener más de" . $data[$keys[$x]][$attributes[$a]]["max"] . " carácteres.";
                                        } else if (isset($data[$keys[$x]][$attributes[$a]]["min"]) && $valueLength < $data[$keys[$x]][$attributes[$a]]["min"]) {
                                            $errors[$attributes[$a]] = $referencia . " no puede tener menos de " . $data[$keys[$x]][$attributes[$a]]["min"] . " carácteres.";
                                        }
                                    }
                                    if (!$errors[$attributes[$a]]) {
                                        $result[$attributes[$a]] = str_replace('  ', '', $trimmedValue);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if (count($errors) > 0) {
            $response = [
                "status" => false,
                "errors" => $errors
            ];
        } else {
            $response = [
                "status" => true,
                "data" => $result
            ];
        }
        return $response;
    }
}
