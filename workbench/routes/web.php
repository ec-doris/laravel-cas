<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use Workbench\App\Models\User;

Route::get('/', function () {
    return view('home', [
        'user' => auth('laravel-cas')->user(),
    ]);
})->name('home');

Route::middleware(['web', 'cas.auth'])->group(function () {
    Route::get('/dashboard', function () {
        /** @var User $user */
        $user = auth('laravel-cas')->user();

        return view('dashboard', [
            'user' => $user,
        ]);
    })->name('dashboard');

    Route::get('/whoami', function (): JsonResponse {
        /** @var User $user */
        $user = auth('laravel-cas')->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'departmentNumber' => $user->departmentNumber,
            'department_number' => $user->department_number,
            'organisation' => $user->organisation,
        ]);
    })->name('whoami');
});
