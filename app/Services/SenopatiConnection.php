<?php

namespace App\Services;

use Throwable;

class SenopatiConnection
{
    public static function describe(Throwable $exception): string
    {
        for ($cause = $exception; $cause; $cause = $cause->getPrevious()) {
            if (preg_match('/^cURL error (\d+):/i', $cause->getMessage(), $match)) {
                $errno = (int) $match[1];
                return match ($errno) {
                    6 => 'DNS Senopati gagal (cURL 6).',
                    7 => 'Koneksi ke Senopati gagal dibuka (cURL 7).',
                    28 => 'Waktu tunggu Senopati habis (cURL 28).',
                    default => "Koneksi ke Senopati gagal (cURL $errno).",
                };
            }
        }
        return 'Koneksi ke Senopati gagal; penyebab belum diketahui.';
    }
}
