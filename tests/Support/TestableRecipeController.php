<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Controllers\RecipeController;

class TestableRecipeController extends RecipeController
{
    public ?string $renderedView = null;
    public array $renderedData = [];
    public ?string $redirectedTo = null;
    public ?int $statusCode = null;

    protected function render(string $view, array $data = []): void
    {
        $this->renderedView = $view;
        $this->renderedData = $data;
    }

    protected function redirect(string $url): void
    {
        $this->redirectedTo = $url;
    }

    protected function setStatusCode(int $code): void
    {
        $this->statusCode = $code;
    }
}
