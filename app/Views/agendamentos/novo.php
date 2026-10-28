<?php
use App\Core\Csrf;
/** @var array $servicos */
$old = App\Core\Session::get('_old', []);
$servicoAtual = (int) ($old['servico_id'] ?? $servicoSelecionado);
?>
<section class="caixa-form caixa-larga">
    <h1>Novo agendamento</h1>
    <form method="post" action="<?= url('agendamentos/novo') ?>" id="form-agendamento" novalidate data-validar
          data-api-profissionais="<?= e(url('api/profissionais')) ?>"
          data-api-horarios="<?= e(url('api/horarios')) ?>"
          data-profissional-old="<?= (int) ($old['profissional_id'] ?? 0) ?>"
          data-hora-old="<?= e($old['hora'] ?? '') ?>">
        <?= Csrf::campo() ?>

        <div class="campo">
            <label for="servico_id">1. Serviço *</label>
            <select id="servico_id" name="servico_id" required>
                <option value="">Selecione...</option>
                <?php foreach ($servicos as $s): ?>
                    <option value="<?= (int) $s['id'] ?>" data-duracao="<?= (int) $s['duracao_min'] ?>"
                        <?= $servicoAtual === (int) $s['id'] ? 'selected' : '' ?>>
                        <?= e($s['nome']) ?> — <?= (int) $s['duracao_min'] ?> min — <?= moeda($s['preco']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?= erro('servico_id') ?>
        </div>

        <div class="campo">
            <label for="profissional_id">2. Profissional *</label>
            <select id="profissional_id" name="profissional_id" required disabled>
                <option value="">Escolha um serviço primeiro</option>
            </select>
            <?= erro('profissional_id') ?>
        </div>

        <div class="campo">
            <label for="data">3. Data *</label>
            <input type="date" id="data" name="data" value="<?= e($old['data'] ?? '') ?>" min="<?= e($hoje) ?>" max="<?= e($maxData) ?>" required>
            <small class="muted">Atendemos de segunda a sábado.</small>
            <?= erro('data') ?>
        </div>

        <fieldset class="campo">
            <legend>4. Horário *</legend>
            <div id="horarios" class="horarios" aria-live="polite">
                <p class="muted">Escolha serviço, profissional e data para ver os horários livres.</p>
            </div>
            <?= erro('hora') ?>
        </fieldset>

        <div class="campo">
            <label for="observacao">Observação (opcional)</label>
            <textarea id="observacao" name="observacao" maxlength="255" rows="2" placeholder="Ex.: prefiro corte com tesoura"><?= e($old['observacao'] ?? '') ?></textarea>
            <?= erro('observacao') ?>
        </div>

        <button type="submit" class="botao botao-bloco" id="btn-confirmar">Confirmar agendamento</button>
    </form>
</section>
