<?php
// garante que só acessa quem está logado
require_once 'session_check.php';

// calcula o tempo que sobrou pra passar pro js
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
    <title>FOCO — Jogar</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800&family=Barlow:wght@400;500&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        /* estilos da arena do jogo */
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
            --red-glow:   rgba(232,64,64,.15);
        }

        body {
            font-family: 'Barlow', sans-serif;
            background: var(--bg-deep);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            overflow: hidden;
        }

        /* barra lateral */
        .sidebar {
            width: 300px;
            flex-shrink: 0;
            background: var(--bg-panel);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            padding: 40px 28px;
            z-index: 10;
        }

        .logo-icon { width: 56px; height: 56px; margin-bottom: 12px; }

        .logo-name {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 44px;
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
            margin-bottom: 36px;
        }

        .sidebar-section { margin-bottom: 28px; }

        .section-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 3px;
            color: var(--green);
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .section-desc {
            font-size: 13px;
            line-height: 1.6;
            color: var(--text-muted);
        }

        .dica-box {
            background: rgba(45,255,110,.02);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 16px;
            margin-bottom: auto;
            position: relative;
        }

        .dica-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 3px;
            color: var(--green);
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        /* painel do cronômetro */
        .timer-container {
            margin-top: auto;
            margin-bottom: 24px;
        }

        .timer-label {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 3px;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .timer-display {
            font-family: 'Share Tech Mono', monospace;
            font-size: 48px;
            color: var(--green);
            background: #060e0a;
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 8px 16px;
            text-align: center;
            letter-spacing: 2px;
            box-shadow: inset 0 0 10px rgba(0,0,0,0.8);
            transition: color 0.3s, border-color 0.3s;
        }

        /* pisca vermelho quando o tempo tá acabando */
        .timer-display.danger {
            color: var(--red);
            border-color: var(--red);
            box-shadow: inset 0 0 10px rgba(0,0,0,0.8), 0 0 8px var(--red-glow);
            animation: pulse-red 1s infinite alternate;
        }

        @keyframes pulse-red {
            from { box-shadow: inset 0 0 10px rgba(0,0,0,0.8), 0 0 4px rgba(232,64,64,.2); }
            to   { box-shadow: inset 0 0 10px rgba(0,0,0,0.8), 0 0 12px rgba(232,64,64,.4); }
        }

        .btn-desistir {
            width: 100%;
            background: transparent;
            border: 1px solid rgba(232,64,64,.4);
            border-radius: 4px;
            color: var(--red);
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            padding: 14px;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background .2s, border-color .2s;
        }

        .btn-desistir:hover {
            background: rgba(232,64,64,.08);
            border-color: var(--red);
        }

        /* área do jogo */
        .game-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            position: relative;
            background: #030605;
        }

        /* hud do topo */
        .hud-top {
            position: absolute;
            top: 24px;
            left: 24px;
            right: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 5;
            pointer-events: none;
        }

        .mission-status {
            background: rgba(12,21,16,0.85);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 10px 16px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            pointer-events: auto;
            backdrop-filter: blur(4px);
        }

        .status-header {
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 2px;
            color: var(--green);
            text-transform: uppercase;
        }

        .status-header span {
            width: 8px; height: 8px;
            background: var(--green);
            border-radius: 50%;
            box-shadow: 0 0 6px var(--green);
            display: inline-block;
        }

        .status-dots { display: flex; gap: 6px; }

        .status-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--border);
        }

        .status-dot.active {
            background: var(--green);
            box-shadow: 0 0 4px var(--green);
        }

        .zoom-badge {
            background: rgba(12,21,16,0.85);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 12px;
            font-weight: 600;
            color: var(--text);
            pointer-events: auto;
            backdrop-filter: blur(4px);
        }

        .zoom-badge svg { color: var(--green); }

        .viewport {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 80px 40px;
            position: relative;
        }

        /* container da imagem */
        .image-container {
            position: relative;
            max-width: 100%;
            max-height: 80vh;
            border: 1px solid var(--border);
            box-shadow: 0 20px 50px rgba(0,0,0,0.8);
            border-radius: 2px;
            overflow: hidden;
            cursor: crosshair; /* cursor de mira */
        }

        .game-image {
            display: block;
            max-width: 100%;
            max-height: 80vh;
            width: auto;
            height: auto;
            object-fit: contain;
            user-select: none;
            -webkit-user-drag: none;
            pointer-events: none; /* o container que recebe o clique, não a imagem */
        }

        /* o ponto alvo invisível */
        .target-point {
            position: absolute;
            left: 56.4%;
            top: 51.2%;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: transparent;
            cursor: crosshair;
            z-index: 10;
            transform: translate(-50%, -50%);
        }

        /* animação de sonar ao acertar */
        .sonar-effect {
            position: absolute;
            border: 2px solid var(--green);
            border-radius: 50%;
            background: rgba(45,255,110,0.2);
            animation: sonar 0.6s ease-out forwards;
            pointer-events: none;
            z-index: 9;
            transform: translate(-50%, -50%);
        }

        @keyframes sonar {
            0%   { width: 10px;  height: 10px;  opacity: 1; }
            100% { width: 60px;  height: 60px;  opacity: 0; }
        }

        /* pontinho vermelho de erro */
        .miss-dot {
            position: absolute;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--red);
            box-shadow: 0 0 8px var(--red);
            pointer-events: none;
            transform: translate(-50%, -50%);
            transition: opacity 0.4s;
        }

        .hud-bottom {
            position: absolute;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(12,21,16,0.9);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 8px 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 5;
            pointer-events: none;
            backdrop-filter: blur(4px);
        }

        .hud-bottom span {
            font-size: 13px;
            color: var(--text-muted);
            letter-spacing: 0.5px;
        }

        .hud-bottom svg { color: var(--green); }
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

    <div class="sidebar-section" style="margin-top: 10px;">
        <div class="section-title">Objetivo</div>
        <p class="section-desc">Encontre o ponto exato em algum lugar da imagem antes que o tempo acabe.</p>
    </div>

    <div class="dica-box">
        <div class="dica-title">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A5.5 5.5 0 0012 3 5.5 5.5 0 006.5 8.5c0 1.3.5 2.6 1.5 3.5.8.8 1.3 1.5 1.5 2.5"/>
                <line x1="9" y1="18" x2="15" y2="18"/>
                <line x1="10" y1="22" x2="14" y2="22"/>
            </svg>
            Dica
        </div>
        <p class="section-desc" style="color: var(--text-muted);">O ponto está camuflado em um dos prédios centrais, próximo às janelas iluminadas.</p>
    </div>

    <div class="timer-container">
        <div class="timer-label">Tempo Restante</div>
        <div class="timer-display" id="countdown">03:00</div>
    </div>

    <a href="home.php" class="btn-desistir">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/>
            <line x1="4" y1="22" x2="4" y2="15"/>
        </svg>
        Desistir
    </a>
