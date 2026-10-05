<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    /**
     * Settings Page
     */
    public function index()
    {
        return view('settings.index', [
            'user' => auth()->user(),
            'page' => 'personal',
        ]);
    }

    public function password()
    {
        return view('settings.index', [
            'user' => auth()->user(),
            'page' => 'password',
        ]);
    }

    public function danger()
    {
        return view('settings.index', [
            'user' => auth()->user(),
            'page' => 'danger',
        ]);
    }
    /**
     * Update Personal Information
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        switch ($request->type) {

            case 'nickname':

                $request->validate([
                    'nickname' => 'required|string|max:255',
                ]);

                $user->update([
                    'nickname' => $request->nickname,
                ]);

                break;

            case 'fullname':

                $request->validate([
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                ]);

                $user->update([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                ]);

                break;

            case 'email':

                $request->validate([
                    'email' => 'required|email|unique:users,email,' . $user->id,
                ]);

                $user->update([
                    'email' => $request->email,
                ]);

                break;

            case 'budget':

                $request->validate([
                    'monthly_budget' => 'required|numeric|min:0',
                ]);

                $user->update([
                    'monthly_budget' => $request->monthly_budget,
                ]);

                break;
        }

        return redirect()
            ->back()
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Change Password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([

            'current_password' => 'required',

            'password' => 'required|min:8|confirmed',

        ]);

        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {

            return back()->withErrors([
                'current_password' => 'Current password is incorrect.',
            ]);
        }

        $user->update([

            'password' => Hash::make($request->password),

        ]);

        return redirect()
            ->route('settings')
            ->with('success', 'Password updated successfully.');
    }

    /**
     * Delete Expenses, Budgets and Reports
     */
    public function clearData()
    {
        $userId = Auth::id();

        Expense::where('user_id', $userId)->delete();

        Budget::where('user_id', $userId)->delete();

        return redirect()
            ->route('settings')
            ->with('success', 'All data has been deleted.');
    }

    /**
     * Delete Account
     */
    public function deleteAccount(Request $request)
    {
        $user = Auth::user();

        Expense::where('user_id', $user->id)->delete();

        Budget::where('user_id', $user->id)->delete();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }
}