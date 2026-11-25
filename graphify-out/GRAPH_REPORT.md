# Graph Report - pbkk-fp  (2026-11-14)

## Corpus Check
- 127 files · ~38,398 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 24 file(s) not represented in the graph (top: (none) 17, .css 2, .example 1)

## Summary
- 889 nodes · 1298 edges · 76 communities (42 shown, 34 thin omitted)
- Extraction: 96% EXTRACTED · 4% INFERRED · 0% AMBIGUOUS · INFERRED: 50 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `914af027`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- package.json
- Users and Chat Models
- Files and Kernel Services
- Monaco Editor and Diff
- Chat Panel and Sessions
- Database Migrations
- FileExplorerService
- Continuous Integration
- Calculator Interface
- TypeScript Compiler Settings
- Deployment Workflow
- Composer Scripts
- HarnessPanel.vue
- File Explorer Interface
- IDE State and Settings
- Application Service Provider
- PHP Development Dependencies
- Python Calculator
- Dependency Update Settings
- Inertia Pages and Bootstrap
- Authentication Type Definitions
- Composer Package Metadata
- Illuminate\Http\Request
- Frontend Runtime Dependencies
- IDE Application Shell
- Indeks file per kluster
- flappy_bird.py
- Composer Plugin Settings
- PHP Test Framework
- HandleInertiaRequests
- File Drag and Drop
- File Tree Nodes
- Controller
- PHP Runtime Dependencies
- bootstrap/app.php
- SettingsModal.vue
- Vue Component Integration
- Knowledge Graph Workflow
- hello_world.py
- Repository
- Laravel Package Discovery
- PNPM Workspace Configuration
- Deployment Script
- Crawler Access Rules
- Controller
- dependencies
- vite.config.ts
- devDependencies
- scripts
- clsx
- ChatAiTest.php
- load_data
- generate_primes
- HarnessController
- UserController
- AuthController.php

## God Nodes (most connected - your core abstractions)
1. `FileExplorerService` - 22 edges
2. `Repository` - 20 edges
3. `vue` - 20 edges
4. `compilerOptions` - 19 edges
5. `User` - 18 edges
6. `Controller` - 17 edges
7. `scripts` - 13 edges
8. `FileController` - 12 edges
9. `FallbackAgentResponder` - 12 edges
10. `LlmProviderService` - 12 edges

## Surprising Connections (you probably didn't know these)
- `UserController` --inherits--> `Controller`  [EXTRACTED]
  folder-generated/app/Http/Controllers/UserController.php → app/Http/Controllers/Controller.php
- `{closure#1}()` --calls--> `RunCodingAgent`  [EXTRACTED]
  tests/Feature/HarnessTest.php → app/Jobs/RunCodingAgent.php
- `{closure#1}()` --calls--> `AgentResponseFormatter`  [EXTRACTED]
  tests/Unit/AgentFilesTest.php → app/Services/AgentIde/AgentResponseFormatter.php
- `handleDelete()` --calls--> `confirm()`  [INFERRED]
  resources/js/components/agent-ide/SidebarExplorer.vue → resources/js/components/agent-ide/CreateModal.vue
