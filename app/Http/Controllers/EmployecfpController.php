<?php

namespace App\Http\Controllers;

use App\Mail\InvitationReferent;
use App\Mail\PasswordChange;
use App\Mail\RequestCustomer;
use App\Models\Customer;
use Exception;
use App\Models\User;
use App\Models\Employe;
use App\Models\RoleUser;
use App\Services\BrevoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Mail;

class EmployecfpController extends Controller
{
    public function index()
    {
        $authenticatedUser = Customer::idCustomer();
        $userNow = Customer::findOrFail($authenticatedUser);
        $mysubscriptions = $userNow->planSubscriptions()->first();
        if ($mysubscriptions && $mysubscriptions->ended()) {
            $nextSubscription = $userNow->planSubscriptions()->where('starts_at', '>=', $mysubscriptions->ends_at)->first();
            if ($nextSubscription) {
                $mysubscriptions->delete();
            }
        }

        $referentAll = DB::table('v_employe_alls')
            ->select('idEmploye', 'idCustomer', 'role_id', 'matricule as ref_matricule', 'initialName as ref_initial_name', 'name as ref_name', 'firstName as ref_firstname', 'phone as ref_phone', 'email as ref_email', 'cin as ref_cin', 'adresse as ref_adresse', 'sexe as ref_sexe', 'fonction as ref_fonction', 'photo as ref_photo', 'idSexe', 'isActive', 'hasRole', 'phone as ref_phone')
            ->where('idCustomer', Customer::idCustomer())
            ->where('role_id', 8)
            ->orderBy('isActive', 'desc')
            ->get();

        $id = Auth::user()->id;

        $refConnected = DB::table('users')
            ->select('role_users.role_id')
            ->join('role_users', 'users.id', '=', 'role_users.user_id')
            ->where('users.id',  $id)
            ->where('role_users.role_id', 3)
            ->exists();

        $referentActifs = DB::table('v_employe_alls')
            ->select('idEmploye', 'idCustomer', 'role_id', 'matricule as ref_matricule', 'initialName as ref_initial_name', 'name as ref_name', 'firstName as ref_firstname', 'phone as ref_phone', 'email as ref_email', 'cin as ref_cin', 'adresse as ref_adresse', 'sexe as ref_sexe', 'fonction as ref_fonction', 'photo as ref_photo', 'idSexe', 'isActive', 'hasRole', 'phone as ref_phone')
            ->where('idCustomer', Customer::idCustomer())
            ->where('role_id', 8)
            ->where('isActive', 1)
            ->orderBy('name', 'asc')
            ->get();

        $referentInactifs = DB::table('v_employe_alls')
            ->select('idEmploye', 'idCustomer', 'role_id', 'matricule as ref_matricule', 'initialName as ref_initial_name', 'name as ref_name', 'firstName as ref_firstname', 'phone as ref_phone', 'email as ref_email', 'cin as ref_cin', 'adresse as ref_adresse', 'sexe as ref_sexe', 'fonction as ref_fonction', 'photo as ref_photo', 'idSexe', 'isActive', 'hasRole', 'phone as ref_phone')
            ->where('idCustomer', Customer::idCustomer())
            ->where('role_id', 8)
            ->where('isActive', 0)
            ->orderBy('name', 'asc')
            ->get();

        $countReferentAll = DB::table('v_employe_alls')->where('idCustomer', Customer::idCustomer())->where('role_id', 8)->count();
        $countReferentActifs = DB::table('v_employe_alls')->where('idCustomer', Customer::idCustomer())->where('role_id', 8)->where('isActive', 1)->count();
        $countReferentInactifs = DB::table('v_employe_alls')->where('idCustomer', Customer::idCustomer())->where('role_id', 8)->where('isActive', 0)->count();

        $authenticatedUser = Customer::idCustomer();
        $userNow = Customer::findOrFail($authenticatedUser);
        $mysubscriptions = $userNow->planSubscriptions()->first();
        if ($mysubscriptions && $mysubscriptions->ended()) {
            $nextSubscription = $userNow->planSubscriptions()->where('starts_at', '>=', $mysubscriptions->ends_at)->first();
            if ($nextSubscription) {
                $mysubscriptions->delete();
            }
        }

        if ($countReferentAll <= 0) {
            return response()->json([
                'status' => 404,
                'message' => 'Aucun résultat !'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'referentAll' => $referentAll,
            'referentActifs' => $referentActifs,
            'referentInactifs' => $referentInactifs,
            'countReferentAll' => $countReferentAll,
            'countReferentActifs' => $countReferentActifs,
            'countReferentInactifs' => $countReferentInactifs,
            'refConnected' => $refConnected
        ]);
    }

    public function updatePassword($idEmploye, Request $req)
    {
        $validator = Validator::make($req->all(), [
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::find($idEmploye);
            if ($user) {
                DB::transaction(function () use ($user, $req) {
                    $user->password = Hash::make($req->password);
                    $user->save();

                    $cfp = DB::table('customers')
                        ->select('idCustomer', 'customerName', 'customer_addr_lot AS customerAdress')
                        ->where('idCustomer', Customer::idCustomer())
                        ->first();

                    $ref = $req->emp_email;
                    // Mail::to($req->emp_email)->send(new PasswordChange($cfp, $ref, $req->password));
                });

                return response()->json([
                    'status' => 200,
                    'message' => 'Succès'
                ], 200);
            }

            return response()->json([
                'status' => 404,
                'message' => 'Introuvable !'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // public function store(Request $req)
    // {

    //     // // FIN LIMITEUR PAR RAPPORT AU ABONNEMENT

    //     $validation = Validator::make($req->all(), [
    //         // 'emp_matricule' => 'required|max:50|unique:users,matricule',
    //         'emp_name' => 'required|max:240',
    //         'emp_email' => 'required|max:150|unique:users,email'
    //     ]);

    //     if ($validation->fails()) {
    //         return response()->json(['error' => $validation->messages()]);
    //     } else {
    //         $cfp = DB::table('customers')
    //             ->select('idCustomer')
    //             ->where('idCustomer', Customer::idCustomer())
    //             ->first();

    //         try {

    //             DB::beginTransaction();

    //             $fonction = DB::table('fonctions')->select('idFonction')->where('idCustomer', Customer::idCustomer())->first();

    //             $user = new User();
    //             $user->matricule = $req->emp_matricule;
    //             $user->name = $req->emp_name;
    //             $user->firstName = $req->emp_firstname;
    //             $user->email = $req->emp_email;
    //             $user->phone = $req->emp_phone;
    //             $password = Hash::make('0000@#');
    //             $user->password = $password;
    //             $user->save();

    //             $emp = new Employe();
    //             $emp->idEmploye = $user->id;
    //             $emp->idSexe = 1;
    //             $emp->idNiveau = 6;
    //             $emp->idCustomer = $cfp->idCustomer;
    //             $emp->idFonction = $fonction->idFonction;
    //             $emp->save();

    //             RoleUser::create([
    //                 'role_id'  => 8,
    //                 'user_id'  => $user->id,
    //                 'isActive' => 1,
    //                 'hasRole' => 1
    //             ]);

    //             $cfp = DB::table('customers')->select('idCustomer', 'customerName', 'customer_addr_lot AS customerAdress')->where('idCustomer', Customer::idCustomer())->first();
    //             $ref = $req->emp_email;
    //             // Mail::to($req->emp_email)->send(new InvitationReferent($cfp, $ref));

    //             DB::commit();

    //             return response()->json([
    //                 'status' => 200,
    //                 'message' => 'Succès',
    //                 'idEmploye' =>$user->id
    //             ]);
    //         } catch (Exception $e) {
    //             DB::rollBack();
    //             return response()->json([
    //                 'status' => 500,
    //                 'message' => 'Erreur !'
    //             ]);
    //         }
    //     }
    // }

    public function store(Request $req)
    {
        // Validation
        $validation = Validator::make($req->all(), [
            // 'emp_matricule' => 'required|max:50|unique:users,matricule',
            'emp_name'     => 'required|max:240',
            'emp_email'    => 'required|max:150|unique:users,email',
            'image'        => 'nullable|string', // base64 de l’image
            'emp_matricule' => 'required|unique:users,matricule'

        ]);

        if ($validation->fails()) {
            return response()->json(['error' => $validation->messages()]);
        }

        $cfp = DB::table('customers')
            ->select('idCustomer')
            ->where('idCustomer', Customer::idCustomer())
            ->first();

        try {
            DB::beginTransaction();

            $fonction = DB::table('fonctions')
                ->select('idFonction')
                ->where('idCustomer', Customer::idCustomer())
                ->first();

            $user = new User();
            $user->matricule = $req->emp_matricule;
            $user->name = $req->emp_name;
            $user->firstName = $req->emp_firstname;
            $user->email = $req->emp_email;
            $user->phone = $req->emp_phone;
            $user->password = Hash::make('0000@#');

            // --- Upload image si fournie ---
            if ($req->has('image') && !empty($req->image)) {
                $driver = new Driver();
                $manager = new ImageManager($driver);

                $image_parts = explode(";base64,", $req->image);
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1] ?? 'jpeg';
                $image_base64 = base64_decode($image_parts[1]);

                // Compression en WebP
                $image = $manager->read($image_base64)->toWebp(25);

                $imageName = uniqid() . '.webp';
                $filePath = 'img/referents/' . $imageName;

                // Upload vers DigitalOcean
                Storage::disk('do')->put($filePath, $image, 'public');

                $user->photo = $imageName;
            }

            $user->save();

            $emp = new Employe();
            $emp->idEmploye = $user->id;
            $emp->idSexe = 1;
            $emp->idNiveau = 6;
            $emp->idCustomer = $cfp->idCustomer;
            $emp->idFonction = $fonction->idFonction;
            $emp->save();

            RoleUser::create([
                'role_id'  => 8,
                'user_id'  => $user->id,
                'isActive' => 1,
                'hasRole'  => 1
            ]);

            $cfp = DB::table('customers')
                ->select('idCustomer', 'customerName', 'customer_addr_lot AS customerAdress')
                ->where('idCustomer', Customer::idCustomer())
                ->first();

            $ref = $req->emp_email;
            // Mail::to($req->emp_email)->send(new InvitationReferent($cfp, $ref));
            $htmlContent = (new RequestCustomer(Customer::getCustomer(Customer::idCustomer())->customer_name))->render();

            // Envoyer via Brevo
            app(BrevoService::class)->sendEmail(
                $ref,
                "Invitation - Plateforme",
                $htmlContent
            );

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Succès',
                'idEmploye' => $user->id,
                'image' => $user->photo ?? null
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 422,
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Erreur !',
                'error' => $e->getMessage()
            ]);
        }
    }


    public function edit($idEmploye)
    {
        $query = DB::table('v_employe_alls')
            ->select('idEmploye', 'idCustomer', 'matricule', 'initialName', 'name', 'firstName', 'phone', 'email', 'cin', 'fonction', 'photo', 'isActive', 'hasRole', 'user_addr_quartier', 'user_addr_lot', 'user_addr_rue', 'user_addr_code_postal')
            ->where('idEmploye', $idEmploye);

        if ($query->first()) {
            $villes = DB::table('villes')->select('idVille', 'ville')->get();

            return response()->json([
                'status' => 200,
                'emp' => $query->first(),
                'villes' => $villes
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Introuvable !'
            ], 404);
        }
    }

    public function update(Request $req, $idEmploye)
    {
        $req->validate([
            'emp_name' => 'required|min:3|max:240',
            'emp_email'     => 'required|max:150|unique:users,email,' . $idEmploye,
            'emp_matricule' => 'required|unique:users,matricule,' . $idEmploye,

        ]);
        try {
            $query = DB::table('users')->where('id', $idEmploye);
            if ($query->first()) {
                $query->update([
                    'matricule' => $req->emp_matricule,
                    'name' => $req->emp_name,
                    'firstName' => $req->emp_firstname,
                    //'photo' => $req->emp_photo,
                    'phone' => $req->emp_phone,
                    'email' => $req->emp_email,
                    'user_addr_lot' => $req->emp_lot,
                    'user_addr_quartier' => $req->emp_quartier,
                    'user_addr_code_postal' => $req->emp_code_postal
                ]);

                return response()->json([
                    'status' => 200,
                    'message' => 'Succès'
                ]);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'Introuvable !'
                ], 404);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 422,
                'errors' => $e->errors()
            ], 422);
        }
    }

