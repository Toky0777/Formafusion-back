<?php

namespace App\Services;

use App\Interfaces\AttendanceRepository;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;


class AttendanceService implements AttendanceRepository
{
    public function indexFilter($idCustomer, $status = null): mixed
    {
        return DB::table('v_projet_cfps')
            ->select(
                'idProjet',
                'dateDebut',
                'idEtp',
                'dateFin',
                'module_name',
                'etp_name',
                'li_name',
                'ville',
                'project_status',
                'project_reference',
                'project_description',
                'project_type',
                'paiement',
                DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'),
                'module_image',
                'etp_logo',
                'etp_initial_name',
                'idSalle',
                'salle_name',
                'salle_quartier',
                'salle_code_postal',
                'idCfp_inter',
                'modalite',
                'total_ht',
                'total_ttc',
                'idModule',
                'project_inter_privacy',
                'sub_name',
                'idSubContractor',
                'idCfp',
                'cfp_name',
                'headYear',
                'headMonthDebut',
                'headMonthFin',
                'headDayDebut',
                'headDayFin',
                'total_ht_sub_contractor'
            )
            ->where(function ($query) use ($idCustomer) {
                $query->where('idCfp', $idCustomer)
                    ->orWhere('idCfp_inter', $idCustomer)
                    ->orWhere('idSubContractor', $idCustomer);
            })
            ->where('module_name', '!=', 'Default module')
            ->when($status, fn($q) => $q->where('project_status', $status))
            ->groupBy(
                'idProjet',
                'dateDebut',
                'idEtp',
                'dateFin',
                'module_name',
                'etp_name',
                'li_name',
                'ville',
                'project_status',
                'project_reference',
                'project_description',
                'project_type',
                'paiement',
                'module_image',
                'etp_logo',
                'etp_initial_name',
                'idSalle',
                'salle_name',
                'salle_quartier',
                'salle_code_postal',
                'ville',
                'idCfp_inter',
                'modalite',
                'total_ht',
                'total_ttc',
                'idModule',
                'project_inter_privacy',
                'sub_name',
                'idSubContractor',
                'idCfp',
                'cfp_name',
                'headYear',
                'headMonthDebut',
                'headMonthFin',
                'headDayDebut',
                'headDayFin',
                'total_ht_sub_contractor'
            )
            ->orderBy('dateDebut', 'desc')
            ->get();
    }
    public function indexFilterByFormateur($status = null, $idFormateur = null): mixed
    {
        $query = DB::table('v_projet_cfps')
            ->select(
                'idProjet',
                'dateDebut',
                'idEtp',
                'dateFin',
                'module_name',
                'etp_name',
                'li_name',
                'ville',
                'project_status',
                'project_reference',
                'project_description',
                'project_type',
                'paiement',
                DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'),
                'module_image',
                'etp_logo',
                'etp_initial_name',
                'idSalle',
                'salle_name',
                'salle_quartier',
                'salle_code_postal',
                'idCfp_inter',
                'modalite',
                'total_ht',
                'total_ttc',
                'idModule',
                'project_inter_privacy',
                'sub_name',
                'idSubContractor',
                'idCfp',
                'cfp_name',
                'headYear',
                'headMonthDebut',
                'headMonthFin',
                'headDayDebut',
                'headDayFin',
                'total_ht_sub_contractor'
            )
            ->where('module_name', '!=', 'Default module')
            ->when($status, fn($q) => $q->where('project_status', $status))
            ->when($idFormateur, function ($q) use ($idFormateur) {
                $ids = is_array($idFormateur) ? $idFormateur : [$idFormateur];
                $q->whereExists(function ($sub) use ($ids) {
                    $sub->select(DB::raw(1))
                        ->from('v_formateur_cfps')
                        ->whereColumn('v_formateur_cfps.idProjet', 'v_projet_cfps.idProjet')
                        ->whereIn('v_formateur_cfps.idFormateur', $ids);
                });
            })
            ->groupBy(
                'idProjet',
                'dateDebut',
                'idEtp',
                'dateFin',
                'module_name',
                'etp_name',
                'li_name',
                'ville',
                'project_status',
                'project_reference',
                'project_description',
                'project_type',
                'paiement',
                'module_image',
                'etp_logo',
                'etp_initial_name',
                'idSalle',
                'salle_name',
                'salle_quartier',
                'salle_code_postal',
                'ville',
                'idCfp_inter',
                'modalite',
                'total_ht',
                'total_ttc',
                'idModule',
                'project_inter_privacy',
                'sub_name',
                'idSubContractor',
                'idCfp',
                'cfp_name',
                'headYear',
                'headMonthDebut',
                'headMonthFin',
                'headDayDebut',
                'headDayFin',
                'total_ht_sub_contractor'
            )
            ->orderBy('dateDebut', 'desc')
            ->get();

        return $query;
    }
    public function index(int|array|null $idFormateur = null, int $idCustomer, ?string $status = null, array $filters = []): mixed
    {
        return $this->baseQuery($idFormateur, $status, $filters, $idCustomer);
    }

