<?php

namespace App\Services\Qcm;

use Illuminate\Support\Facades\Auth;

/**
 * Navigation service, this service is responsible for handling navigation actions
 * It can use be used to determine the layout based on the user's role ; so it's not limited to the qcm part
 */
class QcmNavigationService
{
    /**
     * Determine the layout based on the user's role
     */
    public function determineLayout(): ?string
    {
        if (!Auth::check()) {
            return 'layouts.masterGuest';
        }

        $roleLayouts = [
            'Formateur' => 'layouts.masterForm',
            'Formateur interne' => 'layouts.masterFormInterne',
            'Particulier' => 'layouts.masterParticulier',
            'EmployeCfp' => 'layouts.masterEmpCfp',
            'Employe' => 'layouts.masterEmp',
            'EmployeEtp' => 'layouts.masterEmp',
            'Cfp' => 'layouts.master',
            'Admin' => 'layouts.masterAdmin',
            'SuperAdmin' => 'layouts.masterAdmin',
            'Referent' => 'layouts.masterEtp'
        ];

        foreach ($roleLayouts as $role => $layout) {
            if (Auth::user()->hasRole($role)) {
                return $layout;
            }
        }

        return 'layouts.masterGuest';
    }

    /**
     * Handle navigation actions
     * 
     * @param int $currentIndex The current question index, int $totalQuestions The total number of questions, string $action The navigation action
     * 
     * @return int The new question index
     */
    public function handleNavigation(int $currentIndex, int $totalQuestions, string $action): int
    {
        return match ($action) {
            'next' => min($currentIndex + 1, $totalQuestions - 1),
            'previous' => max($currentIndex - 1, 0),
            default => $currentIndex
        };
    }
}
