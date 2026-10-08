<?php
/**
 * Controlador Base MVC
 * Manejo de renderizado de vistas, respuestas JSON, redirecciones y mensajes flash
 */

namespace App\Controllers;

use Config\Auth;

abstract class Controller {
    /**
     * Renderizar una vista dentro del layout principal
     */
    protected function render(string $viewPath, array $data = [], string $layout = 'main'): void {
        extract($data);
        $currentUser = Auth::user();

        // Capturar la vista solicitada
        $viewFile = APP_PATH . '/Views/' . $viewPath . '.php';
        if (!file_exists($viewFile)) {
            die("Error 404: La vista '{$viewPath}' no fue encontrada en " . htmlspecialchars($viewFile));
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Si layout es false, emitir vista directa sin plantilla (útil para PDFs limpios o AJAX)
        if ($layout === '' || $layout === 'none') {
            echo $content;
            return;
        }

        $layoutFile = APP_PATH . '/Views/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            die("Error: El layout '{$layout}' no existe.");
        }

        require $layoutFile;
    }

    /**
     * Responder con payload JSON (para llamadas AJAX y Fetch de JavaScript Vanilla)
     */
    protected function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    /**
     * Redirección HTTP con soporte de BASE_URL
     */
    protected function redirect(string $url): void {
        if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            $url = function_exists('url') ? url($url) : $url;
        }
        header("Location: {$url}");
        exit;
    }

    /**
     * Establecer mensaje flash en sesión
     */
    protected function setFlash(string $type, string $message): void {
        Auth::initSession();
        $_SESSION['flash_' . $type] = $message;
    }
}
