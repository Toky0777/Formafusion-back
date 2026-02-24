<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait MarketPlaceQuery
{
    use EvaluationQuery;

    public function getAllCfp()
    {
        return DB::table('customers as C')
            ->join('ville_codeds as VC', 'VC.id', '=', 'C.idVilleCoded')
            ->join('mdls as M', 'M.idCustomer', '=', 'C.idCustomer')
            ->join('domaine_formations as DF', 'DF.idDomaine', '=', 'M.idDomaine')
            ->join('users', 'users.id', '=', 'C.idCustomer')
            ->select(
                'C.idCustomer as id',
                'C.customerName as name',
                'C.description',
                'C.customer_addr_quartier as neighborhood',
                'VC.ville_name as city',
                'C.logo',
                'C.customerPhone as phone',
                'C.siteWeb',
                'C.customerEmail as email'
            )
            ->where('users.user_is_deleted', 0)
            ->where('C.idTypeCustomer', 1)
            ->where('M.moduleName', '!=', 'Default module')
            ->where('M.moduleStatut', 1);
    }


    public function getCategoriesByCenter($idCustomer)
    {
        return DB::table('domaine_formations as df')
            ->join('mdls as m', 'm.idDomaine', '=', 'df.idDomaine')
            ->join('customers as c', 'c.idCustomer', '=', 'm.idCustomer')
            ->select('df.idDomaine as id', 'df.nomDomaine as name')
            ->where('c.idCustomer', $idCustomer)
            ->where('m.moduleName', '!=', 'Default module')
            ->where('m.moduleStatut', 1)
            ->where('m.is_public', 1)
            ->distinct()
            ->orderBy('df.nomDomaine')
            ->limit(4)
            ->get();
    }

    public function getNoticeCfp($idCfp)
    {
        $modules = $this->getModuleCfp($idCfp)->toArray();

        $evaluations = array_map([$this, 'getEval'], $modules);

        $validEvaluations = array_filter($evaluations, function ($eval) {
            return $eval['totalEmployees'] > 0;
        });

        $totalEmployees = array_sum(array_column($validEvaluations, 'totalEmployees'));
        $totalAverage = array_sum(array_column($validEvaluations, 'average'));
        $count = count($validEvaluations);

        return [
            'total' => $totalEmployees,
            'average' => round($count > 0 ? $totalAverage / $count : 0, 2),
        ];
    }


    public function getModuleCfp($idCfp)
    {
        $modules = DB::table('mdls')
            ->where('idCustomer', $idCfp)
            ->pluck('idModule');
        return $modules;
    }

    public function trained($idCfp)
    {
        $modules = $this->getModuleCfp($idCfp);

        $trained = 0;

        foreach ($modules as $module) {
            $trained += $this->countLearnerByModuleId($module);
        }

        return $trained;
    }

    public function countLearnerByModuleId($id)
    {
        $projects = $this->getProjectByModulee($id);

        $totalLearnerFormed = 0;

        foreach ($projects as $project) {
            $totalLearnerFormed += count($this->getLearnerByProjectId($project));
        }

        return $totalLearnerFormed;
    }

    public function getLearnerByProjectId($id)
    {
        return $this->checkTypeProject($id) == 1 ? $this->getLearnerByProjectIdIntra($id) : $this->getLearnerByProjectIdInter($id);
    }

    public function getLearnerByProjectIdInter($id)
    {
        $learner = DB::table('detail_apprenant_inters as DA')
            ->join('users as U', 'U.id', 'DA.idEmploye')
            ->join('customers as C', 'C.idCustomer', 'DA.idEtp')
            ->select('DA.idEmploye', 'U.name', 'U.firstName', 'U.photo', 'C.idCustomer', 'C.customerName')
            ->where('DA.idProjet', $id)
            ->get();

        return $learner;
    }

    public function getLearnerByProjectIdIntra($id)
    {
        $learner = DB::table('detail_apprenants as DA')
            ->join('users as U', 'U.id', 'DA.idEmploye')
            ->join('employes as E', 'E.idEmploye', 'DA.idEmploye')
            ->join('customers as C', 'C.idCustomer', 'E.idCustomer')
            ->select('DA.idEmploye', 'U.name', 'U.firstName', 'U.photo', 'C.idCustomer', 'C.customerName')
            ->where('DA.idProjet', $id)
            ->get();

        return $learner;
    }

    public function checkTypeProject($id)
    {
        $typeProject = DB::table('projets')
            ->select('idTypeProjet')
            ->where('idProjet', $id)
            ->first();

        return $typeProject->idTypeProjet;
    }

    public function getAllModules()
    {
        $modules = DB::table('mdls as M')
            ->join('module_levels as ML', 'ML.idLevel', 'M.idLevel')
            ->join('modules as MP', 'MP.idModule', 'M.idModule')
            ->join('domaine_formations as DF', 'DF.idDomaine', 'M.idDomaine')
            ->join('customers as C', 'C.idCustomer', 'M.idCustomer')
            ->join('ville_codeds as VC', 'C.idVilleCoded', 'VC.id')
            ->select('M.idModule', 'M.module_image', 'M.moduleName', 'M.module_image', 'M.description', 'MP.prix', 'M.dureeJ', 'DF.nomDomaine', 'M.dureeH', 'ML.module_level_name', 'C.customerName', 'C.idCustomer', 'VC.ville_name')
            ->where('M.moduleStatut', 1)
            ->where('M.is_public', 1)
            ->where('M.moduleName', '!=', 'Default module')
            ->where('M.idTypeModule', 1)
            ->orderBy('M.moduleName');
        return $modules;
    }

    public function searchByCfp($key)
    {
        $modules = DB::table('mdls as M')
            ->join('module_levels as ML', 'ML.idLevel', 'M.idLevel')
            ->join('modules as MP', 'MP.idModule', 'M.idModule')
            ->join('domaine_formations as DF', 'DF.idDomaine', 'M.idDomaine')
            ->join('customers as C', 'C.idCustomer', 'M.idCustomer')
            ->join('ville_codeds as VC', 'C.idVilleCoded', 'VC.id')
            ->select(
                'M.idModule',
                'M.module_image',
                'M.moduleName',
                'M.description',
                'MP.prix',
                'M.dureeJ',
                'C.siteWeb',
                'MP.prixGroupe',
                'DF.nomDomaine',
                'M.dureeH',
                'ML.module_level_name',
                'C.customerName as cfp_name',
                'C.logo as logo_cfp',
                'C.customerPhone',
                'C.customer_addr_lot',
                'C.customer_addr_quartier',
                'VC.ville_name'
            )
            ->where('M.moduleStatut', 1)
            ->where('M.moduleName', '!=', 'Default module')
            ->where(
                function ($query) use ($key) {
                    $query->where('M.moduleName', 'like', "%$key%")
                        ->orWhere('C.customerName', 'like', "%$key%")
                        ->orWhere('M.description', 'like', "%$key%");
                }
            )
            ->orderBy('M.moduleName')
            ->groupBy('M.idModule');
        return $modules;
    }

    public function getCfpFilter(?string $key, ?int $idCategory, ?int $idRegion)
    {
        $customers = DB::table('customers as C')
            ->join('ville_codeds as VC', 'VC.id', '=', 'C.idVilleCoded')
            ->join('users', 'users.id', 'C.idCustomer')
            ->select(
                'C.idCustomer',
                'C.customerName',
                'C.description',
                'C.customer_addr_quartier',
                'VC.ville_name',
                'C.logo',
                'C.customerPhone',
                'C.siteWeb'
            )
            ->where('users.user_is_deleted', 0)
            ->where('C.idTypeCustomer', 1);

        if ($key) {
            $customers->where(function ($query) use ($key) {
                $query->where('C.customerName', 'like', "%$key%")
                    ->orWhere('C.description', 'like', "%$key%");
            });
        }

        if ($idRegion) {
            $customers->where('C.idVilleCoded', $idRegion);
        }

        if ($idCategory != 0) {
            $customers->join('mdls as M', 'M.idCustomer', '=', 'C.idCustomer')
                ->join('domaine_formations as DF', 'DF.idDomaine', '=', 'M.idDomaine')
                ->where('M.idDomaine', $idCategory)
                ->whereNot('M.moduleName', 'Default Module');
        }

        return $customers->groupBy('C.idCustomer');
    }


    public function getListGategories()
    {
        $categories = DB::table('domaine_formations as df')
            ->join('mdls as m', 'm.idDomaine', '=', 'df.idDomaine')
            ->join('customers as c', 'c.idCustomer', '=', 'm.idCustomer')
            ->join('users', 'users.id', '=', 'c.idCustomer')
            ->select([
                'df.idDomaine as id',
                'df.nomDomaine as name',
                DB::raw('COUNT(DISTINCT c.idCustomer) as count')
            ])
            ->where('m.moduleName', '!=', 'Default module')
            ->where('m.moduleStatut', 1)
            ->where('c.idTypeCustomer', 1)
            ->where('m.is_public', 1)
            ->where('users.user_is_deleted', 0)
            ->groupBy('df.idDomaine', 'df.nomDomaine')
            ->orderBy('df.nomDomaine')
            ->get();
        return $categories;
    }

    public function getListRegion()
    {
        $regions = DB::table('mdls as M')
            ->join('projets as P', 'P.idModule', 'M.idModule')
            ->join('ville_codeds as VC', 'VC.id', 'P.idVilleCoded')
            ->select('VC.id', 'VC.ville_name')
            ->where('P.dateDebut', '>', now())
            ->where('P.idTypeProjet', 2)
            ->where('P.projet_is_trashed', 0)
            ->get();

        return $regions;
    }

    public function getModuleWithEvaluation(?string $key, ?int $category, ?int $place)
    {
        $modules = DB::table('mdls as M')
            ->join('module_levels as ML', 'ML.idLevel', 'M.idLevel', 'ML.module_level_name')
            ->join('modules as MP', 'MP.idModule', 'M.idModule')
            ->join('customers as C', 'C.idCustomer', 'M.idCustomer')
            ->join('ville_codeds as VCST', 'VCST.id', 'C.idVilleCoded')
            ->join('domaine_formations as DF', 'DF.idDomaine', '=', 'M.idDomaine');

        if ($place) {
            $modules->join('projets as P', 'P.idModule', 'M.idModule')
                ->where('P.idVilleCoded', $place)
                ->where('P.idTypeProjet', 2)
                ->where('P.dateDebut', '>', Carbon::now());
        }

        if ($category) {
            $modules->where('M.idDomaine', $category);
        }

        if ($key) {
            $modules->where(function ($query) use ($key) {
                $query->where('M.moduleName', 'like', "%$key%")
                    ->orWhere('C.customerName', 'like', "%$key%")
                    ->orWhere('M.description', 'like', "%$key%");
            });
        }

        $resultQuery = $modules->select('M.idModule', 'M.module_image', 'M.moduleName', 'M.description', 'DF.nomDomaine as domaine_module', 'MP.prix', 'MP.prixGroupe', 'M.dureeJ', 'M.dureeH', 'ML.module_level_name', 'C.customerName as cfp_name', 'C.logo as logo_cfp', 'C.customerPhone as cfp_phone', 'C.siteWeb as cfp_site', 'C.customer_addr_lot as cfp_lot', 'C.customer_addr_quartier as cfp_quartier', 'VCST.ville_name as cfp_ville')
            ->where('M.moduleStatut', 1)
            ->where('M.moduleName', '!=', 'Default module')->orderBy('M.moduleName')->groupBy('M.idModule');

        return $resultQuery;
    }

    public function getModulesWithLevel(?int $level, ?int $category, ?int $price, ?string $key)
    {
        $modules = DB::table('mdls as M')
            ->join('module_levels as ML', 'ML.idLevel', '=', 'M.idLevel')
            ->join('customers as C', 'C.idCustomer', '=', 'M.idCustomer')
            ->join('domaine_formations as DF', 'DF.idDomaine', '=', 'M.idDomaine')
            ->join('modules as MP', 'MP.idModule', 'M.idModule')
            ->select(
                'M.idModule',
                'M.module_image',
                'M.moduleName',
                'M.description',
                'MP.prix',
                'M.dureeJ',
                'M.dureeH',
                'DF.nomDomaine as domaine_module',
                'ML.module_level_name',
                'C.customerName as cfp_name'
            )
            ->where('M.moduleStatut', 1)
            ->where('M.moduleName', '!=', 'Default module');

        if ($category) {
            $modules->where('M.idDomaine', $category);
        }

        if ($key) {
            $modules->where(function ($query) use ($key) {
                $query->where('M.moduleName', 'like', "%$key%")
                    ->orWhere('C.customerName', 'like', "%$key%")
                    ->orWhere('M.description', 'like', "%$key%");
            });
        }

        if ($level) {
            $modules->where('ML.idLevel', $level);
        }

        if ($price) {
            if ($price == 1) {
                $modules->where('Mp.prix', '<', 500000);
            } elseif ($price == 2) {
                $modules->whereBetween('Mp.prix', [500000, 1000000]);
            } elseif ($price == 3) {
                $modules->where('Mp.prix', '>', 1000000);
            }
        }

        return $modules->orderBy('M.moduleName');
    }


    public function getProjectFutre(?int $periode, ?int $category, ?int $price, ?string $key)
    {
        $modules = DB::table('mdls as M')
            ->join('modules as MP', 'MP.idModule', 'M.idModule')
            ->join('customers as C', 'C.idCustomer', 'M.idCustomer')
            ->join('projets as P', 'P.idModule', '=', 'M.idModule')
            ->join('domaine_formations as DF', 'DF.idDomaine', '=', 'M.idDomaine')
            ->leftJoin('seances as S', function ($join) {
                $join->on('S.idProjet', '=', 'P.idProjet')
                    ->on('S.dateSeance', '=', 'P.dateDebut');
            })
            ->join('ville_codeds as V', 'V.id', 'P.idVilleCoded')
            ->select('M.idModule', 'M.module_image', 'M.moduleName', 'M.description', 'MP.prix', 'M.dureeJ', 'M.dureeH', 'C.customerName as cfp_name', 'DF.nomDomaine as domaine_module', 'P.dateDebut', DB::raw("CONCAT(heureDebut, ' - ', heureFin) as seance_time"), 'V.ville_name', 'P.idProjet')
            ->where('M.moduleStatut', 1)
            ->where('M.moduleName', '!=', 'Default module')
            ->where('idTypeProjet', 2);

        if ($key) {
            $modules->where(function ($query) use ($key) {
                $query->where('M.moduleName', 'like', "%$key%")
                    ->orWhere('C.customerName', 'like', "%$key%")
                    ->orWhere('M.description', 'like', "%$key%");
            });
        }

        if ($periode) {
            if ($periode == 1) {
                $modules->whereMonth('P.dateDebut', Carbon::now()->month)
                    ->whereYear('P.dateDebut', Carbon::now()->year);
            }
        }

        if ($category) {
            $modules->where('M.idDomaine', $category);
        }

        if ($price) {
            if ($price == 1) {
                $modules->where(DB::raw('MP.prix * 1'), '<', 200000);
            } elseif ($price == 2) {
                $modules->whereBetween(DB::raw('MP.prix * 1'), [200000, 350000]);
            } elseif ($price == 3) {
                $modules->where(DB::raw('MP.prix * 1'), '>', 350000);
            }
        }


        $resultQuery = $modules->orderBy('M.moduleName')->where('P.dateDebut', '>', Carbon::now());

        return $resultQuery;
    }

    public function getPlaceAvailable($idProjet)
    {
        $placeValidated = DB::table('inter_entreprises')->where('idProjet', $idProjet)->where('isActiveInter', 1)->sum('nbPlaceReserved');
        $totalPlace = DB::table('inters')->where('idProjet', $idProjet)->value('nbPlace');
        $placeAvailable = $totalPlace - $placeValidated;
        return $placeAvailable ?? null;
    }

    public function getDomaine($key, $category, $place)
    {
        $domaines = DB::table('v_module_cfps as M')
            ->select('M.idDomaine', 'M.nomDomaine', DB::raw('COUNT(M.idModule) as nb_module'))
            ->whereNot('M.moduleName', 'Default module')
            ->where('M.moduleStatut', 1);

        if (!empty($key)) {
            $domaines->where('moduleName', 'like', "%$key%");
        }

        if ($category !== 'all') {
            $domaines->where('idDomaine', $category);
        }

        if ($place !== 'all') {
            $domaines->join('projets as P', 'P.idModule', '=', 'M.idModule')
                ->join('ville_codeds', 'ville_codeds.id', 'P.idVilleCoded')
                ->join('villes as V', 'V.idVille', '=', 'ville_codeds.id')
                ->where('ville_codeds.id', $place)
                ->where('P.dateDebut', '>', Carbon::now());
        }

        return $domaines->orderBy('nomDomaine')->groupBy('idDomaine')->get();
    }

    public function getCfp($key, $category, $place)
    {
        $cfps = DB::table('v_module_cfps as M')
            ->select('M.cfpName', 'M.idCustomer', DB::raw('COUNT(M.idModule) as nb_module'))
            ->whereNot('M.moduleName', 'Default module')
            ->where('M.moduleStatut', 1);

        if (!empty($key)) {
            $cfps->where('moduleName', 'like', "%$key%");
        }

        if ($category !== 'all') {
            $cfps->where('idDomaine', $category);
        }

        if ($place !== 'all') {
            $cfps->join('projets as P', 'P.idModule', '=', 'M.idModule')
                ->join('ville_codeds', 'ville_codeds.id', 'P.idVilleCoded')
                ->join('villes as V', 'V.idVille', '=', 'ville_codeds.idVille')
                ->where('ville_codeds.id', $place)
                ->where('P.dateDebut', '>', Carbon::now());
        }

        return $cfps->orderBy('cfpName')->groupBy('idCustomer')->get();
    }

    public function getVille($key, $category, $place)
    {
        $villes = DB::table('mdls as M')
            ->join('projets as P', 'P.idModule', '=', 'M.idModule')
            ->join('ville_codeds as V', 'V.id', 'P.idVilleCoded')
            ->select('V.id', 'V.ville_name', 'V.vi_code_postal', DB::raw('COUNT(M.idModule) as nb_module'));

        if (!empty($key)) {
            $villes->where('moduleName', 'like', "%$key%");
        }

        if ($category !== 'all') {
            $villes->where('idDomaine', $category);
        }

        if ($place !== 'all') {
            $villes->where('V.id', $place);
        }

        $villes = $villes->where('P.project_is_active', 1)
            ->where('P.dateDebut', '>', Carbon::now())
            ->where('V.ville_name', '!=', 'Default')
            ->where('P.idTypeProjet', 2)
            ->orderBy('ville_name')->groupBy('V.id')
            ->get();

        return $villes;
    }

    public function getLevel($key, $category, $place)
    {
        $levels = DB::table('v_module_cfps as M')
            ->select('M.module_level_name as level_name', 'M.idLevel', DB::raw('COUNT(M.idModule) as nb_module'))
            ->whereNot('M.moduleName', 'Default module')
            ->where('M.moduleStatut', 1);

        if (!empty($key)) {
            $levels = $levels->where('moduleName', 'like', "%$key%");
        }

        if ($category !== 'all') {
            $levels->where('idDomaine', $category);
        }

        if ($place !== 'all') {
            $levels->join('projets as P', 'P.idModule', '=', 'M.idModule')
                ->join('ville_codeds', 'ville_codeds.id', 'P.idVilleCoded')
                ->join('villes as V', 'V.idVille', '=', 'ville_codeds.idVille')
                ->where('ville_codeds.id', $place)
                ->where('P.dateDebut', '>', Carbon::now());
        }

        return $levels->groupBy('idLevel')->get();
    }

    public function getSessionGuaranteed($key)
    {
        $sessions = DB::table('v_projet_cfps_inters')->select('idProjet')->where('module_name', 'like', "%$key%")->where('project_status', 'Planifié')->count();

        return $sessions;
    }

    public function getCourseFiltered($domaineIds, $cfpIds, $villeIds, $duringIds, $levelIds, $key)
    {

        $projectsQuery = DB::table('v_module_cfp_with_ville')
            ->select(
                'idVille',
                'ville',
                'idModule',
                'moduleName',
                'prix',
                'cfp_name as cfpName',
                'dureeJ',
                'dureeH',
                'idDomaine',
                'module_image',
                'logo_cfp',
                'idCustomer',
                'description',
                'level_name'
            )->where('moduleName', 'like', "%$key%");

        if (isset($duringIds)) {
            $projectsQuery->whereIn('during', $duringIds);
        }

        if (isset($domaineIds)) {
            $projectsQuery->whereIn('idDomaine', $domaineIds);
        }

        if (isset($villeIds)) {
            $projectsQuery->whereIn('idVille', $villeIds)->where('ville', '!=', 'Default')->where('project_is_active', 1)->where('idTypeProjet', 2)->where('dateDebut', '>', Carbon::now());
        }

        if (isset($cfpIds)) {
            $projectsQuery->whereIn('idCustomer', $cfpIds);
        }

        if (isset($levelIds)) {
            $projectsQuery->whereIn('idLevel', $levelIds);
        }

        $project_count = count($projectsQuery->orderBy('moduleName')->groupBy('idModule')->get());
        $resultsQuery = $projectsQuery->orderBy('moduleName')->groupBy('idModule');
        $results = $resultsQuery->paginate(21);

        $projects = [];
        foreach ($results as $p) {
            $projects[] = [
                'project' => $p,
                'note' => $this->getEval($p->idModule)
            ];
        }

        return [
            'projects' => $projects,
            'project_count' => $project_count,
            'domaine' => $domaineIds,
            'project_count' => $project_count,
            'current_page' => $results->currentPage(),
            'last_page' => $results->lastPage()

        ];
    }

    public function getAllSessionGuaranteed($key)
    {
        $projectQuery = DB::table('v_projet_cfps_inters')->select('idProjet', 'idCustomer', 'module_name', 'idModule', 'logo_cfp', 'dateDebut', 'dateFin', 'ville_name as ville', 'module_description', 'module_image', 'prix', 'dureeH', 'dureeJ', 'cfp_name', 'level_name')
            ->where('project_status', 'Planifié');

        $domainesQuery = DB::table('v_projet_cfps_inters')
            ->select('idDomaine', 'domaine_name as nomDomaine', DB::raw('COUNT(idProjet) as nb_module'))
            ->whereNot('module_name', 'Default module')
            ->where('moduleStatut', 1)
            ->where('project_status', 'Planifié');

        $cfpQuery = DB::table('v_projet_cfps_inters')
            ->select('cfp_name as cfpName', 'idCustomer', DB::raw('COUNT(idProjet) as nb_module'))
            ->whereNot('module_name', 'Default module')
            ->where('moduleStatut', 1)
            ->where('project_status', 'Planifié');

        $villeQuery = DB::table('v_projet_cfps_inters')
            ->select('idVille as id', 'ville_name', 'vi_code_postal',  DB::raw('COUNT(idProjet) as nb_module'))
            ->whereNot('module_name', 'Default module')
            ->where('moduleStatut', 1)
            ->whereNot('ville', 'Default')
            ->where('project_status', 'Planifié');

        $levelQuery = DB::table('v_projet_cfps_inters')
            ->select('idLevel', 'level_name', DB::raw('COUNT(idProjet) as nb_module'))
            ->whereNot('module_name', 'Default module')
            ->where('moduleStatut', 1)
            ->where('project_status', 'Planifié');

        if (isset($key)) {
            $projectQuery->where('module_name', 'like', "%$key%");
            $domainesQuery->where('module_name', 'like', "%$key%");
            $cfpQuery->where('module_name', 'like', "%$key%");
            $villeQuery->where('module_name', 'like', "%$key%");
            $levelQuery->where('module_name', 'like', "%$key%");
        }

        $domaines = $domainesQuery->groupBy('idDomaine')->get();
        $cfps = $cfpQuery->groupBy('idCustomer')->get();
        $villes = $villeQuery->groupBy('idVille')->get();
        $levels = $levelQuery->groupBy('idLevel')->get();

        $project_count = count($projectQuery->orderBy('dateDebut')->get());
        $resultProjects = $projectQuery->orderBy('dateDebut')->paginate(10);

        $projects = [];
        foreach ($resultProjects as $projec) {
            $dateDebut = Carbon::parse($projec->dateDebut);
            Carbon::setLocale('fr');
            $projects[] = [
                'idProjet' => $projec->idProjet,
                'idModule' => $projec->idModule,
                'module_name' => $projec->module_name,
                'idCustomer' => $projec->idCustomer,
                'date_debut' => $this->dateConverted($projec->dateDebut),
                'date_fin' => $this->dateConverted($projec->dateFin),
                'cfp_name' => $projec->cfp_name,
                'ville' => $projec->ville,
                'prix' => $projec->prix,
                'dureeJ' => $projec->dureeJ,
                'dureeH' => $projec->dureeH,
                'module_description' => $projec->module_description,
                'module_image' => $projec->module_image,
                'day' => $dateDebut->day,
                'logo_cfp' => $projec->logo_cfp,
                'mois' => $dateDebut->format('M Y'),
                'note' => $this->getEval($projec->idModule),
                'level_name' => $projec->level_name
            ];
        }

        return [
            'cfps' => $cfps,
            'villes' => $villes,
            'domaines' => $domaines,
            'levels' => $levels,
            'projects' => $projects,
            'project_count' => $project_count,
            'current_page' => $resultProjects->currentPage(),
            'last_page' => $resultProjects->lastPage()
        ];
    }

    public function getSessionGuaranteedFilter($domaineIds, $cfpIds, $villeIds, $duringIds, $levelIds, $key, $selectedTime, $startDate, $endDate)
    {

        $projectsQuery = DB::table('v_projet_cfps_inters')
            ->select('idProjet', 'module_name', 'idModule', 'logo_cfp', 'cfp_name', 'dateDebut', 'dateFin', 'ville', 'module_description', 'module_image', 'during', 'prix', 'dureeH', 'dureeJ', 'level_name', 'idCustomer')
            ->where('project_status', 'Planifié')
            ->where('moduleStatut', 1)
            ->where('module_name', 'like', "%$key%");

        if (isset($selectedTime)) {
            if ($selectedTime == 'week') {
                $projectsQuery->whereBetween('dateDebut', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            }
            if ($selectedTime == 'month') {
                $projectsQuery->whereMonth('dateDebut', Carbon::now()->month);
            }
            if ($selectedTime == 'next_month') {
                $projectsQuery->whereMonth('dateDebut', Carbon::now()->month + 1);
            }
        }

        if (isset($startDate) && !isset($endStart)) {
            $projectsQuery->where('dateDebut', '>=', $startDate);
        }
        if (!isset($startDate) && isset($endDate)) {
            $projectsQuery->where('dateFin', '<=', $endDate);
        }
        if (isset($startDate) && isset($endDate)) {
            $projectsQuery->where('dateDebut', '>=', $startDate)->where('dateFin', '<=', $endDate);
        }

        if (isset($duringIds)) {
            $projectsQuery->whereIn('during', $duringIds);
        }

        if (isset($domaineIds)) {
            $projectsQuery->whereIn('idDomaine', $domaineIds);
        }

        if (isset($villeIds)) {
            $projectsQuery->whereIn('idVille', $villeIds)->where('ville', '!=', 'Default');
        }

        if (isset($cfpIds)) {
            $projectsQuery->whereIn('idCustomer', $cfpIds);
        }

        if (isset($levelIds)) {
            $projectsQuery->whereIn('idLevel', $levelIds);
        }

        $project_count = count($projectsQuery->orderBy('dateDebut')->groupBy('idModule')->get());
        $results = $projectsQuery->orderBy('dateDebut')->groupBy('idModule')->paginate(10);

        $projects = [];
        foreach ($results as $projec) {
            $dateDebut = Carbon::parse($projec->dateDebut);
            Carbon::setLocale('fr');
            $projects[] = [
                'idProjet' => $projec->idProjet,
                'idModule' => $projec->idModule,
                'module_name' => $projec->module_name,
                'date_debut' => $this->dateConverted($projec->dateDebut),
                'date_fin' => $this->dateConverted($projec->dateFin),
                'ville' => $projec->ville,
                'prix' => $projec->prix,
                'dureeH' => $projec->dureeH,
                'dureeJ' => $projec->dureeJ,
                'module_description' => $projec->module_description,
                'module_image' => $projec->module_image,
                'day' => $dateDebut->day,
                'logo_cfp' => $projec->logo_cfp,
                'cfp_name' => $projec->cfp_name,
                'mois' => $dateDebut->format('M Y'),
                'note' => $this->getEval($projec->idModule),
                'level_name' => $projec->level_name,
                'idCustomer' => $projec->idCustomer
            ];
        }

        return [
            'projects' => $projects,
            'total_project' => $project_count,
            'current_page' => $results->currentPage(),
            'last_page' => $results->lastPage(),
            'project_count_result' => $results->total(),
            'res' => $results
        ];
    }

    public function dateConverted($date)
    {
        Carbon::setLocale('fr');
        $dateSeance = \Carbon\Carbon::parse($date);
        return  $dateSeance->translatedFormat('d M Y');
    }

    public function getModuleDomaine($idDomaine)
    {
        return DB::table('v_module_cfps')->select('idDomaine')->where('idDomaine', $idDomaine)->where('moduleStatut', 1)->count();
    }

    public function getEval($idModule)
    {
        $projectIds = $this->getProjectByModulee($idModule);

        $result = DB::table('eval_chauds')
            ->select(
                DB::raw('SUM(firstNotes.generalApreciate) as sumFirstNotes'),
                DB::raw('COUNT(DISTINCT firstNotes.idEmploye) as totalEmployees')
            )
            ->fromSub(function ($query) use ($projectIds) {
                $query->select('idEmploye', 'idProjet', 'generalApreciate')
                    ->from('eval_chauds')
                    ->whereIn('idProjet', $projectIds)
                    ->whereNotNull('generalApreciate')
                    ->groupBy('idEmploye', 'idProjet');
            }, 'firstNotes')
            ->first();

        $average = $result->totalEmployees > 0 ? $result->sumFirstNotes / $result->totalEmployees : 0;

        return [
            'totalEmployees' => $result->totalEmployees,
            'average' => round($average, 1)
        ];
    }

    private function getProjectByModulee($idModule)
    {
        $projects = DB::table('v_projet_cfps')
            ->select('idProjet')
            ->where('idModule', $idModule)
            ->pluck('idProjet');
        return $projects;
    }
}
