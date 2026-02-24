<?php

namespace App\Interfaces;

interface AttendanceRepository
{
    /**
     * Liste paginée des projets avec filtres
     */
    public function index(int|array|null $idFormateur, int $idCustomer, ?string $status = null, array $filters = []): mixed;

    /**
     * Créer un nouveau projet
     */
    public function store(
        int $idCustomer,
        ?string $reference,
        string $title,
        ?string $description,
        bool $isProjectReserved,
        int $idModalite,
        int $idModule,
        int $idTypeProjet,
        int $idSalle,
        ?string $dateDebut,
        ?string $dateFin
    ): void;

    /**
     * Récupère un projet spécifique
     */
    public function show(int $idCustomer, int $idProjet): mixed;

    /**
     * Récupère les dates d'entête pour les statistiques
     */
    public function headDate(int $idCustomer): mixed;

    /**
     * Récupère la liste brute des projets (ex: pour des sélecteurs)
     */
    public function getProject(int $idCustomer): mixed;
}
