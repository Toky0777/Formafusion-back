<?php

namespace App\Http\Controllers;

use App\Models\Projet;
use Exception;
use GuzzleHttp\Promise\Each;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\EvaluationController;

class ProjetInterController extends Controller
{
    public function idCfp()
    {
        $customer = DB::select("SELECT employes.idEmploye, employes.idCustomer, customers.idTypeCustomer FROM employes INNER JOIN customers ON employes.idCustomer = customers.idCustomer WHERE idEmploye = ?", [Auth::user()->id]);
        return $customer[0]->idCustomer;
    }

    public function store(Request $req)
    {
        $req->validate([
            'dateDebutInter' => 'required | date | after_or_equal:today',
            'dateFinInter' => 'required | date | after_or_equal:dateDebut',
            'idModuleInter' => 'required | integer',
            'idModaliteInter' => 'required | integer',
            'idVilleInter' => 'required | integer',
        ], [
            'dateDebutInter.required' => 'Ce champs est obligatoire',
            'dateDebutInter.after_or_equal' => 'Veuillez entrer une date valide',
            'dateFinInter.required' => 'Ce champs est obligatoire',
            'dateFinInter.after' => 'La date de fin est incorrect',
            'idFormation.required' => 'Ce champs est obligatoire',
            'idModaliteInter.required' => 'Ce champs est obligatoire',
            'idVilleInter.required' => 'Ce champs est obligatoire',
        ]);

        try {
            DB::beginTransaction();

            $typeProjet = DB::table('type_projets')->select('idTypeProjet', 'type')->get();
            $typeProjet = $typeProjet->toArray();

            $projet = new Projet();
            $projet->dateDebut = $req->dateDebutInter;
            $projet->dateFin = $req->dateFinInter;
            $projet->idModalite = $req->idModaliteInter;
            $projet->idCustomer = $this->idCfp();
            $projet->idModule = $req->idModuleInter;
            $projet->idTypeProjet = $typeProjet['1']->idTypeProjet;
            $projet->idVille = $req->idVilleInter;
            $projet->isActiveProjet = 0;
            $projet->save();

            $prj = DB::table('projets')->select('idProjet')->orderBy('idProjet', 'desc')->first();

            DB::table('inters')->insert([
                'idProjet' => $prj->idProjet,
                'idCfp' => $this->idCfp()
            ]);

            DB::commit();

            return response()->json([
                'status' => 200,
                'success' => 'Opération effectuée avec succès'
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 401,
                'error' => 'Opération echouée'
            ]);
        }
    }

    public function showCfp($idProjet)
    {
        $projet = DB::table('v_projet_cfps')
            ->select('idProjet', 'dateDebut', 'dateFin', 'project_title', 'etp_name', 'ville', 'project_status', 'project_type', 'paiement', 'project_reference', 'modalite', 'idEtp', 'etp_initial_name', 'etp_logo', 'idModule', 'module_name', 'module_image', 'project_price_pedagogique', 'project_price_annexe', 'module_description', 'salle_name', 'salle_rue', 'salle_quartier', 'salle_code_postal', 'ville')
            ->where('idProjet', $idProjet)
            ->first();

        $villes = DB::table('villes')->select('idVille', 'ville')->get();
        $paiements = DB::table('paiements')->select('idPaiement', 'paiement')->get();

        $seances = DB::table('v_seances')
            ->select('idSeance', 'dateSeance', 'heureDebut', 'heureFin', 'idProjet', 'idModule', DB::raw('TIMEDIFF(heureFin,heureDebut) as intervalle_raw'))
            ->where('idProjet', $idProjet)
            ->get();

        $modules = DB::table('mdls')
            ->select('idModule', 'moduleName AS module_name')
            ->where('moduleName', '!=', 'Default module')
            ->where('idCustomer', $this->idCfp())
            ->orderBy('moduleName', 'asc')
            ->get();

        $materiels = DB::table('prestation_modules')
            ->select('idPrestation', 'prestation_name', 'idModule')
            ->get();

        $objectifs = DB::table('objectif_modules')->select('idObjectif', 'objectif', 'idModule')->get();

        return view('ETP.projets.newDetail', compact('projet', 'villes', 'paiements', 'seances', 'modules', 'materiels', 'objectifs'));
    }
    public function updateInter($idSession, $idProjet)
    {
        DB::table('inter_entreprises')
            ->where('idProjet', '=', $idProjet)
            ->where('idSession', '=', $idSession)
            ->where('idEtp', '=', Auth::user()->id)
            ->update([
                'isActiveInter' => 1
            ]);

        return back();
    }

