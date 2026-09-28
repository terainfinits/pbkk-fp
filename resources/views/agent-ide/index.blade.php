<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Agentic AI IDE | Magentic Engine</title>

    {{-- Google Fonts & Font Awesome --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- Prism Syntax Highlighting --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-markup.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-typescript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-json.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/marked/12.0.0/marked.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.ts'])

    {{-- Agentic IDE's own styles: layout chrome + chat markdown typography. --}}
    @vite(['resources/css/agent-ide.css'])
</head>
<body class="h-screen flex flex-col antialiased select-none bg-[#090d16]">

    @include('agent-ide.partials.header')

    <div class="flex-1 flex overflow-hidden">
        @include('agent-ide.partials.sidebar-explorer')
        @include('agent-ide.partials.editor-pane')
        @include('agent-ide.partials.chat-panel')
    </div>

    @include('agent-ide.partials.settings-modal')
    @include('agent-ide.partials.create-modal')

    {{--
        The JS modules are static assets and can't call the Blade `route()`
        helper, so the handful of URLs they need are handed over here as
        plain config. This is the only inline script left on the page.
    --}}
    <script>
        window.IdeRoutes = {
            tree: "{{ route('ide.api.tree') }}",
            fileRead: "{{ route('ide.api.file.read') }}",
            fileSave: "{{ route('ide.api.file.save') }}",
            fileCreate: "{{ route('ide.api.file.create') }}",
            fileDelete: "{{ route('ide.api.file.delete') }}",
            kernels: "{{ route('ide.api.kernels') }}",
            codeRun: "{{ route('ide.api.code.run') }}",
            agentPrompt: "{{ route('ide.api.agent.prompt') }}",
            terminalExecute: "{{ route('ide.api.terminal.execute') }}",
        };
    </script>

    @vite(['resources/js/agent-ide/main.js'])
</body>
</html>
