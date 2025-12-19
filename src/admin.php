<?php
session_start();
require 'includes/db_connect.php';
require 'includes/domain_functions.php';
require 'includes/ftp_functions.php';

// Verifica se o usuário é administrador
if ($_SESSION['user_type'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Adicionar domínio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_domain'])) {
    $domain = $_POST['domain'];
    // Domínio livre (sem .c22.ifrn.local)
    
    // Verifica se o domínio já existe
    $stmt = $conn->prepare("SELECT domain FROM domains WHERE domain = ?");
    $stmt->bind_param("s", $domain);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        header('Location: admin.php?erro=1');
        exit();
    }
    
    // Insere o domínio no banco de dados (tabela domains)
    $stmt = $conn->prepare("INSERT INTO domains (domain) VALUES (?)");
    $stmt->bind_param("s", $domain);
    $stmt->execute();
    
    // Insere o domínio no banco de dados (tabela dominios)
    $stmt = $conn->prepare("INSERT INTO dominios (nome) VALUES (?)");
    $stmt->bind_param("s", $domain);
    $stmt->execute();
    $dominio_id = $conn->insert_id; // Obtém o ID do domínio inserido

    // Cria a estrutura do domínio usando o exeroot
    $output = shell_exec("/var/www/projeto/exeroot add $domain 2>&1");
    error_log("Saída do exeroot ao criar domínio: $output");

    // Cria o administrador do domínio (root@dominio)
    $admin_email = "root@$domain";
    $admin_password = substr(md5(rand()), 0, 8); // Senha aleatória
    $stmt = $conn->prepare("INSERT INTO usuarios_dominio (email, senha, dominio_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $admin_email, $admin_password, $dominio_id);
    $stmt->execute();

    // Cria o usuário FTP com login único
    $login = 'root' . $dominio_id;
    if (!criarUsuarioFTP($admin_email, $admin_password, $domain, $login)) {
        error_log("Falha ao criar usuário FTP para $admin_email");
        header('Location: admin.php?erro=ftp');
        exit();
    }

    // Exibe a senha temporária
    $_SESSION['senha_temporaria'] = $admin_password;
    header("Location: show_password.php?email=" . urlencode($admin_email));
    exit();
}

// Remover domínio
if (isset($_GET['remove_domain'])) {
    $domain = $_GET['remove_domain'];
    // Remove o domínio do banco de dados (tabela domains)
    $stmt = $conn->prepare("DELETE FROM domains WHERE domain = ?");
    $stmt->bind_param("s", $domain);
    $stmt->execute();
    
    // Remove o domínio do banco de dados (tabela dominios)
    $stmt = $conn->prepare("DELETE FROM dominios WHERE nome = ?");
    $stmt->bind_param("s", $domain);
    $stmt->execute();
    
    // Remove a estrutura do domínio usando o exeroot
    $output = shell_exec("/var/www/projeto/exeroot remove $domain 2>&1");
    error_log("Saída do exeroot ao remover domínio: $output");

    // Remove o administrador do domínio
    $admin_email = "root@$domain";
    $stmt = $conn->prepare("DELETE FROM usuarios_dominio WHERE email = ?");
    $stmt->bind_param("s", $admin_email);
    $stmt->execute();
    // Remove o usuário FTP
    removerUsuarioFTP($admin_email);
    
    header('Location: admin.php');
    exit();
}

// Lista de domínios
$domains = $conn->query("SELECT domain FROM domains LIMIT 100");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Administrador Geral</h1>
        <h2>Domínios Configurados</h2>
        <ul>
            <?php while ($row = $domains->fetch_assoc()): ?>
                <li>
                    <?= htmlspecialchars($row['domain']) ?>
                    <a href="admin.php?remove_domain=<?= urlencode($row['domain']) ?>">[Remover]</a>
                </li>
            <?php endwhile; ?>
        </ul>

        <h2>Adicionar Domínio</h2>
        <form method="POST">
            <input type="text" name="domain" placeholder="Nome do domínio (ex: meudominio.com)" required>
            <button type="submit" name="add_domain">Adicionar Domínio</button>
        </form>

        <div class="links">
            <a href="change_password.php">Trocar Senha</a>
            <a href="sair.php">Sair</a>
        </div>
    </div>
</body>
</html>
