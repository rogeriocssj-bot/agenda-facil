<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Usuario;

class AuthController extends Controller
{
    public function formLogin(): void
    {
        if (Auth::logado()) {
            $this->redirecionar(Auth::admin() ? 'admin' : 'agendamentos');
        }
        $this->view('auth/login', ['titulo' => 'Entrar']);
    }

    public function login(): void
    {
        $v = new Validator($_POST);
        if (!$v->validar(['email' => 'obrigatorio|email', 'senha' => 'obrigatorio'], ['email' => 'E-mail', 'senha' => 'Senha'])) {
            $this->guardarEntrada($_POST, $v->erros());
            $this->redirecionar('login');
        }

        $usuario = (new Usuario())->autenticar($_POST['email'], $_POST['senha']);
        if (!$usuario) {
            $this->guardarEntrada($_POST, []);
            Session::flash('erro', 'E-mail ou senha incorretos.');
            $this->redirecionar('login');
        }

        limpar_entrada();
        Auth::entrar($usuario);
        Session::flash('sucesso', 'Bem-vindo(a), ' . explode(' ', $usuario['nome'])[0] . '!');
        $this->voltarOuIr($usuario['perfil'] === 'admin' ? 'admin' : 'agendamentos');
    }

    /** Volta para a página que o usuário tentou abrir antes do login (se houver). */
    private function voltarOuIr(string $padrao): never
    {
        $voltar = Session::get('voltar_para');
        Session::remover('voltar_para');
        if (is_array($voltar) && isset($voltar['r']) && is_string($voltar['r'])) {
            $rota = $voltar['r'];
            unset($voltar['r']);
            $this->redirecionar($rota, array_filter($voltar, 'is_scalar'));
        }
        $this->redirecionar($padrao);
    }

    public function formCadastro(): void
    {
        $this->view('auth/cadastro', ['titulo' => 'Criar conta']);
    }

    public function cadastro(): void
    {
        $v = new Validator($_POST);
        $ok = $v->validar([
            'nome'              => 'obrigatorio|min:3|max:100',
            'email'             => 'obrigatorio|email|max:150',
            'telefone'          => 'telefone',
            'senha'             => 'obrigatorio|min:6|max:72',
            'senha_confirmacao' => 'obrigatorio|igual:senha',
        ], [
            'nome' => 'Nome', 'email' => 'E-mail', 'telefone' => 'Telefone',
            'senha' => 'Senha', 'senha_confirmacao' => 'Confirmação de senha',
        ]);

        $erros = $v->erros();
        $usuarios = new Usuario();
        if ($ok && $usuarios->emailExiste($_POST['email'])) {
            $erros['email'] = 'Este e-mail já está cadastrado.';
        }

        if ($erros) {
            $this->guardarEntrada($_POST, $erros);
            $this->redirecionar('cadastro');
        }

        $id = $usuarios->criar($_POST);
        limpar_entrada();
        Auth::entrar($usuarios->buscar($id));
        Session::flash('sucesso', 'Conta criada com sucesso! Agora é só agendar.');
        $voltar = Session::get('voltar_para');
        Session::remover('voltar_para');
        $this->redirecionar('agendamentos/novo', isset($voltar['servico']) ? ['servico' => (int) $voltar['servico']] : []);
    }

    public function sair(): void
    {
        Auth::sair();
        Session::iniciar();
        Session::flash('sucesso', 'Você saiu do sistema.');
        $this->redirecionar('login');
    }
}
