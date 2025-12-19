<?php
// Funções auxiliares que não exigem permissões de root
function gerarSenhaAleatoria($tamanho = 8) {
    return substr(md5(rand()), 0, $tamanho);
}
?>
