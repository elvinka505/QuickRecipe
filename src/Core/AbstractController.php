<?php

declare(strict_types=1);

namespace App\Core;

abstract class AbstractController
{
    public function __construct(protected Router $router, protected Logger $logger)
    {
    }

    protected function render(string $view, array $data = []): void
    {
        extract($data);

        $viewPath = PROJECT_ROOT . '/src/Views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View не найден: {$viewPath}");
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        $layoutPath = PROJECT_ROOT . '/src/Views/layouts/main.php';
        require $layoutPath;
    }

    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    protected function setStatusCode(int $code): void
    {
        http_response_code($code);
    }
    protected function requireAuth(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
    }
}
