<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HealthScoreResource;
use App\Services\HealthScoreCalculator;
use Illuminate\Http\Request;

class HealthScoreController extends Controller
{
    protected HealthScoreCalculator $calculator;

    public function __construct(HealthScoreCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Get the current Health Score and metrics breakdown.
     *
     * @param Request $request
     * @return HealthScoreResource
     */
    public function show(Request $request): HealthScoreResource
    {
        $lookbackDays = (int) $request->query('lookback_days', 7);
        $scoreData = $this->calculator->calculate($lookbackDays);

        return new HealthScoreResource($scoreData);
    }
}
