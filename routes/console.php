<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// La commande affinite:tester (App\Console\Commands\TesterAffinite)
// est découverte automatiquement par Laravel 11 via app/Console/Commands/
