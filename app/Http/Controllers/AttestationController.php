<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ProjetController;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class AttestationController extends Controller
{

    public function index()
    {
        $attestations = DB::table('v_attestation_projet_employe')
            ->select('name', 'firstName', 'number_attestation', 'moduleName', DB::raw('DATE_FORMAT(dateDebut, "%d %b. %Y") as dateDebut'), DB::raw('DATE_FORMAT(dateFin, "%d %b. %Y") as dateFin'), 'idEtp', 'logo', 'idCfp', 'etpName', 'idProjet', 'idAttestation', 'file_path', 'file_name')
            ->where('idCfp', auth()->user()->id)
            ->orderBy('idAttestation', 'desc')
            ->get();

        $countAttestation = DB::table('v_attestation_projet_employe')
            ->where('idCfp', auth()->user()->id)
            ->count();

        return response()->json([
            'attestations' => $attestations,
            'countAttestation' => $countAttestation
        ]);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'projet' => 'required',
                'apprenant' => 'required',
                'fichier' => 'required|image|max:2048',
            ], [
                'projet.required' => 'Le champ projet est obligatoire.',
                'apprenant.required' => 'Le champ apprenant est obligatoire.',
                'fichier.required' => 'Veuillez sélectionner une image.',
                'fichier.image' => 'Le fichier doit être une image valide (PNG, JPEG, WEBP, etc.).',
                'fichier.max' => 'L\'image ne doit pas dépasser 2 Mo.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()]);
        }

        $idProjet = $request->input('projet');
        $idEmploye = $request->input('apprenant');
        $file = $request->file('fichier');

        if ($this->attestationExists($idProjet, $idEmploye)) {
            return response()->json(['error' => 'Cet apprenant a déjà une attestation']);
        }

        $driver = new Driver();
        $manager = new ImageManager($driver);

        try {
            $image = $manager->read($file)->toWebp(25);

            $disk = Storage::disk('do');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.webp';
            $path = 'attestation/idProjet_' . $idProjet . '/idEmploye_' . $idEmploye . '/' . $filename;
            $disk->put($path, $image->__toString());

            $numeroAttestation = $this->generateNumberAttestation($idProjet, $idEmploye);

            DB::table('attestations')->insert([
                'idProjet' => $idProjet,
                'idEmploye' => $idEmploye,
                'idCfp' => auth()->user()->id,
                'file_path' => $path,
                'file_name' => $filename,
                'number_attestation' => $numeroAttestation,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de l\'enregistrement de l\'attestation : ' . $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cette attestation a été enregistré avec succès.'
        ]);
    }

    public function edit($idAttestation)
    {
        $attestation = DB::table('v_attestation_projet_employe')
            ->select('name', 'firstName', 'number_attestation', 'moduleName', DB::raw('DATE_FORMAT(dateDebut, "%d %b. %Y") as dateDebut'), DB::raw('DATE_FORMAT(dateFin, "%d %b. %Y") as dateFin'), 'idEtp', 'logo', 'idCfp', 'etpName', 'idProjet', 'idAttestation', 'file_path', 'file_name')
            ->where('idCfp', auth()->user()->id)
            ->where('idAttestation', $idAttestation)
            ->orderBy('idAttestation', 'desc')
            ->first();

        return response()->json(['attestation' => $attestation]);
    }

    public function update(Request $request, $idAttestation)
    {
        $request->validate([
            'projet' => 'required|integer',
            'apprenant' => 'required|integer',
            'fichier' => 'nullable|file|mimes:pdf|max:2048',
        ]);

        try {
            return DB::transaction(function () use ($request, $idAttestation) {
                $attestation = DB::table('attestations')
                    ->where('idCfp', auth()->user()->id)
                    ->where('idAttestation', $idAttestation)
                    ->first();

                if (!$attestation) {
                    return response()->json(['error' => 'Attestation non trouvée'], 404);
                }

                $data = [
                    'idProjet' => $request->projet,
                    'idApprenant' => $request->apprenant,
                ];

                if ($request->hasFile('fichier')) {
                    if ($attestation->file_path && Storage::exists($attestation->file_path)) {
                        Storage::disk('do')->delete($attestation->file_path);
                    }

                    $file = $request->file('fichier');
                    $driver = new Driver();
                    $manager = new ImageManager($driver);
                    $image = $manager->read($file)->toWebp(25);

                    $disk = Storage::disk('do');
                    $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.webp';
                    $filePath = 'attestation/idProjet_' . $request->projet . '/idEmploye_' . $request->apprenant . '/' . $filename;
                    $disk->put($filePath, $image->__toString());

                    $numeroAttestation = $this->generateNumberAttestation($request->projet, $request->apprenant);
                    $data['number_attestation'] = $numeroAttestation;
                    $data['file_path'] = $filePath;
                    $data['file_name'] = $filename;
                }

                DB::table('attestations')
                    ->where('idAttestation', $idAttestation)
                    ->update($data);

                return response()->json(['success' => 'Attestation mise à jour avec succès']);
            });
        } catch (Exception $e) {
            return response()->json(['error' => "Une erreur s'est produite lors de la modification de cette attestation"]);
        }
    }

    public function destroy($idAttestation)
    {
        return DB::transaction(function () use ($idAttestation) {

            try {
                $filePath = DB::table('attestations')
                    ->where('idAttestation', $idAttestation)
                    ->pluck('file_path')
                    ->first();

                if ($filePath && Storage::disk('do')->exists($filePath)) {
                    Storage::disk('do')->delete($filePath);
                }

                $delete = DB::table('attestations')->where('idAttestation', $idAttestation)->delete();

                if ($delete) {
                    return response()->json(['success' => 'L\'attestation a bien été supprimée']);
                } else {
                    return response()->json(['error' => 'Une erreur s\'est produite lors de la suppression de cette attestation']);
                }
            } catch (\Exception $e) {
                // return response()->json(['error' => 'Erreur inattendue: ' . $e->getMessage()]);
                return response()->json(['error' => 'Erreur inattendue']);
            }
        });
    }

    function generateNumberAttestation($idProjet, $idEmploye)
    {
        return now()->format('mY') . $idProjet . $idEmploye;
    }

    function attestationExists($idProjet, $idEmploye)
    {
        return DB::table('attestations')
            ->where('idProjet', $idProjet)
            ->where('idEmploye', $idEmploye)
            ->exists();
    }

    public function getProjet()
    {
        $projects = DB::table('v_projet_cfps')
            ->select('idProjet', 'dateDebut', 'idEtp', 'idCfp_inter', 'dateFin', 'module_name', 'etp_name', 'project_reference', 'idCfp')
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                });
            })
            ->where('project_status', 'Terminé')
            ->where('module_name', '!=', 'Default module')
            ->groupBy('idProjet')
            ->orderBy('dateDebut', 'asc')
            ->get();

        $projets = [];
        foreach ($projects as $project) {

            $projets[] = [
                'idProjet' => $project->idProjet,
                'project_reference' => $project->project_reference,
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
                'module_name' => $project->module_name,
                'etp_name' => ProjetController::getEtpProjectInter($project->idProjet, $project->idCfp_inter),
                'idEtp' => $project->idEtp,
                // 'apprs' => ProjetController::getApprListProjet($project->idProjet),
            ];
        }

        // dd($projets);

        // return $projets;

        return response()->json($projets);
    }

    public static function getApprenant($idProjet)
    {
        $apprIntras = DB::table('v_list_apprenants')->from('v_list_apprenants as vl')
            ->leftJoin('attestations as a', 'a.idEmploye', 'vl.idEmploye')
            ->select('vl.idEmploye', 'number_attestation', 'emp_name', 'vl.idProjet', 'emp_firstname', 'emp_photo', 'etp_name', 'emp_initial_name')
            ->where('vl.idProjet', $idProjet)
            ->orderBy('emp_name', 'asc')
            ->get()
            ->toArray();

        $apprenantInters = DB::table('v_list_apprenant_inter_added')->from('v_list_apprenant_inter_added as vl')
            ->leftJoin('attestations as a', 'a.idEmploye', 'vl.idEmploye')
            ->select('vl.idEmploye', 'number_attestation', 'emp_name', 'vl.idProjet', 'emp_firstname', 'emp_photo', 'etp_name')
            ->where('vl.idProjet', $idProjet)
            ->orderBy('emp_name', 'asc')
            ->get()
            ->toArray();

        $apprs = array_merge($apprIntras, $apprenantInters);

        // return response()->json(['apprs' => $apprs]);
        return response()->json($apprs);
    }
}
