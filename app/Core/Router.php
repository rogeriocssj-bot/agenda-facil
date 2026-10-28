<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Roteador simples: as rotas são acessadas por ?r=controle/acao
 * (funciona em qualquer hospedagem compartilhada, sem precisar de .htaccess).
 */
final class Router
{
    /** @var array<string, array{0:string,1:string,2:string}> */
    private array $rotas = [];

    public function get(string $rota, string $controller, string $acao): void
    {
        $this->rotas['GET ' . $rota] = ['GET', $controller, $acao];
    }

    public function post(string $rota, string $controller, string $acao): void
    {
        $this->rotas['POST ' . $rota] = ['POST', $controller, $acao];
    }

    public function despachar(string $metodo, string $rota): void
    {
        $rota = trim($rota, '/') ?: 'home';
        $chave = strtoupper($metodo) . ' ' . $rota;

        if (!isset($this->rotas[$chave])) {
            http_response_code(404);
            View::render('erros/404', ['titulo' => 'Página não encontrada']);
            return;
        }

        [, $classe, $acao] = $this->rotas[$chave];

        if ($metodo === 'POST' && !Csrf::validar($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            Session::flash('erro', 'Sua sessão expirou. Tente novamente.');
            header('Location: ' . url(isset($this->rotas['GET ' . $rota]) ? $rota : 'home'));
            return;
        }

        (new $classe())->$acao();
    }
}
