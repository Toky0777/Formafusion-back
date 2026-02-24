<?php

namespace App\Services;

use App\Http\Requests\TestRequest;
use App\Interfaces\EntrepriseInterface;
use App\Interfaces\InvitationInterface;
use App\Mail\RequestCustomer;
use App\Models\Customer;
use App\Models\User;
use App\Traits\GetQuery;
use App\Traits\StoreQuery;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class EntrepriseService implements EntrepriseInterface, InvitationInterface
{
    use GetQuery, StoreQuery;

    public function index($idCfp): mixed
    {
        $query = DB::table('v_collaboration_cfp_etps')
            ->select('idEtp', 'etp_name', 'etp_logo', 'etp_nif', 'etp_stat', 'etp_description', 'etp_phone', 'etp_addr_quartier', 'etp_site_web', 'etp_ville', 'etp_addr_lot', 'etp_email', 'etp_referent_name', 'etp_referent_firstname', 'etp_referent_phone', 'idTypeEtp', 'type_etp_desc')
            ->where('idCfp', $idCfp)
            ->orderBy('etp_name', 'ASC');

        return $query;
    }

    // Listes des ETP pour FORMATEUR entre (7 jours avant debut formation et 3 jours apès formation)
    public function getEntrepriseForm($idFormateur): array
    {
        $etps = $this->getEntreprises($idFormateur);

        return $etps;
    }

    // invitation d'une Entreprise par une tièrces personne
    public function inviteCustomer($req): mixed
    {
        $customerEmail = Customer::getCustomer(Customer::idCustomer())->customer_email;

        return response([
            'cust' => $customerEmail
        ]);

        $validation = Validator::make($req->all(), [
            'customer_name' => 'required|min:2|max:150',
            'customer_email' => 'required|email|exists:customers,customerEmail|different:' . $customerEmail
        ]);

        if ($validation->fails()) {
            return response([
                'status' => 422,
                'message' => $validation->messages()
            ]);
        } else {
            $checkCfp = DB::table('customers')
                ->select('customerEmail AS email')
                ->where('idCustomer', $this->idCfp())
                ->first();

            $checkEtp = DB::table('customers')
                ->select('customerEmail AS email')
                ->where('nif', $req->etp_rcs)
                ->where('customerEmail', $req->etp_email)
                ->first();

            $cfp = DB::table('customers')->select('idCustomer', 'customerName', 'customer_addr_lot AS customerAdress')->where('idCustomer', $this->idCfp())->first();

            if ($checkRcs <= 0 && $checkMail <= 0 && $checkCfp->email != $req->etp_email) {
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

                    $idModule = DB::table('mdls')->insertGetId([
                        'moduleName' => "Default module",
                        'idDomaine' => 1,
                        'idCustomer' => $user->id,
                        'idTypeModule' => 2
                    ]);

                    DB::table('module_internes')->insert(['idModule' => $idModule]);

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
                        'idCfp' => $this->idCfp(),
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
                    DB::commit();

                    return response()->json(['success' => 'Invitation envoyée avec succès']);
                } catch (Exception $e) {
                    DB::rollBack();
                    return response()->json(['error' => $e->getMessage()]);
                }
            } elseif ($checkRcs >= 1 && $req->etp_email == $checkEtp->email) {
                $req->validate(['idEtp' => 'required|exists:customers,idCustomer']);

                $isEtp = DB::table('users')
                    ->join('customers', 'customers.idCustomer', 'users.id')
                    ->select('email', 'idTypeCustomer')
                    ->where('email', $req->etp_email)
                    ->first();

                $isCollaborated = DB::table('cfp_etps')
                    ->select('idEtp', 'idCfp')
                    ->where('idCfp', $this->idCfp())
                    ->where('idEtp', $req->idEtp)
                    ->count('idEtp', 'idCfp');

                if ($isEtp->idTypeCustomer == 2 && $isCollaborated <= 0) {
                    try {
                        DB::beginTransaction();

                        DB::table('cfp_etps')->insert([
                            'idEtp' => $req->idEtp,
                            'idCfp' => $this->idCfp(),
                            'dateCollaboration' => Carbon::now(),
                            'activiteEtp' => 0,
                            'activiteCfp' => 1,
                            'isSent' => 1
                        ]);

                        Mail::to($req->etp_email)->send(new RequestCustomer($cfp));
                        DB::commit();

                        return response()->json(['success' => 'Invitation envoyée avec succès']);
                    } catch (Exception $e) {
                        DB::rollBack();
                        return response()->json(['error' => $e->getMessage()]);
                    }
                } else {
                    return response()->json(['error' => 'Erreur inconnue, Veuillez verifier votre invitation !']);
                }
            } else {
                return response()->json(['error' => "Mail existant, veuillez vérifier vos données !"]);
            }
        }
    }

    // ajout + invitation d'une Entreprise par une tièrce personne
    public function inviteNewCustomer($req): mixed
    {
        return true;
    }

    // récuperation customerName à partir de la recherche par nom pdt l'invitation
    public function getCustomerName($name): array
    {
        $customers = DB::table('customers AS c')
            ->select('c.idCustomer', 'c.idTypeCustomer', 'c.customerName AS customer_name', 'c.customerEmail AS customer_email', 'typeCustomer as customer_type', 'nif as customer_nif', 'customer_addr_lot', 'type_etp_desc as type_customer_desc', 'e.idTypeEtp')
            ->join('type_customers as tc', 'c.idTypeCustomer', 'tc.idTypeCustomer')
            ->join('entreprises as e', 'c.idCustomer', 'e.idCustomer')
            ->join('type_entreprises as te', 'e.idTypeEtp', 'te.idTypeEtp')
            ->leftJoin('cfp_etps', 'cfp_etps.idEtp', 'c.idCustomer')
            ->where('c.idTypeCustomer', 2)
            // ->whereNull('cfp_etps.isSent')
            ->where('c.customerName', 'like', $name . '%')
            ->groupBy('c.idCustomer', 'c.idTypeCustomer', 'c.customerName', 'c.customerEmail', 'typeCustomer', 'nif', 'customer_addr_lot')
            ->get();

        return $customers->toArray();
    }

    public function edit($idCfp, $idEtp): mixed
    {
        $etp = $this->index($idCfp)->where('idEtp', $idEtp);

        return $etp;
    }

    // récupération de toutes les ENTREPRISES en collaboration avec un CFP(Clients dans CFP)
    public function getAllEnterprises($idCfp, $idTypeEtp): mixed
    {
        $entreprises = DB::table('v_collaboration_cfp_etps')
            ->select('idEtp', 'etp_initial_name', 'etp_name', 'etp_logo', 'etp_description', 'etp_phone', 'etp_addr_lot', 'etp_site_web', 'etp_email', 'idCfp', 'activiteCfp', 'activiteEtp', 'dateInvitation', 'etp_referent_name', 'etp_referent_firstname', 'etp_referent_phone', 'idTypeEtp', 'type_etp_desc')
            ->where('idCfp', $idCfp)
            ->where('idTypeEtp', $idTypeEtp)
            ->orderBy('etp_name', 'ASC')
            ->get();

        return $entreprises;
    }

    // filtre par lettre alphabétique des entreprises
    public function letterFilterEnterprises($tableCollections): mixed
    {
        $enabledLetters = [];

        // Générer les lettres activées
        foreach (range('A', 'Z') as $letter) {
            if ($tableCollections->first(fn($etp) => strtoupper(substr($etp->etp_name, 0, 1)) === $letter)) {
                $enabledLetters[] = $letter;
            }
        }

        // Ajouter 0-9 si nécessaire
        if ($tableCollections->first(fn($etp) => preg_match('/^[0-9]/', $etp->etp_name))) {
            $enabledLetters[] = '0-9';
        }

        // Déterminer la première lettre valide
        $firstLetter = $enabledLetters[0] ?? null;

        // Filtrer les entreprises par la première lettre valide
        $filteredEtps = $tableCollections->filter(function ($etp) use ($firstLetter) {
            if ($firstLetter === '0-9') {
                return preg_match('/^[0-9]/', $etp->etp_name);
            }
            return strtoupper(substr($etp->etp_name, 0, 1)) === $firstLetter;
        });

        return [
            'firstLetter' => $firstLetter,
            'filteredEtps' => $filteredEtps,
            'enabledLetters' => $enabledLetters
        ];
    }

    public function getEnterpriseType($idEtp): mixed
    {
        $type = DB::table('entreprises')
            ->select('idCustomer', 'idTypeEtp')
            ->where('idCustomer', $idEtp)
            ->first();

        return $type;
    }



    public function getEnterpriseOptions(): array
    {
        $etp = DB::table('entreprises')
            ->select('idCustomer', 'idTypeEtp')
            ->where('idCustomer', Customer::idCustomer())
            ->first();

        if (!$etp) {
            return [];
        }

        $isGroup = ($etp->idTypeEtp == 2);
        $enterprises = [];

        // Parent enterprise
        $parent = DB::table('customers')
            ->select('idCustomer', 'customerName')
            ->where('idCustomer', Customer::idCustomer())
            ->first();

        if ($parent) {
            $enterprises[] = (array)$parent;
        }

        // Child enterprises si groupe
        if ($isGroup) {
            $children = DB::table('etp_groupeds as egp')
                ->select('egp.idEntreprise as idCustomer', 'cst.customerName')
                ->join('customers as cst', 'egp.idEntreprise', 'cst.idCustomer')
                ->where('egp.idEntrepriseParent', Customer::idCustomer())
                ->orderBy('cst.customerName', 'asc')
                ->get()
                ->toArray();

            $enterprises = array_merge($enterprises, $children);
        }

        return $enterprises;
    }

    public function updateEntreprise($idCfp, $idEtp, $idTypeEtp): void
    {
        DB::table('entreprises')->where('idCustomer', $idEtp)->update(['idTypeEtp' => $idTypeEtp]);
    }
    public function destroy($idCfp, $idEtp): void
    {
        DB::table('cfp_etps')->where('idCfp', $idCfp)->where('idEtp', $idEtp)->delete();
    }



    public function getTypeEntreprise(int $userId): ?string
    {
        $entrepriseId = Customer::idCustomerById($userId);

        if (DB::table('etp_groupes')->where('idEntreprise', $entrepriseId)->exists()) {
            return 'etp_grouped';
        }

        if (DB::table('etp_singles')->where('idEntreprise', $entrepriseId)->exists()) {
            return 'etp_single';
        }

        return null;
    }
}
