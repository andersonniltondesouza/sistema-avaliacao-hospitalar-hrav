<?php
require 'auth.php';
include 'db_connection.php';

// Verifica se o formulário foi enviado e inicia a variável de mensagem de erro
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario'], $_POST['senha'])) {
    $usuario = trim($_POST['usuario']);
    $senha = $_POST['senha'];

    // Busca o usuário e confere a senha contra o hash bcrypt armazenado
    $query = "SELECT id, usuario, senha, perfil FROM tb_usuario WHERE usuario = :usuario";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':usuario', $usuario);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($senha, $user['senha'])) {
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario'] = $user['usuario'];
        $_SESSION['perfil'] = $user['perfil'];

        header('Location: ' . ($user['perfil'] === 'ADMIN' ? 'admin_dashboard.php' : 'selecionar_dispositivo.php'));
        exit;
    } else {
        $error_message = 'Usuário ou senha incorretos. Tente novamente.';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/login_page.css">
    <script src="js/login_page.js"></script>
</head>
<body>
    <div class="container">
        <h1>Login</h1>
       <form action="login_page.php" method="POST">
            <label for="usuario">Usuário:</label>
            <input type="text" id="usuario" name="usuario" required>
            <br><br>
            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>
            <br><br>
            <button type="submit" class="botao-login">Entrar</button>
        </form>
        <!-- Exibe a mensagem de erro caso exista -->
        <?php if ($error_message): ?>
            <p class="erro"> <?= htmlspecialchars($error_message) ?> </p>
        <?php endif; ?>
    </div>
</body>
</html>
