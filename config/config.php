<?php
/**
 * Configurações da aplicação.
 * Os valores podem ser sobrescritos por variáveis de ambiente (ex.: na hospedagem).
 * Copie config/config.local.example.php para config/config.local.php para ajustes locais.
 */

$config = [
    'app' => [
        'nome'     => 'AgendaFácil',
        'url'      => getenv('APP_URL') ?: 'http://localhost:8000',
        'timezone' => 'America/Sao_Paulo',
        'debug'    => (getenv('APP_DEBUG') ?: '1') === '1',
    ],
    'db' => [
        'dsn'     => getenv('DB_DSN') ?: 'mysql:host=localhost;dbname=agendafacil;charset=utf8mb4',
        'usuario' => getenv('DB_USER') ?: 'root',
        'senha'   => getenv('DB_PASS') ?: '',
    ],
    'agenda' => [
        'abertura'                => '08:00',
        'fechamento'              => '18:00',
        'dias_funcionamento'      => [1, 2, 3, 4, 5, 6], // 1 = segunda ... 6 = sábado (0 = domingo fechado)
        'antecedencia_cancelar_h' => 2,                   // cliente cancela até 2h antes
        'intervalo_slots_min'     => 30,
    ],
];

$local = __DIR__ . '/config.local.php';
if (is_file($local)) {
    $config = array_replace_recursive($config, require $local);
}

return $config;
