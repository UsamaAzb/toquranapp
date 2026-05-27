<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GameController extends Controller
{
    public function index(Request $request): View
    {
        if (auth()->user()?->hasAnyRole(['teacher', 'admin', 'super_admin', 'owner'])) {
            return app(VocabularyGameController::class)->teacherLauncher($request);
        }

        return app(VocabularyGameController::class)->hub();
    }

    public function begin(Request $request): View
    {
        if (auth()->user()?->hasAnyRole(['teacher', 'admin', 'super_admin', 'owner'])) {
            return app(VocabularyGameController::class)->teacherLauncher($request);
        }

        return app(VocabularyGameController::class)->hub();
    }

    public function begin_old(Request $request): never
    {
        abort(410, 'Legacy Floatie compatibility start is retired. Use Vocabulary Games.');
    }

    public function start(Request $request): JsonResponse
    {
        return $this->retiredAjaxResponse();
    }

    public function guess(Request $request): JsonResponse
    {
        return $this->retiredAjaxResponse();
    }

    public function hint(Request $request): JsonResponse
    {
        return $this->retiredAjaxResponse();
    }

    private function retiredAjaxResponse(): JsonResponse
    {
        return response()->json([
            'message' => 'Legacy Floatie AJAX endpoints have been retired. Use Vocabulary Games.',
        ], 410);
    }
}
