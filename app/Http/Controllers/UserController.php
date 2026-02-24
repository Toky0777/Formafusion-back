<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AuthService;
use App\Services\RoleService;
use App\Services\EntrepriseService;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
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

    public function getUser(Request $request)
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = $request->user();
        $userId = $user->id;
        $country    = $this->authService->getCountrySettings();
        $roleId     = $this->roleService->getRoleId($userId);
        $userType   = $this->roleService->getUserType($userId);
        $etpType    = $this->entrepriseService->getTypeEntreprise($userId);

        return response()->json([
            'user' => $user,
            'user_type' => $userType,
            'symbol' => $country->currency_code,
            'customer_name' => Customer::customerName($userId),
            'roleId' => $roleId,
            'etp_type' => $etpType,
            'setting' => $country
        ], 200);
    }

    public function getProfilLearner(Request $request)
    {
        return response()->json($request->user(), 200);
    }

    public function updateProfil(Request $request)
    {
        $userId = Auth::id();

        $request->validate([
            'name' => 'required|string|max:255',
            'firstName' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,' . $userId,
        ]);

        $updateData = [
            'name' => $request->input('name'),
            'firstName' => $request->input('firstName'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
        ];

        DB::table('users')
            ->where('id', $userId)
            ->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour avec succès.',
            'user' => $updateData
        ], 200);
    }

    public function updateImageProfil(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $file = $request->file('photo');
        $fileName = time() . '.' . $file->extension();
        $filePath = 'img/employes/' . $fileName;

        $file->move(public_path('img/employes'), $fileName);

        DB::table('users')
            ->where('id', Auth::id())
            ->update(['photo' => $fileName]);

        return response()->json(['success' => true, 'image_url' => $fileName]);
    }
}
