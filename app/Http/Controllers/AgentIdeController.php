<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class AgentIdeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('AgentIdeApp', [
            'routes' => [
                'tree' => route('ide.api.tree'),
                'fileRead' => route('ide.api.file.read'),
                'fileSave' => route('ide.api.file.save'),
                'fileCreate' => route('ide.api.file.create'),
                'fileDelete' => route('ide.api.file.delete'),
                'kernels' => route('ide.api.kernels'),
                'codeRun' => route('ide.api.code.run'),
                'pythonStart' => route('ide.api.python.start'),
                'pythonStatus' => route('ide.api.python.status'),
                'pythonInput' => route('ide.api.python.input'),
                'pythonStop' => route('ide.api.python.stop'),
                'agentPrompt' => route('ide.api.agent.prompt'),
                'terminalExecute' => route('ide.api.terminal.execute'),
            ],
        ]);
    }
}
