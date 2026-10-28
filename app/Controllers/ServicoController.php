<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Servico;

class ServicoController extends Controller
{
    public function index(): void
    {
        $this->exigirAdmin();
        $this->view('servicos/index', ['titulo' => 'Serviços', 'servicos' => (new Servico())->listar()]);
    }

    public function form(): void
    {
        $this->exigirAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        $servico = $id ? (new Servico())->buscar($id) : null;
        if ($id && !$servico) {
            Session::flash('erro', 'Serviço não encontrado.');
            $this->redirecionar('servicos');
        }
        $this->view('servicos/form', [
            'titulo'  => $servico ? 'Editar serviço' : 'Novo serviço',
            'servico' => $servico ?? ['id' => 0, 'nome' => '', 'descricao' => '', 'duracao_min' => 30, 'preco' => '', 'ativo' => 1],
        ]);
    }

    public function salvar(): void
    {
        $this->exigirAdmin();
        $id = (int) ($_POST['id'] ?? 0) ?: null;
        $v = new Validator($_POST);
        $v->validar([
            'nome'        => 'obrigatorio|min:3|max:100',
            'descricao'   => 'max:255',
            'duracao_min' => 'obrigatorio|inteiro',
            'preco'       => 'obrigatorio|numero',
        ], ['nome' => 'Nome', 'descricao' => 'Descrição', 'duracao_min' => 'Duração', 'preco' => 'Preço']);

        $erros = $v->erros();
        $model = new Servico();
        $duracao = (int) ($_POST['duracao_min'] ?? 0);
        if (!isset($erros['duracao_min']) && ($duracao < 10 || $duracao > 480)) {
            $erros['duracao_min'] = 'A duração deve ficar entre 10 e 480 minutos.';
        }
        if (!isset($erros['nome']) && $model->nomeExiste($_POST['nome'], $id)) {
            $erros['nome'] = 'Já existe um serviço com este nome.';
        }

        if ($erros) {
            $this->guardarEntrada($_POST, $erros);
            $this->redirecionar('servicos/form', $id ? ['id' => $id] : []);
        }

        $model->salvar($_POST, $id);
        limpar_entrada();
        Session::flash('sucesso', $id ? 'Serviço atualizado.' : 'Serviço cadastrado.');
        $this->redirecionar('servicos');
    }

    public function excluir(): void
    {
        $this->exigirAdmin();
        $id = (int) ($_POST['id'] ?? 0);
        $model = new Servico();
        if ($model->possuiAgendamentos($id)) {
            Session::flash('erro', 'Este serviço possui agendamentos e não pode ser excluído. Desative-o em "Editar".');
        } elseif ($model->excluir($id)) {
            Session::flash('sucesso', 'Serviço excluído.');
        } else {
            Session::flash('erro', 'Serviço não encontrado.');
        }
        $this->redirecionar('servicos');
    }
}
