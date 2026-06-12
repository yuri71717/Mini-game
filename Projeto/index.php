<?php
// ==========================================
// BACKEND - SISTEMA DE AUTENTICAÇÃO
// (Meu amigo do backend pode alterar/integrar com banco MySQL aqui)
// ==========================================

session_start();

// Se o usuário já tiver login ativo dentro dos 3 minutos, manda direto pra home.php
if (isset($_SESSION['usuario']) && isset($_SESSION['login_time'])) {
    $SESSION_DURACAO = 180;
    if ((time() - $_SESSION['login_time']) < $SESSION_DURACAO) {
        header('Location: home.php');
        exit;
    } else {
        session_destroy();
    }
}

$erro = '';
$sucesso = '';

// Recebe alertas via parâmetro GET na URL
if (isset($_GET['cadastro_sucesso'])) {
    $sucesso = 'Conta criada! Use suas novas credenciais para acessar.';
}
if (isset($_GET['expirado'])) {
    $erro = 'A sessão limite de 3 minutos acabou. Faça login novamente.';
}
if (isset($_GET['logout'])) {
    $sucesso = 'Sessão encerrada com sucesso.';
}

// Credenciais fixas padrão do projeto (conforme briefing do trabalho)
$EMAIL_PADRAO = 'agente@foco.com';
$SENHA_PADRAO = 'foco123';

// Trata o envio dos dados do formulário de login via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $senha = isset($_POST['senha']) ? $_POST['senha'] : '';

    $autenticado = false;
    $nome_usuario = '';

    // 1. Tenta validar a credencial padrão pré-definida em código
    if (strtolower($email) === strtolower($EMAIL_PADRAO) && $senha === $SENHA_PADRAO) {
        $autenticado = true;
        $nome_usuario = 'Agente Foco';
    } else {
        // 2. Senão, tenta ler do banco temporário JSON (usuarios.json)
        $arquivo_json = 'usuarios.json';
        if (file_exists($arquivo_json)) {
            $conteudo = file_get_contents($arquivo_json);
            $usuarios = json_decode($conteudo, true);
            
            if (is_array($usuarios)) {
                foreach ($usuarios as $usr) {
                    if (isset($usr['email']) && strtolower($usr['email']) === strtolower($email)) {
                        // Verifica o hash da senha cadastrada
                        if (password_verify($senha, $usr['senha'])) {
                            $autenticado = true;
                            $nome_usuario = $usr['nome'];
                            break;
                        }
                    }
                }
            }
        }
    }

    // Se bater as credenciais, inicia a sessão do agente
    if ($autenticado) {
        $_SESSION['usuario'] = $nome_usuario;
        $_SESSION['email'] = $email;
        $_SESSION['login_time'] = time(); // timestamp do login para controle dos 3 minutos
        $_SESSION['login_date_time'] = date('d/m/Y H:i:s'); // data formatada para mostrar no resultado
        
        header('Location: home.php');
        exit;
    } else {
        $erro = 'E-mail ou senha incorretos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FOCO — Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800&family=Barlow:wght@400;500&display=swap" rel="stylesheet">
    <style>
        /* CSS reset e variáveis de cores com tema cyberpunk verde escuro */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg-deep:    #070d0b;
            --bg-panel:   #0c1510;
            --bg-card:    #111c16;
            --bg-input:   #0a1209;
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

        .split { display: flex; width: 100%; min-height: 100vh; }

        /* ── Barra Lateral Esquerda ── */
        .sidebar {
            width: 300px;
            flex-shrink: 0;
            background: var(--bg-panel);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            padding: 48px 36px;
        }

        .logo-icon { width: 72px; height: 72px; margin-bottom: 16px; }

        .logo-name {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 56px;
            font-weight: 800;
            letter-spacing: 4px;
            color: #fff;
            text-transform: uppercase;
            line-height: 1;
        }

        .logo-sub {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 6px;
            color: var(--green);
            text-transform: uppercase;
            margin-bottom: 56px;
        }

        .sidebar-desc  { font-size: 14px; line-height: 1.7; color: var(--text-muted); margin-bottom: 20px; }
        .sidebar-highlight { color: var(--green); font-weight: 600; font-size: 14px; line-height: 1.6; }

        /* ── Área Central do Login ── */
        .main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 24px;
            background: var(--bg-deep);
            position: relative;
        }

        /* Linhas de grade do plano de fundo */
        .main::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(var(--border) 1px, transparent 1px),
                linear-gradient(90deg, var(--border) 1px, transparent 1px);
            background-size: 60px 60px;
            opacity: .2;
            pointer-events: none;
        }

        /* Painel principal do login */
        .card {
            width: 100%;
            max-width: 520px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 48px 44px;
            position: relative;
            z-index: 1;
        }

        .card-eyebrow {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 5px;
            color: var(--green);
            text-transform: uppercase;
            text-align: center;
            margin-bottom: 8px;
        }

        .card-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 52px;
            font-weight: 800;
            letter-spacing: 3px;
            color: #fff;
            text-transform: uppercase;
            text-align: center;
            margin-bottom: 6px;
        }

        .card-desc { text-align: center; color: var(--text-muted); font-size: 14px; margin-bottom: 36px; }

        .alert {
            padding: 12px 16px;
            border-radius: 3px;
            font-size: 13px;
            margin-bottom: 24px;
            line-height: 1.5;
        }
        .alert-error {
            background: rgba(232,64,64,.08);
            border: 1px solid var(--red);
            color: #ff6b6b;
        }
        .alert-success {
            background: rgba(45,255,110,.08);
            border: 1px solid var(--green);
            color: var(--green);
        }

        /* Campos e Input do formulário */
        .field { margin-bottom: 20px; }

        .field label {
            display: block;
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 3px;
            color: var(--green);
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .input-wrap { position: relative; }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
        }

        .field input {
            width: 100%;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 3px;
            color: var(--text);
            font-family: 'Barlow', sans-serif;
            font-size: 14px;
            padding: 14px 14px 14px 42px;
            outline: none;
            transition: border-color .2s;
        }

        .field input::placeholder { color: var(--text-muted); }
        .field input:focus { border-color: var(--green-dim); box-shadow: 0 0 0 3px var(--green-glow); }

        /* Botões */
        .btn-primary {
            width: 100%;
            background: transparent;
            border: 1.5px solid var(--green);
            border-radius: 3px;
            color: var(--green);
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            padding: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: background .2s, color .2s, box-shadow .2s;
            margin-top: 8px;
        }

        .btn-primary:hover {
            background: var(--green);
            color: var(--bg-deep);
            box-shadow: 0 0 24px var(--green-glow);
        }

        .sep { display: flex; align-items: center; gap: 12px; margin: 24px 0; }
        .sep::before, .sep::after { content: ''; flex: 1; height: 1px; background: var(--border); }
        .sep span { color: var(--text-muted); font-size: 12px; }

        .btn-secondary {
            width: 100%;
            background: transparent;
            border: 1px solid var(--border);
            border-radius: 3px;
            color: var(--text-muted);
            font-family: 'Barlow', sans-serif;
            font-size: 14px;
            padding: 14px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: block;
            transition: border-color .2s, color .2s;
        }
        .btn-secondary strong { color: var(--green); }
        .btn-secondary:hover { border-color: var(--green-dim); color: var(--text); }
    </style>
