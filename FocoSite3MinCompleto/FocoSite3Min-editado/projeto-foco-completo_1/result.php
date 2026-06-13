<?php
require_once 'session_check.php';

// garante que o resultado veio do jogo
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['status'])) {
    header('Location: jogar.php');
    exit;
}

$status = $_POST['status'];
$tempo_gasto = isset($_POST['tempo_gasto']) ? intval($_POST['tempo_gasto']) : 0;

// formata o tempo pra mostrar na tela
$minutos = floor($tempo_gasto / 60);
$segundos = $tempo_gasto % 60;
$tempo_formatado = '';
if ($minutos > 0) {
    $tempo_formatado .= $minutos . ($minutos === 1 ? ' minuto e ' : ' minutos e ');
}
$tempo_formatado .= $segundos . ($segundos === 1 ? ' segundo' : ' segundos');

// calcula quanto tempo de sessão ainda sobrou
$tempo_decorrido = time() - $_SESSION['login_time'];
$tempo_restante_sessao = 180 - $tempo_decorrido;
if ($tempo_restante_sessao < 0) {
    $tempo_restante_sessao = 0;
}
$minutos_restantes = floor($tempo_restante_sessao / 60);
$segundos_restantes = $tempo_restante_sessao % 60;
$tempo_sessao_formatado = sprintf('%02d:%02d', $minutos_restantes, $segundos_restantes);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FOCO — Resultado da Missão</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800&family=Barlow:wght@400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg-deep:    #070d0b;
            --bg-panel:   #0c1510;
            --bg-card:    #111c16;
            --green:      #2dff6e;
            --green-dim:  #1a9940;
            --green-glow: rgba(45,255,110,.18);
            --border:     #1e3326;
            --text:       #d4e8da;
            --text-muted: #5a7a64;
            --red:        #e84040;
            --red-glow:   rgba(232,64,64,.18);
        }

        body {
            font-family: 'Barlow', sans-serif;
            background: var(--bg-deep);
            color: var(--text);
            min-height: 100vh;
            display: flex;
        }

        /* barra lateral */
        .sidebar {
            width: 280px;
            flex-shrink: 0;
            background: var(--bg-panel);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            padding: 40px 28px;
        }

        .logo-icon { width: 64px; height: 64px; margin-bottom: 14px; }

        .logo-name {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 48px; font-weight: 800;
            letter-spacing: 4px; color: #fff;
            text-transform: uppercase; line-height: 1;
        }

        .logo-sub {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 11px; font-weight: 600;
            letter-spacing: 5px; color: var(--green);
            text-transform: uppercase; margin-bottom: 40px;
        }

        nav { display: flex; flex-direction: column; gap: 6px; flex: 1; }

        .nav-link {
            display: flex; align-items: center; gap: 12px;
            padding: 14px 16px; border-radius: 3px;
            border: 1px solid transparent;
            text-decoration: none; color: var(--text-muted);
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 15px; font-weight: 600;
            letter-spacing: 2px; text-transform: uppercase;
            transition: all .2s;
        }

        .nav-link:hover { background: var(--bg-card); border-color: var(--border); color: var(--text); }
        .nav-link.active { background: rgba(45,255,110,.06); border-color: var(--green-dim); color: var(--green); }

        .sidebar-timer {
            margin-top: auto;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 3px; padding: 14px 16px;
        }

        .sidebar-timer-label {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 10px; font-weight: 600;
            letter-spacing: 3px; color: var(--text-muted);
            text-transform: uppercase;
            display: flex; align-items: center; gap: 6px;
            margin-bottom: 4px;
        }

        .sidebar-timer-value {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 28px; font-weight: 700;
            color: var(--green); letter-spacing: 2px;
        }

        /* área principal */
        .main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 24px;
            position: relative;
        }

        .main::before {
            content: '';
            position: absolute; inset: 0;
            background-image:
                linear-gradient(var(--border) 1px, transparent 1px),
                linear-gradient(90deg, var(--border) 1px, transparent 1px);
            background-size: 60px 60px; opacity: .12;
            pointer-events: none;
        }

        /* card de resultado */
        .card {
            width: 100%;
            max-width: 580px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 44px;
            position: relative;
            z-index: 1;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        }

        .success-icon {
            width: 80px; height: 80px;
            margin: 0 auto 20px auto;
            background: rgba(45,255,110,.06);
            border: 1.5px solid var(--green);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: var(--green);
            box-shadow: 0 0 20px var(--green-glow);
        }

        .card-eyebrow {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 13px; font-weight: 600;
            letter-spacing: 5px; color: var(--green);
            text-transform: uppercase; margin-bottom: 8px;
        }

        .card-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 48px; font-weight: 800;
            letter-spacing: 3px; color: #fff;
            text-transform: uppercase; line-height: 1.1;
            margin-bottom: 12px;
        }

        .card-desc { color: var(--text-muted); font-size: 15px; margin-bottom: 32px; line-height: 1.6; }

        /* tabela com os dados da partida */
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 36px;
            text-align: left;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 3px;
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .stats-table tr {
            border-bottom: 1px solid var(--border);
        }
        .stats-table tr:last-child {
            border-bottom: none;
        }

        .stats-table td {
            padding: 14px 18px;
            font-size: 14px;
        }

        .stats-table td.label {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--green);
            width: 40%;
        }

        .stats-table td.val {
            color: #fff;
            font-weight: 500;
        }

        /* botões */
        .actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn-group-row {
            display: flex;
            gap: 12px;
        }

        .btn-group-row a {
            flex: 1;
        }

        .btn-primary {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: var(--green);
            color: var(--bg-deep);
            border: none;
            border-radius: 3px;
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            padding: 16px;
            cursor: pointer;
            text-decoration: none;
            transition: box-shadow .2s, opacity .2s;
        }
        .btn-primary:hover {
            box-shadow: 0 0 24px var(--green-glow);
        }

        .btn-outline {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: transparent;
            border: 1px solid var(--border);
            border-radius: 3px;
            color: var(--text-muted);
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 15px;
            cursor: pointer;
            text-decoration: none;
            transition: border-color .2s, color .2s;
        }
        .btn-outline:hover {
            border-color: var(--green-dim);
            color: #fff;
        }

        .btn-danger-outline {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: transparent;
            border: 1px solid rgba(232,64,64,.3);
            border-radius: 3px;
            color: var(--red);
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 15px;
            cursor: pointer;
            text-decoration: none;
            transition: background .2s, border-color .2s;
        }
        .btn-danger-outline:hover {
            background: rgba(232,64,64,.06);
            border-color: var(--red);
        }
    </style>