    public function indexByFormateur(int|array|null $idFormateur = null, ?string $status = null, array $filters = []): mixed
    {
        return $this->baseQuery($idFormateur, $status, $filters);
    }

    public function indexByApprenant(int|array $idApprenant, ?string $status = null, array $filters = []): mixed
    {
        $filters['Apprenant'] = $idApprenant;
        return $this->baseQuery(null, $status, $filters);
    }

    private function baseQuery(int|array|null $idFormateur = null, ?string $status = null, array $filters = [], int $idCustomer = null): mixed
    {
        $query = DB::table('v_projet_cfps')
            ->select(
                'idProjet',
                'dateDebut',
                'idEtp',
                'dateFin',
                'module_name',
                'etp_name',
                'li_name',
                'ville',
                'project_status',
                'project_reference',
                'project_description',
                'project_type',
                'paiement',
                DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'),
                'module_image',
                'etp_logo',
                'etp_initial_name',
                'idSalle',
                'salle_name',
                'salle_quartier',
                'salle_code_postal',
                'idCfp_inter',
                'modalite',
                'total_ht',
                'total_ttc',
                'idModule',
                'project_inter_privacy',
                'sub_name',
                'idSubContractor',
                'idCfp',
                'cfp_name',
                'headYear',
                'headMonthDebut',
                'headMonthFin',
                'headDayDebut',
                'headDayFin',
                'total_ht_sub_contractor'
            )
            ->where('module_name', '!=', 'Default module')
            ->when($status, fn($q) => $q->where('project_status', $status));

        // Filtre sur le client (seulement si $idCustomer est fourni)
        if ($idCustomer) {
            $query->where(function ($query) use ($idCustomer) {
                $query->where('idCfp', $idCustomer)
                    ->orWhere('idCfp_inter', $idCustomer)
                    ->orWhere('idSubContractor', $idCustomer);
            });
        }

        // Filtre sur formateur
        $formateurIds = $filters['Formateur'] ?? $idFormateur;
        if (!empty($formateurIds)) {
            $ids = is_array($formateurIds) ? $formateurIds : [$formateurIds];
            $query->whereExists(function ($q) use ($ids) {
                $q->select(DB::raw(1))
                    ->from('v_formateur_cfps')
                    ->whereColumn('v_formateur_cfps.idProjet', 'v_projet_cfps.idProjet')
                    ->whereIn('v_formateur_cfps.idFormateur', $ids);
            });
        }

        // Filtres dynamiques
        if (!empty($filters['Ville'])) {
            $query->whereIn('li_name', (array)$filters['Ville']);
        }

        if (!empty($filters['Projet'])) {
            $query->whereIn('project_type', (array)$filters['Projet']);
        }

        if (!empty($filters['Entreprise'])) {
            $query->whereIn('idEtp', (array)$filters['Entreprise']);
        }

        if (!empty($filters['Cours'])) {
            $query->whereIn('idModule', (array)$filters['Cours']);
        }

        if (!empty($filters['Mois'])) {
            $mois = (array)$filters['Mois'];
            $query->where(function ($q) use ($mois) {
                foreach ($mois as $m) {
                    $q->orWhereRaw('DATE_FORMAT(dateDebut, "%Y-%m") = ?', [$m]);
                }
            });
        }

        if (!empty($filters['Periode'])) {
            $today = now();
            $periods = [
                'prev_3_month' => [$today->copy()->subMonths(3), $today],
                'prev_6_month' => [$today->copy()->subMonths(6), $today],
                'prev_12_month' => [$today->copy()->subMonths(12), $today],
                'next_3_month' => [$today, $today->copy()->addMonths(3)],
                'next_6_month' => [$today, $today->copy()->addMonths(6)],
                'next_12_month' => [$today, $today->copy()->addMonths(12)],
            ];
            if (isset($periods[$filters['Periode']])) {
                [$from, $to] = $periods[$filters['Periode']];
                $query->whereBetween('dateDebut', [$from, $to]);
            }
        }
        // ✅ Filtre sur apprenant
        if (!empty($filters['Apprenant'])) {
            $apprenantIds = is_array($filters['Apprenant']) ? $filters['Apprenant'] : [$filters['Apprenant']];
            $query->whereExists(function ($q) use ($apprenantIds) {
                $q->select(DB::raw(1))
                    ->from('v_list_apprenants') // Vue pour les apprenants internes
                    ->whereColumn('v_list_apprenants.idProjet', 'v_projet_cfps.idProjet')
                    ->whereIn('v_list_apprenants.idEmploye', $apprenantIds)
                    ->unionAll(
                        DB::table('v_list_apprenant_inter_added') // Vue pour les apprenants ajoutés inter
                            ->select(DB::raw(1))
                            ->whereColumn('v_list_apprenant_inter_added.idProjet', 'v_projet_cfps.idProjet')
                            ->whereIn('v_list_apprenant_inter_added.idEmploye', $apprenantIds)
                    );
            });
        }


        // Group By
        $query->groupBy(
            'idProjet',
            'dateDebut',
            'idEtp',
            'dateFin',
            'module_name',
            'etp_name',
            'li_name',
            'ville',
            'project_status',
            'project_reference',
            'project_description',
            'project_type',
            'paiement',
            'module_image',
            'etp_logo',
            'etp_initial_name',
            'idSalle',
            'salle_name',
            'salle_quartier',
            'salle_code_postal',
            'idCfp_inter',
            'modalite',
            'total_ht',
            'total_ttc',
            'idModule',
            'project_inter_privacy',
            'sub_name',
            'idSubContractor',
            'idCfp',
            'cfp_name',
            'headYear',
            'headMonthDebut',
            'headMonthFin',
            'headDayDebut',
            'headDayFin',
            'total_ht_sub_contractor'
        );

        return $query->orderBy('dateDebut', 'desc')->paginate(20);
    }


