<?php
session_start();
require_once 'config.php';

// Redirecionar para o painel administrativo
header("Location: admin_dashboard.php");
exit;
?> 