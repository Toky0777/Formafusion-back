<?php

namespace App\Services;

use App\Models\Customer;
use App\Traits\Project;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    // This service can be used to handle project-related logic
    // For example, fetching projects, creating new projects, etc.
    use Project;

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
            ->where('module_name', '!=', 'Default module')
            ->where('project_is_active', 1)
            ->count();
    }

    public function getProjectCfp($idCustomer, $key)
    {
        $projects = DB::table('v_projet_cfps')
            ->select('idProjet', 'dateDebut', 'idEtp', 'dateFin', 'module_name', 'li_name', 'etp_name', 'ville', 'project_status', 'project_reference', 'project_description', 'project_type', 'paiement', DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'), 'module_image', 'etp_logo', 'etp_initial_name', 'idSalle', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'modalite', 'total_ht', 'total_ttc', 'idModule', 'project_inter_privacy', 'sub_name', 'idSubContractor', 'idCfp', 'cfp_name', 'headYear', 'headMonthDebut', 'headMonthFin', 'headDayDebut', 'headDayFin')
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
            ->where('module_name', '!=', 'Default module')
            ->orderBy('dateDebut', 'desc')
            ->get();

        $results = [];
        foreach ($projects as $project) {
            $results[] = [
                'reference' => $project->project_reference,
                'dossier' => $this->getNomDossier($project->idProjet),
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
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

        return $results;
    }

    public function countProjectEtp($key)
    {
        return DB::table('v_union_projets')
            ->select('project_status', DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where(function ($query) {
                $query->where('idEtp', Customer::idCustomer())
                    ->orWhere('idEtp_inter', Customer::idCustomer());
            })
            ->where(function ($query) {
                $query->where('project_type', 'Interne')
                    ->orWhere(function ($query) {
                        $query->whereIn('project_type', ['Intra', 'Inter'])
                            ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                    });
            })
            ->where('module_name', 'like', "%$key%")
            ->where('module_name', '!=', 'Default module')
            ->count();
    }

    public function getProjectEtp($key)
    {
        $projects =  DB::table('v_union_projets')
            ->select(
                'idProjet',
                'idEtp',
                'idEtp_inter',
                'idCfp_inter',
                'idModule',
                'project_reference',
                'project_status',
                'project_type',
                'project_description',
                'paiement',
                'modalite',
                'headDate',
                'dateDebut',
                'dateFin',
                'module_name',
                'module_image',
                'etp_name',
                'etp_logo',
                'etp_initial_name',
                'ville',
                'salle_name',
                'salle_quartier',
                'salle_code_postal',
                'total_ht',
                'total_ttc'
            )
            ->where(function ($query) {
                $query->where('idEtp', Customer::idCustomer())
                    ->orWhere('idEtp_inter', Customer::idCustomer());
            })
            ->where(function ($query) {
                $query->where('project_type', 'Interne')
                    ->orWhere(function ($query) {
                        $query->whereIn('project_type', ['Intra', 'Inter'])
                            ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                    });
            })
            ->where('module_name', 'like', "%$key%")
            ->where('module_name', '!=', 'Default module')
            ->get();

        $results = [];
        foreach ($projects as $project) {
            $results[] = [
                'reference' => $project->project_reference,
                'dossier' => $this->getNomDossier($project->idProjet),
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
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
                'startDate' => $project->dateDebut,
                'endDate' => $project->dateFin,
                'module_name' => $project->module_name,
                'partCount' => $this->getParticulierProject($project->idProjet, $project->idCfp_inter),
                'etp_name' => $this->getEtpProjectInter($project->idProjet, $project->idCfp_inter),
                'idEtp' => $project->idEtp,
                'ville' => $project->ville,
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
                'checkEmg' => $this->checkEmg($project->idProjet),
                'checkEval' => $this->checkEval($project->idProjet),
                'avg_before' => $this->averageEvalApprenant($project->idProjet)->avg_avant,
                'avg_after' => $this->averageEvalApprenant($project->idProjet)->avg_apres,
                'apprs' => $this->getApprListProjet($project->idProjet),
            ];
        }

        return $results;
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
            ->where('module_name', '!=', 'Default module')
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
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
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
            ->where('module_name', '!=', 'Default module')
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
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
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
            ->where('module_name', '!=', 'Default module')
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
            ->where('module_name', '!=', 'Default module')
            ->where('project_is_active', 1)
            ->count();
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
            ->where('module_name', '!=', 'Default module')
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
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
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
            ->where('module_name', '!=', 'Default module')
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
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
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
            ->where('module_name', '!=', 'Default module')
            ->where('project_is_active', 1)
            ->count();
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
            ->where('module_name', '!=', 'Default module')
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
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
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

    public function countProjectByReference($key, $idCustomer)
    {
        return DB::table('v_projet_cfps')
            ->select('idProjet')
            ->where('project_reference', 'like', "%$key%")
            ->where('project_is_trashed', 0)
            ->where('idCfp', $idCustomer)
            ->where('module_name', '!=', 'Default module')
            ->where('project_is_active', 1)
            ->count();
    }

    public function getProjectByReference($key, $idCustomer, $perPage, $page)
    {
        $query = DB::table('v_projet_cfps')
            ->select('idProjet', 'dateDebut', 'idEtp', 'dateFin', 'module_name', 'etp_name', 'ville', 'li_name', 'project_status', 'project_reference', 'project_description', 'project_type', 'paiement', DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'), 'module_image', 'etp_logo', 'etp_initial_name', 'idSalle', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'modalite', 'total_ht', 'total_ttc', 'idModule', 'project_inter_privacy', 'sub_name', 'idSubContractor', 'idCfp', 'cfp_name', 'headYear', 'headMonthDebut', 'headMonthFin', 'headDayDebut', 'headDayFin')
            ->where('project_reference', 'like', "%$key%")
            ->where('project_is_trashed', 0)
            ->where('idCfp', $idCustomer)
            ->where('module_name', '!=', 'Default module')
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
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
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
}
