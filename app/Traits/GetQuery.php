<?php
namespace App\Traits;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

trait GetQuery{
    public function getIdFonction($idEntreprise)
    {
        $fonction = DB::table('fonctions')->select('idFonction')->where('idCustomer', $idEntreprise)->first();

        return $fonction->idFonction;
    }

    // Listes des ETP pour FORMATEUR entre (7 jours avant debut formation et 3 jours apès formation)
    public function getEntreprises($idFormateur): array
    {
        $etps = DB::table('v_projet_form_apprenants')
            ->select('idEtp', 'etp_name')
            ->where('idFormateur', $idFormateur)
            ->where('project_status', '!=', "Supprimé")
            ->where(function($query){
                $query->whereBetween(DB::raw('CURRENT_DATE'), [DB::raw('p_start_date_minus_7'), DB::raw('p_end_date_plus_3')]);
            })
            ->groupBy('idEtp', 'etp_name')
            ->orderBy('etp_name', 'asc')
            ->get();

        return $etps->toArray();
    }

     // Listes des idEtp pour FORMATEUR entre (7 jours avant debut formation et 3 jours apès formation)
    public function getIdEntreprises($idFormateur): array
    {
        $etps = DB::table('v_projet_form_apprenants')
            ->select('idEtp')
            ->where('idFormateur', $idFormateur)
            ->where('project_status', '!=', "Supprimé")
            ->where(function($query){
                $query->whereBetween(DB::raw('CURRENT_DATE'), [DB::raw('p_start_date_minus_7'), DB::raw('p_end_date_plus_3')]);
            })
            ->groupBy('idEtp')
            ->pluck('idEtp');

        return $etps->toArray();
    }

    // Apprenants nampidirin'ilay FORMATEUR ihhany no ato
    public function getFApprenants($idEtps): array{
        $apprs = DB::table('v_apprenant_etp_alls')
            ->select('idEmploye', 'idEtp', 'etp_name', 'etp_email', 'emp_matricule', 'emp_initial_name', 'emp_name', 'emp_firstname', 'emp_email', 'emp_photo', 'emp_phone')
            ->where('role_id', 4)
            ->whereIn('idEtp', $idEtps)
            ->groupBy('idEmploye', 'idEtp', 'etp_name', 'etp_email', 'emp_matricule', 'emp_initial_name', 'emp_name', 'emp_firstname', 'emp_email', 'emp_photo', 'emp_phone')
            ->get();
        
        return $apprs->toArray();
    }

    // listes des entreprises en collaboration avec un CFP
    public function getEntrepriseCollaborated($idCfp): array{
        $etps = DB::table('v_collaboration_cfp_etps')
            ->select('idEtp', 'etp_name')
            ->groupBy('idEtp', 'etp_name')
            ->where('idCfp', $idCfp)
            ->orderBy('etp_name', 'asc')
            ->get();
            
        return $etps->toArray();
    }

    // liste des villes sans code postal
    public function getVilles(): array{
        $villes = DB::table('villes')
            ->select('idVille', 'ville')
            ->orderBy('ville', 'asc')
            ->get();

        return $villes->toArray();
    }

    // liste des villes avec code postal
    public function getVilleCodeds(){
        $villes = DB::table('ville_codeds')->select('*')->orderBy('ville_name', 'asc')->get();

        return $villes;
    }

    // Récupération type d'entreprises sans ->get()
    public function getTypeEntreprise(){
        $typeEntreprises = DB::table('type_entreprises')->select('*')->orderBy('type_etp_desc', 'asc');

        return $typeEntreprises;
    }

    public function getIdCfpInter($idProjet){
        $id = DB::table('v_projet_cfps')
                ->where('idProjet', $idProjet)
                ->pluck('idCfp_inter')
                ->first();
        
        return $id;
    }

    public function getRoleUser($idUser){
        $user = DB::table('role_users')
            ->select('role_id', 'user_id', 'isActive', 'user_is_deleted')
            ->join('users', 'users.id', 'role_users.user_id')
            ->where('user_id', $idUser)
            ->get();

        return $user;
    }
}