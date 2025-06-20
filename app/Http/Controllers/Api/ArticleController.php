<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class ArticleController extends Controller
{
    /**
     * Lister les articles avec pagination et filtres
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Article::query();

            // Filtres
            if ($request->filled('company_id')) {
                $query->bycompanies($request->company_id);
            }

            if ($request->filled('search')) {
                $query->search($request->search);
            }

            // Filtre par date
            if ($request->filled('date_debut') || $request->filled('date_fin')) {
                $query->byDateRange($request->date_debut, $request->date_fin);
            }

            // Tri
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            if (in_array($sortBy, ['designation', 'code_article', 'prix_unitaire', 'created_at', 'updated_at'])) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = min($request->get('per_page', 15), 100);
            $articles = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Articles récupérés avec succès',
                'data' => $articles->items(),
                'pagination' => [
                    'current_page' => $articles->currentPage(),
                    'last_page' => $articles->lastPage(),
                    'per_page' => $articles->perPage(),
                    'total' => $articles->total(),
                    'from' => $articles->firstItem(),
                    'to' => $articles->lastItem()
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des articles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un nouvel article
     */
    public function store(StoreArticleRequest $request): JsonResponse
    {
        try {
            $article = Article::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Article créé avec succès',
                'data' => $article
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'article',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un article spécifique
     */
    public function show(int $id): JsonResponse
    {
        try {
            $article = Article::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Article récupéré avec succès',
                'data' => $article
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Article non trouvé'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'article',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour un article
     */
    public function update(UpdateArticleRequest $request, int $id): JsonResponse
    {
        try {
            $article = Article::findOrFail($id);
            $article->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Article mis à jour avec succès',
                'data' => $article->fresh()
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Article non trouvé'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'article',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un article
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $article = Article::findOrFail($id);
            $article->delete();

            return response()->json([
                'success' => true,
                'message' => 'Article supprimé avec succès'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Article non trouvé'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'article',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recherche avancée
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'company_id' => 'sometimes|integer',
            'per_page' => 'sometimes|integer|min:1|max:100'
        ]);

        try {
            $query = Article::search($request->query);
            
            if ($request->filled('company_id')) {
                $query->byCompanies($request->company_id);
            }

            $query->active();
            
            $perPage = $request->get('per_page', 15);
            $articles = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Recherche effectuée avec succès',
                'query' => $request->query,
                'data' => $articles->items(),
                'pagination' => [
                    'current_page' => $articles->currentPage(),
                    'last_page' => $articles->lastPage(),
                    'per_page' => $articles->perPage(),
                    'total' => $articles->total()
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
