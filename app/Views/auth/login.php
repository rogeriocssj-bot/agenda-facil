<?php use App\Core\Csrf; ?>
<section class="caixa-form">
    <h1>Entrar</h1>
    <form method="post" action="<?= url('login') ?>" novalidate data-validar>
        <?= Csrf::campo() ?>
        <div class="campo">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" value="<?= old('email') ?>" required autocomplete="email" autofocus>
            <?= erro('email') ?>
        </div>
        <div class="campo">
            <label for="senha">Senha</label>
            <div class="senha-wrap">
                <input type="password" id="senha" name="senha" required autocomplete="current-password">
                <button type="button" class="ver-senha" data-alvo="senha" aria-label="Mostrar senha">Mostrar</button>
            </div>
            <?= erro('senha') ?>
        </div>
        <button type="submit" class="botao botao-bloco">Entrar</button>
    </form>
    <p class="centro">Não tem conta? <a href="<?= url('cadastro') ?>">Cadastre-se</a></p>
</section>
