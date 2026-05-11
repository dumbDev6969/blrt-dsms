<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureDialogflow();
    }

   private function configureDialogflow(): void
{
    // Skip validation during artisan maintenance commands
    if ($this->app->runningInConsole() && !$this->app->runningUnitTests()) {
        return;
    }

    $path = config('services.dialogflow.credentials_json');

    if (blank($path) || ! file_exists($path)) {
        throw new \RuntimeException(
            "Dialogflow credentials file not found at: {$path}. " .
            "Please ensure GOOGLE_APPLICATION_CREDENTIALS is set correctly and the file exists."
        );
    }

    putenv("GOOGLE_APPLICATION_CREDENTIALS={$path}");
}

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(app()->isProduction());

        Password::defaults(fn(): ?Password => app()->isProduction() ? Password::min(12)->mixedCase()->letters()->numbers()->symbols()->uncompromised() : null);
    }
}
