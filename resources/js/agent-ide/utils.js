// Small, dependency-free helpers shared across modules.

export function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

export function showNotification(msg, isError = false) {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-8 right-8 z-50 px-4 py-2.5 rounded-lg text-xs font-semibold shadow-2xl flex items-center gap-2 border transition-all duration-300 transform translate-y-2 opacity-0 ${isError ? 'bg-red-950 border-red-500 text-red-200' : 'bg-slate-900 border-indigo-500 text-indigo-200'}`;
    toast.innerHTML = `<i class="fa-solid ${isError ? 'fa-triangle-exclamation text-red-400' : 'fa-circle-check text-emerald-400'}"></i> <span>${escapeHtml(msg)}</span>`;
    document.body.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.remove('translate-y-2', 'opacity-0');
    });

    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-2');
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}
