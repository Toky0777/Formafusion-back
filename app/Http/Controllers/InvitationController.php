<?php

namespace App\Http\Controllers;

use App\Mail\CustomerInvited;
use App\Mail\ParticulierMail;
use App\Mail\RequestCustomer;
use App\Models\Customer;
use App\Models\Particulier;
use App\Models\User;
use App\Services\BrevoService;
use App\Services\CfpService;
use App\Services\CustomerService;
use App\Services\EmployeService;
use App\Services\EntrepriseService;
use App\Services\LieuService;
use App\Services\ParticulierService;
use App\Services\UserService;
use App\Traits\CheckQuery;
use App\Traits\DeleteQuery;
use App\Traits\HasEnterprise;
use App\Traits\StoreQuery;
use App\Traits\UpdateQuery;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class InvitationController extends Controller
{
    use StoreQuery, CheckQuery, DeleteQuery, UpdateQuery, HasEnterprise;

    public function getCustomerName(EntrepriseService $entreprise, CfpService $cfp, ParticulierService $particulier, $name)
    {
        $typeCustomer = Customer::typeCustomer();
        $particuliers = $particulier->getCustomerName($name);

        switch ($typeCustomer) {
            case 1:
                $etps = $entreprise->getCustomerName($name);
                $customers = array_merge($etps, $particuliers);
                break;
            case 2:
                $customers = $cfp->getCustomerName($name);
                break;
            default:
                $customers = null;
                break;
        }

        if (count($customers) <= 0) {
            return response([
                'status' => 404,
                'message' => "Aucun résultat trouvé !"
            ]);
        }

        $custs = [];

        foreach ($customers as $c) {
            $custs[] = [
                'idCustomer' => $c->idCustomer,
                'idTypeCustomer' => $c->idTypeCustomer,
                'customer_name' => $c->customer_name,
                'customer_email' => $c->customer_email,
                'customer_type' => $c->customer_type,
                'customer_logo' => $c->customer_nif,
                'customer_addr_lot' => $c->customer_addr_lot,
                'is_customer_in_collaboration' => $this->isCustomerInvited($c->idCustomer, $c->idTypeCustomer),
                'customer_type_desc' => $c->type_customer_desc,
            ];
        }

        return response([
            'status' => 200,
            'customers' => $custs
        ]);
    }

    public function getCustomer($id, $typeCustomer)
    {
        if ($typeCustomer == 1 || $typeCustomer == 2) {
            $customer = Customer::getCustomer($id);
        } elseif ($typeCustomer == 3) {
            $customer = Particulier::getParticulier($id);
        } else {
            return response([
                'status' => 404,
                'message' => "introuvable !"
            ]);
        }

        if (!$customer) {
            return response([
                'status' => 404,
                'message' => "Client introuvable !"
            ]);
        }

        return response([
            'status' => 200,
            'customer' => $customer
        ]);
    }

    // invitation Customer existant
    public function inviteCustomer($typeCustomer, $idCustomer)
    {
        if ($typeCustomer == 1) {
            // CFP → ENTREPRISE
            $customer = Customer::getCustomer($idCustomer)->customer_email;

            try {
                DB::transaction(function () use ($idCustomer, $customer) {
                    $this->cfpEtp(Customer::idCustomer(), $idCustomer, 1, 0);

                    // Générer le contenu du mail avec ton Mailable
                    $htmlContent = (new RequestCustomer(Customer::getCustomer(Customer::idCustomer())->customer_name))->render();

                    // Envoyer via Brevo
                    app(BrevoService::class)->sendEmail(
                        $customer,
                        "Invitation - Plateforme",
                        $htmlContent
                    );
                });

                return response([
                    'status' => 200,
                    'message' => "Invitation envoyée avec succès"
                ]);
            } catch (Exception $e) {
                Log::error("Erreur envoi invitation CFP → ENTREPRISE : " . $e->getMessage());
                return response([
                    'status' => 411,
                    'message' => $e->getMessage()
                ]);
            }
        } elseif ($typeCustomer == 2) {
            // ENTREPRISE → CFP
            $customer = Customer::getCustomer($idCustomer)->customer_email;

            try {
                DB::transaction(function () use ($idCustomer, $customer) {
                    $this->cfpEtp($idCustomer, Customer::idCustomer(), 0, 1);

                    $htmlContent = (new RequestCustomer(Customer::getCustomer(Customer::idCustomer())->customer_name))->render();

                    app(BrevoService::class)->sendEmail(
                        $customer,
                        "Invitation - Plateforme",
                        $htmlContent
                    );
                });

                return response([
                    'status' => 200,
                    'message' => "Invitation envoyée avec succès"
                ]);
            } catch (Exception $e) {
                Log::error("Erreur envoi invitation ENTREPRISE → CFP : " . $e->getMessage());
                return response([
                    'status' => 411,
                    'message' => $e->getMessage()
                ]);
            }
        } elseif ($typeCustomer == 3) {
            // CFP → PARTICULIER
            $customer = Particulier::getParticulier($idCustomer)->customer_email;

            try {
                DB::transaction(function () use ($idCustomer, $customer) {
                    $this->cfpParticulier(Customer::idCustomer(), $idCustomer, 1);

                    $htmlContent = (new RequestCustomer($customer->customer_name))->render();

                    app(BrevoService::class)->sendEmail(
                        $customer,
                        "Invitation - Plateforme",
                        $htmlContent
                    );
                });

                return response([
                    'status' => 200,
                    'message' => "Invitation envoyée avec succès"
                ]);
            } catch (Exception $e) {
                Log::error("Erreur envoi invitation CFP → PARTICULIER : " . $e->getMessage());
                return response([
                    'status' => 411,
                    'message' => $e->getMessage()
                ]);
            }
        } else {
            return response([
                'status' => 404,
                'message' => "introuvable !"
            ]);
        }
    }

    public function inviteNewCustomer(Request $req, UserService $usr, CustomerService $cst, EmployeService $emp, LieuService $lieu)
    {
        $customer = Customer::getCustomer(Customer::idCustomer());

        switch ($customer->idTypeCustomer) {
            case 1:
                // CFP invite ENTREPRISE && PARTICULIER
                $validation = Validator::make($req->all(), [
                    'idTypeCustomer' => 'required|integer',
                    'customer_name' => 'required|min:2|max:150',
                    'customer_email' => 'required|email|unique:users,email|not_in:' . $customer->customer_email
                ]);

                if ($validation->fails()) {
                    return response([
                        'status' => 422,
                        'message' => $validation->messages(),
                    ]);
                }

                // Entreprise
                if (in_array($req->idTypeCustomer, [1, 4, 5])) {
                    try {
                        $user = DB::transaction(function () use ($req, $usr, $cst, $emp, $lieu, $customer) {
                            $user = $usr->store(NULL, "Admin_" . $req->customer_name, "Admin_" . $req->customer_name, $req->customer_email, NULL, Hash::make('1234@#'));

                            $cst->store($user->id, $req->customer_name, $req->customer_email, 7, 2, 1);
                            $this->entreprise($user->id, $req->idTypeCustomer);
                            $this->etpPrivate($user->id);

                            $fonction = $this->fonctions("default_fonction", $user->id);
                            $module = $this->mdls("Default module", 1, $user->id, 2);
                            $this->moduleInterne($module);

                            $emp->store($user->id, 1, $user->id, 1, $fonction);
                            $this->roleUser(6, $user->id, 1, 1, 1);
                            $lieu->store($user->id);

                            $this->cfpEtp($user->id, Customer::idCustomer(), 0, 1);

                            $checkProspect = $this->checkProspect(Customer::idCustomer(), $req->customer_name);

                            if ($checkProspect) {
                                if ($this->updateOpportunity($checkProspect->id, $user->id)) {
                                    $this->deleteProspect($req->customer_name);
                                }
                            }

                            $htmlContent = (new RequestCustomer($customer->customer_name))->render();
                            app(BrevoService::class)->sendEmail(
                                $req->customer_email,
                                "Nouveau client de Formafusion",
                                $htmlContent
                            );

                            return $user;
                        });

                        return response([
                            'status' => 200,
                            'message' => "Invitation envoyée avec succès",
                            'etp_id' => $user->id,
                            'etp_name' => $req->customer_name
                        ]);
                    } catch (Exception $e) {
                        return response([
                            'status' => 411,
                            'message' => $e->getMessage()
                        ]);
                    }
                } elseif ($req->idTypeCustomer == 3) {
                    try {
                        DB::transaction(function () use ($req, $usr, $customer) {
                            $password = '1234@#';
                            $user = $usr->store(NULL, $req->customer_name, $req->customer_name, $req->customer_email, NULL, $password);
                            $this->particulier($user->id);
                            $this->roleUser(10, $user->id, 1, 1, 1);
                            $this->cfpParticulier(Customer::idCustomer(), $user->id, 1);

                            $htmlContent = (new ParticulierMail($customer->customer_name, $password, $req->customer_email))->render();
                            $brevo = app(BrevoService::class);
                            $brevo->sendEmail(
                                $req->customer_email,
                                "Nouveau client de Formafusion",
                                $htmlContent
                            );
                            // Mail::to($req->customer_email)->send(new ParticulierMail($customer->customer_name, $password, $req->customer_email));
                        });

                        return response([
                            'status' => 200,
                            'message' => "Invitation envoyée avec succès",
                            'customer' => Customer::getCustomer(Customer::idCustomer())

                        ]);
                    } catch (Exception $e) {
                        return response([
                            'status' => 411,
                            'message' => $e->getMessage()
                        ]);
                    }
                }

                break;
            case 2:
                // ENTREPRISE invite CFP
                $validation = Validator::make($req->all(), [
                    'customer_name' => 'required|min:2|max:150',
                    'customer_email' => 'required|email|unique:users,email|not_in:' . $customer?->customer_email
                ]);

                if ($validation->fails()) {
                    return response([
                        'status' => 422,
                        'message' => $validation->messages()
                    ]);
                }

                try {
                    $createdUserId = null;

                    DB::transaction(function () use ($req, $usr, $cst, $emp, $customer, &$createdUserId) {
                        $user = $usr->store(NULL, "Admin_" . $req->customer_name, "Admin_" . $req->customer_name, $req->customer_email, NULL, Hash::make('1234@#'));

                        $createdUserId = $user->id;

                        $cst->store($user->id, $req->customer_name, $req->customer_email, 7, 1, 1);
                        $this->cfp($user->id);
                        $fonction = $this->fonctions("default_fonction", $user->id);
                        $emp->store($user->id, 1, $user->id, 1, $fonction);
                        $this->roleUser(3, $user->id, 1, 1, 1);
                        $this->cfpEtp(Customer::idCustomer(), $user->id, 1, 0);

                        $htmlContent = (new RequestCustomer($customer->customer_name))->render();
                        $brevo = app(BrevoService::class);
                        $brevo->sendEmail(
                            $req->customer_email,
                            "Nouveau client de Formafusion",
                            $htmlContent
                        );
                    });

                    return response([
                        'status' => 200,
                        'message' => "Invitation envoyée avec succès",
                        'id' => $createdUserId
                    ]);
                } catch (Exception $e) {
                    return response([
                        'status' => 411,
                        'message' => $e->getMessage()
                    ]);
                }

                break;
            default:
                return null;
                break;
        }
    }
}
