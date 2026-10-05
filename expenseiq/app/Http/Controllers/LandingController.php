<?php

namespace App\Http\Controllers;

class LandingController extends Controller
{
    public function index()
    {
        $features = [
            [
                'title' => 'Expense Entry',
                'icon' => 'fa-receipt',
                'description' => 'Quickly log every expense.',
            ],
        ];

        return view('landing-page', [
            'features' => $features,
        ]);
    }
}