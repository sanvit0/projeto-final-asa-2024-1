<?php
function criarDominio($domain) {
    global $conn;

    // Chama o sb.sc para criar o domínio
    $output = shell_exec("/var/www/projeto/exeroot add $domain 2>&1");
    if (strpos($output, "Domínio adicionado com sucesso") === false) {
        die("Erro ao criar domínio: $output");
    }

    return true;
}

function removerDominio($domain) {
    global $conn;
    // Chama o sb.sc para remover o domínio
    $output = shell_exec("/var/www/projeto/exeroot remove $domain 2>&1");
    if (strpos($output, "Domínio removido com sucesso") === false) {
        die("Erro ao remover domínio: $output");
    }

    return true;
}
?>
