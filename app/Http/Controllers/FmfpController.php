<?php


namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\ProjetService;
use Carbon\Carbon;
use Dotenv\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator as FacadesValidator;

class FmfpController extends Controller
{
    protected $projectService;

    public function __construct(ProjetService $projectService)
    {
        $this->projectService = $projectService;
    }

    private function getCountCommentsInFolder($folderId)
    {
        $count = DB::table('fmfp_comments')
            ->where('fmfp_projects_id', $folderId)
            ->count();
        return $count;
    }

    private function getModuleIsExistByFolder($folderId)
    {
        return DB::table('fmfp_module_contents')
            ->where('idFmfp', $folderId)
            ->pluck('idModule');
    }


    private function getAllModuleByFmfp($id)
    {
        $modules = DB::table('fmfp_module_contents as fm')
            ->select('fm.id as idFmfp', 'mdls.idModule', 'mdls.moduleName', 'module_image as moduleImage')
            ->join('mdls', 'fm.idModule', 'mdls.idModule')
            ->where('fm.idFmfp', $id)
            ->get();
        return $modules;
    }

    private function getAllModules()
    {
        $modules = DB::table('mdls')
            ->select('idModule', 'moduleName')
            ->where('idCustomer', Customer::idCustomer())
            ->where('moduleName', '!=', 'Default module')
            ->get();
        return $modules;
    }

    private function getAllFmfpType()
    {
        $types = DB::table('fmfp_type_project')
            ->select('idType', 'type', 'type_description')
            ->get();

        return $types;
    }

    private function getAllEntreprises()
    {
        $entreprises = DB::table('v_collaboration_cfp_etps')
            ->select('idEtp', 'etp_name', 'etp_logo', 'etp_email')
            ->where('idCfp', Customer::idCustomer())
            ->orderBy('etp_name', 'ASC')
            ->get();

        return $entreprises;
    }

    private function getAllStatus()
    {
        $status_fmfp = DB::table('fmfp_status')
            ->select('idStatus', 'status_name')
            ->get();

        return $status_fmfp;
    }

    public function index()
    {
        $fmfp = DB::table('v_fmfp')
            ->select('id', 'type', 'code', 'first_deposit', 'date_first_deposit', 'second_deposit', 'date_second_deposit', 'idEtp', 'requested_amount', 'start_date', 'end_date', 'idStatus', 'status_name', 'type_fmfp_description', 'etp_name', 'etp_email', 'etp_phone', 'ap_name')
            ->where('idCfp', Customer::idCustomer())
            ->groupBy('id')
            ->get();
        if (count($fmfp) < 0) {
            return response()->json([
                'status' => 204,
                'message' => 'Aucun résultat trouvé'
            ], 204);
        }

        $entreprises = $this->getAllEntreprises();
        $types = $this->getAllFmfpType();
        $status_fmfp = $this->getAllStatus();
        $modules = $this->getAllModules();






        $result = [];

        foreach ($fmfp as $item) {
            $result[] = [
                'id' => $item->id,
                'code' => $item->code,
                'type' => $item->type,
                'idEtp' => $item->idEtp,
                'first_deposit' => $item->first_deposit,
                'date_first_deposit' => $item->date_first_deposit,
                'second_deposit' => $item->second_deposit,
                'date_second_deposit' => $item->date_second_deposit,
                'requested_amount' => $item->requested_amount,
                'start_date' => $item->start_date,
                'end_date' => $item->end_date,
                'status_name' => $item->status_name,
                'type_fmfp_description' => $item->type_fmfp_description,
                'etp_name' => $item->etp_name,
                'etp_email' => $item->etp_email,
                'etp_phone' => $item->etp_phone,
                'ap_name' => $item->ap_name,
                'modules' => $this->getAllModuleByFmfp($item->id),
                'comment_count' => $this->getCountCommentsInFolder($item->id)

            ];
        }

        return response()->json([
            'status' => 200,
            'fmfp' => $result,
            'entreprises' => $entreprises,
            'types' => $types,
            'status_fmfp' => $status_fmfp,
            'modules' => $modules

        ], 200);
    }

