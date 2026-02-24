<?php

namespace App\Services\Qcm;

use Carbon\Carbon;

class QcmSessionService
{
    /**
     * Initialize the timer
     * 
     * @param int $duration The duration of the timer in seconds
     * 
     * @return array
     */
    public function initializeTimer(int $duration): array
    {
        $timer = [
            'start_time' => now(),
            'duration' => $duration,
            'end_time' => now()->addSeconds($duration)
        ];
        session(['qcm_timer' => $timer]);
        return $timer;
    }

    /**
     * Initialize the progress
     * 
     * @return array
     */
    public function initializeProgress(): array
    {
        $progress = [
            'current_index' => 0,
            'responses' => []
        ];
        session(['qcm_progress' => $progress]);
        return $progress;
    }

    /**
     * Validate the timer
     * 
     * @return bool
     */
    public function validateTimer(): bool
    {
        $timer = session('qcm_timer');
        return now()->gt(Carbon::parse($timer['end_time']));
    }

    /**
     * Update the progress
     * 
     * @param string $questionId The question ID
     * @param string|null $responseId The response ID
     * @param int $currentIndex The current index
     */
    public function updateProgress(string $questionId, ?string $responseId, int $currentIndex): void
    {
        $progress = session('qcm_progress', ['responses' => [], 'current_index' => 0]);

        // Save the response for the current question
        if ($responseId !== null) {
            $progress['responses'][$questionId] = $responseId;
        }

        // Update current index
        $progress['current_index'] = $currentIndex;

        // Save back to session
        session(['qcm_progress' => $progress]);
    }

    /**
     * Save the response
     * 
     * @param string $questionId The question ID
     * @param string|null $responseId The response ID
     */
    public function saveResponse(string $questionId, ?string $responseId): void
    {
        $progress = session('qcm_progress', ['responses' => [], 'current_index' => 0]);
        $progress['responses'][$questionId] = $responseId;
        session(['qcm_progress' => $progress]);
    }

    /**
     * Get the current progress
     * 
     * @return array
     */
    public function getCurrentProgress(): array
    {
        return session('qcm_progress', ['responses' => [], 'current_index' => 0]);
    }
}
