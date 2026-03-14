<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Models\AppToken;

class GenerateAppToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-token {name : The name of the external application}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new API token for an external application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');

        $token = Str::random(60);

        $appToken = AppToken::create([
            'name' => $name,
            'token' => $token,
            'active' => true,
        ]);

        $this->info("Token generated successfully for application: {$name}");
        $this->warn("Please copy this token. You won't be able to see it again!");
        $this->line($token);

        return Command::SUCCESS;
    }
}
