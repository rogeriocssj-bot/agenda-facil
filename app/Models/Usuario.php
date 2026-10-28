<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class Usuario extends Model
{
    protected string $tabela = 'usuarios';

    public function buscarPorEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE email = :email');
        $stmt->execute(['email' => mb_strtolower(trim($email))]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function emailExiste(string $email): bool
    {
        return $this->buscarPorEmail($email) !== null;
    }

    public function criar(array $dados): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO usuarios (nome, email, telefone, senha_hash, perfil)
             VALUES (:nome, :email, :telefone, :senha_hash, :perfil)'
        );
        $stmt->execute([
            'nome'       => trim($dados['nome']),
            'email'      => mb_strtolower(trim($dados['email'])),
            'telefone'   => ($dados['telefone'] ?? '') !== '' ? trim($dados['telefone']) : null,
            'senha_hash' => password_hash($dados['senha'], PASSWORD_DEFAULT),
            'perfil'     => $dados['perfil'] ?? 'cliente',
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** Retorna o usuário se e-mail e senha conferem; caso contrário, null. */
    public function autenticar(string $email, string $senha): ?array
    {
        $usuario = $this->buscarPorEmail($email);
        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            return $usuario;
        }
        return null;
    }

    public function totalClientes(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM usuarios WHERE perfil = 'cliente'")->fetchColumn();
    }
}
