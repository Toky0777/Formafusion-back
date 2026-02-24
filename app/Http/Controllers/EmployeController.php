<?php

namespace App\Http\Controllers;

use App\Imports\ExcelApprenants;
use App\Models\Customer;
use App\Models\Employe;
use App\Models\RoleUser;
use App\Models\User;
use App\Services\EmployeService;
use App\Services\EntrepriseService;
use App\Services\UserService;
use App\Traits\CheckQuery;
use App\Traits\GetQuery;
use App\Traits\StoreQuery;
use Exception;
use Google\Service\Monitoring\Custom;
use Illuminate\Http\Request;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

use App\Imports\EmployesImport;




class EmployeController extends Controller
{
    use CheckQuery, StoreQuery, GetQuery;

    public function index()
    {
        $checkEtpGrp = DB::table('etp_groupes')->where('idEntreprise', Customer::idCustomer())->exists();

        // Récupérer tous les paramètres de filtrage
        $searchTerm = request('search');
        $companies = request('companies') ? explode(',', request('companies')) : [];
        $periodes = request('periodes') ? explode(',', request('periodes')) : [];
        $courses = request('courses') ? explode(',', request('courses')) : [];
        $lieux = request('lieux') ? explode(',', request('lieux')) : [];
        $modalites = request('modalites') ? explode(',', request('modalites')) : [];
        $infoStatus = request('infoStatus', 'all');
        $activeStatus = request('activeStatus', 'all');

        if ($checkEtpGrp) {
            $query = DB::table('v_union_emp_grps')
                ->select('idEmploye', 'idEntreprise', 'idEntrepriseParent', 'etp_name_parent', 'etp_name', 'emp_initial_name', 'emp_name', 'emp_firstname', 'emp_email', 'emp_matricule', 'emp_phone', 'emp_photo', 'emp_cin', 'emp_sexe', 'emp_is_active', 'emp_has_role', 'user_is_in_service')
                ->where('idEntrepriseParent', Customer::idCustomer());

            // Filtre par recherche
            if ($searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('emp_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('emp_firstname', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('emp_matricule', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('emp_email', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('etp_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('etp_name_parent', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Filtre par entreprises
            if (!empty($companies)) {
                $query->whereIn('idEntreprise', $companies);
            }

            // Filtre par statut d'information complète
            if ($infoStatus !== 'all') {
                $query->where(function ($q) use ($infoStatus) {
                    $q->whereNotNull('emp_name')
                        ->whereNotNull('emp_firstname')
                        ->whereNotNull('emp_matricule')
                        ->whereNotNull('emp_email')
                        ->whereNotNull('emp_phone');

                    if ($infoStatus === 'incomplete') {
                        $q->orWhereNull('emp_name')
                            ->orWhereNull('emp_firstname')
                            ->orWhereNull('emp_matricule')
                            ->orWhereNull('emp_email')
                            ->orWhereNull('emp_phone');
                    }
                });
            }

            // Filtre par statut actif/inactif
            if ($activeStatus !== 'all') {
                $query->where('user_is_in_service', $activeStatus === 'active' ? 1 : 0);
            }

            // Filtre par périodes (à adapter selon votre structure de données)
            if (!empty($periodes)) {
                // Implémentation dépendante de votre structure
                // $query->whereIn('periode_id', $periodes);
            }

            // Filtre par cours (à adapter selon votre structure de données)
            if (!empty($courses)) {
                // Implémentation dépendante de votre structure
                // $query->whereIn('course_id', $courses);
            }

            // Filtre par lieux (à adapter selon votre structure de données)
            if (!empty($lieux)) {
                // Implémentation dépendante de votre structure
                // $query->whereIn('lieu_id', $lieux);
            }

            // Filtre par modalités (à adapter selon votre structure de données)
            if (!empty($modalites)) {
                // Implémentation dépendante de votre structure
                // $query->whereIn('modalite_id', $modalites);
            }

            $empGrps = $query->orderBy('emp_name', 'asc')->paginate(12);

            // Pour le count total, il faut faire une requête sans pagination
            $countQuery = clone $query;
            $countEmpls = $countQuery->count();

            $etpChildren = DB::table('etp_groupeds as egp')
                ->join('customers as c', 'egp.idEntreprise', 'c.idCustomer')
                ->select('egp.idEntreprise', 'c.customerName as etp_name')
                ->where('egp.idEntrepriseParent', Customer::idCustomer())
                ->orderBy('c.customerName', 'asc')
                ->get();

            $etpParent = DB::table('customers')
                ->select('idCustomer as idEntreprise', 'customerName AS etp_name')
                ->where('idCustomer', Customer::idCustomer())
                ->get();

            $entreprises = array_merge($etpChildren->toArray(), $etpParent->toArray());

            return response()->json([
                'status' => 200,
                'empGrps' => $empGrps,
                'countEmpls' => $countEmpls,
                'checkEtpGrp' => $checkEtpGrp,
                'entreprises' => $entreprises
            ]);
        } else {
            $query = DB::table('v_employe_alls')
                ->select('idEmploye', 'idCustomer as idEntreprise', 'role_id', 'customerName', 'matricule', 'initialName', 'name', 'firstName', 'phone', 'email', 'cin', 'adresse', 'sexe', 'fonction', 'photo', 'idSexe', 'isActive', 'hasRole', 'user_is_in_service')
                ->where(function ($query) {
                    $query->where('idCustomer', Customer::idCustomer())
                        ->where('role_id', 4);
                });

            // Appliquer les mêmes filtres pour la deuxième requête
            if ($searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('firstName', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('matricule', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('customerName', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Filtre par statut d'information complète
            if ($infoStatus !== 'all') {
                $query->where(function ($q) use ($infoStatus) {
                    $q->whereNotNull('name')
                        ->whereNotNull('firstName')
                        ->whereNotNull('matricule')
                        ->whereNotNull('email')
                        ->whereNotNull('phone');

                    if ($infoStatus === 'incomplete') {
                        $q->orWhereNull('name')
                            ->orWhereNull('firstName')
                            ->orWhereNull('matricule')
                            ->orWhereNull('email')
                            ->orWhereNull('phone');
                    }
                });
            }

            // Filtre par statut actif/inactif
            if ($activeStatus !== 'all') {
                $query->where('user_is_in_service', $activeStatus === 'active' ? 1 : 0);
            }

            $employes = $query->orderBy('idEmploye', 'desc')->paginate(16);

            // Count total sans pagination
            $countQuery = clone $query;
            $countEmpls = $countQuery->count();

            return response()->json([
                'status' => 200,
                'employes' => $employes,
                'countEmpls' => $countEmpls
            ]);
        }
    }
    public function edit($idEmploye)
    {
        $checkEtpGrp = DB::table('etp_groupes')->where('idEntreprise', Customer::idCustomer())->exists();
        if ($checkEtpGrp) {
            $emp = DB::table('v_union_emp_grps')
                ->select('*')
                ->where('idEmploye', $idEmploye);
        } else {
            $emp = DB::table('v_employe_alls')
                ->select('idEmploye', 'idCustomer', 'role_id', 'matricule AS emp_matricule', 'name AS emp_name', 'user_idVille AS idVille', 'firstName AS emp_firstname', 'photo AS emp_photo', 'phone AS emp_phone', 'email AS emp_email', 'user_addr_code_postal AS emp_cp', 'fonction AS emp_fonction', 'cin AS emp_cin', 'isActive AS emp_is_active', 'hasRole AS emp_has_role')
                ->where('idCustomer', Customer::idCustomer())
                ->where('idEmploye', $idEmploye);
        }

        if ($emp->exists()) {
            $villes = DB::table('villes')
                ->select('idVille', 'ville')
                ->orderBy('ville', 'asc')
                ->get();

            return response()->json([
                'status' => 200,
                'emp' => $emp->first(),
                'villes' => $villes,
                'checkEtpGrp' => $checkEtpGrp,
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Employe introuvable !'
            ], 404);
        }
    }

    public function store(Request $req, EntrepriseService $entreprise, UserService $usr, EmployeService $employe)
    {
        $currentEnterpriseType = $entreprise->getEnterpriseType(Customer::idCustomer());

        $idEntreprise = Customer::idCustomer();
        $validate = Validator::make($req->all(), [
            'emp_name' => 'required|min:2|max:200',
        ]);
        if ($currentEnterpriseType && $currentEnterpriseType->idTypeEtp == 2) {
            if ($req->has('idEntreprise') && !empty($req->idEntreprise)) {
                $selectedEnterpriseId = $req->idEntreprise;
                $isValidEnterprise = DB::table('etp_groupeds')
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('idEntreprise', $selectedEnterpriseId)
                    ->exists();
                $isParentEnterprise = ($selectedEnterpriseId == Customer::idCustomer());

                if ($isValidEnterprise || $isParentEnterprise) {
                    $validate = Validator::make($req->all(), [
                        'emp_name' => 'required|min:2|max:200',
                        'idEntreprise' => 'nullable|exists:customers,idCustomer',
                    ]);

                    $idEntreprise = $selectedEnterpriseId;
                } else {
                    return response([
                        'status' => 403,
                        'message' => 'Entreprise non autorisée'
                    ], 403);
                }
            }
        }

        if ($validate->fails()) {
            return response([
                'status' => 422,
                'message' => $validate->messages()
            ], 422);
        }

        try {
            DB::transaction(function () use ($req, $usr, $employe, $idEntreprise) {
                $user = $usr->store($req->emp_matricule, $req->emp_name, $req->emp_firstname, $req->emp_email, $req->emp_phone, Hash::make('0000@#'));
                $employe->store($user->id, 6, $idEntreprise, 1, $this->getIdFonction($idEntreprise));
                $this->roleUser(4, $user->id, 1, 1, 1);
            });

            return response([
                'status' => 200,
                'message' => 'Employé ajouté avec succès'
            ]);
        } catch (Exception $e) {
            \Log::error('Erreur lors de l\'ajout d\'employé: ' . $e->getMessage());
            return response([
                'status' => 411,
                'message' => 'Ajout impossible !'
            ], 411);
        }
    }




    public function importExcel(Request $req, EntrepriseService $entreprise, UserService $usr, EmployeService $employe)
    {
        $idEntreprise = $req->idEntreprise;

        if (is_null($idEntreprise)) {
            $validate = Validator::make($req->all(), [
                'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
                'idEntreprise' => 'required|integer'
            ]);
        } else {
            $validate = Validator::make($req->all(), [
                'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240'
            ]);
        }

        if ($validate->fails()) {
            return response([
                'status' => 422,
                'message' => 'Fichier invalide',
                'errors' => $validate->messages()
            ], 422);
        }

        try {
            $currentEnterpriseType = DB::table('entreprises')
                ->select('idCustomer', 'idTypeEtp')
                ->where('idCustomer', Customer::idCustomer())
                ->first();

            if (!$currentEnterpriseType) {
                return response([
                    'status' => 404,
                    'message' => 'Entreprise non trouvée'
                ], 404);
            }

            \Log::info('Current enterprise type: ' . json_encode($currentEnterpriseType));
            \Log::info('Request idEntreprise: ' . $req->idEntreprise);
            \Log::info('Customer ID: ' . Customer::idCustomer());

            // Par défaut, entreprise connectée
            $idEntreprise = Customer::idCustomer();

            // Si l'entreprise connectée est un groupe (type 2)
            if ($currentEnterpriseType->idTypeEtp == 2) {
                // Si une entreprise est spécifiée et différente de l'entreprise parente
                if ($req->filled('idEntreprise') && $req->idEntreprise != Customer::idCustomer()) {
                    $selectedEnterpriseId = $req->idEntreprise;

                    \Log::info('Checking enterprise authorization: ' . $selectedEnterpriseId);

                    // Vérifier que l'entreprise sélectionnée appartient bien au groupe
                    $isValidEnterprise = DB::table('etp_groupeds')
                        ->where('idEntrepriseParent', Customer::idCustomer())
                        ->where('idEntreprise', $selectedEnterpriseId)
                        ->exists();

                    \Log::info('Is valid enterprise: ' . ($isValidEnterprise ? 'YES' : 'NO'));

                    if ($isValidEnterprise) {
                        $idEntreprise = $selectedEnterpriseId;
                    } else {
                        return response([
                            'status' => 403,
                            'message' => 'Entreprise non autorisée'
                        ], 403);
                    }
                }
                // Si pas d'entreprise spécifiée ou si c'est l'entreprise parente, on garde l'entreprise parente
            }

            \Log::info('Final enterprise ID: ' . $idEntreprise);

            // Importer le fichier Excel
            $import = new EmployesImport($usr, $employe, $idEntreprise);
            Excel::import($import, $req->file('excel_file'));

            return response([
                'status' => 200,
                'message' => 'Importation réussie',
                'data' => [
                    'total_rows' => $import->getTotalRows(),
                    'successful_imports' => $import->getSuccessfulImports(),
                    'failed_imports' => $import->getFailedImports(),
                    'errors' => $import->getErrors()
                ]
            ]);
        } catch (Exception $e) {
            \Log::error('Erreur lors de l\'import Excel: ' . $e->getMessage());
            return response([
                'status' => 411,
                'message' => 'Importation impossible !',
                'error' => $e->getMessage()
            ], 411);
        }
    }

    public function update(Request $req, $idEmploye)
    {
        // Debug: voir ce qui est reçu
        \Log::info('Request data:', $req->all());
        \Log::info('Files:', $req->file() ?: []);

        $validate = Validator::make($req->all(), [
            'idEntreprise' => 'required',
            'emp_name' => 'required|min:2|max:100',
            'idVille' => 'nullable|exists:villes,idVille',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validate->fails()) {
            \Log::error('Validation failed:', $validate->errors()->toArray());
            return response()->json(['error' => $validate->messages()]);
        }

        $employe = DB::table('users')->where('id', $idEmploye);

        if (!$employe->exists()) {
            return response()->json([
                'status' => 404,
                'message' => 'Employe introuvable !'
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Gestion de la photo
            $photoName = null;
            if ($req->hasFile('photo')) {
                $photo = $req->file('photo');
                $photoName = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();

                // Déplacer le fichier vers le dossier de stockage
                $photo->move(public_path('img/employes'), $photoName);

                // Supprimer l'ancienne photo si elle existe
                $oldPhoto = $employe->first()->photo;
                if ($oldPhoto && file_exists(public_path('img/employes/' . $oldPhoto))) {
                    unlink(public_path('img/employes/' . $oldPhoto));
                }
            }

            // Mise à jour des données
            $updateData = [
                'name' => $req->emp_name,
                'firstName' => $req->emp_firstname ?? '',
                'matricule' => $req->emp_matricule ?? '',
                'email' => $req->emp_email ?? '',
                'phone' => $req->emp_phone ?? '',
                'user_addr_lot' => $req->emp_lot ?? '',
                'user_addr_quartier' => $req->emp_qrt ?? '',
                'user_addr_code_postal' => $req->emp_cp ?? '',
                'idVille' => $req->idVille ?: null,
            ];

            // Ajouter le nom de la photo seulement si une nouvelle photo est uploadée
            if ($photoName) {
                $updateData['photo'] = $photoName;
            }

            $employe->update($updateData);

            // Gestion de la fonction
            if (!empty($req->emp_fonction)) {
                $check = DB::table('fonctions')
                    ->where('fonction', $req->emp_fonction)
                    ->where('idCustomer', Customer::idCustomer())
                    ->count();

                if ($check == 0) {
                    $idFonction = DB::table('fonctions')->insertGetId([
                        'fonction' => $req->emp_fonction,
                        'idCustomer' => Customer::idCustomer()
                    ]);

                    $employe->update(['idFonction' => $idFonction]);
                }
            }

            DB::table('employes')->where('idEmploye', $idEmploye)->update([
                'idCustomer' => $req->idEntreprise
            ]);

            DB::commit();
            return response()->json(['success' => 'Modification réussie']);
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Exception: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la modification: ' . $e->getMessage()]);
        }
    }


    // public function updateImageEmpl(Request $req, $idEmploye)
    // {
    //     // $validate = Validator::make($req->all(), [
    //     //     'photo' => 'required|image|mimes:png,jpg,webp,gif|max:6144'
    //     // ]);
    //     // if ($validate->fails()) {
    //     //     return back()->with('error', $validate->messages());
    //     // } else {

    //     $empl = DB::table('users')->select('photo')->where('id', $idEmploye)->first();

    //     if ($empl != null) {
    //         $folder = 'img/employes/' . $empl->photo;

    //         if (File::exists($folder)) {
    //             File::delete($folder);
    //         }

    //         $folderPath = public_path('img/employes/');

    //         $image_parts = explode(";base64,", $req->image);
    //         $image_type_aux = explode("image/", $image_parts[0]);
    //         $image_type = $image_type_aux[1];
    //         $image_base64 = base64_decode($image_parts[1]);

    //         $imageName = uniqid() . '.webp';
    //         $imageFullPath = $folderPath . $imageName;

    //         file_put_contents($imageFullPath, $image_base64);

    //         DB::table('users')->where('id', $idEmploye)->update([
    //             'photo' => $imageName,
    //         ]);
    //         return response()->json([
    //             'success' => 'Image Uploaded Successfully',
    //             'imageName' =>  $imageName
    //         ]);
    //     }
    // }

    public function updateImageEmpl(Request $req, $idEmploye)
    {
        // Validation du fichier
        $req->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048', // max 2MB
        ]);

        // Récupérer l'employé
        $empl = DB::table('users')->select('photo')->where('id', $idEmploye)->first();

        if (!$empl) {
            return response()->json(['error' => 'Employé non trouvé'], 404);
        }

        // Si l'employé a déjà une photo → supprimer l'ancienne
        if (!empty($empl->photo)) {
            $oldPath = 'img/employes/' . $empl->photo;
            if (Storage::disk('do')->exists($oldPath)) {
                Storage::disk('do')->delete($oldPath);
            }
        }

        // Sauvegarder la nouvelle photo
        $file = $req->file('photo');
        $manager = new ImageManager(new Driver());

        $image = $manager->read($file)->toWebp(80); // Compression qualité 80%
        $imageName = uniqid() . '.webp';
        $imagePath = 'img/employes/' . $imageName;

        Storage::disk('do')->put($imagePath, (string) $image, 'public');

        // Mise à jour BDD
        DB::table('users')->where('id', $idEmploye)->update([
            'photo' => $imageName,
        ]);

        return response()->json([
            'success' => 'Image mise à jour avec succès',
            'photoUrl' => Storage::disk('do')->url($imagePath)
        ]);
    }
    public function searchName(string $name)
    {
        $checkEtpGrp = DB::table('etp_groupes')->where('idEntreprise', Customer::idCustomer())->exists();
        if ($checkEtpGrp) {
            $apprenants = DB::table('v_union_emp_grps')
                ->select('idEmploye', 'idEntreprise', 'idEntrepriseParent', 'etp_name_parent', 'etp_name', 'emp_initial_name', 'emp_name', 'emp_firstname', 'emp_email', 'emp_matricule', 'emp_phone', 'emp_photo', 'emp_cin', 'emp_sexe', 'emp_is_active', 'emp_has_role', 'user_is_in_service')
                ->where('idEntrepriseParent', Customer::idCustomer())
                ->where('role_id', '=', 4)
                ->where(function ($query) use ($name) {
                    $query->where('emp_name', 'LIKE', '%' . $name . '%')
                        ->orWhere('emp_firstname', 'LIKE', '%' . $name . '%');
                })
                ->get();
        } else {
            $apprenants = DB::table('v_employe_alls')
                ->select('idEmploye', 'customerName AS etp_name', 'initialName AS emp_initial_name', 'name AS emp_name', 'firstName AS emp_firstname', 'email AS emp_email', 'matricule AS emp_matricule', 'phone AS emp_phone', 'photo AS emp_photo', 'cin AS emp_cin', 'sexe AS emp_sexe', 'isActive AS emp_is_active', 'hasRole AS emp_has_role', 'user_is_in_service')
                ->where(function ($query) {
                    $query->where('idCustomer', Customer::idCustomer())
                        ->where('role_id', 4);
                })
                ->where(function ($query) use ($name) {
                    $query->where('name', 'LIKE', '%' . $name . '%')
                        ->orWhere('firstname', 'LIKE', '%' . $name . '%');
                })
                ->get();
        }

        if (count($apprenants) > 0) {
            return response()->json([
                'status' => 200,
                'apprenants' => $apprenants
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Aucun résultat !'
            ], 404);
        }
    }

    public function getEmpFiltered($idEtp)
    {
        $checkEtpGrp = DB::table('etp_groupes')->where('idEntreprise', Customer::idCustomer())->exists();
        if ($checkEtpGrp) {
            $apprenants = DB::table('v_apprenant_etp_all_groups')
                ->select('idEmploye', 'emp_initial_name', 'emp_photo', 'emp_name', 'emp_firstname', 'emp_matricule', 'emp_email', 'etp_name', 'emp_phone', 'emp_is_active', 'user_is_in_service')
                ->where('idEntrepriseParent', Customer::idCustomer())
                ->where('role_id', '=', 4)
                ->groupBy('idEmploye', 'emp_initial_name', 'emp_photo', 'emp_name', 'emp_firstname', 'emp_matricule', 'emp_email', 'etp_name', 'emp_phone', 'emp_is_active', 'user_is_in_service')
                ->get();
        } else {
            $apprenants = DB::table('v_apprenant_etp_alls')
                ->select('idEmploye', 'emp_initial_name', 'emp_photo', 'emp_name', 'emp_firstname', 'emp_matricule', 'emp_email', 'etp_name', 'emp_phone', 'emp_is_active', 'user_is_in_service')
                ->where('idEtp', Customer::idCustomer())
                ->where('role_id', 4)
                // ->where('idEtp', $idEtp)
                ->groupBy('idEmploye', 'emp_initial_name', 'emp_photo', 'emp_name', 'emp_firstname', 'emp_matricule', 'emp_email', 'etp_name', 'emp_phone', 'emp_is_active', 'user_is_in_service')
                ->get();
        }
        return response()->json(['apprenants' => $apprenants]);
    }

    public function getDropdownItem()
    {
        $checkEtpGrp = DB::table('etp_groupes')->where('idEntreprise', Customer::idCustomer())->exists();
        // $etps = DB::table('v_apprenant_etp_all_filters')
        //     ->select('idEtp', 'etp_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
        //     ->where('idEtp', Customer::idCustomer())
        //     ->orderBy('etp_name', 'asc')
        //     ->groupBy('idEtp', 'etp_name')
        //     ->get();

        // $fonctions = DB::table('v_apprenant_etp_all_filters')
        //     ->select('idFonction', 'emp_fonction', DB::raw('COUNT(idEmploye) AS emp_nb'))
        //     ->where('idEtp', Customer::idCustomer())
        //     ->orderBy('emp_fonction', 'asc')
        //     ->groupBy('idFonction', 'emp_fonction')
        //     ->get();
        // dd($fonctions);
        if ($checkEtpGrp) {
            $villes = DB::table('v_periode_groups')
                ->select('project_id_ville', 'project_ville as ville', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idEntrepriseParent', Customer::idCustomer())
                ->orderBy('project_ville', 'asc')
                ->groupBy('project_id_ville', 'project_ville')
                ->get();

            $status = DB::table('v_apprenant_etp_all_groups')
                ->select('project_status', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idEntrepriseParent', Customer::idCustomer())
                ->where('project_status', '!=', 'null')
                ->orderBy('project_status', 'asc')
                ->groupBy('project_status')
                ->get();

            $modalites = DB::table('v_apprenant_etp_all_groups')
                ->select('project_modality', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idEntrepriseParent', Customer::idCustomer())
                ->where('project_modality', '!=', 'null')
                ->orderBy('project_modality', 'asc')
                ->groupBy('project_modality')
                ->get();

            $modules = DB::table('v_apprenant_etp_all_groups')
                ->select('idModule', 'module_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idEntrepriseParent', Customer::idCustomer())
                ->where('idModule', '!=', 'null')
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();

            $periodePrev3 = DB::table('v_apprenant_etp_all_groups')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idEntrepriseParent', Customer::idCustomer())
                //->where('id_cfp', Customer::idCustomer())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "prev_3_month")
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_apprenant_etp_all_groups')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idEntrepriseParent', Customer::idCustomer())
                //->where('id_cfp', Customer::idCustomer())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "prev_6_month")
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev12 = DB::table('v_apprenant_etp_all_groups')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idEntrepriseParent', Customer::idCustomer())
                //->where('id_cfp', Customer::idCustomer())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "prev_12_month")
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext3 = DB::table('v_apprenant_etp_all_groups')
                ->select('p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idEntrepriseParent', Customer::idCustomer())
                //->where('id_cfp', Customer::idCustomer())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "next_3_month")
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_periode_groups')
                ->select('p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idEntrepriseParent', Customer::idCustomer())
                ->where('p_id_periode', "next_6_month")
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext12 = DB::table('v_apprenant_etp_all_groups')
                ->select('p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idEntrepriseParent', Customer::idCustomer())
                //->where('id_cfp', Customer::idCustomer())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "next_12_month")
                ->groupBy('p_id_periode')
                ->first();

            // dd($modules);

        } else { //ETPS FILLES...
            $villes = DB::table('v_periodes')
                ->select('project_id_ville', 'project_ville as ville', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idEtp', Customer::idCustomer())
                ->orderBy('project_ville', 'asc')
                ->groupBy('project_id_ville', 'project_ville')
                ->get();

            $status = DB::table('v_apprenant_etp_alls')
                ->select('project_status', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idEtp', Customer::idCustomer())
                ->where('project_status', '!=', 'null')
                ->orderBy('project_status', 'asc')
                ->groupBy('project_status')
                ->get();

            $modalites = DB::table('v_apprenant_etp_alls')
                ->select('project_modality', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idEtp', Customer::idCustomer())
                ->where('project_modality', '!=', 'null')
                ->orderBy('project_modality', 'asc')
                ->groupBy('project_modality')
                ->get();

            $modules = DB::table('v_apprenant_etp_alls')
                ->select('idModule', 'module_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idEtp', Customer::idCustomer())
                ->where('idModule', '!=', 'null')
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();

            $periodePrev3 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idEtp', Customer::idCustomer())
                ->where('id_cfp', Customer::idCustomer())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "prev_3_month")
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idEtp', Customer::idCustomer())
                ->where('id_cfp', Customer::idCustomer())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "prev_6_month")
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev12 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idEtp', Customer::idCustomer())
                ->where('id_cfp', Customer::idCustomer())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "prev_12_month")
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext3 = DB::table('v_apprenant_etp_alls')
                ->select('p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idEtp', Customer::idCustomer())
                ->where('id_cfp', Customer::idCustomer())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "next_3_month")
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_periodes')
                ->select('p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idEtp', Customer::idCustomer())
                ->where('p_id_periode', "next_6_month")
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext12 = DB::table('v_apprenant_etp_alls')
                ->select('p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idEtp', Customer::idCustomer())
                ->where('id_cfp', Customer::idCustomer())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "next_12_month")
                ->groupBy('p_id_periode')
                ->first();
        }
        return response()->json([
            //'etps' => $etps,
            //'fonctions' => $fonctions,
            'villes' => $villes,
            'status' => $status,
            'modalites' => $modalites,
            'modules' => $modules,
            'periodePrev3' => $periodePrev3,
            'periodePrev6' => $periodePrev6,
            'periodePrev12' => $periodePrev12,
            'periodeNext3' => $periodeNext3,
            'periodeNext6' => $periodeNext6,
            'periodeNext12' => $periodeNext12
        ]);
    }

    public function filterItems(Request $req)
    {
        $idEtps = explode(',', $req->idEtp);
        $idFonctions = explode(',', $req->idFonction);
        $idModules = explode(',', $req->idModule);
        $idStatus = explode(',', $req->idStatut);
        $idModalites = explode(',', $req->idModalite);
        $idVilles = explode(',', $req->idVille);
        $idPeriodes = $req->idPeriode;

        $checkEtpGrp = DB::table('etp_groupes')->where('idEntreprise', Customer::idCustomer())->exists();

        if ($checkEtpGrp) {

            $query = DB::table('v_apprenant_etp_all_groups')
                ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_initial_name', 'emp_photo', 'emp_matricule', 'emp_phone', 'emp_email', 'emp_fonction', 'idEtp', 'etp_name', 'user_is_in_service')
                ->where('idEntrepriseParent', Customer::idCustomer());

            if ($idEtps[0] != null) {
                $query->whereIn('idEtp', $idEtps);

                if ($idFonctions[0] != null) {
                    $query->whereIn('idFonction', $idFonctions);
                }

                $fonctions = DB::table('v_periode_groups')
                    ->select('idFonction', 'emp_fonction', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->whereIn('idEtp', $idEtps)
                    ->orderBy('emp_fonction', 'asc')
                    ->groupBy('idFonction', 'emp_fonction')
                    ->get();

                $villes = DB::table('v_periode_groups')
                    ->select('project_id_ville', 'project_ville as ville', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->whereIn('idEtp', $idEtps)
                    ->orderBy('project_ville', 'asc')
                    ->groupBy('project_id_ville', 'project_ville')
                    ->get();

                $status = DB::table('v_apprenant_etp_all_groups')
                    ->select('project_status', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('project_status', '!=', 'null')
                    ->whereIn('idEtp', $idEtps)
                    ->orderBy('project_status', 'asc')
                    ->groupBy('project_status')
                    ->get();

                $modalites = DB::table('v_apprenant_etp_all_groups')
                    ->select('project_modality', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('project_modality', '!=', 'null')
                    ->whereIn('idEtp', $idEtps)
                    ->orderBy('project_modality', 'asc')
                    ->groupBy('project_modality')
                    ->get();

                $modules = DB::table('v_apprenant_etp_alls')
                    ->select('idModule', 'module_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('idModule', '!=', 'null')
                    ->whereIn('idEtp', $idEtps)
                    ->orderBy('module_name', 'asc')
                    ->groupBy('idModule', 'module_name')
                    ->get();

                $periodePrev3 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_3_month")
                    ->whereIn('idEtp', $idEtps)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodePrev6 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_6_month")
                    ->whereIn('idEtp', $idEtps)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodePrev12 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_12_month")
                    ->whereIn('idEtp', $idEtps)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext3 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_3_month")
                    ->whereIn('idEtp', $idEtps)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext6 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_6_month")
                    ->whereIn('idEtp', $idEtps)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext12 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_12_month")
                    ->whereIn('idEtp', $idEtps)
                    ->groupBy('p_id_periode')
                    ->first();
            } elseif ($idFonctions[0] != null) {
                $query->whereIn('idFonction', $idFonctions);

                $etps = DB::table('v_periode_groups')
                    ->select('idEtp', 'etp_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->whereIn('idFonction', $idFonctions)
                    ->orderBy('etp_name', 'asc')
                    ->groupBy('idEtp', 'etp_name')
                    ->get();

                $villes = DB::table('v_periode_groups')
                    ->select('project_id_ville', 'project_ville as ville', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->whereIn('idFonction', $idFonctions)
                    ->orderBy('project_ville', 'asc')
                    ->groupBy('project_id_ville', 'project_ville')
                    ->get();

                $status = DB::table('v_apprenant_etp_all_groups')
                    ->select('project_status', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('project_status', '!=', 'null')
                    ->whereIn('idFonction', $idFonctions)
                    ->orderBy('project_status', 'asc')
                    ->groupBy('project_status')
                    ->get();

                $modalites = DB::table('v_apprenant_etp_all_groups')
                    ->select('project_modality', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('project_modality', '!=', 'null')
                    ->whereIn('idFonction', $idFonctions)
                    ->orderBy('project_modality', 'asc')
                    ->groupBy('project_modality')
                    ->get();

                $modules = DB::table('v_apprenant_etp_all_groups')
                    ->select('idModule', 'module_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('idModule', '!=', 'null')
                    ->whereIn('idFonction', $idFonctions)
                    ->orderBy('module_name', 'asc')
                    ->groupBy('idModule', 'module_name')
                    ->get();

                $periodePrev3 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    //->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_3_month")
                    ->whereIn('idFonction', $idFonctions)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodePrev6 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    //->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_6_month")
                    ->whereIn('idFonction', $idFonctions)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodePrev12 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    //->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_12_month")
                    ->whereIn('idFonction', $idFonctions)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext3 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    //->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_3_month")
                    ->whereIn('idFonction', $idFonctions)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext6 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    //->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_6_month")
                    ->whereIn('idFonction', $idFonctions)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext12 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    //->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_12_month")
                    ->whereIn('idFonction', $idFonctions)
                    ->groupBy('p_id_periode')
                    ->first();
            } elseif ($idModules[0] != null) {
                $query->whereIn('idModule', $idModules);

                $etps = DB::table('v_apprenant_etp_all_groups')
                    ->select('idEtp', 'etp_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->whereIn('idModule', $idModules)
                    ->orderBy('etp_name', 'asc')
                    ->groupBy('idEtp', 'etp_name')
                    ->get();

                $fonctions = DB::table('v_apprenant_etp_all_groups')
                    ->select('idFonction', 'emp_fonction', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->whereIn('idModule', $idModules)
                    ->orderBy('emp_fonction', 'asc')
                    ->groupBy('idFonction', 'emp_fonction')
                    ->get();

                $villes = DB::table('v_apprenant_etp_all_groups')
                    ->select('project_id_ville', 'project_ville as ville', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->whereIn('idModule', $idModules)
                    ->orderBy('project_ville', 'asc')
                    ->groupBy('project_id_ville', 'project_ville')
                    ->get();

                $status = DB::table('v_apprenant_etp_all_groups')
                    ->select('project_status', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('project_status', '!=', 'null')
                    ->whereIn('idModule', $idModules)
                    ->orderBy('project_status', 'asc')
                    ->groupBy('project_status')
                    ->get();

                $modalites = DB::table('v_apprenant_etp_all_groups')
                    ->select('project_modality', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('project_modality', '!=', 'null')
                    ->whereIn('idModule', $idModules)
                    ->orderBy('project_modality', 'asc')
                    ->groupBy('project_modality')
                    ->get();

                $periodePrev3 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    //->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_3_month")
                    ->whereIn('idModule', $idModules)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodePrev6 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    //->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_6_month")
                    ->whereIn('idModule', $idModules)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodePrev12 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    //->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_12_month")
                    ->whereIn('idModule', $idModules)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext3 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    // ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_3_month")
                    ->whereIn('idModule', $idModules)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext6 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    // ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_6_month")
                    ->whereIn('idModule', $idModules)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext12 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    //->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_12_month")
                    ->whereIn('idModule', $idModules)
                    ->groupBy('p_id_periode')
                    ->first();
            } elseif ($idStatus[0] != null) {
                $query->whereIn('project_status', $idStatus);

                $etps = DB::table('v_apprenant_etp_all_groups')
                    ->select('idEtp', 'etp_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->whereIn('project_status', $idStatus)
                    ->orderBy('etp_name', 'asc')
                    ->groupBy('idEtp', 'etp_name')
                    ->get();

                $fonctions = DB::table('v_apprenant_etp_all_groups')
                    ->select('idFonction', 'emp_fonction', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->whereIn('project_status', $idStatus)
                    ->orderBy('emp_fonction', 'asc')
                    ->groupBy('idFonction', 'emp_fonction')
                    ->get();

                $villes = DB::table('v_apprenant_etp_all_groups')
                    ->select('project_id_ville', 'project_ville as ville', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->whereIn('project_status', $idStatus)
                    ->orderBy('project_ville', 'asc')
                    ->groupBy('project_id_ville', 'project_ville')
                    ->get();

                $modalites = DB::table('v_apprenant_etp_all_groups')
                    ->select('project_modality', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('project_modality', '!=', 'null')
                    ->whereIn('project_status', $idStatus)
                    ->orderBy('project_modality', 'asc')
                    ->groupBy('project_modality')
                    ->get();

                $modules = DB::table('v_apprenant_etp_all_groups')
                    ->select('idModule', 'module_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('idModule', '!=', 'null')
                    ->whereIn('project_status', $idStatus)
                    ->orderBy('module_name', 'asc')
                    ->groupBy('idModule', 'module_name')
                    ->get();

                $periodePrev3 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    //->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_3_month")
                    ->whereIn('project_status', $idStatus)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodePrev6 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    //->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_6_month")
                    ->whereIn('project_status', $idStatus)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodePrev12 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    //->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_12_month")
                    ->whereIn('project_status', $idStatus)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext3 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    //->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_3_month")
                    ->whereIn('project_status', $idStatus)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext6 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    //->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_6_month")
                    ->whereIn('project_status', $idStatus)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext12 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    //->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_12_month")
                    ->whereIn('project_status', $idStatus)
                    ->groupBy('p_id_periode')
                    ->first();
            } elseif ($idModalites[0] != null) {
                $query->whereIn('project_modality', $idModalites);

                $etps = DB::table('v_apprenant_etp_all_groups')
                    ->select('idEtp', 'etp_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->whereIn('project_modality', $idModalites)
                    ->orderBy('etp_name', 'asc')
                    ->groupBy('idEtp', 'etp_name')
                    ->get();

                $fonctions = DB::table('v_apprenant_etp_all_groups')
                    ->select('idFonction', 'emp_fonction', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->whereIn('project_modality', $idModalites)
                    ->orderBy('emp_fonction', 'asc')
                    ->groupBy('idFonction', 'emp_fonction')
                    ->get();

                $villes = DB::table('v_apprenant_etp_all_groups')
                    ->select('project_id_ville', 'project_ville as ville', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->whereIn('project_modality', $idModalites)
                    ->orderBy('project_ville', 'asc')
                    ->groupBy('project_id_ville', 'project_ville')
                    ->get();

                $status = DB::table('v_apprenant_etp_all_groups')
                    ->select('project_status', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('project_status', '!=', 'null')
                    ->whereIn('project_modality', $idModalites)
                    ->orderBy('project_status', 'asc')
                    ->groupBy('project_status')
                    ->get();

                $modules = DB::table('v_apprenant_etp_all_groups')
                    ->select('idModule', 'module_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('idModule', '!=', 'null')
                    ->whereIn('project_modality', $idModalites)
                    ->orderBy('module_name', 'asc')
                    ->groupBy('idModule', 'module_name')
                    ->get();

                $periodePrev3 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    //->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_3_month")
                    ->whereIn('project_modality', $idModalites)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodePrev6 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    //->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_6_month")
                    ->whereIn('project_modality', $idModalites)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodePrev12 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    //->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_12_month")
                    ->whereIn('project_modality', $idModalites)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext3 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    //->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_3_month")
                    ->whereIn('project_modality', $idModalites)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext6 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    //->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_6_month")
                    ->whereIn('project_modality', $idModalites)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext12 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    //->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_12_month")
                    ->whereIn('project_modality', $idModalites)
                    ->groupBy('p_id_periode')
                    ->first();
            } elseif ($idVilles[0] != null) {
                $query->whereIn('project_id_ville', $idVilles);

                $etps = DB::table('v_periode_groups')
                    ->select('idEtp', 'etp_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->whereIn('project_id_ville', $idVilles)
                    ->orderBy('etp_name', 'asc')
                    ->groupBy('idEtp', 'etp_name')
                    ->get();

                $fonctions = DB::table('v_periode_groups')
                    ->select('idFonction', 'emp_fonction', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->whereIn('project_id_ville', $idVilles)
                    ->orderBy('emp_fonction', 'asc')
                    ->groupBy('idFonction', 'emp_fonction')
                    ->get();

                $status = DB::table('v_apprenant_etp_all_groups')
                    ->select('project_status', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('project_status', '!=', 'null')
                    ->whereIn('project_id_ville', $idVilles)
                    ->orderBy('project_status', 'asc')
                    ->groupBy('project_status')
                    ->get();

                $modalites = DB::table('v_apprenant_etp_all_groups')
                    ->select('project_modality', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('project_modality', '!=', 'null')
                    ->whereIn('project_id_ville', $idVilles)
                    ->orderBy('project_modality', 'asc')
                    ->groupBy('project_modality')
                    ->get();

                $modules = DB::table('v_apprenant_etp_all_groups')
                    ->select('idModule', 'module_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('idModule', '!=', 'null')
                    ->whereIn('project_id_ville', $idVilles)
                    ->orderBy('module_name', 'asc')
                    ->groupBy('idModule', 'module_name')
                    ->get();

                $periodePrev3 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_3_month")
                    ->whereIn('project_id_ville', $idVilles)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodePrev6 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_6_month")
                    ->whereIn('project_id_ville', $idVilles)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodePrev12 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_12_month")
                    ->whereIn('project_id_ville', $idVilles)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext3 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_3_month")
                    ->whereIn('project_id_ville', $idVilles)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext6 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_6_month")
                    ->whereIn('project_id_ville', $idVilles)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext12 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_12_month")
                    ->whereIn('project_id_ville', $idVilles)
                    ->groupBy('p_id_periode')
                    ->first();
            } elseif ($idPeriodes != null) {
                $query->where('p_id_periode', $idPeriodes);

                $etps = DB::table('v_periode_groups')
                    ->select('idEtp', 'etp_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('p_id_periode', $idPeriodes)
                    ->orderBy('etp_name', 'asc')
                    ->groupBy('idEtp', 'etp_name')
                    ->get();

                $fonctions = DB::table('v_periode_groups')
                    ->select('idFonction', 'emp_fonction', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('p_id_periode', $idPeriodes)
                    ->orderBy('emp_fonction', 'asc')
                    ->groupBy('idFonction', 'emp_fonction')
                    ->get();

                $villes = DB::table('v_periode_groups')
                    ->select('project_id_ville', 'project_ville as ville', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('p_id_periode', $idPeriodes)
                    ->orderBy('project_ville', 'asc')
                    ->groupBy('project_id_ville', 'project_ville')
                    ->get();

                $status = DB::table('v_apprenant_etp_all_groups')
                    ->select('project_status', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('project_status', '!=', 'null')
                    ->where('p_id_periode', $idPeriodes)
                    ->orderBy('project_status', 'asc')
                    ->groupBy('project_status')
                    ->get();

                $modalites = DB::table('v_apprenant_etp_all_groups')
                    ->select('project_modality', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('project_modality', '!=', 'null')
                    ->where('p_id_periode', $idPeriodes)
                    ->orderBy('project_modality', 'asc')
                    ->groupBy('project_modality')
                    ->get();

                $modules = DB::table('v_apprenant_etp_all_groups')
                    ->select('idModule', 'module_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('idModule', '!=', 'null')
                    ->where('p_id_periode', $idPeriodes)
                    ->orderBy('module_name', 'asc')
                    ->groupBy('idModule', 'module_name')
                    ->get();
            } else {
                $etps = DB::table('v_apprenant_etp_all_filter_groups')
                    ->select('idEtp', 'etp_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->orderBy('etp_name', 'asc')
                    ->groupBy('idEtp', 'etp_name')
                    ->get();

                $fonctions = DB::table('v_apprenant_etp_all_filter_groups')
                    ->select('idFonction', 'emp_fonction', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->orderBy('emp_fonction', 'asc')
                    ->groupBy('idFonction', 'emp_fonction')
                    ->get();

                $villes = DB::table('v_periode_groups')
                    ->select('project_id_ville', 'project_ville as ville', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->orderBy('project_ville', 'asc')
                    ->groupBy('project_id_ville', 'project_ville')
                    ->get();

                $status = DB::table('v_apprenant_etp_all_groups')
                    ->select('project_status', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('project_status', '!=', 'null')
                    ->orderBy('project_status', 'asc')
                    ->groupBy('project_status')
                    ->get();

                $modalites = DB::table('v_apprenant_etp_all_groups')
                    ->select('project_modality', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('project_modality', '!=', 'null')
                    ->orderBy('project_modality', 'asc')
                    ->groupBy('project_modality')
                    ->get();

                $modules = DB::table('v_apprenant_etp_all_groups')
                    ->select('idModule', 'module_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('idModule', '!=', 'null')
                    ->orderBy('module_name', 'asc')
                    ->groupBy('idModule', 'module_name')
                    ->get();

                $periodePrev3 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_3_month")
                    ->groupBy('p_id_periode')
                    ->first();

                $periodePrev6 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_6_month")
                    ->groupBy('p_id_periode')
                    ->first();

                $periodePrev12 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_12_month")
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext3 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_3_month")
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext6 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_6_month")
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext12 = DB::table('v_apprenant_etp_all_groups')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_12_month")
                    ->groupBy('p_id_periode')
                    ->first();
            }

            $query->groupBy('idEmploye', 'emp_name', 'emp_firstname', 'emp_initial_name', 'emp_photo', 'emp_matricule', 'emp_phone', 'emp_email', 'emp_fonction', 'idEtp', 'etp_name');

            $apprs = $query->get();

            if ($idEtps[0] != null) {
                return response()->json([
                    'fonctions' => $fonctions,
                    'status' => $status,
                    'villes' => $villes,
                    'modalites' => $modalites,
                    'modules' => $modules,
                    'periodes' => $idPeriodes,
                    'apprs' => $apprs,
                    'periodePrev3' => $periodePrev3,
                    'periodePrev6' => $periodePrev6,
                    'periodePrev12' => $periodePrev12,
                    'periodeNext3' => $periodeNext3,
                    'periodeNext6' => $periodeNext6,
                    'periodeNext12' => $periodeNext12
                ]);
            } elseif ($idFonctions[0] != null) {
                return response()->json([
                    'etps' => $etps,
                    'status' => $status,
                    'villes' => $villes,
                    'modalites' => $modalites,
                    'modules' => $modules,
                    'periodes' => $idPeriodes,
                    'apprs' => $apprs,
                    'periodePrev3' => $periodePrev3,
                    'periodePrev6' => $periodePrev6,
                    'periodePrev12' => $periodePrev12,
                    'periodeNext3' => $periodeNext3,
                    'periodeNext6' => $periodeNext6,
                    'periodeNext12' => $periodeNext12
                ]);
            } elseif ($idModules[0] != null) {
                return response()->json([
                    'etps' => $etps,
                    'fonctions' => $fonctions,
                    'status' => $status,
                    'villes' => $villes,
                    'modalites' => $modalites,
                    'periodes' => $idPeriodes,
                    'apprs' => $apprs,
                    'periodePrev3' => $periodePrev3,
                    'periodePrev6' => $periodePrev6,
                    'periodePrev12' => $periodePrev12,
                    'periodeNext3' => $periodeNext3,
                    'periodeNext6' => $periodeNext6,
                    'periodeNext12' => $periodeNext12
                ]);
            } elseif ($idStatus[0] != null) {
                return response()->json([
                    'etps' => $etps,
                    'fonctions' => $fonctions,
                    'villes' => $villes,
                    'modalites' => $modalites,
                    'modules' => $modules,
                    'periodes' => $idPeriodes,
                    'apprs' => $apprs,
                    'periodePrev3' => $periodePrev3,
                    'periodePrev6' => $periodePrev6,
                    'periodePrev12' => $periodePrev12,
                    'periodeNext3' => $periodeNext3,
                    'periodeNext6' => $periodeNext6,
                    'periodeNext12' => $periodeNext12
                ]);
            } elseif ($idModalites[0] != null) {
                return response()->json([
                    'etps' => $etps,
                    'fonctions' => $fonctions,
                    'status' => $status,
                    'villes' => $villes,
                    'modules' => $modules,
                    'periodes' => $idPeriodes,
                    'apprs' => $apprs,
                    'periodePrev3' => $periodePrev3,
                    'periodePrev6' => $periodePrev6,
                    'periodePrev12' => $periodePrev12,
                    'periodeNext3' => $periodeNext3,
                    'periodeNext6' => $periodeNext6,
                    'periodeNext12' => $periodeNext12
                ]);
            } elseif ($idVilles[0] != null) {
                return response()->json([
                    'etps' => $etps,
                    'fonctions' => $fonctions,
                    'status' => $status,
                    'modalites' => $modalites,
                    'modules' => $modules,
                    'periodes' => $idPeriodes,
                    'apprs' => $apprs,
                    'periodePrev3' => $periodePrev3,
                    'periodePrev6' => $periodePrev6,
                    'periodePrev12' => $periodePrev12,
                    'periodeNext3' => $periodeNext3,
                    'periodeNext6' => $periodeNext6,
                    'periodeNext12' => $periodeNext12
                ]);
            } elseif ($idPeriodes != null) {
                return response()->json([
                    'etps' => $etps,
                    'fonctions' => $fonctions,
                    'status' => $status,
                    'villes' => $villes,
                    'modalites' => $modalites,
                    'modules' => $modules,
                    'apprs' => $apprs
                ]);
            } else {
                return response()->json([
                    'etps' => $etps,
                    'fonctions' => $fonctions,
                    'status' => $status,
                    'villes' => $villes,
                    'modalites' => $modalites,
                    'modules' => $modules,
                    'periodes' => $idPeriodes,
                    'apprs' => $apprs,
                    'periodePrev3' => $periodePrev3,
                    'periodePrev6' => $periodePrev6,
                    'periodePrev12' => $periodePrev12,
                    'periodeNext3' => $periodeNext3,
                    'periodeNext6' => $periodeNext6,
                    'periodeNext12' => $periodeNext12
                ]);
            }
        } else {

            //ETPS FILLES...
            $query = DB::table('v_apprenant_etp_alls')
                ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_initial_name', 'emp_photo', 'emp_matricule', 'emp_phone', 'emp_email', 'emp_fonction', 'idEtp', 'etp_name', 'user_is_in_service')
                ->where('idEtp', Customer::idCustomer());

            if ($idEtps[0] != null) {
                $query->whereIn('idEtp', $idEtps);

                if ($idFonctions[0] != null) {
                    $query->whereIn('idFonction', $idFonctions);
                }

                $fonctions = DB::table('v_periodes')
                    ->select('idFonction', 'emp_fonction', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->whereIn('idEtp', $idEtps)
                    ->orderBy('emp_fonction', 'asc')
                    ->groupBy('idFonction', 'emp_fonction')
                    ->get();

                $villes = DB::table('v_periodes')
                    ->select('project_id_ville', 'project_ville as ville', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->whereIn('idEtp', $idEtps)
                    ->orderBy('project_ville', 'asc')
                    ->groupBy('project_id_ville', 'project_ville')
                    ->get();

                $status = DB::table('v_apprenant_etp_alls')
                    ->select('project_status', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('project_status', '!=', 'null')
                    ->whereIn('idEtp', $idEtps)
                    ->orderBy('project_status', 'asc')
                    ->groupBy('project_status')
                    ->get();

                $modalites = DB::table('v_apprenant_etp_alls')
                    ->select('project_modality', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('project_modality', '!=', 'null')
                    ->whereIn('idEtp', $idEtps)
                    ->orderBy('project_modality', 'asc')
                    ->groupBy('project_modality')
                    ->get();

                $modules = DB::table('v_apprenant_etp_alls')
                    ->select('idModule', 'module_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('idModule', '!=', 'null')
                    ->whereIn('idEtp', $idEtps)
                    ->orderBy('module_name', 'asc')
                    ->groupBy('idModule', 'module_name')
                    ->get();

                $periodePrev3 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_3_month")
                    ->whereIn('idEtp', $idEtps)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodePrev6 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_6_month")
                    ->whereIn('idEtp', $idEtps)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodePrev12 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_12_month")
                    ->whereIn('idEtp', $idEtps)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext3 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_3_month")
                    ->whereIn('idEtp', $idEtps)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext6 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_6_month")
                    ->whereIn('idEtp', $idEtps)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext12 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_12_month")
                    ->whereIn('idEtp', $idEtps)
                    ->groupBy('p_id_periode')
                    ->first();
            } elseif ($idFonctions[0] != null) {
                $query->whereIn('idFonction', $idFonctions);

                $etps = DB::table('v_periodes')
                    ->select('idEtp', 'etp_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->whereIn('idFonction', $idFonctions)
                    ->orderBy('etp_name', 'asc')
                    ->groupBy('idEtp', 'etp_name')
                    ->get();

                $villes = DB::table('v_periodes')
                    ->select('project_id_ville', 'project_ville as ville', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->whereIn('idFonction', $idFonctions)
                    ->orderBy('project_ville', 'asc')
                    ->groupBy('project_id_ville', 'project_ville')
                    ->get();

                $status = DB::table('v_apprenant_etp_alls')
                    ->select('project_status', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('project_status', '!=', 'null')
                    ->whereIn('idFonction', $idFonctions)
                    ->orderBy('project_status', 'asc')
                    ->groupBy('project_status')
                    ->get();

                $modalites = DB::table('v_apprenant_etp_alls')
                    ->select('project_modality', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('project_modality', '!=', 'null')
                    ->whereIn('idFonction', $idFonctions)
                    ->orderBy('project_modality', 'asc')
                    ->groupBy('project_modality')
                    ->get();

                $modules = DB::table('v_apprenant_etp_alls')
                    ->select('idModule', 'module_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('idModule', '!=', 'null')
                    ->whereIn('idFonction', $idFonctions)
                    ->orderBy('module_name', 'asc')
                    ->groupBy('idModule', 'module_name')
                    ->get();

                $periodePrev3 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_3_month")
                    ->whereIn('idFonction', $idFonctions)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodePrev6 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_6_month")
                    ->whereIn('idFonction', $idFonctions)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodePrev12 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_12_month")
                    ->whereIn('idFonction', $idFonctions)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext3 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_3_month")
                    ->whereIn('idFonction', $idFonctions)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext6 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_6_month")
                    ->whereIn('idFonction', $idFonctions)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext12 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_12_month")
                    ->whereIn('idFonction', $idFonctions)
                    ->groupBy('p_id_periode')
                    ->first();
            } elseif ($idModules[0] != null) {
                $query->whereIn('idModule', $idModules);

                $etps = DB::table('v_apprenant_etp_alls')
                    ->select('idEtp', 'etp_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->whereIn('idModule', $idModules)
                    ->orderBy('etp_name', 'asc')
                    ->groupBy('idEtp', 'etp_name')
                    ->get();

                $fonctions = DB::table('v_apprenant_etp_alls')
                    ->select('idFonction', 'emp_fonction', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->whereIn('idModule', $idModules)
                    ->orderBy('emp_fonction', 'asc')
                    ->groupBy('idFonction', 'emp_fonction')
                    ->get();

                $villes = DB::table('v_apprenant_etp_alls')
                    ->select('project_id_ville', 'project_ville as ville', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->whereIn('idModule', $idModules)
                    ->orderBy('project_ville', 'asc')
                    ->groupBy('project_id_ville', 'project_ville')
                    ->get();

                $status = DB::table('v_apprenant_etp_alls')
                    ->select('project_status', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('project_status', '!=', 'null')
                    ->whereIn('idModule', $idModules)
                    ->orderBy('project_status', 'asc')
                    ->groupBy('project_status')
                    ->get();

                $modalites = DB::table('v_apprenant_etp_alls')
                    ->select('project_modality', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('project_modality', '!=', 'null')
                    ->whereIn('idModule', $idModules)
                    ->orderBy('project_modality', 'asc')
                    ->groupBy('project_modality')
                    ->get();

                $periodePrev3 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_3_month")
                    ->whereIn('idModule', $idModules)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodePrev6 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_6_month")
                    ->whereIn('idModule', $idModules)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodePrev12 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_12_month")
                    ->whereIn('idModule', $idModules)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext3 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_3_month")
                    ->whereIn('idModule', $idModules)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext6 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_6_month")
                    ->whereIn('idModule', $idModules)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext12 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_12_month")
                    ->whereIn('idModule', $idModules)
                    ->groupBy('p_id_periode')
                    ->first();
            } elseif ($idStatus[0] != null) {
                $query->whereIn('project_status', $idStatus);

                $etps = DB::table('v_apprenant_etp_alls')
                    ->select('idEtp', 'etp_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->whereIn('project_status', $idStatus)
                    ->orderBy('etp_name', 'asc')
                    ->groupBy('idEtp', 'etp_name')
                    ->get();

                $fonctions = DB::table('v_apprenant_etp_alls')
                    ->select('idFonction', 'emp_fonction', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->whereIn('project_status', $idStatus)
                    ->orderBy('emp_fonction', 'asc')
                    ->groupBy('idFonction', 'emp_fonction')
                    ->get();

                $villes = DB::table('v_apprenant_etp_alls')
                    ->select('project_id_ville', 'project_ville as ville', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->whereIn('project_status', $idStatus)
                    ->orderBy('project_ville', 'asc')
                    ->groupBy('project_id_ville', 'project_ville')
                    ->get();

                $modalites = DB::table('v_apprenant_etp_alls')
                    ->select('project_modality', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('project_modality', '!=', 'null')
                    ->whereIn('project_status', $idStatus)
                    ->orderBy('project_modality', 'asc')
                    ->groupBy('project_modality')
                    ->get();

                $modules = DB::table('v_apprenant_etp_alls')
                    ->select('idModule', 'module_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('idModule', '!=', 'null')
                    ->whereIn('project_status', $idStatus)
                    ->orderBy('module_name', 'asc')
                    ->groupBy('idModule', 'module_name')
                    ->get();

                $periodePrev3 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_3_month")
                    ->whereIn('project_status', $idStatus)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodePrev6 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_6_month")
                    ->whereIn('project_status', $idStatus)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodePrev12 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_12_month")
                    ->whereIn('project_status', $idStatus)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext3 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_3_month")
                    ->whereIn('project_status', $idStatus)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext6 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_6_month")
                    ->whereIn('project_status', $idStatus)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext12 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_12_month")
                    ->whereIn('project_status', $idStatus)
                    ->groupBy('p_id_periode')
                    ->first();
            } elseif ($idModalites[0] != null) {
                $query->whereIn('project_modality', $idModalites);

                $etps = DB::table('v_apprenant_etp_alls')
                    ->select('idEtp', 'etp_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->whereIn('project_modality', $idModalites)
                    ->orderBy('etp_name', 'asc')
                    ->groupBy('idEtp', 'etp_name')
                    ->get();

                $fonctions = DB::table('v_apprenant_etp_alls')
                    ->select('idFonction', 'emp_fonction', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->whereIn('project_modality', $idModalites)
                    ->orderBy('emp_fonction', 'asc')
                    ->groupBy('idFonction', 'emp_fonction')
                    ->get();

                $villes = DB::table('v_apprenant_etp_alls')
                    ->select('project_id_ville', 'project_ville as ville', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->whereIn('project_modality', $idModalites)
                    ->orderBy('project_ville', 'asc')
                    ->groupBy('project_id_ville', 'project_ville')
                    ->get();

                $status = DB::table('v_apprenant_etp_alls')
                    ->select('project_status', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('project_status', '!=', 'null')
                    ->whereIn('project_modality', $idModalites)
                    ->orderBy('project_status', 'asc')
                    ->groupBy('project_status')
                    ->get();

                $modules = DB::table('v_apprenant_etp_alls')
                    ->select('idModule', 'module_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('idModule', '!=', 'null')
                    ->whereIn('project_modality', $idModalites)
                    ->orderBy('module_name', 'asc')
                    ->groupBy('idModule', 'module_name')
                    ->get();

                $periodePrev3 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_3_month")
                    ->whereIn('project_modality', $idModalites)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodePrev6 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_6_month")
                    ->whereIn('project_modality', $idModalites)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodePrev12 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_12_month")
                    ->whereIn('project_modality', $idModalites)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext3 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_3_month")
                    ->whereIn('project_modality', $idModalites)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext6 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_6_month")
                    ->whereIn('project_modality', $idModalites)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext12 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_12_month")
                    ->whereIn('project_modality', $idModalites)
                    ->groupBy('p_id_periode')
                    ->first();
            } elseif ($idVilles[0] != null) {
                $query->whereIn('project_id_ville', $idVilles);

                $etps = DB::table('v_periodes')
                    ->select('idEtp', 'etp_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->whereIn('project_id_ville', $idVilles)
                    ->orderBy('etp_name', 'asc')
                    ->groupBy('idEtp', 'etp_name')
                    ->get();

                $fonctions = DB::table('v_periodes')
                    ->select('idFonction', 'emp_fonction', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->whereIn('project_id_ville', $idVilles)
                    ->orderBy('emp_fonction', 'asc')
                    ->groupBy('idFonction', 'emp_fonction')
                    ->get();

                $status = DB::table('v_apprenant_etp_alls')
                    ->select('project_status', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('project_status', '!=', 'null')
                    ->whereIn('project_id_ville', $idVilles)
                    ->orderBy('project_status', 'asc')
                    ->groupBy('project_status')
                    ->get();

                $modalites = DB::table('v_apprenant_etp_alls')
                    ->select('project_modality', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('project_modality', '!=', 'null')
                    ->whereIn('project_id_ville', $idVilles)
                    ->orderBy('project_modality', 'asc')
                    ->groupBy('project_modality')
                    ->get();

                $modules = DB::table('v_apprenant_etp_alls')
                    ->select('idModule', 'module_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('idModule', '!=', 'null')
                    ->whereIn('project_id_ville', $idVilles)
                    ->orderBy('module_name', 'asc')
                    ->groupBy('idModule', 'module_name')
                    ->get();

                $periodePrev3 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_3_month")
                    ->whereIn('project_id_ville', $idVilles)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodePrev6 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_6_month")
                    ->whereIn('project_id_ville', $idVilles)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodePrev12 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_12_month")
                    ->whereIn('project_id_ville', $idVilles)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext3 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_3_month")
                    ->whereIn('project_id_ville', $idVilles)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext6 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_6_month")
                    ->whereIn('project_id_ville', $idVilles)
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext12 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_12_month")
                    ->whereIn('project_id_ville', $idVilles)
                    ->groupBy('p_id_periode')
                    ->first();
            } elseif ($idPeriodes != null) {
                $query->where('p_id_periode', $idPeriodes);

                $etps = DB::table('v_periodes')
                    ->select('idEtp', 'etp_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('p_id_periode', $idPeriodes)
                    ->orderBy('etp_name', 'asc')
                    ->groupBy('idEtp', 'etp_name')
                    ->get();

                $fonctions = DB::table('v_periodes')
                    ->select('idFonction', 'emp_fonction', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('p_id_periode', $idPeriodes)
                    ->orderBy('emp_fonction', 'asc')
                    ->groupBy('idFonction', 'emp_fonction')
                    ->get();

                $villes = DB::table('v_periodes')
                    ->select('project_id_ville', 'project_ville as ville', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('p_id_periode', $idPeriodes)
                    ->orderBy('project_ville', 'asc')
                    ->groupBy('project_id_ville', 'project_ville')
                    ->get();

                $status = DB::table('v_apprenant_etp_alls')
                    ->select('project_status', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('project_status', '!=', 'null')
                    ->where('p_id_periode', $idPeriodes)
                    ->orderBy('project_status', 'asc')
                    ->groupBy('project_status')
                    ->get();

                $modalites = DB::table('v_apprenant_etp_alls')
                    ->select('project_modality', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('project_modality', '!=', 'null')
                    ->where('p_id_periode', $idPeriodes)
                    ->orderBy('project_modality', 'asc')
                    ->groupBy('project_modality')
                    ->get();

                $modules = DB::table('v_apprenant_etp_alls')
                    ->select('idModule', 'module_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('idModule', '!=', 'null')
                    ->where('p_id_periode', $idPeriodes)
                    ->orderBy('module_name', 'asc')
                    ->groupBy('idModule', 'module_name')
                    ->get();
            } else {
                $etps = DB::table('v_apprenant_etp_all_filters')
                    ->select('idEtp', 'etp_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->orderBy('etp_name', 'asc')
                    ->groupBy('idEtp', 'etp_name')
                    ->get();

                $fonctions = DB::table('v_apprenant_etp_all_filters')
                    ->select('idFonction', 'emp_fonction', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->orderBy('emp_fonction', 'asc')
                    ->groupBy('idFonction', 'emp_fonction')
                    ->get();

                $villes = DB::table('v_periodes')
                    ->select('project_id_ville', 'project_ville as ville', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->orderBy('project_ville', 'asc')
                    ->groupBy('project_id_ville', 'project_ville')
                    ->get();

                $status = DB::table('v_apprenant_etp_alls')
                    ->select('project_status', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('project_status', '!=', 'null')
                    ->orderBy('project_status', 'asc')
                    ->groupBy('project_status')
                    ->get();

                $modalites = DB::table('v_apprenant_etp_alls')
                    ->select('project_modality', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('project_modality', '!=', 'null')
                    ->orderBy('project_modality', 'asc')
                    ->groupBy('project_modality')
                    ->get();

                $modules = DB::table('v_apprenant_etp_alls')
                    ->select('idModule', 'module_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('idModule', '!=', 'null')
                    ->orderBy('module_name', 'asc')
                    ->groupBy('idModule', 'module_name')
                    ->get();

                $periodePrev3 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_3_month")
                    ->groupBy('p_id_periode')
                    ->first();

                $periodePrev6 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_6_month")
                    ->groupBy('p_id_periode')
                    ->first();

                $periodePrev12 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "prev_12_month")
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext3 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_3_month")
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext6 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_6_month")
                    ->groupBy('p_id_periode')
                    ->first();

                $periodeNext12 = DB::table('v_apprenant_etp_alls')
                    ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                    ->where('idEtp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->where('dateDebut', '!=', 'null')
                    ->where('p_id_periode', "next_12_month")
                    ->groupBy('p_id_periode')
                    ->first();
            }

            $query->groupBy('idEmploye', 'emp_name', 'emp_firstname', 'emp_initial_name', 'emp_photo', 'emp_matricule', 'emp_phone', 'emp_email', 'emp_fonction', 'idEtp', 'etp_name');

            $apprs = $query->get();

            if ($idEtps[0] != null) {
                return response()->json([
                    'fonctions' => $fonctions,
                    'status' => $status,
                    'villes' => $villes,
                    'modalites' => $modalites,
                    'modules' => $modules,
                    'periodes' => $idPeriodes,
                    'apprs' => $apprs,
                    'periodePrev3' => $periodePrev3,
                    'periodePrev6' => $periodePrev6,
                    'periodePrev12' => $periodePrev12,
                    'periodeNext3' => $periodeNext3,
                    'periodeNext6' => $periodeNext6,
                    'periodeNext12' => $periodeNext12
                ]);
            } elseif ($idFonctions[0] != null) {
                return response()->json([
                    'etps' => $etps,
                    'status' => $status,
                    'villes' => $villes,
                    'modalites' => $modalites,
                    'modules' => $modules,
                    'periodes' => $idPeriodes,
                    'apprs' => $apprs,
                    'periodePrev3' => $periodePrev3,
                    'periodePrev6' => $periodePrev6,
                    'periodePrev12' => $periodePrev12,
                    'periodeNext3' => $periodeNext3,
                    'periodeNext6' => $periodeNext6,
                    'periodeNext12' => $periodeNext12
                ]);
            } elseif ($idModules[0] != null) {
                return response()->json([
                    'etps' => $etps,
                    'fonctions' => $fonctions,
                    'status' => $status,
                    'villes' => $villes,
                    'modalites' => $modalites,
                    'periodes' => $idPeriodes,
                    'apprs' => $apprs,
                    'periodePrev3' => $periodePrev3,
                    'periodePrev6' => $periodePrev6,
                    'periodePrev12' => $periodePrev12,
                    'periodeNext3' => $periodeNext3,
                    'periodeNext6' => $periodeNext6,
                    'periodeNext12' => $periodeNext12
                ]);
            } elseif ($idStatus[0] != null) {
                return response()->json([
                    'etps' => $etps,
                    'fonctions' => $fonctions,
                    'villes' => $villes,
                    'modalites' => $modalites,
                    'modules' => $modules,
                    'periodes' => $idPeriodes,
                    'apprs' => $apprs,
                    'periodePrev3' => $periodePrev3,
                    'periodePrev6' => $periodePrev6,
                    'periodePrev12' => $periodePrev12,
                    'periodeNext3' => $periodeNext3,
                    'periodeNext6' => $periodeNext6,
                    'periodeNext12' => $periodeNext12
                ]);
            } elseif ($idModalites[0] != null) {
                return response()->json([
                    'etps' => $etps,
                    'fonctions' => $fonctions,
                    'status' => $status,
                    'villes' => $villes,
                    'modules' => $modules,
                    'periodes' => $idPeriodes,
                    'apprs' => $apprs,
                    'periodePrev3' => $periodePrev3,
                    'periodePrev6' => $periodePrev6,
                    'periodePrev12' => $periodePrev12,
                    'periodeNext3' => $periodeNext3,
                    'periodeNext6' => $periodeNext6,
                    'periodeNext12' => $periodeNext12
                ]);
            } elseif ($idVilles[0] != null) {
                return response()->json([
                    'etps' => $etps,
                    'fonctions' => $fonctions,
                    'status' => $status,
                    'modalites' => $modalites,
                    'modules' => $modules,
                    'periodes' => $idPeriodes,
                    'apprs' => $apprs,
                    'periodePrev3' => $periodePrev3,
                    'periodePrev6' => $periodePrev6,
                    'periodePrev12' => $periodePrev12,
                    'periodeNext3' => $periodeNext3,
                    'periodeNext6' => $periodeNext6,
                    'periodeNext12' => $periodeNext12
                ]);
            } elseif ($idPeriodes != null) {
                return response()->json([
                    'etps' => $etps,
                    'fonctions' => $fonctions,
                    'status' => $status,
                    'villes' => $villes,
                    'modalites' => $modalites,
                    'modules' => $modules,
                    'apprs' => $apprs
                ]);
            } else {
                return response()->json([
                    'etps' => $etps,
                    'fonctions' => $fonctions,
                    'status' => $status,
                    'villes' => $villes,
                    'modalites' => $modalites,
                    'modules' => $modules,
                    'periodes' => $idPeriodes,
                    'apprs' => $apprs,
                    'periodePrev3' => $periodePrev3,
                    'periodePrev6' => $periodePrev6,
                    'periodePrev12' => $periodePrev12,
                    'periodeNext3' => $periodeNext3,
                    'periodeNext6' => $periodeNext6,
                    'periodeNext12' => $periodeNext12
                ]);
            }
        }
    }

    public function filterItem(Request $req)
    {
        $idEtps = explode(',', Customer::idCustomer());
        $idFonctions = explode(',', $req->idFonction);
        $idPeriodes = $req->idPeriode;
        $idModules = explode(',', $req->idModule);
        $idVilles = explode(',', $req->idVille);
        $idModalites = explode(',', $req->idModalite);
        $idStatus = explode(',', $req->idStatut);

        $checkEtpGrp = DB::table('etp_groupes')->where('idEntreprise', Customer::idCustomer())->exists();

        //dd($checkEtpGrp);
        if ($checkEtpGrp) {

            $query = DB::table('v_union_emp_grps')
                ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_initial_name', 'emp_photo', 'emp_matricule', 'emp_phone', 'emp_email', 'emp_fonction', 'idEtp', 'etp_name', 'user_is_in_service')
                ->where('idEntrepriseParent', Customer::idCustomer());
        } else {
            $query = DB::table('v_apprenant_etp_alls')
                ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_initial_name', 'emp_photo', 'emp_matricule', 'emp_phone', 'emp_email', 'emp_fonction', 'idEtp', 'etp_name', 'user_is_in_service')
                ->where('idEtp', Customer::idCustomer());
            // if ($checkEtpGrp) {

            //     $query = DB::table('v_apprenant_etp_all_groups')
            //         ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_initial_name', 'emp_photo', 'emp_matricule', 'emp_phone', 'emp_email', 'emp_fonction', 'idEtp', 'etp_name')
            //         ->where('idEntrepriseParent', Customer::idCustomer());
            // } else { //ETPS FILLES...
            //     $query = DB::table('v_apprenant_etp_alls')
            //         ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_initial_name', 'emp_photo', 'emp_matricule', 'emp_phone', 'emp_email', 'emp_fonction', 'idEtp', 'etp_name')
            //         ->where('idEtp', Customer::idCustomer());
            // }
        }
        if ($idEtps[0] != null) {
            $query->whereIn('idEtp', $idEtps);

            if ($idFonctions[0] != null) {
                $query->whereIn('idFonction', $idFonctions);
            }
            if ($idPeriodes != null) {
                $query->where('p_id_periode', $idPeriodes);
            }
            if ($idModules[0] != null) {
                $query->whereIn('idModule', $idModules);
            }
            if ($idVilles[0] != null) {
                $query->whereIn('project_id_ville', $idVilles);
            }
            if ($idModalites[0] != null) {
                $query->whereIn('project_modality', $idModalites);
            }
            if ($idStatus[0] != null) {
                $query->whereIn('project_status', $idStatus);
            }
        }

        if ($idFonctions[0] != null) {
            $query->whereIn('idFonction', $idFonctions);

            if ($idEtps[0] != null) {
                $query->whereIn('idEtp', $idEtps);
            }
            if ($idPeriodes != null) {
                $query->where('p_id_periode', $idPeriodes);
            }
            if ($idModules[0] != null) {
                $query->whereIn('idModule', $idModules);
            }
            if ($idVilles[0] != null) {
                $query->whereIn('project_id_ville', $idVilles);
            }
            if ($idModalites[0] != null) {
                $query->whereIn('project_modality', $idModalites);
            }
            if ($idStatus[0] != null) {
                $query->whereIn('project_status', $idStatus);
            }
        }

        if ($idPeriodes != null) {
            $query->where('p_id_periode', $idPeriodes);

            if ($idEtps[0] != null) {
                $query->whereIn('idEtp', $idEtps);
            }
            if ($idFonctions[0] != null) {
                $query->whereIn('idFonction', $idFonctions);
            }
            if ($idModules[0] != null) {
                $query->whereIn('idModule', $idModules);
            }
            if ($idVilles[0] != null) {
                $query->whereIn('project_id_ville', $idVilles);
            }
            if ($idModalites[0] != null) {
                $query->whereIn('project_modality', $idModalites);
            }
            if ($idStatus[0] != null) {
                $query->whereIn('project_status', $idStatus);
            }
        }

        if ($idModules[0] != null) {
            $query->whereIn('idModule', $idModules);

            if ($idEtps[0] != null) {
                $query->whereIn('idEtp', $idEtps);
            }
            if ($idFonctions[0] != null) {
                $query->whereIn('idFonction', $idFonctions);
            }
            if ($idPeriodes != null) {
                $query->where('p_id_periode', $idPeriodes);
            }
            if ($idVilles[0] != null) {
                $query->whereIn('project_id_ville', $idVilles);
            }
            if ($idModalites[0] != null) {
                $query->whereIn('project_modality', $idModalites);
            }
            if ($idStatus[0] != null) {
                $query->whereIn('project_status', $idStatus);
            }
        }

        if ($idVilles[0] != null) {
            $query->whereIn('project_id_ville', $idVilles);

            if ($idEtps[0] != null) {
                $query->whereIn('idEtp', $idEtps);
            }
            if ($idFonctions[0] != null) {
                $query->whereIn('idFonction', $idFonctions);
            }
            if ($idPeriodes != null) {
                $query->where('p_id_periode', $idPeriodes);
            }
            if ($idModules[0] != null) {
                $query->whereIn('idModule', $idModules);
            }
            if ($idModalites[0] != null) {
                $query->whereIn('project_modality', $idModalites);
            }
            if ($idStatus[0] != null) {
                $query->whereIn('project_status', $idStatus);
            }
        }

        if ($idModalites[0] != null) {
            $query->whereIn('project_modality', $idModalites);

            if ($idEtps[0] != null) {
                $query->whereIn('idEtp', $idEtps);
            }
            if ($idFonctions[0] != null) {
                $query->whereIn('idFonction', $idFonctions);
            }
            if ($idPeriodes != null) {
                $query->where('p_id_periode', $idPeriodes);
            }
            if ($idModules[0] != null) {
                $query->whereIn('idModule', $idModules);
            }
            if ($idVilles[0] != null) {
                $query->whereIn('project_id_ville', $idVilles);
            }
            if ($idStatus[0] != null) {
                $query->whereIn('project_status', $idStatus);
            }
        }

        if ($idStatus[0] != null) {
            $query->whereIn('project_status', $idStatus);

            if ($idEtps[0] != null) {
                $query->whereIn('idEtp', $idEtps);
            }
            if ($idFonctions[0] != null) {
                $query->whereIn('idFonction', $idFonctions);
            }
            if ($idPeriodes != null) {
                $query->where('p_id_periode', $idPeriodes);
            }
            if ($idModules[0] != null) {
                $query->whereIn('idModule', $idModules);
            }
            if ($idVilles[0] != null) {
                $query->whereIn('project_id_ville', $idVilles);
            }
            if ($idModalites[0] != null) {
                $query->whereIn('project_modality', $idModalites);
            }
        }

        $query->groupBy('idEmploye', 'emp_name', 'emp_firstname', 'emp_initial_name', 'emp_photo', 'emp_matricule', 'emp_phone', 'emp_email', 'emp_fonction', 'idEtp', 'etp_name');

        $apprs = $query->orderBy('idEmploye', 'DESC')->limit(8)->get();

        return response()->json(['apprs' => $apprs]);
    }

    public function updateService(Request $req, $idEmploye)
    {
        $req->validate([
            'user_service' => 'required|integer'
        ]);

        $roleUser = DB::table('role_users')->where('user_id', $idEmploye);

        if ($roleUser->exists()) {
            $roleUser->update(['user_is_in_service' => $req->user_service]);
            return response()->json([
                'status' => 200,
                'message' => 'Modifié avec succès'
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Utilisateur introuvable !'
            ], 404);
        }
    }

    public function addEmpExcel(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'data' => 'required|file|mimes:xls,xlsx',
        ]);

        if ($validate->fails()) {
            return response()->json(['error' => $validate->messages()]);
        } else {

            $idEntreprise = Auth::user()->id;
            $file = $request->file('data');
            $data = Excel::toArray(new ExcelApprenants, $file);
            $fonction = DB::table('fonctions')->select('idFonction')->where('idCustomer', Customer::idCustomer())->first();

            try {
                foreach ($data[0] as $row) {
                    if (!empty($row['nom']) || !empty($row['matricule'])) {
                        DB::beginTransaction();

                        $allUser = DB::table('users')
                            ->select('email', 'matricule')
                            ->get();

                        $verification = true;

                        foreach ($allUser as $userVerif) {
                            if ($userVerif->email == $row['e_mail'] && $userVerif->email != null) {
                                $verification = false;
                            }
                            if ($userVerif->matricule == $row['matricule'] && $userVerif->matricule != null) {
                                $verification = false;
                            }
                        };
                        if ($verification == true) {
                            $user = new User();
                            $user->matricule = $row['matricule'];
                            $user->name = $row['nom'];
                            $user->firstName = $row['prenom'];
                            $user->email = $row['e_mail'];
                            $user->phone = $row['telephone'];
                            $user->password =  Hash::make('0000@#');
                            $user->save();


                            $emp = new Employe();
                            $emp->idEmploye = $user->id;
                            $emp->idSexe = 1;
                            $emp->idNiveau = 6;
                            $emp->idCustomer = $idEntreprise;
                            $emp->idFonction = $fonction->idFonction;
                            $emp->save();

                            RoleUser::create([
                                'role_id'  => 4,
                                'user_id'  => $user->id,
                                'isActive' => 0,
                                'hasRole' => 1
                            ]);
                        }
                        DB::commit();
                    }
                }

                return response()->json(['success' => 'Employé ajouté avec succès !']);
            } catch (Exception $e) {
                return response()->json(['error' => 'Erreur inconnue']);
            }
        }
    }

    public function getEtpType()
    {
        // Récupérer le type d'entreprise
        $etp = DB::table('entreprises')
            ->select('idCustomer', 'idTypeEtp')
            ->where('idCustomer', Customer::idCustomer())
            ->first();

        if (!$etp) {
            return response(['error' => 'Entreprise introuvable'], 404);
        }

        $isGroup = ($etp->idTypeEtp == 2);
        $childEnterprises = [];

        // Si c'est un groupe, charger les entreprises enfants ET l'entreprise parente
        if ($isGroup) {
            // Récupérer les entreprises enfants
            $childEnterprises = DB::table('etp_groupeds as egp')
                ->select('egp.idEntreprise as idCustomer', 'cst.customerName')
                ->join('customers as cst', 'egp.idEntreprise', 'cst.idCustomer')
                ->where('egp.idEntrepriseParent', Customer::idCustomer())
                ->orderBy('cst.customerName', 'asc')
                ->get()
                ->toArray();

            // Ajouter l'entreprise parente à la liste
            $parentEnterprise = DB::table('customers')
                ->select('idCustomer', 'customerName')
                ->where('idCustomer', Customer::idCustomer())
                ->first();

            if ($parentEnterprise) {
                // Convertir l'objet en tableau et l'ajouter au début
                array_unshift($childEnterprises, (array)$parentEnterprise);
            }
        } else {
            // Si ce n'est pas un groupe, retourner seulement l'entreprise connectée
            $parentEnterprise = DB::table('customers')
                ->select('idCustomer', 'customerName')
                ->where('idCustomer', Customer::idCustomer())
                ->first();

            if ($parentEnterprise) {
                $childEnterprises = [(array)$parentEnterprise];
            }
        }

        return response([
            'status' => 200,
            'isGroup' => $isGroup,
            'childEnterprises' => $childEnterprises
        ]);
    }

    public function destroy($id)
    {
        $employe = DB::table('users')->where('id', $id);

        if ($employe->exists()) {
            try {
                DB::beginTransaction();

                $assigned_project = DB::table('detail_apprenants')
                    ->where('idEmploye', $id)
                    ->exists();
                if (!$assigned_project) {
                    DB::table('c_emps')->where('idEmploye', $id)->delete();
                    DB::table('employes')->where('idEmploye', $id)->delete();
                    DB::table('role_users')->where('user_id', $id)->delete();



                    DB::commit();
                    return response()->json([
                        'status' => 200,
                        'message' => 'Employé supprimé avec succès'
                    ], 200);
                } else {
                    DB::rollBack();
                    return response()->json([
                        'status' => 400,
                        'message' => 'Suppression impossible : l\'employé a des projets assignés.'
                    ], 400);
                }
            } catch (Exception $e) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Suppression impossible !'
                ], 400);
            }
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Employé introuvable !'
            ], 404);
        }
    }
}
