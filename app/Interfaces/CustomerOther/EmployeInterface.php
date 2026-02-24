<?php
namespace App\Interfaces\CustomerOther;

interface EmployeInterface{
    public function store($req): void;
    public function getAll(): mixed;
}