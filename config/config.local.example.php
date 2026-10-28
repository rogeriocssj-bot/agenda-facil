<?php
// Copie este arquivo para config.local.php e ajuste com os dados do seu MySQL.
// O arquivo config.local.php NÃO deve ir para o GitHub (está no .gitignore).
return [
    'app' => [
        'url'   => 'http://localhost:8000',
        'debug' => true,
    ],
    'db' => [
        'dsn'     => 'mysql:host=localhost;dbname=agendafacil;charset=utf8mb4',
        'usuario' => 'root',
        'senha'   => '',
    ],
];
