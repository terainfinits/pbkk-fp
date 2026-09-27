// Markdown rendering for the chatbot panel.
//
// Two problems in the old single-file version this fixes:
//   1. marked.js was used with default options (no GFM tables, no line
//      breaks) and its output had no CSS at all, so headings/lists/tables
//      rendered as plain unstyled text — "chatbot can't handle markdown".
//   2. The raw AI response already contains the fenced code block, which
//      was rendered a *second* time in the dedicated code-action card,
//      and un-wrapped <pre> content could push the chat bubble wider than
//      its column ("still outside box ui"). We strip the fenced block out
//      of the prose before parsing, and .markdown-body (agent-ide.css)
//      constrains everything else to the column width.

import { escapeHtml } from './utils.js';

export function configureMarkdown() {
    if (window.marked) {
        window.marked.setOptions({
            gfm: true,
            breaks: true,
            mangle: false,
            headerIds: false,
        });
    }
}

/**
 * Remove the `### [FILE: ...]` marker and the first fenced code block from
 * the raw agent response, since that code is already shown in its own
 * interactive card below the prose. What's left is pure explanation text,
 * which is what should actually be markdown-rendered.
 */
export function extractProseFromRaw(raw) {
    if (!raw) return '';
    return raw
        .replace(/###\s*\[FILE:[^\]]*\]\s*/i, '')
        .replace(/```[a-zA-Z0-9_-]*\r?\n[\s\S]*?```/, '')
        .trim();
}

/**
 * Render markdown text to sanitized-enough HTML for the chat bubble.
 * Falls back to escaped plain text if marked.js isn't available or throws.
 */
export function renderMarkdown(raw) {
    const text = (raw || '').trim();
    if (!text) return '';

    if (window.marked) {
        try {
            return window.marked.parse(text);
        } catch (e) {
            return `<p>${escapeHtml(text)}</p>`;
        }
    }

    return `<p>${escapeHtml(text)}</p>`;
}

/** Run Prism syntax highlighting over any <pre><code> blocks marked has produced. */
export function highlightMarkdownCode(containerEl) {
    if (window.Prism && containerEl) {
        window.Prism.highlightAllUnder(containerEl);
    }
}
