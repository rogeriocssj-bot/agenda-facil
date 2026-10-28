<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class Profissional extends Model
{
    protected string $tabela = 'profissionais';

    public function listar(bool $somenteAtivos = false): array
    {
        $sql = 'SELECT * FROM profissionais' . ($somenteAtivos ? ' WHERE ativo = 1' : '') . ' ORDER BY nome';
        $lista = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        foreach ($lista as &$p) {
            $p['servicos'] = $this->servicosIds((int) $p['id']);
        }
        return $lista;
    }

    /** Profissionais ativos que realizam determinado serviço. */
    public function listarPorServico(int $servicoId): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.* FROM profissionais p
             INNER JOIN profissional_servico ps ON ps.profissional_id = p.id
             WHERE ps.servico_id = :servico AND p.ativo = 1
             ORDER BY p.nome'
        );
        $stmt->execute(['servico' => $servicoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return int[] */
    public function servicosIds(int $profissionalId): array
    {
        $stmt = $this->db->prepare('SELECT servico_id FROM profissional_servico WHERE profissional_id = :id');
        $stmt->execute(['id' => $profissionalId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    public function realizaServico(int $profissionalId, int $servicoId): bool
    {
        return in_array($servicoId, $this->servicosIds($profissionalId), true);
    }

    /** @param int[] $servicos */
    public function salvar(array $dados, array $servicos, ?int $id = null): int
    {
        $this->db->beginTransaction();
        try {
            $params = [
                'nome'          => trim($dados['nome']),
                'especialidade' => trim($dados['especialidade'] ?? '') ?: null,
                'ativo'         => !empty($dados['ativo']) ? 1 : 0,
            ];
            if ($id === null) {
                $stmt = $this->db->prepare(
                    'INSERT INTO profissionais (nome, especialidade, ativo) VALUES (:nome, :especialidade, :ativo)'
                );
                $stmt->execute($params);
                $id = (int) $this->db->lastInsertId();
            } else {
                $params['id'] = $id;
                $stmt = $this->db->prepare(
                    'UPDATE profissionais SET nome = :nome, especialidade = :especialidade, ativo = :ativo WHERE id = :id'
                );
                $stmt->execute($params);
                $this->db->prepare('DELETE FROM profissional_servico WHERE profissional_id = :id')->execute(['id' => $id]);
            }

            $ins = $this->db->prepare('INSERT INTO profissional_servico (profissional_id, servico_id) VALUES (:p, :s)');
            foreach (array_unique(array_map('intval', $servicos)) as $servicoId) {
                $ins->execute(['p' => $id, 's' => $servicoId]);
            }
            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function possuiAgendamentos(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM agendamentos WHERE profissional_id = :id');
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
