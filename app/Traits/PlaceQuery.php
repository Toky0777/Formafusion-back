<?php

namespace App\Traits;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

trait PlaceQuery
{
    public function getPlace($key)
    {
        if (Customer::typeCustomer() == 1) {
            $places = DB::table('villes')
                ->join('salles', 'salles.idVille', 'villes.idVille')
                ->select('idSalle', 'salles.idCustomer', 'salle_name', 'salle_quartier', 'salle_rue', 'salle_code_postal', 'salle_image', 'ville')
                ->where('salles.idCustomer', Customer::idCustomer())
                ->where('salles.salle_name', '!=', 'null')
                ->where('salles.salle_name', '!=', 'In situ')
                ->where('salle_name', 'like', "%$key%")
                ->orderBy('salle_name', 'asc')
                ->get();
        } else {
            $places = DB::table('villes')
                ->join('salles', 'salles.idVille', 'villes.idVille')
                ->select('idSalle', 'salles.idCustomer', 'salle_name', 'salle_quartier', 'salle_rue', 'salle_code_postal', 'ville')
                ->where(function ($query) {
                    $query->where('salles.idCustomer', Customer::idCustomer())
                        ->where('salles.salle_name', '!=', 'null');
                })
                ->orderBy('salle_name', 'asc')
                ->get();
        }

        return $places;
    }

    public function getProjectByPlace($key)
    {
        $projects = DB::table('v_projet_cfps')
            ->select('idProjet', 'dateDebut', 'idEtp', 'dateFin', 'module_name', 'etp_name', 'ville', 'li_name', 'project_status', 'project_reference', 'project_description', 'project_type', 'paiement', DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'), 'module_image', 'etp_logo', 'etp_initial_name', 'idSalle', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'modalite', 'total_ht', 'total_ttc', 'idModule', 'project_inter_privacy', 'sub_name', 'idSubContractor', 'idCfp', 'cfp_name', 'headYear', 'headMonthDebut', 'headMonthFin', 'headDayDebut', 'headDayFin')
            ->where('li_name', 'like', "%$key%")
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
                'apprCount' => $this->getApprenantProject($project->idProjet, $project->idCfp_inter),
                'allApprenant' => $this->getAllApprenantProject($project->idProjet, $project->idCfp_inter),
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
}
