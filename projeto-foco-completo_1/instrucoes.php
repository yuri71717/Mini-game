<?php 
require_once 'session_check.php'; 
$tempo_decorrido = time() - $_SESSION['login_time'];
$tempo_restante = 180 - $tempo_decorrido;
if ($tempo_restante < 0) {
    $tempo_restante = 0;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FOCO — Instruções</title>
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
        }

        body {
            font-family: 'Barlow', sans-serif;
            background: var(--bg-deep);
            color: var(--text);
            min-height: 100vh;
            display: flex;
        }

        /* ── Sidebar ── */
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

        /* ── Main ── */
        .main {
            flex: 1; padding: 56px 64px;
            position: relative; overflow-y: auto;
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

        /* Topbar */
        .topbar {
            display: flex; align-items: center;
            justify-content: space-between;
            margin-bottom: 48px; position: relative; z-index: 1;
        }

        .page-eyebrow {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 12px; font-weight: 600;
            letter-spacing: 5px; color: var(--green);
            text-transform: uppercase;
        }

        .page-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 56px; font-weight: 800;
            letter-spacing: 3px; color: #fff;
            text-transform: uppercase; line-height: 1;
            margin-bottom: 4px;
        }

        .btn-logout {
            background: transparent;
            border: 1px solid rgba(232,64,64,.4); border-radius: 3px;
            color: var(--red);
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 12px; font-weight: 600;
            letter-spacing: 2px; text-transform: uppercase;
            padding: 8px 14px; text-decoration: none;
            display: flex; align-items: center; gap: 6px;
            transition: background .2s, border-color .2s;
        }
        .btn-logout:hover { background: rgba(232,64,64,.1); border-color: var(--red); }

        /* ── Passos ── */
        .steps { position: relative; z-index: 1; max-width: 720px; }

        .step {
            display: flex; gap: 24px; margin-bottom: 36px;
            align-items: flex-start;
        }

        .step-num {
            flex-shrink: 0;
            width: 48px; height: 48px;
            background: rgba(45,255,110,.06);
            border: 1px solid var(--green-dim);
            border-radius: 3px;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 22px; font-weight: 800;
            color: var(--green); letter-spacing: 1px;
        }

        .step-body {}

        .step-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 20px; font-weight: 700;
            letter-spacing: 2px; color: #fff;
            text-transform: uppercase; margin-bottom: 6px;
        }

        .step-desc { font-size: 15px; color: var(--text-muted); line-height: 1.7; }

        /* Divider */
        .divider {
            height: 1px; background: var(--border);
            margin: 40px 0; max-width: 720px;
            position: relative; z-index: 1;
        }

        /* Info boxes */
        .info-grid {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 16px; max-width: 720px;
            position: relative; z-index: 1; margin-bottom: 40px;
        }

        .info-box {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 3px; padding: 20px;
        }

        .info-box-label {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 11px; font-weight: 600;
            letter-spacing: 3px; color: var(--green);
            text-transform: uppercase; margin-bottom: 6px;
            display: flex; align-items: center; gap: 6px;
        }

        .info-box-val {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 32px; font-weight: 800;
            color: #fff; letter-spacing: 2px;
        }

        /* CTA */
        .btn-jogar {
            display: inline-flex; align-items: center; gap: 10px;
            background: var(--green); color: var(--bg-deep);
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 16px; font-weight: 700;
            letter-spacing: 3px; text-transform: uppercase;
            padding: 18px 36px; border-radius: 3px;
            text-decoration: none;
            position: relative; z-index: 1;
            transition: box-shadow .2s;
        }
        .btn-jogar:hover { box-shadow: 0 0 32px var(--green-glow); }
    </style>
</head>
<body>

<!-- ── SIDEBAR ── -->
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
        <a href="instrucoes.php" class="nav-link active">
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
            Tempo Limite
        </div>
        <div class="sidebar-timer-value" id="sessionTimer">03:00</div>
    </div>
</aside>

<!-- ── MAIN ── -->
<main class="main">

    <div class="topbar">
        <div>
            <p class="page-eyebrow">Briefing do Agente</p>
            <h1 class="page-title">Instruções</h1>
        </div>
        <a href="logout.php" class="btn-logout">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                <polyline points="16,17 21,12 16,7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            Sair
        </a>
    </div>

    <!-- Info rápida -->
    <div class="info-grid">
        <div class="info-box">
            <div class="info-box-label">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/>
                </svg>
                Duração da Missão
            </div>
            <div class="info-box-val">03:00</div>
        </div>
        <div class="info-box">
            <div class="info-box-label">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/>
                </svg>
                Pontos a Encontrar
            </div>
            <div class="info-box-val">01</div>
        </div>
    </div>

    <!-- Passos -->
    <div class="steps">

        <div class="step">
            <div class="step-num">01</div>
            <div class="step-body">
                <div class="step-title">Observe a Cena</div>
                <p class="step-desc">Ao iniciar a missão, você verá uma imagem da cidade. Examine cada parte da tela com atenção — o ponto está escondido em algum lugar da imagem.</p>
            </div>
        </div>

        <div class="step">
            <div class="step-num">02</div>
            <div class="step-body">
                <div class="step-title">Encontre o Ponto</div>
                <p class="step-desc">O ponto-alvo é pequeno e pode estar camuflado com o ambiente ao redor. Passe o mouse pela tela e fique atento a qualquer elemento que pareça diferente.</p>
            </div>
        </div>

        <div class="step">
            <div class="step-num">03</div>
            <div class="step-body">
                <div class="step-title">Clique Rápido</div>
                <p class="step-desc">Ao encontrar o ponto, clique nele para completar a missão. Você tem exatamente 3 minutos — após esse tempo a sessão é encerrada automaticamente.</p>
            </div>
        </div>

        <div class="step">
            <div class="step-num">04</div>
            <div class="step-body">
                <div class="step-title">Atenção ao Timer</div>
                <p class="step-desc">Observe o cronômetro na barra lateral. Quando restar menos de 30 segundos, o timer ficará vermelho. Se o tempo acabar sem que você encontre o ponto, a sessão é encerrada.</p>
            </div>
        </div>

    </div>

    <div class="divider"></div>

    <a href="jogar.php" class="btn-jogar">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polygon points="5,3 19,12 5,21"/>
        </svg>
        Iniciar Missão
    </a>

</main>

<script>
    let timeRemaining = <?php echo $tempo_restante; ?>;
    const sessionTimerEl = document.getElementById('sessionTimer');
    
    function formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }

    sessionTimerEl.textContent = formatTime(timeRemaining);

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
