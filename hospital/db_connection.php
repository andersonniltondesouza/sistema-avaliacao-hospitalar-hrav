<?php
// As credenciais ficam fora do código: em config.php (ignorado pelo Git)
// ou em variáveis de ambiente.
$config = file_exists(__DIR__ . '/config.php') ? require __DIR__ . '/config.php' : [];

$host     = getenv('DB_HOST')     ?: ($config['host'] ?? 'localhost');
$port     = getenv('DB_PORT')     ?: ($config['port'] ?? '5432');
$dbname   = getenv('DB_NAME')     ?: ($config['dbname'] ?? 'hospital');
$user     = getenv('DB_USER')     ?: ($config['user'] ?? 'postgres');
$password = getenv('DB_PASSWORD') ?: ($config['password'] ?? '');

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // O detalhe do erro pode expor usuário e servidor; vai só para o log.
    error_log('Erro na conexão com o banco: ' . $e->getMessage());
    http_response_code(500);
    exit('Não foi possível conectar ao banco de dados.');
}
