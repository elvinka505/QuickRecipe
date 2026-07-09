<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AbstractController;
use App\Core\Attributes\Route;

class HomeController extends AbstractController
{
    #[Route('/')]
    public function index(): void
    {
        $this->render('home/index', [
            'title' => 'QuickRecipe — Что приготовить?'
        ]);
    }
}
