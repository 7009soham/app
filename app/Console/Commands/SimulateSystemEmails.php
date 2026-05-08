<?php

namespace App\Console\Commands;

use App\Services\SystemEmailSimulationService;
use Illuminate\Console\Command;

class SimulateSystemEmails extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:simulate-system-emails
                            {--to=soham.tare@somaiya.edu : Recipient email address}
                            {--name=Soham Tare : Recipient display name}
                            {--preview-only : Generate previews only without sending emails}';

    /**
     * The console command description.
     */
    protected $description = 'Simulate all system-generated emails for testing and preview';

    /**
     * Execute the console command.
     */
    public function handle(SystemEmailSimulationService $simulationService): int
    {
        $recipientEmail = (string) $this->option('to');
        $recipientName = (string) $this->option('name');
        $previewOnly = (bool) $this->option('preview-only');

        $this->info('Running system email simulation...');
        $this->line('Recipient: ' . $recipientName . ' <' . $recipientEmail . '>');
        $this->line('Mode: ' . ($previewOnly ? 'preview-only' : 'send-and-preview'));

        $result = $simulationService->simulate(
            $recipientEmail,
            $recipientName,
            !$previewOnly
        );

        $this->newLine();
        $this->info('Simulation completed.');
        $this->line('Preview directory: ' . $result['preview_directory']);
        $this->line('Total emails: ' . $result['summary']['total']);
        $this->line('Sent: ' . $result['summary']['sent']);
        $this->line('Failed: ' . $result['summary']['failed']);

        foreach ($result['emails'] as $emailResult) {
            $line = '- ' . $emailResult['label'] . ' => ' . strtoupper($emailResult['status']);
            $line .= ' | Subject: ' . $emailResult['subject'];
            $line .= ' | Preview: ' . $emailResult['preview_file'];

            if (!empty($emailResult['error'])) {
                $line .= ' | Error: ' . $emailResult['error'];
            }

            $this->line($line);
        }

        return self::SUCCESS;
    }
}
