<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class Servico extends Model
{
    protected string $tabela = 'servicos';

    public function listar(bool $somenteAtivos = false): array
    {
        $sql = 'SELECT * FROM servicos' . ($somenteAtivos ? ' WHERE ativo = 1' : '') . ' ORDER BY nome';
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function nomeExiste(string $nome, ?int $ignorarId = null): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM servicos WHERE LOWER(nome) = LOWER(:nome) AND id <> :id');
        $stmt->execute(['nome' => trim($nome), 'id' => $ignorarId ?? 0]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function salvar(array $dados, ?int $id = null): int
    {
        $params = [
            'nome'        => trim($dados['nome']),
            'descricao'   => trim($dados['descricao'] ?? '') ?: null,
            'duracao_min' => (int) $dados['duracao_min'],
            'preco'       => (float) str_replace(',', '.', (string) $dados['preco']),
            'ativo'       => !empty($dados['ativo']) ? 1 : 0,
        ];

        if ($id === null) {
            $stmt = $this->db->prepare(
                'INSERT INTO servicos (nome, descricao, duracao_min, preco, ativo)
                 VALUES (:nome, :descricao, :duracao_min, :preco, :ativo)'
            );
            $stmt->execute($params);
            return (int) $this->db->lastInsertId();
        }

        $params['id'] = $id;
        $stmt = $this->db->prepare(
            'UPDATE servicos SET nome = :nome, descricao = :descricao, duracao_min = :duracao_min,
                    preco = :preco, ativo = :ativo WHERE id = :id'
        );
        $stmt->execute($params);
        return $id;
    }

    public function possuiAgendamentos(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM agendamentos WHERE servico_id = :id');
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
