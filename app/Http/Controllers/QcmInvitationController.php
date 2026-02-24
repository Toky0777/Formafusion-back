<?php

namespace App\Http\Controllers;

use App\Models\Qcm;
use App\Models\QcmInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Mail\QcmInvitation as QcmInvitationMail;
use App\Models\User;
use App\Services\Qcm\QcmNavigationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class QcmInvitationController extends Controller
{
    # Services part added 18-02-2025
    private QcmNavigationService $navigationService;

    public function __construct(
        QcmNavigationService $navigationService
    ) {
        $this->navigationService = $navigationService;
    }
    # Services part added 18-02-2025

    /**
     * Function leading to the view for sending mail to the employee (v4)
     */
    public function create_invitation()
    {
        $extends_containt = $this->navigationService->determineLayout(); // Variable pour stocker les extends selon l'utilisateur connecté

        $qcms = Qcm::getAllPublicQcms();

        // Appel de la méthode pour récupérer les employés
        $employees = QcmInvitation::getListEmployeCreateForm();

        return response()->json([
            'qcms' => $qcms,
            'employees' => $employees,
            'extends_containt' => $extends_containt,
        ]);
    }

    /**
     * Function for storing the invitation(s) (v4)
     * 
     * @param $request
     */
    public function store_invitation(Request $request)
    {
        $enterpriseId = Auth::user()->id;

        // Validate the employees belong to the enterprise
        $employeeIds = is_array($request->idEmploye) ? $request->idEmploye : [$request->idEmploye];
        // Appel de la méthode pour récupérer les employés
        $employees = QcmInvitation::getListEmployeStore($employeeIds);

        if ($employees->count() !== count($employeeIds)) {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid employee selection.'
            ]);
        }

        $validated = $request->validate([
            'idQCM' => 'required|exists:qcm,idQCM',
            'idEmploye' => 'required|array',
            'idEmploye.*' => [
                'required',
                function ($attribute, $value, $fail) use ($enterpriseId) {
                    $existsInView = DB::table('v_employe_alls')
                        ->where('idEmploye', $value)
                        ->where('idCustomer', $enterpriseId)
                        ->exists();

                    $existsInUnion = DB::table('v_union_emp_grps')
                        ->where('idEmploye', $value)
                        ->where('idEntrepriseParent', $enterpriseId)
                        ->exists();

                    if (!($existsInView || $existsInUnion)) {
                        $fail("The selected employee (ID: $value) is invalid.");
                    }
                }
            ],
            'valid_from' => [
                'required',
                'date'
            ],
            'valid_until' => [
                'required',
                'date',
                'after:valid_from'
            ],
            'custom_message' => 'nullable|string|max:1000'
        ], [
            'valid_until.after' => 'The valid until date must be at least 1 hour from now.',
            'valid_from.after_or_equal' => 'The valid from date must be now or in the future.'
        ]);

        $qcm = Qcm::find($validated['idQCM']);
        $successCount = 0;
        $failCount = 0;
        $emailSkippedCount = 0;

        foreach ($employees as $employee) {
            // Créer l'invitation pour chaque employé
            $invitation = QcmInvitation::create([
                'idQCM' => $validated['idQCM'],
                'idEmployeur' => $enterpriseId,
                'idEmploye' => $employee->idEmploye,
                'valid_from' => $validated['valid_from'],
                'valid_until' => $validated['valid_until'],
                'custom_message' => $validated['custom_message'] ?? null,
                'status' => 'pending'
            ]);

            // Vérifier si l'email est vide
            if (empty($employee->email)) {
                Log::info('Skipping email for employee ID: ' . $employee->idEmploye . ' (No email address)');
                $emailSkippedCount++;
                continue;
            }

            try {
                // Créer et envoyer le mail
                $mail = new QcmInvitationMail($invitation, $employee, $qcm);
                Mail::send($mail);
                $successCount++;
            } catch (\Exception $e) {
                Log::error('Failed to send QCM invitation email to ' . $employee->email . ': ' . $e->getMessage());
                $failCount++;
            }
        }

        // Construire le message de résultat
        $message = "Sent $successCount invitation(s) successfully.";

        if ($emailSkippedCount > 0) {
            $message .= " Skipped $emailSkippedCount invitation(s) due to missing email.";
        }

        if ($failCount > 0) {
            $message .= " Failed to send $failCount invitation(s).";

            return response()->json([
                'status' => 400,
                'message' => $message
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => $message
        ]);
    }

    /**
     * Function leading to the view with filter that display all the invitation sended (v2)
     * 
     * @param $request
     */
    public function index_invitation(Request $request)
    {
        $extends_containt = $this->navigationService->determineLayout(); // Variable pour stocker les extends selon l'utilisateur connecté

        $user = Auth::user();

        // Get filter parameters from request
        $state = $request->input('state', null);
        $openingDate = $request->input('opening_date', null);
        $closeDate = $request->input('close_date', null);

        // Call the method to get invitations
        $invitationService = new QcmInvitation(); // Assuming you have a service class
        $result = $invitationService->list_invitation_with_status(
            $state,
            $openingDate,
            $closeDate
        );

        // Prepare filter options
        $statusOptions = [
            'pending' => 'Pending',
            'accepted' => 'Accepted',
            'expired' => 'Expired',
            // Add more status options as needed
        ];

        return response()->json([
            'extends_containt' => $extends_containt,
            'user' => $user,
            'invitations' => $result['invitations'],
            'nbrInvitation' => $result['nbrInvitation'],
            'statusOptions' => $statusOptions,
            'currentState' => $state,
            'currentOpeningDate' => $openingDate,
            'currentCloseDate' => $closeDate
        ]);
    }


    /**
     * Function leading the view displaying the email sended to the user with the details of the mail (v2)
     * 
     * @param $id (id of the invitation)
     */
    public function getInvitation($id)
    {
        $extends_containt = $this->navigationService->determineLayout(); // Variable pour stocker les extends selon l'utilisateur connecté
        $user = Auth::user();

        $invitation = new QcmInvitation();
        // Retrieve the invitation details
        $one_invitation = $invitation->getOneInvitation($id);

        // Check if the invitation exists
        if (!$one_invitation) {
            return response()->json([
                'status' => 404,
                'message' => 'Invitation not found.'
            ], 404);
        }

        // Assuming you have a way to get the employee and QCM details
        $employee = User::find($one_invitation->idEmploye); // Adjust according to your model
        $qcm = Qcm::find($one_invitation->idQCM); // Adjust according to your model

        // Pass the data to the view
        return response()->json([
            'extends_containt' => $extends_containt,
            'user' => $user,
            'invitation' => $one_invitation,
            'employee' => $employee,
            'qcm' => $qcm,
        ]);
    }

    /**
     * Function for deleting a qcm invitation
     * 
     * @param $id
     */
    public function destroyInvitationQcm($id)
    {
        $invitation = new QcmInvitation();

        if ($invitation->deleteInvitation($id)) {
            return response()->json([
                'status' => 200,
                'message' => "Invitation deleted successfully."
            ]);
        }

        return response()->json([
            'status' => 500,
            'message' => "Failed to delete invitation."
        ]);
    }

    /**
     * Function for invitations dashboard (v2)
     * 
     * @param $id (id of the entreprise)
     */
    public function dashboardInvitations()
    {
        $extends_containt = $this->navigationService->determineLayout(); // Variable pour stocker les extends selon l'utilisateur connecté
        $user = Auth::user();

        // Get invitations based on user role
        $invitation = new QcmInvitation();
        $invitationsData = match (true) {
            $user->hasRole('SuperAdmin') => $invitation->getDatasInvitationsDashboard(),
            $user->hasRole('Referent') => $invitation->getDatasInvitationsDashboard($user->id),
            default => abort(403, 'Unauthorized')
        };

        return response()->json([
            'extends_containt' => $extends_containt,
            'user' => $user,
            'invitations' => $invitationsData['data'],
            'totalInvitations' => $invitationsData['total']
        ]);
    }
}
