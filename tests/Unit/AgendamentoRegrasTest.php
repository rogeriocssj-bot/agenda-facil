<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Services\AgendamentoService;
use PHPUnit\Framework\TestCase;

/** Testes unitários das funções puras de regra de negócio (sem banco de dados). */
final class AgendamentoRegrasTest extends TestCase
{
    public function testCalculaHoraFimPelaDuracao(): void
    {
        $this->assertSame('09:30', AgendamentoService::calcularHoraFim('09:00', 30));
        $this->assertSame('12:00', AgendamentoService::calcularHoraFim('10:00', 120));
        $this->assertSame('10:15', AgendamentoService::calcularHoraFim('09:45:00', 30));
    }

    public function testDuracaoInvalidaLancaExcecao(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        AgendamentoService::calcularHoraFim('09:00', 0);
    }

    public function testServicoQueUltrapassaMeiaNoiteELancaExcecao(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        AgendamentoService::calcularHoraFim('23:30', 60);
    }

    public static function provedorSobreposicao(): array
    {
        return [
            'mesmo horário'              => ['09:00', '09:30', '09:00', '09:30', true],
            'começa no meio do outro'    => ['09:15', '09:45', '09:00', '09:30', true],
            'engloba o outro'            => ['08:00', '12:00', '09:00', '09:30', true],
            'encosta no fim (permitido)' => ['09:30', '10:00', '09:00', '09:30', false],
            'encosta no início'          => ['08:30', '09:00', '09:00', '09:30', false],
            'totalmente separado'        => ['14:00', '15:00', '09:00', '09:30', false],
        ];
    }

    /** @dataProvider provedorSobreposicao */
    public function testDeteccaoDeSobreposicao(string $a1, string $a2, string $b1, string $b2, bool $esperado): void
    {
        $this->assertSame($esperado, AgendamentoService::sobrepoe($a1, $a2, $b1, $b2));
    }

    public function testHaConflitoComListaDeOcupados(): void
    {
        $ocupados = [
            ['hora_inicio' => '09:00:00', 'hora_fim' => '09:30:00'],
            ['hora_inicio' => '14:00:00', 'hora_fim' => '16:00:00'],
        ];
        $this->assertTrue(AgendamentoService::haConflito($ocupados, '15:00', '15:30'));
        $this->assertFalse(AgendamentoService::haConflito($ocupados, '10:00', '11:00'));
        $this->assertFalse(AgendamentoService::haConflito([], '10:00', '11:00'));
    }

    public function testTransicoesDeStatus(): void
    {
        $this->assertTrue(AgendamentoService::transicaoPermitida('agendado', 'confirmado'));
        $this->assertTrue(AgendamentoService::transicaoPermitida('confirmado', 'concluido'));
        $this->assertFalse(AgendamentoService::transicaoPermitida('cancelado', 'agendado'));
        $this->assertFalse(AgendamentoService::transicaoPermitida('concluido', 'cancelado'));
        $this->assertFalse(AgendamentoService::transicaoPermitida('agendado', 'status_invalido'));
    }
}