</head>
<body>

<!-- sidebar -->
<aside class="sidebar">
    <svg class="logo-icon" viewBox="0 0 72 72" fill="none">
        <circle cx="36" cy="36" r="34" stroke="#2dff6e" stroke-width="1.5" opacity=".3"/>
        <circle cx="36" cy="36" r="24" stroke="#2dff6e" stroke-width="1.5" opacity=".5"/>
        <circle cx="36" cy="36" r="6" fill="#2dff6e" opacity=".9"/>
        <line x1="36" y1="2" x2="36" y2="18" stroke="#2dff6e" stroke-width="1.5"/>
        <line x1="36" y1="54" x2="36" y2="70" stroke="#2dff6e" stroke-width="1.5"/>
        <line x1="2" y1="36" x2="18" y2="36" stroke="#2dff6e" stroke-width="1.5"/>
        <line x1="54" y1="36" x2="70" y2="36" stroke="#2dff6e" stroke-width="1.5"/>
    </svg>
    <div class="logo-name">FOCO</div>
    <div class="logo-sub">Encontre o Ponto</div>

    <nav>
        <a href="home.php" class="nav-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/>
            </svg>
            Início
        </a>
        <a href="jogar.php" class="nav-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <polygon points="5,3 19,12 5,21"/>
            </svg>
            Jogar
        </a>
        <a href="instrucoes.php" class="nav-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <line x1="7" y1="8" x2="17" y2="8"/>
                <line x1="7" y1="12" x2="17" y2="12"/>
                <line x1="7" y1="16" x2="13" y2="16"/>
            </svg>
            Instruções
        </a>
    </nav>

    <div class="sidebar-timer">
        <div class="sidebar-timer-label">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/>
            </svg>
            Tempo da Sessão
        </div>
        <div class="sidebar-timer-value" id="sessionTimer"><?= $tempo_sessao_formatado ?></div>
    </div>
</aside>

<!-- conteúdo principal -->
<main class="main">

    <div class="card">
        <div class="success-icon">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                <polyline points="22,4 12,14.01 9,11.01"/>
            </svg>
        </div>

        <p class="card-eyebrow">Status do Briefing</p>
        <h1 class="card-title">Missão Completa!</h1>
        <p class="card-desc">Parabéns, agente. Você identificou o ponto correto no mapa urbano.</p>

        <table class="stats-table">
            <tr>
                <td class="label">Agente</td>
                <td class="val"><?= htmlspecialchars($_SESSION['usuario']) ?></td>
            </tr>
            <tr>
                <td class="label">Início do Login</td>
                <td class="val"><?= htmlspecialchars($_SESSION['login_date_time']) ?></td>
            </tr>
            <tr>
                <td class="label">Tempo de Busca</td>
                <td class="val"><?= $tempo_formatado ?></td>
            </tr>
            <tr>
                <td class="label">Sessão Restante</td>
                <td class="val" style="color: var(--green); font-weight: bold;"><?= $tempo_sessao_formatado ?></td>
            </tr>
        </table>

        <div class="actions">
            <div class="btn-group-row">
                <a href="jogar.php" class="btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 11-.57-8.38l5.67-5.67"/>
                    </svg>
                    Jogar Denovo
                </a>
                <a href="home.php" class="btn-outline">
                    Voltar ao Início
                </a>
            </div>
            
            <a href="logout.php" class="btn-danger-outline">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                    <polyline points="16,17 21,12 16,7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                Encerrar Sessão (Logout)
            </a>
        </div>
    </div>

</main>

<script>
    // atualiza o timer da sessão no js
    let timeRemaining = <?php echo $tempo_restante_sessao; ?>;
    const sessionTimerEl = document.getElementById('sessionTimer');
    
    function formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }

    const interval = setInterval(() => {
        timeRemaining--;
        if (timeRemaining <= 0) {
            clearInterval(interval);
            window.location.href = 'index.php?expirado=1';
        } else {
            sessionTimerEl.textContent = formatTime(timeRemaining);
        }
    }, 1000);
</script>

</body>
</html>
