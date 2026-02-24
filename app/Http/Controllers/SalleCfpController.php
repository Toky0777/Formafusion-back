<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Salle;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

use function Laravel\Prompts\select;

class SalleCfpController extends Controller
{
    public function idCustomer()
    {
        $customer = DB::select("SELECT employes.idEmploye, employes.idCustomer, customers.idTypeCustomer FROM employes INNER JOIN customers ON employes.idCustomer = customers.idCustomer WHERE idEmploye = ?", [Auth::user()->id]);
        return $customer[0]->idCustomer;
    }

    public function list()
    {
        $idCfp = Customer::idCustomer();
        $salles = DB::table('v_liste_lieux as vll')
            ->select(
                'salles.idSalle',
                'salles.salle_name',
                'lieux.li_quartier',
                'lieux.li_rue',
                'vll.vi_code_postal as salle_code_postal',
                'vll.ville'
            )
            ->leftJoin('salles', 'salles.idLieu', 'vll.idLieu')
            ->join('lieux', 'lieux.idLieu', 'vll.idLieu')
            ->leftJoin('lieu_privates as lpv', 'lpv.idLieu', '=', 'vll.idLieu')
            ->leftJoin('place_etp_from_cfps as pefc', 'pefc.idLieu', '=', 'vll.idLieu')
            ->where(function ($query) use ($idCfp) {
                $query->where('lpv.idCustomer', $idCfp)
                    ->orWhereNull('lpv.idCustomer');
            })
            ->where(function ($query) use ($idCfp) {
                $query->where('pefc.idCfp', $idCfp)
                    ->orWhereNull('pefc.idCfp');
            })
            ->where('salles.salle_name', '!=', 'null')
            ->orderBy('salles.salle_name', 'asc')
            ->get();

        $events = [];
        foreach ($salles as $salle) {
            $events[] =  [
                'id' => $salle->idSalle,
                'name' => $salle->salle_name
            ];
        }
        return response()->json([
            'salles' => $events
        ]);
    }

    public function index()
    {
        $villes = DB::table('villes')->select('idVille', 'ville')->get();

        $salles = DB::table('salles')
            ->select('*')
            ->orderBy('salle_name', 'asc')
            ->get();

        return view('CFP.salles.index', compact(['villes', 'salles']));
    }

    public function loadVille()
    {
        $villes = DB::table('villes')->select('idVille', 'ville')->get();

        return response()->json(['villes' => $villes]);
    }

    public function getAllSalle($idEtp)
    {
        $idCfp = Customer::idCustomer();

        $salles = DB::table('v_liste_lieux as vll')
            ->select(
                'salles.idSalle',
                'salles.salle_name',
                'lieux.li_quartier as salle_quartier',
                'lieux.li_rue as salle_rue',
                'vll.vi_code_postal as salle_code_postal',
                'vll.ville_name_coded as ville',
                'lieux.li_name as lieux_name',
                'customers.customerName',
                'cc.customerName as cfpName',
            )
            ->leftJoin('salles', 'salles.idLieu', 'vll.idLieu')
            ->join('lieux', 'lieux.idLieu', 'vll.idLieu')
            ->leftJoin('lieu_privates as lpv', 'lpv.idLieu', '=', 'vll.idLieu')
            ->leftJoin('place_etp_from_cfps as pefc', 'pefc.idLieu', '=', 'vll.idLieu')
            ->leftJoin('customers', 'customers.idCustomer', 'pefc.idEntreprise')
            ->leftJoin('customers as cc', 'cc.idCustomer', 'lpv.idCustomer')
            ->where(function ($query) use ($idCfp) {
                $query->where('lpv.idCustomer', $idCfp)
                    ->orWhereNull('lpv.idCustomer');
            })
            ->where(function ($query) use ($idEtp) {
                $query->where('pefc.idEntreprise', $idEtp)
                    ->orWhereNull('pefc.idEntreprise');
            })
            ->where(function ($query) use ($idCfp) {
                $query->where('pefc.idCfp', $idCfp)
                    ->orWhereNull('pefc.idCfp');
            })
            ->where('salles.salle_name', '!=', 'null')
            ->orderBy('salles.salle_name', 'asc')
            ->get();

        return response()->json(['salles' => $salles]);
    }

