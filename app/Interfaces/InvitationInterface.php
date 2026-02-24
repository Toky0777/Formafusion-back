<?php
namespace App\Interfaces;

interface InvitationInterface{
    public function getCustomerName($name): array;
    public function inviteCustomer($req): mixed;
    public function inviteNewCustomer($req): mixed;
}