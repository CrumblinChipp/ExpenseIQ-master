<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $dashboard = $this->dashboardService
            ->getDashboardData(Auth::id());

        return view('livewire.dashboard.index', compact('dashboard'));
    }
}