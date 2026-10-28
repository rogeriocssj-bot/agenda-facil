<?php
/** @var array $agendamentos @var array $profissionais @var array $filtro */
?>
<h1>Agenda geral</h1>
<form method="get" class="filtros" aria-label="Filtros da agenda">
    <input type="hidden" name="r" value="admin/agenda">
    <div class="campo">
        <label for="f-data">Data</label>
        <input type="date" id="f-data" name="data" value="<?= e($filtro['data'] ?? '') ?>">
    </div>
    <div class="campo">
        <label for="f-prof">Profissional</label>
        <select id="f-prof" name="profissional">
            <option value="">Todos</option>
            <?php foreach ($profissionais as $p): ?>
                <option value="<?= (int) $p['id'] ?>" <?= $filtro['profissional'] === (int) $p['id'] ? 'selected' : '' ?>><?= e($p['nome']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="campo">
        <label for="f-status">Status</label>
        <select id="f-status" name="status">
            <option value="">Todos</option>
            <?php foreach (['agendado', 'confirmado', 'concluido', 'cancelado'] as $s): ?>
                <option value="<?= $s ?>" <?= $filtro['status'] === $s ? 'selected' : '' ?>><?= status_label($s) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filtros-botoes">
        <button type="submit" class="botao">Filtrar</button>
        <a class="botao botao-secundario" href="<?= url('admin/agenda') ?>">Limpar</a>
    </div>
</form>

<p class="muted"><?= count($agendamentos) ?> agendamento(s) encontrado(s).</p>
<?php $voltar = 'admin/agenda'; require __DIR__ . '/_tabela.php'; ?>
