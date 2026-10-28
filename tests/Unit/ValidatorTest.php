<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Validator;
use PHPUnit\Framework\TestCase;

/** Testes unitários da classe de validação (CT01 a CT09 do plano de testes). */
final class ValidatorTest extends TestCase
{
    public function testCampoObrigatorioVazioGeraErro(): void
    {
        $v = new Validator(['nome' => '   ']);
        $this->assertFalse($v->validar(['nome' => 'obrigatorio'], ['nome' => 'Nome']));
        $this->assertSame('O campo Nome é obrigatório.', $v->erros()['nome']);
    }

    public function testEmailInvalidoGeraErro(): void
    {
        $v = new Validator(['email' => 'maria@']);
        $this->assertFalse($v->validar(['email' => 'obrigatorio|email']));
        $this->assertArrayHasKey('email', $v->erros());
    }

    public function testEmailValidoPassa(): void
    {
        $v = new Validator(['email' => 'maria@exemplo.com.br']);
        $this->assertTrue($v->validar(['email' => 'obrigatorio|email']));
    }

    public function testTamanhoMinimoDaSenha(): void
    {
        $v = new Validator(['senha' => '123']);
        $this->assertFalse($v->validar(['senha' => 'min:6'], ['senha' => 'Senha']));
        $this->assertSame('Senha deve ter pelo menos 6 caracteres.', $v->erros()['senha']);
    }

    public function testConfirmacaoDeSenhaDiferente(): void
    {
        $v = new Validator(['senha' => 'abc123', 'senha_confirmacao' => 'abc124']);
        $this->assertFalse($v->validar(['senha_confirmacao' => 'igual:senha']));
    }

    public function testCampoOpcionalVazioNaoEValidado(): void
    {
        $v = new Validator(['telefone' => '']);
        $this->assertTrue($v->validar(['telefone' => 'telefone']));
    }

    public static function provedorDatas(): array
    {
        return [
            'data válida'          => ['2026-12-25', true],
            'dia inexistente'      => ['2026-02-30', false],
            'formato brasileiro'   => ['25/12/2026', false],
            'texto'                => ['amanhã', false],
        ];
    }

    /** @dataProvider provedorDatas */
    public function testValidacaoDeData(string $data, bool $esperado): void
    {
        $this->assertSame($esperado, Validator::dataValida($data));
    }

    public static function provedorHoras(): array
    {
        return [['09:00', true], ['23:59', true], ['24:00', false], ['9h', false], ['09:60', false]];
    }

    /** @dataProvider provedorHoras */
    public function testValidacaoDeHora(string $hora, bool $esperado): void
    {
        $v = new Validator(['hora' => $hora]);
        $this->assertSame($esperado, $v->validar(['hora' => 'hora']));
    }

    public function testTelefoneNoFormatoBrasileiro(): void
    {
        $this->assertTrue((new Validator(['t' => '(11) 98888-1111']))->validar(['t' => 'telefone']));
        $this->assertFalse((new Validator(['t' => '1234']))->validar(['t' => 'telefone']));
    }

    public function testPrecoAceitaVirgulaERejeitaNegativo(): void
    {
        $this->assertTrue((new Validator(['p' => '45,50']))->validar(['p' => 'numero']));
        $this->assertFalse((new Validator(['p' => '-10']))->validar(['p' => 'numero']));
    }

    public function testRegraDesconhecidaLancaExcecao(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new Validator(['x' => 'a']))->validar(['x' => 'regra_inexistente']);
    }
}
