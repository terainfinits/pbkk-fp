<?php
// Fitur: Halaman IDE dan navigasi
// connect with ../../../resources/js/pages/AgentIdeApp.vue, ../../../routes/web.php, ../../Services/AgentIde/FallbackAgentResponder.php
// connect with ./Controller.php

namespace App\Http\Controllers;

use Inertia\Inertia;

class AgentIdeController extends Controller
{
    /* Display the Agentic AI IDE interface (resources/views/agent-ide/index.blade.php). */
    public function index()
    {
        return Inertia::render('AgentIdeApp', [
            'projects' => app(\App\Services\AgentIde\Projects::class)->listing(),
            'owner' => request()->user()->id,
            'routes' => [
                'tree' => route('ide.api.tree'),
                'fileRead' => route('ide.api.file.read'),
                'fileSave' => route('ide.api.file.save'),
                'fileCreate' => route('ide.api.file.create'),
                'fileDelete' => route('ide.api.file.delete'),
                'kernels' => route('ide.api.kernels'),
                'codeRun' => route('ide.api.code.run'),
                'agentPrompt' => route('ide.api.agent.prompt'),
                'chats' => route('ide.api.chats.index'),
                'terminalExecute' => route('ide.api.terminal.execute'),
            ],
        ]);
    }
}
