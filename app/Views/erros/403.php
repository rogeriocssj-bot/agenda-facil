<section class="pagina-erro">
    <p class="erro-codigo">403</p>
    <h1>Acesso negado</h1>
    <p>Você não tem permissão para acessar esta página.</p>
<?php if (!empty($detalhe ?? null)): ?>
    <pre class="erro-detalhe"><?= e($detalhe) ?></pre>
<?php endif; ?>
    <a class="botao" href="<?= url('home') ?>">Voltar ao início</a>
</section>
