{{-- Upload file or .zip into the workspace. Reuses the create-modal pattern. --}}
<div id="upload-modal" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="w-full max-w-sm bg-[#0f172a] border border-slate-700 rounded-2xl shadow-2xl p-5 space-y-4">
        <h3 id="upload-modal-title" class="font-bold text-sm text-slate-100">Upload File or ZIP</h3>
        <div>
            <label class="block text-xs text-slate-400 mb-1">Select file (.zip or any file type)</label>
            <input type="file" id="upload-modal-file" class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-xs text-slate-200 outline-none focus:border-indigo-500">
        </div>
        <div>
            <label class="block text-xs text-slate-400 mb-1">Target Directory (optional)</label>
            <input type="text" id="upload-modal-dir" placeholder="e.g. app/Services" class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-xs text-slate-200 outline-none focus:border-indigo-500">
        </div>
        <div class="flex justify-end gap-2">
            <button id="btn-cancel-upload" class="px-3 py-1.5 bg-slate-800 text-slate-300 rounded text-xs font-medium">Cancel</button>
            <button id="btn-confirm-upload" class="px-4 py-1.5 bg-indigo-600 text-white rounded text-xs font-semibold">Upload</button>
        </div>
    </div>
</div>