    public function storeFmfp(Request $req)
    {
        $req->validate([
            'requested_amount' => 'required|numeric',
            'type' => 'required|exists:fmfp_type_project,idType',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'idEtp' => 'required|integer',
            'idModule' => 'required|array',
        ]);

        try {
            DB::transaction(function () use ($req) {
                $projectsID = DB::table('fmfp_projects')->insertGetId([
                    'type' => $req->type,
                    'idEtp' => $req->idEtp,
                    'idCfp' => Customer::idCustomer(),
                    'requested_amount' => $req->requested_amount,
                    'approved_amount' => $req->approved_amount ?? null,
                    'request_date' => Carbon::now(),
                    'start_date' => $req->start_date,
                    'end_date' => $req->end_date,
                    'idStatus' => 1
                ]);

                if ($req->type == 3) {
                    $req->validate(['ap_name' => 'required|string']);
                    DB::table('fmfp_ap_type')->insert([
                        'name' => $req->ap_name,
                        'idfmfp' => $projectsID
                    ]);
                }

                foreach ($req->idModule as $moduleId) {
                    DB::table('fmfp_module_contents')->insert([
                        'idFmfp' => $projectsID,
                        'idModule' => $moduleId
                    ]);
                }
            });

            return response()->json([
                'status' => 201,
                'message' => 'Créé avec succès'
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function show($id)
    {
        $fmfp = DB::table('v_fmfp')
            ->select(
                'id',
                'type',
                'code',
                'idEtp',
                'first_deposit',
                'date_first_deposit',
                'second_deposit',
                'date_second_deposit',
                'requested_amount',
                'approved_amount',
                'request_date',
                'start_date',
                'end_date',
                'idStatus',
                'status_name',
                'type_fmfp_description',
                'type_fmfp',
                'etp_name',
                'etp_email',
                'etp_phone',
                'ap_name'
            )
            ->where('idCfp', Customer::idCustomer())
            ->where('id', $id)
            ->first();

        if (!$fmfp) {
            return response()->json([
                'message' => 'FMFP introuvable.'
            ], 404);
        }
        $modules = $this->getAllModuleByFmfp($id);
        $fmfp->modules = $modules;
        return response()->json([
            'fmfp' => $fmfp
        ], 200);
    }

    public function changeStatus(Request $req, $folderId)
    {
        DB::beginTransaction();

        try {
            if ($req->idStatus == 4) {
                $this->projectService->createProjectFmfp($folderId);
            }

            DB::table('fmfp_projects')
                ->where('id', $folderId)
                ->update([
                    'idStatus' => $req->idStatus
                ]);

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Félicitations, votre dossier fmfp est validé',
                'idStatus' => $req->idStatus
            ], 200);
        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => 500,
                'message' => 'Erreur serveur lors du processus.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $req, $id)
    {
        $fmfp = DB::table('fmfp_projects')->where('id', $id)->first();

        if (!$fmfp) {
            return response()->json(['message' => 'Projet introuvable'], 404);
        }

        if ($req->first_deposit) {
            $req->validate(['date_first_deposit' => 'required']);
        } else if ($req->second_deposit) {
            $req->validate(['date_second_deposit' => 'required']);
        }

        try {
            DB::beginTransaction();
            DB::table('fmfp_projects')
                ->where('id', $id)
                ->update([
                    'code' => $req->code,
                    'requested_amount' => $req->requested_amount,
                    'approved_amount' => $req->approved_amount,
                    'start_date' => $req->start_date,
                    'end_date' => $req->end_date,
                    'first_deposit' => $req->first_deposit,
                    'date_first_deposit' => $req->date_first_deposit,
                    'second_deposit' => $req->second_deposit,
                    'date_second_deposit' => $req->date_second_deposit
                ]);

            $moduleIds = $req->input('idModule', []);
            if (!empty($moduleIds)) {
                $existingModules = $this->getModuleIsExistByFolder($id)->toArray();

                $modulesToDelete = array_diff($existingModules, $moduleIds);
                if (!empty($modulesToDelete)) {
                    DB::table('fmfp_module_contents')
                        ->where('idFmfp', $id)
                        ->whereIn('idModule', $modulesToDelete)
                        ->delete();
                }

                $modulesToInsert = array_diff($moduleIds, $existingModules);
                if (!empty($modulesToInsert)) {
                    $insertData = [];
                    foreach ($modulesToInsert as $moduleId) {
                        $insertData[] = [
                            'idFmfp' => $id,
                            'idModule' => $moduleId,
                        ];
                    }
                    DB::table('fmfp_module_contents')->insert($insertData);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Projet mis à jour avec succès'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }



    public function delete($id)
    {
        $fmfp = DB::table('fmfp_projects')->where('id', $id)->first();

        if (!$fmfp) {
            return response()->json([
                'status' => 404,
                'message' => 'Projet Fmfp introuvable'
            ], 404);
        }


        DB::table('fmfp_projects')->where('id', $id)->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Supprimé avec succès'
        ], 200);
    }



    public function commentFolder(Request $req, $folderId)
    {
        $validator = FacadesValidator::make($req->all(), [
            'contents' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->messages()
            ], 500);
        }


        try {
            $commentId = DB::table('fmfp_comments')
                ->insertGetId([
                    'contents' => $req->contents,
                    'date' => Carbon::now(),
                    'fmfp_projects_id' => $folderId,
                    'idUser' => auth()->user()->id
                ]);

            return response()->json([
                'status' => 201,
                'message' => 'Commentaire ajouté avec succès',
                'comment' => [
                    'id' => $commentId,
                    'contents' => $req->contents,
                    'date' => Carbon::now(),
                    'idUser' => auth()->user()->id,
                    'fmfp_projects_id' => $folderId,
                ]
            ], 201);
        } catch (\Exception $th) {
            return response()->json([
                'status' => 500,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    public function getAllCommentsInFolder($folderId)
    {
        $comments = DB::table('fmfp_comments as fc')
            ->join('users as u', 'fc.idUser', 'u.id')
            ->select('fc.id', 'fc.contents', 'fc.fmfp_projects_id', 'fc.idUser', 'fc.date', 'u.name', 'u.email', 'u.photo')
            ->where('fc.fmfp_projects_id', $folderId)
            ->orderBy('fc.date', 'desc')
            ->get();

        if (count($comments) <= 0) {
            return response()->json([
                'message' => 'Aucun commentaire'
            ], 204);
        }

        return response()->json([
            'comments' => $comments
        ], 200);
    }


    public function deleteCommentInFolder($idComment, $idFolder)
    {
        $comment = DB::table('fmfp_comments')
            ->where('idUser', auth()->user()->id)
            ->where('fmfp_projects_id', $idFolder)
            ->pluck('id')->toArray();

        if (!in_array($idComment, $comment)) {
            return response()->json([
                'message' => "Impossible de supprimer ce commentaire"
            ], 403);
        }

        DB::table('fmfp_comments')
            ->where('idUser', auth()->user()->id)
            ->where('fmfp_projects_id', $idFolder)
            ->where('id', $idComment)
            ->delete();


        return response()->json([
            'status' => 200,
            'message' => 'Supprimer avec succès'
        ], 200);
    }

    public function indexEtp()
    {
        $idEtp = auth()->user()->id;

        $fmfp = DB::table('v_fmfp')
            ->select('id', 'start_date', 'end_date', 'status_name', 'type_fmfp_description', 'ap_name', 'requested_amount', 'approved_amount', 'request_date')
            ->where('idEtp', $idEtp)
            ->get();

        return response()->json(['folderFmfp' => $fmfp], 200);
    }
}
