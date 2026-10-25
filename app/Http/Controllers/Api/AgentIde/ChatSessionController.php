<?php

namespace App\Http\Controllers\Api\AgentIde;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatSessionController extends Controller
{
    /**
     * Display a listing of chat sessions with optional search/filter.
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');

        $query = ChatSession::where('user_id', $request->user()->id)
            ->withCount('messages')
            ->orderByDesc('updated_at');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('messages', function ($mq) use ($search) {
                        $mq->where('content', 'like', "%{$search}%");
                    });
            });
        }

        $sessions = $query->get();

        return response()->json([
            'success' => true,
            'sessions' => $sessions,
        ]);
    }

    /**
     * Create a new chat session.
     */
    public function store(Request $request): JsonResponse
    {
        $session = ChatSession::create([
            'user_id' => $request->user()->id,
            'title' => $request->input('title', 'Percakapan Baru'),
            'provider' => $request->input('provider'),
            'model' => $request->input('model'),
        ]);

        return response()->json([
            'success' => true,
            'session' => $session,
        ], 201);
    }

    /**
     * Display the specified chat session with all its messages.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $session = ChatSession::where('user_id', $request->user()->id)->with('messages')->find($id);

        if (!$session) {
            return response()->json([
                'success' => false,
                'error' => 'Chat session not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'session' => $session,
        ]);
    }

    /**
     * Export the specified chat session as a Markdown or plain text document.
     */
    public function export(Request $request, $id)
    {
        $session = ChatSession::where('user_id', $request->user()->id)->with('messages')->find($id);

        if (!$session) {
            return response()->json([
                'success' => false,
                'error' => 'Chat session not found.',
            ], 404);
        }

        $dateStr = $session->created_at ? $session->created_at->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');
        $providerStr = strtoupper($session->provider ?: 'AI');
        $modelStr = $session->model ?: 'default';

        $md = "# " . $session->title . "\n\n";
        $md .= "- **Date:** {$dateStr}\n";
        $md .= "- **Provider:** {$providerStr}\n";
        $md .= "- **Model:** {$modelStr}\n\n";
        $md .= "---\n\n";

        foreach ($session->messages as $msg) {
            if ($msg->role === 'user') {
                $md .= "### 👤 User\n";
                $md .= $msg->content . "\n\n";
            } else {
                $md .= "### 🤖 Assistant ({$providerStr} - {$modelStr})\n";
                if (!empty($msg->content)) {
                    $md .= $msg->content . "\n\n";
                }
                if (!empty($msg->metadata['code'])) {
                    $lang = $msg->metadata['language'] ?? 'code';
                    $targetPath = !empty($msg->metadata['targetPath']) ? "Target File: `{$msg->metadata['targetPath']}`\n" : '';
                    $md .= "{$targetPath}```{$lang}\n" . $msg->metadata['code'] . "\n```\n\n";
                }
                if (!empty($msg->metadata['steps']) && is_array($msg->metadata['steps'])) {
                    $md .= "<details><summary>Agent Reasoning Steps (" . count($msg->metadata['steps']) . ")</summary>\n\n";
                    foreach ($msg->metadata['steps'] as $step) {
                        $title = $step['title'] ?? 'Step';
                        $detail = $step['detail'] ?? '';
                        $md .= "- **{$title}:** {$detail}\n";
                    }
                    $md .= "\n</details>\n\n";
                }
            }
            $md .= "---\n\n";
        }

        $slug = Str::slug($session->title) ?: 'conversation-' . $session->id;
        $filename = "{$slug}.md";

        return response($md, 200, [
            'Content-Type' => 'text/markdown; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Update session details (e.g., title).
     */
    public function update(Request $request, $id): JsonResponse
    {
        $session = ChatSession::where('user_id', $request->user()->id)->find($id);

        if (!$session) {
            return response()->json([
                'success' => false,
                'error' => 'Chat session not found.',
            ], 404);
        }

        if ($request->filled('title')) {
            $session->update(['title' => $request->input('title')]);
        }

        return response()->json([
            'success' => true,
            'session' => $session,
        ]);
    }

    /**
     * Remove the specified chat session from storage.
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $session = ChatSession::where('user_id', $request->user()->id)->find($id);

        if (!$session) {
            return response()->json([
                'success' => false,
                'error' => 'Chat session not found.',
            ], 404);
        }

        $session->delete();

        return response()->json([
            'success' => true,
            'message' => 'Chat session deleted successfully.',
        ]);
    }
}
