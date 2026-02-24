<?php

namespace App\Http\Controllers;

use App\Mail\AttestationMail;
use App\Mail\CoursMail;
use App\Mail\RapportMail;
use App\Models\Customer;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PedaController extends Controller
{
    public function index()
    {
        $allProjects = $this->getProjectList(['En cours', 'En préparation', 'Terminé']);
        return view('CFP.pedagogies.index', compact(['allProjects']));
    }

    public function getProjectList(array $statuses)
    {
        $projects = DB::table('v_projet_cfps')
            ->select(
                'idProjet',
                'dateDebut',
                'idEtp',
                'module_name',
                'etp_name',
                'etp_logo',
                'project_status',
                'project_type',
                DB::raw('DATE_FORMAT(dateDebut, "%M %Y") AS headDate'),
                'idModule'
            )
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                });
            })
            ->whereIn('project_status', $statuses)
            ->where('module_name', '!=', 'Default module')
            ->where('project_is_trashed', 0)
            ->groupBy('idProjet')
            ->orderBy('dateDebut', 'desc')
            ->get();

        $projetsGroupes = [];

        foreach ($projects as $project) {
            [$coursDejaEnvoye, $rapportDejaEnvoye, $attestationDejaEnvoye] = $this->suiviEnvois($project->idProjet);

            $projetsGroupes[$project->headDate][] = [
                'idProjet' => $project->idProjet,
                'dateDebut' => $project->dateDebut,
                'idEtp' => $project->idEtp,
                'module_name' => $project->module_name,
                'etp_name' => $project->etp_name,
                'etp_logo' => $project->etp_logo,
                'project_status' => $project->project_status,
                'project_type' => $project->project_type,
                'idModule' => $project->idModule,
                'isPaid' => $this->projectIsPaid($project->idProjet),
                'nbAppr' => $this->getNombreApprenant($project->idProjet),
                'evalChaud' => $this->checkEvalChaud($project->idProjet),
                'presence' => $this->checkEmg($project->idProjet),
                'formateur' => $this->getFormProject($project->idProjet),
                'cours' => $this->getCours($project->idProjet),
                'rapport' => $this->getRapport($project->idProjet),
                'coursDejaEnvoye' => $coursDejaEnvoye,
                'rapportDejaEnvoye' => $rapportDejaEnvoye,
                'attestation' => $this->checkAttestation($project->idProjet),
                'attestationDejaEnvoye' => $attestationDejaEnvoye,
            ];
        }

        return $projetsGroupes;
    }


    function getNombreApprenant($idProjet)
    {
        return DB::table('detail_apprenants')
            ->where('idProjet', $idProjet)
            ->count();
    }

    private function getApprenant($idProjet)
    {
        $apprs = DB::table('v_list_apprenants')
            ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_email')
            ->where('idProjet', $idProjet)
            ->orderBy('emp_name', 'asc')
            ->get();
    }
    private function projectIsPaid($id)
    {
        $isPaid = DB::table('invoice_details as ID')
            ->select('IS.invoice_status_name')
            ->join('invoices as I', 'I.idInvoice', '=', 'ID.idInvoice')
            ->join('invoice_status as IS', 'IS.idInvoiceStatus', '=', 'I.invoice_status')
            ->where('ID.idProjet', $id)
            ->whereNotExists(function ($query) {
                $query->select('IL.id')
                    ->from('invoice_deleted as IL')
                    ->whereRaw('IL.idInvoice = ID.idInvoice');
            })
            ->first();

        return $isPaid->invoice_status_name ?? null;
    }

    public function checkEvalChaud($idProjet): bool
    {
        $query = DB::table('eval_chauds')->where('idProjet', $idProjet)->exists();

        return $query;
    }

    public function checkEmg($idProjet): bool
    {
        $query = DB::table('emargements')->where('idProjet', $idProjet)->exists();

        return $query;
    }

    public function getFormProject($idProjet)
    {
        $forms = DB::table('v_formateur_cfps')
            ->select('idFormateur', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'initialNameForm AS form_initial_name')
            ->groupBy('idFormateur', 'name', 'firstName', 'photoForm', 'initialNameForm')
            ->where('idProjet', $idProjet)->get();

        return $forms->toArray();
    }

    public function getCours($idProjet)
    {
        $nbCours = DB::table('module_ressources as mr')
            ->join('mdls as m', 'mr.idModule', 'm.idModule')
            ->join('projets as p', 'p.idModule', 'm.idModule')
            ->where('p.idProjet', $idProjet)
            ->exists();

        return $nbCours;
    }

    public function getRapport($idProjet)
    {
        $nbRapport = DB::table('documents as d')
            ->join('type_documents as tp', 'tp.idTypeDocument', '=', 'd.idTypeDocument')
            ->join('dossiers as ds', 'ds.idDossier', '=', 'd.idDossier')
            ->join('projets as p', 'ds.idDossier', '=', 'p.idDossier')
            ->where('p.idProjet', $idProjet)
            ->where('tp.idSectionDocument', 4)
            ->exists();

        return $nbRapport;
    }

    public function checkAttestation($idProjet): bool
    {
        $query = DB::table('v_attestation_projet_employe')->where('idProjet', $idProjet)->exists();
        return $query;
    }

    public function sendCours($idProjet)
    {
        try {
            $module_ressources = DB::table('module_ressources as mr')
                ->select('idModuleRessource', 'taille', 'module_ressource_name', 'file_path', 'module_ressource_extension', 'mr.idModule')
                ->join('mdls as m', 'mr.idModule', 'm.idModule')
                ->join('projets as p', 'p.idModule', 'm.idModule')
                ->where('p.idProjet', $idProjet)
                ->get();

            $apprenants = DB::table('v_list_apprenants')
                ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_email')
                ->where('idProjet', $idProjet)
                ->get();


            foreach ($apprenants as $apprenant) {
                // Vérifier si l'apprenant a un mail
                if (!isset($apprenant->emp_email) || empty($apprenant->emp_email)) {
                    continue;
                } else {
                    foreach ($module_ressources as $ressource) {
                        $url_cours = "https://formafusionmg.ams3.digitaloceanspaces.com/formafusionmg/{$ressource->file_path}";

                        // Télécharger le fichier depuis DigitalOcean Spaces
                        $fileContents = file_get_contents($url_cours);

                        if ($fileContents) {
                            // Définir un chemin temporaire
                            $localFilePath = storage_path("app/temp/{$ressource->module_ressource_name}");

                            // Sauvegarder le fichier temporairement
                            file_put_contents($localFilePath, $fileContents);

                            Mail::to($apprenant->emp_email)->send(new CoursMail($apprenant, $ressource, $localFilePath));

                            // Supprimer le fichier temporaire après l'envoi
                            unlink($localFilePath);

                            // Enregistrer l'envoi
                            DB::table('suivi_envois')->insert([
                                'idProjet' => $idProjet,
                                'idEmploye' => $apprenant->idEmploye,
                                'idDocument' => $ressource->idModuleRessource,
                                'type_document' => 'cours',
                                'date_envoi' => now()
                            ]);
                        }
                    }
                }
            }
            return redirect()->back()->with('success', 'Cours envoyés avec succès avec les fichiers joints');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Erreur d envoie de cours : ' . $e->getMessage());
        }
    }

    public function sendRapport($idProjet)
    {
        try {
            $rapports = DB::table('documents as d')
                ->select('idDocument', 'titre', 'path', 'filename')
                ->join('type_documents as tp', 'tp.idTypeDocument', '=', 'd.idTypeDocument')
                ->join('dossiers as ds', 'ds.idDossier', '=', 'd.idDossier')
                ->join('projets as p', 'ds.idDossier', '=', 'p.idDossier')
                ->where('p.idProjet', $idProjet)
                ->where('tp.idSectionDocument', 4)
                ->get();

            $referents = DB::table('v_employe_alls')
                ->select('idEmploye', 'name as ref_name', 'firstName as ref_firstname', 'email as ref_email')
                ->where('idCustomer', Customer::idCustomer())
                ->where('role_id', 8)
                ->get();

            foreach ($referents as $referent) {
                // Vérifier si l'referent a un mail
                if (!isset($referent->ref_email) || empty($referent->ref_email)) {
                    continue;
                } else {
                    foreach ($rapports as $rapport) {
                        $url_rapport = "https://formafusionmg.ams3.digitaloceanspaces.com/formafusionmg/{$rapport->path}";

                        // Télécharger le fichier depuis DigitalOcean Spaces
                        $fileContents = file_get_contents($url_rapport);

                        if ($fileContents) {
                            // Définir un chemin temporaire
                            $localFilePath = storage_path("app/temp/{$rapport->filename}");

                            // Sauvegarder le fichier temporairement
                            file_put_contents($localFilePath, $fileContents);

                            Mail::to($referent->ref_email)->send(new RapportMail($referent, $rapport, $localFilePath));

                            // Supprimer le fichier temporaire après l'envoi
                            unlink($localFilePath);

                            DB::table('suivi_envois')->insert([
                                'idProjet' => $idProjet,
                                'idEmploye' => $referent->idEmploye,
                                'idDocument' => $rapport->idDocument,
                                'type_document' => 'rapport',
                                'date_envoi' => now()
                            ]);
                        }
                    }
                }
            }
            return redirect()->back()->with('success', 'Rapport envoyés avec les fichiers joints');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Erreur d envoie de rapport : ' . $e->getMessage());
        }
    }
    public function sendAttestation($idProjet)
    {
        try {
            $attestations = DB::table('v_attestation_projet_employe')
                ->select('idEmploye', 'name as emp_name', 'firstName as emp_firstname', 'email as emp_email', 'file_path', 'file_name', 'idAttestation')
                ->where('idCfp', Customer::idCustomer())
                ->where('idProjet', $idProjet)
                ->get();

            foreach ($attestations as $att) {
                // Vérifier le mail de l'employé
                if (!isset($att->emp_email) || empty($att->emp_email)) {
                    continue;
                } else {
                    $url_attestation = "https://formafusionmg.ams3.digitaloceanspaces.com/formafusionmg/{$att->file_path}";
                    // Télécharger le fichier depuis DigitalOcean Spaces
                    $fileContents = file_get_contents($url_attestation);
                    if ($fileContents) {
                        // Définir un chemin temporaire
                        $localFilePath = storage_path("app/temp/{$att->file_name}");

                        // Sauvegarder le fichier temporairement
                        file_put_contents($localFilePath, $fileContents);

                        Mail::to($att->emp_email)->send(new AttestationMail($att, $localFilePath));

                        // Supprimer le fichier temporaire après l'envoi
                        unlink($localFilePath);

                        DB::table('suivi_envois')->insert([
                            'idProjet' => $idProjet,
                            'idEmploye' => $att->idEmploye,
                            'idDocument' => $att->idAttestation,
                            'type_document' => 'attestation',
                            'date_envoi' => now()
                        ]);
                    }
                }
            }
            return redirect()->back()->with('success', 'Attestations envoyés avec les fichiers joints');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Erreur d envoie de rapport : ' . $e->getMessage());
        }
    }

    public function suiviEnvois($idProjet)
    {
        $coursDejaEnvoye = DB::table('suivi_envois')
            ->where('idProjet', $idProjet)
            ->where('type_document', 'cours')
            ->exists();

        $rapportDejaEnvoye = DB::table('suivi_envois')
            ->where('idProjet', $idProjet)
            ->where('type_document', 'rapport')
            ->exists();

        $attestationDejaEnvoye = DB::table('suivi_envois')
            ->where('idProjet', $idProjet)
            ->where('type_document', 'attestation')
            ->exists();

        return [$coursDejaEnvoye, $rapportDejaEnvoye, $attestationDejaEnvoye];
    }

    public function getSuivie($idProjet)
    {
        $suiviRapport = DB::table('suivi_envois as se')
            ->select('u.name', 'u.firstname', 'u.email', 'se.idDocument', 'd.titre', 'se.type_document', 'se.date_envoi')
            ->join('documents as d', 'se.idDocument', '=', 'd.idDocument')
            ->join('users as u', 'se.idEmploye', '=', 'u.id')
            ->where('se.idProjet', $idProjet)
            ->where('type_document', 'rapport')
            ->orderBy('se.date_envoi', 'asc')
            ->get()
            ->toArray();

        $suiviCours = DB::table('suivi_envois as se')
            ->select('u.name', 'u.firstname', 'u.email', 'se.idDocument', 'mr.module_ressource_name as titre', 'se.type_document', 'se.date_envoi')
            ->join('module_ressources as mr', 'se.idDocument', '=', 'mr.idModuleRessource')
            ->join('users as u', 'se.idEmploye', '=', 'u.id')
            ->where('se.idProjet', $idProjet)
            ->where('type_document', 'cours')
            ->orderBy('se.date_envoi', 'asc')
            ->get()
            ->toArray();

        $suiviAttestations = DB::table('suivi_envois as se')
            ->select('u.name', 'u.firstname', 'u.email', 'se.idDocument', 'att.file_name as titre', 'se.type_document', 'se.date_envoi')
            ->join('attestations as att', 'se.idDocument', '=', 'att.idAttestation')
            ->join('users as u', 'se.idEmploye', '=', 'u.id')
            ->where('se.idProjet', $idProjet)
            ->where('type_document', 'attestation')
            ->orderBy('se.date_envoi', 'asc')
            ->get()
            ->toArray();

        return response()->json([$suiviRapport, $suiviCours, $suiviAttestations]);
    }
}
