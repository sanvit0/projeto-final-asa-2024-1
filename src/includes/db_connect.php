<?php
$host = "192.168.102.100";
$user = "CONTAINER022";
$password = "SUA_SENHA_AQUI";
$database = "BD022";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}
?>
