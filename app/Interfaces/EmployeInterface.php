<?php

namespace App\Interfaces;

interface EmployeInterface
{
    public function store($id, $idNiveau, $idCustomer, $idSexe, $idFonction): void;

    public function countEmployee($key): int;

    public function getEmployee($key);
}
