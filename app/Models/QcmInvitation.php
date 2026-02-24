<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QcmInvitation extends Model
{
    use HasFactory;

    protected $table = 'qcm_invitations';
    protected $primaryKey = 'idInvitation';

    protected $fillable = [
        'idQCM',
        'idEmployeur',
        'idEmploye',
        'valid_from',
        'valid_until',
        'custom_message',
        'status'
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime'
    ];

    // Relation à un qcm
    public function qcm()
    {
        return $this->belongsTo(Qcm::class, 'idQCM', 'idQCM');
    }

    // Relation à un employe
    public function employee()
    {
        return $this->belongsTo(Employe::class, 'idEmploye', 'idEmploye');
    }

    // Relation à un employe campaign
    public function employee_campagne()
    {
        return $this->belongsTo(User::class, 'idEmploye', 'id');
    }

    // Relationship with employer
    public function employer()
    {
        return $this->belongsTo(User::class, 'idEmployeur', 'id');
    }

    // Relationship with invitation campaigns
    public function campaigns()
    {
        return $this->belongsToMany(QcmInvitCamp::class, 'qcm_invit_camp_invitations', 'invitation_id', 'invit_camp_id')
            ->withTimestamps();
    }

    // Validation rules
    public static $rules = [
        'idQCM' => 'required|exists:qcm,idQCM',
        'idEmploye' => 'required|exists:employes,idEmploye',
        'valid_from' => 'required|date|after:now',
        'valid_until' => 'required|date|after:valid_from',
        'custom_message' => 'nullable|string'
    ];

    /**
     * Fonction pour vérifier si la date limite de l'invitation a été atteinte
     */
    public function isExpired()
    {
        return now() > $this->valid_until;
    }

    /**
     * Fonction pour vérifier si l'invitation est encore valide
     */
    public function isValidNow()
    {
        $now = now();
        return $now >= $this->valid_from && $now <= $this->valid_until;
    }

    /**
     * Method for getting the invitations list whether it is pending, accepted or expired (v3)
     * with authentified user and include if the invitation is part of a campaign or not
     * 
     * @param $state, $openingDate, $closeDate
     */
    public function list_invitation_with_status($state = null, $openingDate = null, $closeDate = null)
    {
        // Get the authenticated user
        $user = auth()->user();
        // Modifions la requête pour inclure l'information sur la campagne
        $query = DB::table('v_invitations_sended')
            ->leftJoin('qcm_invit_camp_invitations', 'v_invitations_sended.idInvitation', '=', 'qcm_invit_camp_invitations.invitation_id')
            ->leftJoin('qcm_invit_camps', 'qcm_invit_camp_invitations.invit_camp_id', '=', 'qcm_invit_camps.idInvitCamp')
            ->select('v_invitations_sended.*', 'qcm_invit_camps.name as campaign_name')
            ->orderBy('v_invitations_sended.valid_until', 'asc'); 
        // Le reste de la logique de filtrage reste identique
        if ($user->hasRole('SuperAdmin')) {
            if ($state !== null) {
                $query->where('invitation_status', $state);
            }
            if ($openingDate !== null) {
                $query->whereDate('valid_from', $openingDate);
            }
            if ($closeDate !== null) {
                $query->whereDate('valid_until', $closeDate);
            }
        } elseif ($user->hasRole('Referent') || $user->hasRole('Cfp') || $user->hasRole('Formateur')) {
            // If the user is not a SuperAdmin but an entreprise, filter by their idEmploye
            $query->where('idEmployeur', $user->id);

            // Add filtering based on state, opening date, and close date
            if ($state !== null) {
                $query->where('invitation_status', $state);
            }

            if ($openingDate !== null) {
                $query->whereDate('valid_from', $openingDate);
            }

            if ($closeDate !== null) {
                $query->whereDate('valid_until', $closeDate);
            }
        } elseif ($user->hasRole('Employe')) {
            // If the user is not a SuperAdmin but an employe, filter by their idEmploye
            $query->where('idEmploye', $user->id);

            // Add filtering based on state, opening date, and close date
            if ($state !== null) {
                $query->where('invitation_status', $state);
            }

            if ($openingDate !== null) {
                $query->whereDate('valid_from', $openingDate);
            }

            if ($closeDate !== null) {
                $query->whereDate('valid_until', $closeDate);
            }
            /* dd($user->email);
            dd($query->get()); */
        } else {
            // Don't display anything if the user is none of user's roles above
            return [
                'invitations' => collect(), // Empty collection
                'nbrInvitation' => 0,      // Zero count
            ];
        }

        // Execute the query to get the list of invitations
        $list_invitations = $query->get();

        // Count the number of invitations
        $nbrInvitation = $list_invitations->count();

        // Return both the list and the count
        return [
            'invitations' => $list_invitations,
            'nbrInvitation' => $nbrInvitation,
        ];
    }

    /**
     * Method for getting the details of a specified invitation
     * 
     * @param $idInvitation
     */
    public function getOneInvitation($idInvitation)
    {
        $query = DB::table('v_invitations_sended')
            ->where('idInvitation', '=', $idInvitation)
            ->first();

        $invitation = $query;

        return $invitation;
    }

    /**
     * Method for checking the status of the invitation sended by mail
     * 
     * @param $idQCM, $userId
     */
    public function validateInvitation($idQCM, $userId)
    {
        // Validate invitation
        $now = now();

        // if the invitation is expired
        if ($now > $this->valid_until) {
            $this->update(['status' => 'expired']);
            return [
                'valid' => false,
                'redirect' => redirect()->route('qcm.invitations.index')->with('error', 'This invitation has expired.')
            ];
        }

        // if the invitation is not yet usable (i.e. the current date or time is not yet within the invitation's validity interval)
        if ($now < $this->valid_from) {
            return [
                'valid' => false,
                'redirect' => redirect()->route('qcm.invitations.index')->with('error', 'This invitation is not yet usable.')
            ];
        }

        // if the invitation is already accepted
        if ($this->status == "accepted") {
            return [
                'valid' => false,
                'redirect' => redirect()->route('qcm.invitations.index')->with('error', 'This invitation has already been used.')
            ];
        }

        // Check if invitation is for the correct QCM and employee
        if ($this->idQCM != $idQCM || $this->idEmploye != $userId) {
            return [
                'valid' => false,
                'redirect' => redirect()->route('qcm.invitations.index')->with('error', 'Invalid invitation.')
            ];
        }

        // Update invitation status
        $this->update(['status' => 'accepted']);

        return [
            'valid' => true,
            'redirect' => null
        ];
    }

    /**
     * Method for deleting an invitation
     * 
     * @param $idInvitation
     * @return bool
     */
    public function deleteInvitation($idInvitation)
    {
        $invitation = self::find($idInvitation);

        if ($invitation) {
            return $invitation->delete(); // Returns true if deletion is successful
        }

        return false; // Return false if invitation not found
    }

    /**
     * Method for getting list of employee whether for a company or a group of companies
     * for creating invitation form, for v3 of "create_invitation"
     */
    public static function getListEmployeCreateForm()
    {
        // Première requête
        $employeesFromView = DB::table('v_employe_alls')
            ->select([
                'idEmploye',
                'name',
                'firstName',
                'email'
            ])
            ->where('idCustomer', Auth::user()->id)
            ->where('role_id', 4)
            ->orderBy('name')
            ->get();

        // Deuxième requête
        $employeesFromUnion = DB::table('v_union_emp_grps')
            ->select([
                'idEmploye',
                'emp_name as name',
                'emp_firstname as firstName',
                'emp_email as email'
            ])
            ->where('idEntrepriseParent', Auth::user()->id)
            ->orderBy('name', 'asc')
            ->get();

        // Combiner les résultats des deux requêtes
        return $employeesFromView->merge($employeesFromUnion);
    }

    /**
     * Method for getting list of employee whether for a company or a group of companies
     * for "store_invitation" v4 method
     * 
     * @param array $employeeIds
     */
    public static function getListEmployeStore(array $employeeIds)
    {
        if (Auth::user()->hasRole('Referent')) {
            $enterpriseId = Auth::user()->id;

            // Première requête
            $employeesFromView = DB::table('v_employe_alls')
                ->select('idEmploye', 'name', 'firstName', 'email')
                ->whereIn('idEmploye', $employeeIds)
                ->where('idCustomer', $enterpriseId);
            // Deuxième requête
            $employeesFromUnion = DB::table('v_union_emp_grps')
                ->select('idEmploye', 'emp_name as name', 'emp_firstname as firstName', 'emp_email as email')
                ->whereIn('idEmploye', $employeeIds)
                ->where('idEntrepriseParent', $enterpriseId);

            // Combiner les résultats des deux requêtes
            return $employeesFromView->union($employeesFromUnion)->get();
        }
        elseif (Auth::user()->hasRole('Cfp')) {
            $idCfp = Auth::user()->id;

            $employeesFromView = DB::table('v_apprenant_etp_alls')
                ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_email')
                ->whereIn('idEmploye', $employeeIds)
                ->where('idCfp', $idCfp);

            $employeesFromUnion = DB::table('v_apprenant_union')
            ->select('idEmploye', 'emp_name as name', 'emp_firstname as firstName', 'emp_email as email')
            ->whereIn('idEmploye', $employeeIds)
            ->where('idCfp', $idCfp);

            return $employeesFromView->union($employeesFromUnion)->get();
        }
        elseif (Auth::user()->hasRole('Formateur')) {
            $idFormateur = Auth::user()->id;
            $Cfp = DB::table('cfp_formateurs')->where('idFormateur', $idFormateur)->first();

            $employeesFromView = DB::table('v_apprenant_etp_alls')
                ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_email')
                ->whereIn('idEmploye', $employeeIds)
                ->where('idCfp', $Cfp->idCfp);

            $employeesFromUnion = DB::table('v_apprenant_union')
            ->select('idEmploye', 'emp_name as name', 'emp_firstname as firstName', 'emp_email as email')
            ->whereIn('idEmploye', $employeeIds)
            ->where('idCfp', $Cfp->idCfp);

            return $employeesFromView->union($employeesFromUnion)->get();
        }
    }

    /**
     * Method for getting data for the QCM invitations dashboard
     * 
     * @param int|null $enterpriseId
     * @return array Returns ['data' => Collection, 'total' => int]
     */
    public function getDatasInvitationsDashboard($enterpriseId = null)
    {
        $baseQuery = self::query(); # Initialize base query

        // Apply enterprise filter to base query if provided
        if ($enterpriseId !== null) {
            $baseQuery->where('qcm_invitations.idEmployeur', $enterpriseId);
        }

        // Get filtered count
        $totalCount = $baseQuery->count();

        // Build the detailed query using the same base conditions
        $query = clone $baseQuery;
        $query->select([
            'qcm_invitations.idInvitation',
            'qcm_invitations.idQCM',
            'qcm.intituleQcm as qcm_titre',
            'qcm_invitations.idEmployeur',
            'customers.customerName as employeur_nom',
            'qcm_invitations.idEmploye',
            'users.name as employe_nom',
            'users.firstName as employe_prenom',
            'qcm_invitations.valid_from',
            'qcm_invitations.valid_until',
            'qcm_invitations.custom_message',
            'qcm_invitations.status',
            DB::raw('TIMESTAMPDIFF(DAY, NOW(), qcm_invitations.valid_until) as jours_restants'),
            'qcm_invitations.created_at',
            'qcm_invitations.updated_at',
            'qcm_invit_camps.idInvitCamp as campaign_id',
            'qcm_invit_camps.name as campaign_name',
            'creator.name as campaign_creator_name',
            'creator.firstName as campaign_creator_firstName'
        ])
            ->leftJoin('qcm', 'qcm_invitations.idQCM', '=', 'qcm.idQCM')
            ->leftJoin('users', 'qcm_invitations.idEmploye', '=', 'users.id')
            ->leftJoin('customers', 'qcm_invitations.idEmployeur', '=', 'customers.idCustomer')
            ->leftJoin('qcm_invit_camp_invitations', 'qcm_invitations.idInvitation', '=', 'qcm_invit_camp_invitations.invitation_id') # Add joins for campaign information
            ->leftJoin('qcm_invit_camps', 'qcm_invit_camp_invitations.invit_camp_id', '=', 'qcm_invit_camps.idInvitCamp') # Add joins for campaign information
            ->leftJoin('users as creator', 'qcm_invit_camps.created_by', '=', 'creator.id'); # Add joins for campaign information

        // Sort by creation date
        $query->orderBy('qcm_invitations.created_at', 'desc');

        // Get the results
        $results = $query->get()->map(function ($invitation) {
            // Store the original status
            $original_status = match ($invitation->status) {
                'pending' => 'En attente',
                'accepted' => 'Acceptée',
                'expired' => 'Expirée',
                default => 'Inconnu',
            };

            // Initialize statut_affiche with the original status
            $invitation->statut_affiche = $original_status;

            // Update status display based on actual expiration date
            if ($invitation->status == 'pending' && Carbon::now() > $invitation->valid_until) {
                $invitation->statut_affiche = 'Expirée';
            } elseif ($invitation->jours_restants > 0 && $invitation->jours_restants <= 3) {
                $invitation->statut_affiche .= ' (Urgent)';
            }

            // Format campaign information
            $invitation->campaign_info = null;
            if ($invitation->campaign_id) {
                $invitation->campaign_info = [
                    'id' => $invitation->campaign_id,
                    'name' => $invitation->campaign_name,
                    'creator' => trim($invitation->campaign_creator_firstName . ' ' . $invitation->campaign_creator_name)
                ];
            }

            // Remove redundant campaign fields
            unset(
                $invitation->campaign_id,
                $invitation->campaign_name,
                $invitation->campaign_creator_name,
                $invitation->campaign_creator_firstName
            );

            return $invitation;
        });

        // Return both the data and the total count
        return [
            'data' => $results,
            'total' => $totalCount
        ];
    }
}
