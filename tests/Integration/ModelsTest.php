<?php
declare(strict_types=1);

namespace Tests\Integration;

use App\Models\Profissional;
use App\Models\Servico;
use App\Models\Usuario;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\BancoDeTeste;

/** Testes de integração dos models de cadastro (usuários, serviços e profissionais). */
final class ModelsTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = BancoDeTeste::criar();
    }

    public function testCadastroDeUsuarioGuardaSenhaCriptografada(): void
    {
        $usuarios = new Usuario($this->pdo);
        $id = $usuarios->criar(['nome' => 'Pedro', 'email' => '  PEDRO@Teste.com ', 'senha' => 'segredo1']);
        $u = $usuarios->buscar($id);

        $this->assertSame('pedro@teste.com', $u['email']);            // e-mail normalizado
        $this->assertNotSame('segredo1', $u['senha_hash']);           // senha não fica em texto puro
        $this->assertTrue(password_verify('segredo1', $u['senha_hash']));
        $this->assertSame('cliente', $u['perfil']);
    }

    public function testLoginComSenhaCorretaEIncorreta(): void
    {
        $usuarios = new Usuario($this->pdo);
        $this->assertNotNull($usuarios->autenticar('maria@teste.com', 'maria123'));
        $this->assertNull($usuarios->autenticar('maria@teste.com', 'errada'));
        $this->assertNull($usuarios->autenticar('naoexiste@teste.com', 'maria123'));
    }

    public function testEmailDuplicadoEBloqueadoPeloBanco(): void
    {
        $this->expectException(\PDOException::class);
        (new Usuario($this->pdo))->criar(['nome' => 'Outra Maria', 'email' => 'maria@teste.com', 'senha' => '123456']);
    }

    public function testCrudDeServico(): void
    {
        $servicos = new Servico($this->pdo);
        $id = $servicos->salvar(['nome' => 'Hidratação', 'duracao_min' => '45', 'preco' => '60,50', 'ativo' => '1']);
        $this->assertEqualsWithDelta(60.5, (float) $servicos->buscar($id)['preco'], 0.001);

        $servicos->salvar(['nome' => 'Hidratação capilar', 'duracao_min' => '60', 'preco' => '70', 'ativo' => ''], $id);
        $s = $servicos->buscar($id);
        $this->assertSame('Hidratação capilar', $s['nome']);
        $this->assertSame(0, (int) $s['ativo']);

        $this->assertTrue($servicos->excluir($id));
        $this->assertNull($servicos->buscar($id));
    }

    public function testNomeDeServicoDuplicadoIgnorandoMaiusculas(): void
    {
        $servicos = new Servico($this->pdo);
        $this->assertTrue($servicos->nomeExiste('CORTE'));
        $this->assertFalse($servicos->nomeExiste('Corte', 1)); // editando o próprio registro
        $this->assertFalse($servicos->nomeExiste('Massagem'));
    }

    public function testListarSomenteServicosAtivos(): void
    {
        $this->assertCount(3, (new Servico($this->pdo))->listar());
        $this->assertCount(2, (new Servico($this->pdo))->listar(true));
    }

    public function testProfissionalComServicos(): void
    {
        $prof = new Profissional($this->pdo);
        $id = $prof->salvar(['nome' => 'Bruno', 'especialidade' => 'Barbeiro', 'ativo' => '1'], ['1', '2']);
        $this->assertSame([1, 2], $prof->servicosIds($id));

        $prof->salvar(['nome' => 'Bruno', 'ativo' => '1'], ['2'], $id);
        $this->assertSame([2], $prof->servicosIds($id));
        $this->assertFalse($prof->realizaServico($id, 1));
    }

    public function testListarProfissionaisPorServicoIgnoraInativos(): void
    {
        $nomes = array_column((new Profissional($this->pdo))->listarPorServico(1), 'nome');
        $this->assertSame(['Ana', 'Carlos'], $nomes); // "Inativo" não aparece
    }

    public function testServicoComAgendamentoNaoPodeSerExcluido(): void
    {
        $this->pdo->exec("INSERT INTO agendamentos (cliente_id, servico_id, profissional_id, data, hora_inicio, hora_fim)
                          VALUES (2, 1, 1, '2030-01-07', '09:00:00', '09:30:00')");
        $this->assertTrue((new Servico($this->pdo))->possuiAgendamentos(1));
        $this->expectException(\PDOException::class); // chave estrangeira (RESTRICT)
        (new Servico($this->pdo))->excluir(1);
    }
}
