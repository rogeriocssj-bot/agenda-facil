<?php use App\Core\Csrf;
/** @var array $profissional @var array $selecionados @var array $servicos */
$old = App\Core\Session::get('_old');
$marcados = $old !== null ? array_map('intval', (array) ($old['servicos'] ?? [])) : $selecionados;
$ativo = $old !== null ? !empty($old['ativo']) : (bool) $profissional['ativo'];
?>
<section class="caixa-form">
    <h1><?= e($titulo) ?></h1>
    <form method="post" action="<?= url('profissionais/salvar') ?>" novalidate data-validar>
        <?= Csrf::campo() ?>
        <input type="hidden" name="id" value="<?= (int) $profissional['id'] ?>">
        <div class="campo">
            <label for="nome">Nome *</label>
            <input type="text" id="nome" name="nome" value="<?= e($old['nome'] ?? $profissional['nome']) ?>" required minlength="3" maxlength="100">
            <?= erro('nome') ?>
        </div>
        <div class="campo">
            <label for="especialidade">Especialidade</label>
            <input type="text" id="especialidade" name="especialidade" value="<?= e($old['especialidade'] ?? $profissional['especialidade'] ?? '') ?>" maxlength="100">
            <?= erro('especialidade') ?>
        </div>
        <fieldset class="campo">
            <legend>Serviços que realiza *</legend>
            <div class="lista-checks">
                <?php foreach ($servicos as $s): ?>
                    <label class="checkbox">
                        <input type="checkbox" name="servicos[]" value="<?= (int) $s['id'] ?>" <?= in_array((int) $s['id'], $marcados, true) ? 'checked' : '' ?>>
                        <?= e($s['nome']) ?><?= $s['ativo'] ? '' : ' (inativo)' ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <?= erro('servicos') ?>
        </fieldset>
        <label class="checkbox">
            <input type="checkbox" name="ativo" value="1" <?= $ativo ? 'checked' : '' ?>> Profissional ativo
        </label>
        <div class="botoes">
            <a class="botao botao-secundario" href="<?= url('profissionais') ?>">Voltar</a>
            <button type="submit" class="botao">Salvar</button>
        </div>
    </form>
</section>
