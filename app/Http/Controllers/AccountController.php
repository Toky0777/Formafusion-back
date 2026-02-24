<?php

namespace App\Http\Controllers;

use App\Http\Requests\CfpRegisterRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Services\CfpService;
use App\Services\CustomerService;
use App\Services\EmployeService;
use App\Services\CreditWalletService;
use App\Services\EtpGrpService;
use App\Services\EtpInformalService;
use App\Services\EtpService;
use App\Services\FormateurService;
use App\Services\LieuService;
use App\Services\ParticulierService;
use App\Services\UserService;
use App\Traits\GetQuery;
use App\Traits\StoreQuery;
use App\Traits\UpdateQuery;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Laravelcm\Subscriptions\Models\Plan;

class AccountController extends Controller
{
    use StoreQuery, GetQuery, UpdateQuery;

    private function getModuleDomaine($idDomaine)
    {
        return DB::table('v_module_cfps')->select('idDomaine')->where('idDomaine', $idDomaine)->where('moduleStatut', 1)->count();
    }

    public function login()
    {
        $all_domaines = DB::table('domaine_formations')->select('idDomaine', 'nomDomaine')->orderBy('nomDomaine')->get();
        $domaines = [];

        foreach ($all_domaines as $doma) {
            $domaines[] = [
                'idDomaine' => $doma->idDomaine,
                'nomDomaine' => $doma->nomDomaine,
                'nb_module' => $this->getModuleDomaine($doma->idDomaine)
            ];
        }
        $email = null;
        return view('auth.login', compact('domaines', 'email'));
    }

    public function forgot()
    {
        return view('auth.forgot');
    }
    public function redirect()
    {
        return view('auth.succesForgot');
    }

    public function register()
    {
        $email = null;
        $all_domaines = DB::table('domaine_formations')->select('idDomaine', 'nomDomaine')->orderBy('nomDomaine')->get();
        $domaines = [];

        $typeEntreprises = $this->getTypeEntreprise()->where('idTypeEtp', '!=', 3)->get();

        foreach ($all_domaines as $doma) {
            $domaines[] = [
                'idDomaine' => $doma->idDomaine,
                'nomDomaine' => $doma->nomDomaine,
                'nb_module' => $this->getModuleDomaine($doma->idDomaine)
            ];
        }

        return view('auth.register', compact('email', 'domaines', 'typeEntreprises'));
    }

    public function store(Request $req, UserService $usr, CustomerService $cst, LieuService $lieu, EmployeService $emp, FormateurService $form, CreditWalletService $creditService)
    {
        if ($req->account_type == 8) {
            $validate = Validator::make($req->all(), [
                'part_name' => 'required|min:2|max:200',
                'part_firstName' => 'required|min:2|max:250',
                'customer_email' => 'required|unique:users,email',
                'password' => 'required|min:8',
            ]);
        } elseif (in_array($req->account_type, [1, 2, 4, 5, 6, 7, 9])) {
            $validate = Validator::make($req->all(), [
                'customer_name' => 'required|min:2|max:200',
                'referent_name' => 'required|min:2|max:250',
                'referent_firstName' => 'required|min:2|max:250',
                'customer_email' => 'required|unique:users,email',
                'password' => 'required|min:8',
            ]);
        }

        if ($validate->fails()) {
            return response()->json($validate->messages());
        }

        if ($req->account_type == 9) {
            // compte CFP
            try {
                DB::beginTransaction();

                $user = $usr->store(NULL, $req->referent_name, $req->referent_firstName, $req->customer_email, $req->phone, Hash::make($req->password));
                $cst->store($user->id, $req->customer_name, $req->customer_email, 7, 1, 1);
                $module = $this->mdls("Default module", 1, $user->id, 1);
                $this->module($module);
                $lieu->store($user->id);
                $fonction = $this->fonctions($req->function, $user->id);
                $this->cfp($user->id);
                $emp->store($user->id, 6, $user->id, 1, $fonction);
                $this->roleUser(3, $user->id, 1, 1, 1);

                // make Admin Trainer "Formateur" by default
                // $form->storeForm($user->id, 1);
                // $form->storeFormateur($user->id);
                // $form->storeCfpFormateur($user->id, $user->id, 1, 1);
                $this->roleUser(5, $user->id, 0, 1, 1);

                DB::commit();

                return response()->json([
                    'status' => 200,
                    'message' => 'OK',
                    'user' => $this->getUserById($user->id),
                    'token' => $this->getUserById($user->id)->createToken('formafusion-token')->plainTextToken,
                    'type_customer' => $this->typeCustomer($user->id),
                    'user_type' => 'superAdminCfp',
                ]);
            } catch (Exception $e) {
                return response()->json($e->getMessage());
            }
        } elseif ($req->account_type == 8) {
            // Compte PARTICULIER
            try {
                DB::beginTransaction();
                $particulier = $usr->store(NULL, $req->part_name, $req->part_firstName, $req->customer_email, $req->phone, Hash::make($req->password));
                $this->particulier($particulier->id);
                $this->roleUser(10, $particulier->id, 1, 1, 1);

                DB::commit();

                return response()->json([
                    'status' => 200,
                    'type_customer' => $req->account_type,
                    'user' => $this->getUserById($particulier->id),
                    'token' => $particulier->first()->createToken('formafusion-token')->plainTextToken,
                    'user_type' => 'particular',
                ]);
            } catch (Exception $e) {
                return response()->json($e->getMessage());
            }
        } elseif (in_array($req->account_type, [1, 2, 4, 5, 6, 7])) {
            // Compte ETP(etp privé na etp_single mitovy ihany) et ETP_GROUPE
            try {
                DB::beginTransaction();

                $user = $usr->store(NULL, $req->referent_name, $req->referent_first_name, $req->customer_email, $req->phone, Hash::make($req->password));
                $cst->store($user->id, $req->customer_name, $req->customer_email, 7, 2, 1);
                $module = $this->mdls("Default module", 1, $user->id, 2);
                $this->moduleInterne($module);
                $lieu->store($user->id);
                $fonction = $this->fonctions($req->function, $user->id);
                $this->entreprise($user->id, $req->account_type);

                if ($req->account_type == 1) {
                    $this->etpPrivate($user->id);
                } elseif ($req->account_type == 2) {
                    $this->etpGroupe($user->id);
                }

                $emp->store($user->id, 6, $user->id, 1, $fonction);
                $this->roleUser(6, $user->id, 1, 1, 1);

                // Testing Center -> Crediter les comptes de 1000 crédits des Entreprises d'id : 1, 2, 4, 5, 6, 7 dans la table 'type_entreprises' (v2) (litakelykill)
                // Créditer le compte si éligible
                $creditService->creditNewAccount($user, $req->account_type);
                // Testing Center -> Crediter les comptes de 1000 crédits des Entreprises d'id : 1, 2, 4, 5, 6, 7 dans la table 'type_entreprises' (v2) (litakelykill)

                DB::commit();

                return response()->json([
                    'status' => 200,
                    'message' => 'OK',
                    'user' => $this->getUserById($user->id),
                    'token' => $this->getUserById($user->id)->createToken('formafusion-token')->plainTextToken,
                    'user_type' => 'superAdminEtp',
                    'type_customer' => $this->typeCustomer($user->id)
                ]);
            } catch (Exception $e) {
                return response()->json($e->getMessage());
            }
        }
    }

