<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    /** Renderiza uma view dentro do layout principal. */
    public static function render(string $view, array $dados = []): void
    {
        $arquivo = dirname(__DIR__) . '/Views/' . $view . '.php';
        if (!is_file($arquivo)) {
            throw new \RuntimeException("View não encontrada: {$view}");
        }
        extract($dados, EXTR_SKIP);
        ob_start();
        require $arquivo;
        $conteudo = ob_get_clean();
        $flash = Session::consumirFlash();
        $usuario = Auth::usuario();
        require dirname(__DIR__) . '/Views/layout/main.php';
    }
}
