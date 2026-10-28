<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Agendamento;
use App\Models\Profissional;
use App\Models\Servico;
use App\Models\Usuario;
use App\Services\AgendamentoService;
use App\Services\RegraNegocioException;
use DateTimeImmutable;

class AdminController extends Controller
{
    public function dashboard(): void
    {
        $this->exigirAdmin();
        $hoje = new DateTimeImmutable();
        $inicioMes = $hoje->format('Y-m-01');
        $fimMes = $hoje->format('Y-m-t');
        $ag = new Agendamento();

        $this->view('admin/dashboard', [
            'titulo'       => 'Painel administrativo',
            'hoje'         => $ag->listarTodos($hoje->format('Y-m-d')),
            'resumo'       => $ag->resumoPorStatus($inicioMes, $fimMes),
            'faturamento'  => $ag->faturamentoPrevisto($inicioMes, $fimMes),
            'clientes'     => (new Usuario())->totalClientes(),
        ]);
    }

    public function agenda(): void
    {
        $this->exigirAdmin();
        $data = $_GET['data'] ?? '';
        $data = Validator::dataValida($data) ? $data : null;
        $prof = (int) ($_GET['profissional'] ?? 0) ?: null;
        $status = in_array($_GET['status'] ?? '', array_keys(AgendamentoService::TRANSICOES), true) ? $_GET['status'] : null;

        $this->view('admin/agenda', [
            'titulo'        => 'Agenda geral',
            'agendamentos'  => (new Agendamento())->listarTodos($data, $prof, $status),
            'profissionais' => (new Profissional())->listar(),
            'filtro'        => ['data' => $data, 'profissional' => $prof, 'status' => $status],
        ]);
    }

    public function status(): void
    {
        $this->exigirAdmin();
        $servico = new AgendamentoService(new Agendamento(), new Servico(), new Profissional(), Config::get('agenda'));
        try {
            $servico->alterarStatus((int) ($_POST['id'] ?? 0), (string) ($_POST['status'] ?? ''));
            Session::flash('sucesso', 'Status atualizado para "' . status_label($_POST['status']) . '".');
        } catch (RegraNegocioException $e) {
            Session::flash('erro', $e->getMessage());
        }
        $voltar = $_POST['voltar'] ?? 'admin/agenda';
        $this->redirecionar(in_array($voltar, ['admin', 'admin/agenda'], true) ? $voltar : 'admin/agenda');
    }
}
