<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;

class HomeController
{
    public function index(Request $request): Response
    {
        $featured = [
            ['title' => 'Yirgacheffe Coffee', 'price' => 20.0, 'image' => '/assets/img/sample-coffee.jpg'],
            ['title' => 'Shea Butter', 'price' => 8.5, 'image' => '/assets/img/sample-shea.jpg'],
            ['title' => 'Ethiopian Honey', 'price' => 12.0, 'image' => '/assets/img/sample-honey.jpg'],
        ];
        return Response::view('home', [
            'featured' => $featured,
        ]);
    }
}
