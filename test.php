<?php

// Accesos a la API
$tokenUrl = "http://45.236.128.138:8085/api/auth/login/users";
$nombreUsuario = "api_corral";
$claveUsuario = "api_corral";

// Conexión a la base de datos
$servername = "localhost";
$username = "invers20_casino";
$password = "0g[rfS:61Dh6IQ";
$dbname = "invers20_datacasino";

// Nombre de la tabla de la base de datos
$tableName = "datosdiarioscasino";

// Función de actualización automática
function updateDatabase()
{
    // Obtener el token de autenticación de la API
    $tokenResponse = postData($tokenUrl, array(
        "nombre_usuario" => $nombreUsuario,
        "password" => $claveUsuario
    ));

    // Decodificar la respuesta JSON y obtener el token
    $tokenData = json_decode($tokenResponse, true);
    $token = $tokenData["token"];

    // Obtener los datos de consumos de la API
    $consultaUrl = "http://45.236.128.138:8085/usuarios/consumo_casino";
    $consultaData = array(
        "rut_emisor" => "77067073-k",
        "X-Auth-Token" => $token,
        "fecha_inicio" => "2023-06-25",
        "fecha_fin" => "2023-06-29",
        "nombre_usuario" => "api_corral",
        "rut_cliente" => "" 
    );
    $consultaResponse = postData($consultaUrl, $consultaData);

    // Decodificar la respuesta JSON y obtener los datos de consumos
    $consultaData = json_decode($consultaResponse, true);

    // Actualizar la base de datos con los datos de consumos
    $conexion = new mysqli($servername, $username, $password, $dbname);

    // Si la conexión se realizó correctamente
    if ($conexion->connect_error) {
        die("Error al conectarse a la base de datos: " . $conexion->connect_error);
    }

    // Vaciar la tabla de la base de datos
    $conexion->query("TRUNCATE TABLE $tableName");

    // Insertar los datos de consumos en la tabla de la base de datos
    foreach ($consultaData["consumos"] as $consumo) {
        $conexion->query("INSERT INTO $tableName (rut_emisor, fecha, hora, valor) VALUES ('$consumo[rut_emisor]', '$consumo[fecha]', '$consumo[hora]', '$consumo[valor]')");
    }

    // Cerrar la conexión a la base de datos
    $conexion->close();
}

// Llamar a la función de actualización automática
updateDatabase();

// Función para realizar solicitudes POST
function postData($url, $data) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

?>
