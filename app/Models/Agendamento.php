<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class Agendamento extends Model
{
    protected string $tabela = 'agendamentos';

    private const SELECT_COMPLETO =
        'SELECT a.*, s.nome AS servico_nome, s.preco AS servico_preco, s.duracao_min,
                p.nome AS profissional_nome, u.nome AS cliente_nome, u.telefone AS cliente_telefone
         FROM agendamentos a
         INNER JOIN servicos s      ON s.id = a.servico_id
         INNER JOIN profissionais p ON p.id = a.profissional_id
         INNER JOIN usuarios u      ON u.id = a.cliente_id';

    public function criar(array $dados): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO agendamentos (cliente_id, servico_id, profissional_id, data, hora_inicio, hora_fim, status, observacao)
             VALUES (:cliente_id, :servico_id, :profissional_id, :data, :hora_inicio, :hora_fim, :status, :observacao)'
        );
        $stmt->execute([
            'cliente_id'      => $dados['cliente_id'],
            'servico_id'      => $dados['servico_id'],
            'profissional_id' => $dados['profissional_id'],
            'data'            => $dados['data'],
            'hora_inicio'     => $dados['hora_inicio'],
            'hora_fim'        => $dados['hora_fim'],
            'status'          => $dados['status'] ?? 'agendado',
            'observacao'      => ($dados['observacao'] ?? '') !== '' ? $dados['observacao'] : null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function buscarCompleto(int $id): ?array
    {
        $stmt = $this->db->prepare(self::SELECT_COMPLETO . ' WHERE a.id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function listarDoCliente(int $clienteId): array
    {
        $stmt = $this->db->prepare(self::SELECT_COMPLETO . ' WHERE a.cliente_id = :c ORDER BY a.data DESC, a.hora_inicio DESC');
        $stmt->execute(['c' => $clienteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Lista para o administrador, com filtros opcionais. */
    public function listarTodos(?string $data = null, ?int $profissionalId = null, ?string $status = null): array
    {
        $where = [];
        $params = [];
        if ($data) {
            $where[] = 'a.data = :data';
            $params['data'] = $data;
        }
        if ($profissionalId) {
            $where[] = 'a.profissional_id = :prof';
            $params['prof'] = $profissionalId;
        }
        if ($status) {
            $where[] = 'a.status = :status';
            $params['status'] = $status;
        }
        $sql = self::SELECT_COMPLETO
            . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
            . ' ORDER BY a.data ASC, a.hora_inicio ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Horários já ocupados de um profissional em uma data (ignora cancelados). */
    public function ocupados(int $profissionalId, string $data, ?int $ignorarId = null): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, hora_inicio, hora_fim FROM agendamentos
             WHERE profissional_id = :p AND data = :d AND status <> 'cancelado' AND id <> :ignorar
             ORDER BY hora_inicio"
        );
        $stmt->execute(['p' => $profissionalId, 'd' => $data, 'ignorar' => $ignorarId ?? 0]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Verifica se o cliente já tem outro agendamento que se sobrepõe ao horário. */
    public function clienteOcupado(int $clienteId, string $data, string $inicio, string $fim): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM agendamentos
             WHERE cliente_id = :c AND data = :d AND status <> 'cancelado'
               AND hora_inicio < :fim AND hora_fim > :inicio"
        );
        $stmt->execute(['c' => $clienteId, 'd' => $data, 'inicio' => $inicio, 'fim' => $fim]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function alterarStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE agendamentos SET status = :s WHERE id = :id');
        return $stmt->execute(['s' => $status, 'id' => $id]) && $stmt->rowCount() > 0;
    }

    /** @return array<string,int> contagem por status no período */
    public function resumoPorStatus(string $dataInicio, string $dataFim): array
    {
        $stmt = $this->db->prepare(
            'SELECT status, COUNT(*) AS total FROM agendamentos WHERE data BETWEEN :i AND :f GROUP BY status'
        );
        $stmt->execute(['i' => $dataInicio, 'f' => $dataFim]);
        $resumo = ['agendado' => 0, 'confirmado' => 0, 'concluido' => 0, 'cancelado' => 0];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
            $resumo[$linha['status']] = (int) $linha['total'];
        }
        return $resumo;
    }

    public function faturamentoPrevisto(string $dataInicio, string $dataFim): float
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(s.preco), 0) FROM agendamentos a
             INNER JOIN servicos s ON s.id = a.servico_id
             WHERE a.data BETWEEN :i AND :f AND a.status <> 'cancelado'"
        );
        $stmt->execute(['i' => $dataInicio, 'f' => $dataFim]);
        return (float) $stmt->fetchColumn();
    }
}