    public function edit($idSalle)
    {
        $villes = DB::table('villes')->select('idVille', 'ville')->get();

        $salle = DB::table('villes')
            ->join('salles', 'salles.idVille', 'villes.idVille')
            ->select('idSalle', 'salle_name', 'salle_quartier', 'salle_rue', 'salle_code_postal', 'salle_image', 'villes.ville', 'salles.idVille')
            ->where('idSalle', $idSalle)
            ->first();

        return response()->json([
            'villes' => $villes,
            'salle' => $salle
        ]);
    }

    public function update(Request $req, $idSalle)
    {
        $driver = new Driver();

        $manager = new ImageManager($driver);

        $validate = Validator::make($req->all(), [
            'salle_name' => 'required|max:100|min:3',
            'idVille' => 'required|exists:villes,idVille',
            'salle_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validate->fails()) {
            return response()->json([['error' => $validate->messages()]]);
        } else {
            $salle = DB::table('salles')->where('idSalle', $idSalle)->first();
            if (!$salle) {
                return response()->json([
                    "error" => "Salle non trouvée !"
                ]);
            }

            if ($req->salle_image == null) {
                $update = DB::table('salles')->where('idSalle', $idSalle)->update([
                    'salle_name' => $req->salle_name,
                    'salle_quartier' => $req->salle_quartier,
                    'salle_rue' => $req->salle_rue,
                    'salle_code_postal' => $req->salle_code_postal,
                    'idVille' => $req->idVille
                ]);
            } else {
                if (!empty($salle->salle_image)) {
                    Storage::disk('do')->delete('img/salles/' . $salle->salle_image);
                }

                $file = $req->file('salle_image');
                $image = $manager->read($file)->toWebp(25);
                $imageName = $file->getClientOriginalName();
                $filePath = 'img/salles/' . $imageName;

                Storage::disk('do')->put($filePath, file_get_contents($image));

                $update = DB::table('salles')->where('idSalle', $idSalle)->update([
                    'salle_name' => $req->salle_name,
                    'salle_quartier' => $req->salle_quartier,
                    'salle_rue' => $req->salle_rue,
                    'salle_code_postal' => $req->salle_code_postal,
                    'idVille' => $req->idVille,
                    'salle_image' => $imageName
                ]);
            }

            if ($update !== false) {
                return response()->json([
                    "success" => "Succès"
                ]);
            } else {
                return response()->json([
                    "error" => "Erreur inconnue !",
                ]);
            }
        }
    }

    public function getSalleDetails($idLieu, $vi_code_postal)
    {
        $sallesData = DB::table('salles')
            ->join('lieux', 'salles.idLieu', '=', 'lieux.idLieu')
            ->join('ville_codeds', 'lieux.idVille', '=', 'ville_codeds.idVille')
            ->join('lieu_types', 'lieu_types.idLieuType', '=', 'lieux.idLieuType')
            ->select(
                'salles.salle_name',
                'salles.salle_quartier',
                'salles.salle_rue',
                'ville_codeds.ville_name',
                'ville_codeds.vi_code_postal',
                'salles.salle_image',
                'lieux.idLieuType',
                'lieux.li_name',
                'salles.idSalle',
                'lieu_types.lt_name'
            )
            ->where('ville_codeds.vi_code_postal', $vi_code_postal)
            ->where('salles.idLieu', $idLieu)
            ->get();

        // Vérification si des données sont récupérées
        if ($sallesData->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Aucune salle trouvée pour ce lieu']);
        }

        // Construction de l'URL pour l'image
        $sallesData = $sallesData->map(function ($salle) {
            $salle->image_url = url('path/to/images/' . $salle->salle_image);
            return $salle;
        });

        return response()->json([
            'success' => true,
            'salles' => $sallesData
        ]);
    }
}
