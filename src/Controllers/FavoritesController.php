<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AbstractController;
use App\Core\Attributes\Route;
use App\Models\Favorite;

class FavoritesController extends AbstractController
{
    #[Route('/favorites')]
    public function list(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $this->redirect('/login');
            return;
        }
        $this->render('favorites/list', [
            'title'     => 'Избранное',
            'favorites' => Favorite::getByUser((int) $userId),
        ]);
    }

    #[Route('/favorites/add', 'POST')]
    public function add(): void
    {
        $this->requireAuth();
        $userId   = $_SESSION['user_id'] ?? null;
        $recipeId = $this->router->getBody()['recipe_id'] ?? null;
        if ($userId && $recipeId) {
            Favorite::add((int) $userId, (int) $recipeId);
        }
        $this->redirect('/favorites');
    }

    #[Route('/favorites/remove', 'POST')]
    public function remove(): void
    {
        $this->requireAuth();
        $userId   = $_SESSION['user_id'] ?? null;
        $recipeId = $this->router->getBody()['recipe_id'] ?? null;
        if ($userId && $recipeId) {
            Favorite::remove((int) $userId, (int) $recipeId);
        }
        $this->redirect('/favorites');
    }
}
