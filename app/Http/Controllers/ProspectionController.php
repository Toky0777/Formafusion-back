<?php

namespace App\Http\Controllers;

use App\Mail\OpportuniteMail;
use App\Mail\RequestCustomer;
use App\Mail\WinOpMail;
use App\Models\Customer;
use App\Models\Projet;
use App\Models\User;
use App\Services\UtilService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ProspectionController extends Controller
{
    protected $utilService;

    public function __construct(UtilService $utilService)
    {
        $this->utilService = $utilService;
    }

    public function index()
    {

        // Récupérer les opportunités
        $opportunities = DB::table('opportunites AS op')
            ->select(
                'op.id AS id_opportunite',
                'op.statut AS opportunite_id_statut',
                'op.ref_name',
                'op.ref_firstname',
                'op.ref_email AS etp_email',
                'op.ref_phone AS etp_phone',
                'op.dateDeb',
                'op.dateFin',
                'op.nbPersonne',
                'op.note AS remarque',
                'op.prix',
                'op.idEtp',
                'cst.customerName AS etp_name',
                'cst.logo AS etp_logo',
                'md.moduleName AS cours_name',
                'md.module_image AS cours_img',
                'ps.prospect_name'
            )
            ->leftJoin('customers AS cst', 'op.idEtp', '=', 'cst.idCustomer')
            ->leftjoin('mdls AS md', 'op.idModule', '=', 'md.idModule')
            ->leftJoin('prospects AS ps', 'op.id_prospect', '=', 'ps.id')
            ->where('op.idCustomer', Customer::idCustomer())
            ->where('opportunitie_is_win', 0)
            ->where('opportunitie_is_lost', 0)
            ->where('opportunitie_is_standBy', 0)
            ->orderBy('op.dateDeb', 'asc')
            ->get();

        // Transformer les opportunités en un format approprié
        $encours = $opportunities->map(function ($op) {
            return [
                'id_opportunite' => $op->id_opportunite,
                'opportunite_id_statut' => $op->opportunite_id_statut,
                'ref_name' => $op->ref_name,
                'ref_firstName' => $op->ref_firstname,
                'etp_email' => $op->etp_email,
                'etp_phone' => $op->etp_phone,
                'etp_logo' => $op->etp_logo ?? null,
                'etp_name' => $op->idEtp ? $op->etp_name : $op->prospect_name,
                'cours_name' => $op->cours_name ?? "",
                'dateDeb' => $this->formatDate($op->dateDeb),
                'dateFin' => $this->formatDate($op->dateFin),
                'remarque' => $op->remarque,
                'cours_img' => $op->cours_img,
                'nbPersonne' => $op->nbPersonne . ' Pers',
                'prix' => $this->utilService->formatPrice($op->prix),
                'prix_calculate' => $op->prix
            ];
        });

        // Calculer la somme des prix après transformation
        $total_prix_op = $encours->sum(function ($op) {
            // Vous pouvez obtenir la valeur numérique avant formatage, sans utiliser $this->formatPrice()
            return $op['prix_calculate']; // Assurez-vous que 'prix' est la valeur numérique
        });

        $stat = [
            'total_op' => $this->utilService->formatPrice($total_prix_op),
            'nb_op' => $opportunities->count()
        ];


        // Récupérer les opportunités
        $wins = DB::table('opportunites AS op')
            ->select(
                'op.id AS id_opportunite',
                'op.statut AS opportunite_id_statut',
                'op.ref_name',
                'op.ref_firstname',
                'op.ref_email AS etp_email',
                'op.ref_phone AS etp_phone',
                'op.dateDeb',
                'op.dateFin',
                'op.nbPersonne',
                'op.note AS remarque',
                'op.prix',
                'op.idEtp',
                'op.opportunitie_is_win',
                'cst.customerName AS etp_name',
                'cst.logo AS etp_logo',
                'md.moduleName AS cours_name',
                'md.module_image AS cours_img',
                'ps.prospect_name'
            )
            ->leftJoin('customers AS cst', 'op.idEtp', '=', 'cst.idCustomer')
            ->leftjoin('mdls AS md', 'op.idModule', '=', 'md.idModule')
            ->leftJoin('prospects AS ps', 'op.id_prospect', '=', 'ps.id')
            ->where('op.idCustomer', Customer::idCustomer())
            ->where('op.opportunitie_is_win', 1)
            ->orderBy('op.dateDeb', 'asc')
            ->get();

        // Transformer les opportunités en un format approprié
        $gagnes = $wins->map(function ($op) {
            return [
                'id_opportunite' => $op->id_opportunite,
                'opportunite_id_statut' => $op->opportunite_id_statut,
                'ref_name' => $op->ref_name,
                'ref_firstName' => $op->ref_firstname,
                'etp_email' => $op->etp_email,
                'etp_phone' => $op->etp_phone,
                'etp_logo' => $op->etp_logo ?? null,
                'etp_name' => $op->idEtp ? $op->etp_name : $op->prospect_name,
                'cours_name' => $op->cours_name ?? "",
                'dateDeb' => $this->formatDate($op->dateDeb),
                'dateFin' => $this->formatDate($op->dateFin),
                'remarque' => $op->remarque,
                'opportunitie_is_win' => $op->opportunitie_is_win,
                'cours_img' => $op->cours_img,
                'nbPersonne' => $op->nbPersonne . ' Pers',
                'prix' => $this->utilService->formatPrice($op->prix),
            ];
        });

        // Récupérer les opportunités
        $losts = DB::table('opportunites AS op')
            ->select(
                'op.id AS id_opportunite',
                'op.statut AS opportunite_id_statut',
                'op.ref_name',
                'op.ref_firstname',
                'op.ref_email AS etp_email',
                'op.ref_phone AS etp_phone',
                'op.dateDeb',
                'op.dateFin',
                'op.nbPersonne',
                'op.note AS remarque',
                'op.prix',
                'op.idEtp',
                'op.opportunitie_is_lost',
                'cst.customerName AS etp_name',
                'cst.logo AS etp_logo',
                'md.moduleName AS cours_name',
                'md.module_image AS cours_img',
                'ps.prospect_name'
            )
            ->leftJoin('customers AS cst', 'op.idEtp', '=', 'cst.idCustomer')
            ->leftjoin('mdls AS md', 'op.idModule', '=', 'md.idModule')
            ->leftJoin('prospects AS ps', 'op.id_prospect', '=', 'ps.id')
            ->where('op.idCustomer', Customer::idCustomer())
            ->where('opportunitie_is_lost', 1)
            ->orderBy('op.dateDeb', 'asc')
            ->get();

        // Transformer les opportunités en un format approprié
        $perdues = $losts->map(function ($op) {
            return [
                'id_opportunite' => $op->id_opportunite,
                'opportunite_id_statut' => $op->opportunite_id_statut,
                'ref_name' => $op->ref_name,
                'ref_firstName' => $op->ref_firstname,
                'etp_email' => $op->etp_email,
                'etp_phone' => $op->etp_phone,
                'etp_logo' => $op->etp_logo ?? null,
                'etp_name' => $op->idEtp ? $op->etp_name : $op->prospect_name,
                'cours_name' => $op->cours_name ?? "",
                'dateDeb' => $this->formatDate($op->dateDeb),
                'dateFin' => $this->formatDate($op->dateFin),
                'remarque' => $op->remarque,
                'opportunitie_is_lost' => $op->opportunitie_is_lost,
                'cours_img' => $op->cours_img,
                'nbPersonne' => $op->nbPersonne . ' Pers',
                'prix' => $this->utilService->formatPrice($op->prix),
            ];
        });

        // Récupérer les opportunités
        $standBy = DB::table('opportunites AS op')
            ->select(
                'op.id AS id_opportunite',
                'op.statut AS opportunite_id_statut',
                'op.ref_name',
                'op.ref_firstname',
                'op.ref_email AS etp_email',
                'op.ref_phone AS etp_phone',
                'op.dateDeb',
                'op.dateFin',
                'op.nbPersonne',
                'op.note AS remarque',
                'op.prix',
                'op.idEtp',
                'op.opportunitie_is_standBy',
                'cst.customerName AS etp_name',
                'cst.logo AS etp_logo',
                'md.moduleName AS cours_name',
                'md.module_image AS cours_img',
                'ps.prospect_name'
            )
            ->leftJoin('customers AS cst', 'op.idEtp', '=', 'cst.idCustomer')
            ->leftjoin('mdls AS md', 'op.idModule', '=', 'md.idModule')
            ->leftJoin('prospects AS ps', 'op.id_prospect', '=', 'ps.id')
            ->where('op.idCustomer', Customer::idCustomer())
            ->where('opportunitie_is_standBy', 1)
            ->orderBy('op.dateDeb', 'asc')
            ->get();

        // Transformer les opportunités en un format approprié
        $enveilles = $standBy->map(function ($op) {
            return [
                'id_opportunite' => $op->id_opportunite,
                'opportunite_id_statut' => $op->opportunite_id_statut,
                'ref_name' => $op->ref_name,
                'ref_firstName' => $op->ref_firstname,
                'etp_email' => $op->etp_email,
                'etp_phone' => $op->etp_phone,
                'etp_logo' => $op->etp_logo ?? null,
                'etp_name' => $op->idEtp ? $op->etp_name : $op->prospect_name,
                'cours_name' => $op->cours_name ?? "",
                'dateDeb' => $this->formatDate($op->dateDeb),
                'dateFin' => $this->formatDate($op->dateFin),
                'remarque' => $op->remarque,
                'opportunitie_is_standBy' => $op->opportunitie_is_standBy,
                'cours_img' => $op->cours_img,
                'nbPersonne' => $op->nbPersonne . ' Pers',
                'prix' => $this->utilService->formatPrice($op->prix),
            ];
        });


        $prospectsCollections = DB::table('prospects AS p')
            ->select('p.id', 'p.prospect_name', 'op.ref_email', 'op.ref_name', 'op.ref_firstName')
            ->join('opportunites AS op', 'p.id', '=', 'op.id_prospect')
            ->where('p.idCustomer', Customer::idCustomer())
            ->orderBy('p.prospect_name', 'asc')
            ->get();

        $prospects = $prospectsCollections->map(function ($p) {
            return [
                'id' => $p->id,
                'prospect_name' => $p->prospect_name,
                'email' => $p->ref_email,
                'name' => $p->ref_name,
                'firstName' => $p->ref_firstName
            ];
        });


        return view('CFP.prospections.index', compact(['encours', 'gagnes', 'perdues', 'enveilles', 'prospects', 'stat']));
    }



    public function storeOrUpdate(Request $req)
    {
        // Validation des champs requis
        $validation = Validator::make($req->all(), [
            'idVille' => 'required',
            'ref_email' => 'required|email',
            'ref_name' => 'required|min:2',
            'idModule' => 'required',
            'dateDeb' => 'required|date',
            'dateFin' => 'required|date|after_or_equal:dateDeb',
            'prospect_name' => 'nullable|string',
            'opportunite_id' => 'nullable|integer', // Ajouté pour gérer les mises à jour
        ]);

        if ($validation->fails()) {
            return response()->json([$validation->messages()]);
        }

        // $data = $validation->validated();

        try {

            DB::beginTransaction();

            // Gestion des prospects
            $id_prospect = null;
            if (empty($req->idEtp)) {
                // Vérifier si le prospect existe déjà
                $existingProspect = DB::table('prospects')
                    ->where('prospect_name', $req->prospect_name)
                    ->first();

                if ($existingProspect) {
                    // Récupérer l'ID du prospect existant
                    $id_prospect = $existingProspect->id;
                } else {
                    // Créer un nouveau prospect et récupérer son ID
                    $id_prospect = DB::table('prospects')->insertGetId([
                        'prospect_name' => $req->prospect_name,
                        'idCustomer' => Customer::idCustomer(),
                    ]);
                }
            }

            // Gestion des opportunités
            if (isset($req->opportunite_id)) {
                // Mise à jour d'une opportunité existante
                DB::table('opportunites')
                    ->where('id', $req->opportunite_id)
                    ->update([
                        'idEtp' => $req->idEtp,
                        'id_prospect' => $id_prospect,
                        'idVille' => $req->idVille,
                        'idModule' => $req->idModule,
                        'statut' => $req->statut,
                        'nbPersonne' => $req->nbPersonne,
                        'prix' => $req->prix,
                        'dateDeb' => $req->dateDeb,
                        'dateFin' => $req->dateFin,
                        'ref_name' => $req->ref_name,
                        'ref_firstName' => $req->ref_firstname,
                        'ref_email' => $req->ref_email,
                        'ref_phone' => $req->ref_phone,
                        'source' => $req->source,
                        'note' => $req->note,
                        'idCustomer' => Customer::idCustomer(),
                    ]);
                $message = 'Opportunité mise à jour avec succès.';
            } else {
                // Insertion d'une nouvelle opportunité
                $idOp = DB::table('opportunites')->insertGetId([
                    'idEtp' => $req->idEtp,
                    'id_prospect' => $id_prospect,
                    'idVille' => $req->idVille,
                    'idModule' => $req->idModule,
                    'statut' => $req->statut,
                    'nbPersonne' => $req->nbPersonne,
                    'prix' => $req->prix,
                    'dateDeb' => $req->dateDeb,
                    'dateFin' => $req->dateFin,
                    'ref_name' => $req->ref_name,
                    'ref_firstName' => $req->ref_firstname,
                    'ref_email' => $req->ref_email,
                    'ref_phone' => $req->ref_phone,
                    'source' => $req->source,
                    'note' => $req->note,
                    'idCustomer' => Customer::idCustomer(),
                ]);
                $message = 'Opportunité ajoutée avec succès.';

                $op = DB::table('opportunites AS op')
                    ->select(
                        'op.id AS id_opportunite',
                        'op.statut AS opportunite_id_statut',
                        'op.id_prospect',
                        'op.ref_name',
                        'op.ref_firstname',
                        'op.ref_email AS etp_email',
                        'op.ref_phone AS etp_phone',
                        'op.dateDeb',
                        'op.dateFin',
                        'op.nbPersonne',
                        'op.note',
                        'op.prix',
                        'op.source',
                        'op.idEtp',
                        'op.statut',
                        'op.idVille',
                        'op.idModule',
                        'cst.customerName AS etp_name',
                        'cst.logo AS etp_logo',
                        'md.moduleName AS cours_name',
                        'md.module_image AS cours_img',
                        'ps.prospect_name',
                        'v.ville'
                    )
                    ->leftJoin('customers AS cst', 'op.idEtp', '=', 'cst.idCustomer')
                    ->join('mdls AS md', 'op.idModule', '=', 'md.idModule')
                    ->join('villes AS v', 'op.idVille', '=', 'v.idVille')
                    ->leftJoin('prospects AS ps', 'op.id_prospect', '=', 'ps.id')
                    ->where('op.id', $idOp)
                    ->first();

                $data = $op;

                $refEmailToSend = DB::table('employes AS e')
                    ->select('u.email')
                    ->join('users AS u', 'e.idEmploye', '=', 'u.id')
                    ->join('role_users AS ru', 'e.idEmploye', '=', 'ru.user_id')
                    ->where('e.idCustomer', Customer::idCustomer())
                    ->whereIn('ru.role_id', [3, 8])
                    ->pluck('u.email');

                if ($refEmailToSend->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Aucune adresse e-mail trouvée pour l\'envoi.'
                    ], 422);
                }

                if ($data->idEtp === null) {
                    $etp_name = $data->prospect_name;
                } else {
                    $etp_name = $data->etp_name;
                }

                try {
                    Mail::to($refEmailToSend->toArray())->send(
                        new OpportuniteMail(
                            $etp_name,
                            $data->ville ?? '',
                            $data->etp_email,
                            $data->etp_phone ?? '',
                            $this->formatDate($data->dateDeb),
                            $this->formatDate($data->dateFin),
                            $data->nbPersonne,
                            $data->ref_name ?? '',
                            $data->ref_firstname ?? '',
                            $data->note ?? '',
                            $this->utilService->formatPrice($data->prix) ?? '',
                            $data->source ?? '',
                            $data->statut ?? '',
                            $data->cours_name ?? ''
                        )
                    );
                } catch (\Exception $e) {
                    \Log::error('Erreur lors de l\'envoi de l\'e-mail : ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreur lors de l\'envoi de l\'e-mail.'
                    ], 500);
                }
            }

            DB::commit();

            return response()->json(['success' => $message], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Une erreur est survenue : ' . $e->getMessage()], 500);
        }
    }


    public function show($id)
    {
        $op = DB::table('opportunites AS op')
            ->select(
                'op.id AS id_opportunite',
                'op.statut AS opportunite_id_statut',
                'op.id_prospect',
                'op.ref_name',
                'op.ref_firstname',
                'op.ref_email AS etp_email',
                'op.ref_phone AS etp_phone',
                'op.dateDeb',
                'op.dateFin',
                'op.nbPersonne',
                'op.note AS remarque',
                'op.prix',
                'op.source',
                'op.idEtp',
                'op.statut',
                'op.idVille',
                'op.idModule',
                'cst.customerName AS etp_name',
                'cst.logo AS etp_logo',
                'md.moduleName AS cours_name',
                'md.module_image AS cours_img',
                'ps.prospect_name',
                'v.ville'
            )
            ->leftJoin('customers AS cst', 'op.idEtp', '=', 'cst.idCustomer')
            ->leftjoin('mdls AS md', 'op.idModule', '=', 'md.idModule')
            ->join('villes AS v', 'op.idVille', '=', 'v.idVille')
            ->leftJoin('prospects AS ps', 'op.id_prospect', '=', 'ps.id')
            ->where('op.id', $id)
            ->first();

        $item = [
            'id_opportunite' => $op->id_opportunite,
            'opportunite_id_statut' => $op->opportunite_id_statut,
            'id_prospect' => $op->id_prospect,
            'idVille' => $op->idVille,
            'ville' => $op->ville,
            'idModule' => $op->idModule,
            'ref_name' => $op->ref_name,
            'ref_firstName' => $op->ref_firstname,
            'etp_email' => $op->etp_email,
            'etp_phone' => $op->etp_phone,
            'statut' => $op->statut,
            'etp_logo' => $op->etp_logo ?? null,
            'etp_name' => $op->idEtp ? $op->etp_name : $op->prospect_name,
            'cours_name' => $op->cours_name ?? "",
            'dateDeb' => $this->formatDate($op->dateDeb),
            'dateFin' => $this->formatDate($op->dateFin),
            'op_dateDeb' => $op->dateDeb,
            'op_dateFin' => $op->dateFin,
            'remarque' => $op->remarque,
            'cours_img' => $op->cours_img,
            'source' => $op->source,
            'nbPersonne' => $op->nbPersonne . ' Pers',
            'prix' => $this->utilService->formatPrice($op->prix),
            'idEtp' => $op->idEtp ?? null
        ];

        $detailEncours = view('components.drawer-prospection-detail', ['item' => $item])->render();
        $editEncours = view('components.drawer-prospection-edit', ['opportunite' => $op])->render();
        $opToProject = view('components.op-to-project', ['opportunite' => $item])->render();

        return response()->json([
            'detailEncours' => $detailEncours,
            'editEncours' => $editEncours,
            'opToProject' => $opToProject,
        ]);
    }

    public function delete($id)
    {
        try {
            // Récupérer le prospect_name avant de supprimer l'opportunité
            $opportunite = DB::table('opportunites')->where('id', $id)->first();

            if (!$opportunite) {
                return response()->json(['error' => 'Opportunité introuvable']);
            }

            $idProspect = $opportunite->id_prospect;

            // Supprimer l'opportunité
            DB::table('opportunites')->where('id', $id)->delete();

            // Vérifier si d'autres opportunités existent pour ce prospect_name
            $remainingOpportunites = DB::table('opportunites')
                ->where('id_prospect', $idProspect)
                ->count();

            if ($remainingOpportunites === 0) {
                // Supprimer le prospect s'il n'y a plus d'opportunités associées
                DB::table('prospects')->where('id', $idProspect)->delete();
            }

            return response()->json(['success' => 'Opportunité supprimée avec succès']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }


    public function manageOpportunities(Request $req)
    {
        $query = DB::table('opportunites')->where('id', $req->id);

        if ($query->first()) {
            switch ($req->type) {
                case 'win':
                    $query->update([
                        'opportunitie_is_win' => 1,
                        'opportunitie_is_lost' => 0,
                        'opportunitie_is_standBy' => 0,
                    ]);
                    break;

                case 'lost':
                    $query->update([
                        'opportunitie_is_win' => 0,
                        'opportunitie_is_lost' => 1,
                        'opportunitie_is_standBy' => 0,
                    ]);
                    break;

                case 'standBy':
                    $query->update([
                        'opportunitie_is_win' => 0,
                        'opportunitie_is_lost' => 0,
                        'opportunitie_is_standBy' => 1,
                    ]);
                    break;

                case 'restore':
                    $query->update([
                        'opportunitie_is_win' => 0,
                        'opportunitie_is_lost' => 0,
                        'opportunitie_is_standBy' => 0,
                    ]);
                    break;

                default:
                    break;
            }

            return response(['success' => 'Opération éffectuée avec succès']);
        } else {
            return response(['error' => 'projet introuvable !'], 404);
        }
    }

    public function updateProspect(Request $req)
    {
        $query = DB::table('prospects')->where('id', $req->id);
        $queryOp = DB::table('opportunites')->where('id_prospect', $req->id);

        if ($query && $queryOp) {
            DB::beginTransaction();

            $query->update([
                'prospect_name' => $req->prospect_name,
            ]);

            $queryOp->update([
                'ref_name' => $req->ref_name,
                'ref_firstName' => $req->ref_firstName,
                'ref_email' => $req->ref_email
            ]);

            DB::commit();

            return response()->json(['success' => 'Opportunité supprimer avec succès']);
        } else {
            return response()->json(['error' => 'Une erreur est survenue !']);
        }
    }

    public function getProspect()
    {
        $prospects = DB::table('prospects')->select('id', 'prospect_name')->where('idCustomer', Customer::idCustomer())->get();

        return response()->json([
            'prospects' => $prospects
        ]);
    }

    public function saveOrder(Request $request)
    {
        $lists = $request->input('lists');

        foreach ($lists as $item) {
            DB::table('opportunites')
                ->where('id', $item['id'])
                ->update([
                    'statut' => $this->getStatusFromListId($item['list_id']),
                    'position' => $item['position'],
                ]);
        }

        return response()->json(['success' => 'Order saved successfully!']);
    }

    public function getEtpSelected($idEtp)
    {
        $etp = DB::table('users')
            ->select('id', 'name', 'email', 'firstName', 'phone')
            ->where('id', $idEtp)
            ->first();

        return response()->json(['etp' => $etp]);
    }

    public function etpAssignOp(Request $req)
    {
        $idProjet = $req->idProjet;
        $idEtp = $req->idEtp;
        $idCfp_inter = DB::table('v_projet_cfps')
            ->where('idProjet', $idProjet)
            ->pluck('idCfp_inter')
            ->first();

        $refEmailToSend = DB::table('employes AS e')
            ->select('u.email')
            ->join('users AS u', 'e.idEmploye', '=', 'u.id')
            ->join('role_users AS ru', 'e.idEmploye', '=', 'ru.user_id')
            ->where('e.idCustomer', Customer::idCustomer())
            ->whereIn('ru.role_id', [3, 8])
            ->pluck('u.email');

        if ($refEmailToSend->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune adresse e-mail trouvée pour l\'envoi.'
            ], 422);
        }

        $cfp = DB::table('customers')->select('idCustomer', 'customerName AS customer_name', 'customer_addr_lot AS customerAdress')->where('idCustomer', Customer::idCustomer())->first();

        if (isset($idEtp) && !isset($idCfp_inter)) {
            try {
                DB::beginTransaction();
                DB::table('projets')
                    ->join('intras', 'intras.idProjet', 'projets.idProjet')
                    ->where('projets.idProjet', $idProjet)
                    ->update(['idEtp' => $idEtp]);

                Mail::to($refEmailToSend->toArray())->send(new WinOpMail($req->etp_name, $idProjet, $req->dateDeb, $req->dateFin, $req->ville));
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json(['error' => $e->getMessage()]);
            }
        } elseif (isset($idEtp) && isset($idCfp_inter)) {
            try {
                DB::beginTransaction();
                DB::table('inter_entreprises')->insert([
                    'idProjet' => $idProjet,
                    'idEtp' => $idEtp,
                ]);

                Mail::to($refEmailToSend->toArray())->send(new WinOpMail($req->etp_name, $idProjet, $req->dateDeb, $req->dateFin, $req->ville));
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json(['error' => $e->getMessage()]);
            }
        } else {
            if (!isset($idCfp_inter)) {
                try {
                    DB::beginTransaction();
                    $user = new User();
                    $user->name = $req->etp_referent_name;
                    $user->firstName = $req->etp_referent_firstname;
                    $user->email = $req->etp_email;
                    $user->password = Hash::make('1234@#');
                    $user->save();

                    DB::table('customers')->insert([
                        'idCustomer'    => $user->id,
                        'customerName'  => $req->etp_name,
                        'nif'           => $req->etp_rcs,
                        'customerEmail' => $req->etp_email,
                        'idSecteur'     => 7,
                        'idTypeCustomer' => 2,
                        'idVilleCoded' => 1
                    ]);

                    DB::table('entreprises')->insert([
                        'idCustomer' => $user->id,
                        'idTypeEtp' => 1
                    ]);
                    DB::table('etp_singles')->insert(['idEntreprise' => $user->id]);

                    $customer = DB::table('customers')->select('idCustomer')->orderBy('idCustomer', 'desc')->first();

                    $idFonction = DB::table('fonctions')->insertGetId([
                        'fonction' => "default_fonction",
                        'idCustomer' => $user->id
                    ]);

                    DB::table('employes')->insert([
                        'idEmploye'     => $user->id,
                        'idCustomer'    => $customer->idCustomer,
                        'idSexe'        => 1,
                        'idNiveau'      => 1,
                        'idFonction'    => $idFonction
                    ]);

                    DB::table('role_users')->insert([
                        'role_id'   => 6,
                        'user_id'   => $user->id,
                        'hasRole'   => 1,
                        'isActive'  => 1
                    ]);

                    DB::table('cfp_etps')->insert([
                        'idEtp' => $user->id,
                        'idCfp' => Customer::idCustomer(),
                        'dateCollaboration' => Carbon::now(),
                        'activiteEtp' => 0,
                        'activiteCfp' => 1,
                        'isSent' => 1
                    ]);

                    $checkProspect = DB::table('prospects')
                        ->select('prospect_name', 'id')
                        ->where('idCustomer', Customer::idCustomer())
                        ->where('prospect_name', $req->etp_name)
                        ->first();

                    if (isset($checkProspect)) {
                        // Mise à jour des opportunités associées
                        $opportunitesUpdated = DB::table('opportunites')
                            ->where('id_prospect', '=', $checkProspect->id)
                            ->update([
                                'id_prospect' => null,
                                'idEtp' => $user->id,
                            ]);

                        if ($opportunitesUpdated) {
                            // Suppression du prospect
                            DB::table('prospects')->where('prospect_name', $req->etp_name)->delete();
                        }
                    }

                    Mail::to($req->etp_email)->send(new RequestCustomer($cfp));
                    Mail::to($refEmailToSend->toArray())->send(new WinOpMail($req->etp_name, $idProjet, $req->dateDeb, $req->dateFin, $req->ville));
                    DB::commit();

                    DB::table('projets')
                        ->join('intras', 'intras.idProjet', 'projets.idProjet')
                        ->where('projets.idProjet', $idProjet)
                        ->update(['idEtp' => $user->id]);

                    return response()->json(['success' => 'Invitation envoyée avec succès']);
                } catch (Exception $e) {
                    DB::rollBack();
                    return response()->json(['error' => $e->getMessage()]);
                }
            } else {
                try {
                    DB::beginTransaction();
                    $user = new User();
                    $user->name = $req->etp_referent_name;
                    $user->firstName = $req->etp_referent_firstname;
                    $user->email = $req->etp_email;
                    $user->password = Hash::make('1234@#');
                    $user->save();

                    DB::table('customers')->insert([
                        'idCustomer'    => $user->id,
                        'customerName'  => $req->etp_name,
                        'nif'           => $req->etp_rcs,
                        'customerEmail' => $req->etp_email,
                        'idSecteur'     => 7,
                        'idTypeCustomer' => 2,
                        'idVilleCoded' => 1
                    ]);

                    DB::table('entreprises')->insert([
                        'idCustomer' => $user->id,
                        'idTypeEtp' => 1
                    ]);
                    DB::table('etp_singles')->insert(['idEntreprise' => $user->id]);

                    $customer = DB::table('customers')->select('idCustomer')->orderBy('idCustomer', 'desc')->first();

                    $idFonction = DB::table('fonctions')->insertGetId([
                        'fonction' => "default_fonction",
                        'idCustomer' => $user->id
                    ]);

                    DB::table('employes')->insert([
                        'idEmploye'     => $user->id,
                        'idCustomer'    => $customer->idCustomer,
                        'idSexe'        => 1,
                        'idNiveau'      => 1,
                        'idFonction'    => $idFonction
                    ]);

                    DB::table('role_users')->insert([
                        'role_id'   => 6,
                        'user_id'   => $user->id,
                        'hasRole'   => 1,
                        'isActive'  => 1
                    ]);

                    DB::table('cfp_etps')->insert([
                        'idEtp' => $user->id,
                        'idCfp' => Customer::idCustomer(),
                        'dateCollaboration' => Carbon::now(),
                        'activiteEtp' => 0,
                        'activiteCfp' => 1,
                        'isSent' => 1
                    ]);

                    $checkProspect = DB::table('prospects')
                        ->select('prospect_name', 'id')
                        ->where('idCustomer', Customer::idCustomer())
                        ->where('prospect_name', $req->etp_name)
                        ->first();

                    if (isset($checkProspect)) {
                        // Mise à jour des opportunités associées
                        $opportunitesUpdated = DB::table('opportunites')
                            ->where('id_prospect', '=', $checkProspect->id)
                            ->update([
                                'id_prospect' => null,
                                'idEtp' => $user->id,
                            ]);

                        if ($opportunitesUpdated) {
                            // Suppression du prospect
                            DB::table('prospects')->where('prospect_name', $req->etp_name)->delete();
                        }
                    }

                    Mail::to($req->etp_email)->send(new RequestCustomer($cfp));
                    Mail::to($refEmailToSend->toArray())->send(new WinOpMail($req->etp_name, $idProjet, $req->dateDeb, $req->dateFin, $req->ville));
                    DB::commit();

                    DB::table('inter_entreprises')->insert([
                        'idProjet' => $idProjet,
                        'idEtp' =>  $user->id,
                    ]);

                    return response()->json(['success' => 'Invitation envoyée avec succès']);
                } catch (Exception $e) {
                    DB::rollBack();
                    return response()->json(['error' => $e->getMessage()]);
                }
            }
        }
    }

    // Convertit l'ID de liste HTML en statut (vous pouvez adapter cette logique)
    private function getStatusFromListId($listId)
    {
        return match ($listId) {
            'ul_1' => 1,
            'ul_2' => 2,
            'ul_3' => 3,
            'ul_4' => 4,
            'ul_5' => 5,
            default => null
        };
    }

    private function formatDate($date)
    {
        return Carbon::parse($date)->locale('fr')->translatedFormat('j M y');
    }

    private function getStatus($idStatut)
    {
        return match ($idStatut) {
            1 => 'Identifications',
            2 => 'Offres',
            3 => 'Rendez-vous',
            4 => 'Négociations',
            5 => 'Pré-réservations',
            default => null,
        };
    }

    private function getColorStatus($idStatut)
    {
        return match ($idStatut) {
            1 => '#4056F4',
            2 => '#E42548',
            3 => '#CB9801',
            4 => '#126936',
            5 => '#041925',
            default => null,
        };
    }

    public function getEvents()
    {
        // Récupérer les opportunités
        $opportunities = DB::table('opportunites AS op')
            ->select(
                'op.id AS id_opportunite',
                'op.statut AS opportunite_id_statut',
                'op.id_google_opportunite AS idCalendar',
                'op.ref_name',
                'op.ref_firstname',
                'op.ref_email AS etp_email',
                'op.ref_phone AS etp_phone',
                'op.dateDeb',
                'op.dateFin',
                'op.nbPersonne',
                'op.note AS remarque',
                'op.prix',
                'op.idEtp',
                'cst.customerName AS etp_name',
                'cst.logo AS etp_logo',
                'md.moduleName AS cours_name',
                'md.module_image AS cours_img',
                'ps.prospect_name'
            )
            ->where('op.idCustomer', Customer::idCustomer())
            ->leftJoin('customers AS cst', 'op.idEtp', '=', 'cst.idCustomer')
            ->join('mdls AS md', 'op.idModule', '=', 'md.idModule')
            ->leftJoin('prospects AS ps', 'op.id_prospect', '=', 'ps.id')
            // ->where('opportunitie_is_win', 0)
            // ->where('opportunitie_is_lost', 0)
            // ->where('opportunitie_is_standBy', 0)
            ->orderBy('op.dateDeb', 'asc')
            ->get();

        if (count($opportunities) > 0) {
            foreach ($opportunities as $opportunity) {

                $events[] =  [
                    'idOpportunity' => $opportunity->id_opportunite,
                    'idCfp' => Customer::idCustomer(),
                    'idCalendar' => $opportunity->idCalendar, //id reliant à Google calendar
                    'start' => $opportunity->dateDeb,
                    'end' => $opportunity->dateFin,
                    'etp_logo' => $opportunity->etp_logo ?? null,
                    'etp_name' => $opportunity->idEtp ? $opportunity->etp_name : $opportunity->prospect_name,
                    'ref_name' => $opportunity->ref_name,
                    'ref_firstName' => $opportunity->ref_firstname,
                    'etp_email' => $opportunity->etp_email,
                    'etp_phone' => $opportunity->etp_phone,
                    'idStatut' => $opportunity->opportunite_id_statut,
                    'imgModule' => $opportunity->cours_img,
                    'text' => $opportunity->remarque,
                    'status' => $this->getStatus($opportunity->opportunite_id_statut),
                    'module' => $opportunity->cours_name ?? "",
                    'nbPersonne' => $opportunity->nbPersonne,
                    'prix' => $this->utilService->formatPrice($opportunity->prix),
                    'barColor' => $this->getColorStatus($opportunity->opportunite_id_statut),
                    'barBackColor' => $this->getColorStatus($opportunity->opportunite_id_statut),
                    'backColor' => $opportunity->idCalendar ? "rgba(204, 229, 244, 0.63)" : "rgba(246, 241, 216, 0.5)",
                ];
            }

            return response()->json([
                'status' => 200,
                'seances' => $events
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Aucun résultat !'
            ], 404);
        }
    }

    public function updateIdCalendarOpportunity(Request $req, $id)
    {

        // $isNotNullIdOpportunity = DB::table('opportunites')->where('id',$id)
        //     ->where('id_google_opportunite',null)

        $result = DB::table('opportunites')
            ->where('id', $id)
            ->first();
        if ($result->id_google_opportunite == null) {
            $update = DB::table('opportunites')
                ->where('id', $id)
                ->update([
                    'id_google_opportunite' => $req->idCalendar
                ]);
        } else {
            $update = DB::table('opportunites')
                ->where('id', $id)
                ->update([
                    'id_google_opportunite' => null
                ]);
        }
        return response()->json([
            "success" => "Succès",
            "idCalendar" => $req->idCalendar,
            "update" => $update,
        ]);
    }
}