</aside>

<!-- área do jogo -->
<main class="game-area">
    
    <div class="hud-top">
        <div class="mission-status">
            <div class="status-header">
                <span></span>Missão em Andamento
            </div>
            <div class="status-dots">
                <div class="status-dot active"></div>
                <div class="status-dot"></div>
                <div class="status-dot"></div>
                <div class="status-dot"></div>
                <div class="status-dot"></div>
                <div class="status-dot"></div>
                <div class="status-dot"></div>
                <div class="status-dot"></div>
            </div>
        </div>

        <div class="zoom-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                <line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/>
            </svg>
            100%
        </div>
    </div>

    <div class="viewport">
        <div class="image-container" id="gameContainer">
            <!-- ponto alvo invisível -->
            <div class="target-point" id="target"></div>
            
            <?php if (file_exists('city.png')): ?>
                <img src="city.png" alt="Cidade" class="game-image" id="gameImage">
            <?php else: ?>
                <div class="game-image" id="gameImage" style="width:800px;height:500px;background:radial-gradient(circle,#0e1f16 0%,#030605 100%);display:flex;align-items:center;justify-content:center;border:1px solid var(--border);">
                    <span style="color:var(--text-muted);font-size:14px;letter-spacing:2px;">CARREGANDO MAPA DA CIDADE...</span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="hud-bottom">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
        </svg>
        <span>Clique no ponto exato para completar a missão.</span>
    </div>

</main>

<!-- formulário oculto pra enviar o tempo pro result.php -->
<form id="resultForm" method="POST" action="result.php" style="display: none;">
    <input type="hidden" name="status" value="sucesso">
    <input type="hidden" name="tempo_gasto" id="tempoGastoInput">
</form>

<script>
    // cronômetro do jogo
    let timeRemaining = <?php echo $tempo_restante; ?>;
    const initialTime = 180;
    
    const timerElement   = document.getElementById('countdown');
    const targetElement  = document.getElementById('target');
    const container      = document.getElementById('gameContainer');
    const resultForm     = document.getElementById('resultForm');
    const tempoGastoInput = document.getElementById('tempoGastoInput');

    let gameOver = false; // evita submeter duas vezes

    function formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }

    function updateTimer() {
        timerElement.textContent = formatTime(timeRemaining);
        
        // fica vermelho se faltar menos de 30 segundos
        if (timeRemaining <= 30) {
            timerElement.classList.add('danger');
        } else {
            timerElement.classList.remove('danger');
        }

        if (timeRemaining <= 0) {
            clearInterval(timerInterval);
            window.location.href = 'index.php?expirado=1';
        }
        
        timeRemaining--;
    }

    updateTimer();
    const timerInterval = setInterval(updateTimer, 1000);

    // clique no ponto correto
    targetElement.addEventListener('click', function(e) {
        if (gameOver) return;
        gameOver = true;

        e.stopPropagation();

        const rect = container.getBoundingClientRect();
        const clickX = e.clientX - rect.left;
        const clickY = e.clientY - rect.top;

        // animação de sonar
        const sonar = document.createElement('div');
        sonar.classList.add('sonar-effect');
        sonar.style.left = `${clickX}px`;
        sonar.style.top  = `${clickY}px`;
        container.appendChild(sonar);

        // calcula o tempo que levou
        const tempoGasto = initialTime - timeRemaining;
        tempoGastoInput.value = tempoGasto;

        // espera a animação e envia
        setTimeout(() => {
            resultForm.submit();
        }, 650);
    });

    // cliques errados (fora do ponto)
    container.addEventListener('click', function(e) {
        if (gameOver) return;
        // se clicou no ponto certo, ignora aqui
        if (e.target === targetElement) return;

        const rect = container.getBoundingClientRect();
        const clickX = e.clientX - rect.left;
        const clickY = e.clientY - rect.top;

        // pontinho vermelho de erro
        const dot = document.createElement('div');
        dot.classList.add('miss-dot');
        dot.style.left = `${clickX}px`;
        dot.style.top  = `${clickY}px`;
        container.appendChild(dot);

        setTimeout(() => {
            dot.style.opacity = '0';
            setTimeout(() => dot.remove(), 400);
        }, 300);
    });
</script>
</body>
</html>
