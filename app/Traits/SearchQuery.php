<?php

namespace App\Traits;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait SearchQuery
{
    use LearnerQuery;
    use CustomerQuery;
    use TrainerQuery;
    use Project;
    use RestaurationQuery;

    public function getProject($key)
    {
        if (Customer::typeCustomer() == 2) {
            $results = $this->getAllProjectEp($key);
        } elseif (Customer::typeCustomer() == 1) {
            $results = $this->getProjectCfp($key);
        }
        
        return $results;
    }

    public function eachProject($projects){
        $results = [];
        foreach ($projects as $project) {
            $results[] = [
                'project_reference' => $project->project_reference,
                'dossier' => $this->getNomDossier($project->idProjet),
                'nbDocument' => $this->getNombreDocument($project->idProjet),
                'seanceCount' => $this->getSessionProject($project->idProjet),
                'formateurs' => $this->getFormProject($project->idProjet),
                'apprCount' => $this->geLeanerByProject($project->idProjet, $project->idCfp_inter),
                'allApprenant' => $this->getAllLearnerByProject($project->idProjet, $project->idCfp_inter),
                'allCustomer' => $this->getCustomerByProject($project->idProjet),
                'allForms' => $this->getTrainerByProject($project->idProjet),
                'projectTotalPrice' => $this->getProjectTotalPrice($project->idProjet),
                'totalSessionHour' => $this->getSessionHour($project->idProjet),
                'general_note' => $this->getNote($project->idProjet),
                'idProjet' => $project->idProjet,
                'idCfp_inter' => $project->idCfp_inter,
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
                'module_name' => $project->module_name,
                'partCount' => $this->getParticulierProject($project->idProjet, $project->idCfp_inter),
                'etp_name' => $this->getEtpProjectInter($project->idProjet, $project->idCfp_inter),
                'idEtp' => $project->idEtp,
                'ville' => $project->ville,
                'li_name' => $project->li_name,
                'project_status' => $project->project_status,
                'project_type' => $project->project_type,
                'paiement' => $project->paiement,
                'modalite' => $project->modalite,
                'project_description' => $project->project_description,
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
                'restaurations' => $this->getRestaurationByProject($project->idProjet),
                'project_inter_privacy' => $project->typeCustomer == 1 ? $project->project_inter_privacy : null ,
                'checkEmg' => $this->checkEmg($project->idProjet),
                'checkEval' => $this->checkEval($project->idProjet),
                'avg_before' => $this->averageEvalApprenant($project->idProjet)->avg_avant,
                'avg_after' => $this->averageEvalApprenant($project->idProjet)->avg_apres,
                'apprs' => $this->getApprListByProjet($project->idProjet),
                // 'idSubContractor' => $project->idSubContractor,
                'idCfp' => $project->idCfp,
                'cfp_name' => $project->cfp_name ?? $this->getLogoCfp($project->idCfp)->customerName,
                'idUser' => Customer::idCustomer(),
                'typeCustomer' => $project->typeCustomer,
                'logo_cfp' => $project->typeCustomer == 2 ? $this->getLogoCfp($project->idCfp)->logo : null
            ];
        }

        return $results;
    }

    public function getLogoCfp($idCfp){
        $logoCfp  = DB::table('customers')
                    ->select('logo', 'customerName')
                    ->where('idCustomer', $idCfp)
                    ->first();
                    
        return $logoCfp;
    }

