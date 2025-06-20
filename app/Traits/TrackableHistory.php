<?php

namespace App\Traits;

use App\Services\HistoriqueService;
use Illuminate\Support\Facades\Log;

trait TrackableHistory
{
    protected static function bootTrackableHistory()
    {
        static::created(function ($model) {
            static::enregistrerHistorique($model, 'create');
        });

        static::updated(function ($model) {
            static::enregistrerHistorique($model, 'update');
        });

        static::deleted(function ($model) {
            static::enregistrerHistorique($model, 'delete');
        });
    }

    protected static function enregistrerHistorique($model, $action)
    {
        try {
            $historiqueService = app(HistoriqueService::class);
            
            $typeDocument = static::getTypeDocument();
            $changesData = static::prepareChangesData($model, $action);

            foreach ($changesData as $change) {
                $historiqueService->enregistrerModification([
                    'company_id' => $model->company_id,
                    'type_document' => $typeDocument,
                    'document_id' => $model->id,
                    'action' => $action,
                    'champ_modifie' => $change['champ'] ?? null,
                    'ancienne_valeur' => $change['ancienne_valeur'] ?? null,
                    'nouvelle_valeur' => $change['nouvelle_valeur'] ?? null,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement de l\'historique: ' . $e->getMessage());
        }
    }

    protected static function prepareChangesData($model, $action)
    {
        if ($action === 'create') {
            return [['champ' => 'creation', 'nouvelle_valeur' => 'Document créé']];
        }

        if ($action === 'delete') {
            return [['champ' => 'suppression', 'ancienne_valeur' => 'Document supprimé']];
        }

        // Pour les updates, on récupère les changements
        $changes = [];
        foreach ($model->getDirty() as $field => $newValue) {
            $changes[] = [
                'champ' => $field,
                'ancienne_valeur' => $model->getOriginal($field),
                'nouvelle_valeur' => $newValue
            ];
        }

        return $changes;
    }

    // À implémenter dans chaque modèle
    abstract protected static function getTypeDocument(): string;
}
