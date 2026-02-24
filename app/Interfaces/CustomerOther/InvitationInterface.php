<?php
namespace App\Interfaces\CustomerOther;

interface InvitationInterface{
    public function getCin($cin): mixed;
    public function show($id): mixed;
    public function sendInvitation($req): void;
}