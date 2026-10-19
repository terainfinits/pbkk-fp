{{-- Confirm deletion of a file or folder. Reuses the create-modal pattern. --}}
<div id="delete-confirm-modal" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="w-full max-w-sm bg-[#0f172a] border border-slate-700 rounded-2xl shadow-2xl p-5 space-y-4">
        <h3 id="delete-confirm-title" class="font-bold text-sm text-slate-100">Confirm Delete</h3>
        <p id="delete-confirm-message" class="text-xs text-slate-400 leading-relaxed"></p>
        <div class="flex justify-end gap-2">
            <button id="btn-cancel-delete" class="px-3 py-1.5 bg-slate-800 text-slate-300 rounded text-xs font-medium">Cancel</button>
            <button id="btn-confirm-delete" class="px-4 py-1.5 bg-rose-600 text-white rounded text-xs font-semibold">Delete</button>
        </div>
    </div>
</div>