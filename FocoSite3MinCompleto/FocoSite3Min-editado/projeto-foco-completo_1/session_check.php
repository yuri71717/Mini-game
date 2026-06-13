<?php
// validação de sessão
// incluído no topo de todas as páginas protegidas

session_start();

// 3 minutos em segundos
$SESSION_DURACAO = 180; 

// se não tiver logado, manda pro login
if (!isset($_SESSION['usuario']) || !isset($_SESSION['login_time'])) {
    header('Location: index.php');
    exit;
}

// verifica se o tempo expirou
if ((time() - $_SESSION['login_time']) >= $SESSION_DURACAO) {
    // tempo esgotado: encerra sessão e redireciona
    session_destroy();
    header('Location: index.php?expirado=1');
    exit;
}
?>
