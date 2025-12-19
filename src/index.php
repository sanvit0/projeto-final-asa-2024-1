<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h1>Login</h1>
    <?php
    if (isset($_GET['erro'])) {
        if ($_GET['erro'] == 1) {
            echo '<p style="color: red;">E-mail ou senha incorretos.</p>';
        } elseif ($_GET['erro'] == 2) {
            echo '<p style="color: red;">Erro na passagem de parâmetros.</p>';
        }
    }
    ?>
    <form action="login.php" method="POST">
        <label>E-mail:</label>
        <input type="email" name="email" required><br>
        <label>Senha:</label>
        <input type="password" name="password" required><br>
        <button type="submit">Entrar</button>
    </form>
</body>
</html>
