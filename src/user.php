<?php
session_start();
require 'includes/db_connect.php';

if (!isset($_SESSION['email'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password']; // Senha plana
    $email = $_SESSION['email'];
    // Atualiza a senha no banco de dados
    $stmt = $conn->prepare("UPDATE usuarios_dominio SET senha=? WHERE email=?");
    $stmt->bind_param("ss", $new_password, $email);
    $stmt->execute();

    header('Location: user.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Usuário Comum</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h1>Usuário Comum</h1>
    <h2>Trocar Senha</h2>
    <form method="POST">
        <label>Nova Senha:</label>
        <input type="password" name="new_password" required><br>
        <button type="submit">Trocar Senha</button>
    </form>

    <p><a href="sair.php">Sair</a></p>
</body>
</html>
