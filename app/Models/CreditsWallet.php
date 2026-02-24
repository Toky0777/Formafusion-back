<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreditsWallet extends Model
{
    use HasFactory;

    protected $table = "credits_wallet";
    protected $primaryKey = "idWallet";

    protected $fillable = [
        'idUser',
        'solde',
    ];

    // Relation avec le modèle User
    public function userWallet()
    {
        return $this->belongsTo(User::class, 'idUser', 'id');
    }

    /**
     * Fonction pour avoir l'id de l'entreprise si on a l'id de
     * l'employé
     * 
     * @param $idEmploye
     */
    public function getIdEtpByidEmp($idEmploye)
    {
        // Effectuer une requête pour obtenir l'id de l'entreprise pour un employé donné
        $idEtp = DB::table('v_employe_alls')
            ->where('idEmploye', $idEmploye)
            ->value('idCustomer');

        // Retourner l'id de l'entreprise ou null si aucun résultat
        return $idEtp ? $idEtp : null;
    }

    /**
     * Fonction pour afficher le portefeuille en crédit d'un utilisateur
     * 
     * @param $userId
     */
    public function user_credit_wallet($userId)
    {
        $credit_wallet = self::where('idUser', $userId)
            ->firstOrFail();

        return $credit_wallet;
    }

    /**
     * Fonction pour avoir le contenu du portefeuille en crédits de l'utilisateur
     * en se basant sur le role de l'utilisateur connecté
     * 
     * @param $userId
     */
    public function user_credit_walletBasedOnRole($userId)
    {
        // Vérifier le rôle de l'utilisateur connecté
        if (auth()->user()->hasRole('Employe')) {
            // Si l'utilisateur est un employé, récupérer l'ID de son entreprise
            $idEtp = $this->getIdEtpByidEmp($userId);

            // Utiliser l'ID de l'entreprise pour récupérer le portefeuille de crédit
            $credit_wallet = self::where('idUser', $idEtp)
                ->value('solde') ?? 0;
        } else {
            // Pour les rôles Particulier et Referent, utiliser l'ID de l'utilisateur
            $credit_wallet = self::where('idUser', $userId)
                ->value('solde') ?? 0;
        }

        return $credit_wallet;
    }

    /**
     * Fonction pour créditer le compte d'un utilisateur
     * 
     * @param $userId, $montant (montant à créditer dans le compte)
     */
    public static function crediter($userId, $montant)
    {
        // Créer ou récupérer le portefeuille de l'utilisateur
        $wallet = self::firstOrCreate(
            ['idUser' => $userId],
            ['solde' => 0]
        );

        // Ajouter le montant au solde
        $wallet->solde += $montant;
        $wallet->save();

        // Générer une référence de transaction unique
        $transactionRef = 'CREDIT-' . uniqid();

        // Enregistrer la transaction dans l'historique
        DB::table('transaction_history')->insert([
            'idUser' => $userId,
            'transaction_ref' => $transactionRef,
            'montant' => $montant,
            'typeTransaction' => 'credit',
            'description' => 'Crédit de ' . $montant . ' crédits.',
            'created_at' => now(),
        ]);

        return $wallet;
    }

    /**
     * Fonction pour débiter le compte d'un utilisateur (v2)
     * Avec distinction si c'est un particulier ou un employé d'une entreprise
     * 
     *  @param $userId, $montant (montant à débiter dans le compte), $initiatedByEmployeeId = null (pour voir si c'est un employé ou non)
     */
    public static function debiter($userId, $montant, $initiatedByEmployeeId = null)
    {
        // Récupérer le portefeuille de l'utilisateur
        $wallet = self::where('idUser', $userId)->first();

        if ($wallet && $wallet->solde >= $montant) {
            // Déduire le montant du solde
            $wallet->solde -= $montant;
            $wallet->save();

            // Générer une référence de transaction unique
            $transactionRef = 'DEBIT-' . uniqid();

            // Enregistrer la transaction dans l'historique
            $transactionId = DB::table('transaction_history')->insertGetId([
                'idUser' => $userId,
                'transaction_ref' => $transactionRef,
                'montant' => $montant,
                'typeTransaction' => 'debit',
                'description' => 'Débit de ' . $montant . ' crédits.',
                'created_at' => now(),
            ]);

            // Si la transaction est initiée par un employé, insérer dans `emp_debit_credit`
            if ($initiatedByEmployeeId) {
                $employee = User::find($initiatedByEmployeeId);
                $etp = Customer::find($userId); // L'entreprise (débitée)

                // Insérer la transaction dans la table `emp_debit_credit`
                DB::table('emp_debit_credit')->insert([
                    'idTransaction' => $transactionId,
                    'idUser' => $initiatedByEmployeeId, // ID de l'employé initiateur
                    'description' => sprintf(
                        'Débit de %d crédits par %s %s pour le compte de son entreprise %s',
                        $montant,
                        $employee->name,
                        $employee->firstName,
                        $etp ? $etp->customerName : 'inconnu'
                    ),
                    'montant' => $montant,
                    'typeTransaction' => 'debit',
                    'created_at' => now(),
                ]);
            }

            return $wallet;
        } else {
            throw new \Exception("Solde insuffisant ou portefeuille inexistant.");
        }
    }

    /**
     * Fonction pour distribuer des crédits à des membres d'une entreprise
     * Dans le cas où l'utilisateur est une entreprise
     * 
     * @param $etpId, $montant (montant de crédit à distribuer)
     */
    public function share_credits($etpId, $montant)
    {
        // Récupérer les apprenants d'une entreprise
        $etp_apprenant_list = DB::table('v_apprenant_etp')
            ->select('idEmploye', 'idEtp', 'etp_name', 'etp_email', 'idTypeCustomer', 'emp_name', 'emp_firstname', 'emp_email', 'emp_matricule', 'emp_phone')
            ->where('idEtp', $etpId)
            ->get();

        // Vérifier si la liste des apprenants est vide
        if ($etp_apprenant_list->isEmpty()) {
            return null; // Aucun apprenant trouvé
        }

        // Récupérer le portefeuille de l'entreprise
        $etp_wallet = $this->user_credit_wallet($etpId);

        // Calculer le montant total à déduire du portefeuille de l'entreprise
        $nombreEmployes = $etp_apprenant_list->count();
        $montantTotalADeduire = $montant * $nombreEmployes;

        // Vérifier si l'entreprise a suffisamment de crédits
        if ($etp_wallet->solde < $montantTotalADeduire) {
            return 'Fonds insuffisants dans le portefeuille de l\'entreprise.';
        }

        // Débiter le montant total du portefeuille de l'entreprise
        CreditsWallet::debiter($etpId, $montantTotalADeduire);

        // Tableau pour stocker les informations des apprenants
        $resultats = [];

        // Distribuer le montant fixe à chaque employé
        foreach ($etp_apprenant_list as $apprenant) {
            // Créditer le portefeuille de chaque employé
            $wallet = CreditsWallet::crediter($apprenant->idEmploye, $montant);

            // Ajouter les informations de l'apprenant au tableau des résultats
            $resultats[] = [
                'idEmploye' => $apprenant->idEmploye,
                'name' => $apprenant->emp_name,
                'firstName' => $apprenant->emp_firstname,
                'montantCredite' => $montant,
                'soldeActuel' => $wallet->solde,
            ];
        }

        // Retourner le montant total déduit et la liste des apprenants
        return [
            'montantTotalDeduit' => $montantTotalADeduire,
            'apprenants' => $resultats,
        ];
    }

        /**
     * Fonction pour avoir les opérations sur les crédits en global (que ce soit un credit ou un debit)
     * Logiquement cette fonction ne devrait pas être ici
     * 
     * @param string|null $operationType Optionnel : Filtrer par origine d'opération (EntrepriseCredit, DirectDebit, etc.)
     * @return \Illuminate\Support\Collection
     */
    public function getAllCreditsTransaction($operationType = null)
    {
        $query = DB::table('v_credit_operations')
            ->select('transaction_id', 'transaction_user_id', 'transaction_ref', 'transaction_amount', 'transaction_type', 'transaction_description', 'transaction_created_at', 'operation_origin', 'employee_id', 'debit_description');

        // Si un type d'opération spécifique est fourni, on applique un filtre
        if (!is_null($operationType)) {
            $query->whereRaw('operation_origin COLLATE utf8mb4_unicode_ci = ?', [$operationType]);
        }

        // Exécuter la requête et assigner les résultats dans une variable
        $results = $query->orderBy('transaction_created_at', 'desc')->get();

        return $results;
    }
}
