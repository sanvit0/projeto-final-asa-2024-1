<?php
// Não inicie a sessão aqui
function authenticateUser($email, $password) {
    global $conn;
    $sql = "SELECT * FROM users WHERE email='$email' AND senha='$password'";
    $result = $conn->query($sql);
    return ($result->num_rows > 0);
}

function isAdmin($email) {
    return (strpos($email, 'admin@') !== false);
}

function isDomainAdmin($email) {
    return (strpos($email, 'root@') !== false);
}

function getDomainFromEmail($email) {
    $parts = explode('@', $email);
    return isset($parts[1]) ? $parts[1] : '';
}
?>
