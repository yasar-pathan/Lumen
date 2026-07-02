<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DiagnosticsResource;
use App\Models\Diagnostics;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DoctorController extends Controller
{
    /**
     * Get paginated non-healthy diagnostics cases.
     *
     * @return AnonymousResourceCollection
     */
    public function cases(): AnonymousResourceCollection
    {
        $cases = Diagnostics::with(['message.conversation'])
            ->orderBy('id', 'desc')
            ->paginate(15);

        return DiagnosticsResource::collection($cases);
    }
}
