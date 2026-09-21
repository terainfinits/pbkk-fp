<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function about()
    {
        return view('about');
    }

    // Rute: /agent/{tema?}
    // Parameter diberi nilai default 'General Assistant Agent' jika kosong
    public function agent($tema = 'General Assistant Agent')
    {
        // Mengirimkan variabel $tema ke tampilan agent.blade.php
        return view('agent', compact('tema'));
    }

    public function mahasiswaDetail($nrp)
    {
        return view('mahasiswa', compact('nrp'));
    }
}
