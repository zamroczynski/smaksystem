<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\HolidayService;

class GenerateHolidaysCommand extends Command
{
    /**
     * Name and reference number of the command.
     * {--year= : The year for which holidays are to be generated. The default is the next year.}
     */
    protected $signature = 'app:generate-holidays {--year=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates and saves the dates of fixed and movable holidays for a given year in the database.';

    /**
     * Execute the console command.
     */
    public function handle(HolidayService $holidayService)
    {
        $year = $this->option('year') ?? (int) date('Y') + 1;

        $this->info("Starting to generate holidays for the year {$year}...");

        try {
            $holidayService->generateForYear((int) $year);
        } catch (\Exception $e) {
            $this->error("An error occurred: " . $e->getMessage());
            return self::FAILURE;
        }

        $this->info("Holidays for the year have been successfully generated. {$year}.");
        return self::SUCCESS;
    }
}
