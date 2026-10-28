<?php /** @var array $servicos */ ?>
<section class="hero">
    <div>
        <h1>Agende seu horário em menos de 1 minuto</h1>
        <p>Escolha o serviço, o profissional e o melhor horário. Sem ligação, sem espera no WhatsApp.</p>
        <div class="hero-acoes">
            <a class="botao" href="<?= url('agendamentos/novo') ?>">Agendar agora</a>
            <?php if (!App\Core\Auth::logado()): ?>
                <a class="botao botao-secundario" href="<?= url('login') ?>">Já tenho conta</a>
            <?php endif; ?>
        </div>
    </div>
    <ol class="passos" aria-label="Como funciona">
        <li><strong>1.</strong> Escolha o serviço</li>
        <li><strong>2.</strong> Escolha o profissional</li>
        <li><strong>3.</strong> Escolha data e horário livres</li>
    </ol>
</section>

<section>
    <h2>Serviços disponíveis</h2>
    <?php if (!$servicos): ?>
        <p class="vazio">Nenhum serviço disponível no momento.</p>
    <?php else: ?>
        <div class="grade-cards">
            <?php foreach ($servicos as $s): ?>
                <article class="card">
                    <h3><?= e($s['nome']) ?></h3>
                    <p class="muted"><?= e($s['descricao'] ?? '') ?></p>
                    <p class="card-meta">
                        <span><?= (int) $s['duracao_min'] ?> min</span>
                        <strong><?= moeda($s['preco']) ?></strong>
                    </p>
                    <a class="botao botao-secundario botao-bloco" href="<?= url('agendamentos/novo', ['servico' => $s['id']]) ?>">Agendar</a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
