<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stripe\StripeClient;

class SetupStripeProducts extends Command
{
    protected $signature = 'stripe:setup-products {--force : Overwrite existing products}';

    protected $description = 'Create subscription products and prices in Stripe';

    public function handle(): int
    {
        $stripeSecret = config('cashier.secret');

        if (!$stripeSecret || $stripeSecret === 'sk_test_your_secret_key') {
            $this->error('Please configure STRIPE_SECRET in your .env file first.');
            return self::FAILURE;
        }

        $stripe = new StripeClient($stripeSecret);

        $this->info('Setting up Stripe products and prices...');
        $this->newLine();

        try {
            // Create main product
            $product = $this->createOrGetProduct($stripe);
            $this->info("Product ID: {$product->id}");
            $this->newLine();

            // Create prices for each paid tier
            $priceIds = [];
            $paidTiers = ['starter', 'pro', 'business'];

            foreach ($paidTiers as $tierKey) {
                $tierConfig = config("subscription.tiers.{$tierKey}");
                if (!$tierConfig) {
                    continue;
                }

                $this->info("Creating prices for {$tierConfig['name']} tier...");

                // Monthly price
                $monthlyPrice = $this->createPrice(
                    $stripe,
                    $product->id,
                    $tierConfig['prices']['monthly'],
                    'month',
                    $tierConfig['name']
                );
                $priceIds["{$tierKey}_monthly"] = $monthlyPrice->id;
                $this->line("  Monthly: {$monthlyPrice->id} (€" . number_format($tierConfig['prices']['monthly'] / 100, 2) . "/month)");

                // Yearly price
                $yearlyPrice = $this->createPrice(
                    $stripe,
                    $product->id,
                    $tierConfig['prices']['yearly'],
                    'year',
                    $tierConfig['name']
                );
                $priceIds["{$tierKey}_yearly"] = $yearlyPrice->id;
                $this->line("  Yearly: {$yearlyPrice->id} (€" . number_format($tierConfig['prices']['yearly'] / 100, 2) . "/year)");
            }

            $this->newLine();
            $this->info('Add these to your .env file:');
            $this->newLine();

            $this->line("STRIPE_PRICE_STARTER_MONTHLY={$priceIds['starter_monthly']}");
            $this->line("STRIPE_PRICE_STARTER_YEARLY={$priceIds['starter_yearly']}");
            $this->line("STRIPE_PRICE_PRO_MONTHLY={$priceIds['pro_monthly']}");
            $this->line("STRIPE_PRICE_PRO_YEARLY={$priceIds['pro_yearly']}");
            $this->line("STRIPE_PRICE_BUSINESS_MONTHLY={$priceIds['business_monthly']}");
            $this->line("STRIPE_PRICE_BUSINESS_YEARLY={$priceIds['business_yearly']}");

            $this->newLine();
            $this->info('Stripe products setup completed successfully!');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    protected function createOrGetProduct(StripeClient $stripe): \Stripe\Product
    {
        $productName = config('subscription.stripe.product_name', 'ePages Webhook Notifications');
        $productDescription = config('subscription.stripe.product_description');

        // Check if product already exists
        $products = $stripe->products->all(['limit' => 100]);

        foreach ($products->data as $product) {
            if ($product->name === $productName && $product->active) {
                if (!$this->option('force')) {
                    $this->info("Using existing product: {$product->name}");
                    return $product;
                }
            }
        }

        // Create new product
        $this->info("Creating new product: {$productName}");

        return $stripe->products->create([
            'name' => $productName,
            'description' => $productDescription,
        ]);
    }

    protected function createPrice(
        StripeClient $stripe,
        string $productId,
        int $amountCents,
        string $interval,
        string $tierName
    ): \Stripe\Price {
        $nickname = "{$tierName} - " . ucfirst($interval) . "ly";

        return $stripe->prices->create([
            'product' => $productId,
            'unit_amount' => $amountCents,
            'currency' => config('cashier.currency', 'eur'),
            'recurring' => [
                'interval' => $interval,
            ],
            'nickname' => $nickname,
        ]);
    }
}
