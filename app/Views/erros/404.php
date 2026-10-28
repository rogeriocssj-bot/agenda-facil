<section class="pagina-erro">
    <p class="erro-codigo">404</p>
    <h1>Página não encontrada</h1>
    <p>O endereço acessado não existe.</p>
<?php if (!empty($detalhe ?? null)): ?>
    <pre class="erro-detalhe"><?= e($detalhe) ?></pre>
<?php endif; ?>
    <a class="botao" href="<?= url('home') ?>">Voltar ao início</a>
</section>
