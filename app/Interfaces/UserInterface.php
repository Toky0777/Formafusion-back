<?php
namespace App\Interfaces;

interface UserInterface{
    public function store($matricule = null, $name, $firstName = null, $email, $phone = null, $password): mixed;
}