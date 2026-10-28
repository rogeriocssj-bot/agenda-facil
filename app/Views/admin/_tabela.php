<?php
use App\Core\Csrf;
use App\Services\AgendamentoService;
/** @var array $agendamentos @var string $voltar */
$acoes = ['confirmado' => 'Confirmar', 'concluido' => 'Concluir', 'cancelado' => 'Cancelar'];
?>
<?php if (!$agendamentos): ?>
    <p class="vazio">Nenhum agendamento encontrado.</p>
<?php else: ?>
    <div class="tabela-wrap">
        <table class="tabela">
            <thead>
            <tr><th>Data</th><th>Horário</th><th>Cliente</th><th>Serviço</th><th>Profissional</th><th>Status</th><th>Ações</th></tr>
            </thead>
            <tbody>
            <?php foreach ($agendamentos as $a): ?>
                <tr>
                    <td data-label="Data"><?= data_br($a['data']) ?></td>
                    <td data-label="Horário"><?= hora_curta($a['hora_inicio']) ?> – <?= hora_curta($a['hora_fim']) ?></td>
                    <td data-label="Cliente">
                        <?= e($a['cliente_nome']) ?>
                        <?php if ($a['cliente_telefone']): ?><br><small class="muted"><?= e($a['cliente_telefone']) ?></small><?php endif; ?>
                        <?php if ($a['observacao']): ?><br><small class="obs">“<?= e($a['observacao']) ?>”</small><?php endif; ?>
                    </td>
                    <td data-label="Serviço"><?= e($a['servico_nome']) ?></td>
                    <td data-label="Profissional"><?= e($a['profissional_nome']) ?></td>
                    <td data-label="Status"><span class="selo selo-<?= e($a['status']) ?>"><?= status_label($a['status']) ?></span></td>
                    <td class="acoes">
                        <?php foreach (AgendamentoService::TRANSICOES[$a['status']] as $novo): ?>
                            <form method="post" action="<?= url('admin/status') ?>"
                                  <?= $novo === 'cancelado' ? 'data-confirmar="Cancelar este agendamento?"' : '' ?>>
                                <?= Csrf::campo() ?>
                                <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                                <input type="hidden" name="status" value="<?= e($novo) ?>">
                                <input type="hidden" name="voltar" value="<?= e($voltar) ?>">
                                <button type="submit" class="botao botao-pequeno <?= $novo === 'cancelado' ? 'botao-perigo' : 'botao-secundario' ?>"><?= $acoes[$novo] ?></button>
                            </form>
                        <?php endforeach; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
