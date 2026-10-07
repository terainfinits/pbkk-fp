<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class AgentIdeController extends Controller
{
    /* Display the Agentic AI IDE interface (resources/views/agent-ide/index.blade.php). */
    public function index()
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
                'agentPrompt' => route('ide.api.agent.prompt'),
                'terminalExecute' => route('ide.api.terminal.execute'),
            ],
        ]);
    }
}
