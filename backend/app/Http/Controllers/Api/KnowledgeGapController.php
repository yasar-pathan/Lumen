<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\KnowledgeGapDetector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KnowledgeGapController extends Controller
{
    protected KnowledgeGapDetector $detector;

    public function __construct(KnowledgeGapDetector $detector)
    {
        $this->detector = $detector;
    }

    /**
     * Get ranked knowledge gaps.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $limit = (int) $request->query('limit', 10);
        $gaps = $this->detector->topGaps($limit);

        return response()->json([
            'data' => $gaps
        ]);
    }
}
