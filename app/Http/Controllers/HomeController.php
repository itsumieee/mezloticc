<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('GameHub');
    }

    public function roblox(): Response
    {
        return Inertia::render('Home', [
            'query' => request('q', ''),
        ]);
    }
}