</head>
<body>
<div class="split">

    <aside class="sidebar">
        <!-- Logo e Ícone de Mira -->
        <svg class="logo-icon" viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg">
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

        <p class="sidebar-desc">Sua missão é simples:<br>encontre o ponto exato<br>antes que o tempo acabe.</p>
        <p class="sidebar-highlight">Você tem 3 minutos.<br>Boa sorte!</p>
    </aside>

    <main class="main">
        <div class="card">
            <p class="card-eyebrow">Acesso Restrito</p>
            <h1 class="card-title">Login</h1>
            <p class="card-desc">Entre com as credenciais do agente.</p>

            <?php if ($erro): ?>
                <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <?php if ($sucesso): ?>
                <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
            <?php endif; ?>

            <!-- Formulário que envia o login via POST -->
            <form method="POST" action="index.php">
                <div class="field">
                    <label for="email">E-mail</label>
                    <div class="input-wrap">
                        <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="2,4 12,13 22,4"/>
                        </svg>
                        <input type="email" id="email" name="email" placeholder="Ex: agente@foco.com" required>
                    </div>
                </div>

                <div class="field">
                    <label for="senha">Senha</label>
                    <div class="input-wrap">
                        <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                        <input type="password" id="senha" name="senha" placeholder="Sua senha" required>
                    </div>
                </div>

                <button type="submit" class="btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/>
                    </svg>
                    Acessar Painel
                </button>
            </form>

            <div class="sep"><span>ou</span></div>

            <a href="cadastro.php" class="btn-secondary">
                NÃO TEM CONTA? &nbsp;<strong>CADASTRAR-SE</strong>
            </a>
        </div>
    </main>

</div>
</body>
</html>