    // suppression referent
    public function destroy($id)
    {
        $query = DB::table('users')->join('role_users', 'users.id', 'role_users.user_id')->where('users.id', $id)->where('role_users.role_id', '!=', 3);

        if ($query->first()) {
            $chekProject = DB::table('detail_apprenants')->where('idEmploye', $id)->count();
            $chekProjectInter = DB::table('detail_apprenant_inters')->where('idEmploye', $id)->count();

            if ($chekProject <= 0 || $chekProjectInter <= 0) {
                DB::transaction(function () use ($query, $id) {
                    DB::table('employes')->where('idEmploye', $id)->delete();
                    $query->delete();
                });

                return response()->json([
                    'status' => 200,
                    'message' => "Référent supprimé avec succès !"
                ]);
            } else
                return response()->json([
                    'status' => 500,
                    'message' => "Suppression impossible !"
                ]);
        } else
            return response()->json([
                'status' => 500,
                'message' => "Suppression impossible !"
            ]);
    }
    public function editPhoto($empCfpId)
    {
        $emp = Employe::where('idEmploye', '=', $empCfpId)->firstOrFail();

        return view('CFP.employeCfps.editPhoto', compact('emp'));
    }

    public function updatePhoto(Request $req, $empId)
    {
        $driver = new Driver();

        $manager = new ImageManager($driver);
        $referent = DB::table('users')->select('photo')->where('id', $empId)->first();

        if ($referent != null) {
            if (!empty($referent->photo)) {
                Storage::disk('do')->delete('img/referents/' . $referent->photo);
            }

            $folderPath = public_path('img/referents/');

            $image_parts = explode(";base64,", $req->image);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $image = $manager->read($image_base64)->toWebp(25);

            $imageName = uniqid() . '.webp';
            $filePath = 'img/referents/' . $imageName;

            // Upload the image to DigitalOcean Space
            Storage::disk('do')->put($filePath, $image, 'public');

            DB::table('users')->where('id', $empId)->update([
                'photo' => $imageName,
            ]);
            return response()->json([
                'success' => 'Image Uploaded Successfully',
                'imageName' =>  $imageName
            ]);
        }
    }

