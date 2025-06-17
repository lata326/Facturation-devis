<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ClientController extends Controller
{
    /**
    * Liste tous les clients avec pagination et recherche
    */
        public function index(Request $request): JsonResponse
        {
            try {
                $query = Client::query();

                // Recherche
                if ($request->has('search') && !empty($request->search)) {
                    $query->search($request->search);
                }

                // Filtres
                if ($request->has('ville') && !empty($request->ville)) {
                    $query->where('ville', 'LIKE', "%{$request->ville}%");
                }

                if ($request->has('pays') && !empty($request->pays)) {
                    $query->where('pays', 'LIKE', "%{$request->pays}%");
                }

                // Tri
                $sortBy = $request->get('sort_by', 'created_at');
                $sortOrder = $request->get('sort_order', 'desc');
                
                if (in_array($sortBy, ['nom', 'prenom', 'mail', 'ville', 'pays', 'created_at'])) {
                    $query->orderBy($sortBy, $sortOrder);
                }

                // Pagination
                $perPage = min($request->get('per_page', 15), 100);
                $clients = $query->paginate($perPage);

                return response()->json([
                    'success' => true,
                    'data' => $clients->items(),
                    'pagination' => [
                        'current_page' => $clients->currentPage(),
                        'last_page' => $clients->lastPage(),
                        'per_page' => $clients->perPage(),
                        'total' => $clients->total(),
                        'from' => $clients->firstItem(),
                        'to' => $clients->lastItem(),
                    ],
                    'message' => 'Clients récupérés avec succès'
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la récupération des clients',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

    
    /**
    * Crée un nouveau client
    */
    public function store(ClientRequest $request): JsonResponse
    {
        try {
            $client = Client::create($request->validated());

            return response()->json([
                'success' => true,
                'data' => $client,
                'message' => 'Client créé avec succès'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du client',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Affiche un client spécifique
     */
    public function show(Client $client): JsonResponse
    {
       try {
            return response()->json([
                'success' => true,
                'data' => $client,
                'message' => 'Client récupéré avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Client non trouvé',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Met à jour un client existant
     */
    public function update(ClientRequest $request, Client $client): JsonResponse
    {
        try {
            $client->update($request->validated());

            return response()->json([
                'success' => true,
                'data' => $client->fresh(),
                'message' => 'Client mis à jour avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du client',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprime un client
     */
    public function destroy(Client $client): JsonResponse
    {
        try {
            $client->delete();

            return response()->json([
                'success' => true,
                'message' => 'Client supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du client',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
