<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Servico;

class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('home', [
            'titulo'   => 'Agende seu horário',
            'servicos' => (new Servico())->listar(true),
        ]);
    }
}
