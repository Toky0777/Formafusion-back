<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class DisponibileMatController extends Controller
{
public function sessionMaterielUneDate(Request $request)
{
    try {
        $sessionDate = $request->query('sessionDate');

        if (!$sessionDate) {
            return response()->json(['error' => 'La date de session est requise.'], 400);
        }

        // D'abord, récupérer toutes les séances de la date
        $seances = DB::table('v_seances_materiel as s')
            ->join('v_projet_cfps as p', 's.idProjet', '=', 'p.idProjet')
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
                's.project_title',
                's.ville',
                'p.idProjet',
                'p.module_name',
                'p.salle_quartier',
                'p.etp_name',
                DB::raw('TIMESTAMPDIFF(MINUTE, s.heureDebut, s.heureFin) as duration_minutes')
            )
            ->distinct()
            ->orderBy('s.heureDebut', 'asc')
            ->get();

        // Ensuite, pour chaque séance, récupérer les matériels
        $seancesWithMateriels = $seances->map(function ($seance) {
            $materiels = DB::table('v_seances_materiel')
                ->where('idSeance', $seance->idSeance)
                ->whereNotNull('materiel_name')
                ->select(
                    'materiel_name',
                    'material_number',
                    'material_stock',
                    'material_types'
                )
                ->get();

            return [
                'seance_info' => [
                    'idSeance' => $seance->idSeance,
                    'dateSeance' => $seance->dateSeance,
                    'heureDebut' => $seance->heureDebut,
                    'heureFin' => $seance->heureFin,
                    'duration_minutes' => $seance->duration_minutes,
                    'project_status' => $seance->project_status,
                    'project_title' => $seance->project_title,
                    'ville' => $seance->ville,
                    'idProjet' => $seance->idProjet,
                    'module_name' => $seance->module_name,
                    'salle_quartier' => $seance->salle_quartier,
                    'etp_name' => $seance->etp_name,
                ],
                'materiels' => $materiels
            ];
        });

        return response()->json([
            'success' => true,
            'seances' => $seancesWithMateriels,
            'total_seances' => $seancesWithMateriels->count(),
            'total_materiels' => $seancesWithMateriels->sum(function ($seance) {
                return $seance['materiels']->count();
            }),
            'date_session' => $sessionDate
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération des matériels par séance.',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function getEquipmentStatusSimple(Request $request)
{
    try {
        $currentDate = now()->format('Y-m-d');
        $tomorrow = now()->addDay()->format('Y-m-d');

        // D'abord, récupérer tous les matériels
        $materials = DB::table('materials as m')
            ->select('m.id', 'm.name', 'mt.name as type_name', 'm.stock_number')
            ->leftJoin('material_types as mt', 'm.material_type_id', '=', 'mt.id')
            ->where('m.customer_id', Customer::idCustomer())
            ->get();

        $formattedEquipment = [];

        foreach ($materials as $material) {
            // Récupérer la prochaine réservation FUTURE pour ce matériel
            $nextReservation = DB::table('project_materials as pm')
                ->join('seances as s', 'pm.project_id', '=', 's.idProjet')
                ->join('projets as p', 'pm.project_id', '=', 'p.idProjet')
                ->where('pm.material_id', $material->id)
                ->where('s.dateSeance', '>=', $currentDate) // Seulement les réservations futures
                ->orderBy('s.dateSeance', 'asc')
                ->orderBy('s.heureDebut', 'asc')
                ->select(
                    's.dateSeance', 
                    's.heureDebut',
                    'p.project_title',
                    'p.project_reference'
                )
                ->first();

            // Récupérer les réservations en cours (aujourd'hui)
            $currentReservation = DB::table('project_materials as pm')
                ->join('seances as s', 'pm.project_id', '=', 's.idProjet')
                ->where('pm.material_id', $material->id)
                ->where('s.dateSeance', '=', $currentDate) // Réservations aujourd'hui
                ->first();

            // Récupérer le nombre total de réservations FUTURES
            $futureReservationCount = DB::table('project_materials as pm')
                ->join('seances as s', 'pm.project_id', '=', 's.idProjet')
                ->where('pm.material_id', $material->id)
                ->where('s.dateSeance', '>=', $currentDate)
                ->count();

            // CORRECTION : Déterminer le statut basé sur les réservations réelles
            $status = 'disponible';
            
            if ($currentReservation) {
                // Si le matériel est utilisé aujourd'hui
                $status = 'en-cours';
            } elseif ($futureReservationCount > 0) {
                // Si le matériel est réservé pour le futur
                $status = 'réservé';
            }
            // Sinon, il reste "disponible"

            // Déterminer la prochaine session et date de réservation
            $nextSession = null;
            $reservationDate = null;
            $reservationFullDate = null;
            
            if ($nextReservation) {
                if ($nextReservation->dateSeance === $currentDate) {
                    $nextSession = "Aujourd'hui " . substr($nextReservation->heureDebut, 0, 5);
                    $reservationDate = "Aujourd'hui";
                    $reservationFullDate = $nextReservation->dateSeance;
                } elseif ($nextReservation->dateSeance === $tomorrow) {
                    $nextSession = "Demain " . substr($nextReservation->heureDebut, 0, 5);
                    $reservationDate = "Demain";
                    $reservationFullDate = $nextReservation->dateSeance;
                } else {
                    $sessionDate = new DateTime($nextReservation->dateSeance);
                    $nextSession = $sessionDate->format('d M H:i');
                    $reservationDate = $sessionDate->format('d/m/Y');
                    $reservationFullDate = $nextReservation->dateSeance;
                }
            }

            // Mapping des types
            $typeMapping = [
                'Matériel informatique' => 'laptop',
                'Ordinateur' => 'ordinateur', 
                'Projecteur' => 'projecteur',
                'Écran' => 'ecran',
                'Tablette' => 'tablette',
            ];

            $formattedEquipment[] = [
                'name' => $material->name,
                'type' => $typeMapping[$material->type_name] ?? 'autre',
                'status' => $status,
                'location' => 'Entrepôt principal',
                'nextSession' => $nextSession,
                'reservationDate' => $reservationDate, // Date de réservation formatée
                'reservationFullDate' => $reservationFullDate, // Date complète Y-m-d
                'reservationCount' => $futureReservationCount, // Nombre total de réservations futures
                'nextProject' => $nextReservation ? $nextReservation->project_title : null,
                'hasCurrentReservation' => (bool)$currentReservation, // Réservation aujourd'hui
                'hasFutureReservation' => $futureReservationCount > 0 // Réservations futures
            ];
        }

        return response()->json([
            'success' => true,
            'equipment' => $formattedEquipment
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération des équipements.',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function dayAvailabilityMaterielTimeBased(Request $request)
{
    try {
        $startDate = $request->startDate;
        $endDate = $request->endDate;

        if (!$startDate || !$endDate) {
            return response()->json(['error' => 'Les dates de début et de fin sont requises.'], 400);
        }

        // 🔹 1. Récupérer le nombre total de matériels du CFP
        $totalMaterialsCount = DB::table('materials')
            ->where('customer_id', Customer::idCustomer())
            ->count();

        // 🔹 2. Récupérer toutes les séances avec les matériels utilisés
        $seances = DB::table('v_seances_materiel as s')
            ->join('v_projet_cfps as p', 's.idProjet', '=', 'p.idProjet')
            ->where('p.module_name', '!=', 'Default module')
            ->where('p.project_is_active', 1)
            ->where('s.idCfp', Customer::idCustomer())
            ->whereBetween('s.dateSeance', [$startDate, $endDate])
            ->whereNotNull('s.materiel_name')
            ->select(
                's.idSeance',
                's.dateSeance',
                's.heureDebut',
                's.heureFin',
                's.materiel_name',
                's.material_number',
                's.material_stock',
                's.material_types',
                'p.module_name',
                'p.project_title',
                DB::raw('TIMESTAMPDIFF(MINUTE, s.heureDebut, s.heureFin) as duration_minutes')
            )
            ->orderBy('s.dateSeance', 'asc')
            ->orderBy('s.heureDebut', 'asc')
            ->get();

        // 🔹 3. Grouper par date et calculer le pourcentage d'utilisation basé sur le temps
        $dailyAvailability = $seances->groupBy('dateSeance')->map(function ($sessions, $date) use ($totalMaterialsCount) {
            // Heures d'ouverture par jour (8h-18h = 10 heures)
            $HOURS_PER_DAY = 8;
            $MINUTES_PER_DAY = $HOURS_PER_DAY * 60;

            // Grouper les séances par matériel
            $materialDetails = $sessions->groupBy('materiel_name')->map(function ($materialSessions, $materialName) use ($MINUTES_PER_DAY) {
                // Calculer le temps total d'utilisation en minutes
                $totalMinutesUsed = $materialSessions->sum('duration_minutes');
                
                // Calculer le pourcentage d'utilisation du temps
                $timeUtilizationPercentage = min(100, round(($totalMinutesUsed / $MINUTES_PER_DAY) * 100, 2));

                // Calculer les unités utilisées
                $totalUnitsUsed = $materialSessions->sum('material_number');
                $availableStock = $materialSessions->first()->material_stock ?? 0;
                
                // Pourcentage d'utilisation des unités
                $unitsUtilizationPercentage = $availableStock > 0 
                    ? min(100, round(($totalUnitsUsed / $availableStock) * 100, 2))
                    : 0;

                // Détails des projets
                $projectsDetails = $materialSessions->groupBy('project_title')->map(function ($projectSessions, $projectTitle) {
                    $projectMinutes = $projectSessions->sum('duration_minutes');
                    $projectUnits = $projectSessions->sum('material_number');
                    
                    return [
                        'project_title' => $projectTitle,
                        'module_name' => $projectSessions->first()->module_name,
                        'units_used' => $projectUnits,
                        'time_used_hours' => round($projectMinutes / 60, 2),
                        'sessions_count' => $projectSessions->count()
                    ];
                })->values();

                return [
                    'materiel_nom' => $materialName,
                    'type_materiel' => $materialSessions->first()->material_types,
                    'stock_disponible' => $availableStock,
                    'units_utilisees' => $totalUnitsUsed,
                    'utilization_percentage' => $unitsUtilizationPercentage,
                    'temps_utilisation_heures' => round($totalMinutesUsed / 60, 2),
                    'pourcentage_utilisation_temps' => $timeUtilizationPercentage,
                    'nombre_seances' => $materialSessions->count(),
                    'projets_details' => $projectsDetails
                ];
            });

            // Calculer la moyenne basée sur le pourcentage d'utilisation du temps
            $totalTimePercentageSum = $materialDetails->sum('pourcentage_utilisation_temps');
            $averageTimeUtilization = $totalMaterialsCount > 0 
                ? round($totalTimePercentageSum / $totalMaterialsCount, 2)
                : 0;

            // Calculer la moyenne basée sur le pourcentage d'utilisation des unités
            $totalUnitsPercentageSum = $materialDetails->sum('pourcentage_utilisation_unites');
            $averageUnitsUtilization = $totalMaterialsCount > 0 
                ? round($totalUnitsPercentageSum / $totalMaterialsCount, 2)
                : 0;

            return [
                'date' => $date,
                'total_materials_used' => $materialDetails->count(),
                'total_materials_cfp' => $totalMaterialsCount,
                'utilization_percentage' => $averageTimeUtilization,
                'utilization_percentage_units' => $averageUnitsUtilization,
                'target_hours_per_day' => $HOURS_PER_DAY,
                'materiels_details' => $materialDetails->values(),
                'details_calcul' => [
                    'materials_used_count' => $materialDetails->count(),
                    'total_time_percentage_sum' => $totalTimePercentageSum,
                    'total_units_percentage_sum' => $totalUnitsPercentageSum,
                    'average_time_calculation' => $totalMaterialsCount > 0 ? 
                        $totalTimePercentageSum . ' / ' . $totalMaterialsCount . ' = ' . $averageTimeUtilization . '%' :
                        'Aucun matériel dans le CFP',
                    'average_units_calculation' => $totalMaterialsCount > 0 ? 
                        $totalUnitsPercentageSum . ' / ' . $totalMaterialsCount . ' = ' . $averageUnitsUtilization . '%' :
                        'Aucun matériel dans le CFP'
                ]
            ];
        })->values();

        // ✅ 4. Retourner tout au format JSON
        return response()->json([
            'success' => true,
            'dailyAvailability' => $dailyAvailability,
            'summary' => [
                'total_materials_in_cfp' => $totalMaterialsCount,
                'period' => $startDate . ' to ' . $endDate,
                'days_analyzed' => $dailyAvailability->count(),
                'total_sessions_with_materials' => $seances->count(),
                'calculation_method' => 'Basé sur le temps d\'utilisation (8h-18h) et l\'utilisation des unités'
            ]
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération des disponibilités matériels.',
            'error' => $e->getMessage()
        ], 500);
    }
}
}
