<?php
require_once 'includes/functions.php';

session_destroy();
flash_message('success', 'Has cerrado sesión exitosamente');
redirect('index.php');
?>