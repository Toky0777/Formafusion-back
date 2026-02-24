<?php
namespace App\Interfaces;

interface EntrepriseInterface{
    public function getEntrepriseForm($idFormateur): array;
    public function getAllEnterprises($idCfp, $idTypeEtp): mixed;
    public function letterFilterEnterprises($tableCollections): mixed;
    public function getEnterpriseType($idEtp): mixed;
}