    public function activate(Request $req, $idEmploye)
    {
        $abn = DB::table('v_abonnement_cfps')
            ->select('idAbn', 'idCustomer', 'nbReferent', 'isInfinity', 'isActive')
            ->where('idCustomer', Customer::idCustomer())
            ->where('isActive', 1)
            ->first();

        $check = DB::select("SELECT COUNT(idEmploye) AS nbrEmp FROM v_employe_alls WHERE idCustomer = ? AND isActive = ? AND role_id = ? OR role_id = ?", [Auth::user()->id, 1, 3, 8]);

        if ($abn->isInfinity == 1) {
            DB::table('employes')
                ->join('users', 'users.id', 'employes.idEmploye')
                ->join('role_users', 'role_users.user_id', 'users.id')
                ->where('idEmploye', $idEmploye)
                ->update([
                    'role_users.isActive' => 1
                ]);

            return response()->json(["success" => "Succès"]);
        } elseif ($abn->idAbn == $req->idAbn) {
            if ($check[0]->nbrEmp < intval($req->nbRef)) {
                DB::table('employes')
                    ->join('users', 'users.id', 'employes.idEmploye')
                    ->join('role_users', 'role_users.user_id', 'users.id')
                    ->where('idEmploye', $idEmploye)
                    ->update([
                        'role_users.isActive' => 1
                    ]);

                return response()->json(["success" => "Succès"]);
            } else {
                return response()->json(["error" => "Vous avez atteint le nombre maximale de réferent, Veuillez mettre à niveau votre abonnement !"]);
            }
        }
    }

    public function disableEmp($idEmploye)
    {
        $emp = DB::table('employes')
            ->join('users', 'users.id', 'employes.idEmploye')
            ->join('role_users', 'role_users.user_id', 'users.id')
            ->where('idEmploye', $idEmploye)
            ->update([
                'role_users.isActive' => 0
            ]);

        if ($emp) {
            return response()->json([
                "success" => "Succès"
            ]);
        } else {
            return response()->json([
                "error" => "Erreur Inconnue"
            ]);
        }
    }
}
