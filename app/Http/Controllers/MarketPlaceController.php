<?php

namespace App\Http\Controllers;

use App\Mail\QuoteDemandCompany;
use App\Mail\QuoteDemandIndividual;

use App\Models\Customer;
use App\Services\BrevoService;
use App\Traits\HasFormed;
use App\Traits\MarketPlaceQuery;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class MarketPlaceController extends Controller
{
    use MarketPlaceQuery;

    public function getAllCategoryHaveCourse()
    {
        $categories = DB::table('domaine_formations as DF')
            ->join('mdls as M', 'M.idDomaine', 'DF.idDomaine')
            ->select('DF.nomDomaine', DB::raw('COUNT(M.idModule) as nb_module'), 'DF.idDomaine')
            ->whereNot('M.moduleName', 'Default module')
            ->where('M.moduleStatut', 1)
            ->where('M.is_public', 1)
            ->where('M.idTypeModule', 1)
            ->groupBy('DF.idDomaine')
            ->orderBy('DF.nomDomaine')
            ->get();

        return response()->json($categories);
    }

    public function getAllLevel()
    {
        $levels = DB::table('module_levels')
            ->select('idLevel as id', 'module_level_name as name')
            ->get();

        return response()->json($levels);
    }

    public function getAllCategoryCourse()
    {
        $categories = DB::table('domaine_formations as DF')
            ->join('mdls as M', 'M.idDomaine', 'DF.idDomaine')
            ->join('customers as C', 'C.idCustomer', 'M.idCustomer')
            ->join('ville_codeds as VC', 'C.idVilleCoded', 'VC.id')
            ->select('DF.nomDomaine as name', 'DF.idDomaine', DB::raw('COUNT(M.idModule) as count'))
            ->where('M.moduleStatut', 1)
            ->where('C.idTypeCustomer', 1)
            ->whereNot('M.moduleName', 'Default module')
            ->where('M.is_public', 1)
            ->groupBy('DF.idDomaine')
            ->orderByDesc('count')
            ->get();

        return response()->json([
            'data' => $categories
        ]);
    }

    public function getAllCategoryInEvent()
    {
        $categories = DB::table('domaine_formations as DF')
            ->join('mdls as M', 'M.idDomaine', 'DF.idDomaine')
            ->join('projets as P', 'P.idModule', 'M.idModule')
            ->join('customers as C', 'C.idCustomer', 'M.idCustomer')
            ->join('ville_codeds as VC', 'P.idVilleCoded', 'VC.id')
            ->select('DF.nomDomaine as name', 'DF.idDomaine', DB::raw('COUNT(P.idProjet) as count'))
            ->where('P.idTypeProjet', 2)
            ->where('P.project_is_active', 1)
            ->where('P.project_is_trashed', 0)
            ->where('C.idTypeCustomer', 1)
            ->whereNot('M.moduleName', 'Default module')
            ->groupBy('DF.idDomaine')
            ->orderByDesc('count')
            ->get();

        return response()->json([
            'data' => $categories
        ]);
    }

    public function getAllCustomerCourse()
    {
        $customers = DB::table('mdls as M')
            ->join('customers as C', 'C.idCustomer', 'M.idCustomer')
            ->join('ville_codeds as VC', 'C.idVilleCoded', 'VC.id')
            ->select(
                'C.customerName as name',
                'C.idCustomer',
                DB::raw('COUNT(M.idModule) as count')
            )
            ->where('C.idTypeCustomer', 1)
            ->where('M.moduleStatut', 1)
            ->where('M.is_public', 1)
            ->whereNot('M.moduleName', 'Default module')
            ->groupBy('C.idCustomer')
            ->orderByDesc('count')
            ->get();

        return response()->json([
            'data' => $customers
        ]);
    }

    public function getAllCustomerInEvent()
    {
        $customers = DB::table('projets as P')
            ->join('mdls as M', 'M.idModule', 'P.idModule')
            ->join('ville_codeds as VC', 'P.idVilleCoded', 'VC.id')
            ->join('customers as C', 'C.idCustomer', 'M.idCustomer')
            ->select(
                'C.customerName as name',
                'C.idCustomer',
                DB::raw('COUNT(P.idProjet) as count')
            )
            ->where('C.idTypeCustomer', 1)
            ->where('P.idTypeProjet', 2)
            ->where('P.project_is_active', 1)
            ->where('P.project_is_trashed', 0)
            ->whereNot('M.moduleName', 'Default module')
            ->groupBy('C.idCustomer')
            ->orderByDesc('count')
            ->get();

        return response()->json([
            'data' => $customers
        ]);
    }


    public function getAllCitiesEvent()
    {
        $cities = DB::table('projets as P')
            ->join('modalites as M', 'M.idModalite', '=', 'P.idModalite')
            ->join('ville_codeds as VC', 'P.idVilleCoded', '=', 'VC.id')
            ->join('mdls as MD', 'MD.idModule', '=', 'P.idModule')
            ->select(
                DB::raw('COUNT(P.idProjet) AS count'),
                DB::raw("CASE
                            WHEN P.idModalite = 2 THEN M.modalite
                            ELSE VC.ville_name
                        END AS name"),
                DB::raw("CASE
                            WHEN P.idModalite = 2 THEN 0
                            ELSE VC.id
                        END AS id")
            )
            ->where('P.idTypeProjet', 2)
            ->where('P.project_is_active', 1)
            ->where('P.project_is_trashed', 0)
            ->where('MD.moduleName', '!=', 'Default module')
            ->groupBy('name')
            ->orderByRaw("
                CASE 
                    WHEN (P.idModalite = 2 AND M.modalite = 'En ligne') THEN 0
                    ELSE 1
                END
            ")
            ->orderBy('name')
            ->get();


        return response()->json([
            'data' => $cities
        ]);
    }

    public function getAllCitiesCourse()
    {
        $cities = DB::table('mdls as M')
            ->join('customers as C', 'C.idCustomer', 'M.idCustomer')
            ->join('ville_codeds as VC', 'C.idVilleCoded', 'VC.id')
            ->select(
                'VC.ville_name as name',
                'VC.id',
                DB::raw('COUNT(C.idCustomer) as count')
            )
            ->where('C.idTypeCustomer', 1)
            ->where('M.moduleStatut', 1)
            ->where('M.is_public', 1)
            ->whereNot('M.moduleName', 'Default module')
            ->groupBy('VC.id')
            ->orderBy('VC.ville_name')
            ->get();

        return response()->json([
            'data' => $cities
        ]);
    }

    public function getAllLevelsEvent()
    {
        $levels = DB::table('projets as P')
            ->join('mdls as M', 'M.idModule', 'P.idModule')
            ->join('module_levels as ML', 'ML.idLevel', 'M.idLevel')
            ->join('ville_codeds as VC', 'P.idVilleCoded', 'VC.id')
            ->join('customers as C', 'C.idCustomer', 'M.idCustomer')
            ->select(
                'ML.module_level_name AS name',
                'ML.idLevel',
                DB::raw('COUNT(P.idProjet) as count')
            )
            ->where('C.idTypeCustomer', 1)
            ->where('P.idTypeProjet', 2)
            ->where('P.project_is_active', 1)
            ->where('P.project_is_trashed', 0)
            ->whereNot('M.moduleName', 'Default module')
            ->groupBy('ML.idLevel')
            ->orderBy('ML.idLevel')
            ->get();

        return response()->json(
            [
                'data' => $levels
            ]
        );
    }

    // getAllLevelsCourse
    public function getAllLevelsCourse()
    {
        $levels = DB::table('mdls as M')
            ->join('module_levels as ML', 'ML.idLevel', 'M.idLevel')
            ->join('customers as C', 'C.idCustomer', 'M.idCustomer')
            ->join('ville_codeds as VC', 'C.idVilleCoded', 'VC.id')
            ->select(
                'ML.module_level_name AS name',
                'ML.idLevel',
                DB::raw('COUNT(M.idModule) as count')
            )
            ->where('C.idTypeCustomer', 1)
            ->where('M.moduleStatut', 1)
            ->where('M.is_public', 1)
            ->whereNot('M.moduleName', 'Default module')
            ->groupBy('ML.idLevel')
            ->orderBy('ML.idLevel')
            ->get();

        return response()->json(
            [
                'data' => $levels
            ]
        );
    }

    public function getAllPeriodsEvent()
    {
        $now = $this->getProjectByPeriod(Carbon::today(), Carbon::today()->endOfDay());

        $thisWeek = $this->getProjectByPeriod(Carbon::today(), Carbon::today()->endOfWeek());

        $thisMonth = $this->getProjectByPeriod(Carbon::today(), Carbon::today()->endOfMonth());

        $nextMonth = $this->getProjectByPeriod(
            Carbon::now()->addMonthNoOverflow()->startOfMonth(),
            Carbon::now()->addMonthNoOverflow()->endOfMonth()
        );

        $nextThreeMonth = $this->getProjectByPeriod(
            Carbon::now()->addMonthsNoOverflow(3)->startOfMonth(),
            Carbon::now()->addMonthsNoOverflow(3)->endOfMonth()
        );

        $customers = [
            'today' => $now,
            'this_week' => $thisWeek,
            'this_month' => $thisMonth,
            'next_month' => $nextMonth,
            'next_three_month' => $nextThreeMonth,
        ];

        return response()->json($customers);
    }


    public function getProjectByPeriod($start, $end)
    {
        $project = DB::table('projets as P')
            ->join('mdls as M', 'M.idModule', 'P.idModule')
            ->join('customers as C', 'C.idCustomer', 'M.idCustomer')
            ->select(
                'P.idProjet'
            )
            ->where('C.idTypeCustomer', 1)
            ->where('P.idTypeProjet', 2)
            ->where('P.project_is_active', 1)
            ->where('P.project_is_trashed', 0)
            ->whereNot('M.moduleName', 'Default module')
            ->whereBetween('P.dateDebut', [$start, $end])
            ->get();

        return count($project);
    }

    public function getAllProjectInEvent(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $projects = DB::table('projets as P')
            ->join('mdls as M', 'M.idModule', 'P.idModule')
            ->join('domaine_formations as DF', 'DF.idDomaine', 'M.idDomaine')
            ->join('module_levels as ML', 'ML.idLevel', 'M.idLevel')
            ->leftJoin('modules as MP', 'MP.idModule', 'M.idModule')
            ->join('modalites as MD', 'MD.idModalite', 'P.idModalite')
            ->join('ville_codeds as VC', 'P.idVilleCoded', 'VC.id')
            ->join('customers as C', 'C.idCustomer', 'M.idCustomer')
            ->select(
                'M.moduleName as name',
                'P.dateDebut as date',
                'DF.nomDomaine as category',
                'M.idModule',
                'C.idCustomer',
                'P.idProjet',
                'M.description',
                'M.idModule',
                'C.customerName as center',
                'MP.prix as price',
                'VC.ville_name as city',
                'M.module_image',
                'M.dureeJ as duration',
                'MD.modalite as modality',
                'ML.module_level_name as level'
            )
            ->where('C.idTypeCustomer', 1)
            ->where('P.idTypeProjet', 2)
            ->where('P.project_is_active', 1)
            ->where('P.project_is_trashed', 0)
            ->whereNot('M.moduleName', 'Default module')
            ->orderBy('P.dateDebut', 'desc')
            ->paginate($perPage);

        $projects->getCollection()->transform(function ($project) {
            return [
                'id' => $project->idProjet,
                'name' => $project->name,
                'date' => $project->date,
                'category' => $project->category,
                'level' => $project->level,
                'module_image' =>$project->module_image,
                'duration' => $project->duration,
                'modality' => $project->modality,
                'price' => $project->price,
                'center' => $project->center,
                'description' => $project->description,
                'city' => $project->city,
                'rating' => $this->getEval($project->idModule),
                'module_image' => $project->module_image
            ];
        });

        return response()->json($projects);
    }

    public function filterEvent(Request $request)
    {
        $key          = $request->search;
        $period       = $request->period;
        $perPage      = $request->per_page ?? 10;
        $categoryKey  = $request->theme_search;
        $centerKey    = $request->center_search;

        $categories = is_string($request->categories) ? array_filter(explode(',', $request->categories)) : [];
        $customers  = is_string($request->customers)  ? array_filter(explode(',', $request->customers))  : [];
        $levels     = is_string($request->levels)     ? array_filter(explode(',', $request->levels))     : [];
        $cities     = is_string($request->cities)     ? array_filter(explode(',', $request->cities))     : [];

        $query = DB::table('projets as P')
            ->join('mdls as M', 'M.idModule', '=', 'P.idModule')
            ->join('domaine_formations as DF', 'DF.idDomaine', '=', 'M.idDomaine')
            ->join('module_levels as ML', 'ML.idLevel', '=', 'M.idLevel')
            ->join('modules as MP', 'MP.idModule', '=', 'M.idModule')
            ->join('modalites as MD', 'MD.idModalite', '=', 'P.idModalite')
            ->join('ville_codeds as VC', 'P.idVilleCoded', '=', 'VC.id')
            ->join('customers as C', 'C.idCustomer', '=', 'M.idCustomer')
            ->select([
                'M.moduleName as name',
                'P.dateDebut as date',
                'DF.nomDomaine as category',
                'M.idModule',
                'C.idCustomer',
                'P.idProjet',
                'M.description',
                'C.customerName as center',
                'MP.prix as price',
                'VC.ville_name as city',
                'M.module_image',
                'M.dureeJ as duration',
                'MD.modalite as modality',
                'ML.module_level_name as level'
            ])
            ->where([
                ['C.idTypeCustomer', '=', 1],
                ['P.idTypeProjet', '=', 2],
                ['P.project_is_active', '=', 1],
                ['P.project_is_trashed', '=', 0],
            ])
            ->where('M.moduleName', '!=', 'Default module')
            ->orderByDesc('P.dateDebut');

        if ($key) {
            $query->where('M.moduleName', 'like', "%$key%");
        }

        if ($levels) {
            $query->whereIn('ML.module_level_name', $levels);
        }

        if ($cities) {
            $query->where(function ($q) use ($cities) {
                $physical = array_filter($cities, fn($c) => strtolower($c) !== 'en ligne');
                if ($physical) {
                    $q->whereIn('VC.ville_name', $physical);
                }
                if (in_array('En ligne', $cities)) {
                    $q->orWhere('P.idModalite', '=', 2);
                }
            });
        }

        if ($categoryKey) {
            $query->where('DF.nomDomaine', 'like', "%$categoryKey%");
        }

        if ($centerKey) {
            $query->where('C.customerName', 'like', "%$centerKey%");
        }

        if ($categories) {
            $query->whereIn('DF.nomDomaine', $categories);
        }

        if ($customers) {
            $query->whereIn('C.customerName', $customers);
        }

        if ($period) {
            $now = Carbon::now();

            switch ($period) {
                case 'today':
                    $query->whereDate('P.dateDebut', $now);
                    break;

                case 'this_week':
                    $query->whereBetween('P.dateDebut', [Carbon::today(), Carbon::today()->endOfWeek()]);
                    break;

                case 'this_month':
                    $query->whereBetween('P.dateDebut', [Carbon::today(), Carbon::today()->endOfMonth()]);
                    break;

                case 'next_month':
                    $query->whereBetween('P.dateDebut', [
                        Carbon::now()->addMonthNoOverflow()->startOfMonth(),
                        Carbon::now()->addMonthNoOverflow()->endOfMonth()
                    ]);
                    break;

                case 'next_three_month':
                    $query->whereBetween('P.dateDebut', [
                        Carbon::now()->addMonthsNoOverflow(3)->startOfMonth(),
                        Carbon::now()->addMonthsNoOverflow(3)->endOfMonth()
                    ]);
                    break;
            }
        }

        $paginated = $query->paginate($perPage);

        $paginated->getCollection()->transform(function ($project) {
            return [
                'id'          => $project->idProjet,
                'name'        => $project->name,
                'date'        => $project->date,
                'category'    => $project->category,
                'level'       => $project->level,
                'duration'    => $project->duration,
                'modality'    => $project->modality,
                'price'       => $project->price,
                'center'      => $project->center,
                'description' => $project->description,
                'city'        => $project->city,
                'rating'      => $this->getEval($project->idModule),
                'module_image' => $project->module_image
            ];
        });

        return response()->json($paginated);
    }

    private function getCustomerById($idCustomer)
    {
        return DB::table('customers as C')
            ->join('ville_codeds as V', 'V.id', 'C.idVilleCoded')
            ->select(
                'C.customerName',
                'C.customerPhone',
                'C.customerEmail',
                'C.customer_addr_lot',
                'C.customer_addr_quartier',
                'C.siteWeb',
                'C.description',
                'V.ville_name',
                'C.idCustomer',
                'C.logo'
            )
            ->where('C.idCustomer', $idCustomer)
            ->first();
    }

    public function showDetail($idCfp)
    {
        $customer = $this->getCustomerById($idCfp);

        $modules = DB::table('mdls as M')
            ->leftJoin('modules as MP', 'MP.idModule', 'M.idModule')
            ->leftJoin('projets as P', 'P.idModule', 'M.idModule')
            ->select('M.moduleName', 'MP.prix', 'P.dateDebut', 'M.idModule')
            ->whereNot('M.moduleName', 'Default module')
            ->where('M.idCustomer', $idCfp)
            ->where('P.dateDebut', '>', now())
            ->where('M.is_public', 1)
            ->where('P.idTypeProjet', 2)
            ->groupBy('M.idModule')
            ->get()
            ->take(2);

        $moduleWithProject = (count($modules) > 2) ? true : false;

        if (count($modules) < 1) {
            $modules = DB::table('mdls as M')
                ->leftJoin('modules as MP', 'MP.idModule', 'M.idModule')
                ->select('M.moduleName', 'MP.prix', 'M.idModule')
                ->whereNot('M.moduleName', 'Default module')
                ->where('M.is_public', 1)
                ->where('M.idCustomer', $idCfp)
                ->get()
                ->take(2);
        }

        $noticeEmployee = $this->getFirstNotice($idCfp);

        $notice = $this->getNoticeCfp($customer->idCustomer);

        return response()->json(
            [
                'notice' => $notice,
                'noticeEmployee' => $noticeEmployee,
                'moduleWithProject' => $moduleWithProject,
                'modules' => $modules,
                'customer' => $customer
            ]
        );
    }

    public function getFirstNotice($idCustomer)
    {
        $notice  = DB::table('mdls as M')
            ->join('projets as P', 'M.idModule', 'P.idModule')
            ->join('eval_chauds as E', 'E.idProjet', 'P.idProjet')
            ->select('E.idEmploye', 'E.temoignage', 'E.idProjet', DB::raw('AVG(note) as note'))
            ->where('M.idCustomer', $idCustomer)
            ->whereNotNull('E.temoignage')
            ->groupBy('E.idProjet', 'E.idEmploye')
            ->orderByDesc('note')
            ->first();

        $data = [];

        if ($notice) {
            $data = [
                'employee' => $this->getEmployee($notice->idEmploye),
                'average' => $this->getAverageByEmployeeFirst($notice->idProjet, $notice->idEmploye),
                'temoignage' => $notice->temoignage
            ];
        }

        return $data;
    }

    public function showDetailCours($idModule)
    {
        $module =  DB::table('mdls as M')
            ->leftJoin('modules as MP', 'MP.idModule', 'M.idModule')
            ->join('customers as C', 'C.idCustomer', 'M.idCustomer')
            ->join('domaine_formations as DF', 'DF.idDomaine', 'M.idDomaine')
            ->join('module_levels as ML', 'ML.idLevel', 'M.idLevel')
            ->join('ville_codeds as VC', 'VC.id', 'C.idVilleCoded')
            ->select('M.idModule', 'M.moduleName', 'DF.idDomaine', 'M.module_image', 'DF.nomDomaine', 'ML.module_level_name', 'C.idCustomer', 'C.customerName', 'MP.prix', 'C.customer_addr_lot', 'C.customer_addr_quartier', 'C.siteWeb', 'C.customerPhone', 'VC.ville_name', 'C.logo', 'C.customerEmail', 'M.module_image')
            ->where('M.idModule', $idModule)
            ->first();

        $simularModules = DB::table('mdls as M')
            ->join('modules as MP', 'MP.idModule', 'M.idModule')
            ->join('domaine_formations as DF', 'DF.idDomaine', 'M.idDomaine')
            ->join('customers as C', 'C.idCustomer', 'M.idCustomer')
            ->select('DF.nomDomaine', 'M.module_image', 'MP.prix', 'M.moduleName', 'C.customerName', 'M.idModule')
            ->where('M.idDomaine', $module->idDomaine)
            ->where('M.is_public', 1)
            ->whereNot('M.idModule', $idModule)
            ->take(3)
            ->get();

        $firstNotice = $this->getFirstNoticeModule($idModule);


        return response()->json(
            [
                'module' => $module,
                'simularModules' => $simularModules,
                'firstNotice' => $firstNotice,
            ]
        );
    }

    public function getFirstNoticeModule($idModule)
    {
        $notice  = DB::table('mdls as M')
            ->join('projets as P', 'M.idModule', 'P.idModule')
            ->join('eval_chauds as E', 'E.idProjet', 'P.idProjet')
            ->select('E.idEmploye', 'E.temoignage', 'E.idProjet', DB::raw('AVG(note) as note'))
            ->where('M.idModule', $idModule)
            ->whereNotNull('E.temoignage')
            ->groupBy('E.idProjet', 'E.idEmploye')
            ->orderByDesc('note')
            ->first();

        $data = [];

        if ($notice) {
            $data = [
                'employee' => $this->getEmployee($notice->idEmploye),
                'average' => $this->getAverageByEmployeeFirst($notice->idProjet, $notice->idEmploye),
                'temoignage' => $notice->temoignage
            ];
        }

        return $data;
    }

    public function showDetailEvent($idProjet)
    {
        $project = DB::table('projets as P')
            ->join('mdls as M', 'M.idModule', 'P.idModule')
            ->join('module_levels as ML', 'ML.idLevel', 'M.idLevel')
            ->join('domaine_formations as DF', 'DF.idDomaine', 'M.idDomaine')
            ->join('modules as MP', 'MP.idModule', 'M.idModule')
            ->leftJoin('ville_codeds as VC', 'P.idVilleCoded', 'VC.id')
            ->join('customers as C', 'C.idCustomer', 'M.idCustomer')
            ->leftJoin('ville_codeds as VCST', 'VCST.id', 'C.idVilleCoded')
            ->select(
                'M.moduleName',
                'P.dateDebut',
                'DF.nomDomaine',
                'P.idProjet',
                'VCST.ville_name as ville_cfp',
                'P.dateFin',
                'C.customerPhone',
                'C.idCustomer',
                'C.logo',
                'C.customerEmail',
                'C.description as description_cfp',
                'M.description',
                'M.idModule',
                'C.customerName',
                'MP.prix',
                'VC.ville_name',
                'ML.module_level_name',
                'M.module_image',
                'M.dureeJ',
                'M.dureeH'
            )
            ->where('C.idTypeCustomer', 1)
            ->where('P.idTypeProjet', 2)
            ->where('P.project_is_active', 1)
            ->where('P.project_is_trashed', 0)
            ->where('P.idProjet', $idProjet)
            ->whereNot('M.moduleName', 'Default module')
            ->first();

        $placeValidated = DB::table('inter_entreprises')->where('idProjet', $idProjet)->where('isActiveInter', 1)->sum('nbPlaceReserved') ?? 0;
        $totalPlace = DB::table('inters')->where('idProjet', $idProjet)->value('nbPlace') ?? 0;
        $placeAvailable = ($totalPlace - $placeValidated) ?? 0;

        return response()->json(
            [
                'project' => $project,
                'placeAvailable' => $placeAvailable,
                'placeValidated' => $placeValidated,
                'totalPlace' => $totalPlace,
                'gtftf' => 1
            ],
            200
        );
    }

    public function getAllCustomer(Request $request)
    {
        $customersQuery = $this->getAllCfp();

        $cdnBaseUrl = config('filesystems.disks.do.url_cdn_digital') . '/' . config('filesystems.disks.do.bucket');

        $perPage = $request->per_page ?? 10;
        $customers = $customersQuery->groupBy('id')->paginate($perPage);

        $customers->getCollection()->transform(function ($customer) use ($cdnBaseUrl) {
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'category' => $this->getCategoriesByCenter($customer->id),
                'description' => $customer->description,
                'city' => $customer->city,
                'rating' => $this->getNoticeCfp($customer->id),
                'phone' => $customer->phone,
                'email' => $customer->email,
                'image' => $customer->logo,
                'logo' => "{$cdnBaseUrl}/img/entreprises/{$customer->logo}",
            ];
        });

        return response()->json($customers);
    }



    public function getAllModule(Request $request)
    {
        $modules = $this->getAllModules();

        $modulePaginate = $modules->paginate($request->per_page ?? 9);
        $modulePaginate->getCollection()->transform(function ($m) {
            return [
                'id' => $m->idModule,
                'name' => $m->moduleName,
                'module_image'=> $m -> module_image,
                'category' => $m->nomDomaine,
                'level' => $m->module_level_name,
                'duration' => $m->dureeJ,
                'price' => $m->prix,
                'center' => $m->customerName,
                'description' => $m->description,
                'city' => $m->ville_name,
                'rating' => $this->getEval($m->idModule),
                'module_image' => $m->module_image
                // 'beginDate' => $this->getFirstProject($m->idModule),
                // 'trained' => $this->countLearnerByModule($m->idModule)
            ];
        });


        return response()->json($modulePaginate);
    }

    public function getDetailProjectInReservation($projectId)
    {
        $project = DB::table('projets as P')
            ->join('mdls as M', 'M.idModule', 'P.idModule')
            ->select('M.moduleName', 'P.dateDebut', 'P.dateFin')
            ->where('P.idProjet', $projectId)
            ->first();

        return response()->json($project);
    }

    public function reservationStore(Request $request)
    {
        $projectId = $request->projectId;
        $id_etp = DB::table('entreprises')->where('idCustomer', Customer::idCustomer())->exists();
        $projet = DB::table('projets as P')
            ->select(
                'P.idProjet',
                'P.dateDebut',
                'P.dateFin',
                'P.project_title',
                'V.ville',
                'M.prix',
                'MD.moduleName as module_name',
                'P.idCustomer as idCustomer',
                'P.idModule as idModule'
            )
            ->join('ville_codeds', 'ville_codeds.id', 'P.idVilleCoded')
            ->join('villes as V', 'V.idVille', '=', 'ville_codeds.idVille')
            ->join('inters as I', 'I.idProjet', '=', 'P.idProjet')
            ->join('modules as M', 'M.idModule', '=', 'P.idModule')
            ->join('mdls as MD', 'MD.idModule', '=', 'P.idModule')
            ->where('P.idProjet', $projectId)
            ->first();

        $customer = DB::table('customers')->select('customerName')->where('idCustomer', Customer::idCustomer())->first();
        $reservation = DB::table('inter_entreprises')->where('idEtp', Customer::idCustomer())->where('idProjet', $projectId)->exists();

        if (!$id_etp) {
            return response()->json([
                'error' => "Vous ne pouvez pas réserver de places; seule l'entreprise en a la possibilité."
            ], 403);
        }

        if ($reservation) {
            return response()->json([
                'error' => "Vous avez déjà effectué une réservation pour ce projet."
            ], 409);
        }

        $data = [];

        $reservation_id = null;
        DB::transaction(function () use ($request, $projet, $customer, &$reservation_id, $projectId, &$data) {
            $reservation_id = DB::table('inter_entreprises')->insertGetId([
                'idProjet' => $projectId,
                'idEtp' => Customer::idCustomer(),
                'isActiveInter' => 0,
                'nbPlaceReserved' => count($request->participants)
            ]);

            $prixTotal = $projet->prix * count($request->participants);

            $idPaiement = DB::table('mode_paiements')->insertGetId([
                'idTypePm' => 1
            ]);

            $invoiceId = DB::table('invoices')->insertGetId([
                'invoice_number' => 'RSV-' . $reservation_id,
                'invoice_date' => now(),
                'invoice_date_pm' => now()->addDays(10),
                'invoice_status' => 1,
                'invoice_reduction' => 0,
                'invoice_tva' => 0,
                'invoice_sub_total' => $prixTotal,
                'invoice_total_amount' => $prixTotal,
                'invoice_letter' => null,
                'idCustomer' => $projet->idCustomer,
                'idEntreprise' => Customer::idCustomer(),
                'idPaiement' => $idPaiement,
                'idTypeFacture' => 2
            ]);

            DB::table('invoice_details')->insert([
                'idInvoice' => $invoiceId,
                'idItems' => 0,
                'idProjet' => $projet->idProjet,
                'item_qty' => count($request->participants),
                'item_description' => 'Réservation pour ' . $projet->project_title,
                'item_unit_price' => $projet->prix,
                'idUnite' => 1,
                'item_total_price' => $prixTotal,
            ]);

            foreach ($request->participants as $participant) {
                DB::table('reservation_participant')->insert([
                    'idReservation' => $reservation_id,
                    'nom' => $participant['nom'],
                    'prenom' => $participant['prenom'],
                    'email' => $participant['email'],
                    'fonction' => $participant['fonction']
                ]);
            }

            DB::table('reservation_responsable')->insert([
                'idReservation' => $reservation_id,
                'nom' => $request->responsable['nom'],
                'prenom' => $request->responsable['prenom'],
                'email' => $request->responsable['email'],
                'telephone' => $request->responsable['telephone'],
                'fonction' => $request->responsable['fonction']
            ]);

            $data[] = [
                'id' => $projet->idModule,
                'nbPlace' => count($request->participants),
                'date_begin' => $this->monthConverted($projet->dateDebut),
                'date_end' => $this->dateConverted($projet->dateFin),
                'project_title' => $projet->project_title,
                'customer_name' => $customer->customerName,
                'ville' => $projet->ville,
                'module_name' => $projet->module_name,
                'prix_total' => $prixTotal,
                'invoice_id' => $invoiceId,
                'reservation_id' => $reservation_id
            ];
        });

        return response()->json([
            'message' => 'Reservation ajouté avec succes',
            'data' => $data
        ], 200);
    }

    public function reservationShow($id)
    {
        $data = DB::table('inter_entreprises as R')
            ->join('projets as P', 'P.idProjet', '=', 'R.idProjet')
            ->join('ville_codeds', 'ville_codeds.id', '=', 'P.idVilleCoded')
            ->join('villes as V', 'V.idVille', '=', 'ville_codeds.idVille')
            ->join('mdls as MD', 'MD.idModule', '=', 'P.idModule')
            ->select(
                'R.id as reservation_id',
                'P.project_title',
                'MD.moduleName as module_name',
                'V.ville',
                'P.dateDebut',
                'P.dateFin',
                'R.nbPlaceReserved as nbPlace'
            )
            ->where('R.id', $id)
            ->first();

        if (!$data) {
            return response()->json(['error' => 'Reservation introuvable'], 404);
        }

        return response()->json(['data' => $data], 200);
    }



    private function dateConverted($date)
    {
        Carbon::setLocale('fr');
        $dateSeance = \Carbon\Carbon::parse($date);
        return  $dateSeance->translatedFormat('d M Y');
    }

    public function monthConverted($date)
    {
        Carbon::setLocale('fr');
        $dateSeance = \Carbon\Carbon::parse($date);
        return  $dateSeance->translatedFormat('d M');
    }


    public function filterCourse(Request $request)
    {
        $key          = $request->search;
        $perPage      = $request->per_page ?? 10;
        $categoryKey  = $request->theme_search;
        $centerKey    = $request->center_search;

        $categories = is_string($request->categories) ? array_filter(explode(',', $request->categories)) : [];
        $customers  = is_string($request->customers)  ? array_filter(explode(',', $request->customers))  : [];
        $levels     = is_string($request->levels)     ? array_filter(explode(',', $request->levels))     : [];
        $cities     = is_string($request->cities)     ? array_filter(explode(',', $request->cities))     : [];

        $query = $this->getAllModules();

        if ($key) {
            $query->where('M.moduleName', 'like', "%$key%");
        }

        if ($levels) {
            $query->whereIn('ML.module_level_name', $levels);
        }

        if ($cities) {
            $query->whereIn('VC.ville_name', $cities);
        }

        if ($categoryKey) {
            $query->where('DF.nomDomaine', 'like', "%$categoryKey%");
        }

        if ($centerKey) {
            $query->where('C.customerName', 'like', "%$centerKey%");
        }

        if ($categories) {
            $query->whereIn('DF.nomDomaine', $categories);
        }

        if ($customers) {
            $query->whereIn('C.customerName', $customers);
        }

        $paginated = $query->paginate($perPage);

        $paginated->getCollection()->transform(function ($m) {
            return [
                'id' => $m->idModule,
                'name' => $m->moduleName,
                'category' => $m->nomDomaine,
                'level' => $m->module_level_name,
                'duration' => $m->dureeJ,
                'price' => $m->prix,
                'center' => $m->customerName,
                'description' => $m->description,
                'city' => $m->ville_name,
                'rating' => $this->getEval($m->idModule),
                'module_image' => $m->module_image
                // 'beginDate' => $this->getFirstProject($m->idModule),
                // 'trained' => $this->countLearnerByModule($m->idModule)
            ];
        });

        return response()->json($paginated);
    }

    public function getAllCategory()
    {
        $categories = $this->getListGategories();

        return response()->json([
            'data' => $categories
        ]);
    }

    public function getAllRegion()
    {
        $regions = DB::table('domaine_formations as df')
            ->join('mdls as m', 'm.idDomaine', '=', 'df.idDomaine')
            ->join('customers as c', 'c.idCustomer', '=', 'm.idCustomer')
            ->join('users', 'users.id', '=', 'c.idCustomer')
            ->join('ville_codeds as VC', 'VC.id', 'c.idVilleCoded')
            ->select([
                'VC.id as id',
                'VC.ville_name as name',
                DB::raw('COUNT(DISTINCT c.idCustomer) as count')
            ])
            ->where('m.moduleName', '!=', 'Default module')
            ->where('m.moduleStatut', 1)
            ->where('c.idTypeCustomer', 1)
            ->where('users.user_is_deleted', 0)
            ->groupBy('VC.id', 'VC.ville_name')
            ->orderBy('VC.ville_name')
            ->get();

        return response()->json([
            'data' => $regions
        ]);
    }

    public function filterCfp(Request $request)
    {
        $key          = $request->search;
        $categoryKey  = $request->theme_search;

        $categories = is_string($request->categories) ? array_filter(explode(',', $request->categories)) : [];
        $cities     = is_string($request->cities)     ? array_filter(explode(',', $request->cities))     : [];

        $query = $this->getAllCfp();

        if ($cities) {
            $query->whereIn('VC.ville_name', $cities);
        }

        if ($categories) {
            $query->whereIn('DF.nomDomaine', $categories);
        }

        if ($key) {
            $query->where('C.customerName', 'like', "%$key%");
        }

        if ($categoryKey) {
            $query->where('DF.nomDomaine', 'like', "%$categoryKey%");
        }

        $endpoint = config('filesystems.disks.do.url_cdn_digital');
        $bucket = config('filesystems.disks.do.bucket');
        $digitalOcean = $endpoint . '/' . $bucket;

        $customerPaginate = $query->groupBy('id')->paginate($request->per_page ?? 10);
        $customerPaginate->getCollection()->transform(function ($customer) use ($digitalOcean) {
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'category' => $this->getCategoriesByCenter($customer->id),
                'description' => $customer->description,
                'city' => $customer->city,
                'rating' => $this->getNoticeCfp($customer->id),
                'image' => $customer->logo,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'logo' => $digitalOcean . '/img/entreprises/' . $customer->logo
            ];
        });

        return response()->json($customerPaginate);
    }


    protected function formatCustomers($customers)
    {
        return $customers->map(function ($customer) {
            return [
                'customer' => $customer,
                'notice' => $this->getNoticeCfp($customer->idCustomer),
                'trained' => $this->trained($customer->idCustomer),
            ];
        });
    }

    protected function filterCustomersByEvaluation($customers, $evaluation)
    {
        return $customers->filter(function ($customer) use ($evaluation) {
            $notice = $this->getNoticeCfp($customer->idCustomer);
            return $notice['average'] > $evaluation;
        })->map(function ($customer) {
            return [
                'customer' => $customer,
                'notice' => $this->getNoticeCfp($customer->idCustomer),
                'trained' => $this->trained($customer->idCustomer),
            ];
        });
    }

    protected function paginateCollection($items, $currentPage, $perPage)
    {
        $offset = ($currentPage - 1) * $perPage;
        $currentItems = collect($items)->slice($offset, $perPage)->values();

        return new LengthAwarePaginator(
            $currentItems,
            count($items),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    public function getApercu($idCfp)
    {
        $customer = $this->getCustomerById($idCfp);
        $traits = DB::table('traits')->where('idCustomer', $idCfp)->get();
        $reasons = DB::table('reasons')->where('idCustomer', $idCfp)->get();
        $images = DB::table('marketplace_images')
            ->select('id', 'path')
            ->where('idCustomer', $idCfp)
            ->where('path', 'like', 'img/marketpicture%')
            ->get();

        return response()->json([
            'customer' => $customer,
            'traits' => $traits,
            'reasons' => $reasons,
            'images' => $images
        ]);
    }

    public function getCoursProgram($idCfp)
    {
        $modules = $this->getAllModules()
            ->where('C.idCustomer', $idCfp)
            ->get()
            ->map(function ($module) {
                $eval = $this->getEval($module->idModule);
                $value = $module->prix;

                return [
                    'id'           => $module->idModule,
                    'title'        => $module->moduleName,
                    'theme'        => $module->nomDomaine,
                    'level'        => $module->module_level_name,
                    'duration'     => $module->dureeJ,
                    'price'        => is_numeric($value) ? number_format($value, 0, ',', ' ') . ' Ar' : 'Prix sur demande',
                    'center'       => $module->customerName,
                    'description'  => $module->description ?? 'Aucune description',
                    'location'     => $module->ville_name,
                    'rating'       => $eval['average'],
                    'reviewCount'  => $eval['totalEmployees'],
                    // 'beginDate' => $this->getFirstProject($module->idModule),
                    // 'trained'   => $this->countLearnerByModule($module->idModule),
                ];
            });

        return response()->json($modules);
    }


    public function getFirstProject($idModule)
    {
        $project = DB::table('projets')
            ->select(DB::raw('MIN(dateDebut) as debut'))
            ->where('idModule', $idModule)
            ->where('idTypeProjet', 2)
            ->where('project_is_active', 1)
            ->where('dateDebut', '>', now())
            ->first();

        return $project->debut ?? null;
    }

    public function getAvisModule($idModule)
    {
        $notices  = DB::table('mdls as M')
            ->join('projets as P', 'M.idModule', 'P.idModule')
            ->join('eval_chauds as E', 'E.idProjet', 'P.idProjet')
            ->select('E.idEmploye', 'E.temoignage', 'E.idProjet')
            ->where('M.idModule', $idModule)
            ->groupBy('E.idProjet', 'E.idEmploye')
            ->get();

        $data = [];

        foreach ($notices as $notice) {
            $idEmployee = $notice->idEmploye;
            $idProjet = $notice->idProjet;
            $data[] = [
                'employee' => $this->getEmployee($idEmployee),
                'average' => $this->getAverageByEmployee($idProjet, $idEmployee),
                'temoignage' => $notice->temoignage
            ];
        }

        return response()->json($data);
    }

    public function getAvisCfp($id)
    {
        $notices  = DB::table('customers as C')
            ->join('projets as P', 'P.idCustomer', 'C.idCustomer')
            ->join('eval_chauds as E', 'E.idProjet', 'P.idProjet')
            ->select('E.idEmploye', 'E.temoignage', 'E.idProjet')
            ->where('C.idCustomer', $id)
            ->groupBy('E.idProjet', 'E.idEmploye')
            ->get();

        $data = [];

        foreach ($notices as $notice) {
            $idEmployee = $notice->idEmploye;
            $idProjet = $notice->idProjet;
            $data[] = [
                'employee' => $this->getEmployee($idEmployee),
                'average' => $this->getAverageByEmployee($idProjet, $idEmployee),
                'temoignage' => $notice->temoignage
            ];
        }

        return response()->json($data);
    }

    public function getEmployee($id)
    {
        $employee = DB::table('users')
            ->select(DB::raw("CONCAT(name, ' ', firstname) as fullname"))
            ->where('id', $id)
            ->first();

        return $employee->fullname;
    }

    public function getAverageByEmployee($idProjet, $idEmploye)
    {
        $notice = DB::table('eval_chauds')
            ->select(DB::raw('AVG(note) as note'))
            ->where('idProjet', $idProjet)
            ->where('idEmploye', $idEmploye)
            ->first();

        return $notice->note;
    }

    public function sendEmail(Request $request, $idCfp)
    {
        $customer = DB::table('customers as C')
            ->select('C.customerEmail')
            ->where('C.idCustomer', $idCfp)
            ->first();

        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        $validated = $request->validate([
            'email' => 'required|email',
            'nom' => 'required|string',
            'telephone' => 'required',
            'entreprise' => 'required',
            'demandeFormation' => 'required',
            'subject' => 'required',
        ]);

        Mail::raw(
            "
        Nom: {$request->nom}
        Téléphone: {$request->telephone}
        Email: {$request->email}
        Entreprise: {$request->entreprise}
        Demande: {$request->demandeFormation}
        ",
            function ($message) use ($request, $customer) {
                $message->to($customer->customerEmail)
                    ->subject($request->subject);
            }
        );

        return redirect()->back()->with('success', 'Votre demande a été envoyée avec succès.');
    }

    public function getAverageByEmployeeFirst($idProjet, $idEmploye)
    {
        $notice = DB::table('eval_chauds')
            ->select(DB::raw('AVG(note) as note'))
            ->where('idProjet', $idProjet)
            ->where('idEmploye', $idEmploye)
            ->orderByDesc('note')
            ->first();

        return $notice->note;
    }

    public function getContact($idCfp)
    {
        $customer = DB::table('customers as C')
            ->join('ville_codeds as V', 'V.id', 'C.idVilleCoded')
            ->select('C.customerName', 'C.customerPhone', 'C.customerEmail', 'C.customer_addr_lot', 'C.customer_addr_quartier', 'C.siteWeb', 'C.description', 'V.ville_name', 'C.idCustomer', 'V.vi_code_postal')
            ->where('C.idCustomer', $idCfp)
            ->first();

        return response()->json($customer);
    }

    public function getEvenementCfp($idCfp)
    {
        $projects = DB::table('projets as P')
            ->join('mdls as M', 'M.idModule', '=', 'P.idModule')
            ->join('domaine_formations as DF', 'DF.idDomaine', '=', 'M.idDomaine')
            ->join('module_levels as ML', 'ML.idLevel', '=', 'M.idLevel')
            ->leftJoin('modules as MP', 'MP.idModule', '=', 'M.idModule')
            ->join('modalites as MD', 'MD.idModalite', '=', 'P.idModalite')
            ->join('ville_codeds as VC', 'P.idVilleCoded', '=', 'VC.id')
            ->join('customers as C', 'C.idCustomer', '=', 'M.idCustomer')
            ->select(
                'P.idProjet',
                'M.idModule',
                'M.moduleName as name',
                'P.dateDebut as date',
                'DF.nomDomaine as category',
                'C.idCustomer',
                'C.customerName as center',
                'M.description',
                'MP.prix as price',
                'VC.ville_name as city',
                'M.module_image',
                'M.dureeJ as duration',
                'MD.modalite as modality',
                'ML.module_level_name as level'
            )
            ->where('C.idCustomer', $idCfp)
            ->where('P.dateDebut', '>', Carbon::now())
            ->where('P.idTypeProjet', 2)
            ->where('P.project_is_active', 1)
            ->where('P.project_is_trashed', 0)
            ->where('M.moduleName', '!=', 'Default module')
            ->orderByDesc('P.dateDebut')
            ->get();

        $formatted = $projects->map(function ($formation) {
            $date = $formation->date ? Carbon::parse($formation->date) : null;

            return [
                'id'           => $formation->idProjet,
                'title'        => $formation->name,
                'theme'        => $formation->category,
                'center'       => $formation->center,
                'date'         => $date ? $date->format('d/m/Y') : '',
                'dateObj'      => $date ? $date->format('Y-m-d\TH:i:sP') : null,
                'month'        => $date ? $date->translatedFormat('F') : '',
                'monthShort'   => $date ? strtoupper($date->translatedFormat('M')) : '',
                'dayShort'     => $date ? strtoupper($date->translatedFormat('D')) : '',
                'day'          => $date ? $date->format('d') : '',
                'year'         => $date ? $date->format('Y') : '',
                'duration'     => $formation->duration,
                'format'       => $formation->modality ?? 'Présentiel',
                'price'        => $formation->price ? number_format($formation->price, 0, ',', ' ') . ' Ar' : 'Prix sur demande',
                'priceNum'     => $formation->price ? (int) $formation->price : 0,
                'location'     => $formation->city,
                'level'        => $formation->level,
                'description'  => $formation->description ?? 'Aucune description',
                'rating'       => optional($this->getEval($formation->idModule))['average'],
                'reviewCount'  => optional($this->getEval($formation->idModule))['totalEmployees'] ?? 0,
            ];
        });

        return response()->json($formatted);
    }

    public function getEvenementModule($idModule)
    {
        $projects = DB::table('projets as P')
            ->join('mdls as M', 'M.idModule', '=', 'P.idModule')
            ->join('domaine_formations as DF', 'DF.idDomaine', '=', 'M.idDomaine')
            ->join('module_levels as ML', 'ML.idLevel', '=', 'M.idLevel')
            ->leftJoin('modules as MP', 'MP.idModule', '=', 'M.idModule')
            ->join('modalites as MD', 'MD.idModalite', '=', 'P.idModalite')
            ->join('ville_codeds as VC', 'P.idVilleCoded', '=', 'VC.id')
            ->join('customers as C', 'C.idCustomer', '=', 'M.idCustomer')
            ->select(
                'P.idProjet',
                'M.idModule',
                'M.moduleName as name',
                'P.dateDebut as date',
                'DF.nomDomaine as category',
                'C.idCustomer',
                'C.customerName as center',
                'M.description',
                'MP.prix as price',
                'VC.ville_name as city',
                'M.module_image',
                'M.dureeJ as duration',
                'MD.modalite as modality',
                'ML.module_level_name as level'
            )
            ->where('M.idModule', $idModule)
            ->where('P.dateDebut', '>', Carbon::now())
            ->where('P.idTypeProjet', 2)
            ->where('P.project_is_active', 1)
            ->where('P.project_is_trashed', 0)
            ->where('M.moduleName', '!=', 'Default module')
            ->orderByDesc('P.dateDebut')
            ->get();

        $formatted = $projects->map(function ($formation) {
            $date = $formation->date ? Carbon::parse($formation->date) : null;

            return [
                'id'           => $formation->idProjet,
                'title'        => $formation->name,
                'theme'        => $formation->category,
                'center'       => $formation->center,
                'date'         => $date ? $date->format('d/m/Y') : '',
                'dateObj'      => $date ? $date->format('Y-m-d\TH:i:sP') : null,
                'month'        => $date ? $date->translatedFormat('F') : '',
                'monthShort'   => $date ? strtoupper($date->translatedFormat('M')) : '',
                'dayShort'     => $date ? strtoupper($date->translatedFormat('D')) : '',
                'day'          => $date ? $date->format('d') : '',
                'year'         => $date ? $date->format('Y') : '',
                'duration'     => $formation->duration,
                'format'       => $formation->modality ?? 'Présentiel',
                'price'        => $formation->price ? number_format($formation->price, 0, ',', ' ') . ' Ar' : 'Prix sur demande',
                'priceNum'     => $formation->price ? (int) $formation->price : 0,
                'location'     => $formation->city,
                'level'        => $formation->level,
                'description'  => $formation->description ?? 'Aucune description',
                'rating'       => optional($this->getEval($formation->idModule))['average'],
                'reviewCount'  => optional($this->getEval($formation->idModule))['totalEmployees'] ?? 0,
            ];
        });

        return response()->json($formatted);
    }

    private function getPlaceAvailable($idProjet)
    {
        $place_validated = DB::table('inter_entreprises')->where('idProjet', $idProjet)->where('isActiveInter', 1)->sum('nbPlaceReserved');
        $place_project = DB::table('inters')->where('idProjet', $idProjet)->value('nbPlace');
        $place_available = $place_project - $place_validated;
        return $place_available;
    }

    public function getApercuCours($idModule)
    {
        $queryModule = DB::table('mdls')
            ->select('description', 'dureeJ', 'idModule', 'dureeH', 'idDomaine')
            ->where('idModule', $idModule)
            ->first();

        $module = [
            'description' => $queryModule->description,
            'dureeJ' => $queryModule->dureeJ,
            'dureeH' => $queryModule->dureeH,
            'trained' => $this->countLearnerByModuleId($queryModule->idModule)
        ];

        $objectifs = DB::table('objectif_modules')
            ->select('objectif')
            ->where('idModule', $idModule)
            ->get();

        $cibles = DB::table('cible_modules')
            ->select('cible')
            ->where('idModule', $idModule)
            ->get();

        $requirements = DB::table('prerequis_modules')
            ->select('prerequis_name')
            ->where('idModule', $idModule)
            ->get();

        $programs = DB::table('programmes')
            ->select('idProgramme', 'program_title', 'program_description')
            ->where('idModule', $idModule)
            ->get();

        return response()->json([
            'module' => $module,
            'objectifs' => $objectifs,
            'cibles' => $cibles,
            'requirements' => $requirements,
            'programs' => $programs
        ]);
    }

    public function getAutre($idCustomer)
    {
        $sections = $this->getCustomerSections($idCustomer);
        $img_org = DB::table('marketplace_images')
            ->select('path')
            ->where('idCustomer', $idCustomer)
            ->where('path', 'like', 'img/marketorg%')
            ->first();

        return response()->json(
            [
                'img_org' => $img_org,
                'sections' => $sections
            ]
        );
    }

    private function getCustomerSections($idCustomer)
    {
        return [
            'reglement' => DB::table('reglement')->where('idCustomer', $idCustomer)->value('contenu'),
            'accueil' => DB::table('accueil')->where('idCustomer', $idCustomer)->value('contenu'),
            'conditions' => DB::table('conditions')->where('idCustomer', $idCustomer)->value('contenu'),
            'acces' => DB::table('acces')->where('idCustomer', $idCustomer)->value('contenu'),
            'accompagnement' => DB::table('accompagnement')->where('idCustomer', $idCustomer)->value('contenu'),
        ];
    }

    public function profilCustomer()
    {
        $customerId = Customer::idCustomer();
        $customerName = DB::table('customers')->where('idCustomer', $customerId)->select('customerName')->first();
        $name = $customerName->customerName ?? null;

        $traits = DB::table('traits')->where('idCustomer', $customerId)->get();
        $reasons = DB::table('reasons')->where('idCustomer', $customerId)->get();
        $images = DB::table('marketplace_images')
            ->select('id', 'url')
            ->where('idCustomer', $customerId)
            ->where('path', 'like', 'img/marketpicture%')
            ->get();

        return response()->json(
            [
                'images' => $images,
                'reasons' => $reasons,
                'traits' => $traits,
                'customer' => $name
            ]
        );
    }

    public function regualationCustomer()
    {
        $customerId = Customer::idCustomer();
        $sections = $this->getCustomerSections($customerId);
        $img_org = DB::table('marketplace_images')
            ->select('path')
            ->where('idCustomer', $customerId)
            ->where('path', 'like', 'img/marketorg%')
            ->first();


        return response()->json([
            'sections' => $sections,
            'img_org' => $img_org
        ]);
    }

    public function addReason(Request $request)
    {
        $id = DB::table('reasons')->insertGetId([
            'idCustomer' => Customer::idCustomer(),
            'title' => $request->title,
            'description' => $request->description,
        ]);
        return response()->json(['id' => $id, 'title' => $request->title, 'description' => $request->description]);
    }

    public function removeReason(Request $request)
    {
        DB::table('reasons')->where('id', $request->id)->delete();
        return response()->json(['success' => true]);
    }

    public function quoteDemandCompany(Request $request)
    {
        $rules = [
            'companyName'       => 'required|string|max:255',
            'companyEmail'      => 'required|email|max:255',
            'companyPhone'      => 'required|string|max:50',
            'contactLastName'   => 'required|string|max:255',
            'contactFirstName'  => 'nullable|string|max:255',
            'projectType'       => 'required|in:1,2',
            'modality'          => 'required|in:1,2,3',
            'learnerCount'      => 'required|integer|min:1',
            'fundingType'       => 'required|in:1,2,3',
            'startDate'         => 'required|date',
            'endDate'           => 'required|date|after_or_equal:startDate',
            'location'          => 'nullable|string|max:255',
            'notes'             => 'nullable|string|max:2000',
            'idModule'          => 'required'
        ];

        $messages = [
            'required' => 'Le champ :attribute est requis.',
            'email' => 'Le champ :attribute doit être un email valide.',
            'after_or_equal' => 'La date de fin doit être après ou égale à la date de début.',
            'integer' => 'Le champ :attribute doit être un nombre.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $customer = DB::table('customers as C')
            ->join('mdls as M', 'M.idCustomer', 'C.idCustomer')
            ->leftJoin('module_levels as ML', 'ML.idLevel', 'M.idLevel')
            ->select('C.customerEmail', 'M.moduleName', 'ML.module_level_name')
            ->where('M.idModule', $request->idModule)
            ->first();

        $data = array_merge($request->all(), ['course' => $customer->moduleName, 'level' => $customer->module_level_name]);

        $brevo = app(BrevoService::class);
        $htmlContent = (new QuoteDemandCompany($data))->render();
        $brevo->sendEmail(
            $customer->customerEmail,
            "Demande de devis",
            $htmlContent
        );

        return response()->json([
            'success' => true,
            'message' => 'Formulaire soumis avec succès',
            'data' => $request->all(),
        ], 200);
    }

    public function quoteDemandIndividual(Request $request)
    {
        $rules = [
            'lastName'       => 'required|string|max:255',
            'email'      => 'required|email|max:255',
            'firstName'      => 'required|string|max:50',
            'phone'      => 'required|string|max:20',
            'professionalStatus' => 'required|string|max:50',
            'startDate'         => 'required|date',
            'endDate'           => 'required|date|after_or_equal:startDate',
            'location'          => 'nullable|string|max:255',
            'notes'             => 'nullable|string|max:2000',
            'idModule'          => 'required',
            'modality'          => 'required|in:1,2,3'
        ];

        $messages = [
            'required' => 'Le champ :attribute est requis.',
            'email' => 'Le champ :attribute doit être un email valide.',
            'after_or_equal' => 'La date de fin doit être après ou égale à la date de début.',
            'integer' => 'Le champ :attribute doit être un nombre.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $customer = DB::table('customers as C')
            ->join('mdls as M', 'M.idCustomer', 'C.idCustomer')
            ->leftJoin('module_levels as ML', 'ML.idLevel', 'M.idLevel')
            ->select('C.customerEmail', 'M.moduleName', 'ML.module_level_name')
            ->where('M.idModule', $request->idModule)
            ->first();

        $data = array_merge($request->all(), ['course' => $customer->moduleName, 'level' => $customer->module_level_name]);

        $brevo = app(BrevoService::class);
        $htmlContent = (new QuoteDemandIndividual($data))->render();
        $brevo->sendEmail(
            $customer->customerEmail,
            "Demande de devis",
            $htmlContent
        );

        return response()->json([
            'success' => true,
            'message' => 'Formulaire soumis avec succès',
            'data' => $request->all(),
        ], 200);
    }
}
