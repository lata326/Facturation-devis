<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class CheckNotifications extends Command
{
   protected $signature = 'notifications:check';
    protected $description = 'Vérifier et envoyer les notifications automatiques';

    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    public function handle()
    {
        $this->info('Vérification des notifications en cours...');
        
        $this->notificationService->checkAndSendNotifications();
        
        $this->info('Vérification terminée avec succès.');
    }
}
