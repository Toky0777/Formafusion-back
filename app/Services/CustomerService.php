<?php

namespace App\Services;

use App\Interfaces\CustomerInterface;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;

class CustomerService implements CustomerInterface
{
    public function store($idCustomer, $name, $email, $idSecteur, $idType, $idVilleCoded): void
    {
        $cst = new Customer();
        $cst->idCustomer = $idCustomer;
        $cst->customerName = $name;
        $cst->customerEmail = $email;
        $cst->idSecteur = $idSecteur;
        $cst->idTypeCustomer = $idType;
        $cst->idVilleCoded = $idVilleCoded;
        $cst->save();
    }

    public function update($idCustomer, $idCustomerToUpdate, $nif = null, $stat = null, $rcs = null, $name, $phone, $email, $addrLot = null, $addrQuartier = null, $idVilleCoded, $referentName, $referentFirstname = null): void
    {
        DB::table('customers')
            ->join('users', 'users.id', 'customers.idCustomer')
            ->where(function ($query) use ($idCustomerToUpdate) {
                $query->where('customers.idCustomer', $idCustomerToUpdate)
                    ->where('users.id', $idCustomerToUpdate);
            })
            ->update([
                'customers.nif' => $nif,
                'customers.stat' => $stat,
                'customers.rcs' => $rcs,
                'customers.customerName' => $name,
                'customers.customerPhone' => $phone,
                'customers.customerEmail' => $email,
                'customers.customer_addr_lot' => $addrLot,
                'customers.customer_addr_quartier' => $addrQuartier,
                'customers.idVilleCoded' => $idVilleCoded,
                'users.name' => $referentName,
                'users.firstName' => $referentFirstname,
                'users.email' => $email
            ]);
    }

    public function updateLogo($idCfp, $idEtp, $query, $imageFile): void
    {
        $manager = new ImageManager(new Driver());

        $etp = $query->first();

        $image_parts = explode(";base64,", $imageFile);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_base64 = base64_decode($image_parts[1]);
        $image = $manager->read($image_base64)->toWebp(25);

        if (!empty($etp->logo)) {
            Storage::disk('do')->delete('img/entreprises/' . $etp->logo);
        }

        $imageName = uniqid() . '.webp';
        $filePath = 'img/entreprises/' . $imageName;

        DB::transaction(function () use ($filePath, $image, $idEtp, $imageName) {
            // Upload the image to DigitalOcean Space
            Storage::disk('do')->put($filePath, $image, 'public');

            // Update the database with the new image name
            DB::table('customers')->where('idCustomer', $idEtp)->update([
                'logo' => $imageName,
            ]);
        });
    }

    public function getEntreprise($idCustomer, $key)
    {
        $entreprises = DB::table('v_collaboration_cfp_etps')
            ->select('etp_initial_name', 'etp_name', 'etp_logo', 'etp_description', 'etp_phone', 'etp_addr_lot', 'etp_site_web', 'etp_email', 'idEtp', 'idCfp', 'activiteCfp', 'activiteEtp', 'dateInvitation', 'etp_referent_name', 'etp_referent_firstname', 'etp_referent_fonction', 'etp_referent_phone')
            ->where('idCfp', $idCustomer)
            ->where('etp_name', 'like', "%$key%")
            ->orderBy('etp_name', 'ASC')
            ->get();
        return $entreprises;
    }

    public function countEntreprise($idCustomer, $key)
    {
        $entreprises = DB::table('v_collaboration_cfp_etps')
            ->select('etp_name')
            ->where('idCfp', $idCustomer)
            ->where('etp_name', 'like', "%$key%")
            ->orderBy('etp_name', 'ASC')
            ->get();
        return count($entreprises);
    }

    public function countCfp($key)
    {
        $cfps = DB::table('v_collaboration_etp_cfps')
            ->select('etp_name')
            ->where('idEtp', Customer::idCustomer())
            ->where('etp_name', 'like', "%$key%")
            ->orderBy('etp_name', 'ASC')
            ->get();
        return count($cfps);
    }

    public function getCfp($key)
    {
        $cfps = DB::table('v_collaboration_etp_cfps')
            ->select('etp_initial_name', 'etp_name', 'etp_logo', 'etp_description', 'etp_phone', 'etp_addr_lot', 'etp_site_web', 'etp_email', 'idEtp', 'idCfp', 'activiteCfp', 'activiteEtp', 'dateInvitation', 'etp_referent_name', 'etp_referent_firstname', 'etp_referent_fonction', 'etp_referent_phone')
            ->where('idEtp', Customer::idCustomer())
            ->where('etp_name', 'like', "%$key%")
            ->orderBy('etp_name', 'ASC')
            ->get();
        return $cfps;
    }
}
