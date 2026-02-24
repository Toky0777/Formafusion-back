<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CommissionSettings extends Model
{
    use HasFactory;

    protected $table = 'commissions_settings';
    protected $primaryKey = 'idCommissionSetting';

    protected $fillable = [
        'payment_type',
        'commission_rate',
        'currency'
    ];

    /**
     * Create a new commission setting
     *
     * @param array $data
     * @return CommissionSettings
     * @throws ValidationException
     */
    public static function createCommissionSetting(array $data): CommissionSettings
    {
        // Validation rules
        $validator = Validator::make($data, [
            'payment_type' => [
                'required', 
                'string', 
                'in:cb,cheque,virement'
            ],
            'commission_rate' => [
                'required', 
                'numeric', 
                'min:0', 
                'max:100'
            ],
            'currency' => [
                'required', 
                'string', 
                'max:10'
            ]
        ], [
            'payment_type.in' => 'Invalid payment type. Must be one of: credit, loan, investment, other',
            'commission_rate.min' => 'Commission rate must be at least 0',
            'commission_rate.max' => 'Commission rate cannot exceed 100',
        ]);

        // Validate the input
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Check for existing setting with same payment type
        $existingSetting = self::where('payment_type', $data['payment_type'])->first();
        if ($existingSetting) {
            throw ValidationException::withMessages([
                'payment_type' => ['A commission setting for this payment type already exists']
            ]);
        }

        // Create the commission setting
        return self::create([
            'payment_type' => $data['payment_type'],
            'commission_rate' => $data['commission_rate'],
            'currency' => $data['currency']
        ]);
    }

    /**
     * Create multiple commission settings
     *
     * @param array $settingsData
     * @return \Illuminate\Support\Collection
     * @throws ValidationException
     */
    public static function createMultipleCommissionSettings(array $settingsData)
    {
        $createdSettings = collect();

        foreach ($settingsData as $settingData) {
            try {
                $createdSettings->push(self::createCommissionSetting($settingData));
            } catch (ValidationException $e) {
                // You can choose to stop on first error or continue
                throw $e;
            }
        }

        return $createdSettings;
    }

    /**
     * Validate and sanitize commission setting data
     *
     * @param array $data
     * @return array
     */
    private static function sanitizeCommissionSettingData(array $data): array
    {
        return [
            'payment_type' => strtolower(trim($data['payment_type'] ?? '')),
            'commission_rate' => floatval($data['commission_rate'] ?? 0),
            'currency' => strtoupper(trim($data['currency'] ?? ''))
        ];
    }
}
