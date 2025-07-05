<?php
/**
 * Sistema de subida de fotos de perfil
 * Funcionalidad premium para FractalMerch
 */

class PhotoUploader {
    private $pdo;
    private $upload_dir;
    private $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private $max_file_size = 5 * 1024 * 1024; // 5MB
    private $max_dimensions = ['width' => 2048, 'height' => 2048];
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->upload_dir = dirname(__DIR__) . '/assets/images/profiles/';
        
        // Crear directorio si no existe
        if (!is_dir($this->upload_dir)) {
            mkdir($this->upload_dir, 0755, true);
        }
    }
    
    /**
     * Subir foto de perfil
     */
    public function uploadProfilePhoto($user_id, $file, $type = 'profile') {
        try {
            // Validar archivo
            $validation = $this->validateFile($file);
            if (!$validation['success']) {
                return $validation;
            }
            
            // Generar nombre único
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = $type . '_' . $user_id . '_' . time() . '.' . $extension;
            $filepath = $this->upload_dir . $filename;
            
            // Procesar imagen
            $processed_image = $this->processImage($file['tmp_name'], $filepath, $type);
            if (!$processed_image['success']) {
                return $processed_image;
            }
            
            // Generar thumbnails
            $thumbnails = $this->generateThumbnails($filepath, $filename, $type);
            
            // Guardar en base de datos
            $db_result = $this->saveToDatabase($user_id, $filename, $type, $thumbnails);
            if (!$db_result['success']) {
                // Limpiar archivos si falla DB
                $this->cleanupFiles($filepath, $thumbnails);
                return $db_result;
            }
            
            // Eliminar foto anterior si existe
            $this->cleanupOldPhoto($user_id, $type);
            
            return [
                'success' => true,
                'message' => 'Foto subida exitosamente',
                'filename' => $filename,
                'url' => 'assets/images/profiles/' . $filename,
                'thumbnails' => $thumbnails
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Validar archivo subido
     */
    private function validateFile($file) {
        // Verificar errores de subida
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error_messages = [
                UPLOAD_ERR_INI_SIZE => 'El archivo es demasiado grande (límite del servidor)',
                UPLOAD_ERR_FORM_SIZE => 'El archivo es demasiado grande (límite del formulario)',
                UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
                UPLOAD_ERR_NO_FILE => 'No se seleccionó ningún archivo',
                UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal',
                UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir el archivo',
                UPLOAD_ERR_EXTENSION => 'Extensión de archivo no permitida'
            ];
            
            return [
                'success' => false,
                'message' => $error_messages[$file['error']] ?? 'Error desconocido al subir archivo'
            ];
        }
        
        // Verificar tamaño
        if ($file['size'] > $this->max_file_size) {
            return [
                'success' => false,
                'message' => 'El archivo es demasiado grande. Máximo 5MB permitido.'
            ];
        }
        
        // Verificar tipo MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $this->allowed_types)) {
            return [
                'success' => false,
                'message' => 'Tipo de archivo no permitido. Solo se permiten: JPEG, PNG, GIF, WebP'
            ];
        }
        
        // Verificar dimensiones
        $image_info = getimagesize($file['tmp_name']);
        if (!$image_info) {
            return [
                'success' => false,
                'message' => 'El archivo no es una imagen válida'
            ];
        }
        
        if ($image_info[0] > $this->max_dimensions['width'] || 
            $image_info[1] > $this->max_dimensions['height']) {
            return [
                'success' => false,
                'message' => 'Dimensiones demasiado grandes. Máximo 2048x2048 píxeles.'
            ];
        }
        
        return ['success' => true];
    }
    
    /**
     * Procesar imagen (optimización, compresión)
     */
    private function processImage($source_path, $destination_path, $type) {
        try {
            $image_info = getimagesize($source_path);
            $mime_type = $image_info['mime'];
            
            // Crear imagen desde archivo
            switch ($mime_type) {
                case 'image/jpeg':
                    $source_image = imagecreatefromjpeg($source_path);
                    break;
                case 'image/png':
                    $source_image = imagecreatefrompng($source_path);
                    break;
                case 'image/gif':
                    $source_image = imagecreatefromgif($source_path);
                    break;
                case 'image/webp':
                    $source_image = imagecreatefromwebp($source_path);
                    break;
                default:
                    return ['success' => false, 'message' => 'Tipo de imagen no soportado'];
            }
            
            if (!$source_image) {
                return ['success' => false, 'message' => 'No se pudo procesar la imagen'];
            }
            
            // Redimensionar si es necesario
            $original_width = imagesx($source_image);
            $original_height = imagesy($source_image);
            
            // Determinar tamaño objetivo según tipo
            $target_sizes = [
                'profile' => ['width' => 300, 'height' => 300],
                'cover' => ['width' => 1200, 'height' => 400]
            ];
            
            $target_size = $target_sizes[$type] ?? $target_sizes['profile'];
            
            // Calcular dimensiones manteniendo proporción
            $aspect_ratio = $original_width / $original_height;
            $target_aspect = $target_size['width'] / $target_size['height'];
            
            if ($aspect_ratio > $target_aspect) {
                $new_width = $target_size['width'];
                $new_height = $target_size['width'] / $aspect_ratio;
            } else {
                $new_height = $target_size['height'];
                $new_width = $target_size['height'] * $aspect_ratio;
            }
            
            // Crear imagen redimensionada
            $processed_image = imagecreatetruecolor($new_width, $new_height);
            
            // Preservar transparencia para PNG
            if ($mime_type === 'image/png') {
                imagealphablending($processed_image, false);
                imagesavealpha($processed_image, true);
            }
            
            // Redimensionar
            imagecopyresampled(
                $processed_image, $source_image,
                0, 0, 0, 0,
                $new_width, $new_height,
                $original_width, $original_height
            );
            
            // Guardar imagen procesada
            $saved = false;
            switch ($mime_type) {
                case 'image/jpeg':
                    $saved = imagejpeg($processed_image, $destination_path, 85);
                    break;
                case 'image/png':
                    $saved = imagepng($processed_image, $destination_path, 6);
                    break;
                case 'image/gif':
                    $saved = imagegif($processed_image, $destination_path);
                    break;
                case 'image/webp':
                    $saved = imagewebp($processed_image, $destination_path, 80);
                    break;
            }
            
            // Limpiar memoria
            imagedestroy($source_image);
            imagedestroy($processed_image);
            
            if (!$saved) {
                return ['success' => false, 'message' => 'No se pudo guardar la imagen procesada'];
            }
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error procesando imagen: ' . $e->getMessage()];
        }
    }
    
    /**
     * Generar thumbnails
     */
    private function generateThumbnails($source_path, $filename, $type) {
        $thumbnails = [];
        
        $thumbnail_sizes = [
            'small' => ['width' => 50, 'height' => 50],
            'medium' => ['width' => 150, 'height' => 150]
        ];
        
        foreach ($thumbnail_sizes as $size_name => $dimensions) {
            $thumbnail_filename = str_replace('.', '_' . $size_name . '.', $filename);
            $thumbnail_path = $this->upload_dir . $thumbnail_filename;
            
            if ($this->createThumbnail($source_path, $thumbnail_path, $dimensions)) {
                $thumbnails[$size_name] = $thumbnail_filename;
            }
        }
        
        return $thumbnails;
    }
    
    /**
     * Crear thumbnail
     */
    private function createThumbnail($source_path, $destination_path, $dimensions) {
        try {
            $image_info = getimagesize($source_path);
            $mime_type = $image_info['mime'];
            
            // Crear imagen desde archivo
            switch ($mime_type) {
                case 'image/jpeg':
                    $source_image = imagecreatefromjpeg($source_path);
                    break;
                case 'image/png':
                    $source_image = imagecreatefrompng($source_path);
                    break;
                case 'image/gif':
                    $source_image = imagecreatefromgif($source_path);
                    break;
                case 'image/webp':
                    $source_image = imagecreatefromwebp($source_path);
                    break;
                default:
                    return false;
            }
            
            if (!$source_image) return false;
            
            $original_width = imagesx($source_image);
            $original_height = imagesy($source_image);
            
            // Crear thumbnail cuadrado con crop centrado
            $thumbnail = imagecreatetruecolor($dimensions['width'], $dimensions['height']);
            
            // Preservar transparencia para PNG
            if ($mime_type === 'image/png') {
                imagealphablending($thumbnail, false);
                imagesavealpha($thumbnail, true);
            }
            
            // Calcular área de crop
            $crop_size = min($original_width, $original_height);
            $crop_x = ($original_width - $crop_size) / 2;
            $crop_y = ($original_height - $crop_size) / 2;
            
            // Crear thumbnail con crop
            imagecopyresampled(
                $thumbnail, $source_image,
                0, 0, $crop_x, $crop_y,
                $dimensions['width'], $dimensions['height'],
                $crop_size, $crop_size
            );
            
            // Guardar thumbnail
            $saved = false;
            switch ($mime_type) {
                case 'image/jpeg':
                    $saved = imagejpeg($thumbnail, $destination_path, 85);
                    break;
                case 'image/png':
                    $saved = imagepng($thumbnail, $destination_path, 6);
                    break;
                case 'image/gif':
                    $saved = imagegif($thumbnail, $destination_path);
                    break;
                case 'image/webp':
                    $saved = imagewebp($thumbnail, $destination_path, 80);
                    break;
            }
            
            // Limpiar memoria
            imagedestroy($source_image);
            imagedestroy($thumbnail);
            
            return $saved;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Guardar información en base de datos
     */
    private function saveToDatabase($user_id, $filename, $type, $thumbnails) {
        try {
            $column = $type === 'profile' ? 'profile_photo' : 'cover_photo';
            
            $stmt = $this->pdo->prepare("
                UPDATE users 
                SET $column = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            
            $result = $stmt->execute([$filename, $user_id]);
            
            if (!$result) {
                return ['success' => false, 'message' => 'Error guardando en base de datos'];
            }
            
            // Registrar actividad
            $this->logActivity($user_id, 'profile_photo_update', "Foto de $type actualizada");
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }
    
    /**
     * Limpiar archivos
     */
    private function cleanupFiles($main_file, $thumbnails) {
        if (file_exists($main_file)) {
            unlink($main_file);
        }
        
        foreach ($thumbnails as $thumbnail) {
            $thumbnail_path = $this->upload_dir . $thumbnail;
            if (file_exists($thumbnail_path)) {
                unlink($thumbnail_path);
            }
        }
    }
    
    /**
     * Limpiar foto anterior
     */
    private function cleanupOldPhoto($user_id, $type) {
        try {
            $column = $type === 'profile' ? 'profile_photo' : 'cover_photo';
            
            $stmt = $this->pdo->prepare("SELECT $column FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $old_photo = $stmt->fetchColumn();
            
            if ($old_photo) {
                $old_file = $this->upload_dir . $old_photo;
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
                
                // Eliminar thumbnails antiguos
                $old_thumbnails = [
                    str_replace('.', '_small.', $old_photo),
                    str_replace('.', '_medium.', $old_photo)
                ];
                
                foreach ($old_thumbnails as $thumbnail) {
                    $thumbnail_path = $this->upload_dir . $thumbnail;
                    if (file_exists($thumbnail_path)) {
                        unlink($thumbnail_path);
                    }
                }
            }
            
        } catch (Exception $e) {
            // Continuar aunque falle la limpieza
        }
    }
    
    /**
     * Registrar actividad
     */
    private function logActivity($user_id, $type, $description) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO user_activity_log 
                (user_id, activity_type, description, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $user_id,
                $type,
                $description,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
            
        } catch (Exception $e) {
            // Continuar aunque falle el log
        }
    }
    
    /**
     * Obtener URL de foto
     */
    public function getPhotoUrl($filename, $size = 'original') {
        if (!$filename) return null;
        
        if ($size === 'original') {
            return 'assets/images/profiles/' . $filename;
        } else {
            $thumbnail_filename = str_replace('.', '_' . $size . '.', $filename);
            return 'assets/images/profiles/' . $thumbnail_filename;
        }
    }
    
    /**
     * Eliminar foto
     */
    public function deletePhoto($user_id, $type) {
        try {
            $this->cleanupOldPhoto($user_id, $type);
            
            $column = $type === 'profile' ? 'profile_photo' : 'cover_photo';
            $stmt = $this->pdo->prepare("UPDATE users SET $column = NULL WHERE id = ?");
            $result = $stmt->execute([$user_id]);
            
            if ($result) {
                $this->logActivity($user_id, 'profile_photo_delete', "Foto de $type eliminada");
                return ['success' => true, 'message' => 'Foto eliminada exitosamente'];
            } else {
                return ['success' => false, 'message' => 'Error eliminando foto'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}
?>