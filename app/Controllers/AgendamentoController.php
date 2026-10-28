<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Config;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Agendamento;
use App\Models\Profissional;
use App\Models\Servico;
use App\Services\AgendamentoService;
use App\Services\RegraNegocioException;
use DateTimeImmutable;

class AgendamentoController extends Controller
{
    private function servico(): AgendamentoService
    {
        return new AgendamentoService(new Agendamento(), new Servico(), new Profissional(), Config::get('agenda'));
    }

    /** Meus agendamentos (cliente). */
    public function index(): void
    {
        $this->exigirLogin();
        $this->view('agendamentos/index', [
            'titulo'       => 'Meus agendamentos',
            'agendamentos' => (new Agendamento())->listarDoCliente(Auth::id()),
            'agora'        => new DateTimeImmutable(),
            'horasCancel'  => (int) Config::get('agenda.antecedencia_cancelar_h'),
        ]);
    }

    public function form(): void
    {
        $this->exigirLogin();
        $this->view('agendamentos/novo', [
            'titulo'   => 'Novo agendamento',
            'servicos' => (new Servico())->listar(true),
            'hoje'     => (new DateTimeImmutable())->format('Y-m-d'),
            'maxData'  => (new DateTimeImmutable('+60 days'))->format('Y-m-d'),
            'servicoSelecionado' => (int) ($_GET['servico'] ?? 0),
        ]);
    }

    public function salvar(): void
    {
        $this->exigirLogin();
        $v = new Validator($_POST);
        $ok = $v->validar([
            'servico_id'      => 'obrigatorio|inteiro',
            'profissional_id' => 'obrigatorio|inteiro',
            'data'            => 'obrigatorio|data',
            'hora'            => 'obrigatorio|hora',
            'observacao'      => 'max:255',
        ], [
            'servico_id' => 'Serviço', 'profissional_id' => 'Profissional',
            'data' => 'Data', 'hora' => 'Horário', 'observacao' => 'Observação',
        ]);

        if (!$ok) {
            $this->guardarEntrada($_POST, $v->erros());
            Session::flash('erro', 'Verifique os campos destacados.');
            $this->redirecionar('agendamentos/novo');
        }

        try {
            $id = $this->servico()->agendar(
                Auth::id(),
                (int) $_POST['servico_id'],
                (int) $_POST['profissional_id'],
                $_POST['data'],
                $_POST['hora'],
                $_POST['observacao'] ?? null,
                new DateTimeImmutable(),
            );
        } catch (RegraNegocioException $e) {
            $this->guardarEntrada($_POST, []);
            Session::flash('erro', $e->getMessage());
            $this->redirecionar('agendamentos/novo');
        }

        limpar_entrada();
        $ag = (new Agendamento())->buscarCompleto($id);
        Session::flash('sucesso', sprintf(
            'Agendamento confirmado: %s com %s em %s às %s.',
            $ag['servico_nome'], $ag['profissional_nome'], data_br($ag['data']), hora_curta($ag['hora_inicio'])
        ));
        $this->redirecionar('agendamentos');
    }

    public function cancelar(): void
    {
        $this->exigirLogin();
        try {
            $this->servico()->cancelarPeloCliente((int) ($_POST['id'] ?? 0), Auth::id(), new DateTimeImmutable());
            Session::flash('sucesso', 'Agendamento cancelado.');
        } catch (RegraNegocioException $e) {
            Session::flash('erro', $e->getMessage());
        }
        $this->redirecionar('agendamentos');
    }

    // ------------------- Endpoints JSON usados pelo JavaScript -------------------

    public function apiProfissionais(): void
    {
        $this->exigirLoginJson();
        $this->json((new Profissional())->listarPorServico((int) ($_GET['servico'] ?? 0)));
    }

    public function apiHorarios(): void
    {
        $this->exigirLoginJson();
        $data = (string) ($_GET['data'] ?? '');
        if (!Validator::dataValida($data)) {
            $this->json(['erro' => 'Data inválida.'], 422);
        }
        $slots = $this->servico()->horariosDisponiveis(
            (int) ($_GET['profissional'] ?? 0),
            (int) ($_GET['servico'] ?? 0),
            $data,
            new DateTimeImmutable(),
        );
        $this->json(['horarios' => $slots]);
    }

    private function exigirLoginJson(): void
    {
        if (!Auth::logado()) {
            $this->json(['erro' => 'Não autenticado.'], 401);
        }
    }

    private function json(mixed $dados, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
