<?php
session_start();
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método de requisição inválido.');
}

// Verifica se o dispositivo e o setor foram selecionados
if (!isset($_SESSION['dispositivo_id'], $_SESSION['setor_id'])) {
    exit('Erro: Nenhum dispositivo ou setor selecionado.');
}

$notas = $_POST['notas'] ?? [];
if (!is_array($notas) || empty($notas)) {
    exit('Erro: nenhuma resposta recebida.');
}

// Aceita apenas notas inteiras de 0 a 10
foreach ($notas as $nota) {
    if (filter_var($nota, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 10]]) === false) {
        exit('Erro: todas as perguntas precisam de uma nota entre 0 e 10.');
    }
}

try {
    $conn->beginTransaction();

    $query = "INSERT INTO respostas (id_pergunta, nota, observacoes, id_dispositivo, id_setor, data_hora)
              VALUES (:id_pergunta, :nota, :observacoes, :id_dispositivo, :id_setor, NOW())";
    $stmt = $conn->prepare($query);

    foreach ($notas as $id_pergunta => $nota) {
        $observacoes = trim($_POST['observacoes'][$id_pergunta] ?? '');

        $stmt->execute([
            ':id_pergunta' => $id_pergunta,
            ':nota' => $nota,
            ':observacoes' => $observacoes !== '' ? $observacoes : null,
            ':id_dispositivo' => $_SESSION['dispositivo_id'],
            ':id_setor' => $_SESSION['setor_id'],
        ]);
    }

    $conn->commit();

    header('Location: sucesso.html');
    exit;
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log('Erro ao registrar a avaliação: ' . $e->getMessage());
    exit('Erro ao registrar a avaliação. Tente novamente.');
}
