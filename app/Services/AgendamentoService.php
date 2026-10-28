<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Agendamento;
use App\Models\Profissional;
use App\Models\Servico;
use DateTimeImmutable;

/**
 * Regras de negócio do agendamento.
 * Fica separada do controller para poder ser testada isoladamente (PHPUnit).
 *
 * RN01 - Não é permitido agendar em data/horário que já passou.
 * RN02 - O horário deve estar dentro do expediente (abertura/fechamento) e em dia de funcionamento.
 * RN03 - Um profissional não pode ter dois agendamentos sobrepostos.
 * RN04 - O profissional escolhido precisa realizar o serviço escolhido.
 * RN05 - O cliente não pode ter dois agendamentos sobrepostos.
 * RN06 - O cliente só pode cancelar com antecedência mínima configurada (padrão 2h).
 * RN07 - Agendamentos concluídos ou cancelados não mudam mais de status.
 */
class AgendamentoService
{
    /** Transições de status permitidas (RN07). */
    public const TRANSICOES = [
        'agendado'   => ['confirmado', 'cancelado', 'concluido'],
        'confirmado' => ['concluido', 'cancelado'],
        'concluido'  => [],
        'cancelado'  => [],
    ];

    public function __construct(
        private Agendamento $agendamentos,
        private Servico $servicos,
        private Profissional $profissionais,
        private array $config,
    ) {
    }

    // ------------------------------------------------------------------
    // Funções puras (sem banco) — fáceis de testar
    // ------------------------------------------------------------------

    public static function calcularHoraFim(string $inicio, int $duracaoMin): string
    {
        $h = DateTimeImmutable::createFromFormat('H:i', substr($inicio, 0, 5));
        if ($h === false || $duracaoMin <= 0) {
            throw new \InvalidArgumentException('Horário ou duração inválidos.');
        }
        $fim = $h->modify("+{$duracaoMin} minutes");
        if ($fim->format('Y-m-d') !== $h->format('Y-m-d')) {
            throw new \InvalidArgumentException('O serviço ultrapassa a meia-noite.');
        }
        return $fim->format('H:i');
    }

    /** Dois intervalos [a1,a2) e [b1,b2) se sobrepõem? */
    public static function sobrepoe(string $a1, string $a2, string $b1, string $b2): bool
    {
        return substr($a1, 0, 5) < substr($b2, 0, 5) && substr($a2, 0, 5) > substr($b1, 0, 5);
    }

    public static function haConflito(array $ocupados, string $inicio, string $fim): bool
    {
        foreach ($ocupados as $o) {
            if (self::sobrepoe($inicio, $fim, $o['hora_inicio'], $o['hora_fim'])) {
                return true;
            }
        }
        return false;
    }

    public function dentroDoExpediente(string $data, string $inicio, string $fim): bool
    {
        $diaSemana = (int) (new DateTimeImmutable($data))->format('w');
        if (!in_array($diaSemana, $this->config['dias_funcionamento'], true)) {
            return false;
        }
        return substr($inicio, 0, 5) >= $this->config['abertura']
            && substr($fim, 0, 5) <= $this->config['fechamento'];
    }

    public static function transicaoPermitida(string $de, string $para): bool
    {
        return in_array($para, self::TRANSICOES[$de] ?? [], true);
    }

    // ------------------------------------------------------------------
    // Regras que consultam o banco
    // ------------------------------------------------------------------

    /**
     * Horários livres (HH:MM) para um profissional/serviço numa data.
     * @return string[]
     */
    public function horariosDisponiveis(int $profissionalId, int $servicoId, string $data, DateTimeImmutable $agora): array
    {
        $servico = $this->servicos->buscar($servicoId);
        if (!$servico || !$servico['ativo']) {
            return [];
        }
        $duracao = (int) $servico['duracao_min'];
        $ocupados = $this->agendamentos->ocupados($profissionalId, $data);
        $passo = (int) $this->config['intervalo_slots_min'];

        $slots = [];
        $hora = DateTimeImmutable::createFromFormat('Y-m-d H:i', $data . ' ' . $this->config['abertura']);
        $limite = DateTimeImmutable::createFromFormat('Y-m-d H:i', $data . ' ' . $this->config['fechamento']);
        if (!$hora || !$limite) {
            return [];
        }

        while ($hora < $limite) {
            $inicio = $hora->format('H:i');
            $fimDt = $hora->modify("+{$duracao} minutes");
            $fim = $fimDt->format('H:i');
            if ($fimDt <= $limite
                && $hora > $agora
                && $this->dentroDoExpediente($data, $inicio, $fim)
                && !self::haConflito($ocupados, $inicio, $fim)) {
                $slots[] = $inicio;
            }
            $hora = $hora->modify("+{$passo} minutes");
        }
        return $slots;
    }

