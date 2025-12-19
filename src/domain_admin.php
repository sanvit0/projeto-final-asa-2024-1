<?php
session_start();
require 'includes/db_connect.php';

// Verifica se o usuário é um administrador de domínio
if ($_SESSION['user_type'] !== 'domain_admin') {
    header('Location: index.php');
    exit();
}

// Obtém o domínio do administrador logado
$domain = explode('@', $_SESSION['email'])[1];
// Adicionar usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = substr(md5(rand()), 0, 8);
    // Senha aleatória
    $email = "$username@$domain";

    // Insere o usuário no banco de dados
    $stmt = $conn->prepare("INSERT INTO usuarios_dominio (email, senha, dominio_id) VALUES (?, ?, (SELECT id FROM dominios WHERE nome = ?))");
    $stmt->bind_param("sss", $email, $password, $domain);
    $stmt->execute();

    // Exibe a senha temporária
    $_SESSION['senha_temporaria'] = $password;
    header("Location: show_password.php?email=" . urlencode($email));
    exit();
}

// Remover usuário
if (isset($_GET['remove_user'])) {
    $email = $_GET['remove_user'];
    // Remove o usuário do banco de dados
    $stmt = $conn->prepare("DELETE FROM usuarios_dominio WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    header('Location: domain_admin.php');
    exit();
}

// Lista de usuários do domínio
$stmt = $conn->prepare("SELECT email FROM usuarios_dominio WHERE dominio_id = (SELECT id FROM dominios WHERE nome = ?)");
$stmt->bind_param("s", $domain);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Administrador do Domínio</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Administrador do Domínio</h1>
        <h2>Usuários do Domínio</h2>
        <ul>
            <?php while ($row = $result->fetch_assoc()): ?>
                <li>
                    <?= htmlspecialchars($row['email']) ?>
                    <a href="domain_admin.php?remove_user=<?= urlencode($row['email']) ?>">[Remover]</a>
                    <a href="change_password.php?user=<?= urlencode($row['email']) ?>">[Trocar Senha]</a>
                </li>
            <?php endwhile; ?>
        </ul>

        <h2>Adicionar Usuário</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Nome do usuário" required>
            <button type="submit" name="add_user">Adicionar Usuário</button>
        </form>

        <div class="links">
            <a href="change_password.php">Trocar Minha Senha</a>
            <a href="sair.php">Sair</a>
        </div>
    </div>
</body>
</html>
