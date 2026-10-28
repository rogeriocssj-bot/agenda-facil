<?php use App\Core\Csrf; ?>
<section class="caixa-form">
    <h1>Criar conta</h1>
    <form method="post" action="<?= url('cadastro') ?>" novalidate data-validar>
        <?= Csrf::campo() ?>
        <div class="campo">
            <label for="nome">Nome completo *</label>
            <input type="text" id="nome" name="nome" value="<?= old('nome') ?>" required minlength="3" maxlength="100" autocomplete="name">
            <?= erro('nome') ?>
        </div>
        <div class="campo">
            <label for="email">E-mail *</label>
            <input type="email" id="email" name="email" value="<?= old('email') ?>" required maxlength="150" autocomplete="email">
            <?= erro('email') ?>
        </div>
        <div class="campo">
            <label for="telefone">Telefone (WhatsApp)</label>
            <input type="tel" id="telefone" name="telefone" value="<?= old('telefone') ?>" placeholder="(11) 99999-9999" data-mascara="telefone" maxlength="15" autocomplete="tel">
            <?= erro('telefone') ?>
        </div>
        <div class="campo">
            <label for="senha">Senha * <small class="muted">(mínimo 6 caracteres)</small></label>
            <div class="senha-wrap">
                <input type="password" id="senha" name="senha" required minlength="6" maxlength="72" autocomplete="new-password">
                <button type="button" class="ver-senha" data-alvo="senha" aria-label="Mostrar senha">Mostrar</button>
            </div>
            <?= erro('senha') ?>
        </div>
        <div class="campo">
            <label for="senha_confirmacao">Confirme a senha *</label>
            <input type="password" id="senha_confirmacao" name="senha_confirmacao" required data-igual="senha" autocomplete="new-password">
            <?= erro('senha_confirmacao') ?>
        </div>
        <button type="submit" class="botao botao-bloco">Criar conta</button>
    </form>
    <p class="centro">Já tem conta? <a href="<?= url('login') ?>">Entrar</a></p>
</section>
