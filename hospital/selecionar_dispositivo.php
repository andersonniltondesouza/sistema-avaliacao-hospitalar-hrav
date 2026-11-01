<?php
require 'auth.php';
exigir_login();
include 'db_connection.php';

// O processamento vem antes de qualquer HTML: header() só funciona se nada
// tiver sido enviado ao navegador ainda.
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dispositivo'])) {
    $dispositivo_id = $_POST['dispositivo'];

    // Obtém o setor ao qual o dispositivo (ativo) está vinculado
    $query_setor = "SELECT id_setor FROM dispositivos WHERE id = :dispositivo_id AND status = 'ATIVO'";
    $stmt_setor = $conn->prepare($query_setor);
    $stmt_setor->bindParam(':dispositivo_id', $dispositivo_id);
    $stmt_setor->execute();
    $setor_id = $stmt_setor->fetchColumn();

    if ($setor_id) {
        $_SESSION['dispositivo_id'] = $dispositivo_id;
        $_SESSION['setor_id'] = $setor_id;
        header('Location: index.php');
        exit;
    }
    $erro = 'Dispositivo inválido ou sem setor vinculado. Verifique o cadastro do dispositivo.';
}

// Consulta para obter os dispositivos ativos
$query = "SELECT id, nome, id_setor FROM dispositivos WHERE status = 'ATIVO' ORDER BY nome";
$stmt = $conn->prepare($query);
$stmt->execute();
$dispositivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selecionar Dispositivo</title>
    <link rel="stylesheet" href="css/selecionar_dispositivo.css">
    <script src="js/selecionar_dispositivo.js"></script>
</head>
<body>
    <button class="voltar-button" onclick="location.href='logout.php'">
    <i class="arrow-left">&#8592;</i>
    </button>
    <div class="container">
        <h1>Selecionar Dispositivo</h1>
        <form action="selecionar_dispositivo.php" method="POST">
            <label for="dispositivo">Dispositivo:</label>
            <select id="dispositivo" name="dispositivo" required>
                <option value="">Selecione um dispositivo</option>
                <?php foreach ($dispositivos as $dispositivo): ?>
                    <option value="<?= htmlspecialchars($dispositivo['id']) ?>">
                        <?= htmlspecialchars($dispositivo['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>
            <button type="submit" class="botao-confirmar">Confirmar</button>
        </form>

        <?php if ($erro): ?>
            <p class="erro"><?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
