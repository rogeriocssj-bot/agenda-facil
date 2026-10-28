<?php
use App\Core\Csrf;
/** @var array $agendamentos @var DateTimeImmutable $agora @var int $horasCancel */
?>
<div class="cabecalho-pagina">
    <h1>Meus agendamentos</h1>
    <a class="botao" href="<?= url('agendamentos/novo') ?>">+ Novo agendamento</a>
</div>

<?php if (!$agendamentos): ?>
    <div class="vazio">
        <p>Você ainda não tem agendamentos.</p>
        <a class="botao" href="<?= url('agendamentos/novo') ?>">Fazer meu primeiro agendamento</a>
    </div>
<?php else: ?>
    <div class="tabela-wrap">
        <table class="tabela">
            <thead>
            <tr><th>Data</th><th>Horário</th><th>Serviço</th><th>Profissional</th><th>Valor</th><th>Status</th><th><span class="sr">Ações</span></th></tr>
            </thead>
            <tbody>
            <?php foreach ($agendamentos as $a):
                $inicio = new DateTimeImmutable($a['data'] . ' ' . $a['hora_inicio']);
                $podeCancelar = in_array($a['status'], ['agendado', 'confirmado'], true)
                    && $inicio->modify("-{$horasCancel} hours") >= $agora;
            ?>
                <tr>
                    <td data-label="Data"><?= data_br($a['data']) ?></td>
                    <td data-label="Horário"><?= hora_curta($a['hora_inicio']) ?> – <?= hora_curta($a['hora_fim']) ?></td>
                    <td data-label="Serviço"><?= e($a['servico_nome']) ?></td>
                    <td data-label="Profissional"><?= e($a['profissional_nome']) ?></td>
                    <td data-label="Valor"><?= moeda($a['servico_preco']) ?></td>
                    <td data-label="Status"><span class="selo selo-<?= e($a['status']) ?>"><?= status_label($a['status']) ?></span></td>
                    <td>
                        <?php if ($podeCancelar): ?>
                            <form method="post" action="<?= url('agendamentos/cancelar') ?>" data-confirmar="Deseja realmente cancelar este agendamento?">
                                <?= Csrf::campo() ?>
                                <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                                <button type="submit" class="botao botao-perigo botao-pequeno">Cancelar</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="muted">Cancelamentos pelo sistema podem ser feitos até <?= $horasCancel ?> horas antes do horário marcado.</p>
<?php endif; ?>
