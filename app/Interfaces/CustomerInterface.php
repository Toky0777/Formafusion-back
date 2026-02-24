<?php

namespace App\Interfaces;

interface CustomerInterface
{
    public function store($idCustomer, $name, $email, $idSecteur, $idType, $idVilleCoded): void;
    public function update($idCustomer, $idCustomerToUpdate, $nif = null, $stat = null, $rcs = null, $name, $phone, $email, $addrLot = null, $addrQuartier = null, $idVilleCoded, $referentName, $referentFirstname = null): void;
    public function updateLogo($idCfp, $idEtp, $query, $imageFile): void;
}
