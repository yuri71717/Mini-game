<?php
// Validação de sessão do usuário
// Importante: esse arquivo é incluído no topo de todas as telas protegidas do site.

session_start();

// Tempo limite da sessão em segundos (3 minutos)
$SESSION_DURACAO = 180; 

// Se o usuário não estiver logado ou não tiver a marcação de tempo, manda direto pro login
if (!isset($_SESSION['usuario']) || !isset($_SESSION['login_time'])) {
    header('Location: index.php');
    exit;
}

// Verifica se passou dos 3 minutos limite
if ((time() - $_SESSION['login_time']) >= $SESSION_DURACAO) {
    // Passou do tempo: destrói a sessão e avisa na tela de login
    session_destroy();
    header('Location: index.php?expirado=1');
    exit;
}
?>
