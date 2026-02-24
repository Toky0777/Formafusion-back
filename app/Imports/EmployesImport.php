<?php

namespace App\Imports;

use App\Services\UserService;
use App\Services\EmployeService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployesImport implements ToCollection, WithHeadingRow
{
    private $userService;
    private $employeService;
    private $idEntreprise;
    private $totalRows = 0;
    private $successfulImports = 0;
    private $failedImports = 0;
    private $errors = [];

    public function __construct(UserService $userService, EmployeService $employeService, $idEntreprise)
    {
        $this->userService = $userService;
        $this->employeService = $employeService;
        $this->idEntreprise = $idEntreprise;
    }

    public function collection(Collection $rows)
    {
        $this->totalRows = $rows->count();

        foreach ($rows as $index => $row) {
            try {
                // Nettoyer les données et convertir les clés
                $data = $this->cleanRowData($row->toArray());

                // Valider les données de la ligne
                    $validator = Validator::make($data, [
                    'nom'       => 'required|min:2|max:200',
                    'prenom'    => 'required|min:2|max:200',
                    'matricule' => 'required|unique:users,matricule',
                    'email'     => 'nullable|email|unique:users,email',
                    'telephone' => 'nullable|min:8|max:20',
                    'fonction'  => 'nullable|max:200'
                ]);

                if ($validator->fails()) {
                    $this->failedImports++;
                    $this->errors[] = [
                        'row' => $index + 2, // +2 pour l'en-tête et l'index 0-based
                        'errors' => $validator->errors()->all(),
                        'data' => $data
                    ];
                    continue;
                }

                // Créer l'utilisateur et l'employé dans une transaction
                DB::transaction(function() use($data) {
                    // Générer un mot de passe par défaut
                    $password = Hash::make('0000@#');
                    
                    // Créer l'utilisateur
                $user = $this->userService->store(
                    $data['matricule'],      // matricule
                    $data['nom'],            // nom
                    $data['prenom'],         // prénom
                    $data['email'] ?? null,  // email
                    $data['telephone'] ?? null, // téléphone
                    $password                // mot de passe par défaut
                );


                    // Créer l'employé
                    $this->employeService->store(
                        $user->id,
                        6, // Type d'employé
                        $this->idEntreprise,
                        1, // Statut
                        $this->getIdFonction($this->idEntreprise, $data['fonction'] ?? null)
                    );

                    // Assigner le rôle (à adapter selon votre méthode)
                    $this->roleUser(4, $user->id, 1, 1, 1);
                });

                $this->successfulImports++;

            } catch (Exception $e) {
                $this->failedImports++;
                $this->errors[] = [
                    'row' => $index + 2,
                    'error' => $e->getMessage(),
                    'data' => $data ?? $row->toArray()
                ];
                continue;
            }
        }
    }

    /**
     * Nettoyer et normaliser les données de la ligne
     */
    private function cleanRowData(array $rowData): array
    {
        $cleanedData = [];
        
        // Mapping des colonnes (adaptez selon votre structure Excel)
        $mapping = [
            'nom' => ['nom', 'name', 'emp_name', 'employe_nom'],
            'prenom' => ['prenom', 'firstname', 'emp_firstname', 'employe_prenom'],
            'matricule' => ['matricule', 'matricule_employe', 'emp_matricule', 'code'],
            'email' => ['email', 'mail', 'emp_email', 'courriel'],
            'telephone' => ['telephone', 'phone', 'emp_phone', 'tel'],
            'fonction' => ['fonction', 'poste', 'emp_fonction', 'position']
        ];

        foreach ($mapping as $key => $possibleHeaders) {
            foreach ($possibleHeaders as $header) {
                if (isset($rowData[$header]) && !empty($rowData[$header])) {
                    $cleanedData[$key] = trim($rowData[$header]);
                    break;
                }
            }
            
            // Valeur par défaut si non trouvée
            if (!isset($cleanedData[$key])) {
                $cleanedData[$key] = null;
            }
        }

        return $cleanedData;
    }


private function getIdFonction($idEntreprise, $fonctionName = null)
{
    try {
        $query = DB::table('fonctions')
            ->select('idFonction')
            ->where('idCustomer', $idEntreprise);

        if ($fonctionName) {
            $query->where('fonction', $fonctionName);
        } else {
            // Prendre la première fonction de l'entreprise si aucun nom spécifié
            $query->orderBy('idFonction')->limit(1);
        }

        $fonction = $query->first();

        return $fonction ? $fonction->idFonction : 1;

    } catch (\Exception $e) {
        \Log::error('Erreur dans getIdFonction: ' . $e->getMessage());
        return 1; // Valeur par défaut en cas d'erreur
    }
}
 
private function roleUser($roleId, $userId, $param1, $param2, $param3)
{
    // Utilisez le nom correct de la table
    DB::table('role_users')->insert([
        'role_id' => $roleId,
        'user_id' => $userId,
        'isActive' => $param1, // Ajoutez les autres paramètres
        'hasRole' => $param2,
        // autres champs selon vos besoins...
    ]);
    
    // Alternative : Utiliser le modèle Eloquent
    // RoleUser::create([
    //     'role_id' => $roleId,
    //     'user_id' => $userId,
    //     'isActive' => $param1,
    //     'hasRole' => $param2,
    // ]);
}

    // Getters pour les statistiques
    public function getTotalRows(): int { return $this->totalRows; }
    public function getSuccessfulImports(): int { return $this->successfulImports; }
    public function getFailedImports(): int { return $this->failedImports; }
    public function getErrors(): array { return $this->errors; }
}