<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\AIServices\RentabilityPredictionService;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RentabilityController extends Controller
{
    protected RentabilityPredictionService $predictionService;

    public function __construct(RentabilityPredictionService $predictionService)
    {
        $this->predictionService = $predictionService;
    }

    /**
     * Prédire la rentabilité d'un projet
     */
    public function predict(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module_id' => 'required|exists:mdls,idModule',
            'type_projet' => 'required|in:1,2,4',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after:date_debut',
            'nb_place' => 'nullable|integer|min:1',
            'frais' => 'nullable|array',
            'frais.*.montant' => 'nullable|numeric|min:0',
            'entreprises_interessees' => 'nullable|array',
            'entreprises_interessees.*' => 'exists:customers,idCustomer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $customerId = Customer::idCustomer();
        $prediction = $this->predictionService->predictRentability($request->all(), $customerId);

        return response()->json(['success' => true, 'data' => $prediction]);
    }

    /**
     * Entraîner le modèle
     */
    public function train(Request $request)
    {
        $customerId = $request->get('customer_id', Customer::idCustomer());
        $result = $this->predictionService->trainModel($customerId);
        return response()->json($result);
    }

    /**
     * Vérifier l'état de l'API ML
     */
    public function health()
    {
        $health = $this->predictionService->checkApiHealth();
        return response()->json(['success' => true, 'data' => $health]);
    }

    /**
     * Récupérer les statistiques des prédictions
     */
    public function statistics()
    {
        $customerId = Customer::idCustomer();

        $projets = DB::table('projets as p')
            ->join('mdls as m', 'p.idModule', '=', 'm.idModule')
            ->leftJoin('modules as mod', 'm.idModule', '=', 'mod.idModule')
            ->leftJoin('inters as i', 'p.idProjet', '=', 'i.idProjet')
            ->leftJoin('fraisprojet as f', 'p.idProjet', '=', 'f.idProjet')
            ->select(
                'p.idProjet',
                'p.idTypeProjet',
                'mod.prix as prix_module',
                DB::raw('COALESCE(SUM(f.montant), 0) as total_frais'),
                'i.nbPlace as nb_places_inter',
                DB::raw('(SELECT COUNT(*) FROM detail_apprenants WHERE idProjet = p.idProjet) as nb_apprenants_intra')
            )
            ->where('p.idCustomer', $customerId)
            ->whereIn('p.idTypeProjet', [1, 2])
            ->whereNotNull('mod.prix')
            ->groupBy('p.idProjet', 'p.idTypeProjet', 'mod.prix', 'i.nbPlace')
            ->get();

        $total = $projets->count();
        $rentable = 0;
        $total_recettes = 0;
        $total_depenses = 0;

        foreach ($projets as $projet) {
            $nbPersonnes = $projet->idTypeProjet == 1
                ? ($projet->nb_apprenants_intra ?? 0)
                : ($projet->nb_places_inter ?? 0);

            if ($nbPersonnes == 0) continue;

            $recettes = $projet->prix_module * $nbPersonnes;
            $depenses = $projet->total_frais - $recettes;

            $total_recettes += $recettes;
            $total_depenses += $depenses;

            if ($recettes > $depenses) {
                $rentable++;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'total_projets' => $total,
                'projets_rentables' => $rentable,
                'taux_rentabilite' => $total > 0 ? round(($rentable / $total) * 100, 2) : 0,
                'total_recettes' => round($total_recettes, 0),
                'total_depenses' => round($total_depenses, 0),
                'resultat_net' => round($total_recettes - $total_depenses, 0)
            ]
        ]);
    }

    /**
     * Récupérer les données historiques pour debug
     */
    public function getHistoricalDataForDebug(Request $request)
    {
        $customerId = $request->get('customer_id', Customer::idCustomer());
        $limit = $request->get('limit', 100);
        $data = $this->predictionService->getHistoricalData($customerId, $limit);
        return response()->json(['success' => true, 'count' => count($data), 'data' => $data]);
    }

    /**
     * Tester le fallback
     */
    public function testFallback(Request $request)
    {
        $features = [
            'duree_jours' => $request->get('duree_jours', 5),
            'nb_places' => $request->get('nb_places', 12),
            'mois' => $request->get('mois', 3),
            'type_projet' => $request->get('type_projet', 2),
            'prix_par_jour' => $request->get('prix_par_jour', 100000),
            'chiffre_affaire_potentiel' => $request->get('chiffre_affaire_potentiel', 1200000),
            'frais_totaux_estimes' => $request->get('frais_totaux_estimes', 800000),
            'nb_entreprises_interessees' => $request->get('nb_entreprises_interessees', 4),
            'haute_saison' => $request->get('haute_saison', 1)
        ];

        $service = new RentabilityPredictionService();
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('getFallbackPrediction');
        $method->setAccessible(true);
        $result = $method->invoke($service, $features, 'test');

        return response()->json(['success' => true, 'data' => $result]);
    }

    public function getModuleCfp()
    {
        $idCustomer = Customer::idCustomer();
        $modules = DB::table('v_module_cfps')
            ->select('idModule', 'moduleName as module_name', 'dureeJ as module_duree', 'prix as module_prix')
            ->where('moduleStatut', 1)
            ->whereNot('moduleName', 'Default module')
            ->where('idCustomer', $idCustomer)
            ->get();

        return $modules;
    }

    public function getClientCfp()
    {
        $query = DB::table('v_collaboration_cfp_etps')
            ->select('idEtp', 'etp_name', 'etp_email')
            ->where('idCfp', Customer::idCustomer())
            ->whereNot('etp_name', 'like', '%PRODECID%')
            ->orderBy('etp_name', 'ASC')
            ->get();

        return $query;
    }
}
