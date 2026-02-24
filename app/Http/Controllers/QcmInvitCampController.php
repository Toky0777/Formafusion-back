<?php

namespace App\Http\Controllers;

use App\Mail\QcmInvitation;
use App\Models\Qcm;
use App\Models\QcmInvitation as ModelsQcmInvitation;
use App\Models\QcmInvitCamp;
use App\Services\Qcm\QcmNavigationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;


class QcmInvitCampController extends Controller
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
     * Determine layout based on user roles with priority and fallback mechanisms (reusable method)
     */
    private function determineLayoutBasedOnRole()
    {
        $user = Auth::user();

        // Define layouts with priority order
        $layoutPriorities = [
            'SuperAdmin' => 'layouts.masterAdmin',
            'Admin' => 'layouts.masterAdmin',
            'Cfp' => 'layouts.master',
            'Formateur' => 'layouts.masterForm',
            'Formateur interne' => 'layouts.masterFormInterne',
            'EmployeCfp' => 'layouts.masterEmpCfp',
            'Employe' => 'layouts.masterEmp',
            'EmployeEtp' => 'layouts.masterEmp',
            'Particulier' => 'layouts.masterParticulier',
            'Referent' => 'layouts.masterEtp'
        ];

        // Get user's active roles
        $userRoles = $user->roles->pluck('roleName')->toArray();

        // Find the first matching layout based on role priority
        foreach ($layoutPriorities as $role => $layout) {
            if (in_array($role, $userRoles)) {
                return $layout;
            }
        }

        // Fallback to default layout if no specific role matches
        return 'layouts.master';
    }

    /**
     * Get the payment details for a campaign.
     * 
     * @param $idCampaign
     */
    private function getCreditsForCampaign($idCampaign)
    {
        $campaignExists = DB::table('v_invitations_sended')
            ->where('idInvitCamp', '=', $idCampaign)
            ->exists();

        if (!$campaignExists) {
            return [
                'totalCredits' => 0,
                'paidCredits' => 0,
                'remainingCredits' => 0,
            ];
        }

        $totalCredits = DB::table('v_invitations_sended')
            ->where('idInvitCamp', '=', $idCampaign)
            ->sum('prixUnitaire');

        $paidCredits = DB::table('v_invitations_sended')
            ->where('idInvitCamp', '=', $idCampaign)
            ->where('invitation_status', '=', 'accepted')
            ->sum('prixUnitaire');

        $remainingCredits = DB::table('v_invitations_sended')
            ->where('idInvitCamp', '=', $idCampaign)
            ->whereIn('invitation_status', ['pending', 'expired'])
            ->sum('prixUnitaire');

        return [
            'totalCredits' => $totalCredits,
            'paidCredits' => $paidCredits,
            'remainingCredits' => $remainingCredits,
        ];
    }

    /**
     * Method for the index of invitation campaign
     */
    public function index_campaign()
    {
        $user = Auth::user(); // Get the authenticated user
        $extends_containt = $this->navigationService->determineLayout(); // Variable pour stocker les extends selon l'utilisateur connecté

        $campaigns = QcmInvitCamp::with(['creator', 'invitations.employee_campagne', 'invitations.qcm'])
            ->where('created_by', Auth::user()->id)
            ->latest()
            ->paginate(10);

        // Map payment details to campaigns
        foreach ($campaigns as $campaign) {
            $paymentDetails = $this->getCreditsForCampaign($campaign->idInvitCamp);
            $campaign->paymentDetails = $paymentDetails;
        }

        return response()->json([
            'extends_containt' => $extends_containt,
            'campaigns' => $campaigns,
            'user' => $user,
        ]);
    }

    /**
     * Campaign Naming Step
     */
    public function stepOne()
    {
        $campaignName = Session::get('campaign_data.name', '');

        return response()->json([
            'extends_containt' => $this->determineLayoutBasedOnRole(),
            'campaignName' => $campaignName
        ]);
        
    }

    /**
     * Save draft campaign name dynamically
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveDraftName(Request $request)
    {
        // Get campaign data from session
        $campaignData = Session::get('campaign_data', []);

        // Update the name with what's being typed
        $campaignData['name'] = $request->input('campaign_name');

        // Save back to session
        Session::put('campaign_data', $campaignData);

        return response()->json(['status' => 'success']);
    }

    /**
     * Store Campaign Name
     * 
     * @param $request
     */
    public function storeStepOne(Request $request)
    {
        $validated = $request->validate([
            'campaign_name' => 'required|string|max:255'
        ]);

        $campaignData = Session::get('campaign_data', []);
        $campaignData['name'] = $validated['campaign_name'];
        Session::put('campaign_data', $campaignData);

        return response()->json([
            'status' => 'success',
            'nextRoute' => route('qcm.invitation.campaign.step-two')
        ]);
    }

    /**
     * QCM Selection Step
     */
    public function stepTwo()
    {
        $qcms = Qcm::where('statut', 1)->get();
        $selectedQcm = Session::get('campaign_data.qcm_id');
        
        return response()->json([
            'extends_containt' => $this->determineLayoutBasedOnRole(),
            'qcms' => $qcms,
            'selectedQcm' => $selectedQcm
        ]);
    }

    /**
     * Store QCM Selection
     * 
     * @param $request
     */
    public function storeStepTwo(Request $request)
    {
        $validated = $request->validate([
            'idQCM' => 'nullable|exists:qcm,idQCM'
        ]);

        $campaignData = Session::get('campaign_data', []);
        $campaignData['qcm_id'] = $validated['idQCM'] ? $validated['idQCM'] : null;
        Session::put('campaign_data', $campaignData);

        return response()->json([
            'status' => 'success',
            'nextRoute' => route('qcm.invitation.campaign.step-three')
        ]);
    }

    /**
     * Employee Selection Step
     */
    public function stepThree()
    {
        $employees = ModelsQcmInvitation::getListEmployeCreateForm();
        $selectedEmployees = Session::get('campaign_data.employee_ids', []);

        return response()->json([
            'extends_containt' => $this->determineLayoutBasedOnRole(),
            'employees' => $employees,
            'selectedEmployees' => is_array($selectedEmployees) ? $selectedEmployees : []
        ]);
    }

    /**
     * Store Employee Selection
     * 
     * @param $request
     */
    public function ajaxUpdateEmployees(Request $request)
    {
        $selectedEmployees = $request->input('selectedEmployees', []);
        $campaignData = Session::get('campaign_data', []);
        $campaignData['employee_ids'] = !empty($selectedEmployees) ? $selectedEmployees : [];
        Session::put('campaign_data', $campaignData);

        return response()->json([
            'status' => 'success'
        ]);
    }

    /**
     * Invitation Details Step
     */
    public function stepFour()
    {
        $validFrom = Session::get('campaign_data.valid_from');
        $validUntil = Session::get('campaign_data.valid_until');
        $customMessage = Session::get('campaign_data.custom_message');

        return response()->json([
            'extends_containt' => $this->determineLayoutBasedOnRole(),
            'validFrom' => $validFrom,
            'validUntil' => $validUntil,
            'customMessage' => $customMessage
        ]);
    }

    /**
     * Save step four data to session via AJAX
     * 
     * @param Request $request
     */
    public function saveStepFourData(Request $request)
    {
        $validKeys = ['valid_from', 'valid_until', 'custom_message'];

        $data = $request->validate([
            'valid_from' => 'sometimes|date',
            'valid_until' => 'sometimes|date',
            'custom_message' => 'sometimes|nullable|string|max:1000'
        ]);

        $campaignData = Session::get('campaign_data', []);

        foreach ($data as $key => $value) {
            if (in_array($key, $validKeys)) {
                $campaignData[$key] = $value;
            }
        }

        Session::put('campaign_data', $campaignData);

        return response()->json([
            'status' => 'success',
            'message' => 'Data saved to session'
        ]);
    }

    /**
     * Final Campaign Creation (v2)
     * 
     * @param $request
     */
    public function createCampaign(Request $request)
    {
        // Retrieve enterprise ID securely
        $enterpriseId = Auth::id(); // slight improvement over ->user()->id

        $validated = $request->validate([
            'valid_from' => ['required', 'date', 'after_or_equal:today'],
            'valid_until' => ['required', 'date', 'after:valid_from'],
            'custom_message' => 'nullable|string|max:1000'
        ], [
            'valid_until.after' => 'The valid until date must be after the valid from date.',
            'valid_from.after_or_equal' => 'The valid from date must be today or in the future.'
        ]);

        // Retrieve campaign data from session
        $campaignData = Session::get('campaign_data', []);
        // Comprehensive validation check with more descriptive error messages
        $missingData = [];
        if (empty($campaignData['name'])) $missingData[] = 'Campaign Name';
        if (empty($campaignData['qcm_id'])) $missingData[] = 'QCM Selection';
        if (empty($campaignData['employee_ids'])) $missingData[] = 'Employee Selection';

        if (!empty($missingData)) {
            return response()->json([
                'status' => 400,
                'message' => 'Please complete the following steps: ' . implode(', ', $missingData)
            ]);
        }

        try {
            // Find the QCM
            $qcm = Qcm::findOrFail($campaignData['qcm_id']);

            // Create Campaign
            $campaign = QcmInvitCamp::createCampaign($campaignData['name']);

            // Process and track invitations
            $result = $this->processInvitations(
                $campaign,
                $campaignData['employee_ids'],
                $qcm,
                $enterpriseId,
                $validated
            );

            // Clear session data
            Session::forget('campaign_data');

            // Redirect with success message
            return response()->json([
                'status' => $result['status'],
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Campaign Creation Failed: ' . $e->getMessage());

            // Redirect back with error message
            return response()->json([
                'status' => 500,
                'message' => 'An unexpected error occurred. Please try again.'
            ]);
        }
    }

    /**
     * Process campaign invitations
     * 
     * @param $campaign, $employeeIds, $qcm, $enterpriseId, $validationData
     */
    private function processInvitations($campaign, $employeeIds, $qcm, $enterpriseId, $validationData)
    {
        $successCount = 0;
        $failCount = 0;
        $emailSkippedCount = 0;

        $employees = ModelsQcmInvitation::getListEmployeStore($employeeIds);
        // dd($employees);
        foreach ($employees as $employee) {
            if (empty($employee->email)) {
                $emailSkippedCount++;
                continue;
            }

            try {
                $invitation = ModelsQcmInvitation::create([
                    'idQCM' => $qcm->idQCM,
                    'idEmployeur' => $enterpriseId,
                    'idEmploye' => $employee->idEmploye,
                    'valid_from' => $validationData['valid_from'],
                    'valid_until' => $validationData['valid_until'],
                    'custom_message' => $validationData['custom_message'] ?? null,
                    'status' => 'pending'
                ]);
                // dd($campaign->invitations());
                $campaign->invitations()->attach($invitation->idInvitation);

                $mail = new QcmInvitation($invitation, $employee, $qcm);
                //Mail::send($mail);

                $successCount++;
            } catch (\Exception $e) {
                Log::error('Invitation creation failed: ' . $e->getMessage());
                $failCount++;
            }
        }

        $status = $failCount > 0 ? 'warning' : 'success';
        $message = $this->buildResultMessage($campaign->name, $successCount, $emailSkippedCount, $failCount);

        return [
            'status' => $status,
            'message' => $message
        ];
    }

    /**
     * Build result message for campaign creation
     * 
     * @param $campaignName, $successCount, $emailSkippedCount, $failCount
     */
    private function buildResultMessage($campaignName, $successCount, $emailSkippedCount, $failCount)
    {
        $message = "Campaign '{$campaignName}' created.";
        $message .= " Sent {$successCount} invitation(s) successfully.";

        if ($emailSkippedCount > 0) {
            $message .= " Skipped {$emailSkippedCount} invitation(s) due to missing email.";
        }

        if ($failCount > 0) {
            $message .= " Failed to send {$failCount} invitation(s).";
        }

        return $message;
    }

    // Go back methods
    public function backToStepOne()
    {
        return redirect()->route('qcm.invitation.campaign.step-one');
    }

    public function backToStepTwo()
    {
        return redirect()->route('qcm.invitation.campaign.step-two');
    }

    public function backToStepThree()
    {
        return redirect()->route('qcm.invitation.campaign.step-three');
    }
    // Go back methods

    /**
     * Method for deleting a campaign
     * 
     * @param $id (id de la campagne)
     */
    public function destroy($id)
    {
        $model = new QcmInvitCamp();
        $result = $model->deleteCampaign($id);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Campagne supprimée avec succès'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la suppression'
        ], 500);
    }

    /**
     * Method for getting one invitation (v2)
     * 
     * @param $id (id de l'invitation)
     */
    public function getInvitationDetails($id)
    {
        $QcmInvitCamp = QcmInvitCamp::getFormattedInvitationDetails($id);

        return response()->json([
            'QcmInvitCamp' => $QcmInvitCamp
        ]);
    }
}
