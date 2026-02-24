<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\CentreService;

class CentreController extends Controller
{
    protected $centreService;

    public function __construct(CentreService $centreService)
    {
        $this->centreService = $centreService;
    }

    public function index()
    {
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Vous devez être authentifié pour accéder à cette ressource.'
            ], 401);
        }

        $userId = Auth::user()->id;
        $cfp = $this->centreService->getCentresForUser($userId);

        return response()->json([
            'cfp' => $cfp
        ]);
    }

    public function show($idCfp)
    {
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Vous devez être authentifié pour accéder à cette ressource.'
            ], 401);
        }

        $userId = Auth::user()->id;
        $cfp = $this->centreService->getCentreDetails($idCfp, $userId);
        $projects = $this->centreService->getProjectsForCentre($idCfp, $userId);

        return response()->json([
            'cfp' => $cfp,
            'projects' => $projects
        ]);
    }
}
