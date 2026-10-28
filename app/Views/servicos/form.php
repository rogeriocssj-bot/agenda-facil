<?php use App\Core\Csrf; /** @var array $servico */
$temOld = App\Core\Session::get('_old') !== null; ?>
<section class="caixa-form">
    <h1><?= e($titulo) ?></h1>
    <form method="post" action="<?= url('servicos/salvar') ?>" novalidate data-validar>
        <?= Csrf::campo() ?>
        <input type="hidden" name="id" value="<?= (int) $servico['id'] ?>">
        <div class="campo">
            <label for="nome">Nome *</label>
            <input type="text" id="nome" name="nome" value="<?= old('nome', $servico['nome']) ?>" required minlength="3" maxlength="100">
            <?= erro('nome') ?>
        </div>
        <div class="campo">
            <label for="descricao">Descrição</label>
            <input type="text" id="descricao" name="descricao" value="<?= old('descricao', $servico['descricao'] ?? '') ?>" maxlength="255">
            <?= erro('descricao') ?>
        </div>
        <div class="linha">
            <div class="campo">
                <label for="duracao_min">Duração (minutos) *</label>
                <input type="number" id="duracao_min" name="duracao_min" value="<?= old('duracao_min', $servico['duracao_min']) ?>" min="10" max="480" step="5" required>
                <?= erro('duracao_min') ?>
            </div>
            <div class="campo">
                <label for="preco">Preço (R$) *</label>
                <input type="text" inputmode="decimal" id="preco" name="preco" value="<?= old('preco', $servico['preco'] !== '' ? number_format((float) $servico['preco'], 2, ',', '') : '') ?>" required placeholder="0,00">
                <?= erro('preco') ?>
            </div>
        </div>
        <label class="checkbox">
            <input type="checkbox" name="ativo" value="1" <?= ($temOld ? !empty(App\Core\Session::get('_old')['ativo']) : $servico['ativo']) ? 'checked' : '' ?>>
            Serviço ativo (aparece para os clientes)
        </label>
        <div class="botoes">
            <a class="botao botao-secundario" href="<?= url('servicos') ?>">Voltar</a>
            <button type="submit" class="botao">Salvar</button>
        </div>
    </form>
</section>
