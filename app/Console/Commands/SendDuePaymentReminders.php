<?php

namespace App\Console\Commands;

use App\Mail\PaymentDueReminderMail;
use App\Models\MonthlyTaxBill;
use App\Models\PropertyTaxAnnualBill;
use App\Models\SiteSetting;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDuePaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:send-due-reminders {--days= : Override days before due date}';

    /**
     * The console command description.
     */
    protected $description = 'Send advance due-date reminder emails for water/property tax bills';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $enabled = SiteSetting::get('due_reminder_enabled', '1') === '1';
        if (!$enabled) {
            $this->info('Due reminder notifications are disabled in settings.');
            return self::SUCCESS;
        }

        $daysBefore = $this->option('days');
        if ($daysBefore === null || $daysBefore === '') {
            $daysBefore = (int) SiteSetting::get('due_reminder_days_before', '3');
        } else {
            $daysBefore = (int) $daysBefore;
        }

        if ($daysBefore < 0) {
            $daysBefore = 0;
        }

        $targetDate = Carbon::today()->addDays($daysBefore);
        $this->info('Sending due reminders for bills due on ' . $targetDate->format('Y-m-d') . ' (' . $daysBefore . ' days before due date).');

        $waterCount = $this->sendWaterReminders($targetDate);
        $propertyCount = $this->sendPropertyReminders($targetDate);

        $this->info('Water reminders sent: ' . $waterCount);
        $this->info('Property reminders sent: ' . $propertyCount);
        $this->info('Total reminders sent: ' . ($waterCount + $propertyCount));

        return self::SUCCESS;
    }

    private function sendWaterReminders(Carbon $targetDate): int
    {
        $count = 0;

        $bills = MonthlyTaxBill::with('citizen')
            ->where('tax_type', 'water_tax')
            ->whereDate('due_date', $targetDate->toDateString())
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->where('balance', '>', 0)
            ->whereNull('due_reminder_sent_at')
            ->get();

        foreach ($bills as $bill) {
            $citizen = $bill->citizen;
            if (!$citizen || empty($citizen->email)) {
                continue;
            }

            try {
                Mail::to($citizen->email)->send(new PaymentDueReminderMail([
                    'citizen_name' => $citizen->name,
                    'tax_type' => 'water_tax',
                    'customer_no' => $bill->customer_no,
                    'bill_no' => $bill->customer_no . '-' . str_pad((string) $bill->bill_month, 2, '0', STR_PAD_LEFT) . '-' . $bill->bill_year,
                    'amount_due' => (float) $bill->balance,
                    'due_date' => optional($bill->due_date)->format('d M Y'),
                    'billing_period' => $bill->month_name . ' ' . $bill->bill_year,
                    'payment_link' => url('/citizen/login'),
                ]));

                $bill->update(['due_reminder_sent_at' => now()]);
                $count++;
            } catch (\Throwable $e) {
                Log::error('Water due reminder email failed', [
                    'bill_id' => $bill->id,
                    'citizen_id' => $citizen->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }

    private function sendPropertyReminders(Carbon $targetDate): int
    {
        $count = 0;

        $bills = PropertyTaxAnnualBill::with('citizen')
            ->whereDate('due_date', $targetDate->toDateString())
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->where('balance', '>', 0)
            ->whereNull('due_reminder_sent_at')
            ->get();

        foreach ($bills as $bill) {
            $citizen = $bill->citizen;
            if (!$citizen || empty($citizen->email)) {
                continue;
            }

            try {
                Mail::to($citizen->email)->send(new PaymentDueReminderMail([
                    'citizen_name' => $citizen->name,
                    'tax_type' => 'property_tax',
                    'customer_no' => $bill->customer_no,
                    'bill_no' => $bill->bill_no,
                    'amount_due' => (float) $bill->balance,
                    'due_date' => optional($bill->due_date)->format('d M Y'),
                    'billing_period' => $bill->financial_year,
                    'payment_link' => url('/citizen/login'),
                ]));

                $bill->update(['due_reminder_sent_at' => now()]);
                $count++;
            } catch (\Throwable $e) {
                Log::error('Property due reminder email failed', [
                    'bill_id' => $bill->id,
                    'citizen_id' => $citizen->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }
}
