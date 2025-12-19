<?php
require 'includes/db_connect.php';
// Testa conexão
if (!$conn) {
    die("Erro ao conectar ao banco de dados: " . $conn->connect_error);
}
echo "Conexão bem-sucedida!<br>";

// Testa consulta na tabela `administradores`
$result = $conn->query("SELECT * FROM administradores LIMIT 1");
if (!$result) {
    die("Erro na consulta SQL: " . $conn->error);
}
$row = $result->fetch_assoc();
echo "Teste administradores: ";
var_dump($row);

// Testa consulta na tabela `usuarios_dominio`
$result = $conn->query("SELECT * FROM usuarios_dominio LIMIT 1");
if (!$result) {
    die("Erro na consulta SQL: " . $conn->error);
}
$row = $result->fetch_assoc();
echo "<br>Teste usuarios_dominio: ";
var_dump($row);

// Testa consulta na tabela `users`
$result = $conn->query("SELECT * FROM users LIMIT 1");
if (!$result) {
    die("Erro na consulta SQL: " . $conn->error);
}
$row = $result->fetch_assoc();
echo "<br>Teste users: ";
var_dump($row);
?>
