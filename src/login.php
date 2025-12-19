<?php
session_start();
require 'includes/db_connect.php';

if (!isset($_POST['email']) || !isset($_POST['password'])) {
    header("Location: index.php?erro=2");
    exit();
}

$email = $_POST['email'];
$password = $_POST['password'];
// Verifica se é um administrador geral
$stmt = $conn->prepare("SELECT * FROM administradores WHERE email = ? AND senha = ?");
$stmt->bind_param("ss", $email, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['user_type'] = 'admin';
    $_SESSION['email'] = $email;
    header("Location: admin.php");
    exit();
}

// Verifica se é um administrador de domínio (root@dominio)
$stmt = $conn->prepare("SELECT * FROM usuarios_dominio WHERE email = ? AND senha = ? AND email LIKE 'root@%'");
$stmt->bind_param("ss", $email, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['user_type'] = 'domain_admin';
    $_SESSION['email'] = $email;
    header("Location: domain_admin.php");
    exit();
}

// Verifica se é um usuário comum
$stmt = $conn->prepare("SELECT * FROM usuarios_dominio WHERE email = ? AND senha = ?");
$stmt->bind_param("ss", $email, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['user_type'] = 'user';
    $_SESSION['email'] = $email;
    header("Location: user.php");
    exit();
}

// Se chegou aqui, login falhou
header("Location: index.php?erro=1");
exit();
?>
