<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function agent($tema = 'General Assistant Agent')
    {
        $lowercaseTema = strtolower($tema);
        $codingAliases = ['coding', 'coding assistant', 'coding assistant agent', 'coding-assistant', 'coding-assistant-agent', 'coding_assistant', 'coding_assistant_agent'];

        $isCoding = in_array($lowercaseTema, $codingAliases);

        $nextTema = $isCoding ? 'General Assistant Agent' : 'Coding Assistant Agent';

        if ($isCoding) {
            return view('fp_idea', [
                'tema' => $tema,
                'nextTema' => $nextTema,
            ]);
        }

        return view('agent', [
            'tema' => $tema,
            'nextTema' => $nextTema,
        ]);
    }

    public function mahasiswaDetail($nrp)
    {
        return view('mahasiswa', compact('nrp'));
    }
}
