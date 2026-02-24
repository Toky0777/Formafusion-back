<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class WorkingDaysPolicyController extends Controller
{
    /**
     * 🟢 Récupérer la politique des jours travaillés
     */
 public function index()
{
    try {
        $idCfp = Customer::idCustomer();
        
        // Récupérer la politique pour le CFP courant, sinon la politique par défaut
        $policy = DB::table('working_days_policy')
                    ->where('idCfp', $idCfp)
                    ->orWhereNull('idCfp')
                    ->orderBy('idCfp', 'DESC') // Priorité à la politique spécifique du CFP
                    ->first();

        // Politique par défaut (semaine standard Lundi-Vendredi)
        $defaultPolicy = [
            'id' => 1,
            'sunday' => false,
            'monday' => true,
            'tuesday' => true,
            'wednesday' => true,
            'thursday' => true,
            'friday' => true,
            'saturday' => false,
            'idCfp' => null,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString()
        ];

        // Si aucune donnée trouvée, retourner la politique par défaut
        if (!$policy) {
            return response()->json($defaultPolicy);
        }

        // Retourne la politique existante
        return response()->json([
            'id' => $policy->id,
            'sunday' => (bool) $policy->sunday,
            'monday' => (bool) $policy->monday,
            'tuesday' => (bool) $policy->tuesday,
            'wednesday' => (bool) $policy->wednesday,
            'thursday' => (bool) $policy->thursday,
            'friday' => (bool) $policy->friday,
            'saturday' => (bool) $policy->saturday,
            'idCfp' => $policy->idCfp,
            'created_at' => $policy->created_at,
            'updated_at' => $policy->updated_at
        ]);
    } catch (\Exception $e) {
        // Même en cas d'erreur, retourner une politique par défaut
        return response()->json([
            'id' => 1,
            'sunday' => false,
            'monday' => true,
            'tuesday' => true,
            'wednesday' => true,
            'thursday' => true,
            'friday' => true,
            'saturday' => false,
            'idCfp' => null,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
            'error_note' => 'Erreur survenue, valeurs par défaut utilisées'
        ]);
    }
}

/**
 * 🟡 Créer ou mettre à jour la politique des jours travaillés
 */
public function createOrUpdate(Request $request)
{
    try {
        $idCfp = Customer::idCustomer();
        
        // Validation des champs booléens
        $validated = $request->validate([
            'monday' => 'boolean',
            'tuesday' => 'boolean',
            'wednesday' => 'boolean',
            'thursday' => 'boolean',
            'friday' => 'boolean',
            'saturday' => 'boolean',
            'sunday' => 'boolean',
        ]);

        // Vérifier si une politique existe déjà pour ce CFP
        $existingPolicy = DB::table('working_days_policy')
                            ->where('idCfp', $idCfp)
                            ->first();

        if ($existingPolicy) {
            // Mise à jour de la politique existante
            DB::table('working_days_policy')
                ->where('id', $existingPolicy->id)
                ->update(array_merge($validated, [
                    'updated_at' => now(),
                ]));

            $updatedPolicy = DB::table('working_days_policy')
                                ->where('id', $existingPolicy->id)
                                ->first();

            return response()->json([
                'message' => 'Politique mise à jour avec succès',
                'data' => $updatedPolicy
            ], 200);
        } else {
            // Création d'une nouvelle politique pour ce CFP
            $id = DB::table('working_days_policy')->insertGetId(array_merge($validated, [
                'idCfp' => $idCfp,
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            $newPolicy = DB::table('working_days_policy')
                            ->where('id', $id)
                            ->first();

            return response()->json([
                'message' => 'Politique créée avec succès',
                'data' => $newPolicy
            ], 201);
        }

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Erreur lors de la sauvegarde de la politique',
            'message' => $e->getMessage(),
        ], 500);
    }
}

/**
 * 🟡 Modifier la politique des jours travaillés (pour une mise à jour spécifique par ID)
 */
public function update(Request $request, $id)
{
    try {
        $idCfp = Customer::idCustomer();
        
        // Validation des champs booléens
        $validated = $request->validate([
            'monday' => 'boolean',
            'tuesday' => 'boolean',
            'wednesday' => 'boolean',
            'thursday' => 'boolean',
            'friday' => 'boolean',
            'saturday' => 'boolean',
            'sunday' => 'boolean',
        ]);

        // Vérifie si la politique existe et appartient au CFP courant
        $policy = DB::table('working_days_policy')
                    ->where('id', $id)
                    ->where('idCfp', $idCfp)
                    ->first();

        if (!$policy) {
            return response()->json([
                'message' => 'Politique non trouvée ou vous n\'avez pas les droits pour la modifier'
            ], 404);
        }

        // Met à jour les valeurs
        DB::table('working_days_policy')
            ->where('id', $id)
            ->update(array_merge($validated, [
                'updated_at' => now(),
            ]));

        $updatedPolicy = DB::table('working_days_policy')
                            ->where('id', $id)
                            ->first();

        return response()->json([
            'message' => 'Politique mise à jour avec succès',
            'data' => $updatedPolicy
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Erreur lors de la mise à jour de la politique',
            'message' => $e->getMessage(),
        ], 500);
    }
}

public function sessionFormateurDeuxDate(Request $request){
     try {

         $startDate = $request->startDate;
         $endDate = $request->endDate;

          if (!$startDate || !$endDate) {
            return response()->json(['error' => 'Les dates de début et de fin sont requises.'], 400);
        }

            $seances = DB::table('v_seances as s')
            ->join('v_projet_cfps as p', 's.idProjet', '=', 'p.idProjet')
            ->leftJoin('v_formateur_cfps as f', 'f.idProjet', '=', 'p.idProjet')
            ->where('p.module_name', '!=', 'Default module')
            ->where('p.project_is_active', 1)
            ->whereBetween('s.dateSeance', [$startDate, $endDate]) 
            ->where('s.idCfp', Customer::idCustomer())
            ->select(
                's.idSeance',
                's.dateSeance',
                's.heureDebut',
                's.heureFin',
                's.project_status',
                'p.idProjet',
                'p.module_name',
                'p.ville',
                'p.salle_quartier',
                'p.etp_name',
                'f.idFormateur',
                'f.name as formateur_name',
                'f.firstName as formateur_firstName',
                'f.form_phone as formateur_phone',
                DB::raw('TIMESTAMPDIFF(MINUTE, s.heureDebut, s.heureFin) as duration_minutes'),
                DB::raw('(SELECT COUNT(*) FROM detail_apprenants da WHERE da.idProjet = p.idProjet) as nb_apprenants')
            )
            ->orderBy('s.dateSeance', 'asc')
            ->orderBy('s.heureDebut', 'asc')
            ->get();

        // Formater les séances avec des informations supplémentaires
        $seancesCompletes = $seances->map(function ($seance) {
            return [
                'idSeance' => $seance->idSeance,
                'dateSeance' => $seance->dateSeance,
                'heureDebut' => $seance->heureDebut,
                'heureFin' => $seance->heureFin,
                'duree_heures' => round($seance->duration_minutes / 60, 2),
                'idProjet' => $seance->idProjet,
                'module_name' => $seance->module_name,
                'status' => $seance->project_status,
                'ville' => $seance->ville,
                'salle_quartier' => $seance->salle_quartier,
                'etp_name' => $seance->etp_name,
                'idFormateur' => $seance->idFormateur,
                'formateur_nom' => $seance->formateur_name,
                'formateur_prenom' => $seance->formateur_firstName,
                'formateur_complet' => $seance->formateur_name . ' ' . $seance->formateur_firstName,
                'formateur_phone' => $seance->formateur_phone,
                'nb_apprenants' => $seance->nb_apprenants
            ];
        });
        
        return response()->json([
            'success' => true,
            'seances' => $seancesCompletes
        ], 200);

    } catch (\Exception $e) {
       
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération des séances formateurs.',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function sessionFormateurUneDate(Request $request)
{
    try {
        $sessionDate = $request->query('sessionDate');

        if (!$sessionDate) {
            return response()->json(['error' => 'La date de session est requise.'], 400);
        }

        // Récupérer toutes les séances avec DISTINCT
        $seances = DB::table('v_seances as s')
            ->join('v_projet_cfps as p', 's.idProjet', '=', 'p.idProjet')
            ->leftJoin('v_formateur_cfps as f', 'f.idProjet', '=', 'p.idProjet')
            ->where('p.module_name', '!=', 'Default module')
            ->where('p.project_is_active', 1)
            ->where('s.dateSeance', $sessionDate)
            ->where('s.idCfp', Customer::idCustomer())
            ->select(
                's.idSeance',
                's.dateSeance',
                's.heureDebut',
                's.heureFin',
                's.project_status',
                'p.idProjet',
                'p.module_name',
                'p.ville',
                'p.salle_quartier',
                'p.etp_name',
                'f.idFormateur',
                'f.name as formateur_name',
                'f.firstName as formateur_firstName',
                'f.form_phone as formateur_phone',
                DB::raw('TIMESTAMPDIFF(MINUTE, s.heureDebut, s.heureFin) as duration_minutes'),
                DB::raw('(SELECT COUNT(*) FROM detail_apprenants da WHERE da.idProjet = p.idProjet) as nb_apprenants')
            )
            ->distinct() // ← AJOUT IMPORTANT ICI
            ->orderBy('s.heureDebut', 'asc')
            ->get();

        // Formater les séances avec des informations supplémentaires
        $seancesCompletes = $seances->map(function ($seance) {
            return [
                'idSeance' => $seance->idSeance,
                'dateSeance' => $seance->dateSeance,
                'heureDebut' => $seance->heureDebut,
                'heureFin' => $seance->heureFin,
                'duree_heures' => round($seance->duration_minutes / 60, 2),
                'idProjet' => $seance->idProjet,
                'module_name' => $seance->module_name,
                'status' => $seance->project_status,
                'ville' => $seance->ville,
                'salle_quartier' => $seance->salle_quartier,
                'etp_name' => $seance->etp_name,
                'formateur_id' => $seance->idFormateur,
                'formateur_nom' => $seance->formateur_name,
                'formateur_prenom' => $seance->formateur_firstName,
                'formateur_complet' => $seance->formateur_name . ' ' . $seance->formateur_firstName,
                'formateur_phone' => $seance->formateur_phone,
                'nb_apprenants' => $seance->nb_apprenants
            ];
        });

        // Statistiques simples pour information
        $stats = [
            'total_seances' => $seances->count(),
            'total_formateurs_travaillant' => $seances->where('idFormateur', '!=', null)->pluck('idFormateur')->unique()->count(),
            'total_heures_formation' => round($seances->sum('duration_minutes') / 60, 2)
        ];

        return response()->json([
            'success' => true,
            'seances' => $seancesCompletes,
            'statistiques' => $stats,
            'date_session' => $sessionDate
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération des séances formateurs.',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function dayAvailabilityFormateur(Request $request){
    try {
        $startDate = $request->startDate;
        $endDate = $request->endDate;

        if (!$startDate || !$endDate) {
            return response()->json(['error' => 'Les dates de début et de fin sont requises.'], 400);
        }

        // 🔹 1. Récupérer le nombre total de formateurs du CFP
        $totalTrainersCount = DB::table('v_formateur_cfps')
            ->where('idCfp', Customer::idCustomer())
            ->distinct('idFormateur')
            ->count('idFormateur');

        // 🔹 2. Récupérer toutes les séances avec calcul des heures
        $seances = DB::table('v_seances as s')
            ->join('v_projet_cfps as p', 's.idProjet', '=', 'p.idProjet')
            ->leftJoin('v_formateur_cfps as f', 'f.idProjet', '=', 'p.idProjet')
            ->where('p.module_name', '!=', 'Default module')
            ->where('p.project_is_active', 1)
            ->where('s.idCfp', Customer::idCustomer())
            ->whereBetween('s.dateSeance', [$startDate, $endDate]) 
            ->select(
                's.idSeance',
                's.dateSeance',
                's.heureDebut',
                's.heureFin',
                'f.idFormateur',
                'f.name as formateur_name',
                'f.firstName as formateur_firstName',
                'p.module_name',
                DB::raw('TIMESTAMPDIFF(MINUTE, s.heureDebut, s.heureFin) as duration_minutes')
            )
            ->orderBy('s.dateSeance', 'asc')
            ->orderBy('s.heureDebut', 'asc')
            ->get();

        // 🔹 3. Grouper par date et calculer la moyenne des pourcentages d'utilisation
        $dailyAvailability = $seances->groupBy('dateSeance')->map(function ($sessions, $date) use ($totalTrainersCount) {
            $HOURS_PER_DAY = 6; // 6 heures maximum par formateur par jour
            
            // Grouper les séances par formateur avec leurs modules détaillés
            $trainerDetails = $sessions->where('idFormateur', '!=', null)
                                        ->groupBy('idFormateur')
                                        ->map(function ($trainerSessions) use ($HOURS_PER_DAY) {
                // Calculer les heures totales
                $totalMinutes = $trainerSessions->sum('duration_minutes');
                $totalHours = round($totalMinutes / 60, 2);
                
                // Récupérer les modules avec les heures détaillées
                $modulesDetails = $trainerSessions->groupBy('module_name')->map(function ($moduleSessions, $moduleName) {
                    return $moduleSessions->map(function ($session) {
                        return [
                            'heure_debut' => $session->heureDebut,
                            'heure_fin' => $session->heureFin,
                            'duree_heures' => round($session->duration_minutes / 60, 2),
                            'seance_id' => $session->idSeance
                        ];
                    });
                });
                
                // Liste des modules uniques pour un affichage simple
                $modulesList = $trainerSessions->pluck('module_name')->unique()->values();
                
                // Calculer le pourcentage d'utilisation
                $utilizationPercentage = min(100, round(($totalHours / $HOURS_PER_DAY) * 100, 2));
                
                return [
                    'formateur_id' => $trainerSessions->first()->idFormateur,
                    'formateur_nom' => $trainerSessions->first()->formateur_name,
                    'formateur_prenom' => $trainerSessions->first()->formateur_firstName,
                    'formateur_complet' => $trainerSessions->first()->formateur_name . ' ' . $trainerSessions->first()->formateur_firstName,
                    'heures_travaillees' => $totalHours,
                    'pourcentage_utilisation' => $utilizationPercentage,
                    'modules_enseignes' => $modulesList,
                    'modules_details' => $modulesDetails,
                    'nombre_seances' => $trainerSessions->count()
                ];
            });
            
            // Calculer la SOMME des pourcentages d'utilisation
            $totalPercentageSum = $trainerDetails->sum('pourcentage_utilisation');
            
            // Calculer la moyenne : SOMME des pourcentages / NOMBRE TOTAL de formateurs du CFP
            $averageUtilization = $totalTrainersCount > 0 
                ? round($totalPercentageSum / $totalTrainersCount, 2)
                : 0;

            return [
                'date' => $date,
                'total_trainers_working' => $trainerDetails->count(),
                'total_trainers_cfp' => $totalTrainersCount,
                'utilization_percentage' => $averageUtilization,
                'target_hours_per_day' => $HOURS_PER_DAY,
                'formateurs_details' => $trainerDetails->values(),
                'details_calcul' => [
                    'trainers_working_count' => $trainerDetails->count(),
                    'total_percentage_sum' => $totalPercentageSum,
                    'average_calculation' => $totalTrainersCount > 0 ? 
                        $totalPercentageSum . ' / ' . $totalTrainersCount . ' = ' . $averageUtilization . '%' :
                        'Aucun formateur dans le CFP'
                ]
            ];
        })->values();

        // ✅ 4. Retourner tout au format JSON
        return response()->json([
            'success' => true,
            'dailyAvailability' => $dailyAvailability,
            'summary' => [
                'total_trainers_in_cfp' => $totalTrainersCount,
                'period' => $startDate . ' to ' . $endDate,
                'days_analyzed' => $dailyAvailability->count()
            ]
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération des disponibilités formateurs.',
            'error' => $e->getMessage()
        ], 500);
    }
}






}
