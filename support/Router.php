<?php

class Router
{
    /**
     * @var array Stores all registered routes
     */
    private array $routes = [
        'GET' => [],
        'POST' => []
    ];

    /**
     * @var string Base directory for PHP files
     */
    private string $baseDirectory;

    /**
     * @var string|null Path to fallback file
     */
    private ?string $fallbackFile = null;

    /**
     * Router constructor
     *
     * @param string $baseDirectory Base directory for PHP files
     */
    public function __construct(string $baseDirectory)
    {
        $this->baseDirectory = rtrim($baseDirectory, '/');
    }

    /**
     * Register a GET route
     *
     * @param string $path Route path
     * @param string $file PHP file path relative to base directory
     * @return void
     */
    public function get(string $path, string $file): void
    {
        $this->addRoute('GET', $path, $file);
    }

    /**
     * Register a POST route
     *
     * @param string $path Route path
     * @param string $file PHP file path relative to base directory
     * @return void
     */
    public function post(string $path, string $file): void
    {
        $this->addRoute('POST', $path, $file);
    }

    /**
     * Set fallback file for unmatched routes
     *
     * @param string $file Path to fallback PHP file
     * @return void
     */
    public function setFallback(string $file): void
    {
        $this->fallbackFile = $file;
    }

    /**
     * Add route to the routes collection
     *
     * @param string $method HTTP method
     * @param string $path Route path
     * @param string $file PHP file to execute
     * @return void
     */
    private function addRoute(string $method, string $path, string $file): void
    {
        // Convert path parameters to regex pattern
        $pattern = preg_replace('/\{([^}]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = "#^" . $pattern . "$#";

        $this->routes[$method][$pattern] = [
            'file' => $file,
            'path' => $path
        ];
    }

    /**
     * Resolve and execute the current route
     *
     * @param string $method HTTP method
     * @param string $uri Request URI
     * @return void
     */
    public function resolve(string $method, string $uri): void
    {
        try {
            $method = strtoupper($method);
            
            if (!isset($this->routes[$method])) {
                $this->handleNotFound();
                return;
            }

            foreach ($this->routes[$method] as $pattern => $route) {
                if (preg_match($pattern, $uri, $matches)) {
                    // Remove numeric keys from matches
                    $params = array_filter($matches, fn($key) => !is_numeric($key), ARRAY_FILTER_USE_KEY);
                    
                    $this->executeFile($route['file'], $params);
                    return;
                }
            }

            $this->handleNotFound();
        } catch (\Exception $e) {
            // Log the error in production
            error_log("Router Error: " . $e->getMessage());
            $this->handleError($e);
        }
    }

    /**
     * Execute the PHP file for the matched route
     *
     * @param string $file File path relative to base directory
     * @param array $params Route parameters
     * @return void
     * @throws \Exception
     */
    private function executeFile(string $file, array $params = []): void
    {
        $filePath = $this->baseDirectory . '/' . ltrim($file, '/');
        
        // Prevent directory traversal
        $realBasePath = realpath($this->baseDirectory);
        $realFilePath = realpath($filePath);

        if ($realFilePath === false || strpos($realFilePath, $realBasePath) !== 0) {
            throw new \Exception('Invalid file path');
        }

        if (!file_exists($filePath)) {
            throw new \Exception("Route file not found: $file");
        }

        // Make route params available to the included file
        $routeParams = $params;
        
        // Execute the file
        include $filePath;
    }

    /**
     * Handle not found routes
     *
     * @return void
     */
    private function handleNotFound(): void
    {
        if ($this->fallbackFile !== null) {
            $this->executeFile($this->fallbackFile);
            return;
        }

        http_response_code(404);
        echo 'Page not found';
    }

    /**
     * Handle router errors
     *
     * @param \Exception $e Exception object
     * @return void
     */
    private function handleError(\Exception $e): void
    {
        http_response_code(500);
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
            echo 'Router Error: ' . $e->getMessage();
        } else {
            echo 'Internal Server Error';
        }
    }
}