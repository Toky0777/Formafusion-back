<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\EntrepriseService;
use App\Traits\GetQuery;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\Response;

class CfpInviteEtp extends Controller
{
    use GetQuery;

    public function idCfp()
    {
        $customer = DB::select("SELECT employes.idEmploye, employes.idCustomer, customers.idTypeCustomer FROM employes INNER JOIN customers ON employes.idCustomer = customers.idCustomer WHERE idEmploye = ?", [Auth::user()->id]);
        return $customer[0]->idCustomer;
    }

    public function index(EntrepriseService $etp, $idTypeEtp)
    {
        $url = 'cfp/invites/etp/list/';
        if (in_array($idTypeEtp, [1, 2, 4, 5, 6, 7])) {
            switch ($idTypeEtp) {
                case 1:
                    $allEtps = $etp->getAllEnterprises(Customer::idCustomer(), 1);
                    break;
                case 2:
                    $allEtps = $etp->getAllEnterprises(Customer::idCustomer(), 2);
                    break;
                case 4:
                    $allEtps = $etp->getAllEnterprises(Customer::idCustomer(), 4);
                    break;
                case 5:
                    $allEtps = $etp->getAllEnterprises(Customer::idCustomer(), 5);
                    break;
                case 6:
                    $allEtps = $etp->getAllEnterprises(Customer::idCustomer(), 6);
                    break;
                case 7:
                    $allEtps = $etp->getAllEnterprises(Customer::idCustomer(), 7);
                    break;
            }
        } else {
            abort(404);
        }

        // filtre par lettre
        $filters = $etp->letterFilterEnterprises($allEtps);
        $filteredEtps = $filters['filteredEtps'];
        $firstLetter = $filters['firstLetter'];
        $enabledLetters = $filters['enabledLetters'];

        $ville_codeds = $this->getVilleCodeds();
        $typeEntreprises = $this->getTypeEntreprise()->whereIn('idTypeEtp', [1, 4, 5, 6, 7])->get();

        return view('CFP.collaborations.index', compact('allEtps', 'filteredEtps', 'firstLetter', 'enabledLetters', 'ville_codeds', 'typeEntreprises', 'url'));
    }

    public function searchName(string $name)
    {
        $etps = DB::table('v_collaboration_cfp_etps')
            ->select('etp_initial_name', 'etp_name', 'etp_logo', 'etp_description', 'etp_phone', 'etp_addr_lot', 'etp_site_web', 'etp_email', 'idEtp', 'idCfp', 'activiteCfp', 'activiteEtp', 'dateInvitation', 'etp_referent_name', 'etp_referent_firstname', 'etp_referent_fonction', 'etp_referent_phone')
            ->where('idCfp', $this->idCfp())
            ->where('etp_name', 'like', '%' . $name . '%')
            ->get();
        return response()->json(['etps' => $etps]);
    }

    public function getAllEtps()
    {
        $etps = DB::table('v_collaboration_cfp_etps')
            ->select('idEtp', 'etp_name', 'etp_email', 'etp_initial_name', 'etp_logo')
            ->where('idCfp', Customer::idCustomer())
            ->orderBy('etp_name', 'asc')
            ->get();

        return response()->json(['etps' => $etps]);
    }

    public function getAllEtpsByKey(Request $request)
    {
        $etps = DB::table('v_collaboration_cfp_etps')
            ->select('idEtp', 'etp_name', 'etp_email', 'etp_initial_name', 'etp_logo')
            ->where('idCfp', Customer::idCustomer());

        if ($request->key) {
            $etps->where('etp_name', 'like', "%{$request->key}%");
        }
        $etps = $etps->orderBy('etp_name', 'asc')->get();

        return response()->json(['etps' => $etps]);
    }


    public function getAllFrais()
    {
        $frais = DB::table('frais')
            ->select('idFrais', 'Frais', 'exemple')
            ->get();

        return response()->json(['frais' => $frais]);
    }

