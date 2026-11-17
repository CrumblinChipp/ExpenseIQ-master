<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    public function step1()
    {
        return view('onboarding.step1');
    }

    public function saveStep1(Request $request)
    {
        $request->validate([
            'nickname' => 'required|max:255',
        ]);

        $user = Auth::user();

        $user->nickname = $request->nickname;
        $user->save();

        return redirect()->route('welcome.step2');
    }

    public function step2()
    {
        return view('onboarding.step2');
    }

    public function finish()
    {
        $user = Auth::user();

        $user->is_onboarded = true;
        $user->save();

        return redirect()->route('dashboard');
    }
}