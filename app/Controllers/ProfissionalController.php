<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Profissional;
use App\Models\Servico;

class ProfissionalController extends Controller
{
    public function index(): void
    {
        $this->exigirAdmin();
        $servicos = array_column((new Servico())->listar(), 'nome', 'id');
        $this->view('profissionais/index', [
            'titulo'        => 'Profissionais',
            'profissionais' => (new Profissional())->listar(),
            'servicos'      => $servicos,
        ]);
    }

    public function form(): void
    {
        $this->exigirAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        $model = new Profissional();
        $prof = $id ? $model->buscar($id) : null;
        if ($id && !$prof) {
            Session::flash('erro', 'Profissional não encontrado.');
            $this->redirecionar('profissionais');
        }
        $this->view('profissionais/form', [
            'titulo'       => $prof ? 'Editar profissional' : 'Novo profissional',
            'profissional' => $prof ?? ['id' => 0, 'nome' => '', 'especialidade' => '', 'ativo' => 1],
            'selecionados' => $prof ? $model->servicosIds($id) : [],
            'servicos'     => (new Servico())->listar(),
        ]);
    }

    public function salvar(): void
    {
        $this->exigirAdmin();
        $id = (int) ($_POST['id'] ?? 0) ?: null;
        $servicos = array_filter((array) ($_POST['servicos'] ?? []), 'ctype_digit');
        $v = new Validator($_POST);
        $v->validar(['nome' => 'obrigatorio|min:3|max:100', 'especialidade' => 'max:100'],
            ['nome' => 'Nome', 'especialidade' => 'Especialidade']);
        $erros = $v->erros();
        if (!$servicos) {
            $erros['servicos'] = 'Selecione pelo menos um serviço.';
        }
        if ($erros) {
            $this->guardarEntrada($_POST, $erros);
            $this->redirecionar('profissionais/form', $id ? ['id' => $id] : []);
        }

        (new Profissional())->salvar($_POST, $servicos, $id);
        limpar_entrada();
        Session::flash('sucesso', $id ? 'Profissional atualizado.' : 'Profissional cadastrado.');
        $this->redirecionar('profissionais');
    }

    public function excluir(): void
    {
        $this->exigirAdmin();
        $id = (int) ($_POST['id'] ?? 0);
        $model = new Profissional();
        if ($model->possuiAgendamentos($id)) {
            Session::flash('erro', 'Este profissional possui agendamentos e não pode ser excluído. Desative-o em "Editar".');
        } elseif ($model->excluir($id)) {
            Session::flash('sucesso', 'Profissional excluído.');
        } else {
            Session::flash('erro', 'Profissional não encontrado.');
        }
        $this->redirecionar('profissionais');
    }
}