    public function edit($idEtp)
    {
        $etp = DB::table('v_collaboration_cfp_etps')
            ->select('idEtp', 'etp_name', 'etp_email', 'etp_initial_name', 'etp_nif', 'etp_stat', 'etp_rcs', 'etp_addr_lot', 'etp_addr_quartier', 'etp_addr_code_postal', 'etp_phone', 'etp_logo', 'etp_referent_name', 'etp_referent_firstname', 'etp_referent_fonction', 'idTypeEtp')
            ->where('idEtp', $idEtp)
            ->first();

        // dd($etp);

        $villeCodedEtp = DB::table('customers')
            ->select('idVilleCoded')
            ->where('idCustomer', $idEtp)
            ->first();

        $villeCoded = $villeCodedEtp->idVilleCoded ?? 0;

        return response()->json(['etp' => $etp, 'villeCoded' => $villeCoded]);
    }


    public function update(Request $req, $idEtp)
    {

        if (in_array($req->idTypeEtp, [1, 4, 5, 6, 7])) {
            $validate = Validator::make($req->all(), [
                'etp_name' => 'required|min:2|max:200',
                'etp_email' => 'required|email',
                'idTypeEtp' => 'required|exists:type_entreprises,idTypeEtp'
            ]);
        } else {
            $validate = Validator::make($req->all(), [
                'etp_name' => 'required|min:2|max:200',
                'etp_email' => 'required|email'
            ]);
        }

        if ($validate->fails()) {
            return response()->json(['error' => $validate->messages()]);
        } else {
            try {
                DB::beginTransaction();
                DB::table('customers')
                    ->join('users', 'users.id', 'customers.idCustomer')
                    ->where(function ($query) use ($idEtp) {
                        $query->where('customers.idCustomer', $idEtp)
                            ->where('users.id', $idEtp);
                    })
                    ->update([
                        'customers.nif' => $req->etp_nif,
                        'customers.stat' => $req->etp_stat,
                        'customers.rcs' => $req->etp_rcs,
                        'customers.customerName' => $req->etp_name,
                        'customers.customerPhone' => $req->etp_phone,
                        'customers.customerEmail' => $req->etp_email,
                        'customers.customer_addr_lot' => $req->etp_addr_lot,
                        'customers.customer_addr_quartier' => $req->etp_addr_quartier,
                        // 'customers.customer_addr_code_postal' => $req->etp_addr_code_postal,
                        'customers.idVilleCoded' => $req->etp_ville_id,
                        'users.name' => $req->etp_referent_name,
                        'users.firstName' => $req->etp_referent_firstname,
                        'users.email' => $req->etp_email
                    ]);

                if (in_array($req->idTypeEtp, [1, 4, 5, 6, 7])) {
                    DB::table('entreprises')->where('idCustomer', $idEtp)->update(['idTypeEtp' => $req->idTypeEtp]);
                }

                DB::commit();

                return response()->json(['success' => 'Opération effectuée avec succès']);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json(['error' => $e->getMessage()]);
            }
        }
    }

    public function updateLogo(Request $req, $idEtp)
    {
        $driver = new Driver();

        $manager = new ImageManager($driver);

        $etp = DB::table('customers')->select('logo')->where('idCustomer', $idEtp)->first();

        if ($etp != null) {
            if (!empty($etp->logo)) {
                Storage::disk('do')->delete('img/entreprises/' . $etp->logo);
            }

            $image_parts = explode(";base64,", $req->image);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $image = $manager->read($image_base64)->toWebp(25);

            $imageName = uniqid() . '.webp';
            $filePath = 'img/entreprises/' . $imageName;

            // Upload the image to DigitalOcean Space
            Storage::disk('do')->put($filePath, $image, 'public');

            // Update the database with the new image name
            DB::table('customers')->where('idCustomer', $idEtp)->update([
                'logo' => $imageName,
            ]);
            return response()->json([
                'success' => 'Image Uploaded Successfully',
                'imageName' =>  $imageName
            ]);
        }
    }

    // suppression client via CFP
    public function destroy($id)
    {
        $query = DB::table('intras')->where('idEtp', $id);
        $queryInter = DB::table('inter_entreprises')->where('idEtp', $id);

        if ($query->count() > 0 || $queryInter->count() > 0) {
            $messages = [
                'status' => 401,
                'message' => "Suppression impossible ! Ce client est déjà associé à un projet.",
            ];
            return response(['error' => 'Suppression impossible !', 'messages' => $messages]);
        } else {
            $messages = [
                'status' => 200,
                'message' => "Client supprimé avec succès",
            ];
            DB::table('cfp_etps')->where('idEtp', $id)->where('idCfp', Customer::idCustomer())->delete();

            return response(['success' => 'Client supprimé avec succès', 'messages' => $messages]);
        }
    }
}
