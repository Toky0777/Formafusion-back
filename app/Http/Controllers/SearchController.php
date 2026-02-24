<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Traits\CourseQuery;
use App\Traits\EmployeQuery;
use App\Traits\EvaluationQuery;
use App\Traits\FolderQuery;
use App\Traits\PlaceQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Traits\Project;
use App\Traits\ReferentQuery;
use Carbon\Carbon;
use App\Traits\SearchQuery;
use App\Traits\TrainerQuery;
use App\Services\CourseService;
use App\Services\CustomerService;
use App\Services\EmployeService;
use App\Services\FolderService;
use App\Services\InvoiceService;
use App\Services\LearnerService;
use App\Services\ProjectService;
use App\Services\PurchaseorderService;
use App\Services\ReferentService;
use App\Services\SearchService;
use App\Services\TrainerService;
use App\Services\ParticulierService;

class SearchController extends Controller
{
    use Project;

    use SearchQuery;

    use PlaceQuery;

    use TrainerQuery;

    use ReferentQuery;

    use CourseQuery;

    use FolderQuery;

    use EvaluationQuery;

    use EmployeQuery;

    protected $searchService;
    protected $customerService;
    protected $learnerService;
    protected $projectService;
    protected $courseService;
    protected $referentService;
    protected $folderService;
    protected $trainerService;
    protected $employeeService;
    protected $invoiceService;
    protected $purchaseorderService;
    protected $particularService;

    public function __construct(CustomerService $customerService, ParticulierService $particularService, PurchaseorderService $purchaseorderService, InvoiceService $invoiceService, EmployeService $employeeService, LearnerService $learnerService, ProjectService $projectService, CourseService $courseService, ReferentService $referentService, FolderService $folderService, TrainerService $trainerService)
    {
        $this->projectService = $projectService;
        $this->customerService = $customerService;
        $this->learnerService = $learnerService;
        $this->courseService = $courseService;
        $this->referentService = $referentService;
        $this->folderService = $folderService;
        $this->trainerService = $trainerService;
        $this->employeeService = $employeeService;
        $this->invoiceService = $invoiceService;
        $this->purchaseorderService = $purchaseorderService;
        $this->particularService = $particularService;
    }

    public function searchGenerality(Request $request)
    {
        $key = $request->key;
        $idCustomer = Customer::idCustomer();

        $project = $this->projectService->countProjectCfp($idCustomer, $key);

        $trainer = $this->trainerService->countTrainerCfp($key, $idCustomer);

        $entreprise = $this->customerService->countEntreprise($idCustomer, $key);

        $learner = $this->learnerService->countLearner($key, $idCustomer);

        $referent = $this->referentService->countReferentCfp($key, $idCustomer);

        $course = $this->courseService->countCourse($key, $idCustomer);

        $referent_customer = $this->referentService->countReferentCustomer($key, $idCustomer);

        $project_reference = $this->projectService->countProjectByReference($key, $idCustomer);

        $projectCity = $this->projectService->countProjectByCity($key, $idCustomer);

        $projectPlace = $this->projectService->countProjectByPlace($key, $idCustomer);

        $projectNeighborhood = $this->projectService->countProjectByNeighborhood($key, $idCustomer);

        $folder = $this->folderService->countFolder($key, $idCustomer);

        $project_with_client = $this->getProjectWithEtp($key);

        $invoice = $this->invoiceService->countInvoice($key, $idCustomer);

        $quote = $this->invoiceService->countQuote($key, $idCustomer);

        $purchaseOrder = $this->purchaseorderService->countPurchaseOrderByKey($key, $idCustomer);

        $particulars = $this->particularService
            ->getParticularByKey($idCustomer, $key)
            ->count();

        return response()->json([
            'project' => $project,
            'trainer' => $trainer,
            'entreprise' => $entreprise,
            'learner' => $learner,
            'referent' => $referent,
            'project_with_client' => $project_with_client,
            'course' => $course,
            'referent_customer' => $referent_customer,
            'project_reference' => $project_reference,
            'projectNeighborhood' => $projectNeighborhood,
            'projectPlace' => $projectPlace,
            'projectCity' => $projectCity,
            'folder' => $folder,
            'invoice' => $invoice,
            'quote' => $quote,
            'purchase_order' => $purchaseOrder,
            'particulars' => $particulars
        ]);
    }

    public function getInvoice(Request $request)
    {
        $invoices = $this->invoiceService->getInvoice($request->key);

        return response()->json([
            'status' => 200,
            'invoices' => $invoices
        ]);
    }

