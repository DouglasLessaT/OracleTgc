<?php
/**
 * SPA fallback: quando o servidor encaminha todas as rotas para este script
 * (ex.: Nginx try_files ... /index.php), servimos o index.html para o React Router
 * tratar a URL (evita 404 / "No input file specified" ao dar F5 em /dashboard, etc.).
 */
$index = __DIR__ . '/index.html';
if (file_exists($index)) {
    header('Content-Type: text/html; charset=utf-8');
    readfile($index);
} else {
    header('HTTP/1.0 404 Not Found');
    echo '<!DOCTYPE html><html><body><h1>404</h1><p>index.html not found. Run: npm run build</p></body></html>';
}
