<?php

namespace App\Http\Controllers;

class AgentIdeController extends Controller {
    /* Display the Agentic AI IDE interface (resources/views/agent-ide/index.blade.php). */
    public function index() {
        return view('agent-ide.index');
    }
}
