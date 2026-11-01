<?php
// Controle de acesso por sessão. Inclua no topo das páginas, antes de qualquer saída.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function usuario_logado(): bool
{
    return isset($_SESSION['usuario_id']);
}

function exigir_login(): void
{
    if (!usuario_logado()) {
        header('Location: login_page.php');
        exit;
    }
}

function exigir_admin(): void
{
    exigir_login();
    if (($_SESSION['perfil'] ?? '') !== 'ADMIN') {
        header('Location: selecionar_dispositivo.php');
        exit;
    }
}
