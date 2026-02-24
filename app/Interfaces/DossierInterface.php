<?php

namespace App\Interfaces;

interface DossierInterface
{
    // liste de tous les dossiers
    public function getAllDossiersByCfpAndYear(int $idCfp, int $year);

    // pour la fonction d'enregistrement d'un dossier dans la base de données
    public function createDossier(string $dossier, int $idCfp): int;

    // afficher la liste des dossier de l'année courant
    public function getDossiersByCfpAndYear(int $cfpId, int $year, int $month);

    // Vérifier si un dossier avec un certain nom existe
    public function dossierExists(string $nomDossier);

    // Mettre à jour un dossier
    public function updateDossier(int $idDossier, string $nouveauNom);

    // Supprimer les fichiers associés à un dossier
    public function deleteFiles(int $idDossier);

    // Supprimer les projets liés à un dossier
    public function deleteRelatedProjets(int $idDossier);

    // Supprimer le dossier de la base
    public function deleteDossier(int $idDossier);

    // les details d'information d'un dossier
    public function getEntreprisesDossierDetail(int $idDossier, int $idCfp);

    public function getMontantTotalDossierDetail(int $idDossier);

    public function getProjectTypesDossierDetail(int $idDossier, int $idCfp);

    public function getModuleNamesDossierDetail(int $idDossier, int $idCfp);

    public function getVillesDossierDetail(int $idDossier, int $idCfp);

    public function getDateMinProjetDossierDetail(int $idDossier);

    public function getDateMaxProjetDossierDetail(int $idDossier);

    public function getNombreDocumentDossierDetail(int $idDossier);

    public function getNbProjetDossierDetail(int $idDossier);

    public function getApprenantCountDossierDetail(int $idDossier);

    public function getPaymentStatusDossierDetail(int $idDossier);
}
