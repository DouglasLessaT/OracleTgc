<?php

namespace App\Core\Infrastructure\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Listener que verifica e cria o banco de dados automaticamente na inicialização
 */
class DatabaseSetupListener implements EventSubscriberInterface
{
    private bool $setupExecuted = false;

    public function __construct(
        private readonly Connection $connection,
        private readonly ?LoggerInterface $logger = null
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 1000],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // Executar apenas uma vez por requisição
        if ($this->setupExecuted) {
            return;
        }

        $this->setupExecuted = true;

        // Apenas executar em ambiente de desenvolvimento
        // Em produção, o banco deve estar configurado manualmente
        if ($_ENV['APP_ENV'] ?? 'dev' === 'prod') {
            return;
        }

        try {
            $this->ensureDatabaseExists();
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->warning('Não foi possível configurar banco de dados automaticamente: ' . $e->getMessage());
            }
            // Não interromper a requisição se houver erro
        }
    }

    /**
     * Verifica se o banco de dados existe e cria se necessário
     */
    private function ensureDatabaseExists(): void
    {
        try {
            // Tentar conectar ao banco de dados
            $this->connection->connect();
            
            // Se chegou aqui, o banco existe e está acessível
            if ($this->logger) {
                $this->logger->debug('Banco de dados verificado e acessível.');
            }
            return;
        } catch (Exception $e) {
            // Se a conexão falhar, pode ser que o banco não exista
            // Verificar se é erro de banco não encontrado
            $errorMessage = $e->getMessage();
            $isDbNotFound = str_contains($errorMessage, 'does not exist')
                || str_contains($errorMessage, 'Unknown database')  // MySQL
                || (str_contains($errorMessage, 'database') && str_contains($errorMessage, 'not found'));
            if ($isDbNotFound) {
                $this->createDatabaseIfNotExists();
            } else {
                // Outro tipo de erro (servidor não disponível, etc)
                throw $e;
            }
        }
    }

    /**
     * Cria o banco de dados se não existir (MySQL)
     */
    private function createDatabaseIfNotExists(): void
    {
        $params = $this->connection->getParams();
        $dbName = $params['dbname'] ?? $params['path'] ?? null;

        if (!$dbName) {
            return;
        }

        $driver = $params['driver'] ?? 'pdo_mysql';
        if ($driver !== 'pdo_mysql') {
            return;
        }

        // Conexão MySQL sem banco (usa "mysql" ou conexão sem dbname)
        $host = $params['host'] ?? '127.0.0.1';
        $port = $params['port'] ?? 3306;
        $user = $params['user'] ?? 'root';
        $password = $params['password'] ?? '';

        try {
            $dsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $host, $port);
            $serverConnection = new \PDO($dsn, $user, $password);

            $escapedDbName = '`' . str_replace('`', '``', $dbName) . '`';
            $serverConnection->exec(
                "CREATE DATABASE IF NOT EXISTS {$escapedDbName} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
            );

            if ($this->logger) {
                $this->logger->info("Banco de dados '{$dbName}' verificado/criado com sucesso.");
            }

            $this->connection->connect();
        } catch (\PDOException $e) {
            if ($this->logger) {
                $this->logger->error("Erro ao criar banco de dados: " . $e->getMessage());
            }
            throw $e;
        }
    }
}

