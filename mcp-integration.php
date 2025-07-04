<?php
/**
 * MCP Media Generator Integration
 * Clase para integrar el generador MCP con el sistema PHP
 */

class MCPImageGenerator {
    private $mcp_path;
    private $output_dir;
    private $web_output_dir;
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->mcp_path = __DIR__ . '/mcp-media-generator/build/index.js';
        $this->output_dir = __DIR__ . '/mcp-media-generator/generated-media/';
        $this->web_output_dir = __DIR__ . '/assets/images/generated/';
        
        $this->ensureDirectories();
    }
    
    private function ensureDirectories() {
        if (!file_exists($this->output_dir)) {
            mkdir($this->output_dir, 0755, true);
        }
        
        if (!file_exists($this->web_output_dir)) {
            mkdir($this->web_output_dir, 0755, true);
        }
    }
    
    /**
     * Generar imagen usando el MCP
     */
    public function generateImage($prompt, $style = 'realistic', $size = '1024x1024', $category = 'otros', $user_id = null) {
        try {
            // Validar parámetros
            $prompt = trim($prompt);
            if (empty($prompt)) {
                throw new Exception('Prompt no puede estar vacío');
            }
            
            // Generar nombre único
            $timestamp = time();
            $unique_id = uniqid();
            $filename_base = "admin_{$category}_{$timestamp}_{$unique_id}";
            
            // Verificar si hay APIs configuradas
            $has_apis = $this->hasConfiguredAPIs();
            
            if ($has_apis && $this->isNodeJSAvailable()) {
                // Intentar generar imagen real
                $result = $this->generateRealImage($prompt, $style, $size, $filename_base);
            } else {
                // Generar placeholder mock
                $result = $this->generateMockImage($prompt, $style, $size, $category, $filename_base);
            }
            
            // Guardar en base de datos
            if ($result['success']) {
                $image_id = $this->saveToDatabase($result['filename'], $prompt, $style, $size, $category, $user_id, $result['is_real']);
                $result['id'] = $image_id;
            }
            
            return $result;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Verificar si hay APIs configuradas
     */
    private function hasConfiguredAPIs() {
        return !empty(getenv('OPENAI_API_KEY')) || !empty(getenv('STABILITY_API_KEY'));
    }
    
    /**
     * Verificar si Node.js está disponible
     */
    private function isNodeJSAvailable() {
        $output = [];
        $return_code = 0;
        exec('node --version 2>&1', $output, $return_code);
        return $return_code === 0;
    }
    
    /**
     * Generar imagen real usando MCP
     */
    private function generateRealImage($prompt, $style, $size, $filename_base) {
        try {
            // Preparar comando MCP
            $command_data = [
                'tool' => 'generate_image',
                'arguments' => [
                    'prompt' => $prompt,
                    'style' => $style,
                    'size' => $size
                ]
            ];
            
            // Crear archivo temporal con los datos
            $temp_file = tempnam(sys_get_temp_dir(), 'mcp_request');
            file_put_contents($temp_file, json_encode($command_data));
            
            // Ejecutar MCP
            $env_vars = '';
            if (getenv('OPENAI_API_KEY')) {
                $env_vars .= 'OPENAI_API_KEY=' . escapeshellarg(getenv('OPENAI_API_KEY')) . ' ';
            }
            if (getenv('STABILITY_API_KEY')) {
                $env_vars .= 'STABILITY_API_KEY=' . escapeshellarg(getenv('STABILITY_API_KEY')) . ' ';
            }
            
            $command = $env_vars . "node " . escapeshellarg($this->mcp_path) . " < " . escapeshellarg($temp_file);
            
            $output = [];
            $return_code = 0;
            exec($command . ' 2>&1', $output, $return_code);
            
            // Limpiar archivo temporal
            unlink($temp_file);
            
            if ($return_code === 0) {
                $filename = $filename_base . '.png';
                return [
                    'success' => true,
                    'filename' => $filename,
                    'is_real' => true,
                    'message' => 'Imagen generada exitosamente con IA'
                ];
            } else {
                throw new Exception('Error ejecutando MCP: ' . implode("\n", $output));
            }
            
        } catch (Exception $e) {
            // Si falla, generar mock
            return $this->generateMockImage($prompt, $style, $size, 'mock', $filename_base);
        }
    }
    
    /**
     * Generar imagen placeholder mock
     */
    private function generateMockImage($prompt, $style, $size, $category, $filename_base) {
        $filename = $filename_base . '.txt';
        $filepath = $this->output_dir . $filename;
        $web_filepath = $this->web_output_dir . $filename;
        
        $mock_content = "=== IMAGEN PLACEHOLDER GENERADA ===\n\n";
        $mock_content .= "Prompt: {$prompt}\n";
        $mock_content .= "Estilo: {$style}\n";
        $mock_content .= "Tamaño: {$size}\n";
        $mock_content .= "Categoría: {$category}\n";
        $mock_content .= "Archivo: {$filename}\n";
        $mock_content .= "Generado: " . date('Y-m-d H:i:s') . "\n\n";
        $mock_content .= "Esta es una imagen placeholder.\n";
        $mock_content .= "En producción con APIs configuradas, sería una imagen real.\n\n";
        $mock_content .= "Para generar imágenes reales:\n";
        $mock_content .= "1. Configurar OPENAI_API_KEY o STABILITY_API_KEY\n";
        $mock_content .= "2. Instalar Node.js en el servidor\n";
        $mock_content .= "3. El sistema detectará automáticamente las APIs\n";
        
        file_put_contents($filepath, $mock_content);
        file_put_contents($web_filepath, $mock_content);
        
        return [
            'success' => true,
            'filename' => $filename,
            'is_real' => false,
            'message' => 'Placeholder generado exitosamente (modo demo)'
        ];
    }
    
    /**
     * Guardar en base de datos
     */
    private function saveToDatabase($filename, $prompt, $style, $size, $category, $user_id, $is_real) {
        $stmt = $this->pdo->prepare("
            INSERT INTO generated_images 
            (filename, prompt, style, size, category, generated_by, is_real_image, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $filename, 
            $prompt, 
            $style, 
            $size, 
            $category, 
            $user_id,
            $is_real ? 1 : 0
        ]);
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Obtener imágenes generadas
     */
    public function getGeneratedImages($limit = 20, $category = null) {
        $sql = "
            SELECT gi.*, u.username 
            FROM generated_images gi 
            LEFT JOIN users u ON gi.generated_by = u.id 
        ";
        
        $params = [];
        
        if ($category) {
            $sql .= " WHERE gi.category = ? ";
            $params[] = $category;
        }
        
        $sql .= " ORDER BY gi.created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Eliminar imagen generada
     */
    public function deleteGeneratedImage($id, $user_id = null) {
        try {
            // Obtener información de la imagen
            $stmt = $this->pdo->prepare("SELECT * FROM generated_images WHERE id = ?");
            $stmt->execute([$id]);
            $image = $stmt->fetch();
            
            if (!$image) {
                throw new Exception('Imagen no encontrada');
            }
            
            // Verificar permisos (solo admin o el usuario que la creó)
            if ($user_id && $image['generated_by'] != $user_id) {
                // Verificar si es admin
                $stmt = $this->pdo->prepare("SELECT role FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
                
                if (!$user || $user['role'] !== 'admin') {
                    throw new Exception('Sin permisos para eliminar esta imagen');
                }
            }
            
            // Eliminar archivos físicos
            $files_to_delete = [
                $this->output_dir . $image['filename'],
                $this->web_output_dir . $image['filename']
            ];
            
            foreach ($files_to_delete as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
            
            // Eliminar de base de datos
            $stmt = $this->pdo->prepare("DELETE FROM generated_images WHERE id = ?");
            $stmt->execute([$id]);
            
            return [
                'success' => true,
                'message' => 'Imagen eliminada exitosamente'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener estadísticas de imágenes generadas
     */
    public function getStats() {
        $stats = [];
        
        // Total de imágenes
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM generated_images");
        $stats['total'] = $stmt->fetch()['total'];
        
        // Imágenes reales vs mock
        $stmt = $this->pdo->query("SELECT is_real_image, COUNT(*) as count FROM generated_images GROUP BY is_real_image");
        $results = $stmt->fetchAll();
        
        $stats['real'] = 0;
        $stats['mock'] = 0;
        
        foreach ($results as $result) {
            if ($result['is_real_image']) {
                $stats['real'] = $result['count'];
            } else {
                $stats['mock'] = $result['count'];
            }
        }
        
        // Por categoría
        $stmt = $this->pdo->query("SELECT category, COUNT(*) as count FROM generated_images GROUP BY category ORDER BY count DESC");
        $stats['by_category'] = $stmt->fetchAll();
        
        // Por mes
        $stmt = $this->pdo->query("
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month, 
                COUNT(*) as count 
            FROM generated_images 
            GROUP BY month 
            ORDER BY month DESC 
            LIMIT 12
        ");
        $stats['by_month'] = $stmt->fetchAll();
        
        return $stats;
    }
}
?>