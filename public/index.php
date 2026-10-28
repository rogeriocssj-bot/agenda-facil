<?php
declare(strict_types=1);

/**
 * AgendaFácil - Front Controller
 * Toda requisição passa por aqui e é encaminhada ao controller correto (padrão MVC).
 */

use App\Controllers\AdminController;
use App\Controllers\AgendamentoController;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\ProfissionalController;
use App\Controllers\ServicoController;
use App\Core\Config;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;

// Funciona nas duas estruturas: public/ separada (local) ou tudo em htdocs/ (hospedagem).
$raiz = is_dir(__DIR__ . '/app') ? __DIR__ : dirname(__DIR__);

// Autoload: usa o Composer se existir; senão, um autoload simples (funciona sem Composer).
if (is_file($raiz . '/vendor/autoload.php')) {
    require $raiz . '/vendor/autoload.php';
} else {
    spl_autoload_register(function (string $classe) use ($raiz): void {
        if (str_starts_with($classe, 'App\\')) {
            $arquivo = $raiz . '/app/' . str_replace('\\', '/', substr($classe, 4)) . '.php';
            if (is_file($arquivo)) {
                require $arquivo;
            }
        }
    });
}
require_once $raiz . '/app/Core/helpers.php';

Config::carregar();
date_default_timezone_set(Config::get('app.timezone'));
Session::iniciar();

// Tratamento global de erros: o usuário nunca vê mensagens técnicas.
set_exception_handler(function (Throwable $e): void {
    error_log('[AgendaFácil] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    try {
        View::render('erros/500', [
            'titulo'  => 'Erro inesperado',
            'detalhe' => Config::get('app.debug') ? $e->getMessage() : null,
        ]);
    } catch (Throwable) {
        echo 'Ocorreu um erro inesperado. Tente novamente mais tarde.';
    }
});

$router = new Router();

// Públicas
$router->get('home', HomeController::class, 'index');
$router->get('login', AuthController::class, 'formLogin');
$router->post('login', AuthController::class, 'login');
$router->get('cadastro', AuthController::class, 'formCadastro');
$router->post('cadastro', AuthController::class, 'cadastro');
$router->post('sair', AuthController::class, 'sair');

// Cliente
$router->get('agendamentos', AgendamentoController::class, 'index');
$router->get('agendamentos/novo', AgendamentoController::class, 'form');
$router->post('agendamentos/novo', AgendamentoController::class, 'salvar');
$router->post('agendamentos/cancelar', AgendamentoController::class, 'cancelar');
$router->get('api/profissionais', AgendamentoController::class, 'apiProfissionais');
$router->get('api/horarios', AgendamentoController::class, 'apiHorarios');

// Administrador
$router->get('admin', AdminController::class, 'dashboard');
$router->get('admin/agenda', AdminController::class, 'agenda');
$router->post('admin/status', AdminController::class, 'status');
$router->get('servicos', ServicoController::class, 'index');
$router->get('servicos/form', ServicoController::class, 'form');
$router->post('servicos/salvar', ServicoController::class, 'salvar');
$router->post('servicos/excluir', ServicoController::class, 'excluir');
$router->get('profissionais', ProfissionalController::class, 'index');
$router->get('profissionais/form', ProfissionalController::class, 'form');
$router->post('profissionais/salvar', ProfissionalController::class, 'salvar');
$router->post('profissionais/excluir', ProfissionalController::class, 'excluir');

$router->despachar($_SERVER['REQUEST_METHOD'] ?? 'GET', (string) ($_GET['r'] ?? 'home'));
