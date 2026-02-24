<?php

namespace App\Interfaces;

interface ProjectRepository
{
    public function index($idCustomer): mixed;
    public function indexStatus($idCustomer, $status): array;
    public function store($idCustomer, $reference = null, $title, $description = null, $isProjectReserved, $idModalite, $idModule, $idTypeProjet, $idSalle, $dateDebut = null, $dateFin = null): mixed;
    public function show($idCustomer, $idProjet): mixed;
    public function headDate($idCustomer): mixed;
    public function getProject($idCustomer): mixed;
}