    public function getProjectCfp($key){
        $projects = DB::table('v_projet_cfps')
                ->select('idProjet',DB::raw('1 as `typeCustomer`'), 'dateDebut', 'idEtp', 'dateFin', 'module_name', 'li_name', 'etp_name', 'ville', 'project_status', 'project_reference', 'project_description', 'project_type', 'paiement', DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'), 'module_image', 'etp_logo', 'etp_initial_name', 'idSalle', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'modalite', 'total_ht', 'total_ttc', 'idModule', 'project_inter_privacy', 'sub_name', 'idSubContractor', 'idCfp', 'cfp_name', 'headYear', 'headMonthDebut', 'headMonthFin', 'headDayDebut', 'headDayFin')
                ->where(function ($query) {
                    $query->where(function ($query) {
                        $query->where('idCfp', Customer::idCustomer())
                            ->orWhere('idCfp_inter', Customer::idCustomer())
                            ->orWhere('idSubContractor', Customer::idCustomer());
                    });
                })
                ->where('module_name', 'like', "%$key%")
                ->where('project_is_trashed', 0)
                ->where('project_is_active', 1)
                ->orderBy('dateDebut', 'desc')
                ->get();

        return $this->eachProject($projects);
    }
    
    public function getAllProjectEp($key)
    {
        $projects = DB::table('v_union_projets')
            ->select('idProjet', DB::raw('2 as `typeCustomer`'), 'dateDebut', 'idEtp', 'dateFin', 'module_name', 'salle_name as li_name', 'etp_name', 'ville', 'project_status', 'project_reference', 'project_description', 'project_type', 'paiement', DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'), 'module_image', 'etp_logo', 'etp_initial_name', 'idVille as idSalle', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'modalite', 'total_ht', 'total_ttc', 'idModule',DB::raw('CASE WHEN idCfp_inter IS NOT NULL THEN idCfp_inter ELSE idCfp_intra END as idCfp'), 'headYear', 'headMonthDebut', 'headMonthFin', 'headDayDebut', 'headDayFin')
            ->where(function ($query) {
                $query->where('idEtp', Customer::idCustomer())
                    ->orWhere('idEtp_inter', Customer::idCustomer());
            })
            ->where(function ($query) {
                $query->where('project_type', 'Interne')
                    ->orWhere(function ($query) {
                        $query->whereIn('project_type', ['Intra', 'Inter'])
                            ->whereIn('project_status', ['Terminé', 'Planifié', 'En cours']);
                    });
            })
            ->where('module_name', 'like', "%$key%")
            ->where('project_is_trashed', 0)
            ->where('idModule', '!=', 1)
            ->groupBy('idProjet')
            ->get();

        return $this->eachProject($projects);
    }

    public function getProjectEtpGrouped($etp_grouped_with_etp_parent)
    {
        $get_projects = DB::table('v_union_projets')
            ->select('idProjet', 'dateDebut', 'dateFin', 'module_name', 'total_ht', 'project_type', 'module_name')
            ->where(function ($query) use ($etp_grouped_with_etp_parent) {
                $query->whereIn('idEtp', $etp_grouped_with_etp_parent)
                    ->orWhereIn('idEtp_inter', $etp_grouped_with_etp_parent);
            })
            ->whereIn('project_status', ['Terminé', 'Planifié', 'En cours'])
            ->whereIn('project_type', ['Intra', 'Inter'])
            ->where('project_is_trashed', 0)
            ->where('idModule', '!=', 1)
            ->orderBy('dateDebut', 'desc')
            ->get();
        $projects = [];
        foreach ($get_projects as $finished) {
            $projects[] = [
                'module_name' => $finished->module_name,
                'date_debut' => $finished->dateDebut,
                'date_fin' => $finished->dateFin,
                'total_ht' => $this->formatPrice($finished->total_ht),
                'average' => $this->getEval($finished->idProjet),
                'project_type' => $finished->project_type
            ];
        }

        return $projects;
    }

    public function getProjectEtpSingle()
    {
        $get_projects = DB::table('v_union_projets')
            ->select('idProjet', 'dateDebut', 'dateFin', 'module_name', 'total_ht', 'project_type', 'module_name')
            ->where(function ($query) {
                $query->where('idEtp', Customer::idCustomer())
                    ->orWhere('idEtp_inter', Customer::idCustomer());
            })
            ->where(function ($query) {
                $query->where('project_type', 'Interne')
                    ->orWhere(function ($query) {
                        $query->whereIn('project_type', ['Intra', 'Inter'])
                            ->whereIn('project_status', ['Terminé', 'Planifié', 'En cours']);
                    });
            })
            ->where('project_is_trashed', 0)
            ->where('idModule', '!=', 1)
            ->orderBy('dateDebut', 'desc')
            ->get();

        $projects = [];
        foreach ($get_projects as $finished) {
            $projects[] = [
                'module_name' => $finished->module_name,
                'date_debut' => $finished->dateDebut,
                'date_fin' => $finished->dateFin,
                'total_ht' => $this->formatPrice($finished->total_ht),
                'average' => $this->getEval($finished->idProjet),
                'project_type' => $finished->project_type
            ];
        }

        return $projects;
    }

    

    public function getEntreprise($key)
    {
        $entreprises = DB::table('v_collaboration_cfp_etps')
            ->select('etp_initial_name', 'etp_name', 'etp_logo', 'etp_description', 'etp_phone', 'etp_addr_lot', 'etp_site_web', 'etp_email', 'idEtp', 'idCfp', 'activiteCfp', 'activiteEtp', 'dateInvitation', 'etp_referent_name', 'etp_referent_firstname', 'etp_referent_fonction', 'etp_referent_phone')
            ->where('idCfp', Customer::idCustomer())
            ->where('etp_name', 'like', "%$key%")
            ->orderBy('etp_name', 'ASC')
            ->get();
        return $entreprises;
    }

    public function getCfp($key){
        $cfps = DB::table('v_collaboration_etp_cfps')
            ->select('etp_initial_name', 'etp_name', 'etp_logo', 'etp_ville', 'etp_phone', 'etp_addr_lot', 'etp_site_web', 'etp_email', 'idEtp', 'idCfp', 'etp_referent_name', 'etp_referent_firstname')
            ->where('idEtp', Customer::idCustomer())
            ->where('etp_name', 'like', "%$key%")
            ->orderBy('etp_name', 'ASC')
            ->get();

        return $cfps;
    }

    public function getProjectByReference($key)
    {
        $projects = DB::table('v_projet_cfps')
            ->select('idProjet', 'dateDebut', 'idEtp', 'dateFin', 'module_name', 'etp_name', 'ville', 'li_name', 'project_status', 'project_reference', 'project_description', 'project_type', 'paiement', DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'), 'module_image', 'etp_logo', 'etp_initial_name', 'idSalle', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'modalite', 'total_ht', 'total_ttc', 'idModule', 'project_inter_privacy', 'sub_name', 'idSubContractor', 'idCfp', 'cfp_name', 'headYear', 'headMonthDebut', 'headMonthFin', 'headDayDebut', 'headDayFin')
            ->where('project_reference', 'like', "%$key%")
            ->where('project_is_trashed', 0)
            ->where('project_is_active', 1)
            ->get();

        $results = [];
        foreach ($projects as $project) {
            $results[] = [
                'project_reference' => $project->project_reference,
                'dossier' => $this->getNomDossier($project->idProjet),
                'nbDocument' => $this->getNombreDocument($project->idProjet),
                'seanceCount' => $this->getSessionProject($project->idProjet),
                'formateurs' => $this->getFormProject($project->idProjet),
                'apprCount' => $this->geLeanerByProject($project->idProjet, $project->idCfp_inter),
                'allApprenant' => $this->getAllLearnerByProject($project->idProjet, $project->idCfp_inter),
                'allCustomer' => $this->getCustomer($project->idProjet),
                'allForms' => $this->getLearnerProject($project->idProjet),
                'projectTotalPrice' => $this->getProjectTotalPrice($project->idProjet),
                'totalSessionHour' => $this->getSessionHour($project->idProjet),
                'general_note' => $this->getNote($project->idProjet),
                'idProjet' => $project->idProjet,
                'idCfp_inter' => $project->idCfp_inter,
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
                'module_name' => $project->module_name,
                'partCount' => $this->getParticulierProject($project->idProjet, $project->idCfp_inter),
                'etp_name' => $this->getEtpProjectInter($project->idProjet, $project->idCfp_inter),
                'idEtp' => $project->idEtp,
                'ville' => $project->ville,
                'li_name' => $project->li_name,
                'project_status' => $project->project_status,
                'project_type' => $project->project_type,
                'paiement' => $project->paiement,
                'modalite' => $project->modalite,
                'project_description' => $project->project_description,
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
                'idUser' => Customer::idCustomer()
            ];
        }

        return $results;
    }

    public function getProjectByCity($key)
    {
        $projects = DB::table('v_projet_cfps')
            ->select('idProjet', 'dateDebut', 'idEtp', 'dateFin', 'module_name', 'etp_name', 'ville', 'li_name', 'project_status', 'project_reference', 'project_description', 'project_type', 'paiement', DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'), 'module_image', 'etp_logo', 'etp_initial_name', 'idSalle', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'modalite', 'total_ht', 'total_ttc', 'idModule', 'project_inter_privacy', 'sub_name', 'idSubContractor', 'idCfp', 'cfp_name', 'headYear', 'headMonthDebut', 'headMonthFin', 'headDayDebut', 'headDayFin')
            ->where('ville', 'like', "%$key%")
            ->where('project_is_trashed', 0)
            ->where('project_is_active', 1)
            ->get();

        $results = [];
        foreach ($projects as $project) {
            $results[] = [
                'project_reference' => $project->project_reference,
                'dossier' => $this->getNomDossier($project->idProjet),
                'nbDocument' => $this->getNombreDocument($project->idProjet),
                'seanceCount' => $this->getSessionProject($project->idProjet),
                'formateurs' => $this->getFormProject($project->idProjet),
                'apprCount' => $this->geLeanerByProject($project->idProjet, $project->idCfp_inter),
                'allApprenant' => $this->getAllLearnerByProject($project->idProjet, $project->idCfp_inter),
                'allCustomer' => $this->getCustomer($project->idProjet),
                'allForms' => $this->getLearnerProject($project->idProjet),
                'projectTotalPrice' => $this->getProjectTotalPrice($project->idProjet),
                'totalSessionHour' => $this->getSessionHour($project->idProjet),
                'general_note' => $this->getNote($project->idProjet),
                'idProjet' => $project->idProjet,
                'idCfp_inter' => $project->idCfp_inter,
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
                'module_name' => $project->module_name,
                'partCount' => $this->getParticulierProject($project->idProjet, $project->idCfp_inter),
                'etp_name' => $this->getEtpProjectInter($project->idProjet, $project->idCfp_inter),
                'idEtp' => $project->idEtp,
                'ville' => $project->ville,
                'li_name' => $project->li_name,
                'project_status' => $project->project_status,
                'project_type' => $project->project_type,
                'paiement' => $project->paiement,
                'modalite' => $project->modalite,
                'project_description' => $project->project_description,
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
                'idUser' => Customer::idCustomer()
            ];
        }

        return $results;
    }

    public function getProjectByNeighborhood($key)
    {
        $projects = DB::table('v_projet_cfps')
            ->select('idProjet', 'dateDebut', 'idEtp', 'dateFin', 'module_name', 'etp_name', 'ville', 'li_name', 'project_status', 'project_reference', 'project_description', 'project_type', 'paiement', DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'), 'module_image', 'etp_logo', 'etp_initial_name', 'idSalle', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'modalite', 'total_ht', 'total_ttc', 'idModule', 'project_inter_privacy', 'sub_name', 'idSubContractor', 'idCfp', 'cfp_name', 'headYear', 'headMonthDebut', 'headMonthFin', 'headDayDebut', 'headDayFin')
            ->where('salle_quartier', 'like', "%$key%")
            ->where('project_is_trashed', 0)
            ->where('project_is_active', 1)
            ->get();

        $results = [];
        foreach ($projects as $project) {
            $results[] = [
                'project_reference' => $project->project_reference,
                'dossier' => $this->getNomDossier($project->idProjet),
                'nbDocument' => $this->getNombreDocument($project->idProjet),
                'seanceCount' => $this->getSessionProject($project->idProjet),
                'formateurs' => $this->getFormProject($project->idProjet),
                'apprCount' => $this->geLeanerByProject($project->idProjet, $project->idCfp_inter),
                'allApprenant' => $this->getAllLearnerByProject($project->idProjet, $project->idCfp_inter),
                'allCustomer' => $this->getCustomer($project->idProjet),
                'allForms' => $this->getLearnerProject($project->idProjet),
                'projectTotalPrice' => $this->getProjectTotalPrice($project->idProjet),
                'totalSessionHour' => $this->getSessionHour($project->idProjet),
                'general_note' => $this->getNote($project->idProjet),
                'idProjet' => $project->idProjet,
                'idCfp_inter' => $project->idCfp_inter,
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
                'module_name' => $project->module_name,
                'partCount' => $this->getParticulierProject($project->idProjet, $project->idCfp_inter),
                'etp_name' => $this->getEtpProjectInter($project->idProjet, $project->idCfp_inter),
                'idEtp' => $project->idEtp,
                'ville' => $project->ville,
                'li_name' => $project->li_name,
                'project_status' => $project->project_status,
                'project_type' => $project->project_type,
                'paiement' => $project->paiement,
                'modalite' => $project->modalite,
                'project_description' => $project->project_description,
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
                'idUser' => Customer::idCustomer()
            ];
        }

        return $results;
    }

    public function getKeySuggestion($key){
        $route = route("searchGenerality") . '?key=';

        $endpoint = config('filesystems.disks.do.url_cdn_digital');
        $bucket = config('filesystems.disks.do.bucket');
        $digitalOcean = $endpoint . '/' . $bucket;

        $result = [];

        $customers = DB::table('customers as C')
            ->select('C.customerName', 'C.logo', 'V.ville_name')
            ->join('cfp_etps as E', 'C.idCustomer', '=', 'E.idEtp')
            ->join('ville_codeds as V', 'V.id', 'C.idVilleCoded')
            ->where('E.idCfp', Customer::idCustomer())
            ->where('C.customerName', 'like', "%$key%")
            ->get();

        $resultCustomers = [];
        foreach ($customers as $customer) {
            $routeCustomer = $route . urlencode($customer->customerName);
            $image = $digitalOcean . '/img/entreprises/' . $customer->logo;
            $showImage = isset($customer->logo) ? '<img src="' . $image . '" width="32" height="32"/>' : '<img src="http://127.0.0.1:8000/img/logo/Logo_mark.svg" class="grayscale h-12 opacity-50" width="32" height="32">';
            $resultCustomers[] = [
                'value' => $customer->customerName,
                'label' => '<a href="' . $routeCustomer . '" class="flex space-x-2">
                            ' . $showImage . '
                            <div class="flex flex-col">
                                <span>' . $customer->customerName . '</span>
                                <span class="text-gray-500">Entreprise à ' . $customer->ville_name . '</span>
                            </div>
                            </a>'
            ];
        }

        $result = array_merge($result, $resultCustomers);
        if (count($result) >= 10) {
            return array_slice($result, 0, 10);
        }

        $folders = DB::table('dossiers')
            ->select('nomDossier', 'idDossier')
            ->where('idCfp', Customer::idCustomer())
            ->where('nomDossier', 'like', "%$key%")
            ->get();

        $resultFolders = [];

        foreach ($folders as $folder) {
            $resultFolders[] = [
                'value' => $folder->nomDossier,
                'label' => '<a href="' . $route . ' ' . $folder->nomDossier . '" class="flex items-center space-x-2">
                            <i class="fa-solid fa-folder fa-2x text-yellow-500"></i>
                            <div class="flex flex-col">
                                <span>' . $folder->nomDossier . '</span>
                                <span>' . $this->getNumberDocumentByFolder($folder->idDossier) . ' document(s), ' . $this->getTotalProjectByFolder($folder->idDossier) . ' projet(s) </span>
                            </div>
                            </a>'
            ];
        }

        $result = array_merge($result, $resultFolders);
        if (count($result) >= 10) {
            return array_slice($result, 0, 10);
        }

        $modules = DB::table('mdls as M')
            ->join('module_levels as ML', 'ML.idLevel', 'M.idLevel')
            ->select('M.moduleName', 'M.module_image', 'ML.module_level_name')
            ->where('M.idCustomer', Customer::idCustomer())
            ->whereNot('M.moduleName', 'Default module')
            ->where('M.moduleName', 'like', "%$key%")
            ->get();

        $resultModules = [];

        foreach ($modules as $module) {
            $image = $digitalOcean . '/img/modules/' . $module->module_image;
            $showImage = isset($module->module_image) ? '<img src="' . $image . '" width="32" height="32"/>' : '<img src="http://127.0.0.1:8000/img/logo/Logo_mark.svg" class="grayscale h-12 opacity-50" width="50" height="50">';
            $resultModules[] = [
                'value' => $module->moduleName,
                'label' => '<a href="' . $route . ' ' . $module->moduleName . '" class="flex space-x-2">
                            ' . $showImage . '
                            <div class="flex flex-col">
                                <span>' . $module->moduleName . '</span>
                                <span class="text-gray-500">Niveau: ' . $module->module_level_name . '</span>
                            </div>
                            </a>'
            ];
        }

        $result = array_merge($result, $resultModules);
        if (count($result) >= 10) {
            return array_slice($result, 0, 10);
        }

        $learners = DB::table('v_apprenant_etp_alls')
            ->select('emp_name', 'emp_firstname', 'emp_photo', 'etp_name')
            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->orWhere('id_cfp_appr', Customer::idCustomer());
            })
            ->whereNotNull('emp_name')
            ->where(function ($query) use ($key) {
                $query->where('emp_name', 'like', "%$key%")
                    ->orWhere('emp_firstname', 'like', "%$key%");
            })
            ->orderBy('emp_name')
            ->distinct()
            ->get();

        $resultLearners = [];

        foreach ($learners as $learner) {
            $fullName = $learner->emp_name . ' ' . $learner->emp_firstname ?? '';
            $image = $digitalOcean . '/img/employes/' . $learner->emp_photo;
            $showImage = isset($learner->emp_photo) ? '<img src="' . $image . '" width="32" height="32"/>' : '<i class="text-4xl text-gray-600 fa-solid fa-user mr-2" width="32" height="32"></i>';
            $resultLearners[] = [
                'value' => $fullName,
                'label' => '<a href="' . $route . ' ' . $fullName . '" class="flex items-center space-x-2">
                            ' . $showImage . '
                            <div class="flex flex-col">
                                <span>' . $fullName . '</span>
                                <span class="text-gray-500">Employé de ' . $learner->etp_name . '</span>
                            </div>
                            </a>'
            ];
        }

        $result = array_merge($result, $resultLearners);
        if (count($result) >= 10) {
            return array_slice($result, 0, 10);
        }

        $referents = DB::table('v_employe_alls')
            ->select('name', 'firstName', 'photo', 'customerName')
            ->where('idCustomer', Customer::idCustomer())
            ->where('role_id', 8)
            ->whereNotNull('name')
            ->where(function ($query) use ($key) {
                $query->where('name', 'like', "%$key%")
                    ->orWhere('firstName', 'like', "%$key%");
            })
            ->get();

        $resultReferents = [];

        foreach ($referents as $referent) {
            $fullName = $referent->name . ' ' . $referent->firstName ?? '';
            $image = $digitalOcean . '/img/referents/' . $referent->photo;
            $showImage = isset($referent->photo) ? '<img src="' . $image . '" width="50" height="50"/>' : '<i class="text-4xl text-gray-400 fa-solid fa-user-tie" width="50" height="50"></i>';
            $resultReferents[] = [
                'value' => $fullName,
                'label' => '<a href="' . $route . ' ' . $fullName . '" class="flex items-center space-x-2">
                                ' . $showImage . '
                                <div class="flex flex-col">
                                    <span>' . $fullName . '</span>
                                    <span class="text-gray-500">Votre référent</span>
                                </div>
                            </a>'
            ];
        }

        $result = array_merge($result, $resultReferents);
        if (count($result) >= 10) {
            return array_slice($result, 0, 10);
        }

        $allEtps = DB::table('v_collaboration_cfp_etps')
            ->where('idCfp', Customer::idCustomer())
            ->orderBy('etp_name', 'ASC')
            ->pluck('idEtp');

        $referentCustomers = DB::table('employes as E')
            ->select('U.name', 'U.firstName', 'U.photo', 'C.customerName')
            ->join('users as U', 'U.id', 'E.idEmploye')
            ->join('role_users as RU', 'RU.user_id', 'U.id')
            ->join('customers as C', 'C.idCustomer', 'E.idCustomer')
            ->whereIn('E.idCustomer', $allEtps)
            ->whereIn('RU.role_id', [6, 9])
            ->whereNotNull('U.name')
            ->where(function ($query) use ($key) {
                $query->where('U.name', 'like', "%$key%")
                    ->orWhere('U.firstName', 'like', "%$key%");
            })
            ->get();

        $resultReferentCustomers = [];

        foreach ($referentCustomers as $referentCustomer) {
            $fullName = $referentCustomer->name . ' ' . $referentCustomer->firstName ?? '';
            $image = $digitalOcean . '/img/referents/' . $referentCustomer->photo;
            $showImage = isset($referentCustomer->photo) ? '<img src="' . $image . '" width="32" height="32"/>' : '<i class="text-4xl text-gray-400 fa-solid fa-user-tie" width="32" height="32"></i>';
            $resultReferentCustomers[] = [
                'value' => $fullName,
                'label' => '<a href="' . $route . ' ' . $fullName . '" class="flex items-center space-x-2">
                            ' . $showImage . '
                            <div class="flex flex-col">
                                <span>' . $fullName . '</span>
                                <span class="text-gray-500">Référent de ' . $referentCustomer->customerName . '</span>
                            </div>
                            </a>'
            ];
        }

        $result = array_merge($result, $resultReferentCustomers);
        if (count($result) >= 10) {
            return array_slice($result, 0, 10);
        }

        $projectReferences = DB::table('v_projet_cfps')
            ->select('project_reference', 'module_image', 'module_name', 'dateDebut', 'dateFin')
            ->where('project_is_trashed', 0)
            ->where('project_is_active', 1)
            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer());
            })
            ->where('project_reference', 'like', "%$key%")
            ->get();

        $resultProjectReferences = [];

        foreach ($projectReferences as $project) {
            $image = $digitalOcean . '/img/modules/' . $project->module_image;
            $showImage = isset($project->module_image) ? '<img src="' . $image . '" width="32" height="32"/>' : '<img src="http://127.0.0.1:8000/img/logo/Logo_mark.svg" class="grayscale h-12 opacity-50" width="50" height="50">';
            $resultProjectReferences[] = [
                'value' => $project->project_reference,
                'label' => '<a href="' . $route . ' ' . $project->project_reference . '" class="flex items-center space-x-2">
                            ' . $showImage . '
                            <div class="flex flex-col">
                                <span>' . $project->project_reference . '</span>
                                <span>Référence du projet le ' . $project->dateDebut . ' - ' . $project->dateFin . '</>
                            </div>
                            </a>'
            ];
        }

        $result = array_merge($result, $resultProjectReferences);
        if (count($result) >= 10) {
            return array_slice($result, 0, 10);
        }

        $trainers = DB::table('cfp_formateurs as CF')
            ->select('U.name', 'U.firstName', 'U.photo')
            ->join('users as U', 'CF.idFormateur', 'U.id')
            ->where('CF.idCfp', '=', Customer::idCustomer())
            ->where(function ($query) use ($key) {
                $query->where('U.name', 'like', "%$key%")
                    ->orWhere('U.firstName', 'like', "%$key%");
            })
            ->get();

        $resultTrainers = [];

        foreach ($trainers as $trainer) {
            $fullName = $trainer->name . ' ' . $trainer->firstName ?? '';
            $image = $digitalOcean . '/img/formateurs/' . $trainer->photo;
            $showImage = isset($trainer->photo) ? '<img src="' . $image . '" width="32" height="32"/>' : '<i class="text-4xl text-slate-600 fa-solid fa-user-graduate"></i>';
            $resultTrainers[] = [
                'value' => $fullName,
                'label' => '<a href="' . $route . ' ' . $fullName . '" class="flex space-x-2 items-center">
                            ' . $showImage . '
                            <div class="flex flex-col">
                                <span>' . $fullName . '</span>
                                <span>Votre formateur</span>
                            </div>
                            </a>'
            ];
        }
        $result = array_merge($result, $resultTrainers);

        $cities = DB::table('v_liste_lieux')
            ->select('ville_name_coded')
            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->orWhere('idCustomer', Customer::idCustomer());
            })
            ->where('ville_name_coded', 'like', "%$key%")
            ->distinct()
            ->get();

        $resultCities = [];

        foreach ($cities as $city) {
            $resultCities[] = [
                'value' => $city->ville_name_coded,
                'label' => '<a href="' . $route . ' ' . $city->ville_name_coded . '" class="flex items-center space-x-2">
                            <i class="fa-solid fa-city fa-2x mr-1"></i>
                            <div class="flex flex-col">
                                <span>' . $city->ville_name_coded . '</span>
                                <span>Votre projet dans cette ville</span>
                            </div>
                            </a>'
            ];
        }

        $result = array_merge($result, $resultCities);

        $places = DB::table('v_liste_lieux')
            ->select('li_name')
            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->orWhere('idCustomer', Customer::idCustomer());
            })
            ->where('li_name', 'like', "%$key%")
            ->distinct()
            ->get();

        $resultPlaces = [];

        foreach ($places as $place) {
            $resultPlaces[] = [
                'value' => $place->li_name,
                'label' => '<a href="' . $route . ' ' . $place->li_name . '" class="flex items-center space-x-2">
                            <i class="fa-solid fa-map-location-dot fa-2x mr-2"></i>
                            <div class="flex flex-col">
                                <span>' . $place->li_name . '</span>
                                <span>Votre projet dans ce lieu</span>
                            </div>
                            </a>'
            ];
        }

        $result = array_merge($result, $resultPlaces);

        $neighborhoods = DB::table('v_liste_lieux as V')
            ->join('lieux as L', 'V.idLieu', 'L.idLieu')
            ->select('L.li_quartier')
            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->orWhere('idCustomer', Customer::idCustomer());
            })
            ->where('L.li_quartier', 'like', "%$key%")
            ->distinct()
            ->get();

        $resultNeighborhoods = [];

        foreach ($neighborhoods as $neighborhood) {
            $resultNeighborhoods[] = [
                'value' => $neighborhood->li_quartier,
                'label' => '<a href="' . $route . ' ' . $neighborhood->li_quartier . '" class="flex items-center space-x-2">
                            <i class="fa-solid fa-map-location-dot fa-2x mr-2"></i>
                            <div class="flex flex-col">
                                <span>' . $neighborhood->li_quartier . '</span>
                                <span>Votre projet dans ce quartier</span>
                            </div>
                            </a>'
            ];
        }

        return array_merge($result, $resultNeighborhoods);

    }

    public function projectEtp($idEtp){
        $get_projects = DB::table('v_projet_cfps')
            ->select('idProjet', 'dateDebut', 'idEtp', 'dateFin', 'module_name', 'etp_name', 'ville', 'li_name', 'project_status', 'project_reference', 'project_description', 'project_type', 'paiement', DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'), 'module_image', 'etp_logo', 'etp_initial_name', 'idSalle', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'modalite', 'total_ht', 'total_ttc', 'idModule', 'project_inter_privacy', 'sub_name', 'idSubContractor', 'idCfp', 'cfp_name', 'headYear', 'headMonthDebut', 'headMonthFin', 'headDayDebut', 'headDayFin')
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                });
            })
            ->where('idEtp', $idEtp)
            ->where('project_is_trashed', 0)
            ->where('project_is_active', 1)
            ->get();

        $projects = [];
        foreach ($get_projects as $project) {
            $projects[] = [
                'project_reference' => $project->project_reference,
                'dossier' => $this->getNomDossier($project->idProjet),
                'nbDocument' => $this->getNombreDocument($project->idProjet),
                'seanceCount' => $this->getSessionProject($project->idProjet),
                'formateurs' => $this->getFormProject($project->idProjet),
                'apprCount' => $this->geLeanerByProject($project->idProjet, $project->idCfp_inter),
                'allApprenant' => $this->getAllLearnerByProject($project->idProjet, $project->idCfp_inter),
                'allCustomer' => $this->getCustomer($project->idProjet),
                'allForms' => $this->getLearnerProject($project->idProjet),
                'projectTotalPrice' => $this->getProjectTotalPrice($project->idProjet),
                'totalSessionHour' => $this->getSessionHour($project->idProjet),
                'general_note' => $this->getNote($project->idProjet),
                'idProjet' => $project->idProjet,
                'idCfp_inter' => $project->idCfp_inter,
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
                'module_name' => $project->module_name,
                'partCount' => $this->getParticulierProject($project->idProjet, $project->idCfp_inter),
                'etp_name' => $this->getEtpProjectInter($project->idProjet, $project->idCfp_inter),
                'idEtp' => $project->idEtp,
                'ville' => $project->ville,
                'li_name' => $project->li_name,
                'project_status' => $project->project_status,
                'project_type' => $project->project_type,
                'paiement' => $project->paiement,
                'modalite' => $project->modalite,
                'project_description' => $project->project_description,
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
                'idUser' => Customer::idCustomer()
            ];
        }

        return $projects;
    }

    public function getProjectWithEtp($key)
    {
        $get_projects = DB::table('v_projet_cfps')
            ->select('idEtp', 'etp_name', DB::raw('COUNT(idProjet) as count_project'))
            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer())
                    ->orWhere('idSubContractor', Customer::idCustomer());
            })
            ->where('project_is_trashed', 0)
            ->whereIn('project_status', ['Planifié', 'En cours', 'Terminé', 'Cloturé', 'Annulé'])
            ->where('project_is_trashed', 0)
            ->where('project_is_active', 1)
            ->where('etp_name', 'like', "%$key%")
            ->groupBy('idEtp')
            ->get();

        $projects = [];

        foreach ($get_projects as $project) {
            $projects[] = [
                'type' => 'Projet avec ' . $project->etp_name,
                'count' => $project->count_project,
                'route' => route('getProjectEtpWithCfp', ['id' => $project->idEtp, 'key' => $key])
            ];
        }

        return $projects;
    }
}