    public function getParticular(Request $request)
    {
        $particulars = $this->particularService
            ->getParticularByKey(Customer::idCustomer(), $request->key)
            ->get();

        return response()->json($particulars, 200);
    }

    public function getProforma(Request $request)
    {
        $quotes = $this->invoiceService->getQuote($request->key);

        return response()->json([
            'status' => 200,
            'quotes' => $quotes
        ]);
    }

    public function getPurchaseOrder(Request $request)
    {
        $purchaseOrders = $this->purchaseorderService->getPurchaseOrderByKey($request->key, Customer::idCustomer());

        return response()->json($purchaseOrders, 200);
    }

    public function getProjectWithEtp($key)
    {
        $get_projects = DB::table('v_projet_cfps')
            ->select('idEtp', 'etp_name', DB::raw('COUNT(idProjet) as count_project'))
            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer())
                    ->orWhere('idSubContractor', Customer::idCustomer());
            })
            ->where('project_is_trashed', 0)
            ->whereIn('project_status', ['Planifié', 'En cours', 'Terminé', 'Cloturé', 'Annulé'])
            ->where('project_is_trashed', 0)
            ->where('project_is_active', 1)
            ->where('etp_name', 'like', "%$key%")
            ->groupBy('idEtp')
            ->get();

        $projects = [];

        foreach ($get_projects as $project) {
            $projects[] = [
                'type' => 'Projet avec ' . $project->etp_name,
                'count' => $project->count_project,
                'idEtp' => $project->idEtp
            ];
        }

        return $projects;
    }

    public function getLearner(Request $request)
    {
        $key = $request->key;
        $idCustomer = Customer::idCustomer();

        $learners = $this->learnerService->getLearner($key, $idCustomer);

        return response()->json($learners, 200);
    }

    public function getAllProject(Request $request)
    {
        $key = $request->key;
        $idCustomer = Customer::idCustomer();
        $perPage = $request->per_page ?? 9;
        $page = $request->page ?? 1;

        $results = $this->projectService->getProjectCfpWithPagination($idCustomer, $key, $perPage, $page);

        return $results;
    }

    public function getProjectCfpByReference(Request $request)
    {
        return $this->projectService->getProjectByReference($request->key, Customer::idCustomer(), $request->per_page ?? 9, $request->page ?? 1);
    }

    public function getProjectCfpByNeighborhood(Request $request)
    {
        return $this->projectService->getProjectByNeighborhood($request->key, Customer::idCustomer(), $request->per_page ?? 9, $request->page ?? 1);
    }

    public function getProjectCfpByPlace(Request $request)
    {
        return $this->projectService->getProjectByPlace($request->key, Customer::idCustomer(), $request->per_page ?? 9, $request->page ?? 1);
    }

    public function getProjectCfpByCity(Request $request)
    {
        return $this->projectService->getProjectByCity($request->key, Customer::idCustomer(), $request->per_page ?? 9, $request->page ?? 1);
    }

    public function getProjectWithEtpPaginate(Request $request)
    {
        return $this->projectService->getProjectByEtpWithPaginate(Customer::idCustomer(), $request->idEtp, $request->per_page ?? 9, $request->page ?? 1);
    }

    public function getTrainerCfp(Request $request)
    {
        $key = $request->key;
        $idCustomer = Customer::idCustomer();

        $trainers = $this->trainerService->getTrainerCfp($key, $idCustomer);

        return response()->json($trainers, 200);
    }

    public function  getEntreprise(Request $request)
    {
        $key = $request->key;
        $idCustomer = Customer::idCustomer();

        $entreprises = $this->customerService->getEntreprise($idCustomer, $key);

        return response()->json($entreprises, 200);
    }

    public function  getCfp(Request $request)
    {
        $key = $request->key;

        $entreprises = $this->customerService->getCfp($key);

        return response()->json($entreprises, 200);
    }

    public function  getEmployee(Request $request)
    {
        $key = $request->key;

        $employees = $this->employeeService->getEmployee($key);

        return response()->json($employees, 200);
    }

    public function getReferentCfp(Request $request)
    {
        $referents = $this->referentService->getReferentCfp($request->key, Customer::idCustomer());

        return response()->json($referents, 200);
    }

    public function getReferentCustomer(Request $request)
    {
        $referents = $this->referentService->getReferentCustomer($request->key, Customer::idCustomer());

        return response()->json($referents, 200);
    }



    public function getCourse(Request $request)
    {
        $courses = $this->courseService->getModuleByKey(Customer::idCustomer(), $request->key);
        return response()->json($courses, 200);
    }

    public function getFolderCfp(Request $request)
    {
        $key = $request->key;
        $idCustomer = Customer::idCustomer();

        $folders = $this->folderService->getFolder($key, $idCustomer);

        return response()->json($folders, 200);
    }

    public function keySuggestion(Request $req)
    {
        $key = $req->key;

        $endpoint = config('filesystems.disks.do.url_cdn_digital');
        $bucket = config('filesystems.disks.do.bucket');
        $digitalOcean = $endpoint . '/' . $bucket;

        $user_icon = asset('img/icon/user.jpg');
        $project_icon = asset('/img/icon/Project.png');
        $module_icon = asset('/img/icon/Catalogue.png');
        $customer_icon = asset('/img/icon/Customer.png');
        $folder_icon = asset('/img/icon/Dossiers.png');
        $place_icon = asset('/img/icon/Lieu et Salle.png');
        $purchase_icon = asset('/img/icon/purchase_order.png');

        $result = [];

        $customers = DB::table('customers as C')
            ->select('C.customerName', 'C.logo', 'V.ville_name')
            ->join('cfp_etps as E', 'C.idCustomer', '=', 'E.idEtp')
            ->join('ville_codeds as V', 'V.id', 'C.idVilleCoded')
            ->where('E.idCfp', Customer::idCustomer())
            ->where('C.customerName', 'like', "%$key%")
            ->get();

        $resultCustomers = [];
        foreach ($customers as $customer) {
            $image = $digitalOcean . '/img/entreprises/' . $customer->logo;

            $resultCustomers[] = [
                'context' => $customer->customerName,
                'image' => ($customer->logo) ? $image : $customer_icon,
                'subContext' => 'Entreprise à ' . $customer->ville_name
            ];
        }

        $result = array_merge($result, $resultCustomers);
        if (count($result) >= 10) {
            return array_slice($result, 0, 10);
        }

        $folders = DB::table('dossiers')
            ->select('nomDossier', 'idDossier')
            ->where('idCfp', Customer::idCustomer())
            ->where('nomDossier', 'like', "%$key%")
            ->get();

        $resultFolders = [];

        foreach ($folders as $folder) {
            $resultFolders[] = [
                'context' => $folder->nomDossier,
                'image' => $folder_icon,
                'subContext' => $this->getNumberDocumentByFolder($folder->idDossier) . ' document(s), ' . $this->getTotalProjectByFolder($folder->idDossier) . ' projet(s)'
            ];
        }

        $result = array_merge($result, $resultFolders);
        if (count($result) >= 10) {
            return array_slice($result, 0, 10);
        }

        $purchaseOrders = $this->purchaseorderService->getPurchaseOrderByKey($key, Customer::idCustomer());

        $resultPurchaseOrder = [];

        foreach ($purchaseOrders as $purchaseOrder) {
            $resultPurchaseOrder[] = [
                'context' => $purchaseOrder->numero_bc,
                'image' => $purchase_icon,
                'subContext' => 'Bon de commande de ' . $purchaseOrder->etp_name
            ];
        }

        $result = array_merge($result, $resultPurchaseOrder);
        if (count($result) >= 10) {
            return array_slice($result, 0, 10);
        }

        $modules = DB::table('mdls as M')
            ->join('module_levels as ML', 'ML.idLevel', 'M.idLevel')
            ->select('M.moduleName', 'M.module_image', 'ML.module_level_name')
            ->where('M.idCustomer', Customer::idCustomer())
            ->whereNot('M.moduleName', 'Default module')
            ->where('M.moduleName', 'like', "%$key%")
            ->get();

        $resultModules = [];

        foreach ($modules as $module) {
            $image = ($module->module_image) ? $digitalOcean . '/img/modules/' . $module->module_image : $module_icon;

            $resultModules[] = [
                'context' => $module->moduleName,
                'image' => $image,
                'subContext' => 'Niveau: ' . $module->module_level_name
            ];
        }

        $result = array_merge($result, $resultModules);
        if (count($result) >= 10) {
            return array_slice($result, 0, 10);
        }

        $learners = DB::table('v_apprenant_etp_alls')
            ->select('emp_name', 'emp_firstname', 'emp_photo', 'etp_name')
            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->where('id_cfp', Customer::idCustomer())
                    ->orWhere('id_cfp_appr', Customer::idCustomer());
            })
            ->whereNotNull('emp_name')
            ->where(function ($query) use ($key) {
                $query->where('emp_name', 'like', "%$key%")
                    ->orWhere('emp_firstname', 'like', "%$key%");
            })
            ->orderBy('emp_name')
            ->distinct()
            ->get();

        $resultLearners = [];

        foreach ($learners as $learner) {
            $fullName = $learner->emp_name . ' ' . $learner->emp_firstname ?? '';
            $image = ($learner->emp_photo) ? $digitalOcean . '/img/employes/' . $learner->emp_photo : $user_icon;
            $resultLearners[] = [
                'context' => $fullName,
                'image' => $image,
                'subContext' => 'Employé de ' . $learner->etp_name
            ];
        }

        $result = array_merge($result, $resultLearners);
        if (count($result) >= 10) {
            return array_slice($result, 0, 10);
        }

        $particulars = $this->particularService
            ->getParticularByKey(Customer::idCustomer(), $key)
            ->get();

        $resultParticulars = [];

        foreach ($particulars as $particular) {
            $fullName = $particular->name . ' ' . $particular->firstName ?? '';
            $image = ($particular->photo) ? $digitalOcean . '/img/particuliers/' . $particular->photo : $user_icon;
            $resultParticulars[] = [
                'context' => $fullName,
                'image' => $image,
                'subContext' => 'Votre particulier'
            ];
        }

        $result = array_merge($result, $resultParticulars);
        if (count($result) >= 10) {
            return array_slice($result, 0, 10);
        }

        $referents = DB::table('v_employe_alls')
            ->select('name', 'firstName', 'photo', 'customerName')
            ->where('idCustomer', Customer::idCustomer())
            ->where('role_id', 8)
            ->whereNotNull('name')
            ->where(function ($query) use ($key) {
                $query->where('name', 'like', "%$key%")
                    ->orWhere('firstName', 'like', "%$key%");
            })
            ->get();

        $resultReferents = [];

        foreach ($referents as $referent) {
            $fullName = $referent->name . ' ' . $referent->firstName ?? '';
            $image = ($referent->photo) ? $digitalOcean . '/img/referents/' . $referent->photo : $user_icon;
            $resultReferents[] = [
                'context' => $fullName,
                'image' => $image,
                'subContext' => 'Votre référent'
            ];
        }

        $result = array_merge($result, $resultReferents);
        if (count($result) >= 10) {
            return array_slice($result, 0, 10);
        }

        $allEtps = DB::table('v_collaboration_cfp_etps')
            ->where('idCfp', Customer::idCustomer())
            ->orderBy('etp_name', 'ASC')
            ->pluck('idEtp');

        $referentCustomers = DB::table('employes as E')
            ->select('U.name', 'U.firstName', 'U.photo', 'C.customerName')
            ->join('users as U', 'U.id', 'E.idEmploye')
            ->join('role_users as RU', 'RU.user_id', 'U.id')
            ->join('customers as C', 'C.idCustomer', 'E.idCustomer')
            ->whereIn('E.idCustomer', $allEtps)
            ->whereIn('RU.role_id', [6, 9])
            ->whereNotNull('U.name')
            ->where(function ($query) use ($key) {
                $query->where('U.name', 'like', "%$key%")
                    ->orWhere('U.firstName', 'like', "%$key%");
            })
            ->get();

        $resultReferentCustomers = [];

        foreach ($referentCustomers as $referentCustomer) {
            $fullName = $referentCustomer->name . ' ' . $referentCustomer->firstName ?? '';
            $image = ($referentCustomer->photo) ? $digitalOcean . '/img/referents/' . $referentCustomer->photo : $user_icon;
            $resultReferentCustomers[] = [
                'context' => $fullName,
                'image' => $image,
                'subContext' => 'Référent de ' . $referentCustomer->customerName
            ];
        }

        $result = array_merge($result, $resultReferentCustomers);
        if (count($result) >= 10) {
            return array_slice($result, 0, 10);
        }

        $projectReferences = DB::table('v_projet_cfps')
            ->select('project_reference', 'module_image', 'module_name', 'dateDebut', 'dateFin')
            ->where('project_is_trashed', 0)
            ->where('project_is_active', 1)
            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer());
            })
            ->where('project_reference', 'like', "%$key%")
            ->get();

        $resultProjectReferences = [];

        foreach ($projectReferences as $project) {
            $image = ($project->module_image) ? $digitalOcean . '/img/modules/' . $project->module_image : $project_icon;

            $resultProjectReferences[] = [
                'context' => $project->project_reference,
                'image' => $image,
                'subContext' => 'Référence du projet le ' . $project->dateDebut . ' - ' . $project->dateFin
            ];
        }

        $result = array_merge($result, $resultProjectReferences);
        if (count($result) >= 10) {
            return array_slice($result, 0, 10);
        }

        $trainers = DB::table('cfp_formateurs as CF')
            ->select('U.name', 'U.firstName', 'U.photo')
            ->join('users as U', 'CF.idFormateur', 'U.id')
            ->where('CF.idCfp', '=', Customer::idCustomer())
            ->where(function ($query) use ($key) {
                $query->where('U.name', 'like', "%$key%")
                    ->orWhere('U.firstName', 'like', "%$key%");
            })
            ->get();

        $resultTrainers = [];

        foreach ($trainers as $trainer) {
            $fullName = $trainer->name . ' ' . $trainer->firstName ?? '';
            $image = ($trainer->photo) ? $digitalOcean . '/img/formateurs/' . $trainer->photo : $user_icon;

            $resultTrainers[] = [
                'context' => $fullName,
                'image' => $image,
                'subContext' => 'Votre formateur'
            ];
        }
        $result = array_merge($result, $resultTrainers);

        $cities = DB::table('v_liste_lieux')
            ->select('ville_name_coded')
            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->orWhere('idCustomer', Customer::idCustomer());
            })
            ->where('ville_name_coded', 'like', "%$key%")
            ->distinct()
            ->get();

        $resultCities = [];

        foreach ($cities as $city) {
            $resultCities[] = [
                'context' => $city->ville_name_coded,
                'image' => $project_icon,
                'subContext' => 'Votre projet dans cette ville'
            ];
        }

        $result = array_merge($result, $resultCities);

        $places = DB::table('v_liste_lieux')
            ->select('li_name')
            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->orWhere('idCustomer', Customer::idCustomer());
            })
            ->where('li_name', 'like', "%$key%")
            ->distinct()
            ->get();

        $resultPlaces = [];

        foreach ($places as $place) {
            $resultPlaces[] = [
                'context' => $place->li_name,
                'image' => $place_icon,
                'subContext' => 'Votre projet dans ce lieu'
            ];
        }

        $result = array_merge($result, $resultPlaces);

        $neighborhoods = DB::table('v_liste_lieux as V')
            ->join('lieux as L', 'V.idLieu', 'L.idLieu')
            ->select('L.li_quartier')
            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->orWhere('idCustomer', Customer::idCustomer());
            })
            ->where('L.li_quartier', 'like', "%$key%")
            ->distinct()
            ->get();

        $resultNeighborhoods = [];

        foreach ($neighborhoods as $neighborhood) {
            $resultNeighborhoods[] = [
                'context' => $neighborhood->li_quartier,
                'image' => $place_icon,
                'subContext' => 'Votre projet dans ce quartier'
            ];
        }

        $result = array_merge($result, $resultNeighborhoods);

        return response()->json(array_slice($result, 0, 10));
    }

    public function getProjectEtp(Request $request)
    {
        $projects = $this->projectService->getProjectEtp($request->key);

        return response()->json($projects, 200);
    }

    public function searchGeneralityEtp(Request $request)
    {
        $key = $request->key;

        $project = $this->projectService->countProjectEtp($key);

        $cfps = $this->customerService->countCfp($key);

        $employee = $this->employeeService->countEmployee($key);

        $referent = $this->referentService->countReferentEtp($key);

        $project_with_client = $this->getProjectWithCfp($key);

        return response()->json([
            'project' => $project,
            'cfp' => $cfps,
            'employee' => $employee,
            'referent' => $referent,
            'project_with_client' => $project_with_client,
        ]);
    }

    public function getProjectWithCfp($key)
    {
        $get_projects = DB::table('v_projet_cfps')
            ->select(
                'idCfp',
                'idCfp_inter',
                'cfp_name',
                DB::raw('COUNT(idProjet) as count_project')
            )
            ->where('idEtp', Customer::idCustomer())
            ->where('project_is_trashed', 0)
            ->whereIn('project_status', ['Planifié', 'En cours', 'Terminé', 'Cloturé', 'Annulé'])
            ->where('project_is_active', 1)
            ->where('cfp_name', 'like', "%{$key}%")
            ->groupBy('idCfp', 'idCfp_inter', 'cfp_name')
            ->get();

        return $get_projects->map(function ($project) {
            return [
                'type' => 'Projet avec ' . $project->cfp_name,
                'count' => (int) $project->count_project,
                'idCfp' => $project->idCfp ?: $project->idCfp_inter,
            ];
        });
    }
}
