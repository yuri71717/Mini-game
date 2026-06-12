<?php
// ==========================================
// BACKEND - CADASTRO DE NOVOS AGENTES
// (Meu amigo do backend pode alterar/integrar com banco MySQL aqui)
// ==========================================

session_start();

$erro = '';
$sucesso = '';

// Processa o formulário de cadastro enviado por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $senha = isset($_POST['senha']) ? $_POST['senha'] : '';
    $confirmar = isset($_POST['confirmar']) ? $_POST['confirmar'] : '';

    // Validações básicas de segurança do cadastro
    if (empty($nome) || empty($email) || empty($senha) || empty($confirmar)) {
        $erro = 'Preencha todos os campos do formulário.';
    } elseif ($senha !== $confirmar) {
        $erro = 'As senhas digitadas não batem.';
    } elseif (strlen($senha) < 4) {
        $erro = 'Crie uma senha de pelo menos 4 dígitos.';
    } else {
        // Por enquanto, salvamos em JSON local para testar sem precisar configurar banco
        $arquivo_json = 'usuarios.json';
        $usuarios = [];

        if (file_exists($arquivo_json)) {
            $conteudo = file_get_contents($arquivo_json);
            $usuarios = json_decode($conteudo, true);
            if (!is_array($usuarios)) {
                $usuarios = [];
            }
        }

        // Evita e-mails duplicados
        $email_existe = false;
        foreach ($usuarios as $usr) {
            if (isset($usr['email']) && strtolower($usr['email']) === strtolower($email)) {
                $email_existe = true;
                break;
            }
        }

        // O e-mail padrão do sistema também fica reservado
        if (strtolower($email) === 'agente@foco.com') {
            $email_existe = true;
        }

        if ($email_existe) {
            $erro = 'Este e-mail já foi cadastrado por outro agente.';
        } else {
            // Guarda o hash seguro da senha
            $novo_usuario = [
                'nome' => $nome,
                'email' => $email,
                'senha' => password_hash($senha, PASSWORD_DEFAULT),
                'criado_em' => date('d/m/Y H:i:s')
            ];

            $usuarios[] = $novo_usuario;

            // Grava os dados de volta no arquivo
            if (file_put_contents($arquivo_json, json_encode($usuarios, JSON_PRETTY_PRINT))) {
                // Sucesso: manda de volta pro Login
                header('Location: index.php?cadastro_sucesso=1');
                exit;
            } else {
                $erro = 'Erro de escrita de arquivos. Verifique as permissões da pasta.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FOCO — Cadastro</title>
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

        /* ── Área Central do Formulário ── */
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

        /* Painel principal do cadastro */
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

        /* Estilo dos campos de texto */
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
        <!-- Logo e Ícone da Mira -->
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
            <p class="card-eyebrow">Criar Conta</p>
            <h1 class="card-title">Cadastro</h1>
            <p class="card-desc">Crie sua conta para iniciar a missão.</p>

            <?php if ($erro): ?>
                <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <!-- Formulário que envia dados de cadastro via POST -->
            <form method="POST" action="cadastro.php">

                <div class="field">
                    <label for="nome">Nome Completo</label>
                    <div class="input-wrap">
                        <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                        </svg>
                        <input type="text" id="nome" name="nome" placeholder="Digite seu nome completo" required>
                    </div>
                </div>

                <div class="field">
                    <label for="email">E-mail</label>
                    <div class="input-wrap">
                        <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="2,4 12,13 22,4"/>
                        </svg>
                        <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>
                    </div>
                </div>

                <div class="field">
                    <label for="senha">Senha</label>
                    <div class="input-wrap">
                        <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                        <input type="password" id="senha" name="senha" placeholder="Crie uma senha" required>
                    </div>
                </div>

                <div class="field">
                    <label for="confirmar">Confirmar Senha</label>
                    <div class="input-wrap">
                        <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                        <input type="password" id="confirmar" name="confirmar" placeholder="Confirme sua senha" required>
                    </div>
                </div>

                <button type="submit" class="btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/>
                    </svg>
                    Criar Conta
                </button>
            </form>

            <div class="sep"><span>ou</span></div>

            <a href="index.php" class="btn-secondary">
                JÁ TEM UMA CONTA? &nbsp;<strong>FAZER LOGIN</strong>
            </a>
        </div>
    </main>

</div>
</body>
</html>