- `ChatController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Api/AgentIde/ChatController.php → app/Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Communities (76 total, 34 thin omitted)

### Community 0 - "package.json"
Cohesion: 0.21
Nodes (12): optionalDependencies, @laravel/multiplex@^0.4.1, lightningcss-linux-x64-gnu@^1.29.1, @rollup/rollup-linux-x64-gnu@4.9.5, @tailwindcss/oxide-linux-x64-gnu@^4.0.1, private, $schema, type (+4 more)

### Community 1 - "Users and Chat Models"
Cohesion: 0.05
Nodes (6): AiChatLog, ChatMessage, ChatSession, User, UserFactory, DatabaseSeeder

### Community 2 - "Files and Kernel Services"
Cohesion: 0.09
Nodes (7): ChatController, AgentContextBuilder, AgentResponseFormatter, activeTab, FallbackAgentResponder, LlmProviderService, {closure#1}()

### Community 3 - "Monaco Editor and Diff"
Cohesion: 0.08
Nodes (18): activeTab, charCount, code, CodeEditorComponent, currentTheme, cursorPos, diffIsNewFile, diffLanguage (+10 more)

### Community 5 - "Chat Panel and Sessions"
Cohesion: 0.08
Nodes (17): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}() (+9 more)

### Community 6 - "Database Migrations"
Cohesion: 0.07
Nodes (24): ChatMessage, ChatSessionItem, clearSearch(), currentSessionId, deleteCurrentSession(), getApiKey(), isLoadingSessions, isThinking (+16 more)

### Community 7 - "FileExplorerService"
Cohesion: 0.09
Nodes (5): FileController, KernelController, FileExplorerService, KernelRunnerService, TerminalService

### Community 8 - "Continuous Integration"
Cohesion: 0.09
Nodes (25): activeTab, commandInput, cwd, examples, execTime, execute(), history, historyIndex (+17 more)

### Community 9 - "Calculator Interface"
Cohesion: 0.10
Nodes (20): runs-on, steps, jobs, ci, name, on, pull_request, push (+12 more)

### Community 10 - "TypeScript Compiler Settings"
Cohesion: 0.13
Nodes (17): appendNumber(), chooseOperation(), clear(), compute(), computeIntermediate(), currentInput, deleteNumber(), finishedOperation (+9 more)

### Community 11 - "Deployment Workflow"
Cohesion: 0.10
Nodes (20): compilerOptions, allowJs, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, jsxImportSource, lib (+12 more)

### Community 12 - "Composer Scripts"
Cohesion: 0.13
Nodes (13): vue, path, props, emit, kernelText, targetDisplay, IdeApi, KernelInfo (+5 more)

### Community 13 - "HarnessPanel.vue"
Cohesion: 0.09
Nodes (25): currentModified, DiffEditorComponent, diffEditorRef, emit, handleApply(), handleClose(), handleDiffEditorMount(), loadMonaco() (+17 more)

### Community 14 - "File Explorer Interface"
Cohesion: 0.14
Nodes (13): runs-on, steps, jobs, deploy, name, on, push, branches (+5 more)

### Community 15 - "IDE State and Settings"
Cohesion: 0.15
Nodes (13): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+5 more)

### Community 16 - "Application Service Provider"
Cohesion: 0.19
Nodes (13): Agent page /agent/{tema?}, Past chat sessions and keyword search, Editor generated changes with accept reject and diff, Gemini integration, IPK calculator, LLM Integration Gemini/Ollama, Local setup with Composer npm and Artisan, Ollama Cloud integration (+5 more)

### Community 17 - "PHP Development Dependencies"
Cohesion: 0.17
Nodes (12): require-dev, fakerphp/faker, larastan/larastan, laravel/boost, laravel/pail, laravel/pao, laravel/pint, laravel/sail (+4 more)

### Community 18 - "Python Calculator"
Cohesion: 0.26
Nodes (7): add(), divide(), get_number(), get_operation(), main(), multiply(), subtract()

### Community 19 - "Dependency Update Settings"
Cohesion: 0.17
Nodes (11): default-days, patterns, github-actions, interval, updates, cooldown, directory, groups (+3 more)

### Community 20 - "Inertia Pages and Bootstrap"
Cohesion: 0.17
Nodes (5): @inertiajs/vue3@^3.0.0, @inertiajs/vue3, pages, form, form

### Community 21 - "Authentication Type Definitions"
Cohesion: 0.21
Nodes (9): prompt, handleDelete(), handleOpenFileEvent(), handleRename(), handleUpload(), isLoading, loadError, loadTree() (+1 more)

### Community 22 - "Composer Package Metadata"
Cohesion: 0.20
Nodes (9): Auth, User, ComponentCustomProperties, ImportMeta, ImportMetaEnv, InertiaConfig, @inertiajs/core, vite/client (+1 more)

### Community 24 - "Frontend Runtime Dependencies"
Cohesion: 0.18
Nodes (10): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, Tests\\ (+2 more)

### Community 25 - "IDE Application Shell"
Cohesion: 0.17
Nodes (15): createIdeApi(), csrf(), defaultRoutes, del(), get(), IdeRoutes, post(), api (+7 more)

### Community 26 - "Indeks file per kluster"
Cohesion: 0.10
Nodes (18): Coding agent lokal, Demo dua file, Menjalankan aplikasi, API chat bertoken dan log penggunaan, Contoh keluaran workspace, Editor teks dan peninjauan diff, Explorer, pembuatan dan unggah file, Halaman IDE dan navigasi (+10 more)

### Community 27 - "flappy_bird.py"
Cohesion: 0.22
Nodes (3): Bird, generate_pipe(), Pipe

### Community 28 - "Composer Plugin Settings"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 31 - "File Drag and Drop"
Cohesion: 0.33
Nodes (3): hasFiles(), isDragging, onDragEnter()

### Community 32 - "File Tree Nodes"
Cohesion: 0.29
Nodes (5): emit, hasChildren, open, props, TreeNodeItem

### Community 33 - "Controller"
Cohesion: 0.25
Nodes (3): AgentIdeController, TerminalController, Controller

### Community 34 - "PHP Runtime Dependencies"
Cohesion: 0.29
Nodes (7): require, inertiajs/inertia-laravel, laravel/framework, laravel/sanctum, laravel/tinker, laravel/wayfinder, php

### Community 35 - "bootstrap/app.php"
Cohesion: 0.28
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 36 - "SettingsModal.vue"
Cohesion: 0.33
Nodes (6): emit, fields, keys, props, save(), storageError

### Community 37 - "Vue Component Integration"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 43 - "Repository"
Cohesion: 0.06
Nodes (9): RegisterWorkspace, {closure#2}(), {closure#1}(), RunCodingAgent, AppServiceProvider, {closure#1}(), Repository, Tools (+1 more)

### Community 45 - "Laravel Package Discovery"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

### Community 62 - "Controller"
Cohesion: 0.17
Nodes (4): ChatAiController, ChatAiRequest, ChatAiResource, ChatAiService

### Community 63 - "dependencies"
Cohesion: 0.11
Nodes (18): dependencies, concurrently@^10.0.3, dompurify@3.4.15, marked@14.0.0, monaco-editor@^0.57.0, monaco-editor-vue3@^1.0.5, tailwindcss@^4.1.1, typescript@^5.2.2 (+10 more)

### Community 64 - "vite.config.ts"
Cohesion: 0.22
Nodes (8): @inertiajs/vite@^3.0.0, laravel-vite-plugin@^3.1, @tailwindcss/vite@^4.1.11, @vitejs/plugin-vue@^6.0.0, @inertiajs/vite, laravel-vite-plugin, @tailwindcss/vite, @vitejs/plugin-vue

### Community 65 - "devDependencies"
Cohesion: 0.22
Nodes (9): devDependencies, @laravel/vite-plugin-wayfinder@^0.1.3, @types/node@^22.13.5, vite-plus@0.3.0, vue-tsc@^2.2.4, @laravel/vite-plugin-wayfinder, @types/node, vite-plus (+1 more)

### Community 66 - "scripts"
Cohesion: 0.29
Nodes (7): scripts, build, build:ssr, check, check:fix, dev, types:check

### Community 67 - "clsx"
Cohesion: 0.33
Nodes (4): clsx@^2.1.1, tailwind-merge@^3.2.0, clsx, tailwind-merge

## Knowledge Gaps
- **6 isolated node(s):** `Past chat sessions and keyword search`, `Editor generated changes with accept reject and diff`, `Local setup with Composer npm and Artisan`, `Python input terminal bug`, `Wiki navigation` (+1 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 415 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **34 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `vue` connect `Composer Scripts` to `package.json`, `File Tree Nodes`, `Files and Kernel Services`, `Monaco Editor and Diff`, `SettingsModal.vue`, `Database Migrations`, `Continuous Integration`, `TypeScript Compiler Settings`, `HarnessPanel.vue`, `Inertia Pages and Bootstrap`, `Authentication Type Definitions`, `IDE Application Shell`, `File Drag and Drop`, `dependencies`?**
  _High betweenness centrality (0.271) - this node is a cross-community bridge._
- **What connects `Past chat sessions and keyword search`, `Editor generated changes with accept reject and diff`, `Local setup with Composer npm and Artisan` to the rest of the system?**
  _6 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Users and Chat Models` be split into smaller, more focused modules?**
  _Cohesion score 0.05 - nodes in this community are weakly interconnected._
- **Why does `User` connect `Users and Chat Models` to `Repository`, `AuthController.php`, `ChatAiTest.php`?**
  _High betweenness centrality (0.052) - this node is a cross-community bridge._
- **Should `Files and Kernel Services` be split into smaller, more focused modules?**
  _Cohesion score 0.09358974358974359 - nodes in this community are weakly interconnected._
- **Should `Monaco Editor and Diff` be split into smaller, more focused modules?**
  _Cohesion score 0.08307692307692308 - nodes in this community are weakly interconnected._
- **Should `Chat Panel and Sessions` be split into smaller, more focused modules?**
  _Cohesion score 0.07822410147991543 - nodes in this community are weakly interconnected._