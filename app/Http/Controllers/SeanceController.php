<?php

namespace App\Http\Controllers;

use App\Mail\SendInvitationCalendar;
use App\Models\Customer;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SeanceController extends Controller
{
    public function idCfp()
    {
        $customer = DB::select("SELECT employes.idEmploye, employes.idCustomer, customers.idTypeCustomer FROM employes INNER JOIN customers ON employes.idCustomer = customers.idCustomer WHERE idEmploye = ?", [Auth::user()->id]);
        return $customer[0]->idCustomer;
    }

    //Update projectDate
    private function updateProjet($idProjet)
    {
        $dateDebut = DB::table('seances')->where('idProjet', $idProjet)->value(DB::raw('MIN(dateSeance) as dateDebut'));
        $dateFin = DB::table('seances')->where('idProjet', $idProjet)->value(DB::raw('MAX(dateSeance) as dateFin'));
        DB::table('projets')
            ->where('idProjet', $idProjet)
            ->update([
                'dateDebut' => $dateDebut,
                'dateFin' =>   $dateFin
            ]);
    }

    private function getNameCfp($idCfp)
    {
        return  DB::table('customers')
            ->select('customerName')
            ->where('idCustomer', $idCfp)->pluck('customerName')
            ->first();
    }

    public function getEtpProjectInter($idProjet, $idCfp_inter)
    {
        if ($idCfp_inter == null) {
            $etp = DB::table('v_projet_cfps')
                ->select('etp_name', 'etp_logo', 'etp_initial_name')
                ->where('idProjet', $idProjet)
                ->orderBy('etp_name', 'asc')
                ->get();
        } elseif ($idCfp_inter != null) {
            $etp = DB::table('v_list_entreprise_inter')
                ->select('etp_name', 'etp_logo', 'etp_initial_name')
                ->where('idProjet', $idProjet)
                ->where('etp_name', '!=', 'null')
                ->orderBy('etp_name', 'asc')
                ->get();
        }
        return $etp->toArray();
    }

    // CFP
    public function store(Request $req)
    {
        $req->validate([
            'dateSeance' => 'required|date',
            'heureDebut' => 'required',
            'heureFin' => 'required',
            'idProjet' => 'required',
        ]);

        if (isset($req->sessionCatchUp)) {
            switch ($req->sessionCatchUp) {
                case false:
                    $sessionCatchUp = 0;
                    break;
                case true:
                    $sessionCatchUp = 1;
                    break;
            }
        } else {
            $sessionCatchUp = 0;
        }
        $insert = DB::table('seances')->insertGetId([
            'dateSeance' => $req->dateSeance,
            'heureDebut' => $req->heureDebut,
            'heureFin' => $req->heureFin,
            'idProjet' => $req->idProjet,
            'session_catch_up' => $sessionCatchUp,
            'intervalle' => $req->intervalle,
        ], 'idSeance');

        $this->updateProjet($req->idProjet);

        return response()->json([
            'success' => $insert,
            'data' => [
                'dateSeance' => $req->dateSeance,
                'heureDebut' => $req->heureDebut,
                'heureFin' => $req->heureFin,
                'idProjet' => $req->idProjet,
                'session_catch_up' => $sessionCatchUp,
                'intervalle' => $req->intervalle,
            ]
        ]);
    }

    public function updateTypeSeance(Request $req, $idSeance)
    {
        try {
            $req->validate([
                'session_catch_up' => 'nullable|in:true,false',
                'is_report' => 'nullable|boolean',
                'report_date' => 'nullable|date',
                'heure_debut_reportee' => 'nullable|date_format:H:i',
                'heure_fin_reportee' => 'nullable|date_format:H:i',
                'is_undetermined' => 'nullable|boolean',
            ]);

            $sessionCatchUpValue = $req->input('session_catch_up') === 'true' ? 1 : 0;
            $seanceQuery = DB::table('seances as SE')
                ->join('projets as P', 'SE.idProjet', '=', 'P.idProjet')
                ->where('SE.idSeance', $idSeance)
                ->where('P.idCustomer', Customer::idCustomer());

            if (!$seanceQuery->exists()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Séance introuvable',
                ], 404);
            }

            $currentSeance = $seanceQuery->first();

            // LOG IMPORTANT POUR DEBUG
            \Log::info('UPDATE TYPE SEANCE - Données actuelles', [
                'idSeance' => $idSeance,
                'current_dateSeance' => $currentSeance->dateSeance,
                'current_heureDebut' => $currentSeance->heureDebut,
                'current_heureFin' => $currentSeance->heureFin,
                'current_original_date' => $currentSeance->original_date,
                'current_original_heure_debut' => $currentSeance->original_heure_debut,
                'current_original_heure_fin' => $currentSeance->original_heure_fin,
                'request_data' => $req->all()
            ]);

            $updateData = [
                'session_catch_up' => $sessionCatchUpValue,
                'updated_at' => Carbon::now(),
            ];

            // GESTION DES SESSIONS REPORTÉES - CORRIGÉE
            if ($req->has('is_report') && $req->boolean('is_report')) {
                $updateData['is_reported'] = true;

                if ($req->has('is_undetermined') && $req->boolean('is_undetermined')) {
                    // Report indéterminé
                    $updateData['is_report_undetermined'] = true;
                    $updateData['report_date'] = null;
                    $updateData['heure_debut_reportee'] = null;
                    $updateData['heure_fin_reportee'] = null;
                } else {
                    // Report avec date précise
                    $updateData['is_report_undetermined'] = false;

                    if ($req->filled('report_date')) {
                        $dateReport = $req->input('report_date');

                        try {
                            if (str_contains($dateReport, 'T')) {
                                $dateReport = explode('T', $dateReport)[0];
                            }

                            $carbonDate = Carbon::createFromFormat('Y-m-d', $dateReport);
                            if ($carbonDate->isValid()) {
                                $updateData['report_date'] = $dateReport;

                                // Sauvegarder les heures de report
                                if ($req->filled('heure_debut_reportee')) {
                                    $updateData['heure_debut_reportee'] = $req->input('heure_debut_reportee');
                                } else {
                                    $updateData['heure_debut_reportee'] = null;
                                }

                                if ($req->filled('heure_fin_reportee')) {
                                    $updateData['heure_fin_reportee'] = $req->input('heure_fin_reportee');
                                } else {
                                    $updateData['heure_fin_reportee'] = null;
                                }
                            } else {
                                throw new \Exception('Date de report invalide');
                            }
                        } catch (\Exception $e) {
                            return response()->json([
                                'status' => 400,
                                'message' => 'Date de report invalide: ' . $e->getMessage(),
                            ], 400);
                        }
                    } else {
                        return response()->json([
                            'status' => 400,
                            'message' => 'Une date de report est requise pour replanifier une session',
                        ], 400);
                    }
                }

                // CORRECTION CRITIQUE : Sauvegarder TOUJOURS les données originales
                // Ne pas vérifier si elles existent déjà - forcer la sauvegarde des vraies données actuelles
                $updateData['original_date'] = $currentSeance->dateSeance; // La vraie date originale
                $updateData['original_heure_debut'] = $currentSeance->heureDebut; // La vraie heure début originale
                $updateData['original_heure_fin'] = $currentSeance->heureFin; // La vraie heure fin originale

                \Log::info('Sauvegarde données originales', [
                    'original_date_saved' => $updateData['original_date'],
                    'original_heure_debut_saved' => $updateData['original_heure_debut'],
                    'original_heure_fin_saved' => $updateData['original_heure_fin']
                ]);
            } else {
                // RETOUR À SESSION NORMALE
                $updateData['is_reported'] = false;
                $updateData['is_report_undetermined'] = false;
                $updateData['report_date'] = null;
                $updateData['heure_debut_reportee'] = null;
                $updateData['heure_fin_reportee'] = null;

                // CORRECTION : Restaurer les données originales si elles existent
                if ($currentSeance->original_date) {
                    $updateData['dateSeance'] = $currentSeance->original_date;
                    $updateData['heureDebut'] = $currentSeance->original_heure_debut;
                    $updateData['heureFin'] = $currentSeance->original_heure_fin;

                    // Garder les données originales pour historique
                    // $updateData['original_date'] = null; // Optionnel : vider après restauration
                    // $updateData['original_heure_debut'] = null;
                    // $updateData['original_heure_fin'] = null;
                }
            }

            $updated = $seanceQuery->update($updateData);

            if ($updated) {
                // Récupérer les données mises à jour pour vérification
                $updatedSeance = $seanceQuery->first();
                \Log::info('Séance mise à jour - vérification', [
                    'idSeance' => $idSeance,
                    'new_dateSeance' => $updatedSeance->dateSeance,
                    'new_heureDebut' => $updatedSeance->heureDebut,
                    'new_heureFin' => $updatedSeance->heureFin,
                    'new_original_date' => $updatedSeance->original_date,
                    'new_original_heure_debut' => $updatedSeance->original_heure_debut,
                    'new_original_heure_fin' => $updatedSeance->original_heure_fin,
                    'new_report_date' => $updatedSeance->report_date,
                    'new_is_reported' => $updatedSeance->is_reported
                ]);

                return response()->json([
                    'status' => 200,
                    'message' => 'Type de séance modifié avec succès',
                    'debug' => [
                        'original_date' => $updatedSeance->original_date,
                        'original_heure_debut' => $updatedSeance->original_heure_debut,
                        'original_heure_fin' => $updatedSeance->original_heure_fin,
                        'report_date' => $updatedSeance->report_date
                    ]
                ], 200);
            } else {
                return response()->json([
                    'status' => 304,
                    'message' => 'Aucune modification effectuée',
                ], 304);
            }
        } catch (\Exception $e) {
            \Log::error('Erreur updateTypeSeance: ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'message' => 'Erreur serveur : ' . $e->getMessage(),
            ], 500);
        }
    }
    public function getAllSeances($idProjet)
    {
        try {
            // Solution temporaire : utiliser directement la table seances
            $seances = DB::table('seances as SE')
                ->select(
                    'SE.idSeance',
                    'SE.dateSeance',
                    'SE.id_google_seance',
                    'SE.is_reported',
                    'SE.report_date',
                    'SE.heure_debut_reportee',
                    'SE.heure_fin_reportee',
                    'SE.session_catch_up',
                    'SE.heureDebut',
                    'SE.heureFin',
                    'SE.original_date',        // Maintenant disponible
                    'SE.original_heure_debut', // Maintenant disponible
                    'SE.original_heure_fin',   // Maintenant disponible
                    'P.idSalle',
                    'P.idProjet',
                    'S.salle_name',
                    'L.li_quartier AS salle_quartier',
                    'P.project_title',
                    'P.project_description',
                    'P.idModule',
                    'M.moduleName AS module_name',
                    'V.ville'
                )
                ->join('projets as P', 'SE.idProjet', '=', 'P.idProjet')
                ->leftJoin('salles as S', 'P.idSalle', '=', 'S.idSalle')
                ->leftJoin('lieux as L', 'S.idLieu', '=', 'L.idLieu')
                ->leftJoin('ville_codeds as VC', 'P.idVilleCoded', '=', 'VC.id')
                ->leftJoin('villes as V', 'VC.idVille', '=', 'V.idVille')
                ->leftJoin('mdls as M', 'P.idModule', '=', 'M.idModule')
                ->where('P.idCustomer', Customer::idCustomer())
                ->where('SE.idProjet', $idProjet)
                ->get();

            if (count($seances) <= 0) {
                return response()->json([
                    'status' => 204,
                    'message' => 'Aucune séance trouvée'
                ], 204);
            }

            // Récupérer les autres données du projet une seule fois
            $projectFields = $this->getFieldsProject($idProjet);
            $formateurs = $this->getFormProject($idProjet);
            $apprenants = $this->getApprenantProject($idProjet);
            $etpNames = $this->getEtpProjectInter($idProjet, $this->idCfp());
            $nameCfp = $this->getNameCfp($this->idCfp());

            $events = [];

            foreach ($seances as $seance) {
                // 📌 Détermination de la date et heure à afficher
                $displayDate = $seance->is_reported && $seance->report_date
                    ? $seance->report_date
                    : $seance->dateSeance;

                $displayHeureDebut = $seance->is_reported && $seance->heure_debut_reportee
                    ? $seance->heure_debut_reportee
                    : $seance->heureDebut;

                $displayHeureFin = $seance->is_reported && $seance->heure_fin_reportee
                    ? $seance->heure_fin_reportee
                    : $seance->heureFin;

                // 📌 Construction de l'évènement
                $events[] = [
                    'id' => $seance->idSeance, // Utiliser idSeance comme id temporairement
                    'idSeance' => $seance->idSeance,
                    'idCfp' => $this->idCfp(),
                    'idEtp' => $projectFields->idEtp ?? null,
                    'start' => $displayDate . "T" . $displayHeureDebut,
                    'end' => $displayDate . "T" . $displayHeureFin,
                    'idProjet' => $seance->idProjet,
                    'idSalle' => $seance->idSalle,
                    'idModule' => $seance->idModule,
                    'text' => $seance->project_title,
                    'description' => $seance->project_description,
                    'idCalendar' => $seance->id_google_seance,
                    'salle' => $seance->salle_name,
                    'module' => $seance->module_name,
                    'ville' => $seance->ville,
                    'formateurs' => $formateurs,
                    'apprCount' => $apprenants,
                    'imgModule' => $projectFields->module_image ?? null,
                    'statut' => $projectFields->project_status ?? null,
                    'nameEtp' => $projectFields->etp_name ?? null,
                    'nameEtps' => $etpNames,
                    'paiementEtp' => $projectFields->paiement ?? null,
                    'typeProjet' => $projectFields->project_type ?? null,
                    'nameCfp' => $nameCfp,

                    // Champs importants - MAINTENANT DISPONIBLES
                    'session_catch_up' => $seance->session_catch_up,
                    'is_reported' => $seance->is_reported,
                    'report_date' => $seance->report_date,
                    'heure_debut_reportee' => $seance->heure_debut_reportee,
                    'heure_fin_reportee' => $seance->heure_fin_reportee,

                    // Données originales - MAINTENANT DISPONIBLES
                    'dateSeance' => $seance->dateSeance,
                    'heureDebut' => $seance->heureDebut,
                    'heureFin' => $seance->heureFin,
                    'original_date' => $seance->original_date,
                    'original_heure_debut' => $seance->original_heure_debut,
                    'original_heure_fin' => $seance->original_heure_fin,
                ];
            }

            return response()->json(['seances' => $events], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erreur serveur : ' . $e->getMessage()
            ], 500);
        }
    }

    public function getInfoSeances($idProjet)
    {
        $seances = DB::table('v_seances')
            ->select('idSeance', 'dateSeance', 'id_google_seance', 'heureDebut', 'heureFin', 'idSalle', 'idProjet', 'salle_name', 'salle_quartier', 'project_title', 'project_description', 'idModule', 'module_name', 'ville')
            ->where('idCfp', $this->idCfp())
            ->where('idProjet', $idProjet)
            ->groupBy('idProjet')
            ->get();

        if (count($seances) > 0) {
            /** @var Seance $seance */
            foreach ($seances as $seance) {
                $event[] =  [

                    'end' => $seance->dateSeance . "T" . $seance->heureFin,
                    'start' => $seance->dateSeance . "T" . $seance->heureDebut,
                    'idProjet' => $seance->idProjet,
                    'idSalle' => $seance->idSalle,
                    'idModule' => $seance->idModule,
                    'text' => $seance->project_title,
                    'description' => $seance->project_description,
                    'idCalendar' => $seance->id_google_seance,      //id reliant à Google calendar
                    'salle' => $seance->salle_name,
                    'module' => $seance->module_name,
                    'ville' => $seance->ville,
                    'formateurs' => $this->getFormProject($seance->idProjet),
                    'apprCount' => $this->getApprenantProject($seance->idProjet),
                    'imgModule' => $this->getFieldsProject($seance->idProjet)->module_image,
                    'statut' => $this->getFieldsProject($seance->idProjet)->project_status,
                    'nameEtp' => $this->getFieldsProject($seance->idProjet)->etp_name,
                    'nameEtps' => $this->getEtpProjectInter($seance->idProjet, $this->idCfp()),
                    'paiementEtp' => $this->getFieldsProject($seance->idProjet)->paiement,
                    'typeProjet' => $this->getFieldsProject($seance->idProjet)->project_type,

                ];
            }
        } else {
            return response()->json(['pas de donnee']);
        }
        return response()->json(['seance' => $event]);
    }

    public function getFormProject($idProjet)
    {
        $forms = DB::table('v_formateur_cfps')
            ->select('idFormateur', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'initialNameForm AS form_initial_name', 'email')
            ->groupBy('idFormateur', 'name', 'firstName', 'photoForm', 'initialNameForm')
            ->where('idProjet', $idProjet)->get();

        return $forms->toArray();
    }

    public function getApprenantProject($idProjet)
    {
        $apprs = DB::table('v_list_apprenants')
            ->select('idEmploye', 'emp_initial_name', 'emp_name', 'emp_firstname', 'emp_email', 'emp_photo', 'emp_matricule', 'emp_phone', 'etp_name')
            ->where('idProjet', $idProjet)
            ->orderBy('emp_name', 'asc')
            ->get();

        return count($apprs);
    }

    public function getFieldsProject($idProjet)
    {

        $projet = DB::table('v_projet_cfps')
            ->select('idProjet', 'dateDebut', 'dateFin', 'project_title', 'etp_name', 'ville', 'project_status', 'project_type', 'module_image', 'paiement', 'project_reference', 'modalite', 'idEtp')
            ->where('idProjet', $idProjet)
            ->first();
        return $projet;
    }

    public function update(Request $req, $idSeance)
    {
        /*$req->validate([
            'dateSeance' => 'required|after_or_equal:today',
            'heureDebut' => 'required',
            'heureFin' => 'required|after:heureDebut',
            //'idFormateur' => 'required',
        ]);*/

        $seance = DB::table('seances')->where('idSeance', $idSeance);

        if ($seance->exists()) {
            DB::transaction(function () use ($seance, $req, $idSeance) {
                $seance->update([
                    'dateSeance' => Carbon::parse($req->dateSeance)->format('Y-m-d'),
                    'heureDebut' => $req->heureDebut,
                    'heureFin' =>   $req->heureFin,
                    //'id_google_seance' => $req->id_google_seance,
                ]);

                $idProjet = DB::table('seances')->where('idSeance', $idSeance)->value('idProjet');
                $this->updateProjet($idProjet);
            });

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
    }

    public function destroy($idSeance)
    {
        $queryPresence = DB::table('emargements')->where('idSeance', $idSeance);

        $checkPresence = $queryPresence->get();
        $idProjet = DB::table('seances')->where('idSeance', $idSeance)->value('idProjet');
        $projet_count = DB::table('seances')->where('idProjet', $idProjet)->get();

        try {
            if (count($checkPresence) < 1) {
                DB::table('seances')->where('idSeance', $idSeance)->delete();
                if (count($projet_count) > 1) {
                    $this->updateProjet($idProjet);
                }
                return response()->json(['success' => 'Succès']);
            } elseif (count($checkPresence) > 0) {
                DB::beginTransaction();
                $queryPresence->delete();
                DB::table('seances')->where('idSeance', $idSeance)->delete();
                DB::commit();
                if (count($projet_count) > 1) {
                    $this->updateProjet($idProjet);
                }
                return response()->json(['success' => 'Succès']);
            }
        } catch (Exception $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }
    // Récupère le dernier élément de la table seances
    public function getLastFieldSeances()
    {

        $lastRecord = DB::table('seances')->latest('idSeance')->first();

        return response()->json(['seance' => $lastRecord]);
    }

    // Récupère le dernier élément de la vue v_seances
    public function getLastFieldVueSeances()
    {

        $lastVueSeance = DB::table('v_seances')->latest('idSeance')->first();

        return response()->json(['seance' => $lastVueSeance]);
    }

    public function getAll($idProjet)
    {
        $seances = DB::table('v_seances')
            ->select('idProjet', 'idSeance', 'dateSeance', 'heureDebut', 'heureFin', 'initialNameForm', 'nameForm', 'firstNameForm', 'photoForm', 'nomSalle', 'quartier', 'ville', 'moduleName')
            ->where('idCfp', $this->idCfp())
            ->where('idProjet', $idProjet)
            ->get();

        return response()->json(['seances' => $seances]);
    }

    // ETP
    public function getAllEtp($idProjet)
    {
        $seances = DB::table('v_union_seanceEtps')
            ->select('idProjet', 'idSeance', 'dateSeance', 'heureDebut', 'heureFin', 'initialNameForm', 'nameForm', 'firstNameForm', 'photoForm', 'salle', 'quartier', 'ville', 'moduleName')
            ->where('idEtp', $this->idCfp())
            ->where('idProjet', $idProjet)
            ->get();

        return response()->json(['seances' => $seances]);
    }

    public function getInsert()
    {
        $foramteurs = DB::table('v_formateur_cfps')
            ->select('idFormateur', 'name', 'firstName')
            ->where('idCfp', $this->idCfp())
            ->where('isActiveCfp', 1)
            ->where('isActiveFormateur', 1)
            ->get();

        $salles = DB::table('villes')
            ->join('salles', 'salles.idVille', 'villes.idVille')
            ->select('salles.idSalle', 'salles.nomSalle', 'villes.ville')
            ->where('idCustomer', $this->idCfp())
            ->get();

        return response()->json([
            'formateurs' => $foramteurs,
            'salles' => $salles
        ]);
    }

    public function edit($idSeance)
    {
        $seance = DB::table('seances')
            ->select('idProjet', 'idSeance', 'dateSeance', 'heureDebut', 'heureFin')
            ->where('idSeance', $idSeance)
            ->first();

        $formateurs = DB::table('v_formateur_cfps')
            ->select('idFormateur', 'name', 'firstName')
            ->where('idCfp', $this->idCfp())
            ->where('isActiveCfp', 1)
            ->where('isActiveFormateur', 1)
            ->get();

        $salles = DB::table('villes')
            ->join('salles', 'salles.idVille', 'villes.idVille')
            ->select('salles.idSalle', 'salles.nomSalle', 'villes.ville')
            ->where('idCustomer', $this->idCfp())
            ->get();

        return response()->json([
            'seance' => $seance,
            'formateurs' => $formateurs,
            'salles' => $salles
        ]);
    }

    public function getSeanceAndTotalTime($idProjet)
    {
        $seances = DB::table('v_seances')
            ->select('idSeance', 'dateSeance', 'heureDebut', 'heureFin', 'idProjet', 'idModule', DB::raw("TIME_FORMAT(SEC_TO_TIME(TIME_TO_SEC(intervalle_raw)), '%H:%i') AS intervalle_raw"))
            ->where('idProjet', $idProjet)
            ->orderBy('dateSeance', 'asc')
            ->get();
        $nbSeance = count($seances);

        $totalSession = DB::table('v_seances')
            ->selectRaw("IFNULL(TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(intervalle_raw))), '%H:%i'), '00:00') as sumHourSession")
            ->where('idProjet', $idProjet)
            ->groupBy('idProjet')
            ->first();

        return response()->json([
            //'seances' => $seances,
            'nbSeance' => $nbSeance,
            'totalSession' => $totalSession
        ]);
    }

    public function sendInvitationCalendar(Request $req)
    {
        try {

            $emailRefs = $req->referents;
            $emailForms = $req->forms;

            // Mail::to($inviterName1)->send(new SendInvitationCalendar($inviterName1));
            // Mail::to($inviterName2)->send(new SendInvitationCalendar($inviterName1));
            foreach ($emailRefs as $email) {
                Mail::to($email)->send(new SendInvitationCalendar($email));
            }
            foreach ($emailForms as $email) {
                Mail::to($email)->send(new SendInvitationCalendar($email));
            }
            return response([

                'status' => 200,
                'message' => "Invitation envoyée avec succès",
                //'to_email' => $req->email,
                'to_email_ref' => $emailRefs,
                'to_email_form' => $emailForms,

            ]);
        } catch (Exception $e) {
            //DB::rollBack();
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function updateIdCalendarLastSession(Request $req)
    {

        $lastSeance = DB::table('seances')
            ->latest('idSeance')
            ->first();

        if ($lastSeance) {
            $update = DB::table('seances')
                ->where('idSeance', $lastSeance->idSeance)
                ->update([
                    'id_google_seance' => $req->idCalendar
                ]);

            return response()->json([
                "success" => "Succès",
                "idCalendar" => $req->idCalendar,
                "update" => $update,
            ]);
        }

        return response()->json([
            "error" => "Aucune séance trouvée",
            "idCalendar" => $req->idCalendar,
        ], 404);
    }

    public function updateIdListCalendarSession(Request $req)
    {
        $seance =  DB::table('seances')->where('idSeance', $req->idSeance);

        if ($seance->exists()) {
            $seance->update([
                'id_google_seance' => $req->idGoogle,
            ]);

            return response()->json([
                "status" => 200,
                'message' => 'Succès',
                "idGoogle" => $req->idGoogle
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Introuvable !'
            ], 404);
        }
    }

    public function getFieldVueSeanceOfId($idSeance)
    {
        $seance = DB::table('v_seances')->where('idSeance', $idSeance)->first();
        return response()->json(['seance' => $seance]);
    }

    // Session report
    public function test()
    {
        return response()->json([
            'message' => 'Route test OK ✅'
        ]);
    }

    public function reportSession(Request $request, $seanceId)
    {
        $request->validate([
            'date_report' => 'nullable|date|after_or_equal:now',
            'is_undetermined' => 'required|boolean'
        ]);

        if ($request->is_undetermined == true) {
            $dateReport = Carbon::now();
            $reportIsUndetermined = true;
        } elseif ($request->is_undetermined == false) {
            $dateReport = $request->date_report;
            $reportIsUndetermined = false;
        }

        $seance = DB::table('seances as SE')
            ->join('projets as P', 'SE.idProjet', 'P.idProjet')
            ->where('SE.idSeance', $seanceId)
            ->where('P.idCustomer', Customer::idCustomer());

        if (!$seance->exists()) {
            return response()->json([
                'status' => 204,
                'message' => 'Seance introuvable !'
            ], 204);
        }

        $seance->update([
            'dateSeance' => $dateReport,
            'is_reported' => true,
            'is_report_undetermined' => $reportIsUndetermined
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Succès'
        ], 200);
    }
}
