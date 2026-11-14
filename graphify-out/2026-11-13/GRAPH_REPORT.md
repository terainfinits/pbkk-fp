# Graph Report - pbkk-fp  (2026-11-13)

## Corpus Check
- 107 files · ~31,480 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 23 file(s) not represented in the graph (top: (none) 16, .css 2, .example 1)

## Summary
- 750 nodes · 1069 edges · 69 communities (40 shown, 29 thin omitted)
- Extraction: 97% EXTRACTED · 3% INFERRED · 0% AMBIGUOUS · INFERRED: 34 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `8ccc89a9`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- package.json
- Users and Chat Models
- Files and Kernel Services
- Monaco Editor and Diff
- Agent Prompt Processing
- Chat Panel and Sessions
- Database Migrations
- Terminal Drawer
- Continuous Integration
- Calculator Interface
- TypeScript Compiler Settings
- Deployment Workflow
- Composer Scripts
- Optional Native Dependencies
- File Explorer Interface
- IDE State and Settings
- Application Service Provider
- PHP Development Dependencies
- Python Calculator
- Dependency Update Settings
- Inertia Pages and Bootstrap
- Authentication Type Definitions
- Composer Package Metadata
- Project Features and Setup
- Frontend Runtime Dependencies
- IDE Application Shell
- Vite Build Integration
- Frontend Development Dependencies
- Composer Plugin Settings
- PHP Test Framework
- Frontend Package Scripts
- File Drag and Drop
- File Tree Nodes
- IDE HTTP Client
- PHP Runtime Dependencies
- Class Name Utilities
- IDE Header Actions
- Vue Component Integration
- Logging Handlers
- Knowledge Graph Workflow
- Hello World Example
- Laravel Package Discovery
- PNPM Workspace Configuration
- Deployment Script
- Crawler Access Rules
- ChatAiTest.php
- dependencies
- vite.config.ts
- devDependencies
- scripts
- clsx

## God Nodes (most connected - your core abstractions)
1. `FileExplorerService` - 22 edges
2. `vue` - 19 edges
3. `compilerOptions` - 19 edges
4. `Controller` - 16 edges
5. `User` - 15 edges
6. `scripts` - 13 edges
7. `FileController` - 12 edges
8. `FallbackAgentResponder` - 12 edges
9. `LlmProviderService` - 12 edges
10. `require-dev` - 12 edges

## Surprising Connections (you probably didn't know these)
- `UserController` --inherits--> `Controller`  [EXTRACTED]
  folder-generated/app/Http/Controllers/UserController.php → app/Http/Controllers/Controller.php
- `handleDelete()` --calls--> `confirm()`  [INFERRED]
  resources/js/components/agent-ide/SidebarExplorer.vue → resources/js/components/agent-ide/CreateModal.vue
