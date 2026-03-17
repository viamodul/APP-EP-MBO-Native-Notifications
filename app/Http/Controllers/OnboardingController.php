<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class OnboardingController extends Controller
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Show the registration form for new users during onboarding.
     */
    public function showRegister(Request $request)
    {
        $onboarding = session('epages_onboarding');

        if (!$onboarding) {
            return redirect('/')->withErrors(['error' => 'No onboarding session found.']);
        }

        $shop = Shop::find($onboarding['shop_id']);

        if (!$shop) {
            return redirect('/')->withErrors(['error' => 'Shop not found.']);
        }

        return view('onboarding.register', [
            'shop' => $shop,
            'shopName' => $onboarding['shop_name'],
            'returnUrl' => $onboarding['return_url'] ?? null,
        ]);
    }

    /**
     * Handle user registration during onboarding.
     */
    public function register(Request $request)
    {
        $onboarding = session('epages_onboarding');

        if (!$onboarding) {
            return redirect('/')->withErrors(['error' => 'No onboarding session found.']);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'webhook_url' => ['nullable', 'url'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Initialize trial for new user
        $this->subscriptionService->initializeTrial($user);

        // Link the shop to the user and set webhook URL if provided
        $shopData = ['user_id' => $user->id];
        if (!empty($validated['webhook_url'])) {
            $shopData['webhook_url'] = $validated['webhook_url'];
        }
        Shop::where('id', $onboarding['shop_id'])->update($shopData);

        Log::info('User registered during onboarding', [
            'user_id' => $user->id,
            'shop_id' => $onboarding['shop_id'],
        ]);

        Auth::login($user);

        return redirect()->route('epages.onboarding.success');
    }

    /**
     * Show the login form for existing users during onboarding.
     */
    public function showLogin(Request $request)
    {
        $onboarding = session('epages_onboarding');

        if (!$onboarding) {
            return redirect('/')->withErrors(['error' => 'No onboarding session found.']);
        }

        $shop = Shop::find($onboarding['shop_id']);

        if (!$shop) {
            return redirect('/')->withErrors(['error' => 'Shop not found.']);
        }

        return view('onboarding.login', [
            'shop' => $shop,
            'shopName' => $onboarding['shop_name'],
            'returnUrl' => $onboarding['return_url'] ?? null,
        ]);
    }

    /**
     * Handle user login during onboarding.
     */
    public function login(Request $request)
    {
        $onboarding = session('epages_onboarding');

        if (!$onboarding) {
            return redirect('/')->withErrors(['error' => 'No onboarding session found.']);
        }

        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'webhook_url' => ['nullable', 'url'],
        ]);

        if (!Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']], $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email', 'webhook_url');
        }

        $request->session()->regenerate();

        // Link the shop to the user and set webhook URL if provided
        $shopData = ['user_id' => Auth::id()];
        if (!empty($validated['webhook_url'])) {
            $shopData['webhook_url'] = $validated['webhook_url'];
        }
        Shop::where('id', $onboarding['shop_id'])->update($shopData);

        Log::info('User logged in during onboarding', [
            'user_id' => Auth::id(),
            'shop_id' => $onboarding['shop_id'],
        ]);

        return redirect()->route('epages.onboarding.success');
    }

    /**
     * Show the success page after onboarding.
     */
    public function showSuccess(Request $request)
    {
        $onboarding = session('epages_onboarding');

        if (!$onboarding) {
            return redirect()->route('dashboard');
        }

        $shop = Shop::find($onboarding['shop_id']);

        // Clear the onboarding session
        session()->forget('epages_onboarding');

        return view('onboarding.success', [
            'shop' => $shop,
            'returnUrl' => $onboarding['return_url'] ?? null,
        ]);
    }
}
