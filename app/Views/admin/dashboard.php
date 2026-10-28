<?php
/** @var array $hoje @var array $resumo @var float $faturamento @var int $clientes */
?>
<div class="cabecalho-pagina">
    <h1>Painel administrativo</h1>
    <a class="botao" href="<?= url('admin/agenda', ['data' => date('Y-m-d')]) ?>">Ver agenda de hoje</a>
</div>

<section class="indicadores" aria-label="Indicadores do mês">
    <div class="indicador"><span class="indicador-num"><?= $resumo['agendado'] + $resumo['confirmado'] ?></span><span>Agendamentos ativos no mês</span></div>
    <div class="indicador"><span class="indicador-num"><?= $resumo['concluido'] ?></span><span>Atendimentos concluídos</span></div>
    <div class="indicador"><span class="indicador-num"><?= $resumo['cancelado'] ?></span><span>Cancelamentos</span></div>
    <div class="indicador"><span class="indicador-num"><?= moeda($faturamento) ?></span><span>Faturamento previsto</span></div>
    <div class="indicador"><span class="indicador-num"><?= $clientes ?></span><span>Clientes cadastrados</span></div>
</section>

<section>
    <h2>Atendimentos de hoje (<?= date('d/m/Y') ?>)</h2>
    <?php $agendamentos = $hoje; $voltar = 'admin'; require __DIR__ . '/_tabela.php'; ?>
</section>
