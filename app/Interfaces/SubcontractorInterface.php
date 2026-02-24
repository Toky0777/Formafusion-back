<?php
namespace App\Interfaces;

interface SubcontractorInterface{
    public function index($idCustomer): mixed;
    public function edit($idCustomer, $idSubcontractor): mixed;
}