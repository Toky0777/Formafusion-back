<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Employe;
use App\Models\RoleUser;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ApprenantEtpController extends Controller
{
    // Liste de tous les apprenants
    public function getApprenantProjets()
    {
        $checkEtp = DB::table('entreprises')->select('idCustomer', 'idTypeEtp')->where('idCustomer', Customer::idCustomer())->first();

        if ($checkEtp) {
            if ($checkEtp->idTypeEtp == 1 || $checkEtp->idTypeEtp == 3) {
                $apprs = DB::table('v_apprenant_etp')
                    ->select('idEmploye', 'emp_initial_name', 'emp_name', 'emp_firstname', 'emp_fonction', 'emp_email', 'emp_photo', 'emp_matricule', 'idEtp', 'etp_name')
                    ->where('role_id', 4)
                    ->where('idEtp', Customer::idCustomer())
                    ->where('user_is_in_service', 1)
                    ->orderBy('emp_name', 'asc')
                    ->get();

                $etps = DB::table('entreprises AS et')
                    ->select('et.idCustomer AS idEtp', 'cst.customerName AS etp_name')
                    ->join('customers AS cst', 'et.idCustomer', 'cst.idCustomer')
                    ->where('et.idCustomer', Customer::idCustomer())
                    ->get();
            } elseif ($checkEtp->idTypeEtp == 2) {
                $apprs = DB::table('v_list_emp_grps')
                    ->select('idEmploye', 'emp_initial_name', 'emp_name', 'emp_firstname', 'emp_email', 'emp_photo', 'emp_matricule', 'idEntrepriseParent', 'etp_name', 'etp_name_parent', 'idEntreprise AS idEtp', 'emp_fonction')
                    ->where('role_id', 4)
                    ->where('idEntrepriseParent', Customer::idCustomer())
                    ->where('user_is_in_service', 1)
                    ->orderBy('emp_name', 'asc')
                    ->get();

                $etps = DB::table('etp_groupeds AS egd')
                    ->select('egd.idEntreprise AS idEtp', 'cst.customerName AS etp_name')
                    ->join('customers AS cst', 'egd.idEntreprise', 'cst.idCustomer')
                    ->where('egd.idEntrepriseParent', Customer::idCustomer())
                    ->get();
            } else {
                return response(['error' => 'Erreur inconnue !']);
            }

            return response()->json([
                'status' => 200,
                'apprenants'  => $apprs,
                'entreprise' => $etps
            ]);
        } else {
            return response(['error' => 'Entreprise introuvable !']);
        }
    }

    public function getApprenantAdded($idProjet)
    {
        $apprs = DB::table('v_list_apprenants as L')
            ->leftJoin('eval_apprenant as E', function ($join) use ($idProjet) {
                $join->on('E.idEmploye', '=', 'L.idEmploye')
                    ->where('E.idProjet', '=', $idProjet);
            })
            ->select(
                'L.idEmploye',
                'emp_initial_name',
                'emp_name',
                'emp_firstname',
                'emp_fonction',
                'emp_email',
                'emp_photo',
                'emp_matricule',
                'etp_name',
                'idEtp',
                'E.avant as avant',
                'E.apres as apres'
            )
            ->where('L.idProjet', $idProjet)
            ->orderBy('emp_name', 'asc')
            ->get();

        $getEtps = DB::table('detail_apprenants AS da')
            ->select('da.idProjet', 'da.idEmploye', 'cst.idCustomer AS idEtp', 'cst.customerName AS etp_name', 'cst.logo AS etp_logo')
            ->join('employes AS emp', 'da.idEmploye', 'emp.idEmploye')
            ->join('customers AS cst', 'emp.idCustomer', 'cst.idCustomer')
            ->where('da.idProjet', $idProjet)
            ->groupBy('cst.idCustomer', 'cst.customerName')
            ->orderBy('etp_name', 'asc')
            ->get();

        $getSeance = DB::table('v_emargement_appr')
            ->select('idSeance', 'idProjet', 'heureDebut', 'heureFin', 'dateSeance', 'isPresent', 'idEmploye')
            ->where('idProjet', $idProjet)
            ->whereDate('dateSeance', '<=', Carbon::now()->toDateString()) // Ajoute la condition pour la date
            ->groupBy('idSeance')
            ->get();

        $getPresence = DB::table('v_emargement_appr')
            ->select('idSeance', 'dateSeance', 'idProjet', 'isPresent', 'idEmploye')
            ->where('idProjet', $idProjet)
            ->whereDate('dateSeance', '<=', Carbon::now()->toDateString()) // Ajoute la condition pour la date
            ->get();

        $countDate = DB::table('v_seances')
            ->select('idProjet', 'idSeance', DB::raw('COUNT(*) as count'), 'dateSeance')
            ->where('idProjet', $idProjet)
            ->whereDate('dateSeance', '<=', Carbon::now()->toDateString()) // Ajoute la condition pour la date
            ->groupBy('dateSeance')
            ->get();


        $getAppr = DB::table('v_emargement_appr')
            ->select('idProjet', 'idEmploye', 'name', 'firstName', 'photo')
            ->where('idProjet', $idProjet)
            ->groupBy('idEmploye')
            ->get();


        $getEmargement = DB::table('emargements')
            ->select('idProjet', 'idSeance', 'isPresent')
            ->where('idProjet', $idProjet)
            ->groupBy('idSeance')
            ->get();

        $present = DB::table('emargements')
            ->select('idProjet', 'idSeance', 'isPresent')
            ->where('idProjet', $idProjet)
            ->where('isPresent', 3)
            ->get();

        $partiel = DB::table('emargements')
            ->select('idProjet', 'idSeance', 'isPresent')
            ->where('idProjet', $idProjet)
            ->where('isPresent', 2)
            ->get();

        $absent = DB::table('emargements')
            ->select('idProjet', 'idSeance', 'isPresent')
            ->where('idProjet', $idProjet)
            ->where('isPresent', 1)
            ->get();

        $nonDefini = DB::table('emargements')
            ->select('idProjet', 'idSeance', 'isPresent')
            ->where('idProjet', $idProjet)
            ->where('isPresent', 0)
            ->get();

        $countAppr = count($getAppr);

        $countPresent = count($present);
        $countPartiel = count($partiel);
        $countAbsent = count($absent) + count($nonDefini);

        $countEmargement = count($getEmargement);
        $divide = $countAppr * $countEmargement;

        if ($divide > 0) {
            $percentPresent = number_format(($countPresent / $divide) * 100, 1, ',', ' ');
            $percentPartiel = number_format(($countPartiel / $divide) * 100, 1, ',', ' ');
            $percentAbsent = number_format(($countAbsent / $divide) * 100, 1, ',', ' ');
        } else {
            $percentPresent = 0;
            $percentPartiel = 0;
            $percentAbsent = 0;
        }

        $getIdAppr = DB::table('v_emargement_appr')
            ->select('idProjet', 'idEmploye', 'idSeance', 'name', 'firstName', 'photo')
            ->where('idProjet', $idProjet)
            ->get();

        return response()->json([
            'countDate' => $countDate,
            'countAppr' => $countAppr,
            'countEmargement' => $countEmargement,
            'percentPresent' => $percentPresent . '%',
            'percentPartiel' => $percentPartiel . '%',
            'percentAbsent' => $percentAbsent . '%',
            'apprenants' => $apprs,
            'getEtps' => $getEtps,
            'getSeance' => $getSeance,
            'getPresence' => $getPresence,
            'getAppr' => $getAppr,
            'getIdAppr' => $getIdAppr
        ]);
    }

    public function getAllApprenantInter($idProjet)
    {
        $apprenantInter = DB::table('v_list_apprenant_inter_added')
            ->select('*')
            ->where('idProjet', $idProjet)
            ->get();

        if(count($apprenantInter) <= 0 ){
            return response()->json([
                'status' => 404,
                'message' => 'Aucun résultat !'
            ], 404);
        }else{
            return response()->json([
                'status' => 200,
                'apprenants' => $apprenantInter
            ]);
        }
    }

    public function addApprenant($idProjet, $idApprenant)
    {
        $checkAppr = DB::table('apprenants')->where('idEmploye', $idApprenant)->get();
        $check = DB::table('detail_apprenants')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->get();

        if (count($checkAppr) < 1 && count($check) < 1) {
            try {
                DB::beginTransaction();
                DB::table('apprenants')->insert([
                    'idEmploye' => $idApprenant
                ]);

                DB::table('detail_apprenants')->insert([
                    'idProjet' => $idProjet,
                    'idEmploye' => $idApprenant
                ]);
                DB::commit();
                return response()->json(['success' => 'Apprenant ajouté avec succès']);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json(['error' => 'Erreur inconnue !']);
            }
        } elseif (count($checkAppr) >= 1 && count($check) < 1) {
            DB::table('detail_apprenants')->insert([
                'idProjet' => $idProjet,
                'idEmploye' => $idApprenant
            ]);

            return response()->json(['success' => 'Apprenant ajouté avec succès']);
        } elseif (count($checkAppr) >= 1 && count($check) >= 1) {
            return response()->json(['error' => 'Employée déjas inscrit à la session']);
        }
    }

    public function removeApprenant($idProjet, $idApprenant)
    {
        $eval = DB::table('eval_chauds')->select('idProjet')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->get();
        $presence = DB::table('emargements')->select('idProjet')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->get();

        $checkEval = count($eval);
        $checkPresence = count($presence);

        try {
            if ($checkEval > 0 && $checkPresence > 0) {
                $delete = DB::transaction(function () use ($idProjet, $idApprenant) {
                    DB::table('detail_apprenants')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->delete();
                    DB::table('eval_chauds')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->delete();
                    DB::table('emargements')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->delete();
                });
            } elseif ($checkEval > 0) {
                $delete = DB::transaction(function () use ($idProjet, $idApprenant) {
                    DB::table('detail_apprenants')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->delete();
                    DB::table('eval_chauds')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->delete();
                });
            } elseif ($checkPresence > 0) {
                $delete = DB::transaction(function () use ($idProjet, $idApprenant) {
                    DB::table('detail_apprenants')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->delete();
                    DB::table('emargements')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->delete();
                });
            } else {
                $delete = DB::table('detail_apprenants')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->delete();
            }
            return response()->json(['success' => 'Succès']);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Erreur inconnue !']);
        }
    }

    public function getPresenceUnique($idProjet, $idEmploye)
    {
        $checkEmp = DB::table('emargements')->select('idProjet', 'idEmploye', 'isPresent')->where('idProjet', $idProjet)->where('idEmploye', $idEmploye)->get();

        $sum = DB::table('emargements')->select(DB::raw('SUM(isPresent) AS somme'))->where('idProjet', $idProjet)->where('idEmploye', $idEmploye)->first();

        $intSum = (int)$sum->somme;

        $countCheckEmp = count($checkEmp);

        if ($countCheckEmp > 0) {
            $present = $countCheckEmp * 3;
            $absent = $countCheckEmp;

            if ($intSum === $present) {
                return response()->json(['checking' => 3]);
            } elseif ($intSum < $present && $intSum !== $absent) {
                return response()->json(['checking' => 2]);
            } elseif ($intSum === $absent) {
                return response()->json(['checking' => 1]);
            }
        } else {
            return response()->json(['checking' => 0]);
        }
    }











































    public function index()
    {
        $apprs = DB::table('v_apprenant_etp')
            ->select('idEmploye', 'emp_initial_name', 'emp_fonction', 'emp_photo', 'emp_name', 'emp_firstname', 'emp_matricule', 'emp_email', 'etp_name', 'emp_phone', 'emp_is_active')
            ->where(function ($query) {
                $query->where('idEtp', Customer::idCustomer())
                    // ->where('id_cfp', $this->idCfp())
                    ->where('role_id', 4);
            })
            ->groupBy('idEmploye', 'emp_initial_name', 'emp_fonction', 'emp_photo', 'emp_name', 'emp_firstname', 'emp_matricule', 'emp_email', 'etp_name', 'emp_phone', 'emp_is_active')
            ->orderBy('emp_name', 'asc')
            ->paginate(10);

        $countAppr = DB::table('v_apprenant_etp')
            ->select('idEmploye', 'emp_initial_name', 'emp_fonction', 'emp_photo', 'emp_name', 'emp_firstname', 'emp_matricule', 'emp_email', 'etp_name', 'emp_phone', 'emp_is_active')
            ->where(function ($query) {
                $query->where('idEtp', Customer::idCustomer())
                    //->where('id_cfp', $this->idCfp())
                    ->where('role_id', 4);
            })
            ->groupBy('idEmploye', 'emp_initial_name', 'emp_fonction', 'emp_photo', 'emp_name', 'emp_firstname', 'emp_matricule', 'emp_email', 'etp_name', 'emp_phone', 'emp_is_active')
            ->get();

        $countApprs = count($countAppr);

        return view('ETP.stagiaireEtps.newindex', compact('apprs', 'countApprs'));
    }

    public function addEmp(Request $req)
    {
        $validate = Validator::make($req->all(), [
            'emp_matricule' => 'required|min:2|max:200|unique:users,matricule',
            'idEntreprise' => 'required|exists:customers,idCustomer',
            'emp_name' => 'required|min:2|max:200',
            'emp_fonction' => 'required|min:2|max:200',
        ]);

        if ($validate->fails()) {
            return response()->json(['error' => $validate->messages()]);
        } else {
            try {
                DB::beginTransaction();

                $check = DB::table('fonctions')
                    ->select('idFonction', 'fonction')
                    ->where('fonction', 'like', $req->emp_fonction)
                    ->where('idCustomer', $this->idCfp())
                    ->get();

                if (count($check) == 0) {
                    $idFonction = DB::table('fonctions')->insertGetId([
                        'fonction' => $req->emp_fonction,
                        'idCustomer' => $this->idCfp()
                    ]);

                    $user = new User();
                    $user->matricule = $req->emp_matricule;
                    $user->name = $req->emp_name;
                    $user->firstName = $req->emp_firstname;
                    $user->email = $req->emp_email;
                    $user->phone = $req->emp_phone;
                    $user->idFonction = $idFonction;
                    $user->fonction = $req->emp_fonction;
                    $user->password = Hash::make('0000@#');
                    $user->save();
                } else {
                    $user = new User();
                    $user->matricule = $req->emp_matricule;
                    $user->name = $req->emp_name;
                    $user->firstName = $req->emp_firstname;
                    $user->email = $req->emp_email;
                    $user->phone = $req->emp_phone;
                    $user->idFonction = $check[0]->idFonction;
                    $user->fonction = $req->emp_fonction;
                    $user->password = Hash::make('0000@#');
                    $user->save();
                }

                $emp = new Employe();
                $emp->idEmploye = $user->id;
                $emp->idSexe = 1;
                $emp->idNiveau = 6;
                $emp->idCustomer = $req->idEntreprise;
                $emp->id_cfp = $this->idCfp();
                $emp->save();

                RoleUser::create([
                    'role_id'  => 4,
                    'user_id'  => $user->id,
                    'isActive' => 0,
                    'hasRole' => 1
                ]);

                DB::commit();
                return response()->json(['success' => 'Apprenant ajouté avec succès !']);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json(['error' => 'Erreur inconnue']);
            }
        }
    }

    public function getApprenants()
    {
        $apprs = DB::table('v_apprenant_etp_alls')
            ->select('idEmploye', 'emp_initial_name', 'emp_photo', 'emp_name', 'emp_firstname', 'emp_matricule', 'emp_email', 'etp_name', 'emp_phone', 'emp_is_active')
            ->where('idCfp', $this->idCfp())
            ->where('id_cfp', $this->idCfp())
            ->where('role_id', 4)
            ->groupBy('idEmploye', 'emp_initial_name', 'emp_photo', 'emp_name', 'emp_firstname', 'emp_matricule', 'emp_email', 'etp_name', 'emp_phone', 'emp_is_active')
            ->orderBy('emp_name', 'asc')
            ->get();

        return response()->json(['apprs' => $apprs]);
    }

    public function edit($idApprenant)
    {
        $appr = DB::table('v_apprenant_etp_alls')
            ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_email', 'emp_phone', 'emp_matricule', 'emp_fonction', 'user_addr_lot', 'user_addr_quartier', 'user_addr_code_postal', 'emp_initial_name', 'emp_photo', 'etp_name')
            ->where('idEmploye', $idApprenant)
            ->first();

        $etps = DB::table('v_collaboration_cfp_etps')
            ->select('idEtp', 'etp_name')
            ->groupBy('idEtp', 'etp_name')
            ->where('idCfp', $this->idCfp())
            ->get();

        $villes = DB::table('villes')
            ->select('idVille', 'ville')
            ->orderBy('ville', 'asc')
            ->get();

        return response()->json([
            'appr' => $appr,
            'etps' => $etps,
            'villes' => $villes
        ]);
    }

    public function update(Request $req, $idApprenant)
    {
        $validate = Validator::make($req->all(), [
            'idEntreprise' => 'required',
            'emp_matricule' => 'required|min:2|max:100',
            'emp_name' => 'required|min:2|max:100',
            'idVille' => 'required|exists:villes,idVille',
            'emp_fonction' => 'required|min:2|max:200'
        ]);

        if ($validate->fails()) {
            return response()->json(['error' => $validate->messages()]);
        } else {
            try {
                DB::beginTransaction();

                $check = DB::table('fonctions')
                    ->select('fonction')
                    ->where('fonction', 'like', $req->emp_fonction)
                    ->where('idCustomer', $this->idCfp())
                    ->count();

                if ($check == 0) {
                    $idFonction = DB::table('fonctions')->insertGetId([
                        'fonction' => $req->emp_fonction,
                        'idCustomer' => $this->idCfp()
                    ]);

                    DB::table('users')->where('id', $idApprenant)->update([
                        'name' => $req->emp_name,
                        'firstName' => $req->emp_firstname,
                        'matricule' => $req->emp_matricule,
                        'email' => $req->emp_email,
                        'phone' => $req->emp_phone,
                        'idFonction' => $idFonction,
                        'fonction' => $req->emp_fonction,
                        'user_addr_lot' => $req->emp_lot,
                        'user_addr_quartier' => $req->emp_qrt,
                        'user_addr_code_postal' => $req->emp_cp,
                        'idVille' => $req->idVille
                    ]);
                } else {
                    DB::table('users')->where('id', $idApprenant)->update([
                        'name' => $req->emp_name,
                        'firstName' => $req->emp_firstname,
                        'matricule' => $req->emp_matricule,
                        'email' => $req->emp_email,
                        'phone' => $req->emp_phone,
                        'fonction' => $req->emp_fonction,
                        'user_addr_lot' => $req->emp_lot,
                        'user_addr_quartier' => $req->emp_qrt,
                        'user_addr_code_postal' => $req->emp_cp,
                        'idVille' => $req->idVille
                    ]);
                }

                Db::table('employes')->where('idEmploye', $idApprenant)->update([
                    'idCustomer' => $req->idEntreprise
                ]);

                DB::commit();
                return response()->json(['success' => 'Succès']);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json(['error' => 'Erreur inconnue !']);
            }
        }
    }

    public function searchName(string $name)
    {
        $apprenants = DB::table('v_apprenant_etp_alls')
            ->select('idEmploye', 'emp_initial_name', 'emp_photo', 'emp_name', 'emp_firstname', 'emp_matricule', 'emp_email', 'etp_name', 'emp_phone', 'emp_is_active')
            ->where('idCfp', $this->idCfp())
            ->where('id_cfp', $this->idCfp())
            ->where('role_id', '=', 4)
            ->where(function ($query) use ($name) {
                $query->where('emp_name', 'LIKE', '%' . $name . '%')
                    ->orWhere('emp_firstname', 'LIKE', '%' . $name . '%');
            })
            ->groupBy('idEmploye', 'emp_initial_name', 'emp_photo', 'emp_name', 'emp_firstname', 'emp_matricule', 'emp_email', 'etp_name', 'emp_phone', 'emp_is_active')
            ->get();

        return response()->json(['apprenants' => $apprenants]);
    }

    public function getEtpFilter()
    {
        $etps = DB::table('v_apprenant_etp_alls')
            ->select('idEtp', 'etp_name')
            ->where('idCfp', $this->idCfp())
            ->where('id_cfp', $this->idCfp())
            ->groupBy('idEtp', 'etp_name')
            ->get();

        return response()->json(['etps' => $etps]);
    }

    public function getEmpFiltered($idEtp)
    {
        $apprenants = DB::table('v_apprenant_etp_alls')
            ->select('idEmploye', 'emp_initial_name', 'emp_photo', 'emp_name', 'emp_firstname', 'emp_matricule', 'emp_email', 'etp_name', 'emp_phone', 'emp_is_active')
            ->where('idCfp', $this->idCfp())
            ->where('id_cfp', $this->idCfp())
            ->where('role_id', 4)
            ->where('idEtp', $idEtp)
            ->groupBy('idEmploye', 'emp_initial_name', 'emp_photo', 'emp_name', 'emp_firstname', 'emp_matricule', 'emp_email', 'etp_name', 'emp_phone', 'emp_is_active')
            ->get();

        return response()->json(['apprenants' => $apprenants]);
    }

    // Filtres
    public function getDropdownItem()
    {
        $etps = DB::table('v_apprenant_etp_all_filters')
            ->select('idEtp', 'etp_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
            ->where('idCfp', $this->idCfp())
            ->orderBy('etp_name', 'asc')
            ->groupBy('idEtp', 'etp_name')
            ->get();

        $fonctions = DB::table('v_apprenant_etp_all_filters')
            ->select('idFonction', 'emp_fonction', DB::raw('COUNT(idEmploye) AS emp_nb'))
            ->where('idCfp', $this->idCfp())
            ->orderBy('emp_fonction', 'asc')
            ->groupBy('idFonction', 'emp_fonction')
            ->get();

        $villes = DB::table('v_periodes')
            ->select('project_id_ville', 'project_ville as ville', DB::raw('COUNT(idEmploye) AS emp_nb'))
            ->where('idCfp', $this->idCfp())
            ->orderBy('project_ville', 'asc')
            ->groupBy('project_id_ville', 'project_ville')
            ->get();

        $status = DB::table('v_apprenant_etp_alls')
            ->select('project_status', DB::raw('COUNT(idEmploye) AS emp_nb'))
            ->where('idCfp', $this->idCfp())
            ->where('project_status', '!=', 'null')
            ->orderBy('project_status', 'asc')
            ->groupBy('project_status')
            ->get();

        $modalites = DB::table('v_apprenant_etp_alls')
            ->select('project_modality', DB::raw('COUNT(idEmploye) AS emp_nb'))
            ->where('idCfp', $this->idCfp())
            ->where('project_modality', '!=', 'null')
            ->orderBy('project_modality', 'asc')
            ->groupBy('project_modality')
            ->get();

        $modules = DB::table('v_apprenant_etp_alls')
            ->select('idModule', 'module_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
            ->where('idCfp', $this->idCfp())
            ->where('idModule', '!=', 'null')
            ->orderBy('module_name', 'asc')
            ->groupBy('idModule', 'module_name')
            ->get();

        $periodePrev3 = DB::table('v_apprenant_etp_alls')
            ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
            ->where('idCfp', $this->idCfp())
            ->where('id_cfp', $this->idCfp())
            ->where('dateDebut', '!=', 'null')
            ->where('p_id_periode', "prev_3_month")
            ->groupBy('p_id_periode')
            ->first();

        $periodePrev6 = DB::table('v_apprenant_etp_alls')
            ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
            ->where('idCfp', $this->idCfp())
            ->where('id_cfp', $this->idCfp())
            ->where('dateDebut', '!=', 'null')
            ->where('p_id_periode', "prev_6_month")
            ->groupBy('p_id_periode')
            ->first();

        $periodePrev12 = DB::table('v_apprenant_etp_alls')
            ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
            ->where('idCfp', $this->idCfp())
            ->where('id_cfp', $this->idCfp())
            ->where('dateDebut', '!=', 'null')
            ->where('p_id_periode', "prev_12_month")
            ->groupBy('p_id_periode')
            ->first();

        $periodeNext3 = DB::table('v_apprenant_etp_alls')
            ->select('p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
            ->where('idCfp', $this->idCfp())
            ->where('id_cfp', $this->idCfp())
            ->where('dateDebut', '!=', 'null')
            ->where('p_id_periode', "next_3_month")
            ->groupBy('p_id_periode')
            ->first();

        $periodeNext6 = DB::table('v_periodes')
            ->select('p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
            ->where('idCfp', $this->idCfp())
            ->where('p_id_periode', "next_6_month")
            ->groupBy('p_id_periode')
            ->first();

        $periodeNext12 = DB::table('v_apprenant_etp_alls')
            ->select('p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
            ->where('idCfp', $this->idCfp())
            ->where('id_cfp', $this->idCfp())
            ->where('dateDebut', '!=', 'null')
            ->where('p_id_periode', "next_12_month")
            ->groupBy('p_id_periode')
            ->first();

        return response()->json([
            'etps' => $etps,
            'fonctions' => $fonctions,
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

        $query = DB::table('v_apprenant_etp_alls')
            ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_initial_name', 'emp_photo', 'emp_matricule', 'emp_phone', 'emp_email', 'emp_fonction', 'idEtp', 'etp_name')
            ->where('idCfp', $this->idCfp());

        if ($idEtps[0] != null) {
            $query->whereIn('idEtp', $idEtps);

            if ($idFonctions[0] != null) {
                $query->whereIn('idFonction', $idFonctions);
            }

            $fonctions = DB::table('v_periodes')
                ->select('idFonction', 'emp_fonction', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->whereIn('idEtp', $idEtps)
                ->orderBy('emp_fonction', 'asc')
                ->groupBy('idFonction', 'emp_fonction')
                ->get();

            $villes = DB::table('v_periodes')
                ->select('project_id_ville', 'project_ville as ville', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->whereIn('idEtp', $idEtps)
                ->orderBy('project_ville', 'asc')
                ->groupBy('project_id_ville', 'project_ville')
                ->get();

            $status = DB::table('v_apprenant_etp_alls')
                ->select('project_status', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('project_status', '!=', 'null')
                ->whereIn('idEtp', $idEtps)
                ->orderBy('project_status', 'asc')
                ->groupBy('project_status')
                ->get();

            $modalites = DB::table('v_apprenant_etp_alls')
                ->select('project_modality', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('project_modality', '!=', 'null')
                ->whereIn('idEtp', $idEtps)
                ->orderBy('project_modality', 'asc')
                ->groupBy('project_modality')
                ->get();

            $modules = DB::table('v_apprenant_etp_alls')
                ->select('idModule', 'module_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('idModule', '!=', 'null')
                ->whereIn('idEtp', $idEtps)
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();

            $periodePrev3 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "prev_3_month")
                ->whereIn('idEtp', $idEtps)
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "prev_6_month")
                ->whereIn('idEtp', $idEtps)
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev12 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "prev_12_month")
                ->whereIn('idEtp', $idEtps)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext3 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "next_3_month")
                ->whereIn('idEtp', $idEtps)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "next_6_month")
                ->whereIn('idEtp', $idEtps)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext12 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "next_12_month")
                ->whereIn('idEtp', $idEtps)
                ->groupBy('p_id_periode')
                ->first();
        } elseif ($idFonctions[0] != null) {
            $query->whereIn('idFonction', $idFonctions);

            $etps = DB::table('v_periodes')
                ->select('idEtp', 'etp_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->whereIn('idFonction', $idFonctions)
                ->orderBy('etp_name', 'asc')
                ->groupBy('idEtp', 'etp_name')
                ->get();

            $villes = DB::table('v_periodes')
                ->select('project_id_ville', 'project_ville as ville', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->whereIn('idFonction', $idFonctions)
                ->orderBy('project_ville', 'asc')
                ->groupBy('project_id_ville', 'project_ville')
                ->get();

            $status = DB::table('v_apprenant_etp_alls')
                ->select('project_status', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('project_status', '!=', 'null')
                ->whereIn('idFonction', $idFonctions)
                ->orderBy('project_status', 'asc')
                ->groupBy('project_status')
                ->get();

            $modalites = DB::table('v_apprenant_etp_alls')
                ->select('project_modality', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('project_modality', '!=', 'null')
                ->whereIn('idFonction', $idFonctions)
                ->orderBy('project_modality', 'asc')
                ->groupBy('project_modality')
                ->get();

            $modules = DB::table('v_apprenant_etp_alls')
                ->select('idModule', 'module_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('idModule', '!=', 'null')
                ->whereIn('idFonction', $idFonctions)
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();

            $periodePrev3 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "prev_3_month")
                ->whereIn('idFonction', $idFonctions)
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "prev_6_month")
                ->whereIn('idFonction', $idFonctions)
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev12 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "prev_12_month")
                ->whereIn('idFonction', $idFonctions)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext3 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "next_3_month")
                ->whereIn('idFonction', $idFonctions)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "next_6_month")
                ->whereIn('idFonction', $idFonctions)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext12 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "next_12_month")
                ->whereIn('idFonction', $idFonctions)
                ->groupBy('p_id_periode')
                ->first();
        } elseif ($idModules[0] != null) {
            $query->whereIn('idModule', $idModules);

            $etps = DB::table('v_apprenant_etp_alls')
                ->select('idEtp', 'etp_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->whereIn('idModule', $idModules)
                ->orderBy('etp_name', 'asc')
                ->groupBy('idEtp', 'etp_name')
                ->get();

            $fonctions = DB::table('v_apprenant_etp_alls')
                ->select('idFonction', 'emp_fonction', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->whereIn('idModule', $idModules)
                ->orderBy('emp_fonction', 'asc')
                ->groupBy('idFonction', 'emp_fonction')
                ->get();

            $villes = DB::table('v_apprenant_etp_alls')
                ->select('project_id_ville', 'project_ville as ville', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->whereIn('idModule', $idModules)
                ->orderBy('project_ville', 'asc')
                ->groupBy('project_id_ville', 'project_ville')
                ->get();

            $status = DB::table('v_apprenant_etp_alls')
                ->select('project_status', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('project_status', '!=', 'null')
                ->whereIn('idModule', $idModules)
                ->orderBy('project_status', 'asc')
                ->groupBy('project_status')
                ->get();

            $modalites = DB::table('v_apprenant_etp_alls')
                ->select('project_modality', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('project_modality', '!=', 'null')
                ->whereIn('idModule', $idModules)
                ->orderBy('project_modality', 'asc')
                ->groupBy('project_modality')
                ->get();

            $periodePrev3 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "prev_3_month")
                ->whereIn('idModule', $idModules)
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "prev_6_month")
                ->whereIn('idModule', $idModules)
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev12 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "prev_12_month")
                ->whereIn('idModule', $idModules)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext3 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "next_3_month")
                ->whereIn('idModule', $idModules)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "next_6_month")
                ->whereIn('idModule', $idModules)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext12 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "next_12_month")
                ->whereIn('idModule', $idModules)
                ->groupBy('p_id_periode')
                ->first();
        } elseif ($idStatus[0] != null) {
            $query->whereIn('project_status', $idStatus);

            $etps = DB::table('v_apprenant_etp_alls')
                ->select('idEtp', 'etp_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->whereIn('project_status', $idStatus)
                ->orderBy('etp_name', 'asc')
                ->groupBy('idEtp', 'etp_name')
                ->get();

            $fonctions = DB::table('v_apprenant_etp_alls')
                ->select('idFonction', 'emp_fonction', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->whereIn('project_status', $idStatus)
                ->orderBy('emp_fonction', 'asc')
                ->groupBy('idFonction', 'emp_fonction')
                ->get();

            $villes = DB::table('v_apprenant_etp_alls')
                ->select('project_id_ville', 'project_ville as ville', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->whereIn('project_status', $idStatus)
                ->orderBy('project_ville', 'asc')
                ->groupBy('project_id_ville', 'project_ville')
                ->get();

            $modalites = DB::table('v_apprenant_etp_alls')
                ->select('project_modality', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('project_modality', '!=', 'null')
                ->whereIn('project_status', $idStatus)
                ->orderBy('project_modality', 'asc')
                ->groupBy('project_modality')
                ->get();

            $modules = DB::table('v_apprenant_etp_alls')
                ->select('idModule', 'module_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('idModule', '!=', 'null')
                ->whereIn('project_status', $idStatus)
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();

            $periodePrev3 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "prev_3_month")
                ->whereIn('project_status', $idStatus)
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "prev_6_month")
                ->whereIn('project_status', $idStatus)
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev12 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "prev_12_month")
                ->whereIn('project_status', $idStatus)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext3 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "next_3_month")
                ->whereIn('project_status', $idStatus)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "next_6_month")
                ->whereIn('project_status', $idStatus)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext12 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "next_12_month")
                ->whereIn('project_status', $idStatus)
                ->groupBy('p_id_periode')
                ->first();
        } elseif ($idModalites[0] != null) {
            $query->whereIn('project_modality', $idModalites);

            $etps = DB::table('v_apprenant_etp_alls')
                ->select('idEtp', 'etp_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->whereIn('project_modality', $idModalites)
                ->orderBy('etp_name', 'asc')
                ->groupBy('idEtp', 'etp_name')
                ->get();

            $fonctions = DB::table('v_apprenant_etp_alls')
                ->select('idFonction', 'emp_fonction', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->whereIn('project_modality', $idModalites)
                ->orderBy('emp_fonction', 'asc')
                ->groupBy('idFonction', 'emp_fonction')
                ->get();

            $villes = DB::table('v_apprenant_etp_alls')
                ->select('project_id_ville', 'project_ville as ville', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->whereIn('project_modality', $idModalites)
                ->orderBy('project_ville', 'asc')
                ->groupBy('project_id_ville', 'project_ville')
                ->get();

            $status = DB::table('v_apprenant_etp_alls')
                ->select('project_status', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('project_status', '!=', 'null')
                ->whereIn('project_modality', $idModalites)
                ->orderBy('project_status', 'asc')
                ->groupBy('project_status')
                ->get();

            $modules = DB::table('v_apprenant_etp_alls')
                ->select('idModule', 'module_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('idModule', '!=', 'null')
                ->whereIn('project_modality', $idModalites)
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();

            $periodePrev3 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "prev_3_month")
                ->whereIn('project_modality', $idModalites)
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "prev_6_month")
                ->whereIn('project_modality', $idModalites)
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev12 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "prev_12_month")
                ->whereIn('project_modality', $idModalites)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext3 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "next_3_month")
                ->whereIn('project_modality', $idModalites)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "next_6_month")
                ->whereIn('project_modality', $idModalites)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext12 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "next_12_month")
                ->whereIn('project_modality', $idModalites)
                ->groupBy('p_id_periode')
                ->first();
        } elseif ($idVilles[0] != null) {
            $query->whereIn('project_id_ville', $idVilles);

            $etps = DB::table('v_periodes')
                ->select('idEtp', 'etp_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->whereIn('project_id_ville', $idVilles)
                ->orderBy('etp_name', 'asc')
                ->groupBy('idEtp', 'etp_name')
                ->get();

            $fonctions = DB::table('v_periodes')
                ->select('idFonction', 'emp_fonction', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->whereIn('project_id_ville', $idVilles)
                ->orderBy('emp_fonction', 'asc')
                ->groupBy('idFonction', 'emp_fonction')
                ->get();

            $status = DB::table('v_apprenant_etp_alls')
                ->select('project_status', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('project_status', '!=', 'null')
                ->whereIn('project_id_ville', $idVilles)
                ->orderBy('project_status', 'asc')
                ->groupBy('project_status')
                ->get();

            $modalites = DB::table('v_apprenant_etp_alls')
                ->select('project_modality', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('project_modality', '!=', 'null')
                ->whereIn('project_id_ville', $idVilles)
                ->orderBy('project_modality', 'asc')
                ->groupBy('project_modality')
                ->get();

            $modules = DB::table('v_apprenant_etp_alls')
                ->select('idModule', 'module_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('idModule', '!=', 'null')
                ->whereIn('project_id_ville', $idVilles)
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();

            $periodePrev3 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "prev_3_month")
                ->whereIn('project_id_ville', $idVilles)
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "prev_6_month")
                ->whereIn('project_id_ville', $idVilles)
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev12 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "prev_12_month")
                ->whereIn('project_id_ville', $idVilles)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext3 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "next_3_month")
                ->whereIn('project_id_ville', $idVilles)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "next_6_month")
                ->whereIn('project_id_ville', $idVilles)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext12 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "next_12_month")
                ->whereIn('project_id_ville', $idVilles)
                ->groupBy('p_id_periode')
                ->first();
        } elseif ($idPeriodes != null) {
            $query->where('p_id_periode', $idPeriodes);

            $etps = DB::table('v_periodes')
                ->select('idEtp', 'etp_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('p_id_periode', $idPeriodes)
                ->orderBy('etp_name', 'asc')
                ->groupBy('idEtp', 'etp_name')
                ->get();

            $fonctions = DB::table('v_periodes')
                ->select('idFonction', 'emp_fonction', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('p_id_periode', $idPeriodes)
                ->orderBy('emp_fonction', 'asc')
                ->groupBy('idFonction', 'emp_fonction')
                ->get();

            $villes = DB::table('v_periodes')
                ->select('project_id_ville', 'project_ville as ville', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('p_id_periode', $idPeriodes)
                ->orderBy('project_ville', 'asc')
                ->groupBy('project_id_ville', 'project_ville')
                ->get();

            $status = DB::table('v_apprenant_etp_alls')
                ->select('project_status', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('project_status', '!=', 'null')
                ->where('p_id_periode', $idPeriodes)
                ->orderBy('project_status', 'asc')
                ->groupBy('project_status')
                ->get();

            $modalites = DB::table('v_apprenant_etp_alls')
                ->select('project_modality', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('project_modality', '!=', 'null')
                ->where('p_id_periode', $idPeriodes)
                ->orderBy('project_modality', 'asc')
                ->groupBy('project_modality')
                ->get();

            $modules = DB::table('v_apprenant_etp_alls')
                ->select('idModule', 'module_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('idModule', '!=', 'null')
                ->where('p_id_periode', $idPeriodes)
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();
        } else {
            $etps = DB::table('v_apprenant_etp_all_filters')
                ->select('idEtp', 'etp_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->orderBy('etp_name', 'asc')
                ->groupBy('idEtp', 'etp_name')
                ->get();

            $fonctions = DB::table('v_apprenant_etp_all_filters')
                ->select('idFonction', 'emp_fonction', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->orderBy('emp_fonction', 'asc')
                ->groupBy('idFonction', 'emp_fonction')
                ->get();

            $villes = DB::table('v_periodes')
                ->select('project_id_ville', 'project_ville as ville', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->orderBy('project_ville', 'asc')
                ->groupBy('project_id_ville', 'project_ville')
                ->get();

            $status = DB::table('v_apprenant_etp_alls')
                ->select('project_status', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('project_status', '!=', 'null')
                ->orderBy('project_status', 'asc')
                ->groupBy('project_status')
                ->get();

            $modalites = DB::table('v_apprenant_etp_alls')
                ->select('project_modality', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('project_modality', '!=', 'null')
                ->orderBy('project_modality', 'asc')
                ->groupBy('project_modality')
                ->get();

            $modules = DB::table('v_apprenant_etp_alls')
                ->select('idModule', 'module_name', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('idModule', '!=', 'null')
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();

            $periodePrev3 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "prev_3_month")
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "prev_6_month")
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev12 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "prev_12_month")
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext3 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "next_3_month")
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', "next_6_month")
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext12 = DB::table('v_apprenant_etp_alls')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('id_cfp', $this->idCfp())
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

    public function filterItem(Request $req)
    {
        $idEtps = explode(',', $req->idEtp);
        $idFonctions = explode(',', $req->idFonction);
        $idPeriodes = $req->idPeriode;
        $idModules = explode(',', $req->idModule);
        $idVilles = explode(',', $req->idVille);
        $idModalites = explode(',', $req->idModalite);
        $idStatus = explode(',', $req->idStatut);

        $query = DB::table('v_apprenant_etp_alls')
            ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_initial_name', 'emp_photo', 'emp_matricule', 'emp_phone', 'emp_email', 'emp_fonction', 'idEtp', 'etp_name')
            ->where('idCfp', $this->idCfp());

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

        $apprs = $query->get();

        return response()->json(['apprs' => $apprs]);
    }




    public function getAllApprenantForm($idProjet)
    {
        $apprenants = DB::table('v_list_apprenants')
            ->select('idProjet', 'idEmploye', 'fonction', 'idCustomer', 'photo', 'matricule', 'name', 'firstName', 'email', 'phone', 'cin', 'adresse')
            ->where('idProjet', $idProjet)
            ->get();

        $countApprs = DB::table('v_list_apprenants')
            ->select('idEmploye')
            ->where('idProjet', $idProjet)
            ->count();

        // $checkEvals = DB::select('SELECT eval_chauds.idSession, eval_chauds.idEmploye, sessions.idProjet FROM eval_chauds 
        //     INNER JOIN sessions ON eval_chauds.idSession = sessions.idSession
        //     WHERE eval_chauds.idSession = ? AND sessions.idProjet = ? GROUP BY eval_chauds.idSession, eval_chauds.idEmploye, sessions.idProjet', [$idSession, $idProjet]);
        // $countEvals = count($checkEvals);

        return response()->json([
            'apprenants' => $apprenants,
            'countApprs' => $countApprs,
            // 'countEvals' => $countEvals
        ]);
    }

    public function countPresent($idSeance, $value)
    {
        $count = DB::table('emargements')
            ->select('idEmploye')
            ->where('idSeance', $idSeance)
            ->where('isPresent', $value)
            ->count();

        return $count;
    }

    public function getSeanceApprenantForm($idProjet, $idSeance)
    {
        $seance = DB::table('seances')->select('idSeance')->where('idSeance', $idSeance)->first();

        $apprenants = DB::table('v_seance_emp_emargements')
            ->select('idProjet', 'idSeance', 'idEmploye', 'initialNameEmp', 'photoEmp AS photo', 'nameEmp AS name', 'firstNameEmp AS firstName')
            ->where('idProjet', $idProjet)
            ->where('idSeance', $idSeance)
            ->get();

        $countApprs = DB::table('v_seance_emp_emargements')
            ->select('idProjet')
            ->where('idProjet', $idProjet)
            ->where('idSeance', $idSeance)
            ->count();

        $countAbsent = $this->countPresent($idSeance, 0);
        $countPresent = $this->countPresent($idSeance, 1);
        $countPartiel = $this->countPresent($idSeance, 2);

        return response()->json([
            'seance' => $seance,
            'apprenants' => $apprenants,
            'countApprs' => $countApprs,
            'countAbsent' => $countAbsent,
            'countPresent' => $countPresent,
            'countPartiel' => $countPartiel
        ]);
    }

    public function getAllApprenantFormInterne($idProjet, $idSession)
    {
        $apprenants = DB::table('v_list_apprenants')
            ->select('idProjet', 'idEmploye', 'idSession', 'fonction', 'idCustomer', 'photoEmp', 'matricule', 'name', 'firstName', 'mailEmp', 'phoneEmp', 'cin', 'adress')
            ->where('idProjet', '=', $idProjet)
            ->where('idSession', '=', $idSession)
            ->groupBy('idProjet', 'idEmploye', 'idSession', 'fonction', 'idCustomer', 'photoEmp', 'matricule', 'name', 'firstName', 'mailEmp', 'phoneEmp', 'cin', 'adress')
            ->get();

        $countApprs = DB::select("SELECT COUNT(detail_apprenants.idEmploye) AS nombreAppr
            FROM `detail_apprenants` 
            INNER JOIN apprenants ON detail_apprenants.idEmploye = apprenants.idEmploye
            INNER JOIN employes ON apprenants.idEmploye = employes.idEmploye
            WHERE detail_apprenants.idSession = ?", [$idSession]);

        return response()->json([
            'apprenants' => $apprenants,
            'countApprs' => $countApprs
        ]);
    }

    public function getAllApprenant($idProjet, $idSession)
    {
        $allApprs = DB::table('v_list_apprenants')
            ->select('idProjet', 'idEmploye', 'idSession', 'fonction', 'idCustomer', 'photoEmp', 'matricule', 'name', 'firstName', 'mailEmp', 'phoneEmp', 'cin', 'adress')
            ->where('idCustomer', '=', Auth::user()->id)
            ->where('idProjet', '=', $idProjet)
            ->where('idSession', '=', $idSession)
            ->groupBy('idProjet', 'idEmploye', 'idSession', 'fonction', 'idCustomer', 'photoEmp', 'matricule', 'name', 'firstName', 'mailEmp', 'phoneEmp', 'cin', 'adress')
            ->get();

        $countApprs = DB::select("SELECT COUNT(detail_apprenants.idEmploye) AS nombreAppr
            FROM `detail_apprenants` 
            INNER JOIN apprenants ON detail_apprenants.idEmploye = apprenants.idEmploye
            INNER JOIN employes ON apprenants.idEmploye = employes.idEmploye
            WHERE employes.idCustomer = ?
            AND detail_apprenants.idSession = ?", [Auth::user()->id, $idSession]);

        $checkEvals = DB::select('SELECT eval_chauds.idSession, eval_chauds.idEmploye, sessions.idProjet FROM eval_chauds 
            INNER JOIN sessions ON eval_chauds.idSession = sessions.idSession
            WHERE eval_chauds.idSession = ? AND sessions.idProjet = ? GROUP BY eval_chauds.idSession, eval_chauds.idEmploye, sessions.idProjet', [$idSession, $idProjet]);
        $countEvals = count($checkEvals);

        return response()->json(['allApprs' => $allApprs, 'countApprs' => $countApprs, 'countEvals' => $countEvals]);
    }

    public function getApprenant($idEmploye)
    {
        $idEtp = Auth::user()->id;

        $apprenant = DB::table('employes')
            ->join('fonctions', 'employes.idFonction', 'fonctions.idFonction')
            ->select(
                'idEmploye',
                'matricule',
                'name',
                'firstName',
                'fonctions.fonction',
                'employes.idCustomer',
                'employes.mailEmp',
                'employes.phoneEmp',
                'employes.idFonction'
            )
            ->where('employes.idCustomer', '=', $idEtp)
            ->where('employes.idEmploye', '=', $idEmploye)
            ->get();

        return response()->json($apprenant);
    }

    public function listEmp()
    {
        $customer = DB::select("SELECT employes.idEmploye, employes.idCustomer, customers.idTypeCustomer
            FROM employes
            INNER JOIN customers ON employes.idCustomer = customers.idCustomer
            WHERE idEmploye = ?", [Auth::user()->id]);

        $apprenants = DB::select("SELECT idEmploye, name, firstName, photoEmp FROM v_employe_alls WHERE idCustomer = ? AND isActive = ? AND role_id = ? AND hasRole = ?", [$customer[0]->idCustomer, 1, 4, 1]);

        return response()->json(["apprenants" => $apprenants]);
    }

    public function checkEmp($idSession, $idEmploye)
    {
        $checkAppr = DB::table('apprenants')->select('idEmploye')->where('idEmploye', '=', $idEmploye)->get();

        $check = DB::table('detail_apprenants')
            ->select('idEmploye', 'idSession')
            ->where('idSession', '=', $idSession)
            ->where('idEmploye', '=', $idEmploye)
            ->get();

        $checked = DB::table('detail_apprenants')
            ->select('idEmploye', 'idSession')
            ->where('idSession', '=', $idSession)
            ->where('idEmploye', '=', $idEmploye)
            ->first();

        return response()->json([
            'check' => $checked,
            'countCheck' => count($check),
            'countCheckAppr' => count($checkAppr)
        ]);
    }
    public function store(Request $req)
    {
        $checkAppr = DB::table('apprenants')->select('idEmploye')->where('idEmploye', '=', $req->idEmploye)->get();

        $check = DB::table('detail_apprenants')
            ->select('idEmploye', 'idSession')
            ->where('idSession', '=', $req->idSession)
            ->where('idEmploye', '=', $req->idEmploye)
            ->get();


        if (count($checkAppr) < 1 && count($check) < 1) {
            DB::table('apprenants')->insert([
                'idEmploye' => $req->idEmploye
            ]);

            DB::table('detail_apprenants')->insert([
                'idEmploye' => $req->idEmploye,
                'idSession' => $req->idSession
            ]);

            return response()->json(['success' => 'Apprenant ajouté avec succès']);
        } elseif (count($checkAppr) >= 1 && count($check) < 1) {
            DB::table('detail_apprenants')->insert([
                'idEmploye' => $req->idEmploye,
                'idSession' => $req->idSession
            ]);

            return response()->json(['success' => 'Apprenant ajouté avec succès']);
        } elseif (count($checkAppr) >= 1 && count($check) >= 1) {
            return response()->json(['erreur' => 'Employée déjas inscrit à la session']);
        }
    }

    public function updateImageAppr(Request $req, $idApprenant)
    {
        // $validate = Validator::make($req->all(), [
        //     'photo' => 'required|image|mimes:png,jpg,webp,gif|max:6144'
        // ]);
        // if ($validate->fails()) {
        //     return back()->with('error', $validate->messages());
        // } else {

        $appr = DB::table('users')->select('photo')->where('id', $idApprenant)->first();

        if ($appr != null) {
            $folder = 'img/employes/' . $appr->photo;

            if (File::exists($folder)) {
                File::delete($folder);
            }

            $folderPath = public_path('img/employes/');

            $image_parts = explode(";base64,", $req->image);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);

            $imageName = uniqid() . '.webp';
            $imageFullPath = $folderPath . $imageName;

            file_put_contents($imageFullPath, $image_base64);

            DB::table('users')->where('id', $idApprenant)->update([
                'photo' => $imageName,
            ]);
            return response()->json([
                'success' => 'Image Uploaded Successfully',
                'imageName' =>  $imageName
            ]);
        }
    }
}
