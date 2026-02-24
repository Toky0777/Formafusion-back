<?php
// app/Console/Commands//TrainRentabilityModels.php

namespace App\Console\Commands\AI;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Services\AIServices\RentabilityPredictionService;

class TrainRentabilityModels extends Command
{
    protected $signature = 'ai:train-rentability 
                            {--customer= : ID du client spécifique}
                            {--all : Entraîner pour tous les clients}';

    protected $description = 'Entraîne les modèles de prédiction de rentabilité';

    public function handle()
    {
        $predictionService = new RentabilityPredictionService();

        // Vérifier d'abord la santé de l'API
        $health = $predictionService->checkApiHealth();

        if (!$health['online']) {
            $this->error('❌ API ML indisponible: ' . ($health['message'] ?? ''));
            return 1;
        }

        $this->info('✅ API ML connectée');

        if ($this->option('all')) {
            $customers = Customer::where('is_active', 1)->get();
        } elseif ($customerId = $this->option('customer')) {
            $customers = Customer::where('idCustomer', $customerId)->get();
        } else {
            $this->error('Veuillez spécifier --customer ou --all');
            return 1;
        }

        foreach ($customers as $customer) {
            $this->line('');
            $this->info("📊 Traitement du client #{$customer->idCustomer} - {$customer->customerName}");

            $result = $predictionService->trainModel($customer->idCustomer);

            if ($result['success']) {
                $this->info("✅ " . $result['message']);
                if (isset($result['data'])) {
                    $this->line("   - Accuracy: " . round($result['data']['test_accuracy'] * 100, 2) . "%");
                    $this->line("   - Échantillons: " . $result['data']['n_samples']);
                }
            } else {
                $this->warn("⚠️ " . $result['message']);
            }
        }

        $this->line('');
        $this->info('✅ Traitement terminé');

        return 0;
    }
}
