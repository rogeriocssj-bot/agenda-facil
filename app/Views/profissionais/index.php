<?php use App\Core\Csrf; /** @var array $profissionais @var array $servicos */ ?>
<div class="cabecalho-pagina">
    <h1>Profissionais</h1>
    <a class="botao" href="<?= url('profissionais/form') ?>">+ Novo profissional</a>
</div>
<?php if (!$profissionais): ?>
    <p class="vazio">Nenhum profissional cadastrado.</p>
<?php else: ?>
<div class="tabela-wrap">
    <table class="tabela">
        <thead><tr><th>Nome</th><th>Especialidade</th><th>Serviços</th><th>Situação</th><th>Ações</th></tr></thead>
        <tbody>
        <?php foreach ($profissionais as $p): ?>
            <tr>
                <td data-label="Nome"><?= e($p['nome']) ?></td>
                <td data-label="Especialidade"><?= e($p['especialidade'] ?? '—') ?></td>
                <td data-label="Serviços"><?= e(implode(', ', array_map(fn($id) => $servicos[$id] ?? '?', $p['servicos'])) ?: '—') ?></td>
                <td data-label="Situação"><span class="selo <?= $p['ativo'] ? 'selo-confirmado' : 'selo-cancelado' ?>"><?= $p['ativo'] ? 'Ativo' : 'Inativo' ?></span></td>
                <td class="acoes">
                    <a class="botao botao-secundario botao-pequeno" href="<?= url('profissionais/form', ['id' => $p['id']]) ?>">Editar</a>
                    <form method="post" action="<?= url('profissionais/excluir') ?>" data-confirmar="Excluir <?= e($p['nome']) ?>?">
                        <?= Csrf::campo() ?>
                        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                        <button type="submit" class="botao botao-perigo botao-pequeno">Excluir</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
