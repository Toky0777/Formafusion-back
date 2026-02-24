<?php

namespace App\Services;

use App\Interfaces\ProjectRepository;
use App\Models\Customer;
use App\Traits\Project;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ProjetService implements ProjectRepository
{
    use Project;

    public function index($idCustomer): mixed
    {
        $query = DB::table('v_projet_cfps')
            ->select('idProjet', 'dateDebut', 'idEtp', 'dateFin', 'module_name', 'etp_name', 'li_name', 'ville', 'project_status', 'project_reference', 'project_description', 'project_type', 'paiement', DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'), 'module_image', 'etp_logo', 'etp_initial_name', 'idSalle', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'modalite', 'total_ht', 'total_ttc', 'idModule', 'project_inter_privacy', 'sub_name', 'idSubContractor', 'idCfp', 'cfp_name', 'headYear', 'headMonthDebut', 'headMonthFin', 'headDayDebut', 'headDayFin', 'total_ht_sub_contractor')
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                });
            })
            ->where('module_name', '!=', 'Default module')
            ->groupBy('idProjet', 'dateDebut', 'idEtp', 'dateFin', 'module_name', 'etp_name', 'li_name', 'ville', 'project_status', 'project_reference', 'project_description', 'project_type', 'paiement', 'module_image', 'etp_logo', 'etp_initial_name', 'idSalle', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'modalite', 'total_ht', 'total_ttc', 'idModule', 'project_inter_privacy', 'sub_name', 'idSubContractor', 'idCfp', 'cfp_name', 'headYear', 'headMonthDebut', 'headMonthFin', 'headDayDebut', 'headDayFin', 'total_ht_sub_contractor');

        return $query;
    }

    public function countProjectByReference($key, $idCustomer)
    {
        return DB::table('v_projet_cfps')
            ->select('idProjet')
            ->where('project_reference', 'like', "%$key%")
            ->where('project_is_trashed', 0)
            ->where('idCfp', $idCustomer)
            ->where('project_is_active', 1)
            ->count();
    }

    public function countProjectByCity($key, $idCustomer)
    {
        return DB::table('v_projet_cfps')
            ->select('idProjet')
            ->where('ville', 'like', "%$key%")
            ->where('project_is_trashed', 0)
            ->where(function ($query) use ($idCustomer) {
                $query->where('idCfp', $idCustomer)
                    ->orWhere('idCfp_inter', $idCustomer)
                    ->orWhere('idSubContractor', $idCustomer);
            })
            ->where('project_is_active', 1)
            ->count();
    }

    public function countProjectByNeighborhood($key, $idCustomer)
    {
        return DB::table('v_projet_cfps')
            ->select('idProjet')
            ->where('salle_quartier', 'like', "%$key%")
            ->where(function ($query) use ($idCustomer) {
                $query->where('idCfp', $idCustomer)
                    ->orWhere('idCfp_inter', $idCustomer)
                    ->orWhere('idSubContractor', $idCustomer);
            })
            ->where('project_is_trashed', 0)
            ->where('project_is_active', 1)
            ->count();
    }

    public function countProjectByPlace($key, $idCustomer)
    {
        return DB::table('v_projet_cfps')
            ->select('idProjet')
            ->where('li_name', 'like', "%$key%")
            ->where(function ($query) use ($idCustomer) {
                $query->where('idCfp', $idCustomer)
                    ->orWhere('idCfp_inter', $idCustomer)
                    ->orWhere('idSubContractor', $idCustomer);
            })
            ->where('project_is_trashed', 0)
            ->where('project_is_active', 1)
            ->count();
    }

    public function getProjectByCity($key, $idCustomer, $perPage = 6, $page = 1)
    {
        $query = DB::table('v_projet_cfps')
            ->select('idProjet', 'dateDebut', 'idEtp', 'dateFin', 'module_name', 'etp_name', 'ville', 'li_name', 'project_status', 'project_reference', 'project_description', 'project_type', 'paiement', DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'), 'module_image', 'etp_logo', 'etp_initial_name', 'idSalle', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'modalite', 'total_ht', 'total_ttc', 'idModule', 'project_inter_privacy', 'sub_name', 'idSubContractor', 'idCfp', 'cfp_name', 'headYear', 'headMonthDebut', 'headMonthFin', 'headDayDebut', 'headDayFin')
            ->where('ville', 'like', "%$key%")
            ->where('project_is_trashed', 0)
            ->where(function ($query) use ($idCustomer) {
                $query->where('idCfp', $idCustomer)
                    ->orWhere('idCfp_inter', $idCustomer)
                    ->orWhere('idSubContractor', $idCustomer);
            })
            ->where('project_is_active', 1);

        $totalItems = $query->count();
        $totalPages = ceil($totalItems / $perPage);

        // Calculate offset
        $offset = ($page - 1) * $perPage;

        // Get paginated results
        $projects = $query->offset($offset)->limit($perPage)->get();

        $results = [];
        foreach ($projects as $project) {
            $results[] = [
                'reference' => $project->project_reference,
                'dossier' => $this->getNomDossier($project->idProjet),
                'nbDocument' => $this->getNombreDocument($project->idProjet),
                'seanceCount' => $this->getSessionProject($project->idProjet),
                'formateurs' => $this->getFormProject($project->idProjet),
                'apprCount' => $this->getApprenantProject($project->idProjet, $project->idCfp_inter),
                'allApprenant' => $this->getAllApprenantProject($project->idProjet, $project->idCfp_inter),
                'allCustomer' => $this->getCustomer($project->idProjet),
                'allForms' => $this->getLearnerProject($project->idProjet),
                'projectTotalPrice' => $this->getProjectTotalPrice($project->idProjet),
                'totalSessionHour' => $this->getSessionHour($project->idProjet),
                'general_note' => $this->getNote($project->idProjet),
                'id' => $project->idProjet,
                'idCfp_inter' => $project->idCfp_inter,
                'startDate' => $project->dateDebut,
                'endDate' => $project->dateFin,
                'module_name' => $project->module_name,
                'partCount' => $this->getParticulierProject($project->idProjet, $project->idCfp_inter),
                'etp_name' => $this->getEtpProjectInter($project->idProjet, $project->idCfp_inter),
                'idEtp' => $project->idEtp,
                'ville' => $project->ville,
                'li_name' => $project->li_name,
                'status' => $project->project_status,
                'type' => $project->project_type,
                'paiement' => $project->paiement,
                'modalite' => $project->modalite,
                'description' => $project->project_description,
                'headDate' => $project->headDate,
                'module_image' => $project->module_image,
                'etp_logo' => $project->etp_logo,
                'etp_initial_name' => $project->etp_initial_name,
                'salle_name' => $project->salle_name,
                'salle_quartier' => $project->salle_quartier,
                'salle_code_postal' => $project->salle_code_postal,
                'ville' => $project->ville,
                'etp_name_in_situ' => $project->etp_name,
                'total_ht' => $this->formatPrice($project->total_ht),
                'total_ttc' => $project->total_ttc,
                'idModule' => $project->idModule,
                'restaurations' => $this->getRestauration($project->idProjet),
                'project_inter_privacy' => $project->project_inter_privacy,
                'checkEmg' => $this->checkEmg($project->idProjet),
                'checkEval' => $this->checkEval($project->idProjet),
                'avg_before' => $this->averageEvalApprenant($project->idProjet)->avg_avant,
                'avg_after' => $this->averageEvalApprenant($project->idProjet)->avg_apres,
                'apprs' => $this->getApprListProjet($project->idProjet),
                'sub_name' => $project->sub_name,
                'idSubContractor' => $project->idSubContractor,
                'idCfp' => $project->idCfp,
                'cfp_name' => $project->cfp_name,
                'idUser' => $idCustomer
            ];
        }

        return response()->json([
            'data' => $results,
            'pagination' => [
                'current_page' => (int) $page,
                'last_page' => (int) $totalPages,
                'per_page' => (int) $perPage,
                'total' => (int) $totalItems,
                'from' => $totalItems > 0 ? $offset + 1 : 0,
                'to' => min($offset + $perPage, $totalItems),
                'has_more' => $page < $totalPages,
            ],
            'success' => true
        ]);
    }

    public function getProjectByNeighborhood($key, $idCustomer, $perPage = 6, $page = 1)
    {
        $query = DB::table('v_projet_cfps')
            ->select('idProjet', 'dateDebut', 'idEtp', 'dateFin', 'module_name', 'etp_name', 'ville', 'li_name', 'project_status', 'project_reference', 'project_description', 'project_type', 'paiement', DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'), 'module_image', 'etp_logo', 'etp_initial_name', 'idSalle', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'modalite', 'total_ht', 'total_ttc', 'idModule', 'project_inter_privacy', 'sub_name', 'idSubContractor', 'idCfp', 'cfp_name', 'headYear', 'headMonthDebut', 'headMonthFin', 'headDayDebut', 'headDayFin')
            ->where('salle_quartier', 'like', "%$key%")
            ->where(function ($query) use ($idCustomer) {
                $query->where('idCfp', $idCustomer)
                    ->orWhere('idCfp_inter', $idCustomer)
                    ->orWhere('idSubContractor', $idCustomer);
            })
            ->where('project_is_trashed', 0)
            ->where('project_is_active', 1);

        $totalItems = $query->count();
        $totalPages = ceil($totalItems / $perPage);

        // Calculate offset
        $offset = ($page - 1) * $perPage;

        // Get paginated results
        $projects = $query->offset($offset)->limit($perPage)->get();

        $results = [];
        foreach ($projects as $project) {
            $results[] = [
                'reference' => $project->project_reference,
                'dossier' => $this->getNomDossier($project->idProjet),
                'nbDocument' => $this->getNombreDocument($project->idProjet),
                'seanceCount' => $this->getSessionProject($project->idProjet),
                'formateurs' => $this->getFormProject($project->idProjet),
                'apprCount' => $this->getApprenantProject($project->idProjet, $project->idCfp_inter),
                'allApprenant' => $this->getAllApprenantProject($project->idProjet, $project->idCfp_inter),
                'allCustomer' => $this->getCustomer($project->idProjet),
                'allForms' => $this->getLearnerProject($project->idProjet),
                'projectTotalPrice' => $this->getProjectTotalPrice($project->idProjet),
                'totalSessionHour' => $this->getSessionHour($project->idProjet),
                'general_note' => $this->getNote($project->idProjet),
                'id' => $project->idProjet,
                'idCfp_inter' => $project->idCfp_inter,
                'startDate' => $project->dateDebut,
                'endDate' => $project->dateFin,
                'module_name' => $project->module_name,
                'partCount' => $this->getParticulierProject($project->idProjet, $project->idCfp_inter),
                'etp_name' => $this->getEtpProjectInter($project->idProjet, $project->idCfp_inter),
                'idEtp' => $project->idEtp,
                'ville' => $project->ville,
                'li_name' => $project->li_name,
                'status' => $project->project_status,
                'type' => $project->project_type,
                'paiement' => $project->paiement,
                'modalite' => $project->modalite,
                'description' => $project->project_description,
                'headDate' => $project->headDate,
                'module_image' => $project->module_image,
                'etp_logo' => $project->etp_logo,
                'etp_initial_name' => $project->etp_initial_name,
                'salle_name' => $project->salle_name,
                'salle_quartier' => $project->salle_quartier,
                'salle_code_postal' => $project->salle_code_postal,
                'ville' => $project->ville,
                'etp_name_in_situ' => $project->etp_name,
                'total_ht' => $this->formatPrice($project->total_ht),
                'total_ttc' => $project->total_ttc,
                'idModule' => $project->idModule,
                'restaurations' => $this->getRestauration($project->idProjet),
                'project_inter_privacy' => $project->project_inter_privacy,
                'checkEmg' => $this->checkEmg($project->idProjet),
                'checkEval' => $this->checkEval($project->idProjet),
                'avg_before' => $this->averageEvalApprenant($project->idProjet)->avg_avant,
                'avg_after' => $this->averageEvalApprenant($project->idProjet)->avg_apres,
                'apprs' => $this->getApprListProjet($project->idProjet),
                'sub_name' => $project->sub_name,
                'idSubContractor' => $project->idSubContractor,
                'idCfp' => $project->idCfp,
                'cfp_name' => $project->cfp_name,
                'idUser' => $idCustomer
            ];
        }

        return response()->json([
            'data' => $results,
            'pagination' => [
                'current_page' => (int) $page,
                'last_page' => (int) $totalPages,
                'per_page' => (int) $perPage,
                'total' => (int) $totalItems,
                'from' => $totalItems > 0 ? $offset + 1 : 0,
                'to' => min($offset + $perPage, $totalItems),
                'has_more' => $page < $totalPages,
            ],
            'success' => true
        ]);
    }

    public function getProjectByReference($key, $idCustomer, $perPage, $page)
    {
        $query = DB::table('v_projet_cfps')
            ->select('idProjet', 'dateDebut', 'idEtp', 'dateFin', 'module_name', 'etp_name', 'ville', 'li_name', 'project_status', 'project_reference', 'project_description', 'project_type', 'paiement', DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'), 'module_image', 'etp_logo', 'etp_initial_name', 'idSalle', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'modalite', 'total_ht', 'total_ttc', 'idModule', 'project_inter_privacy', 'sub_name', 'idSubContractor', 'idCfp', 'cfp_name', 'headYear', 'headMonthDebut', 'headMonthFin', 'headDayDebut', 'headDayFin')
            ->where('project_reference', 'like', "%$key%")
            ->where('project_is_trashed', 0)
            ->where('idCfp', $idCustomer)
            ->where('project_is_active', 1);

        $totalItems = $query->count();
        $totalPages = ceil($totalItems / $perPage);

        // Calculate offset
        $offset = ($page - 1) * $perPage;

        // Get paginated results
        $projects = $query->offset($offset)->limit($perPage)->get();

        $results = [];
        foreach ($projects as $project) {
            $results[] = [
                'reference' => $project->project_reference,
                'dossier' => $this->getNomDossier($project->idProjet),
                'nbDocument' => $this->getNombreDocument($project->idProjet),
                'seanceCount' => $this->getSessionProject($project->idProjet),
                'formateurs' => $this->getFormProject($project->idProjet),
                'apprCount' => $this->getApprenantProject($project->idProjet, $project->idCfp_inter),
                'allApprenant' => $this->getAllApprenantProject($project->idProjet, $project->idCfp_inter),
                'allCustomer' => $this->getCustomer($project->idProjet),
                'allForms' => $this->getLearnerProject($project->idProjet),
                'projectTotalPrice' => $this->getProjectTotalPrice($project->idProjet),
                'totalSessionHour' => $this->getSessionHour($project->idProjet),
                'general_note' => $this->getNote($project->idProjet),
                'id' => $project->idProjet,
                'idCfp_inter' => $project->idCfp_inter,
                'startDate' => $project->dateDebut,
                'endDate' => $project->dateFin,
                'module_name' => $project->module_name,
                'partCount' => $this->getParticulierProject($project->idProjet, $project->idCfp_inter),
                'etp_name' => $this->getEtpProjectInter($project->idProjet, $project->idCfp_inter),
                'idEtp' => $project->idEtp,
                'ville' => $project->ville,
                'li_name' => $project->li_name,
                'status' => $project->project_status,
                'type' => $project->project_type,
                'paiement' => $project->paiement,
                'modalite' => $project->modalite,
                'description' => $project->project_description,
                'headDate' => $project->headDate,
                'module_image' => $project->module_image,
                'etp_logo' => $project->etp_logo,
                'etp_initial_name' => $project->etp_initial_name,
                'salle_name' => $project->salle_name,
                'salle_quartier' => $project->salle_quartier,
                'salle_code_postal' => $project->salle_code_postal,
                'ville' => $project->ville,
                'etp_name_in_situ' => $project->etp_name,
                'total_ht' => $this->formatPrice($project->total_ht),
                'total_ttc' => $project->total_ttc,
                'idModule' => $project->idModule,
                'restaurations' => $this->getRestauration($project->idProjet),
                'project_inter_privacy' => $project->project_inter_privacy,
                'checkEmg' => $this->checkEmg($project->idProjet),
                'checkEval' => $this->checkEval($project->idProjet),
                'avg_before' => $this->averageEvalApprenant($project->idProjet)->avg_avant,
                'avg_after' => $this->averageEvalApprenant($project->idProjet)->avg_apres,
                'apprs' => $this->getApprListProjet($project->idProjet),
                'sub_name' => $project->sub_name,
                'idSubContractor' => $project->idSubContractor,
                'idCfp' => $project->idCfp,
                'cfp_name' => $project->cfp_name,
                'idUser' => $idCustomer
            ];
        }

        return response()->json([
            'data' => $results,
            'pagination' => [
                'current_page' => (int) $page,
                'last_page' => (int) $totalPages,
                'per_page' => (int) $perPage,
                'total' => (int) $totalItems,
                'from' => $totalItems > 0 ? $offset + 1 : 0,
                'to' => min($offset + $perPage, $totalItems),
                'has_more' => $page < $totalPages,
            ],
            'success' => true
        ]);
    }

    public function getProjectByEtpWithPaginate($idCustomer, $idEtp, $perPage = 9, $page = 1)
    {
        $query = DB::table('v_projet_cfps')
            ->select(
                'idProjet',
                'dateDebut',
                'idEtp',
                'dateFin',
                'module_name',
                'li_name',
                'etp_name',
                'ville',
                'project_status',
                'project_reference',
                'project_description',
                'project_type',
                'paiement',
                DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'),
                'module_image',
                'etp_logo',
                'etp_initial_name',
                'idSalle',
                'salle_name',
                'salle_quartier',
                'salle_code_postal',
                'ville',
                'idCfp_inter',
                'modalite',
                'total_ht',
                'total_ttc',
                'idModule',
                'project_inter_privacy',
                'sub_name',
                'idSubContractor',
                'idCfp',
                'cfp_name',
                'headYear',
                'headMonthDebut',
                'headMonthFin',
                'headDayDebut',
                'headDayFin'
            )
            ->where(function ($query) use ($idCustomer) {
                $query->where(function ($query) use ($idCustomer) {
                    $query->where('idCfp', $idCustomer)
                        ->orWhere('idCfp_inter', $idCustomer)
                        ->orWhere('idSubContractor', $idCustomer);
                });
            })
            ->where('idEtp', $idEtp)
            ->where('project_is_trashed', 0)
            ->where('project_is_active', 1)
            ->orderBy('dateDebut', 'desc');

        // Get total count for pagination
        $totalItems = $query->count();
        $totalPages = ceil($totalItems / $perPage);

        // Calculate offset
        $offset = ($page - 1) * $perPage;

        // Get paginated results
        $projects = $query->offset($offset)->limit($perPage)->get();

        $results = [];
        foreach ($projects as $project) {
            $results[] = [
                'reference' => $project->project_reference,
                'dossier' => $this->getNomDossier($project->idProjet),
                'nbDocument' => $this->getNombreDocument($project->idProjet),
                'seanceCount' => $this->getSessionProject($project->idProjet),
                'formateurs' => $this->getFormProject($project->idProjet),
                'apprCount' => $this->getApprenantProject($project->idProjet, $project->idCfp_inter),
                'allApprenant' => $this->getAllApprenantProject($project->idProjet, $project->idCfp_inter),
                'allCustomer' => $this->getCustomer($project->idProjet),
                'allForms' => $this->getLearnerProject($project->idProjet),
                'projectTotalPrice' => $this->getProjectTotalPrice($project->idProjet),
                'totalSessionHour' => $this->getSessionHour($project->idProjet),
                'general_note' => $this->getNote($project->idProjet),
                'id' => $project->idProjet,
                'idCfp_inter' => $project->idCfp_inter,
                'startDate' => $project->dateDebut,
                'endDate' => $project->dateFin,
                'module_name' => $project->module_name,
                'partCount' => $this->getParticulierProject($project->idProjet, $project->idCfp_inter),
                'etp_name' => $this->getEtpProjectInter($project->idProjet, $project->idCfp_inter),
                'idEtp' => $project->idEtp,
                'ville' => $project->ville,
                'li_name' => $project->li_name,
                'status' => $project->project_status,
                'type' => $project->project_type,
                'paiement' => $project->paiement,
                'modalite' => $project->modalite,
                'description' => $project->project_description,
                'headDate' => $project->headDate,
                'module_image' => $project->module_image,
                'etp_logo' => $project->etp_logo,
                'etp_initial_name' => $project->etp_initial_name,
                'salle_name' => $project->salle_name,
                'salle_quartier' => $project->salle_quartier,
                'salle_code_postal' => $project->salle_code_postal,
                'ville' => $project->ville,
                'etp_name_in_situ' => $project->etp_name,
                'total_ht' => $this->formatPrice($project->total_ht),
                'total_ttc' => $project->total_ttc,
                'idModule' => $project->idModule,
                'restaurations' => $this->getRestauration($project->idProjet),
                'project_inter_privacy' => $project->project_inter_privacy,
                'checkEmg' => $this->checkEmg($project->idProjet),
                'checkEval' => $this->checkEval($project->idProjet),
                'avg_before' => $this->averageEvalApprenant($project->idProjet)->avg_avant,
                'avg_after' => $this->averageEvalApprenant($project->idProjet)->avg_apres,
                'apprs' => $this->getApprListProjet($project->idProjet),
                'sub_name' => $project->sub_name,
                'idSubContractor' => $project->idSubContractor,
                'idCfp' => $project->idCfp,
                'cfp_name' => $project->cfp_name,
                'idUser' => $idCustomer
            ];
        }

        return response()->json([
            'data' => $results,
            'pagination' => [
                'current_page' => (int) $page,
                'last_page' => (int) $totalPages,
                'per_page' => (int) $perPage,
                'total' => (int) $totalItems,
                'from' => $totalItems > 0 ? $offset + 1 : 0,
                'to' => min($offset + $perPage, $totalItems),
                'has_more' => $page < $totalPages,
            ],
            'success' => true
        ]);
    }

    public function getProjectCfpWithPagination($idCustomer, $key, $perPage = 9, $page = 1)
    {
        $query = DB::table('v_projet_cfps')
            ->select(
                'idProjet',
                'dateDebut',
                'idEtp',
                'dateFin',
                'module_name',
                'li_name',
                'etp_name',
                'ville',
                'project_status',
                'project_reference',
                'project_description',
                'project_type',
                'paiement',
                DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'),
                'module_image',
                'etp_logo',
                'etp_initial_name',
                'idSalle',
                'salle_name',
                'salle_quartier',
                'salle_code_postal',
                'ville',
                'idCfp_inter',
                'modalite',
                'total_ht',
                'total_ttc',
                'idModule',
                'project_inter_privacy',
                'sub_name',
                'idSubContractor',
                'idCfp',
                'cfp_name',
                'headYear',
                'headMonthDebut',
                'headMonthFin',
                'headDayDebut',
                'headDayFin'
            )
            ->where(function ($query) use ($idCustomer) {
                $query->where(function ($query) use ($idCustomer) {
                    $query->where('idCfp', $idCustomer)
                        ->orWhere('idCfp_inter', $idCustomer)
                        ->orWhere('idSubContractor', $idCustomer);
                });
            })
            ->where('module_name', 'like', "%$key%")
            ->where('project_is_trashed', 0)
            ->where('project_is_active', 1)
            ->orderBy('dateDebut', 'desc');

        // Get total count for pagination
        $totalItems = $query->count();
        $totalPages = ceil($totalItems / $perPage);

        // Calculate offset
        $offset = ($page - 1) * $perPage;

        // Get paginated results
        $projects = $query->offset($offset)->limit($perPage)->get();

        $results = [];
        foreach ($projects as $project) {
            $results[] = [
                'reference' => $project->project_reference,
                'dossier' => $this->getNomDossier($project->idProjet),
                'nbDocument' => $this->getNombreDocument($project->idProjet),
                'seanceCount' => $this->getSessionProject($project->idProjet),
                'formateurs' => $this->getFormProject($project->idProjet),
                'apprCount' => $this->getApprenantProject($project->idProjet, $project->idCfp_inter),
                'allApprenant' => $this->getAllApprenantProject($project->idProjet, $project->idCfp_inter),
                'allCustomer' => $this->getCustomer($project->idProjet),
                'allForms' => $this->getLearnerProject($project->idProjet),
                'projectTotalPrice' => $this->getProjectTotalPrice($project->idProjet),
                'totalSessionHour' => $this->getSessionHour($project->idProjet),
                'general_note' => $this->getNote($project->idProjet),
                'id' => $project->idProjet,
                'idCfp_inter' => $project->idCfp_inter,
                'startDate' => $project->dateDebut,
                'endDate' => $project->dateFin,
                'module_name' => $project->module_name,
                'partCount' => $this->getParticulierProject($project->idProjet, $project->idCfp_inter),
                'etp_name' => $this->getEtpProjectInter($project->idProjet, $project->idCfp_inter),
                'idEtp' => $project->idEtp,
                'ville' => $project->ville,
                'li_name' => $project->li_name,
                'status' => $project->project_status,
                'type' => $project->project_type,
                'paiement' => $project->paiement,
                'modalite' => $project->modalite,
                'description' => $project->project_description,
                'headDate' => $project->headDate,
                'module_image' => $project->module_image,
                'etp_logo' => $project->etp_logo,
                'etp_initial_name' => $project->etp_initial_name,
                'salle_name' => $project->salle_name,
                'salle_quartier' => $project->salle_quartier,
                'salle_code_postal' => $project->salle_code_postal,
                'ville' => $project->ville,
                'etp_name_in_situ' => $project->etp_name,
                'total_ht' => $this->formatPrice($project->total_ht),
                'total_ttc' => $project->total_ttc,
                'idModule' => $project->idModule,
                'restaurations' => $this->getRestauration($project->idProjet),
                'project_inter_privacy' => $project->project_inter_privacy,
                'checkEmg' => $this->checkEmg($project->idProjet),
                'checkEval' => $this->checkEval($project->idProjet),
                'avg_before' => $this->averageEvalApprenant($project->idProjet)->avg_avant,
                'avg_after' => $this->averageEvalApprenant($project->idProjet)->avg_apres,
                'apprs' => $this->getApprListProjet($project->idProjet),
                'sub_name' => $project->sub_name,
                'idSubContractor' => $project->idSubContractor,
                'idCfp' => $project->idCfp,
                'cfp_name' => $project->cfp_name,
                'idUser' => $idCustomer
            ];
        }

        return response()->json([
            'data' => $results,
            'pagination' => [
                'current_page' => (int) $page,
                'last_page' => (int) $totalPages,
                'per_page' => (int) $perPage,
                'total' => (int) $totalItems,
                'from' => $totalItems > 0 ? $offset + 1 : 0,
                'to' => min($offset + $perPage, $totalItems),
                'has_more' => $page < $totalPages,
            ],
            'success' => true
        ]);
    }

    public function getProjectByPlace($key, $idCustomer, $perPage = 6, $page = 1)
    {
        $query = DB::table('v_projet_cfps')
            ->select('idProjet', 'dateDebut', 'idEtp', 'dateFin', 'module_name', 'etp_name', 'ville', 'li_name', 'project_status', 'project_reference', 'project_description', 'project_type', 'paiement', DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'), 'module_image', 'etp_logo', 'etp_initial_name', 'idSalle', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'modalite', 'total_ht', 'total_ttc', 'idModule', 'project_inter_privacy', 'sub_name', 'idSubContractor', 'idCfp', 'cfp_name', 'headYear', 'headMonthDebut', 'headMonthFin', 'headDayDebut', 'headDayFin')
            ->where('li_name', 'like', "%$key%")
            ->where(function ($query) use ($idCustomer) {
                $query->where('idCfp', $idCustomer)
                    ->orWhere('idCfp_inter', $idCustomer)
                    ->orWhere('idSubContractor', $idCustomer);
            })
            ->where('project_is_trashed', 0)
            ->where('project_is_active', 1);

        $totalItems = $query->count();
        $totalPages = ceil($totalItems / $perPage);

        // Calculate offset
        $offset = ($page - 1) * $perPage;

        // Get paginated results
        $projects = $query->offset($offset)->limit($perPage)->get();

        $results = [];
        foreach ($projects as $project) {
            $results[] = [
                'reference' => $project->project_reference,
                'dossier' => $this->getNomDossier($project->idProjet),
                'nbDocument' => $this->getNombreDocument($project->idProjet),
                'seanceCount' => $this->getSessionProject($project->idProjet),
                'formateurs' => $this->getFormProject($project->idProjet),
                'apprCount' => $this->getApprenantProject($project->idProjet, $project->idCfp_inter),
                'allApprenant' => $this->getAllApprenantProject($project->idProjet, $project->idCfp_inter),
                'allCustomer' => $this->getCustomer($project->idProjet),
                'allForms' => $this->getLearnerProject($project->idProjet),
                'projectTotalPrice' => $this->getProjectTotalPrice($project->idProjet),
                'totalSessionHour' => $this->getSessionHour($project->idProjet),
                'general_note' => $this->getNote($project->idProjet),
                'id' => $project->idProjet,
                'idCfp_inter' => $project->idCfp_inter,
                'startDate' => $project->dateDebut,
                'endDate' => $project->dateFin,
                'module_name' => $project->module_name,
                'partCount' => $this->getParticulierProject($project->idProjet, $project->idCfp_inter),
                'etp_name' => $this->getEtpProjectInter($project->idProjet, $project->idCfp_inter),
                'idEtp' => $project->idEtp,
                'ville' => $project->ville,
                'li_name' => $project->li_name,
                'status' => $project->project_status,
                'type' => $project->project_type,
                'paiement' => $project->paiement,
                'modalite' => $project->modalite,
                'description' => $project->project_description,
                'headDate' => $project->headDate,
                'module_image' => $project->module_image,
                'etp_logo' => $project->etp_logo,
                'etp_initial_name' => $project->etp_initial_name,
                'salle_name' => $project->salle_name,
                'salle_quartier' => $project->salle_quartier,
                'salle_code_postal' => $project->salle_code_postal,
                'ville' => $project->ville,
                'etp_name_in_situ' => $project->etp_name,
                'total_ht' => $this->formatPrice($project->total_ht),
                'total_ttc' => $project->total_ttc,
                'idModule' => $project->idModule,
                'restaurations' => $this->getRestauration($project->idProjet),
                'project_inter_privacy' => $project->project_inter_privacy,
                'checkEmg' => $this->checkEmg($project->idProjet),
                'checkEval' => $this->checkEval($project->idProjet),
                'avg_before' => $this->averageEvalApprenant($project->idProjet)->avg_avant,
                'avg_after' => $this->averageEvalApprenant($project->idProjet)->avg_apres,
                'apprs' => $this->getApprListProjet($project->idProjet),
                'sub_name' => $project->sub_name,
                'idSubContractor' => $project->idSubContractor,
                'idCfp' => $project->idCfp,
                'cfp_name' => $project->cfp_name,
                'idUser' => $idCustomer
            ];
        }

        return response()->json([
            'data' => $results,
            'pagination' => [
                'current_page' => (int) $page,
                'last_page' => (int) $totalPages,
                'per_page' => (int) $perPage,
                'total' => (int) $totalItems,
                'from' => $totalItems > 0 ? $offset + 1 : 0,
                'to' => min($offset + $perPage, $totalItems),
                'has_more' => $page < $totalPages,
            ],
            'success' => true
        ]);
    }

    public function countByStatus($idCustomer, string $status): int
    {
        $query = DB::table('v_projet_cfps')
            ->where(function ($query) use ($idCustomer) {
                $query->where('idCfp', $idCustomer)
                    ->orWhere('idCfp_inter', $idCustomer)
                    ->orWhere('idSubContractor', $idCustomer);
            })
            ->where('module_name', '!=', 'Default module')
            ->where('project_status', $status);



        return $query->count();
    }

    public function indexFilter($idCustomer, $status = null): mixed
    {
        return DB::table('v_projet_cfps')
            ->select(
                'idProjet',
                'dateDebut',
                'idEtp',
                'dateFin',
                'module_name',
                'etp_name',
                'li_name',
                'ville',
                'project_status',
                'project_reference',
                'project_description',
                'project_type',
                'paiement',
                DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'),
                'module_image',
                'etp_logo',
                'etp_initial_name',
                'idSalle',
                'salle_name',
                'salle_quartier',
                'salle_code_postal',
                'idCfp_inter',
                'modalite',
                'total_ht',
                'total_ttc',
                'idModule',
                'project_inter_privacy',
                'sub_name',
                'idSubContractor',
                'idCfp',
                'cfp_name',
                'headYear',
                'headMonthDebut',
                'headMonthFin',
                'headDayDebut',
                'headDayFin',
                'total_ht_sub_contractor'
            )
            ->where(function ($query) use ($idCustomer) {
                $query->where('idCfp', $idCustomer)
                    ->orWhere('idCfp_inter', $idCustomer)
                    ->orWhere('idSubContractor', $idCustomer);
            })
            ->where('module_name', '!=', 'Default module')
            ->when($status, fn($q) => $q->where('project_status', $status))
            ->groupBy(
                'idProjet',
                'dateDebut',
                'idEtp',
                'dateFin',
                'module_name',
                'etp_name',
                'li_name',
                'ville',
                'project_status',
                'project_reference',
                'project_description',
                'project_type',
                'paiement',
                'module_image',
                'etp_logo',
                'etp_initial_name',
                'idSalle',
                'salle_name',
                'salle_quartier',
                'salle_code_postal',
                'ville',
                'idCfp_inter',
                'modalite',
                'total_ht',
                'total_ttc',
                'idModule',
                'project_inter_privacy',
                'sub_name',
                'idSubContractor',
                'idCfp',
                'cfp_name',
                'headYear',
                'headMonthDebut',
                'headMonthFin',
                'headDayDebut',
                'headDayFin',
                'total_ht_sub_contractor'
            )
            ->orderBy('dateDebut', 'desc')
            ->get();
    }

    public function indexStatus($idCustomer, $status): array
    {
        $query = $this->index($idCustomer)->where('project_status', $status)->orderBy('dateDebut', 'asc')->get();

        return $query->toArray();
    }

    public function countProjectCfp($idCustomer, $key)
    {
        return DB::table('v_projet_cfps')
            ->select('idProjet')
            ->where(function ($query) use ($idCustomer) {
                $query->where('idCfp', $idCustomer)
                    ->orWhere('idCfp_inter', $idCustomer)
                    ->orWhere('idSubContractor', $idCustomer);
            })
            ->where('module_name', 'like', "%$key%")
            ->where('project_is_trashed', 0)
            ->where('project_is_active', 1)
            ->count();
    }

    public function indexByCfp(int|array|null $idFormateur = null, int $idCustomer, ?string $status = null, array $filters = []): mixed
    {
        return $this->baseQuery($idFormateur, $status, $filters, $idCustomer);
    }

    public function indexByFormateur(int|array|null $idFormateur = null, ?string $status = null, array $filters = []): mixed
    {
        return $this->baseQuery($idFormateur, $status, $filters);
    }

    public function indexByApprenant(int|array $idApprenant, ?string $status = null, array $filters = []): mixed
    {
        $filters['Apprenant'] = $idApprenant;
        return $this->baseQuery(null, $status, $filters);
    }

    private function baseQuery(int|array|null $idFormateur = null, ?string $status = null, array $filters = [], int $idCustomer = null): mixed
    {
        $query = DB::table('v_projects')
            ->select(
                'idProjet',
                'dateDebut',
                'idEtp',
                'dateFin',
                'module_name',
                'etp_name',
                'li_name',
                'ville',
                'project_status',
                'project_reference',
                'project_description',
                'project_type',
                'paiement',
                DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'),
                'module_image',
                'etp_logo',
                'etp_initial_name',
                'idSalle',
                'salle_name',
                'salle_quartier',
                'salle_code_postal',
                'modalite',
                'total_ht',
                'total_ttc',
                'idModule',
                'project_inter_privacy',
                'sub_name',
                'idSubContractor',
                'idCfp',
                'cfp_name',
                'headYear',
                'headMonthDebut',
                'headMonthFin',
                'headDayDebut',
                'headDayFin',
                'total_ht_sub_contractor',
                'idType',
                'idTypeProjet'
            )
            ->where('module_name', '!=', 'Default module')
            ->when($status, fn($q) => $q->where('project_status', $status));

        // Filtre sur le client (seulement si $idCustomer est fourni)
        if ($idCustomer) {
            $query->where(function ($query) use ($idCustomer) {
                $query->where('idCfp', $idCustomer)
                    ->orWhere('idSubContractor', $idCustomer);
            });
        }

        // Filtre sur formateur
        $formateurIds = $filters['Formateur'] ?? $idFormateur;
        if (!empty($formateurIds)) {
            $ids = is_array($formateurIds) ? $formateurIds : [$formateurIds];
            $query->whereExists(function ($q) use ($ids) {
                $q->select(DB::raw(1))
                    ->from('v_formateur_cfps')
                    ->whereColumn('v_formateur_cfps.idProjet', 'v_projet_cfps.idProjet')
                    ->whereIn('v_formateur_cfps.idFormateur', $ids);
            });
        }

        // Filtres dynamiques
        if (!empty($filters['Ville'])) {
            $query->whereIn('li_name', (array)$filters['Ville']);
        }

        if (!empty($filters['Projet'])) {
            $query->whereIn('project_type', (array)$filters['Projet']);
        }

        if (!empty($filters['Entreprise'])) {
            $query->whereIn('idEtp', (array)$filters['Entreprise']);
        }

        if (!empty($filters['Cours'])) {
            $query->whereIn('idModule', (array)$filters['Cours']);
        }

        if (!empty($filters['Mois'])) {
            $mois = (array)$filters['Mois'];
            $query->where(function ($q) use ($mois) {
                foreach ($mois as $m) {
                    $q->orWhereRaw('DATE_FORMAT(dateDebut, "%Y-%m") = ?', [$m]);
                }
            });
        }

        if (!empty($filters['Periode'])) {
            $today = now();
            $periods = [
                'prev_3_month' => [$today->copy()->subMonths(3), $today],
                'prev_6_month' => [$today->copy()->subMonths(6), $today],
                'prev_12_month' => [$today->copy()->subMonths(12), $today],
                'next_3_month' => [$today, $today->copy()->addMonths(3)],
                'next_6_month' => [$today, $today->copy()->addMonths(6)],
                'next_12_month' => [$today, $today->copy()->addMonths(12)],
            ];
            if (isset($periods[$filters['Periode']])) {
                [$from, $to] = $periods[$filters['Periode']];
                $query->whereBetween('dateDebut', [$from, $to]);
            }
        }
        // ✅ Filtre sur apprenant
        if (!empty($filters['Apprenant'])) {
            $apprenantIds = is_array($filters['Apprenant']) ? $filters['Apprenant'] : [$filters['Apprenant']];
            $query->whereExists(function ($q) use ($apprenantIds) {
                $q->select(DB::raw(1))
                    ->from('v_list_apprenants') // Vue pour les apprenants internes
                    ->whereColumn('v_list_apprenants.idProjet', 'v_projet_cfps.idProjet')
                    ->whereIn('v_list_apprenants.idEmploye', $apprenantIds)
                    ->unionAll(
                        DB::table('v_list_apprenant_inter_added') // Vue pour les apprenants ajoutés inter
                            ->select(DB::raw(1))
                            ->whereColumn('v_list_apprenant_inter_added.idProjet', 'v_projet_cfps.idProjet')
                            ->whereIn('v_list_apprenant_inter_added.idEmploye', $apprenantIds)
                    );
            });
        }


        // Group By
        $query->groupBy(
            'idProjet',
            'dateDebut',
            'idEtp',
            'dateFin',
            'module_name',
            'etp_name',
            'li_name',
            'ville',
            'project_status',
            'project_reference',
            'project_description',
            'project_type',
            'paiement',
            'module_image',
            'etp_logo',
            'etp_initial_name',
            'idSalle',
            'salle_name',
            'salle_quartier',
            'salle_code_postal',
            'modalite',
            'total_ht',
            'total_ttc',
            'idModule',
            'project_inter_privacy',
            'sub_name',
            'idSubContractor',
            'idCfp',
            'cfp_name',
            'headYear',
            'headMonthDebut',
            'headMonthFin',
            'headDayDebut',
            'headDayFin',
            'total_ht_sub_contractor'
        );

        return $query->orderBy('dateDebut', 'desc')->get();
    }

    public function store(
        $idCustomer,
        $reference = null,
        $title,
        $description = null,
        $isProjectReserved,
        $idModalite,
        $idModule,
        $idTypeProjet,
        $idSalle,
        $dateDebut = null,
        $dateFin = null
    ): mixed {
        DB::beginTransaction();
        $projet = DB::table('projets')->insertGetId([
            'project_reference' => $reference,
            'project_title' => $title,
            'project_description' => $description,
            'project_is_reserved' => $isProjectReserved,
            'idModalite' => $idModalite,
            'idCustomer' => $idCustomer,
            'idModule' => $idModule,
            'idTypeProjet' => $idTypeProjet,
            'idVilleCoded' => 1,
            'project_is_active' => 0,
            'idSalle' => $idSalle,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin
        ]);

        if ($idTypeProjet == 1) {
            DB::table('intras')->insert([
                'idProjet' => $projet,
                'idPaiement' => 3,
                'idEtp' => $idCustomer,
                'idCfp' => $idCustomer
            ]);
        } elseif ($idTypeProjet == 2) {
            DB::table('inters')->insert([
                'idProjet' => $projet,
                'idPaiement' => 3,
                'idCfp' => $idCustomer,
                'project_inter_privacy' => 0,
            ]);
        }
        DB::commit();

        return $projet;
    }

    public function show($idCustomer, $idProjet): mixed
    {
        $query = $this->index($idCustomer)->where('idProjet', $idProjet);

        return $query;
    }

    public function headDate($idCustomer): mixed
    {
        $query = DB::table('v_projet_cfps')
            ->select(DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'))
            ->groupBy('headDate')
            ->orderBy('dateDebut', 'asc')
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                });
            })
            ->where('module_name', '!=', 'Default module');

        return $query;
    }

    public function getProject($idCustomer): mixed
    {
        $query = DB::table('projets')->select('*');

        return $query;
    }

    public function getLearnerByProject($projectTypeId, $projectId)
    {
        if ($projectTypeId == 1) {
            return $this->getIntraLearner($projectId);
        } elseif ($projectTypeId == 2) {
            return $this->getInterLearner($projectId);
        } elseif ($projectTypeId == 4) {
            return $this->getParticularLearner($projectId);
        }

        return [];
    }

    public function getParticularLearner($projectId)
    {
        return DB::table('particulier_projet as P')
            ->join('users as U', 'U.id', 'P.idParticulier')
            ->select('U.id', 'U.name', 'U.firstName', 'U.photo')
            ->where('P.idProjet', $projectId)
            ->get();
    }

    public function getInterLearner($projectId)
    {
        return DB::table('detail_apprenant_inters as P')
            ->join('users as U', 'U.id', 'P.idEmploye')
            ->select('U.id', 'U.name', 'U.firstName', 'U.photo')
            ->where('P.idProjet', $projectId)
            ->get();
    }

    public function getIntraLearner($projectId)
    {
        return DB::table('detail_apprenants as P')
            ->join('users as U', 'U.id', 'P.idEmploye')
            ->select('U.id', 'U.name', 'U.firstName', 'U.photo')
            ->where('P.idProjet', $projectId)
            ->get();
    }


    public function getEntrepriseByProject($projectTypeId, $projectId)
    {
        if ($projectTypeId == 1) {
            return $this->getEntrepriseByProjectIntra($projectId);
        } elseif ($projectTypeId == 2) {
            return $this->getEntrepriseByProjectInter($projectId);
        }

        return [];
    }

    public function getEntrepriseByProjectInter($projectId)
    {
        return DB::table('inter_entreprises as I')
            ->join('customers as C', 'C.idCustomer', 'I.idEtp')
            ->select('C.customerName as name', 'C.idCustomer as id', 'C.logo', 'I.idEtp', 'C.customerEmail as email')
            ->where('I.idProjet', $projectId)
            ->get();
    }

    public function getEntrepriseByProjectIntra($projectId)
    {
        return DB::table('intras as I')
            ->join('customers as C', 'C.idCustomer', 'I.idEtp')
            ->select('C.customerName as name', 'C.idCustomer as id', 'C.logo', 'I.idEtp', 'C.customerEmail as email')
            ->where('I.idProjet', $projectId)
            ->get();
    }

    public function getProjectMaterials($idProjet)
    {
        $materials = DB::table('project_materials as PM')
            ->join('projets as P', 'PM.project_id', '=', 'P.idProjet')
            ->join('mdls', 'P.idModule', '=', 'mdls.idModule')
            ->join('materials as MTL', 'PM.material_id', '=', 'MTL.id')
            ->select(
                'PM.project_id',
                'PM.material_id',
                'MTL.name as material_name',
                'MTL.stock_number',
                'MTL.customer_id as cfp_id',
                'PM.number',
                'PM.created_at',
                'P.dateDebut as project_start_date',
                'P.dateFin as project_end_date',
                'P.idModule as module_id',
                'mdls.moduleName as module_name',
                'mdls.description as module_description',
                'mdls.module_image'
            )
            ->where('P.idCustomer', Customer::idCustomer())
            ->where('PM.project_id', $idProjet)
            ->get();

        return [
            'material_count' => $materials->count(),
            'material_items' => $materials
        ];
    }

    public function createProjectIntra($title, $description, $entreprisesId, $modalitiId, $purchaseOrderId, $folderId, $dateBegin, $dateEnd, $courseId, $paiementId)
    {
        try {
            $projectId = DB::table('projets')->insertGetId([
                'project_title' => $title,
                'project_description' => $description,
                'dateDebut' => $dateBegin,
                'dateFin' => $dateEnd,
                'idModalite' => $modalitiId,
                'idCustomer' => Customer::idCustomer(),
                'idModule' => $courseId,
                'idTypeProjet' => 1,
                'idDossier' => $folderId,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('intras')->insert([
                'idEtp' => $entreprisesId,
                'idProjet' => $projectId,
                'idCfp' => Customer::idCustomer(),
                'idPaiement' => $paiementId ? $paiementId : 3
            ]);

            return response()->json(['success' => true, 'project_id' => $projectId]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function createProjectFmfp($fmfpId)
    {
        $fmfp = DB::table('fmfp_projects')
            ->select('idEtp', 'start_date', 'end_date')
            ->where('id', $fmfpId)
            ->first();

        $modules = DB::table('fmfp_module_contents')
            ->where('idFmfp', $fmfpId)
            ->pluck('idModule');

        try {
            foreach ($modules as $moduleId) {
                $this->createProjectIntra(null, null, $fmfp->idEtp, 1, null, null, $fmfp->start_date, $fmfp->end_date, $moduleId, 2);
            }
            // return response()->json(['message' => 'project created successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
