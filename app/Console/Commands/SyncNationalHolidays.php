<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
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
            $holidayRows = [];
            foreach ($holidays as $holiday) {
                if (! is_array($holiday) || ! filter_var($holiday['is_national_holiday'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                    continue;
                }

                $holidayDate = $holiday['holiday_date'] ?? null;
                $name = trim((string) ($holiday['holiday_name'] ?? ''));
                if (! is_string($holidayDate) || ! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/D', $holidayDate, $dateParts)
                    || (int) $dateParts[1] !== $year
                    || ! checkdate((int) $dateParts[2], (int) $dateParts[3], (int) $dateParts[1])
                    || $name === '') {
                    continue;
                }

                $holidayRows[$holidayDate] = ['holiday_date' => $holidayDate, 'name' => $name];
            }

            if ($holidayRows === []) {
                $this->error("Holiday data for {$year} did not contain any valid national holidays.");

                return self::FAILURE;
            }

            DB::transaction(function () use ($year, $holidayRows): void {
                $timestamp = now();
                $rows = array_map(fn (array $holidayRow): array => [
                    ...$holidayRow,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ], array_values($holidayRows));

                DB::table('national_holidays')->upsert($rows, ['holiday_date'], ['name', 'updated_at']);
                DB::table('national_holidays')->whereBetween('holiday_date', [$year.'-01-01', $year.'-12-31'])
                    ->whereNotIn('holiday_date', array_keys($holidayRows))
                    ->delete();
            });
            $this->info("Synchronized national holidays for {$year}.");
        }

        return self::SUCCESS;
    }
}
