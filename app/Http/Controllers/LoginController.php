<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\AuthService;
use App\Services\RoleService;
use App\Services\EntrepriseService;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    private AuthService $authService;
    private RoleService $roleService;
    private EntrepriseService $entrepriseService;

    public function __construct(AuthService $authService, RoleService $roleService, EntrepriseService $entrepriseService)
    {
        $this->authService = $authService;
        $this->roleService = $roleService;
        $this->entrepriseService = $entrepriseService;
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 422,
                'message' => 'Invalid input data',
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = $this->authService->authenticate($request->email, $request->password);

        if (!$user) {
            return response()->json([
                'status'  => 401,
                'message' => 'Email or password is incorrect'
            ], 401);
        }

        $userId = $user->id;
        $userType   = $this->roleService->getUserType($userId);
        $token  = $user->createToken('auth_token')->plainTextToken;

        // if ($userType == 'formateur') {
        //     $trainerIsNotActive = DB::table('cfp_formateurs')->where('idFormateur', $userId)->select('isActiveFormateur')->first();
        //     if ($trainerIsNotActive->isActiveFormateur == 1) {
        //         return response()->json([
        //             'status'  => 401,
        //             'message' => 'Votre compte est désactivé!'
        //         ], 401);
        //     }
        // }

        $cookie = cookie('token', $token, 60 * 24);
        $country    = $this->authService->getCountrySettings();
        $roleId     = $this->roleService->getRoleId($userId);
        $etpType    = $this->entrepriseService->getTypeEntreprise($userId);

        return response()->json([
            'status'        => 200,
            'message'       => 'Login successful',
            'user'          => $user,
            'token'         => $token,
            'role_id'       => $roleId,
            'user_type'     => $userType,
            'etp_type'      => $etpType,
            'setting'       => $country,
            'symbol'      => $country?->currency_code,
            'customer_name' => Customer::customerName($userId),
        ])->withCookie($cookie);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->currentAccessToken()?->delete();
            return response()->json([
                'status'  => 200,
                'message' => 'Déconnexion réussie'
            ]);
        }

        return response()->json([
            'status'  => 204,
            'message' => 'Utilisateur introuvable'
        ], 204);
    }
}
