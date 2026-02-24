<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\MobileMoneyAcount;
use App\Traits\GetQuery;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ProfilController extends Controller
{
    use GetQuery;

    public function update(Request $req)
    {
        $validate = Validator::make($req->all(), [
            'customer_name' => 'required|min:2|max:200',
            'customer_email' => 'required|email',
            'customer_rcs' => 'required|min:2|max:200',
        ]);

        $idCustomer = Customer::idCustomer();

        if ($validate->fails()) {
            return response()->json(['error' => $validate->messages()]);
        } else {
            try {
                DB::beginTransaction();

                DB::table('customers')
                    ->join('users', 'users.id', 'customers.idCustomer')
                    ->where(function ($query) use ($idCustomer) {
                        $query->where('customers.idCustomer', $idCustomer)
                            ->where('users.id', $idCustomer);
                    })
                    ->update([
                        'customers.nif' => $req->customer_nif,
                        'customers.stat' => $req->customer_stat,
                        'customers.rcs' => $req->customer_rcs,
                        'customers.customerName' => $req->customer_name,
                        'customers.customerPhone' => $req->customer_phone,
                        'customers.customerEmail' => $req->customer_email,
                        'customers.customer_addr_lot' => $req->customer_addr_lot,
                        'customers.customer_addr_quartier' => $req->customer_addr_quartier,
                        // 'customers.customer_addr_code_postal' => $req->customer_addr_code_postal,
                        'customers.description' => $req->customer_description,
                        'customers.siteWeb' => $req->customer_site_web,
                        'customers.customer_slogan' => $req->customer_slogan,
                        'users.email' => $req->customer_email
                    ]);
                DB::commit();

                return response()->json(['success' => 'Opération effectuée avec succès']);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json($e->getMessage());
            }
        }
    }


    public function allAgences()
    {
        $ags = DB::table('v_liste_agences')
            ->select('idAgence', 'ag_name', 'idCustomer', 'idVilleCoded', 'idVille', 'ville', 'ville_name_coded as ville_name', 'vi_code_postal', 'customer_name', 'ville_name_coded')
            ->where('idCustomer', Customer::idCustomer())
            ->orderBy('ag_name', 'asc');

        return $ags;
    }

    public function indexCfp()
    {
        //On récupère les infos du CFP (LOGO, nom_etp, ...)

        $infoProfilCfp = DB::table('v_detail_customers')
            ->select('idCustomer', 'initialName', 'customerName', 'customer_addr_quartier', 'customer_addr_rue', 'customer_addr_lot', 'customer_addr_code_postal', 'nif', 'stat', 'assujetti', 'customerPhone', 'rcs', 'description', 'siteWeb', 'logo', 'customerEmail', 'customer_slogan')
            ->where('idCustomer', Customer::idCustomer())
            ->first();

        if (!$infoProfilCfp) {
            return response()->json([
                'status' => 204,
                'message' => 'Introuvable !'
            ], 204);
        }

        $idCustomer = $infoProfilCfp->idCustomer;

        $id = Auth::user()->id;

        $refConnected = DB::table('users')
            ->select('role_users.role_id')
            ->join('role_users', 'users.id', '=', 'role_users.user_id')
            ->where('users.id',  $id)
            ->where('role_users.role_id', 3)
            ->first();

        $ville_codeds = DB::table('ville_codeds')->orderBy('ville_codeds.vi_code_postal', 'asc')->get();

        $bankacounts = DB::table('bankacounts')
            ->join('ville_codeds', 'bankacounts.ba_idPostal', 'ville_codeds.id')
            ->select('bankacounts.id as idAcount', 'bankacounts.*', 'ville_codeds.*')
            ->where('ba_idCustomer', Customer::idCustomer())
            ->get();

        $mobile_money = MobileMoneyAcount::where('mm_idCustomer', Customer::idCustomer())->get();

        $roleUser = $this->getRoleUser(Customer::idCustomer());
        $companies = Company::where('idCustomer', Customer::idCustomer())->get();

        return response()->json([
            'status' => 200,
            'customer' => $infoProfilCfp,
            'id' => $id,
            'idCustomer' => $idCustomer,
            'refConnected' => $refConnected,
            'ville_codeds' => $ville_codeds,
            'bankacounts' => $bankacounts,
            'mobile_money' => $mobile_money,
            'roleUser' => $roleUser,
            'companies' => $companies
        ]);
    }

    public function getUserInfo()
    {
        $user = DB::table('users')
            ->select('name', 'firstName', 'email', 'matricule', 'phone', 'user_addr_code_postal', 'photo', 'idVille', 'user_addr_quartier', 'user_addr_lot')
            ->where('id', Auth::user()->id)
            ->first();

        return response()->json($user, 200);
    }


    public function indexEtp()
    {
        //On récupère les infos de l'ETP (LOGO, nom_etp, ...)

        $infoProfilEtp = DB::table('v_detail_customers')
            ->select('idCustomer', 'initialName', 'customerName', 'customer_addr_quartier', 'customer_addr_rue', 'customer_addr_lot', 'customer_addr_code_postal', 'nif', 'stat', 'assujetti', 'customerPhone', 'rcs', 'description', 'siteWeb', 'logo', 'customerEmail', 'customer_slogan')
            ->where('idCustomer', Customer::idCustomer())
            ->first();

        //On récupère les infos des référents(photo, nom_referent, ...)

        $referentAll = DB::table('v_employe_alls')
            ->select('idEmploye', 'idCustomer', 'role_id', 'matricule as ref_matricule', 'initialName as ref_initial_name', 'name as ref_name', 'firstName as ref_firstname', 'phone as ref_phone', 'email as ref_email', 'cin as ref_cin', 'adresse as ref_adresse', 'sexe as ref_sexe', 'fonction as ref_fonction', 'photo as ref_photo', 'idSexe', 'isActive', 'hasRole', 'phone as ref_phone')
            ->where('idCustomer', Customer::idCustomer())
            ->whereIn('role_id', [6, 9])
            ->orderBy('isActive', 'desc')
            ->get();

        return view('ETP.profil.index', compact('infoProfilEtp', 'referentAll'));
    }

    public function updateLogo(Request $req, $idCustomer)
    {
        $customer = DB::table('customers')->select('logo')->where('idCustomer', $idCustomer)->first();

        $driver = new Driver();

        $manager = new ImageManager($driver);

        if ($customer != null) {
            if (!empty($customer->logo)) {
                Storage::disk('do')->delete('img/entreprises/' . $customer->logo);
            }

            $image_parts = explode(";base64,", $req->image);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $image = $manager->read($image_base64)->toWebp(25);

            $imageName = uniqid() . '.webp';
            $filePath = 'img/entreprises/' . $imageName;

            // Upload the image to DigitalOcean Space
            Storage::disk('do')->put($filePath, $image, 'public');

            // Update the database with the new image name
            DB::table('customers')->where('idCustomer', $idCustomer)->update([
                'logo' => $imageName,
            ]);
            return response()->json([
                'success' => 'Image Uploaded Successfully',
                'imageName' =>  $imageName
            ]);
        }
    }

    public function updateUser(Request $req)
    {
        // Validation des champs
        $validate = Validator::make($req->all(), [
            'emp_name' => 'required|min:3|max:240',
            'emp_email' => 'required|email|max:150',
        ]);

        if ($validate->fails()) {
            // Retourne les erreurs au format JSON
            return response()->json(['error' => $validate->messages()]);
        }

        try {
            DB::beginTransaction();

            // Vérifie que l'utilisateur existe
            $userQuery = DB::table('users')->where('id', Auth::user()->id);

            if (!$userQuery->exists()) {
                return response()->json(['error' => ['general' => ['Utilisateur introuvable !']]], 204);
            }

            // Mise à jour
            $userQuery->update([
                'name' => $req->emp_name,
                'firstName' => $req->emp_firstname ?? null,
                'email' => $req->emp_email,
                'phone' => $req->emp_phone ?? null,
                'matricule' => $req->emp_matricule ?? null,
                'user_addr_lot' => $req->emp_lot ?? null,
                'user_addr_quartier' => $req->emp_quartier ?? null,
                'user_addr_code_postal' => $req->emp_code_postal ?? null,
                'idVille' => $req->ville ?? null
            ]);

            DB::commit();

            return response()->json(['success' => 'Utilisateur mis à jour avec succès']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => ['general' => [$e->getMessage()]]], 500);
        }
    }


    public function indexEmp()
    {

        return view('employes.profil.index');
    }
    public function profilForm()
    {
        return view('CFP.formateurs.profilForm');
    }
}
