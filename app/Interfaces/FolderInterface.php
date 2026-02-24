<?php
namespace App\Interfaces;

interface FolderInterface{
    public function index($year): mixed;
    public function folderIntras($year): mixed;
    public function folderInters($year): mixed;
    public function projectIntras($idDossier): mixed;
    public function projectInters($idDossier): mixed;
}