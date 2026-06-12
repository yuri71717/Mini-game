<?php
// Script simples de logout
// Limpa os dados de sessão e redireciona com um aviso na URL

session_start();
session_destroy();

header('Location: index.php?logout=1');
exit;
?>
