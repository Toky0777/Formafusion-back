<?php

namespace App\Services\AIServices;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Customer;
use Exception;

class RentabilityPredictionService
{
    protected string $apiUrl;
    protected int $timeout = 15;
    protected bool $useCache = true;

    public function __construct()
    {
        $this->apiUrl = rtrim(env('ML_API_URL', 'http://localhost:5000'), '/');
    }

    /**
     * Prédire la rentabilité d'un projet
     */
    public function predictRentability(array $projectData, int $customerId): array
    {
        try {
            $features = $this->extractFeatures($projectData, $customerId);

            $cacheKey = $this->getCacheKey($features);
            if ($this->useCache && Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $response = Http::timeout($this->timeout)
                ->retry(2, 100)
                ->post($this->apiUrl . '/predict', ['features' => $features]);

            if ($response->successful()) {
                $result = $response->json();

                if (!isset($result['factors']) && isset($result['top_factors'])) {
                    $result['factors'] = $result['top_factors'];
                }

                $result['advice'] = $this->generateAdvice($result);
                $result['created_at'] = now()->toIso8601String();

                if ($this->useCache) {
                    Cache::put($cacheKey, $result, now()->addHour());
                }

                $this->logPrediction($customerId, $features, $result);
                return $result;
            }

            return $this->getFallbackPrediction($features, "API Error: " . $response->status());
        } catch (Exception $e) {
            Log::error('Erreur de prédiction IA', [
                'error' => $e->getMessage(),
                'customer_id' => $customerId
            ]);
            return $this->getFallbackPrediction($features ?? [], 'Service indisponible');
        }
    }

    /**
     * Entraîner le modèle avec les données historiques
     */
    public function trainModel(?int $customerId = null): array
    {
        $customerId = $customerId ?? Customer::idCustomer();

        try {
            $historicalData = $this->getHistoricalData($customerId, 1000);

            if (count($historicalData) < 10) {
                return [
                    'success' => false,
                    'message' => "Pas assez de données. Requis: 10, Reçu: " . count($historicalData),
                    'count' => count($historicalData)
                ];
            }

            $rentableCount = collect($historicalData)->where('est_rentable', 1)->count();
            Log::info("Distribution des classes - Rentable: $rentableCount, Non rentable: " . (count($historicalData) - $rentableCount));

            $response = Http::timeout(120)
                ->post($this->apiUrl . '/train', [
                    'historical_data' => $historicalData,
                    'customer_id' => $customerId
                ]);

            if ($response->successful()) {
                $result = $response->json();
                Log::info('Modèle entraîné avec succès', [
                    'customer_id' => $customerId,
                    'samples' => $result['n_samples'] ?? count($historicalData),
                    'accuracy' => $result['test_accuracy'] ?? null
                ]);
                return [
                    'success' => true,
                    'message' => 'Modèle entraîné avec succès',
                    'data' => $result
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'entraînement: ' . $response->body()
            ];
        } catch (Exception $e) {
            Log::error('Erreur entraînement modèle', [
                'error' => $e->getMessage(),
                'customer_id' => $customerId
            ]);
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    public function getHistoricalData(int $customerId = null, int $limit = 1000): array
    {
        $customerId = $customerId ?? Customer::idCustomer();

        // Projets INTER
        $interProjects = DB::table('projets as p')
            ->join('mdls as m', 'p.idModule', '=', 'm.idModule')
            ->join('inters as i', 'p.idProjet', '=', 'i.idProjet')
            ->leftJoin('fraisprojet as f', 'p.idProjet', '=', 'f.idProjet')
            ->leftJoin('modules as mod', 'm.idModule', '=', 'mod.idModule')
            ->select(
                'p.idProjet',
                'p.dateDebut',
                'p.dateFin',
                'p.idTypeProjet as type_projet',
                DB::raw('mod.prix * i.nbPlace as recettes'),
                'm.dureeJ as duree_standard',
                DB::raw('DATEDIFF(p.dateFin, p.dateDebut) + 1 as duree_reelle'), // ✅ Calcul de la durée réelle
                'i.nbPlace as nb_places',
                DB::raw('COALESCE(SUM(f.montant), 0) as total_frais'),
                DB::raw('(mod.prix * i.nbPlace) as recettes_calculees')
            )
            ->where('p.idCustomer', $customerId)
            ->where('p.idTypeProjet', 2)
            ->whereNotNull('p.dateDebut')
            ->whereNotNull('p.dateFin') // ✅ S'assurer qu'on a les dates
            ->groupBy(
                'p.idProjet',
                'p.dateDebut',
                'p.dateFin',
                'p.idTypeProjet',
                'm.dureeJ',
                'i.nbPlace',
                'mod.prix'
            );

        // Projets INTRA (similaire mais avec sous-requêtes)
        $intraProjects = DB::table('projets as p')
            ->join('mdls as m', 'p.idModule', '=', 'm.idModule')
            ->leftJoin('fraisprojet as f', 'p.idProjet', '=', 'f.idProjet')
            ->leftJoin('modules as mod', 'm.idModule', '=', 'mod.idModule')
            ->select(
                'p.idProjet',
                'p.dateDebut',
                'p.dateFin',
                'p.idTypeProjet as type_projet',
                DB::raw('mod.prix * (SELECT COUNT(*) FROM detail_apprenants WHERE idProjet = p.idProjet) as recettes'),
                'm.dureeJ as duree_standard',
                DB::raw('DATEDIFF(p.dateFin, p.dateDebut) + 1 as duree_reelle'),
                DB::raw('(SELECT COUNT(*) FROM detail_apprenants WHERE idProjet = p.idProjet) as nb_places'),
                DB::raw('COALESCE(SUM(f.montant), 0) as total_frais'),
                DB::raw('mod.prix * (SELECT COUNT(*) FROM detail_apprenants WHERE idProjet = p.idProjet) as recettes_calculees')
            )
            ->where('p.idCustomer', $customerId)
            ->where('p.idTypeProjet', 1)
            ->whereNotNull('p.dateDebut')
            ->whereNotNull('p.dateFin')
            ->groupBy(
                'p.idProjet',
                'p.dateDebut',
                'p.dateFin',
                'p.idTypeProjet',
                'm.dureeJ',
                'mod.prix'
            );

        $projects = $interProjects->union($intraProjects)
            ->orderBy('dateFin', 'desc')
            ->limit($limit)
            ->get();

        $historicalData = [];
        foreach ($projects as $project) {
            // Utiliser la durée réelle si disponible, sinon la durée standard
            $dureeJours = $project->duree_reelle ?? $project->duree_standard;

            // Calculer la rentabilité avec les vraies données
            $depenses = $project->total_frais - $project->recettes_calculees;
            $estRentable = $project->recettes_calculees > $depenses ? 1 : 0;

            // Extraire le mois de début pour la saisonnalité
            $mois = $project->dateDebut ? date('n', strtotime($project->dateDebut)) : 1;

            // Calculer la durée en jours ouvrés (optionnel)
            $joursOuvres = $this->calculateWorkingDays($project->dateDebut, $project->dateFin);

            if ($dureeJours > 0 && $project->recettes_calculees > 0 && $project->nb_places > 0) {
                $historicalData[] = [
                    'duree_jours' => (int) $dureeJours,
                    'duree_standard' => (int) $project->duree_standard,
                    'duree_reelle' => (int) ($project->duree_reelle ?? 0),
                    'jours_ouvres' => $joursOuvres,
                    'nb_places' => (int) $project->nb_places,
                    'mois' => (int) $mois,
                    'type_projet' => (int) $project->type_projet,
                    'recettes' => (float) $project->recettes_calculees,
                    'depenses' => (float) $depenses,
                    'est_rentable' => (int) $estRentable,
                    // Features supplémentaires utiles
                    'ratio_duree_reelle_standard' => $project->duree_standard > 0 ?
                        round($dureeJours / $project->duree_standard, 2) : 1,
                    'saison' => $this->getSaison($mois) // printemps, été, etc.
                ];
            }
        }

        return $historicalData;
    }

    /**
     * Calcule le nombre de jours ouvrés entre deux dates
     */
    private function calculateWorkingDays($startDate, $endDate): int
    {
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $end = $end->modify('+1 day');

        $period = new \DatePeriod($start, new \DateInterval('P1D'), $end);
        $workingDays = 0;

        foreach ($period as $date) {
            $dayOfWeek = $date->format('N');
            if ($dayOfWeek < 6) { // Lundi=1, Dimanche=7
                $workingDays++;
            }
        }

        return $workingDays;
    }

    /**
     * Détermine la saison à partir du mois
     */
    private function getSaison(int $mois): string
    {
        if ($mois >= 3 && $mois <= 5) return 'printemps';
        if ($mois >= 6 && $mois <= 8) return 'ete';
        if ($mois >= 9 && $mois <= 11) return 'automne';
        return 'hiver';
    }

    /**
     * Vérifier la santé de l'API
     */
    public function checkApiHealth(): array
    {
        try {
            $response = Http::timeout(5)->get($this->apiUrl . '/health');
            if ($response->successful()) {
                return [
                    'online' => true,
                    'model_loaded' => $response->json('model_loaded', false),
                    'response' => $response->json()
                ];
            }
            return ['online' => false, 'message' => 'API répond avec erreur'];
        } catch (Exception $e) {
            return ['online' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Extraire les features d'un projet
     */
    protected function extractFeatures(array $projectData, int $customerId): array
    {
        $module = DB::table('mdls')->where('idModule', $projectData['module_id'])->first();
        $prixModule = DB::table('modules')->select('prix')->where('idModule', $projectData['module_id'])->first();

        if (!$module) {
            throw new Exception("Module non trouvé");
        }

        // Extract the actual price value from the object
        $prixModuleValue = $prixModule ? floatval($prixModule->prix) : 0;

        $dateDebut = isset($projectData['date_debut']) ? new \DateTime($projectData['date_debut']) : new \DateTime();
        $dateFin = isset($projectData['date_fin']) ? new \DateTime($projectData['date_fin'])
            : (clone $dateDebut)->modify('+' . ($module->dureeJ ?? 5) . ' days');
        $dureeJours = $dateDebut->diff($dateFin)->days ?: 5;

        $fraisTotaux = 0;
        if (isset($projectData['frais']) && is_array($projectData['frais'])) {
            foreach ($projectData['frais'] as $frais) {
                $fraisTotaux += floatval($frais['montant'] ?? 0);
            }
        }

        $nbPlaces = intval($projectData['nb_place'] ?? 10);
        $caPotentiel = $prixModuleValue * $nbPlaces; // Now using the extracted value

        return [
            'duree_jours' => $dureeJours,
            'nb_places' => $nbPlaces,
            'mois' => (int) $dateDebut->format('n'),
            'type_projet' => (int) ($projectData['type_projet'] ?? 2),
            'prix_par_jour' => $dureeJours > 0 ? $prixModuleValue / $dureeJours : 0, // Also fix here
            'chiffre_affaire_potentiel' => $caPotentiel,
            'frais_totaux_estimes' => $fraisTotaux,
            'nb_entreprises_interessees' => count($projectData['entreprises_interessees'] ?? []),
            'haute_saison' => in_array($dateDebut->format('n'), [3, 4, 9, 10]) ? 1 : 0
        ];
    }

    /**
     * Prédiction de fallback
     */
    protected function getFallbackPrediction(array $features, string $reason = ''): array
    {
        $score = 0;
        $factors = [];

        if (($features['haute_saison'] ?? 0) == 1) {
            $score += 20;
            $factors[] = 'haute saison';
        }

        $prixParJour = $features['prix_par_jour'] ?? 0;
        if ($prixParJour > 50000) {
            $score += 25;
            $factors[] = 'prix élevé';
        } elseif ($prixParJour > 30000) {
            $score += 15;
            $factors[] = 'prix attractif';
        }

        $nbPlaces = $features['nb_places'] ?? 0;
        if ($nbPlaces > 15) {
            $score += 25;
            $factors[] = 'grand groupe';
        } elseif ($nbPlaces > 8) {
            $score += 15;
            $factors[] = 'groupe optimal';
        }

        $nbEntreprises = $features['nb_entreprises_interessees'] ?? 0;
        if ($nbEntreprises > 3) {
            $score += 20;
            $factors[] = 'fort intérêt';
        } elseif ($nbEntreprises > 0) {
            $score += 10;
            $factors[] = 'intérêt confirmé';
        }

        $ca = $features['chiffre_affaire_potentiel'] ?? 0;
        $frais = $features['frais_totaux_estimes'] ?? 0;
        if ($ca > 0) {
            $marge = (($ca - $frais) / $ca) * 100;
            if ($marge > 40) {
                $score += 20;
                $factors[] = 'marge élevée';
            } elseif ($marge > 20) {
                $score += 10;
                $factors[] = 'marge correcte';
            }
        }

        $probability = min($score / 100, 0.95);
        $margeEstimee = $ca > 0 ? (($ca - $frais) / $ca) * 100 : 15;

        return [
            'success' => true,
            'probability' => $probability,
            'is_rentable' => $probability > 0.6,
            'confidence' => $this->getConfidenceLevel($probability),
            'margin' => $margeEstimee * $probability,
            'factors' => array_slice($factors, 0, 3),
            'top_factors' => array_slice($factors, 0, 3),
            'method' => 'fallback',
            'note' => 'Prédiction basée sur règles métier' . ($reason ? " ($reason)" : '')
        ];
    }

    /**
     * Générer un conseil basé sur la prédiction
     */
    protected function generateAdvice(array $prediction): string
    {
        $prob = round(($prediction['probability'] ?? 0) * 100);
        $isRentable = $prediction['is_rentable'] ?? false;
        $confidence = $prediction['confidence'] ?? 'low';
        $factors = $prediction['factors'] ?? $prediction['top_factors'] ?? [];

        if ($isRentable) {
            if ($confidence === 'high') {
                return "✅ Projet très prometteur avec {$prob}% de chance d'être rentable. Points forts : " . implode(', ', $factors);
            } elseif ($confidence === 'medium') {
                return "👍 Bon potentiel de rentabilité ({$prob}%). À valider.";
            } else {
                return "📊 Potentiel de rentabilité de {$prob}%. Analyse complémentaire nécessaire.";
            }
        } else {
            if ($confidence === 'high') {
                return "⚠️ Projet risqué ({$prob}%). Envisagez de revoir le pricing.";
            } elseif ($confidence === 'medium') {
                return "🔍 Projet incertain ({$prob}%). Facteurs : " . implode(', ', $factors);
            } else {
                return "ℹ️ Données insuffisantes. Probabilité estimée à {$prob}%.";
            }
        }
    }

    protected function getConfidenceLevel(float $probability): string
    {
        if ($probability > 0.8 || $probability < 0.2) return 'high';
        if ($probability > 0.6 || $probability < 0.4) return 'medium';
        return 'low';
    }

    protected function getCacheKey(array $features): string
    {
        return 'prediction_' . md5(json_encode($features));
    }

    protected function logPrediction(int $customerId, array $features, array $result): void
    {
        Log::channel('daily')->info('Prédiction effectuée', [
            'customer_id' => $customerId,
            'probability' => $result['probability'] ?? null,
            'confidence' => $result['confidence'] ?? null,
            'method' => $result['method'] ?? 'unknown'
        ]);
    }
}
