<?php

namespace App\Console\Commands;

use App\Jobs\PollShopOrders;
use App\Models\Shop;
use Illuminate\Console\Command;

class PollAllShops extends Command
{
    protected $signature = 'shops:poll {--sync : Run synchronously instead of dispatching jobs}';

    protected $description = 'Poll all active shops for new orders and send webhook notifications';

    public function handle()
    {
        $shops = Shop::where('active', true)->get();
        
        if ($shops->isEmpty()) {
            $this->info('No active shops found to poll.');
            return 0;
        }

        $this->info("Found {$shops->count()} active shops to poll.");

        $polledCount = 0;
        
        foreach ($shops as $shop) {
            if ($shop->shouldPoll()) {
                if ($this->option('sync')) {
                    $this->info("Polling shop: {$shop->name} (ID: {$shop->id}) - Synchronous");
                    (new PollShopOrders($shop))->handle();
                } else {
                    $this->info("Dispatching job for shop: {$shop->name} (ID: {$shop->id})");
                    PollShopOrders::dispatch($shop);
                }
                $polledCount++;
            } else {
                $this->line("Skipping shop: {$shop->name} (not ready for polling)");
            }
        }

        $this->info("Processed {$polledCount} shops.");
        
        return 0;
    }
}
