<?php
namespace App\Interfaces;

interface FormateurInterface{
    public function index($idCustomer): mixed;
    public function edit($idCustomer, $idFormateur): mixed;
    public function storeFormateur($idCustomer, $idFormateur, $idTypeFormateur): void;
    public function storeCfpFormateur($idCustomer, $idFormateur, $isActiveFormateur, $isActiveCfp): void;
    public function update($idCustomer, $idFormateur, $name, $firstname, $email, $phone = null): void;
    public function updatePhoto($idCustomer, $idFormateur, $query, $imageFile): void;
    public function destroy($idCustomer, $idFormateur): void;
}