    /**
     * Cria um agendamento aplicando todas as regras de negócio.
     * @throws RegraNegocioException
     */
    public function agendar(int $clienteId, int $servicoId, int $profissionalId, string $data, string $hora,
                            ?string $observacao, DateTimeImmutable $agora): int
    {
        $servico = $this->servicos->buscar($servicoId);
        if (!$servico || !$servico['ativo']) {
            throw new RegraNegocioException('Serviço inválido ou indisponível.');
        }
        $profissional = $this->profissionais->buscar($profissionalId);
        if (!$profissional || !$profissional['ativo']) {
            throw new RegraNegocioException('Profissional inválido ou indisponível.');
        }
        if (!$this->profissionais->realizaServico($profissionalId, $servicoId)) {
            throw new RegraNegocioException('Este profissional não realiza o serviço escolhido.'); // RN04
        }

        $inicioDt = DateTimeImmutable::createFromFormat('Y-m-d H:i', "{$data} {$hora}");
        if ($inicioDt === false) {
            throw new RegraNegocioException('Data ou horário inválidos.');
        }
        if ($inicioDt <= $agora) {
            throw new RegraNegocioException('Não é possível agendar em um horário que já passou.'); // RN01
        }

        try {
            $fim = self::calcularHoraFim($hora, (int) $servico['duracao_min']);
        } catch (\InvalidArgumentException $e) {
            throw new RegraNegocioException($e->getMessage());
        }

        if (!$this->dentroDoExpediente($data, $hora, $fim)) {
            throw new RegraNegocioException(sprintf(
                'Horário fora do expediente. Atendemos de segunda a sábado, das %s às %s.',
                $this->config['abertura'], $this->config['fechamento']
            )); // RN02
        }

        if (self::haConflito($this->agendamentos->ocupados($profissionalId, $data), $hora, $fim)) {
            throw new RegraNegocioException('Este horário acabou de ser ocupado. Escolha outro horário.'); // RN03
        }

        if ($this->agendamentos->clienteOcupado($clienteId, $data, $hora . ':00', $fim . ':00')) {
            throw new RegraNegocioException('Você já possui outro agendamento neste horário.'); // RN05
        }

        return $this->agendamentos->criar([
            'cliente_id'      => $clienteId,
            'servico_id'      => $servicoId,
            'profissional_id' => $profissionalId,
            'data'            => $data,
            'hora_inicio'     => $hora . ':00',
            'hora_fim'        => $fim . ':00',
            'observacao'      => $observacao !== null ? mb_substr(trim($observacao), 0, 255) : null,
        ]);
    }

    /** @throws RegraNegocioException */
    public function cancelarPeloCliente(int $agendamentoId, int $clienteId, DateTimeImmutable $agora): void
    {
        $ag = $this->agendamentos->buscar($agendamentoId);
        if (!$ag || (int) $ag['cliente_id'] !== $clienteId) {
            throw new RegraNegocioException('Agendamento não encontrado.');
        }
        if (!self::transicaoPermitida($ag['status'], 'cancelado')) {
            throw new RegraNegocioException('Este agendamento não pode mais ser cancelado.');
        }
        $inicio = new DateTimeImmutable($ag['data'] . ' ' . substr($ag['hora_inicio'], 0, 5));
        $limite = $inicio->modify('-' . (int) $this->config['antecedencia_cancelar_h'] . ' hours');
        if ($agora > $limite) {
            throw new RegraNegocioException(sprintf(
                'O cancelamento só é permitido com %d horas de antecedência. Entre em contato com o estabelecimento.',
                $this->config['antecedencia_cancelar_h']
            )); // RN06
        }
        $this->agendamentos->alterarStatus($agendamentoId, 'cancelado');
    }

    /** Alteração de status pelo administrador. @throws RegraNegocioException */
    public function alterarStatus(int $agendamentoId, string $novoStatus): void
    {
        $ag = $this->agendamentos->buscar($agendamentoId);
        if (!$ag) {
            throw new RegraNegocioException('Agendamento não encontrado.');
        }
        if (!self::transicaoPermitida($ag['status'], $novoStatus)) {
            throw new RegraNegocioException(sprintf(
                'Não é possível mudar de "%s" para "%s".', status_label($ag['status']), status_label($novoStatus)
            )); // RN07
        }
        $this->agendamentos->alterarStatus($agendamentoId, $novoStatus);
    }
}
