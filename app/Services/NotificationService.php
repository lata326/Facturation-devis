<?php

namespace App\Services;

use App\Models\Facture;
use App\Models\Notification;
use App\Events\NotificationSent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function checkAndSendNotifications()
    {
        Log::info('Vérification des notifications automatiques démarrée');
        
        $this->checkEcheanceProche();
        $this->checkRetardPaiement();
        
        Log::info('Vérification des notifications automatiques terminée');
    }

    private function checkEcheanceProche()
    {
        $factures = Facture::echeanceProche()->get();
        
        foreach ($factures as $facture) {
            if (!$facture->hasNotification('echeance_proche')) {
                $this->createNotification($facture, 'echeance_proche');
            }
        }
        
        Log::info("Notifications d'échéance proche vérifiées: " . $factures->count() . " factures");
    }

    private function checkRetardPaiement()
    {
        $factures = Facture::enRetard()->get();
        
        foreach ($factures as $facture) {
            if (!$facture->hasNotification('retard_paiement')) {
                // Mettre à jour le statut de la facture
                $facture->update(['status' => 'en_retard']);
                
                $this->createNotification($facture, 'retard_paiement');
            }
        }
        
        Log::info("Notifications de retard de paiement vérifiées: " . $factures->count() . " factures");
    }

    private function createNotification(Facture $facture, string $type)
    {
        $messageData = $this->getMessageData($facture, $type);
        
        $notification = Notification::create([
            'company_id' => $facture->client_id,
            'facture_id' => $facture->facture_id,
            'type' => $type,
            'titre' => $messageData['titre'],
            'message' => $messageData['message'],
            'envoye_at' => Carbon::now(),
        ]);

        // Déclencher un événement pour les notifications en temps réel
        event(new NotificationSent($notification));
        
        Log::info("Notification créée - Type: {$type}, Facture: {$facture->numero_facture}");
    }

    private function getMessageData(Facture $facture, string $type)
    {
        switch ($type) {
            case 'echeance_proche':
                return [
                    'titre' => 'Échéance de facture proche',
                    'message' => "Votre facture #{$facture->numero_facture} d'un montant de {$facture->montant_total}€ arrive à échéance le {$facture->date_echeance->format('d/m/Y')}. Pensez à effectuer votre paiement."
                ];
            
            case 'retard_paiement':
                $joursRetard = Carbon::now()->diffInDays($facture->date_echeance);
                return [
                    'titre' => 'Facture en retard de paiement',
                    'message' => "Votre facture #{$facture->numero_facture} d'un montant de {$facture->montant_total}€ est en retard de {$joursRetard} jour(s). Veuillez procéder au paiement dans les plus brefs délais."
                ];
            
            default:
                return [
                    'titre' => 'Notification facture',
                    'message' => 'Une action est requise pour votre facture.'
                ];
        }
    }
}