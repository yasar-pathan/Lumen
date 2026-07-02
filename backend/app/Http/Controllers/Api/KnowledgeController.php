<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKnowledgeChunkRequest;
use App\Http\Resources\KnowledgeChunkResource;
use App\Models\KnowledgeChunk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class KnowledgeController extends Controller
{
    /**
     * List all knowledge chunks.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return KnowledgeChunkResource::collection(KnowledgeChunk::orderBy('id', 'desc')->get());
    }

    /**
     * Create a new knowledge chunk.
     *
     * @param StoreKnowledgeChunkRequest $request
     * @return KnowledgeChunkResource
     */
    public function store(StoreKnowledgeChunkRequest $request): KnowledgeChunkResource
    {
        $chunk = KnowledgeChunk::create($request->validated());

        return new KnowledgeChunkResource($chunk);
    }

    /**
     * Update an existing knowledge chunk.
     *
     * @param StoreKnowledgeChunkRequest $request
     * @param int $id
     * @return JsonResponse|KnowledgeChunkResource
     */
    public function update(StoreKnowledgeChunkRequest $request, int $id): JsonResponse|KnowledgeChunkResource
    {
        $chunk = KnowledgeChunk::find($id);

        if (!$chunk) {
            return response()->json([
                'error' => [
                    'message' => 'Knowledge chunk not found.',
                    'code' => 'NOT_FOUND'
                ]
            ], 404);
        }

        $chunk->update($request->validated());

        return new KnowledgeChunkResource($chunk);
    }

    /**
     * Delete a knowledge chunk.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $chunk = KnowledgeChunk::find($id);

        if (!$chunk) {
            return response()->json([
                'error' => [
                    'message' => 'Knowledge chunk not found.',
                    'code' => 'NOT_FOUND'
                ]
            ], 404);
        }

        $chunk->delete();

        return response()->json([
            'data' => [
                'success' => true
            ]
        ]);
    }
}
