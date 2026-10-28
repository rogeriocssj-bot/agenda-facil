<?php
/** @var string $conteudo */
/** @var array $flash */
/** @var array|null $usuario */
use App\Core\Csrf;
$rotaAtual = $_GET['r'] ?? 'home';
$ativo = fn(string $r) => str_starts_with($rotaAtual, $r) ? ' class="ativo"' : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="AgendaFácil - agende serviços de beleza e bem-estar online">
    <title><?= e(($titulo ?? '') ? $titulo . ' | ' : '') ?><?= e(app_nome()) ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
<a class="pular" href="#conteudo">Pular para o conteúdo</a>
<header class="topo">
    <div class="container topo-inner">
        <a class="marca" href="<?= url('home') ?>" aria-label="Página inicial">
            <span class="marca-icone" aria-hidden="true">◷</span> <?= e(app_nome()) ?>
        </a>
        <button class="menu-toggle" aria-expanded="false" aria-controls="menu" aria-label="Abrir menu">☰</button>
        <nav id="menu" class="menu" aria-label="Menu principal">
            <?php if ($usuario && $usuario['perfil'] === 'admin'): ?>
                <a href="<?= url('admin') ?>"<?= $rotaAtual === 'admin' ? ' class="ativo"' : '' ?>>Painel</a>
                <a href="<?= url('admin/agenda') ?>"<?= $ativo('admin/agenda') ?>>Agenda</a>
                <a href="<?= url('servicos') ?>"<?= $ativo('servicos') ?>>Serviços</a>
                <a href="<?= url('profissionais') ?>"<?= $ativo('profissionais') ?>>Profissionais</a>
            <?php elseif ($usuario): ?>
                <a href="<?= url('agendamentos/novo') ?>"<?= $ativo('agendamentos/novo') ?>>Agendar</a>
                <a href="<?= url('agendamentos') ?>"<?= $rotaAtual === 'agendamentos' ? ' class="ativo"' : '' ?>>Meus agendamentos</a>
            <?php else: ?>
                <a href="<?= url('login') ?>"<?= $ativo('login') ?>>Entrar</a>
                <a href="<?= url('cadastro') ?>" class="botao botao-pequeno">Criar conta</a>
            <?php endif; ?>
            <?php if ($usuario): ?>
                <span class="usuario-nome">Olá, <?= e(explode(' ', $usuario['nome'])[0]) ?></span>
                <form method="post" action="<?= url('sair') ?>" class="form-inline">
                    <?= Csrf::campo() ?>
                    <button type="submit" class="link-botao">Sair</button>
                </form>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main id="conteudo" class="container">
    <?php foreach ($flash as $tipo => $msg): ?>
        <div class="alerta alerta-<?= e($tipo) ?>" role="<?= $tipo === 'erro' ? 'alert' : 'status' ?>">
            <span><?= e($msg) ?></span>
            <button type="button" class="alerta-fechar" aria-label="Fechar aviso">×</button>
        </div>
    <?php endforeach; ?>

    <?= $conteudo ?>
</main>

<footer class="rodape">
    <div class="container">
        <?= e(app_nome()) ?> &middot; Projeto Integrador Transdisciplinar em Engenharia de Software II
    </div>
</footer>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
<?php limpar_entrada(); ?>