    public function getUserById($id)
    {
        return User::whereId($id)->first() ?? null;
    }

    public function userLogin()
    {
        $all_domaines = DB::table('domaine_formations')->select('idDomaine', 'nomDomaine')->orderBy('nomDomaine')->get();
        $domaines = [];

        foreach ($all_domaines as $doma) {
            $domaines[] = [
                'idDomaine' => $doma->idDomaine,
                'nomDomaine' => $doma->nomDomaine,
                'nb_module' => $this->getModuleDomaine($doma->idDomaine)
            ];
        }
        return view('auth.user', compact('domaines'));
    }

    public function userLoginWithPassword()
    {
        $all_domaines = DB::table('domaine_formations')->select('idDomaine', 'nomDomaine')->orderBy('nomDomaine')->get();
        $domaines = [];

        foreach ($all_domaines as $doma) {
            $domaines[] = [
                'idDomaine' => $doma->idDomaine,
                'nomDomaine' => $doma->nomDomaine,
                'nb_module' => $this->getModuleDomaine($doma->idDomaine)
            ];
        }
        return view('auth.login', compact('domaines'));
    }

    public function userRegister()
    {
        $all_domaines = DB::table('domaine_formations')->select('idDomaine', 'nomDomaine')->orderBy('nomDomaine')->get();
        $domaines = [];

        foreach ($all_domaines as $doma) {
            $domaines[] = [
                'idDomaine' => $doma->idDomaine,
                'nomDomaine' => $doma->nomDomaine,
                'nb_module' => $this->getModuleDomaine($doma->idDomaine)
            ];
        }
        return response()->json('ok');
    }

    public function checkUser(Request $request)
    {
        $email = $request->email;
        Session::put('email', $email);
        $userExists = User::where('email', $email)->exists();

        if ($userExists) {
            return redirect('user/login');
        } else {
            return redirect('user/register');
        }
    }

    public function checkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json(false, 400);
        }

        $emailExists = User::where('email', $request->email)->exists();

        return response()->json(!$emailExists);
    }

    public function checkCustomerName(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'account_type' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(false, 400);
        }

        $typeCustomer = ($request->account_type == 'centre') ? 1 : 2;

        $customerExists = DB::table('customers')->where('customerName', $request->name)->where('idTypeCustomer', $typeCustomer)->exists();

        return response()->json(!$customerExists);
    }

    public function typeCustomer($id)
    {
        $type = DB::table('customers')
            ->select('idTypeCustomer')
            ->where('idCustomer', $id)
            ->first();

        return $type->idTypeCustomer ?? null;
    }

    // public function changeUserPrivilege($idUser){
    //     $roleUsers = $this->getRoleUser($idUser);

    //     foreach($roleUsers as $ru){
    //         switch($ru->){

    //         }
    //     }
    //     $this->updateRoleUser($idUser, 0, 1);
    // }
}
