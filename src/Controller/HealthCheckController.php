<?php

declare(strict_types=1);

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Health Check Controller with different security levels
 */
class HealthCheckController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CacheItemPoolInterface $cache
    ) {
    }

    /**
     * Basic health check endpoint - PUBLIC ACCESS
     * Returns HTTP 200 if application is responding
     */
    #[Route('/health', name: 'health_check', methods: ['GET'])]
    public function health(): JsonResponse
    {
        return $this->json([
            'status' => 'ok',
            'timestamp' => time(),
            'environment' => $this->getParameter('kernel.environment'),
            'version' => $this->getAppVersion()
        ]);
    }

    /**
     * Liveness probe for Kubernetes/container orchestration - PUBLIC ACCESS
     */
    #[Route('/health/live', name: 'health_live', methods: ['GET'])]
    public function live(): JsonResponse
    {
        return $this->json([
            'status' => 'alive',
            'timestamp' => time()
        ]);
    }

    /**
     * Readiness probe for Kubernetes/container orchestration - PUBLIC ACCESS
     */
    #[Route('/health/ready', name: 'health_ready', methods: ['GET'])]
    public function ready(): JsonResponse
    {
        $criticalChecks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
        ];

        foreach ($criticalChecks as $check) {
            if ($check['status'] === 'error') {
                return $this->json([
                    'status' => 'not_ready',
                    'timestamp' => time(),
                    'checks' => $criticalChecks
                ], Response::HTTP_SERVICE_UNAVAILABLE);
            }
        }

        return $this->json([
            'status' => 'ready',
            'timestamp' => time(),
            'checks' => $criticalChecks
        ]);
    }

    /**
     * Detailed health check with all system components - ADMIN ONLY
     */
    #[Route('/health/detailed', name: 'health_check_detailed', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', message: 'Access denied. Admin role required for detailed health check.')]
    public function detailedHealth(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'disk_space' => $this->checkDiskSpace(),
            'log_directory' => $this->checkLogDirectory(),
            'memory' => $this->checkMemory()
        ];

        // Determine overall status
        $overallStatus = 'healthy';
        $httpStatus = Response::HTTP_OK;

        foreach ($checks as $check) {
            if ($check['status'] === 'error') {
                $overallStatus = 'unhealthy';
                $httpStatus = Response::HTTP_SERVICE_UNAVAILABLE;
                break;
            } elseif ($check['status'] === 'warning') {
                $overallStatus = 'degraded';
            }
        }

        return $this->json([
            'status' => $overallStatus,
            'timestamp' => time(),
            'environment' => $this->getParameter('kernel.environment'),
            'version' => $this->getAppVersion(),
            'checks' => $checks,
            'user' => $this->getUser()?->getUserIdentifier()
        ], $httpStatus);
    }

    /**
     * System metrics endpoint - SUPER ADMIN ONLY
     */
    #[Route('/health/metrics', name: 'health_metrics', methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'Access denied. Super admin role required for system metrics.')]
    public function metrics(): JsonResponse
    {
        return $this->json([
            'metrics' => [
                'memory' => $this->getDetailedMemoryInfo(),
                'disk' => $this->getDetailedDiskInfo(),
                'php' => $this->getPhpInfo(),
                'system' => $this->getSystemInfo()
            ],
            'timestamp' => time(),
            'user' => $this->getUser()?->getUserIdentifier()
        ]);
    }

    /**
     * Check database connectivity and basic query execution
     * @return array<mixed>
     */
    private function checkDatabase(): array
    {
        try {
            $connection = $this->entityManager->getConnection();

            // Execute a simple query (connection happens automatically)
            $result = $connection->executeQuery('SELECT 1')->fetchOne();

            if ($result !== 1) {
                throw new \Exception('Database query returned unexpected result');
            }

            // Check connection pool status
            $params = $connection->getParams();

            return [
                'status' => 'healthy',
                'message' => 'Database connection successful',
                'details' => [
                    'driver' => $params['driver'] ?? 'unknown',
                    'host' => $params['host'] ?? 'unknown',
                    'database' => $params['dbname'] ?? 'unknown'
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check cache system functionality
     * @return array<mixed>
     * @throws InvalidArgumentException
     */
    private function checkCache(): array
    {
        try {
            $testKey = 'health_check_' . time();
            $testValue = 'test_value';

            // Test cache write
            $item = $this->cache->getItem($testKey);
            $item->set($testValue);
            $item->expiresAfter(10); // 10 seconds
            $saved = $this->cache->save($item);

            if (!$saved) {
                throw new \Exception('Failed to save cache item');
            }

            // Test cache read
            $retrievedItem = $this->cache->getItem($testKey);
            if (!$retrievedItem->isHit() || $retrievedItem->get() !== $testValue) {
                throw new \Exception('Failed to retrieve cache item');
            }

            // Clean up test item
            $this->cache->deleteItem($testKey);

            return [
                'status' => 'healthy',
                'message' => 'Cache system operational',
                'details' => [
                    'adapter' => get_class($this->cache)
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Cache system failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check available disk space
     * @return array<mixed>
     */
    private function checkDiskSpace(): array
    {
        try {
            $projectDir = $this->getStringParameter('kernel.project_dir');
            $varPath = $projectDir . '/var';
            $freeBytes = disk_free_space($varPath);
            $totalBytes = disk_total_space($varPath);

            if ($freeBytes === false || $totalBytes === false) {
                throw new \Exception('Unable to get disk space information');
            }

            $usedBytes = $totalBytes - $freeBytes;
            $usedPercentage = ($usedBytes / $totalBytes) * 100;

            $status = 'healthy';
            $message = 'Disk space sufficient';

            if ($usedPercentage > 90) {
                $status = 'error';
                $message = 'Disk space critically low';
            } elseif ($usedPercentage > 80) {
                $status = 'warning';
                $message = 'Disk space getting low';
            }

            return [
                'status' => $status,
                'message' => $message,
                'details' => [
                    'used_percentage' => round($usedPercentage, 2),
                    'free_space' => $this->formatBytes($freeBytes),
                    'total_space' => $this->formatBytes($totalBytes),
                    'path' => $varPath
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Disk space check failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check log directory writability
     * @return array<mixed>
     */
    private function checkLogDirectory(): array
    {
        try {
            $logDir = $this->getStringParameter('kernel.logs_dir');

            if (!is_dir($logDir)) {
                throw new \Exception('Log directory does not exist');
            }

            if (!is_writable($logDir)) {
                throw new \Exception('Log directory is not writable');
            }

            // Test write capability
            $testFile = $logDir . '/health_check_test.tmp';
            $written = file_put_contents($testFile, 'test');

            if ($written === false) {
                throw new \Exception('Unable to write to log directory');
            }

            // Clean up test file
            unlink($testFile);

            return [
                'status' => 'healthy',
                'message' => 'Log directory writable',
                'details' => [
                    'path' => $logDir,
                    'permissions' => substr(sprintf('%o', fileperms($logDir)), -4)
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Log directory check failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check memory usage
     * @return array<mixed>
     */
    private function checkMemory(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $memoryLimit = $this->getMemoryLimit();

        $status = 'healthy';
        $message = 'Memory usage normal';

        if ($memoryLimit > 0) {
            $usagePercentage = ($memoryUsage / $memoryLimit) * 100;

            if ($usagePercentage > 90) {
                $status = 'error';
                $message = 'Memory usage critically high';
            } elseif ($usagePercentage > 80) {
                $status = 'warning';
                $message = 'Memory usage getting high';
            }
        }

        return [
            'status' => $status,
            'message' => $message,
            'details' => [
                'current_usage' => $this->formatBytes($memoryUsage),
                'peak_usage' => $this->formatBytes($memoryPeak),
                'memory_limit' => $memoryLimit > 0 ? $this->formatBytes($memoryLimit) : 'unlimited',
                'usage_percentage' => $memoryLimit > 0 ? round(($memoryUsage / $memoryLimit) * 100, 2) : null
            ]
        ];
    }

    // ... [Autres méthodes privées restent identiques]

    /**
     * @return array<mixed>
     */
    private function getDetailedMemoryInfo(): array
    {
        return [
            'current' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'limit' => $this->getMemoryLimit(),
            'real_usage' => memory_get_usage(false),
            'real_peak' => memory_get_peak_usage(false)
        ];
    }

    /**
     * @return array<mixed>
     */
    private function getDetailedDiskInfo(): array
    {
        $path = $this->getStringParameter('kernel.project_dir');
        return [
            'free' => disk_free_space($path),
            'total' => disk_total_space($path),
            'path' => $path
        ];
    }

    /**
     * @return array<mixed>
     */
    private function getPhpInfo(): array
    {
        return [
            'version' => PHP_VERSION,
            'sapi' => PHP_SAPI,
            'extensions' => get_loaded_extensions(),
            'ini' => [
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'post_max_size' => ini_get('post_max_size'),
                'upload_max_filesize' => ini_get('upload_max_filesize')
            ]
        ];
    }

    /**
     * @return array<mixed>
     */
    private function getSystemInfo(): array
    {
        return [
            'os' => PHP_OS,
            'hostname' => gethostname(),
            'load_average' => function_exists('sys_getloadavg') ? sys_getloadavg() : null
        ];
    }

    private function getMemoryLimit(): int
    {
        $memoryLimit = ini_get('memory_limit');

        if ($memoryLimit === '-1') {
            return 0; // Unlimited
        }

        return $this->convertToBytes($memoryLimit);
    }

    private function convertToBytes(string $value): int
    {
        $value = trim($value);
        $last = strtolower($value[strlen($value) - 1]);
        $numericValue = (int) $value;

        switch ($last) {
            case 'g':
                $numericValue *= 1024;
            // no break
            case 'm':
                $numericValue *= 1024;
            // no break
            case 'k':
                $numericValue *= 1024;
        }

        return $numericValue;
    }

    private function formatBytes(int|float $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * (int) $pow));

        return round($bytes, 2) . ' ' . $units[(int) $pow];
    }

    private function getAppVersion(): string
    {
        $projectDir = $this->getStringParameter('kernel.project_dir');

        // Try to get version from composer.json
        $composerFile = $projectDir . '/composer.json';
        if (file_exists($composerFile)) {
            $content = file_get_contents($composerFile);
            if ($content !== false) {
                $composer = json_decode($content, true);
                if (isset($composer['version'])) {
                    return $composer['version'];
                }
            }
        }

        // Try to get version from git
        if (is_dir($projectDir . '/.git')) {
            $version = exec('git describe --tags --always 2>/dev/null');
            if ($version) {
                return $version;
            }
        }

        return 'unknown';
    }

    /**
     * Get a string parameter from the container
     * @throws \InvalidArgumentException if parameter is not a string
     */
    private function getStringParameter(string $name): string
    {
        $value = $this->getParameter($name);
        if (!is_string($value)) {
            throw new \InvalidArgumentException(sprintf('Parameter "%s" must be a string, %s given', $name, get_debug_type($value)));
        }
        return $value;
    }
}
