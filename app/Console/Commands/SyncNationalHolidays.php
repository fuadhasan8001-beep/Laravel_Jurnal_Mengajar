<?php

namespace App\Console\Commands;

use App\Models\NationalHoliday;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

#[Signature('calendar:sync-holidays {--year= : Year to synchronize}')]
#[Description('Synchronize Indonesian national holidays')]
class SyncNationalHolidays extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $years = $this->option('year') ? [(int) $this->option('year')] : [(int) now()->year, (int) now()->addYear()->year];
        foreach ($years as $year) {
            $holidays = null;
            foreach (['https://api-harilibur.pages.dev/api', 'https://api-harilibur.netlify.app/api'] as $endpoint) {
                try {
                    $response = Http::timeout(15)->get($endpoint, ['year' => $year]);
                    $payload = $response->json();
                    if ($response->successful() && is_array($payload) && $payload !== []) {
                        $holidays = $payload;
                        break;
                    }
                } catch (ConnectionException) {
                    continue;
                }
            }
            if ($holidays === null) {
                $this->error("Holiday data for {$year} could not be fetched from the available sources.");

                return self::FAILURE;
            }
            foreach ($holidays as $holiday) {
                if (($holiday['is_national_holiday'] ?? false) && ! empty($holiday['holiday_date']) && ! empty($holiday['holiday_name'])) {
                    NationalHoliday::updateOrCreate(['holiday_date' => $holiday['holiday_date']], ['name' => $holiday['holiday_name']]);
                }
            }
            $this->info("Synchronized national holidays for {$year}.");
        }

        return self::SUCCESS;
    }
}