    public function countByStatusByFormateur(string $status, int|array $idFormateur = null): int
    {
        $query = DB::table('v_projet_cfps')
            // ->where(function ($query) use ($idCustomer) {
            //     $query->where('idCfp', $idCustomer)
            //         ->orWhere('idCfp_inter', $idCustomer)
            //         ->orWhere('idSubContractor', $idCustomer);
            // })
            ->where('module_name', '!=', 'Default module')
            ->where('project_status', $status);

        if ($idFormateur) {
            $ids = is_array($idFormateur) ? $idFormateur : [$idFormateur];
            $query->whereExists(function ($q) use ($ids) {
                $q->select(DB::raw(1))
                    ->from('v_formateur_cfps')
                    ->whereColumn('v_formateur_cfps.idProjet', 'v_projet_cfps.idProjet')
                    ->whereIn('v_formateur_cfps.idFormateur', $ids);
            });
        }

        return $query->count();
    }
    public function countByStatusByApprenant(string $status, int $idApprenant): int
    {
        $query = DB::table('v_projet_cfps')
            ->where('module_name', '!=', 'Default module')
            ->where('project_status', $status)
            ->whereExists(function ($q) use ($idApprenant) {
                $q->select(DB::raw(1))
                    ->from('v_list_apprenants')
                    ->whereColumn('v_list_apprenants.idProjet', 'v_projet_cfps.idProjet')
                    ->where('v_list_apprenants.idEmploye', $idApprenant);
            });

        return $query->count();
    }

    public function countByStatus($idCustomer, string $status): int
    {
        $query = DB::table('v_projet_cfps')
            ->where(function ($query) use ($idCustomer) {
                $query->where('idCfp', $idCustomer)
                    ->orWhere('idCfp_inter', $idCustomer)
                    ->orWhere('idSubContractor', $idCustomer);
            })
            ->where('module_name', '!=', 'Default module')
            ->where('project_status', $status);
        return $query->count();
    }


    // public function indexStatus($idCustomer, $status): array
    // {
    //     $query = $this->index($idCustomer)->where('project_status', $status)->orderBy('dateDebut', 'asc')->get();
    //     return $query->toArray();
    // }

    public function store(
        $idCustomer,
        $reference = null,
        $title,
        $description = null,
        $isProjectReserved,
        $idModalite,
        $idModule,
        $idTypeProjet,
        $idSalle,
        $dateDebut = null,
        $dateFin = null
    ): void {
        DB::transaction(
            function ()
            use (
                $idCustomer,
                $reference,
                $title,
                $description,
                $isProjectReserved,
                $idModalite,
                $idModule,
                $idTypeProjet,
                $idSalle,
                $dateDebut,
                $dateFin
            ) {
                $projet = DB::table('projets')->insertGetId([
                    'project_reference' => $reference,
                    'project_title' => $title,
                    'project_description' => $description,
                    'project_is_reserved' => $isProjectReserved,
                    'idModalite' => $idModalite,
                    'idCustomer' => $idCustomer,
                    'idModule' => $idModule,
                    'idTypeProjet' => $idTypeProjet,
                    'idVilleCoded' => 1,
                    'project_is_active' => 0,
                    'idSalle' => $idSalle,
                    'dateDebut' => $dateDebut,
                    'dateFin' => $dateFin
                ]);

                if ($idTypeProjet == 1) {
                    DB::table('intras')->insert([
                        'idProjet' => $projet,
                        'idPaiement' => 3,
                        'idEtp' => $idCustomer,
                        'idCfp' => $idCustomer
                    ]);
                } elseif ($idTypeProjet == 2) {
                    DB::table('inters')->insert([
                        'idProjet' => $projet,
                        'idPaiement' => 3,
                        'idCfp' => $idCustomer,
                        'project_inter_privacy' => 0,
                    ]);
                }
            }
        );
    }

    public function show($idCustomer, $idProjet): mixed
    {
        $query = $this->index($idCustomer)->where('idProjet', $idProjet);

        return $query;
    }

    public function headDate($idCustomer): mixed
    {
        $query = DB::table('v_projet_cfps')
            ->select(DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'))
            ->groupBy('headDate')
            ->orderBy('dateDebut', 'asc')
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                });
            })
            ->where('module_name', '!=', 'Default module');

        return $query;
    }

    public function getProject($idCustomer): mixed
    {
        $query = DB::table('projets')->select('*');

        return $query;
    }
}
