<?php
namespace App\Interfaces;

interface ModuleRepository{
    public function storeMdls($reference = null, $tag = null, $name, $subtitle, $durationHour = null, $durationDay = null, $minAppr = null, $maxAppr = null, $idTypeModule, $idCustomer, $idDomaineFormation, $idLevel): mixed;
    public function storeModule($idModule, $price = null, $priceGroup = null): void;
    public function storeModuleInterne($idModule): void;
    public function changeStatus($idModule, $idCustomer, $status, $isComplete): bool;
    public function getModule($id, $idCustomer): mixed;
    public function updateMdls($idModule, $reference = null, $tag = null, $name, $subtitle, $description, $durationHour = null, $durationDay = null, $minAppr = null, $maxAppr = null, $idCustomer, $idDomaineFormation, $idLevel): void;
    public function updateModule($idModule, $price = null, $priceGroup = null): void;
    public function updateModuleImage($idModule, $idCustomer, $query, $imageFile): void;
    public function storeObjectif($idModule, $name);
    public function destroyObjectif($idModule, $idObjectif): void;
    public function storePrestation($idModule, $name);
    public function destroyPrestation($idModule, $idPrestation): void;
    public function storePrerequis($idModule, $name);
    public function destroyPrerequis($idModule, $idPrerequis): void;
    public function storeCible($idModule, $name);
    public function destroyCible($idModule, $idCible): void;
    public function getSumQuality($idModule, $idCustomer): mixed;
    public function storeProgram($idModule, $title, $description): void;
    public function destroyProgram($idModule, $idProgramme): void;
    public function updateProgram($idModule, $idProgramme, $title, $description): void;
}