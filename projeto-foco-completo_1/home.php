<?php 
// Verifica se o login do agente ainda está válido (3 min)
require_once 'session_check.php'; 

// Calcula o tempo que falta de sessão para passar pro cronômetro em JS
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
    <title>FOCO — Missão</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800&family=Barlow:wght@400;500&display=swap" rel="stylesheet">
    <style>
        /* CSS reset e variáveis de cores com tema cyberpunk verde escuro */
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

        /* ── Barra Lateral ── */
        .sidebar {
            width: 280px;
            flex-shrink: 0;
            background: var(--bg-panel);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            padding: 40px 28px;
            gap: 0;
        }

        .logo-icon { width: 64px; height: 64px; margin-bottom: 14px; }

        .logo-name {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 48px;
            font-weight: 800;
            letter-spacing: 4px;
            color: #fff;
            text-transform: uppercase;
            line-height: 1;
        }

        .logo-sub {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 5px;
            color: var(--green);
            text-transform: uppercase;
            margin-bottom: 40px;
        }

        /* Navegação interna */
        nav { display: flex; flex-direction: column; gap: 6px; flex: 1; }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 3px;
            border: 1px solid transparent;
            text-decoration: none;
            color: var(--text-muted);
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            transition: all .2s;
        }

        .nav-link:hover {
            background: var(--bg-card);
            border-color: var(--border);
            color: var(--text);
        }

        .nav-link.active {
            background: rgba(45,255,110,.06);
            border-color: var(--green-dim);
            color: var(--green);
        }

        /* Painel do cronômetro de sessão */
        .sidebar-timer {
            margin-top: auto;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 3px;
            padding: 14px 16px;
        }

        .sidebar-timer-label {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 3px;
            color: var(--text-muted);
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 4px;
        }

        .sidebar-timer-value {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--green);
            letter-spacing: 2px;
        }

        /* ── Área Principal de Boas-Vindas ── */
        .main {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px;
            position: relative;
        }

        /* Linhas de grade de fundo */
        .main::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(var(--border) 1px, transparent 1px),
                linear-gradient(90deg, var(--border) 1px, transparent 1px);
            background-size: 60px 60px;
            opacity: .15;
            pointer-events: none;
        }

        /* Mira gigante estilizada no meio da tela */
        .hero-mira {
            position: absolute;
            width: 420px;
            height: 420px;
            opacity: .06;
            pointer-events: none;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            text-align: center;
            max-width: 560px;
        }

        .hero-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 80px;
            font-weight: 800;
            letter-spacing: 4px;
            color: #fff;
            text-transform: uppercase;
            line-height: 1;
            margin-bottom: 8px;
        }

        .hero-title span { color: var(--green); }

        .hero-desc {
            color: var(--text-muted);
            font-size: 16px;
            line-height: 1.8;
            margin-bottom: 12px;
        }

        .hero-cta {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--green);
            font-size: 14px;
            font-weight: 500;
            margin-top: 8px;
        }

        /* Barra superior da direita (nome do agente e botão sair) */
        .topbar {
            position: absolute;
            top: 24px;
            right: 32px;
            display: flex;
            align-items: center;
            gap: 16px;
            z-index: 2;
        }

        .topbar-user {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 2px;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .topbar-user strong { color: var(--green); }

        .btn-logout {
            background: transparent;
            border: 1px solid rgba(232,64,64,.4);
            border-radius: 3px;
            color: var(--red);
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 8px 14px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background .2s, border-color .2s;
        }

        .btn-logout:hover {
            background: rgba(232,64,64,.1);
            border-color: var(--red);
        }
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

    <!-- Menus do painel -->
    <nav>
        <a href="home.php" class="nav-link active">
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

    <!-- Cronômetro dinâmico da sessão -->
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

    <!-- Topbar (Boas-vindas ao Agente + Logout) -->
    <div class="topbar">
        <span class="topbar-user">
            Agente: <strong><?= htmlspecialchars($_SESSION['usuario']) ?></strong>
        </span>
        <a href="logout.php" class="btn-logout">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                <polyline points="16,17 21,12 16,7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            Sair
        </a>
    </div>

    <!-- Desenho da mira gigante de fundo -->
    <svg class="hero-mira" viewBox="0 0 420 420" fill="none">
        <circle cx="210" cy="210" r="200" stroke="#2dff6e" stroke-width="1"/>
        <circle cx="210" cy="210" r="140" stroke="#2dff6e" stroke-width="1"/>
        <circle cx="210" cy="210" r="80" stroke="#2dff6e" stroke-width="1"/>
        <circle cx="210" cy="210" r="20" fill="#2dff6e"/>
        <line x1="210" y1="0" x2="210" y2="80" stroke="#2dff6e" stroke-width="1"/>
        <line x1="210" y1="340" x2="210" y2="420" stroke="#2dff6e" stroke-width="1"/>
        <line x1="0" y1="210" x2="80" y2="210" stroke="#2dff6e" stroke-width="1"/>
        <line x1="340" y1="210" x2="420" y2="210" stroke="#2dff6e" stroke-width="1"/>
    </svg>

    <div class="hero-content">
        <div class="hero-title">ENCONTRE<br><span>O PONTO</span></div>
        <p class="hero-desc">
            Seu objetivo é simples:<br>
            encontre o ponto exato na tela<br>
            antes que o tempo acabe.
        </p>
        <p class="hero-cta">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/>
            </svg>
            Você tem 3 minutos. Boa sorte!
        </p>
    </div>
</main>

<script>
    // JS do cronômetro de 3 minutos
    let timeRemaining = <?php echo $tempo_restante; ?>;
    const sessionTimerEl = document.getElementById('sessionTimer');
    
    function formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }

    sessionTimerEl.textContent = formatTime(timeRemaining);

    // Faz a contagem regressiva rodar a cada segundo
    const interval = setInterval(() => {
        timeRemaining--;
        if (timeRemaining <= 0) {
            clearInterval(interval);
            // Expira a sessão no servidor e redireciona
            window.location.href = 'index.php?expirado=1';
        } else {
            sessionTimerEl.textContent = formatTime(timeRemaining);
        }
    }, 1000);
</script>
</body>
</html>
