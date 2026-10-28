<section class="pagina-erro">
    <p class="erro-codigo">500</p>
    <h1>Erro inesperado</h1>
    <p>Algo deu errado do nosso lado. Tente novamente em instantes.</p>
<?php if (!empty($detalhe ?? null)): ?>
    <pre class="erro-detalhe"><?= e($detalhe) ?></pre>
<?php endif; ?>
    <a class="botao" href="<?= url('home') ?>">Voltar ao início</a>
</section>
