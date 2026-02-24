<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectDetailController extends Controller
{
    private function getPlaceAvailable($idProjet)
    {
        $place_validated = DB::table('inter_entreprises')->where('idProjet', $idProjet)->where('isActiveInter', 1)->sum('nbPlaceReserved');
        $place_project = DB::table('inters')->where('idProjet', $idProjet)->value('nbPlace');
        $place_available = $place_project - $place_validated;
        return $place_available;
    }

    private function getNbPlaceReserved($idProjet)
    {
        $place_reserved = DB::table('inter_entreprises')->where('idProjet', $idProjet)->where('isActiveInter', 1)->sum('nbPlaceReserved');
        return $place_reserved;
    }

  public function show($id)
{
    try {
        // Récupération du projet
        $projet = DB::table('v_projet_cfps')
            ->where('idProjet', $id)
            ->first();

        if (!$projet) {
            return response()->json([
                'success' => false,
                'message' => 'Projet non trouvé'
            ], 404);
        }

        // Récupération des sessions
        $seances = DB::table('v_seances')
            ->where('idProjet', $id)
            ->orderBy('dateSeance', 'asc')
            ->get([
                'idSeance',
                'dateSeance',
                'heureDebut',
                'heureFin',
                'idProjet',
                'idModule',
                DB::raw("TIME_FORMAT(SEC_TO_TIME(TIME_TO_SEC(intervalle_raw)), '%H:%i') AS intervalle_raw")
            ]);

        // Traitement des dates
        $datesSession = $seances->pluck('dateSeance');
        $deb = $datesSession->isNotEmpty() ? Carbon::parse($datesSession->first())->locale('fr')->translatedFormat('l j F Y') : null;
        $fin = $datesSession->isNotEmpty() ? Carbon::parse($datesSession->last())->locale('fr')->translatedFormat('l j F Y') : null;

        // Calcul de la durée totale
        $totalSession = DB::table('v_seances')
            ->where('idProjet', $id)
            ->selectRaw("IFNULL(TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(intervalle_raw))), '%H:%i'), '00:00') as sumHourSession")
            ->value('sumHourSession');

        // Données générales
        $generalData = DB::table('v_seances')
            ->where('idProjet', $id)
            ->groupBy('idProjet')
            ->selectRaw("COUNT(DISTINCT dateSeance) as countDate")
            ->first();

        // Modules
        $modules = DB::table('mdls')
            ->where('moduleName', '!=', 'Default module')
            ->where('idCustomer', Customer::idCustomer())
            ->orderBy('moduleName', 'asc')
            ->get(['idModule', 'moduleName AS module_name']);

        // Évaluations
        $evaluations = DB::table('v_evaluation_alls')
            ->where('idProjet', $id)
            ->groupBy('idProjet')
            ->selectRaw("COUNT(idEmploye) as countNotationProjet, IFNULL(AVG(generalApreciate), 0) as noteGeneral")
            ->first() ?? (object) ['countNotationProjet' => 0, 'noteGeneral' => 0];

        // Places
        $placeData = DB::table('inters')
            ->where('idProjet', $id)
            ->select(['nbPlace'])
            ->first();

        $nbPlace = $placeData->nbPlace ?? null;
        $place_available = $this->getPlaceAvailable($id) ?? null;
        $place_reserved = $this->getNbPlaceReserved($id) ?? null;

        // Apprenants
        $apprenantInter = DB::table('v_list_apprenant_inter_added')->where('idProjet', $id)->get();
        $idCfp_inter = $projet->idCfp_inter ?? null;
        $totalApprenants = $this->getApprenantProject($id, $idCfp_inter);
        $listeApprenants = $this->getApprListProjet($id);

        // Formateurs
        $formateurs = $this->getFormProject($id);

        // Documents associés (nouvelle méthode intégrée)
        $documents = $this->getDocumentDrawer($id);

        // Dossier
        $dossierInfo = $this->getNomDossier($id);
        $dossier = DB::table('dossiers AS d')
            ->join('projets AS p', 'd.idDossier', '=', 'p.idDossier')
            ->where('p.idProjet', $id)
            ->first(['nomDossier', 'd.idDossier']);

        // Autres données
        $villes = DB::table('villes')->get(['idVille', 'ville']);
        $paiements = DB::table('paiements')->get(['idPaiement', 'paiement']);
        $modalites = DB::table('modalites')->get(['idModalite', 'modalite']);

        $restaurations = DB::table('project_restaurations AS pr')
            ->join('restaurations AS rst', 'pr.idRestauration', '=', 'rst.idRestauration')
            ->where('idProjet', $id)
            ->get(['pr.idRestauration', 'rst.typeRestauration', 'pr.paidBy']);

        $imagesMomentums = DB::table('images')
            ->where('idProjet', $id)
            ->where('idTypeImage', 1)
            ->get(['nomImage', 'idImages']);

        $module_ressources = DB::table('module_ressources AS mr')
            ->join('mdls AS m', 'mr.idModule', '=', 'm.idModule')
            ->join('projets AS p', 'p.idModule', '=', 'm.idModule')
            ->where('p.idProjet', $id)
            ->get(['idModuleRessource', 'taille', 'module_ressource_name', 'file_path', 'module_ressource_extension', 'mr.idModule']);

        $idCfp = Customer::idCustomer();

        // Construction de la réponse
        $response = [
            'success' => true,
            'data' => [
                'projet' => $projet,
                'dates' => [
                    'debut' => $deb,
                    'fin' => $fin,
                    'total_jours' => $generalData->countDate ?? 0
                ],
                'duree' => [
                    'total_heures' => $totalSession
                ],
                'participants' => [
                    'places' => [
                        'total' => $nbPlace,
                        'disponibles' => $place_available,
                        'reservees' => $place_reserved
                    ],
                    'apprenants' => $apprenantInter,
                    'total_apprenants' => $totalApprenants,
                    'liste_complete_apprenants' => $listeApprenants,
                    'formateurs' => $formateurs
                ],
                'evaluation' => [
                    'nombre_participants' => $evaluations->countNotationProjet,
                    'note_moyenne' => round($evaluations->noteGeneral, 1)
                ],
                'contenu' => [
                    'modules' => $modules,
                    'ressources' => $module_ressources,
                    'seances' => $seances
                ],
                'logistique' => [
                    'villes' => $villes,
                    'restaurations' => $restaurations,
                    'images' => $imagesMomentums
                ],
                'administratif' => [
                    'dossier' => [
                        'id' => $dossier->idDossier ?? null,
                        'nom' => $dossier->nomDossier ?? null,
                        'info_complete' => $dossierInfo
                    ],
                    'paiements' => $paiements,
                    'modalites' => $modalites
                ],
                'documents' => $documents,
                'references' => [
                    'objectifs' => DB::table('objectif_modules')->select('idObjectif', 'objectif', 'idModule')->get(),
                    'materiels' => DB::table('prestation_modules')->select('idPrestation', 'prestation_name', 'idModule')->get(),
                    'prerequis' => DB::table('prerequis_modules')->select('idPrerequis', 'prerequis_name', 'idModule')->get()
                ]
            ],
            'metadata' => [
                'cfp_id' => $idCfp,
                'timestamp' => now()->toDateTimeString()
            ]
        ];

        return response()->json($response);

    } catch (\Exception $e) {
        \Log::error("Erreur API show projet: " . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Erreur serveur',
            'error' => $e->getMessage()
        ], 500);
    }
}
private function getDocumentDrawer($idProjet)
{
    $module = DB::table('projets')
        ->join('mdls', 'projets.idModule', 'mdls.idModule')
        ->select('mdls.*')
        ->where('idProjet', $idProjet)
        ->first();

    $idDossier = DB::table('projets')
        ->select('idDossier')
        ->where('idProjet', $idProjet)
        ->first();

    $documents = DB::table('v_document_dossier')
        ->where('idDossier', $idDossier->idDossier ?? null)
        ->orderBy('updated_at', 'desc')
        ->get();

    $endpoint = config('filesystems.disks.do.url_cdn_digital');
    $bucket = config('filesystems.disks.do.bucket');
    $digitalOcean = $endpoint . '/' . $bucket;

    return [
        'module' => $module,
        'documents' => $documents,
        'digitalOcean' => $digitalOcean
    ];
}


    public function getApprenantProject($idProjet, $idCfp_inter)
    {
        if ($idCfp_inter == null) {
            $apprs = DB::table('v_list_apprenants')
                ->where('idProjet', $idProjet)
                ->count();
        } else {
            $apprs_inter = DB::table('v_list_apprenant_inter_added')
                ->where('idProjet', $idProjet)
                ->count();

            $parts = $this->getParticulierProject($idProjet, $idCfp_inter);

            $apprs = $apprs_inter + $parts;
        }

        return $apprs;
    }

    private function getParticulierProject($idProjet, $idCfp_inter)
    {
        return DB::table('apprenant_particuliers')
            ->where('idProjet', $idProjet)
            ->where('idCfp_inter', $idCfp_inter)
            ->count();
    }

    public function getApprListProjet($idProjet)
    {
        $apprIntras = DB::table('v_list_apprenants')
            ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_photo', 'etp_name', 'emp_initial_name')
            ->where('idProjet', $idProjet)
            ->orderBy('emp_name', 'asc')
            ->get()
            ->toArray();

        $apprenantInters = DB::table('v_list_apprenant_inter_added')
            ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_photo', 'etp_name')
            ->where('idProjet', $idProjet)
            ->orderBy('emp_name', 'asc')
            ->get()
            ->toArray();

        $apprs = array_merge($apprIntras, $apprenantInters);

        return $apprs;
    }

    public function getFormProject($idProjet)
    {
        $forms = DB::table('v_formateur_cfps')
            ->select('idFormateur', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'initialNameForm AS form_initial_name')
            ->groupBy('idFormateur', 'name', 'firstName', 'photoForm', 'initialNameForm')
            ->where('idProjet', $idProjet)
            ->get();

        return $forms->toArray();
    }

    public function getNomDossier($idProjet)
    {
        $dossier = DB::table('dossiers')
            ->select('dossiers.idDossier', 'nomDossier')
            ->join('projets', 'dossiers.idDossier', 'projets.idDossier')
            ->where('idProjet', $idProjet)
            ->first();

        return $dossier;
    }

}