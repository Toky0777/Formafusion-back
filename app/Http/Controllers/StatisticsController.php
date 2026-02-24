<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use function Laravel\Prompts\table;

class StatisticsController extends Controller
{
  public function getCounts()
  {
    $customerId = Customer::idCustomer();
    $now = Carbon::now();

    $counts = DB::table(DB::raw('dual'))
      ->selectRaw("
            (SELECT COUNT(*) 
             FROM mdls 
             WHERE idCustomer = ? 
               AND moduleName != 'Default module'
               AND is_public = 1) AS module_online
        ", [$customerId])
      ->selectRaw("
            (SELECT COUNT(*) 
             FROM mdls 
             WHERE idCustomer = ? 
               AND moduleName != 'Default module'
               AND moduleStatut = 1) AS module_interne
        ", [$customerId])
      ->selectRaw("
            (SELECT COUNT(DISTINCT id) 
             FROM v_fmfp 
             WHERE idCfp = ? 
               AND idStatus = 1) AS fmfp_soumission
        ", [$customerId])
      ->selectRaw("
            (SELECT COUNT(DISTINCT id) 
             FROM v_fmfp 
             WHERE idCfp = ? 
               AND idStatus IN (3,4) ) AS fmfp_excute
        ", [$customerId])
      ->selectRaw("
            (SELECT COUNT(DISTINCT id) 
             FROM v_fmfp 
             WHERE idCfp = ? 
               AND idStatus = 2) AS fmfp_refuse
        ", [$customerId])
      ->selectRaw("
            (SELECT COUNT(DISTINCT id) 
             FROM v_fmfp 
             WHERE idCfp = ? 
               AND idStatus = 5) AS fmfp_cloture
        ", [$customerId])
      ->selectRaw("
            (SELECT COUNT(DISTINCT id) 
             FROM v_fmfp 
             WHERE idCfp = ? 
               AND idStatus = 6) AS fmfp_fait
        ", [$customerId])
      ->selectRaw("
            (SELECT COUNT(*) 
             FROM cfp_formateurs 
             WHERE idCfp = ?) AS trainers
        ", [$customerId])
      ->selectRaw("
            (SELECT COUNT(*) 
             FROM v_collaboration_cfp_etps 
             WHERE idCfp = ?) AS customers
        ", [$customerId])
      ->selectRaw("
            (SELECT COUNT(*) 
             FROM v_collaboration_cfp_particuliers 
             WHERE idCfp = ?) AS particulars
        ", [$customerId])
      ->selectRaw("
            (SELECT COUNT(*) 
             FROM projets P
             JOIN mdls M ON M.idModule = P.idModule
             WHERE P.idCustomer = ?
               AND M.moduleName != 'Default module') AS projects
        ", [$customerId])
      ->selectRaw("
            (SELECT COUNT(*) 
             FROM dossiers 
             WHERE idCfp = ?) AS folders
        ", [$customerId])
      ->selectRaw("
            (SELECT COUNT(*) 
             FROM invoices 
             WHERE idTypeFacture = 1 
               AND idCustomer = ?) AS invoices
        ", [$customerId])
      ->selectRaw("
            (SELECT COUNT(*) 
             FROM invoices 
             WHERE idTypeFacture = 2 
               AND idCustomer = ?) AS quotes
        ", [$customerId])
      ->selectRaw("
            (SELECT COUNT(*) 
             FROM v_reservations_cfp 
             WHERE project_idCfp = ?) AS reservations
        ", [$customerId])
      ->selectRaw("
            (SELECT COUNT(*) 
             FROM badges 
             WHERE idCfp = ?) AS badges
        ", [$customerId])
      ->selectRaw("
            (SELECT COUNT(DISTINCT idEmploye) 
             FROM v_apprenant_union 
             WHERE idCfp = ?) AS learners
        ", [$customerId])
      ->selectRaw("
            (SELECT COUNT(idBC) 
             FROM bon_commandes 
             WHERE idCfp = ?) AS purchase_order
        ", [$customerId])
      ->selectRaw("
            (SELECT COUNT(*) 
             FROM v_employe_alls 
             WHERE idCustomer = ? 
               AND role_id = 8) AS admins
        ", [$customerId])
      ->selectRaw("
            (SELECT COUNT(*) 
             FROM v_projet_cfps 
             WHERE (idCfp_inter = ? OR idCfp = ? OR idSubContractor = ?)
               AND module_name != 'Default module' 
               AND project_status = 'En préparation') AS project_in_preparation
        ", [$customerId, $customerId, $customerId])
      ->selectRaw("
            (SELECT COUNT(*) 
             FROM v_projet_cfps 
             WHERE (idCfp_inter = ? OR idCfp = ? OR idSubContractor = ?)
               AND module_name != 'Default module' 
               AND project_status = 'En cours') AS project_in_progress
        ", [$customerId, $customerId, $customerId])
      ->selectRaw("
            (SELECT COUNT(*) 
             FROM v_projet_cfps 
             WHERE (idCfp_inter = ? OR idCfp = ? OR idSubContractor = ?)
               AND module_name != 'Default module' 
               AND project_status = 'Terminé') AS project_finished
        ", [$customerId, $customerId, $customerId])
      ->selectRaw("
            (SELECT COUNT(*) 
             FROM v_projet_cfps 
             WHERE (idCfp_inter = ? OR idCfp = ? OR idSubContractor = ?)
               AND module_name != 'Default module' 
               AND project_status = 'Planifié') AS project_planned
        ", [$customerId, $customerId, $customerId])
      ->selectRaw("
            (SELECT COUNT(*) 
             FROM v_projet_cfps 
             WHERE (idCfp_inter = ? OR idCfp = ? OR idSubContractor = ?)
               AND module_name != 'Default module' 
               AND project_status = 'Cloturé') AS project_closed
        ", [$customerId, $customerId, $customerId])
      ->selectRaw("
            (SELECT COUNT(*) 
             FROM v_projet_cfps 
             WHERE (idCfp_inter = ? OR idCfp = ? OR idSubContractor = ?)
               AND module_name != 'Default module' 
               AND project_status = 'Reporté') AS project_postponed
        ", [$customerId, $customerId, $customerId])
      ->selectRaw("
            (SELECT COUNT(*) 
             FROM v_projet_cfps 
             WHERE (idCfp_inter = ? OR idCfp = ? OR idSubContractor = ?)
               AND module_name != 'Default module' 
               AND project_status = 'Annulé') AS project_canceled
        ", [$customerId, $customerId, $customerId])
      ->selectRaw("
            (SELECT COUNT(*) 
             FROM v_projet_cfps 
             WHERE (idCfp_inter = ? OR idCfp = ? OR idSubContractor = ?)
               AND module_name != 'Default module' 
               AND project_status = 'Supprimé') AS project_deleted
        ", [$customerId, $customerId, $customerId])
      ->selectRaw("
            (SELECT COUNT(*) 
             FROM v_list_sub_contractors 
             WHERE id_cfp = ?) AS sub_contractors
        ", [$customerId])
      ->selectRaw("
            (SELECT COUNT(*) 
             FROM v_reward_results_cfp 
             WHERE idCfp = ? 
               AND expired_date >= ?) AS rewards
        ", [$customerId, $now])
      ->first();

    return response()->json($counts, 200);
  }


  public function getCountsEtp()
  {
    $etpId = Customer::idCustomer();

    $now = Carbon::now();

    $counts = DB::table(DB::raw('dual'))
      // ->selectRaw("(SELECT COUNT(*) FROM dossiers WHERE idEtp = ?) as folders", [$etpId])
      ->selectRaw("(SELECT COUNT(*) FROM v_collaboration_etp_cfps WHERE idEtp = ?) as customers", [$etpId])
      ->selectRaw("(SELECT COUNT(DISTINCT idEmploye) FROM v_apprenant_union WHERE idEtp = ?) as learners", [$etpId])
      ->selectRaw("(SELECT COUNT(*) FROM v_reward_results WHERE idEtp = ? AND expired_date >= ?) as rewards", [$etpId, $now])
      ->first();

    return response()->json($counts);
  }
}
