<?php

namespace LogicLeap\PhpServerCore;

use Exception;

/**
 * Class Application
 */
class Application
{
    public static Application $app;
    public Database $db;
    public Router $router;
    public Request $request;

    public static string $ROOT_DIR;

    private array $exceptionHandler;

    /**
     * Return an instance of Application
     * @param array $config parse a nested array of configurations.
     */
    public function __construct(array $config)
    {
        self::$app = $this;
        self::$ROOT_DIR = $config['rootPath'];

        date_default_timezone_set('Asia/Colombo');

        $this->request = new Request();
        $this->router = new Router($this->request);
        $this->db = new Database($config['db']['servername'], $config['db']['username'], $config['db']['password'],
            $config['db']['dbName']);
        $this->exceptionHandler = $config['ExceptionHandler'];
    }

    /**
     * Start the application. Call to resolveRoute.
     * If any error occurred, call to render the relevant error page.
     */
    public function run(): void
    {
        try {
            $this->router->resolveRoute();
        } catch (Exception $e) {
            $controller = new $this->exceptionHandler[0];
            $controller->{$this->exceptionHandler[1]}($e->getCode(), $e->getMessage());
        }
    }
}