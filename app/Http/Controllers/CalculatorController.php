<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class CalculatorController extends Controller
{
    public function calculateIpk(Request $request, $ip1 = null, $ip2 = null)
    {
        $result = null;
        $error = '';
        $maxLength = 4;

        // Helper closure to validate each IP input consistently
        $validateIp = function ($value, $label) use ($maxLength) {
            if ($value === null || $value === '') {
                return null;
            }

            if (! is_numeric($value)) {
                return "{$label}: Please enter a valid numeric value.";
            }

            if (strlen((string) $value) > $maxLength) {
                return "{$label}: Number length must not exceed 4 characters.";
            }

            $num = (float) $value;
            if ($num < 0 || $num > 4) {
                return "{$label}: IP value must be between 0.00 and 4.00.";
            }

            return null;
        };

        // 1. Validate Semester 1 IP
        $error = $validateIp($ip1, 'IP Semester 1');

        // 2. Validate Semester 2 IP if IP1 is valid
        if (empty($error)) {
            $error = $validateIp($ip2, 'IP Semester 2') ?? '';
        }

        // 3. Compute result if both inputs are valid and non-empty
        if (empty($error) && $ip1 !== null && $ip1 !== '' && $ip2 !== null && $ip2 !== '') {
            $result = ((float) $ip1 + (float) $ip2) / 2;
        }

        return view('ipk_calculator', [
            'initialIP1' => $ip1,
            'initialIP2' => $ip2,
            'initialResult' => $result,
            'initialError' => $error,
        ]);
    }
}
