<?php
// encerra a sessão e volta pro login

session_start();
session_destroy();

header('Location: index.php?logout=1');
exit;
?>
