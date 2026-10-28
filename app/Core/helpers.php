<?php
declare(strict_types=1);

use App\Core\Config;
use App\Core\Session;

/** Escapa texto para HTML (evita XSS). */
function e(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

/** Monta a URL de uma rota: url('agendamentos') => index.php?r=agendamentos */
function url(string $rota = 'home', array $params = []): string
{
    $query = array_merge(['r' => $rota], $params);
    return 'index.php?' . http_build_query($query);
}

function asset(string $caminho): string
{
    return 'assets/' . ltrim($caminho, '/');
}

/** Valor antigo de um campo (após erro de validação). */
function old(string $campo, mixed $padrao = ''): string
{
    $old = Session::get('_old', []);
    return e($old[$campo] ?? $padrao);
}

/** Mensagem de erro de um campo. */
function erro(string $campo): string
{
    $erros = Session::get('_erros', []);
    return isset($erros[$campo]) ? '<small class="erro-campo">' . e($erros[$campo]) . '</small>' : '';
}

function limpar_entrada(): void
{
    Session::remover('_old');
    Session::remover('_erros');
}

function data_br(string $data): string
{
    $d = DateTime::createFromFormat('Y-m-d', $data);
    return $d ? $d->format('d/m/Y') : $data;
}

function hora_curta(string $hora): string
{
    return substr($hora, 0, 5);
}

function moeda(float|string $valor): string
{
    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
}

function status_label(string $status): string
{
    return [
        'agendado'   => 'Agendado',
        'confirmado' => 'Confirmado',
        'concluido'  => 'Concluído',
        'cancelado'  => 'Cancelado',
    ][$status] ?? $status;
}

function app_nome(): string
{
    return (string) Config::get('app.nome', 'AgendaFácil');
}