    public function getEtpAdded($idProjet)
    {
        $etps = DB::table('v_projet_cfps')
            ->select('idEtp', 'etp_name', 'etp_logo', 'etp_initial_name')
            ->where('idProjet', $idProjet)
            ->groupBy('idEtp', 'etp_name', 'etp_logo', 'etp_initial_name')
            ->get();

        return response()->json(['etps' => $etps]);
    }

    public function removeEtpInter($idProjet, $idEtp)
    {

        $queryEval = DB::table('eval_chauds')
            ->join('detail_apprenant_inters', 'eval_chauds.idEmploye', '=', 'detail_apprenant_inters.idEmploye')
            ->select('eval_chauds.*', 'detail_apprenant_inters.idEtp')
            ->where('eval_chauds.idProjet', $idProjet);

        $queryPresence = DB::table('emargements')
            ->join('detail_apprenant_inters', 'emargements.idEmploye', '=', 'detail_apprenant_inters.idEmploye')
            ->select('emargements.*', 'detail_apprenant_inters.idEtp')
            ->where('emargements.idProjet', $idProjet);

        $checkEval = (clone $queryEval)->where('detail_apprenant_inters.idEtp', $idEtp)->get();

        $checkPresence = (clone $queryPresence)->where('detail_apprenant_inters.idEtp', $idEtp)->get();

        try {
            $checkAppr = DB::table('detail_apprenant_inters')->where('idProjet', $idProjet)->where('idEtp', $idEtp)->get();

            if (count($checkAppr) <= 0) {
                $checkApprEtpGrp = DB::table('etp_groupeds')->where('idEntrepriseParent', $idEtp)->pluck('idEntreprise')->toArray();

                $allIdEtp = array_merge($checkApprEtpGrp, [$idEtp]);

                if ($checkApprEtpGrp != []) {
                    $checkEvalGrp = (clone $queryEval)->whereIn('detail_apprenant_inters.idEtp', $allIdEtp)->get();
                    $checkPresenceGrp = (clone $queryPresence)->whereIn('detail_apprenant_inters.idEtp', $allIdEtp)->get();

                    try {
                        if (count($checkEvalGrp) > 0 && count($checkPresenceGrp) > 0) {
                            DB::beginTransaction();

                            //Remove Evaluation
                            (clone $queryEval)->whereIn('detail_apprenant_inters.idEtp', $allIdEtp)->delete();

                            //Remove presence
                            (clone $queryPresence)->whereIn('detail_apprenant_inters.idEtp', $allIdEtp)->delete();

                            DB::table('detail_apprenant_inters')->where('idProjet', $idProjet)->whereIn('idEtp', $allIdEtp)->delete();
                            DB::table('inter_entreprises')->where('idProjet', $idProjet)->whereIn('idEtp', $allIdEtp)->delete();
                            DB::commit();
                        } elseif (count($checkEvalGrp) > 0 && count($checkPresenceGrp) <= 0) {
                            DB::beginTransaction();

                            //Remove Evaluation
                            (clone $queryEval)->whereIn('detail_apprenant_inters.idEtp', $allIdEtp)->delete();

                            DB::table('detail_apprenant_inters')->where('idProjet', $idProjet)->whereIn('idEtp', $allIdEtp)->delete();
                            DB::table('inter_entreprises')->where('idProjet', $idProjet)->whereIn('idEtp', $allIdEtp)->delete();
                            DB::commit();
                        } elseif (count($checkEvalGrp) <= 0 && count($checkPresenceGrp) > 0) {
                            DB::beginTransaction();

                            //Remove presence
                            (clone $queryPresence)->whereIn('detail_apprenant_inters.idEtp', $allIdEtp)->delete();

                            DB::table('detail_apprenant_inters')->where('idProjet', $idProjet)->whereIn('idEtp', $allIdEtp)->delete();
                            DB::table('inter_entreprises')->where('idProjet', $idProjet)->whereIn('idEtp', $allIdEtp)->delete();
                            DB::commit();
                        } else {
                            DB::beginTransaction();
                            DB::table('detail_apprenant_inters')->where('idProjet', $idProjet)->whereIn('idEtp', $allIdEtp)->delete();
                            DB::table('inter_entreprises')->where('idProjet', $idProjet)->whereIn('idEtp', $allIdEtp)->delete();
                            DB::commit();
                        }
                        return response()->json(['success' => 'Succès']);
                    } catch (\Throwable $th) {
                        return response()->json(['error' => $th->getMessage()]);
                    }
                } else {
                    DB::table('inter_entreprises')->where('idProjet', $idProjet)->whereIn('idEtp', $allIdEtp)->delete();
                    return response()->json(['success' => 'Succès']);
                }
            } else {
                try {
                    if (count($checkEval) > 0 && count($checkPresence) > 0) {
                        DB::beginTransaction();

                        //Remove Evaluation
                        (clone $queryEval)->where('detail_apprenant_inters.idEtp', $idEtp)->delete();

                        //Remove presence
                        (clone $queryPresence)->where('detail_apprenant_inters.idEtp', $idEtp)->delete();

                        DB::table('detail_apprenant_inters')->where('idProjet', $idProjet)->where('idEtp', $idEtp)->delete();
                        DB::table('inter_entreprises')->where('idProjet', $idProjet)->where('idEtp', $idEtp)->delete();
                        DB::commit();
                    } elseif (count($checkEval) > 0 && count($checkPresence) <= 0) {
                        DB::beginTransaction();

                        //Remove Evaluation
                        (clone $queryEval)->where('detail_apprenant_inters.idEtp', $idEtp)->delete();

                        DB::table('detail_apprenant_inters')->where('idProjet', $idProjet)->where('idEtp', $idEtp)->delete();
                        DB::table('inter_entreprises')->where('idProjet', $idProjet)->where('idEtp', $idEtp)->delete();
                        DB::commit();
                    } elseif (count($checkEval) <= 0 && count($checkPresence) > 0) {
                        DB::beginTransaction();

                        //Remove presence
                        (clone $queryPresence)->where('detail_apprenant_inters.idEtp', $idEtp)->delete();

                        DB::table('detail_apprenant_inters')->where('idProjet', $idProjet)->where('idEtp', $idEtp)->delete();
                        DB::table('inter_entreprises')->where('idProjet', $idProjet)->where('idEtp', $idEtp)->delete();
                        DB::commit();
                    } else {
                        DB::beginTransaction();
                        DB::table('detail_apprenant_inters')->where('idProjet', $idProjet)->where('idEtp', $idEtp)->delete();
                        DB::table('inter_entreprises')->where('idProjet', $idProjet)->where('idEtp', $idEtp)->delete();
                        DB::commit();
                    }
                    return response()->json(['success' => 'Succès']);
                } catch (\Throwable $th) {
                    return response()->json(['error' => $th->getMessage()]);
                }
            }
        } catch (Exception $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }

    public function getApprenantProjetInter($idProjet)
    {
        $idEtpParent = $this->getIdEtpAdded($idProjet);

        $queryEtp = DB::table('v_union_list_entreprise_inter')
            ->select('idEtp', 'etp_name');

        $query = DB::table('v_union_list_apprenant_inter')
            ->select('*')
            ->where('role_id', 4)
            ->where('user_is_in_service', 1);

        if (isset($idEtpParent)) {
            $a1 = (clone $query)->whereIn('idEntrepriseParent', $idEtpParent)->orderBy('etp_name', 'asc')->get()->toArray();
            $a2 = (clone $query)->where('idProjet', $idProjet)->orderBy('etp_name', 'asc')->get()->toArray();

            $e1 = (clone $queryEtp)->whereIn('idEtpParent', $idEtpParent)->orderBy('etp_name', 'asc')->get()->toArray();
            $e2 = (clone $queryEtp)->where('idProjet', $idProjet)->orderBy('etp_name', 'asc')->get()->toArray();

            $apprs = array_merge($a1, $a2);
            $etps = array_merge($e1, $e2);
        } else {
            $apprs = $query->where('idProjet', $idProjet)->orderBy('etp_name', 'asc')->get();
            $etps = $queryEtp->where('idProjet', $idProjet)->orderBy('etp_name', 'asc')->get();
        }

        return response()->json([
            'apprs' => $apprs,
            'etps' => $etps,
        ]);
    }

    public function addApprenantInter($idProjet, $idApprenant, $idEtp)
    {
        $checkAppr = DB::table('apprenants')->where('idEmploye', $idApprenant)->get();
        $check = DB::table('detail_apprenant_inters')->where('idProjet', $idProjet)->where('idEtp', $idEtp)->where('idEmploye', $idApprenant)->get();

        if (count($checkAppr) < 1 && count($check) < 1) {
            try {
                DB::transaction(function () use ($idProjet, $idApprenant, $idEtp) {
                    DB::table('apprenants')->insert([
                        'idEmploye' => $idApprenant
                    ]);

                    DB::table('detail_apprenant_inters')->insert([
                        'idProjet' => $idProjet,
                        'idEmploye' => $idApprenant,
                        'idEtp' => $idEtp,
                        'id_cfp_appr' => $this->idCfp()
                    ]);
                });
                return response()->json(['success' => 'Succès']);
            } catch (\Throwable $th) {
                return response()->json(['error' => 'Erreur inconnue !']);
            }
        } elseif (count($checkAppr) >= 1 && count($check) < 1) {
            try {
                DB::table('detail_apprenant_inters')->insert([
                    'idProjet' => $idProjet,
                    'idEmploye' => $idApprenant,
                    'idEtp' => $idEtp,
                    'id_cfp_appr' => $this->idCfp()
                ]);
                return response()->json(['success' => 'Succès']);
            } catch (\Throwable $th) {
                return response()->json(['error' => 'Erreur inconnue !']);
            }
        } elseif (count($checkAppr) >= 1 && count($check) >= 1) {
            return response()->json(['error' => 'Employée déjà inscrit à la session']);
        }
    }

    public function getApprenantAddedInter($idProjet)
    {
        $now = Carbon::now()->toDateString();

        // Liste des apprenants ajoutés
        // $apprs = DB::table('v_list_apprenant_inter_added')
        //     ->where('idProjet', $idProjet)
        //     ->orderBy('emp_name', 'asc')
        //     ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_fonction', 'emp_email', 'emp_photo', 'emp_matricule', 'etp_name', 'idEtp', 'idProjet')
        //     ->get();

        // Liste des entreprises associées
        $getEtps = DB::table('v_list_apprenant_inter_added')
            ->where('idProjet', $idProjet)
            ->groupBy('idEtp', 'etp_name')
            ->orderBy('etp_name', 'asc')
            ->select('etp_name', 'idEtp', 'idProjet')
            ->get();

        // Séances et présences
        $getSeance = DB::table('v_emargement_appr')
            ->where('idProjet', $idProjet)
            ->whereDate('dateSeance', '<=', $now)
            ->groupBy('idSeance')
            ->select('idSeance', 'idProjet', 'heureDebut', 'heureFin', 'dateSeance', 'isPresent', 'idEmploye')
            ->get();

        $getPresence = DB::table('v_emargement_appr')
            ->where('idProjet', $idProjet)
            ->whereDate('dateSeance', '<=', $now)
            ->select('idSeance', 'idProjet', 'isPresent', 'idEmploye')
            ->get();

        // Nombre total de dates de séance
        $countDate = DB::table('v_seances')
            ->where('idProjet', $idProjet)
            ->whereDate('dateSeance', '<=', $now)
            ->groupBy('dateSeance')
            ->select('idProjet', 'dateSeance', 'idSeance', DB::raw('COUNT(*) as count'))
            ->get();

        // Liste des apprenants présents dans les émargements
        $getAppr = DB::table('v_emargement_appr')
            ->where('idProjet', $idProjet)
            ->groupBy('idEmploye')
            ->select('idProjet', 'idEmploye', 'name', 'firstName', 'photo')
            ->get();

        // Comptage des émargements
        $countAppr = $getAppr->count();
        $countEmargement = DB::table('emargements')
            ->where('idProjet', $idProjet)
            ->groupBy('idSeance')
            ->count();

        // Comptage des statuts de présence (optimisé)
        $statuts = DB::table('emargements')
            ->where('idProjet', $idProjet)
            ->whereIn('isPresent', [0, 1, 2, 3])
            ->select('isPresent', DB::raw('COUNT(*) as count'))
            ->groupBy('isPresent')
            ->pluck('count', 'isPresent');

        $countPresent = $statuts[3] ?? 0;
        $countPartiel = $statuts[2] ?? 0;
        $countAbsent = ($statuts[1] ?? 0) + ($statuts[0] ?? 0);

        // Calcul des pourcentages
        $divide = $countAppr * $countEmargement;
        $percentPresent = $divide > 0 ? number_format(($countPresent / $divide) * 100, 1, ',', ' ') : 0;
        $percentPartiel = $divide > 0 ? number_format(($countPartiel / $divide) * 100, 1, ',', ' ') : 0;
        $percentAbsent = $divide > 0 ? number_format(($countAbsent / $divide) * 100, 1, ',', ' ') : 0;

        // Récupération des apprenants avec leurs évaluations
        $apprs = DB::table('v_list_apprenant_inter_added as L')
            ->leftJoin('eval_apprenant as E', function ($join) use ($idProjet) {
                $join->on('E.idEmploye', '=', 'L.idEmploye')
                    ->where('E.idProjet', '=', $idProjet);
            })
            ->select(
                'L.idEmploye',
                'emp_name',
                'emp_firstname',
                'emp_fonction',
                'emp_email',
                'emp_photo',
                'emp_matricule',
                'etp_name',
                'idEtp',
                'E.avant',
                'E.apres'
            )
            ->where('L.idProjet', $idProjet)
            ->orderBy('emp_name', 'asc')
            ->get();

        $arrayApprs = $apprs->pluck('idEmploye')->toArray();

        if (!$arrayApprs || count($arrayApprs) === 0) {
            return response()->json([]);
        }

        $data = DB::table('emargements')
            ->selectRaw('idEmploye, COUNT(*) as total, SUM(isPresent) as somme')
            ->where('idProjet', $idProjet)
            ->whereIn('idEmploye', $arrayApprs)
            ->groupBy('idEmploye')
            ->get();

        $results = $data->map(function ($item) {
            $total = (int) $item->total;
            $sum = (int) $item->somme;
            $present = $total * 3;
            $absent = $total;

            return [
                'idEmploye' => $item->idEmploye,
                'checking' => $sum === $present ? 3 : ($sum < $present && $sum !== $absent ? 2 : ($sum === $absent ? 1 : 0)),
                'color' => $sum === $present ? 'bg-green-500' : ($sum < $present && $sum !== $absent ? 'bg-red-500' : ($sum === $absent ? 1 : 'bg-amber-500'))
            ];
        });

        // Joindre les résultats aux apprenants
        $apprs = $apprs->map(function ($appr) use ($results, $idProjet) {
            // Chercher les résultats de présence
            $result = $results->firstWhere('idEmploye', $appr->idEmploye);

            // Ajouter les valeurs de présence à l'apprenant
            $appr->checking = $result['checking'] ?? 0;
            $appr->color = $result['color'] ?? 'bg-gray-500';

            // Appel à la méthode checkEval de EvaluationController
            $evaluationController = app(EvaluationController::class);
            $evaluationResponse = $evaluationController->checkEval($idProjet, $appr->idEmploye);

            // Décoder la réponse JSON
            $evaluationData = json_decode($evaluationResponse->getContent(), true);

            // Ajouter les données de l'évaluation à l'apprenant
            $appr->evaluation = $evaluationData['one'] ?? null;

            return $appr;
        });

        return response()->json([
            'apprs' => $apprs,
            'getEtps' => $getEtps,
            'getSeance' => $getSeance,
            'getPresence' => $getPresence,
            'getAppr' => $getAppr,
            'countDate' => $countDate,
            'countAppr' => $countAppr,
            'countEmargement' => $countEmargement,
            'percentPresent' => $percentPresent . '%',
            'percentPartiel' => $percentPartiel . '%',
            'percentAbsent' => $percentAbsent . '%',
        ]);
    }


    public function addApprenantInterReservation($idProjet, $idApprenant, $idEtp)
    {
        $nb_place = DB::table('detail_apprenant_inters')->where('idEtp', auth()->user()->id)->where('idProjet', $idProjet)->count();
        $nb_place_reserved = DB::table('inter_entreprises')->where('idProjet', $idProjet)->where('idEtp', $idEtp)->value('nbPlaceReserved');
        $id_cfp = DB::table('inters')->where('idProjet', $idProjet)->value('idCfp');
        if ($nb_place < $nb_place_reserved) {
            DB::table('detail_apprenant_inters')->insert([
                'idProjet' => $idProjet,
                'idEmploye' => $idApprenant,
                'idEtp' => $idEtp,
                'id_cfp_appr' => $id_cfp
            ]);
            return response()->json(['success' => 'Succès', 'status' => 200]);
        } else {
            return response()->json(['erreur' => 'Vous atteint le nombre maximum d\'apprenant', 'status' => 403]);
        }
    }

    public function removeApprsEtp($idProjet, $idApprenant, $idEtp)
    {
        $eval = DB::table('eval_chauds')->select('idProjet')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->get();
        $presence = DB::table('emargements')->select('idProjet')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->get();

        $checkEval = count($eval);
        $checkPresence = count($presence);

        try {
            if ($checkEval > 0 && $checkPresence > 0) {
                DB::transaction(function () use ($idProjet, $idApprenant) {
                    DB::table('detail_apprenant_inters')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->delete();
                    DB::table('eval_chauds')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->delete();
                    DB::table('emargements')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->delete();
                });
            } elseif ($checkEval > 0) {
                DB::transaction(function () use ($idProjet, $idApprenant) {
                    DB::table('detail_apprenant_inters')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->delete();
                    DB::table('eval_chauds')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->delete();
                });
            } elseif ($checkPresence > 0) {
                DB::transaction(function () use ($idProjet, $idApprenant) {
                    DB::table('detail_apprenant_inters')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->delete();
                    DB::table('emargements')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->delete();
                });
            } else {
                DB::table('detail_apprenant_inters')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->delete();
            }
            return response()->json(['success' => 'Succès']);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Erreur inconnue !']);
        }
    }

    public function getIdEtpAdded($idProjet)
    {
        $idEtpAdded = DB::table('v_list_entreprise_inter')->where('idProjet', $idProjet)->pluck('idEtp')->toArray();

        return $idEtpAdded;
    }
}
