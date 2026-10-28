<?php use App\Core\Csrf; /** @var array $servicos */ ?>
<div class="cabecalho-pagina">
    <h1>Serviços</h1>
    <a class="botao" href="<?= url('servicos/form') ?>">+ Novo serviço</a>
</div>
<?php if (!$servicos): ?>
    <p class="vazio">Nenhum serviço cadastrado.</p>
<?php else: ?>
<div class="tabela-wrap">
    <table class="tabela">
        <thead><tr><th>Nome</th><th>Duração</th><th>Preço</th><th>Situação</th><th>Ações</th></tr></thead>
        <tbody>
        <?php foreach ($servicos as $s): ?>
            <tr>
                <td data-label="Nome"><?= e($s['nome']) ?><br><small class="muted"><?= e($s['descricao'] ?? '') ?></small></td>
                <td data-label="Duração"><?= (int) $s['duracao_min'] ?> min</td>
                <td data-label="Preço"><?= moeda($s['preco']) ?></td>
                <td data-label="Situação"><span class="selo <?= $s['ativo'] ? 'selo-confirmado' : 'selo-cancelado' ?>"><?= $s['ativo'] ? 'Ativo' : 'Inativo' ?></span></td>
                <td class="acoes">
                    <a class="botao botao-secundario botao-pequeno" href="<?= url('servicos/form', ['id' => $s['id']]) ?>">Editar</a>
                    <form method="post" action="<?= url('servicos/excluir') ?>" data-confirmar="Excluir o serviço &quot;<?= e($s['nome']) ?>&quot;?">
                        <?= Csrf::campo() ?>
                        <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                        <button type="submit" class="botao botao-perigo botao-pequeno">Excluir</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
