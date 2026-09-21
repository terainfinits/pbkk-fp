<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class CalculatorController extends Controller
{
    public function calculate(Request $request, $a = null, $b = null, $operation = null)
    {
        $result = 0;
        $error = '';
        $maxLength = 15;

        $opSymbolMap = [
            'tambah' => '+',
            'kurang' => '-',
            'kali' => '*',
            'bagi' => '/',
        ];

        $resolvedOpSymbol = $opSymbolMap[$operation] ?? $operation;

        if ($a !== null) {
            if (! is_numeric($a)) {
                $error = 'Invalid input for a';
            } elseif (strlen((string) $a) > $maxLength) {
                $error = 'Number too large for a';
            }
        }

        if ($b !== null && $error === '') {
            if (! is_numeric($b)) {
                $error = 'Invalid input for b';
            } elseif (strlen((string) $b) > $maxLength) {
                $error = 'Number too large for b';
            }
        }

        if ($a !== null && $b !== null && $operation !== null && $error === '') {
            $a = (float) $a;
            $b = (float) $b;
            switch ($operation) {
                case 'tambah':
                case '+':
                    $result = $a + $b;
                    break;
                case 'kurang':
                case '-':
                    $result = $a - $b;
                    break;
                case 'kali':
                case '*':
                    $result = $a * $b;
                    break;
                case 'bagi':
                case '/':
                    if ($b == 0) {
                        $error = 'Division by zero';
                        $result = 'Error';
                    } else {
                        $result = $a / $b;
                    }
                    break;
                default:
                    $error = 'Invalid operation';
                    $result = 'Error';
                    break;
            }
        }

        $userAgent = $request->header('User-Agent', '');
        $isCurlOrHeadless = str_contains($userAgent, 'curl') || $request->expectsJson();

        if ($isCurlOrHeadless) {
            return response()->json([
                'a' => $a,
                'b' => $b,
                'operation' => $operation,
                'result' => ($a !== null && $b !== null && $error === '') ? $result : 'Error',
                'error' => $error !== '' ? $error : null,
            ], $error !== '' ? 400 : 200);
        }

        if ($error === '' || $error === 'Division by zero') {
            return Inertia::render('Calculator', [
                'initialA' => $a,
                'initialB' => $b,
                'initialOperation' => $resolvedOpSymbol,
                'initialResult' => ($a !== null && $b !== null) ? $result : null,
                'initialError' => $error,
            ]);
        } else {
            return Inertia::render('Calculator', [
                'initialA' => null,
                'initialB' => null,
                'initialOperation' => null,
                'initialResult' => 'Error',
                'initialError' => $error,
            ]);
        }
    }
}
