<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

session_start();

// 1. Validar que el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    // Guardar un mensaje de error en la sesión para mostrarlo en la página de login
    $_SESSION['error_message'] = 'Debes iniciar sesión para dejar una reseña.';
    header('Location: login.php');
    exit();
}

// 2. Validar que la solicitud sea de tipo POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 3. Recibir y sanitizar los datos del formulario
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
    $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING);
    $user_id = $_SESSION['user_id'];

    // Validar que los datos esenciales estén presentes
    if ($product_id && $rating && $user_id) {
        // Validar que el rating esté en el rango correcto (1-5)
        if ($rating < 1 || $rating > 5) {
            $_SESSION['error_message'] = 'La calificación debe estar entre 1 y 5.';
            header('Location: particulares.php?product_id=' . $product_id);
            exit();
        }

        try {
            // 4. Insertar la reseña en la base de datos
            $stmt = $pdo->prepare(
                'INSERT INTO product_reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$product_id, $user_id, $rating, $comment]);

            // Guardar un mensaje de éxito
            $_SESSION['success_message'] = 'Tu reseña ha sido enviada con éxito.';

        } catch (PDOException $e) {
            // Manejar errores de la base de datos
            $_SESSION['error_message'] = 'Error al guardar la reseਮa: ' . $e->getMessage();
        }

    } else {
        $_SESSION['error_message'] = 'Datos inválidos. Por favor, intenta de nuevo.';
    }

    // 5. Redirigir al usuario de vuelta a la página del producto
    // Si el product_id no es válido, redirigir a la página principal de la tienda
    if ($product_id) {
        header('Location: particulares.php?product_id=' . $product_id . '#reviews');
    } else {
        header('Location: particulares.php');
    }
    exit();

} else {
    // Si no es una solicitud POST, redirigir a la página principal
    header('Location: index.php');
    exit();
}
?>