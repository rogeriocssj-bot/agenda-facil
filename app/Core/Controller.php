<?php
declare(strict_types=1);

namespace App\Core;

/** Classe base dos controllers (camada C do MVC). */
abstract class Controller
{
    protected function view(string $view, array $dados = []): void
    {
        View::render($view, $dados);
    }

    protected function redirecionar(string $rota, array $params = []): never
    {
        header('Location: ' . url($rota, $params));
        exit;
    }

    protected function exigirLogin(): void
    {
        if (!Auth::logado()) {
            // Correção BUG-04: lembra a página pedida para voltar a ela após o login.
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
                Session::set('voltar_para', $_GET);
            }
            Session::flash('erro', 'Faça login para continuar.');
            $this->redirecionar('login');
        }
    }

    protected function exigirAdmin(): void
    {
        $this->exigirLogin();
        if (!Auth::admin()) {
            http_response_code(403);
            View::render('erros/403', ['titulo' => 'Acesso negado']);
            exit;
        }
    }

    /** Guarda dados digitados para reexibir o formulário após erro. */
    protected function guardarEntrada(array $dados, array $erros): void
    {
        unset($dados['senha'], $dados['senha_confirmacao'], $dados['_csrf']);
        Session::set('_old', $dados);
        Session::set('_erros', $erros);
    }
}