- `AgentIdeController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/AgentIdeController.php → app/Http/Controllers/Controller.php
- `ChatController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Api/AgentIde/ChatController.php → app/Http/Controllers/Controller.php
- `ChatSessionController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Api/AgentIde/ChatSessionController.php → app/Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Communities (69 total, 29 thin omitted)

### Community 0 - "package.json"
Cohesion: 0.21
Nodes (12): optionalDependencies, @laravel/multiplex@^0.4.1, lightningcss-linux-x64-gnu@^1.29.1, @rollup/rollup-linux-x64-gnu@4.9.5, @tailwindcss/oxide-linux-x64-gnu@^4.0.1, private, $schema, type (+4 more)

### Community 1 - "Users and Chat Models"
Cohesion: 0.05
Nodes (6): AiChatLog, ChatMessage, ChatSession, User, UserFactory, DatabaseSeeder

### Community 2 - "Files and Kernel Services"
Cohesion: 0.10
Nodes (6): ChatController, AgentContextBuilder, AgentResponseFormatter, activeTab, FallbackAgentResponder, LlmProviderService

### Community 3 - "Monaco Editor and Diff"
Cohesion: 0.06
Nodes (31): monaco-editor@^0.57.0, monaco-editor-vue3@^1.0.5, monaco-editor, monaco-editor-vue3, currentModified, DiffEditorComponent, diffEditorRef, emit (+23 more)

### Community 4 - "Agent Prompt Processing"
Cohesion: 0.08
Nodes (4): AgentIdeController, AuthController, AppServiceProvider, {closure#1}()

### Community 5 - "Chat Panel and Sessions"
Cohesion: 0.09
Nodes (14): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}() (+6 more)

### Community 6 - "Database Migrations"
Cohesion: 0.09
Nodes (21): ChatMessage, ChatSessionItem, clearSearch(), currentSessionId, getApiKey(), isLoadingSessions, isThinking, loadSessions() (+13 more)

### Community 7 - "Terminal Drawer"
Cohesion: 0.14
Nodes (3): FileController, Controller, FileExplorerService

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
Cohesion: 0.17
Nodes (11): vue, emit, fields, props, save(), KernelInfo, KernelsResponse, OpenTab (+3 more)

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
Cohesion: 0.20
Nodes (8): IdeRoutes, api, createType, isCreateOpen, isSettingsOpen, props, routes, state

### Community 27 - "Frontend Development Dependencies"
Cohesion: 0.28
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 28 - "Composer Plugin Settings"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 30 - "Frontend Package Scripts"
Cohesion: 0.33
Nodes (6): deleteCurrentSession(), confirm(), emit, path, props, IdeApi

### Community 31 - "File Drag and Drop"
Cohesion: 0.33
Nodes (3): hasFiles(), isDragging, onDragEnter()

### Community 32 - "File Tree Nodes"
Cohesion: 0.29
Nodes (5): emit, hasChildren, open, props, TreeNodeItem

### Community 33 - "IDE HTTP Client"
Cohesion: 0.57
Nodes (6): createIdeApi(), csrf(), defaultRoutes, del(), get(), post()

### Community 34 - "PHP Runtime Dependencies"
Cohesion: 0.29
Nodes (7): require, inertiajs/inertia-laravel, laravel/framework, laravel/sanctum, laravel/tinker, laravel/wayfinder, php

### Community 35 - "Class Name Utilities"
Cohesion: 0.33
Nodes (3): emit, kernelText, targetDisplay

### Community 37 - "Vue Component Integration"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 45 - "Laravel Package Discovery"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

### Community 62 - "ChatAiTest.php"
Cohesion: 0.08
Nodes (4): ChatAiController, ChatAiRequest, ChatAiResource, ChatAiService

### Community 63 - "dependencies"
Cohesion: 0.20
Nodes (10): dependencies, concurrently@^10.0.3, tailwindcss@^4.1.1, typescript@^5.2.2, vite@^8.0.0, vue@^3.5.13, concurrently, tailwindcss (+2 more)

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
  These have ≤1 connection - possible missing edges. (Counts symbols only; 355 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **29 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `vue` connect `Composer Scripts` to `package.json`, `File Tree Nodes`, `Files and Kernel Services`, `Monaco Editor and Diff`, `Class Name Utilities`, `Database Migrations`, `Continuous Integration`, `TypeScript Compiler Settings`, `Inertia Pages and Bootstrap`, `Authentication Type Definitions`, `IDE Application Shell`, `File Drag and Drop`, `Frontend Package Scripts`, `dependencies`?**
  _High betweenness centrality (0.285) - this node is a cross-community bridge._
- **What connects `Past chat sessions and keyword search`, `Editor generated changes with accept reject and diff`, `Local setup with Composer npm and Artisan` to the rest of the system?**
  _6 weakly-connected nodes found - possible missing edges._
- **Should `Users and Chat Models` be split into smaller, more focused modules?**
  _Cohesion score 0.05 - nodes in this community are weakly interconnected._
- **Why does `Controller` connect `Terminal Drawer` to `Files and Kernel Services`, `Agent Prompt Processing`, `Logging Handlers`, `Optional Native Dependencies`, `Project Features and Setup`, `Vite Build Integration`, `ChatAiTest.php`?**
  _High betweenness centrality (0.061) - this node is a cross-community bridge._
- **Should `Files and Kernel Services` be split into smaller, more focused modules?**
  _Cohesion score 0.09957325746799431 - nodes in this community are weakly interconnected._
- **Should `Monaco Editor and Diff` be split into smaller, more focused modules?**
  _Cohesion score 0.05975609756097561 - nodes in this community are weakly interconnected._
- **Should `Agent Prompt Processing` be split into smaller, more focused modules?**
  _Cohesion score 0.07954545454545454 - nodes in this community are weakly interconnected._