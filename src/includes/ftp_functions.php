<?php
function criarUsuarioFTP($email, $senha, $dominio, $login) {
    global $conn;
    // Insere no banco de dados
    $stmt = $conn->prepare("INSERT INTO ftpusers (nome, login, senha, uid, gid, dir, shell, ativo, email) VALUES (?, ?, ?, 48, 48, ?, '/sbin/nologin', 's', ?)");
    $nome = "Administrador do Domínio";
    $dir = "/var/www/projeto/domains/$dominio/public_html";
    $stmt->bind_param("sssss", $nome, $login, $senha, $dir, $email);
    if (!$stmt->execute()) {
        error_log("Erro ao criar usuário FTP: " . $stmt->error);
        return false;
    }

    return true;
}

function removerUsuarioFTP($email) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM ftpusers WHERE email = ?");
    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        error_log("Erro ao remover usuário FTP: " . $stmt->error);
        return false;
    }

    return true;
}
?>
