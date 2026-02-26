<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DocsController extends AbstractController
{
    #[Route('/docs', name: 'docs_index', methods: ['GET'])]
    #[Route('/docs/', name: 'docs_index_slash', methods: ['GET'])]
    #[Route('/docs/{path}', name: 'docs_path', requirements: ['path' => '.+'], methods: ['GET'])]
    public function index(Request $request, string $path = 'index.html'): Response
    {
        $projectDir = $this->getParameter('kernel.project_dir');
        $docsPath = $projectDir . '/docs/api/' . $path;
        
        // Normalizar o caminho para evitar directory traversal
        $realDocsDir = realpath($projectDir . '/docs/api');
        $realPath = realpath($docsPath);
        
        if ($realPath === false || !str_starts_with($realPath, $realDocsDir)) {
            // Se o arquivo não existe ou está fora do diretório docs, redirecionar para index
            $path = 'index.html';
            $docsPath = $projectDir . '/docs/api/index.html';
        }
        
        // Se for o index.html, servir diretamente
        if ($path === 'index.html' || $path === '') {
            $docsPath = $projectDir . '/docs/api/index.html';
        }
        
        if (!file_exists($docsPath)) {
            throw $this->createNotFoundException('Documentação não encontrada');
        }
        
        // Determinar o content-type baseado na extensão
        $extension = pathinfo($docsPath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'html' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject',
            'xml' => 'application/xml',
        ];
        
        $contentType = $mimeTypes[$extension] ?? 'application/octet-stream';
        
        $response = new BinaryFileResponse($docsPath);
        $response->headers->set('Content-Type', $contentType);
        
        // Cache para assets estáticos
        if (in_array($extension, ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'woff', 'woff2', 'ttf', 'eot'])) {
            $response->setMaxAge(3600);
            $response->setSharedMaxAge(3600);
        }
        
        return $response;
    }
}

