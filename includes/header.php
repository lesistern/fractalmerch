<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="light-mode">
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="index.php"><?php echo SITE_NAME; ?></a>
            </div>
            <div class="nav-menu">
                <a href="index.php">Inicio</a>
                <a href="customize-shirt.php">Personalizar Remera</a>
                <?php if (is_logged_in()): ?>
                    <a href="profile.php">Mi Perfil</a>
                    <a href="create-post.php">Escribir</a>
                    <?php if (is_admin() || is_moderator()): ?>
                        <a href="admin/dashboard.php">Admin</a>
                    <?php endif; ?>
                    <a href="logout.php">Cerrar Sesión</a>
                    <span class="user-welcome">Hola, <?php echo $_SESSION['username']; ?></span>
                <?php else: ?>
                    <a href="login.php">Iniciar Sesión</a>
                    <a href="register.php">Registrarse</a>
                <?php endif; ?>
                <button class="dark-mode-toggle" onclick="toggleDarkMode()">
                    <i class="fas fa-moon" id="dark-mode-icon"></i>
                </button>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <?php
        $messages = get_flash_messages();
        foreach ($messages as $message):
        ?>
            <div class="alert alert-<?php echo $message['type']; ?>">
                <?php echo $message['message']; ?>
            </div>
        <?php endforeach; ?>