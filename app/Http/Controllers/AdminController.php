<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'total_users'    => User::count(),
            'total_shops'    => Shop::count(),
            'active_shops'   => Shop::where('active', true)->count(),
            'total_webhooks' => User::sum('webhooks_sent_this_period'),
        ];

        $users = User::withCount('shops')
            ->orderBy('created_at', 'desc')
            ->get();

        $shops = Shop::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        $queuedJobs = DB::table('jobs')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($job) {
                $payload = json_decode($job->payload, true);
                $job->job_name = class_basename($payload['displayName'] ?? $payload['job'] ?? 'Unknown');

                $job->related_shop = null;
                try {
                    $command = unserialize($payload['data']['command'] ?? '');
                    if (is_object($command)) {
                        $ref = new \ReflectionObject($command);
                        if ($ref->hasProperty('shop')) {
                            $prop = $ref->getProperty('shop');
                            $prop->setAccessible(true);
                            $val = $prop->getValue($command);
                            if ($val instanceof \App\Models\Shop) {
                                $job->related_shop = $val;
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    // ignore deserialization errors
                }

                return $job;
            });

        $failedJobs = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($job) {
                $payload = json_decode($job->payload, true);
                $job->job_name = class_basename($payload['displayName'] ?? $payload['job'] ?? 'Unknown');
                return $job;
            });

        return view('admin.dashboard', compact('stats', 'users', 'shops', 'queuedJobs', 'failedJobs'));
    }

    public function showUser(User $user)
    {
        $user->loadCount('shops');
        $shops = $user->shops()->orderBy('created_at', 'desc')->get();
        $tiers = array_keys(config('subscription.tiers'));

        return view('admin.user', compact('user', 'shops', 'tiers'));
    }

    public function updateUser(Request $request, User $user)
    {
        $tiers = array_keys(config('subscription.tiers'));

        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'email'              => ['required', 'email', 'unique:users,email,' . $user->id],
            'subscription_tier'  => ['required', 'in:' . implode(',', $tiers)],
            'trial_ends_at'      => ['nullable', 'date'],
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User updated successfully.');
    }
}
