<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QcmInvitCamp extends Model
{
    use HasFactory;

    protected $table = 'qcm_invit_camps';
    protected $primaryKey = 'idInvitCamp';

    protected $fillable = [
        'name',
        'created_date',
        'created_by'
    ];

    protected $dates = [
        'created_date'
    ];

    // Relationship with the user who created the campaign
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relationship with invitations
    public function invitations()
    {
        return $this->belongsToMany(
            QcmInvitation::class,
            'qcm_invit_camp_invitations',
            'invit_camp_id',
            'invitation_id'
        );
    }

    /**
     * Method for creating a campaign
     * 
     * @param $name
     */
    public static function createCampaign($name)
    {
        return self::create([
            'name' => $name,
            'created_date' => now()->toDateString(),
            'created_by' => auth()->id()
        ]);
    }

    /**
     * Method for deleting an invitation campaign
     * 
     * @param $idCampaign
     */
    public function deleteCampaign($idCampaign)
    {
        // Démarrer une transaction
        DB::beginTransaction();

        try {
            // 1. Récupérer toutes les invitations liées à cette campagne
            $invitationIds = DB::table('qcm_invit_camp_invitations')
                ->where('invit_camp_id', $idCampaign)
                ->pluck('invitation_id');

            // 2. Supprimer les liens dans la table pivot
            DB::table('qcm_invit_camp_invitations')
                ->where('invit_camp_id', $idCampaign)
                ->delete();

            // 3. Supprimer les invitations associées
            if ($invitationIds->count() > 0) {
                DB::table('qcm_invitations')
                    ->whereIn('idInvitation', $invitationIds)
                    ->delete();
            }

            // 4. Supprimer la campagne
            $deleted = DB::table('qcm_invit_camps')
                ->where('idInvitCamp', $idCampaign)
                ->delete();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Campagne supprimée avec succès',
                'deletedInvitations' => $invitationIds->count()
            ];
        } catch (Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression de la campagne',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Fetch and format invitation details by ID, including payment status.
     *
     * @param int $idInvitation
     * @return \Illuminate\Http\JsonResponse
     */
    public static function getFormattedInvitationDetails($idInvitation)
    {
        try {
            // Fetch invitation details
            $invitation = DB::table('v_invitations_sended')
                ->where('idInvitation', $idInvitation)
                ->first();

            // If invitation not found
            if (!$invitation) {
                return response()->json([
                    'error' => true,
                    'message' => 'Invitation not found'
                ], 404);
            }

            // Determine payment status
            $isPaid = $invitation->invitation_status === 'accepted';

            // Format and return the invitation details
            return response()->json([
                'employe_nom' => $invitation->employe_nom ?? 'N/A',
                'employe_prenom' => $invitation->employe_prenom ?? '',
                'employe_email' => $invitation->employe_email ?? 'N/A',
                'nom_entreprise' => $invitation->nom_entreprise ?? 'N/A',
                'employeur_email' => $invitation->employeur_email ?? 'N/A',
                'intituleQCM' => $invitation->intituleQCM ?? 'N/A',
                'descriptionQCM' => $invitation->descriptionQCM ?? 'N/A',
                'credits' => $invitation->prixUnitaire ?? 'N/A',
                'invitation_status' => $invitation->invitation_status ?? 'N/A',
                'payment_status' => $isPaid ? 'Paid' : 'Not Paid',
                'valid_from' => $invitation->valid_from,
                'valid_until' => $invitation->valid_until,
                'custom_message' => $invitation->custom_message ?? 'N/A'
            ]);
        } catch (Exception $e) {
            Log::error('Invitation Details Error: ' . $e->getMessage());
            return response()->json(['error' => true, 'message' => 'Failed to retrieve invitation details', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Method for getting the total amount of credits to pay for a campaign and all invitations related to it
     * With the paid credits and the remaining credits to pay
     * 
     * @param $idCampaign
     */
    public function getCreditsToPayForCampaign($idCampaign)
    {
        // Check if any invitations exist for the campaign
        $campaignExists = DB::table('v_invitations_sended')
            ->where('idInvitCamp', '=', $idCampaign)
            ->exists();

        if (!$campaignExists) {
            return response()->json(null, 404); // Return null with a 404 status code
        }

        // List all invitations linked to the campaign
        $campaigns = DB::table('v_invitations_sended')
            ->where('idInvitCamp', '=', $idCampaign)
            ->get();

        // Calculate credits that are paid (accepted)
        $paidCredits = DB::table('v_invitations_sended')
            ->where('idInvitCamp', '=', $idCampaign)
            ->where('invitation_status', '=', 'accepted')
            ->sum('prixUnitaire');

        // Calculate credits that are remaining (pending or expired)
        $remainingCredits = DB::table('v_invitations_sended')
            ->where('idInvitCamp', '=', $idCampaign)
            ->whereIn('invitation_status', ['pending', 'expired'])
            ->sum('prixUnitaire');

        // Total amount of credits for the campaign
        $totalCredits = DB::table('v_invitations_sended')
            ->where('idInvitCamp', '=', $idCampaign)
            ->sum('prixUnitaire');

        // Return JSON response with calculated data
        return response()->json([
            'campaigns' => $campaigns,
            'totalCredits' => $totalCredits,
            'paidCredits' => $paidCredits,
            'remainingCredits' => $remainingCredits,
        ]);
    }
}
