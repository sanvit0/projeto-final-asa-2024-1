<?php
session_start();
require 'includes/db_connect.php';

if (!isset($_SESSION['email'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'];
    // Senha plana
    $email = isset($_GET['user']) ? $_GET['user'] : $_SESSION['email'];
    // Atualiza a senha no banco de dados (usuarios_dominio)
    $stmt = $conn->prepare("UPDATE usuarios_dominio SET senha=? WHERE email=?");
    $stmt->bind_param("ss", $new_password, $email);
    $stmt->execute();

    // Atualiza a senha no banco de dados do FTP (ftpusers)
    $stmt = $conn->prepare("UPDATE ftpusers SET senha=? WHERE email=?");
    $stmt->bind_param("ss", $new_password, $email);
    $stmt->execute();

    // Redireciona conforme o tipo de usuário
    if ($_SESSION['user_type'] == 'admin') {
        header('Location: admin.php');
    } elseif ($_SESSION['user_type'] == 'domain_admin') {
        header('Location: domain_admin.php');
    } else {
        header('Location: user.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Trocar Senha</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h1>Trocar Senha</h1>
    <form method="POST">
        <label>Nova Senha:</label>
        <input type="password" name="new_password" required><br>
        <button type="submit">Trocar Senha</button>
    </form>
</body>
</html>
