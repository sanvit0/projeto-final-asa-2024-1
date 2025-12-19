<?php
session_start();

if (!isset($_GET['email'])) {
    die("E-mail não especificado.");
}

$email = $_GET['email'];
if (!isset($_SESSION['senha_temporaria'])) {
    die("Senha temporária não encontrada.");
}

$senha_temporaria = $_SESSION['senha_temporaria'];
unset($_SESSION['senha_temporaria']);
// Verifica o tipo de usuário para redirecionar corretamente
$redirect_url = ($_SESSION['user_type'] === 'admin') ? 'admin.php' : 'domain_admin.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Senha Temporária</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Senha Temporária</h1>
        <p>O usuário <strong><?= htmlspecialchars($email) ?></strong> foi criado com sucesso.</p>
        <p>A senha temporária é: <strong><?= htmlspecialchars($senha_temporaria) ?></strong></p>
        <p>
            <a href="<?= $redirect_url ?>">Voltar para o painel de administração de domínio</a> |
            <a href="index.php">Página inicial</a>
        </p>
    </div>
</body>
</html>
