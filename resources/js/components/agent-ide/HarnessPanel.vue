<!-- Fitur: Harness tugas coding
connect with ../../../../app/Console/Commands/RegisterWorkspace.php, ../../../../routes/harness.php, ../../pages/AgentIdeApp.vue
connect with ./DiffViewer.vue -->
<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import DiffViewer from './DiffViewer.vue';

const props = defineProps<{hasDirty:boolean; saveDirty:()=>Promise<void>;project:{id:string;name:string;workspace:number|null;reason:string|null}}>();
const emit = defineEmits<{prepared:[]; applied:[paths:string[]]}>();
const confirmSave = ref(false);
type Workspace = { id: number; name: string; latest: number | null };
type RunEvent = { time_ms: number | null; turn: number | null; duration_ms: number | null; id: number; kind: string; message: string; exit_code: number | null };
type Change = { path: string; before: string | null; after: string | null };
type Plan = { goal: string; files: string[]; steps: string[]; criteria: { description: string; check: string }[] };
type Run = { id: number; status: string; phase: string; question: string | null; stop_reason: string | null;
  plan: Plan | null; evidence: { summary: string; evidence: string[] } | null;
  checks: Record<string, {revision: string; exit_code: number}>; task: string; patch: string | null; events: RunEvent[]; changes: Change[] };
const answer = ref('');
const inspecting = ref<Change>();

const workspaces = ref<Workspace[]>([]);
const selected = ref<number>();
const run = ref<Run>();
const task = ref('');
const error = ref('');
const busy = ref(false);
let timer: ReturnType<typeof setTimeout>;
let generation = 0;
const clock = ref(Date.now());
const ticker = setInterval(() => { clock.value = Date.now(); }, 1000);
const showTrace = ref(false);
const traceLabels: Record<string, string> = {
  trace_message: 'Pesan baru dalam konteks Senopati',
  trace_instruction: 'Instruksi Agent untuk giliran ini',
  trace_reply: 'Balasan Senopati',
  trace_result: 'Hasil tool untuk Agent',
};
const timeline = computed(() => run.value?.events.filter(e => !e.kind.startsWith('raw_') && (showTrace.value || !traceLabels[e.kind])) ?? []);
const last = computed(() => run.value?.events.filter(e => !e.kind.startsWith('raw_') && !traceLabels[e.kind]).at(-1));
const statusLabels: Record<string, string> = {
  queued: 'Dalam antrean', running: 'Sedang dikerjakan', cancelling: 'Sedang dibatalkan',
  review: 'Perlu ditinjau', verified: 'Terverifikasi', awaiting_input: 'Menunggu jawaban',
  conflict: 'Konflik perubahan', applied: 'Diterapkan', rejected: 'Ditolak',
  cancelled: 'Dibatalkan', failed: 'Tugas gagal',
};
const emptyReview = computed(() => run.value?.status === 'review' && !run.value.patch);
const history = ref<{id:number; task:string; status:string}[]>([]);
const page = ref(1);
const more = ref(false);
async function fetchHistory(id: number, n = 1) {
  const data = await request('workspaces/' + id + '/runs?page=' + n);
  if (selected.value !== id) return;
  history.value = n === 1 ? data.data : [...history.value, ...data.data];
  page.value = n;
  more.value = !!data.next_page_url;
}
function inspect(id: number) {
  clearTimeout(timer);
  inspecting.value = undefined;
  run.value = undefined;
  void load(id, ++generation);
}
const active = computed(() => {
  const current = run.value;
  return current?.status === 'review'
    ? !!current.patch
    : ['queued', 'running', 'cancelling', 'verified', 'awaiting_input', 'conflict'].includes(current?.status ?? '');
});
async function request(path: string, data?: object) {
  const res = await fetch('/ide/api/harness/' + path, {
    method: data ? 'POST' : 'GET',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '' },
    body: data ? JSON.stringify(data) : undefined,
  });
  if (!res.ok) throw new Error((await res.json()).message ?? res.statusText);
  return res.status === 204 ? null : res.json();
}
function report(e: unknown) { error.value = e instanceof Error ? e.message : String(e); }
async function load(id: number, version: number) {
  clearTimeout(timer);
  const previous = run.value?.events ?? [];
  const previousStatus = run.value?.status;
  try {
    const data: Run = await request('runs/' + id + '?after=' + (previous[previous.length - 1]?.id ?? 0));
    if (generation !== version) return;
    if (data.status !== run.value?.status && selected.value) void fetchHistory(selected.value).catch(report);
    run.value = { ...data, events: [...previous, ...data.events] };
    if (data.status === 'applied' && previousStatus !== 'applied') {
      emit('applied', data.changes.map(c => c.path));
    }
    error.value = '';
    if (!['queued','running','cancelling'].includes(data.status)) return;
  } catch (e) {
    if (generation !== version) return;
    error.value = 'Koneksi terputus, mencoba kembali';
  }
  timer = setTimeout(() => load(id, version), 1500);
}
watch(selected, id => {
  generation++;
  clearTimeout(timer);
  run.value = undefined;
  inspecting.value = undefined;
  const latest = workspaces.value.find(w => w.id === id)?.latest;
  history.value = [];
  more.value = false;
  if (id) {

    void fetchHistory(id).catch(report);
  }
  if (latest) void load(latest, generation);
});
onMounted(async () => {
  try {
    workspaces.value = await request('workspaces');
    selected.value = props.project.workspace ?? undefined;
  } catch (e) { report(e); }
});
onUnmounted(() => { clearInterval(ticker); generation++; clearTimeout(timer); });
async function act(action: string) {
  busy.value = true;
  error.value = '';
  let version = ++generation;
  clearTimeout(timer);
  let id = run.value?.id;
  try {
    if (action === 'start') {
      if (props.hasDirty) {
        if (!confirmSave.value) { confirmSave.value = true; return; }
        await props.saveDirty();
      }
      confirmSave.value = false;
      const path = props.project.id === 'ide' ? 'folder/runs' : 'workspaces/' + selected.value + '/runs';
      const started = await request(path, { task: task.value });
      id = started.id;
      if (selected.value !== started.workspace) {
        selected.value = started.workspace;
        await nextTick();
        version = ++generation;
        emit('prepared');
      }
      run.value = undefined;
      const space = workspaces.value.find(w => w.id === selected.value);
      if (space) { space.latest = id ?? null; await fetchHistory(space.id); }
    } else {
      await request('runs/' + id + '/' + action, action === 'answer' ? { answer: answer.value } : {});
      answer.value = '';
    }
    if (id) await load(id, version);
  } catch (e) {
    if (id) await load(id, version);
    report(e);
  } finally { busy.value = false; }
}
const blockedReason = computed(() => {
  if (props.project.reason) return props.project.reason;
  if (confirmSave.value) return 'Konfirmasi penyimpanan file untuk memulai.';
  if (busy.value) return 'Memproses permintaan…';
  if (!selected.value && props.project.id !== 'ide') return 'Menyiapkan proyek…';
  if (!active.value) return '';
  if (run.value?.status === 'awaiting_input') return 'Jawab pertanyaan Senopati untuk melanjutkan.';
  if (['review', 'verified', 'conflict'].includes(run.value?.status ?? '')) return 'Selesaikan review perubahan sebelum mengirim tugas baru.';
  return 'Tugas masih berjalan.';
});
const blocked = computed(() => !!blockedReason.value);
async function start(text: string) {
  if (blocked.value) return false;
  task.value = text;
  await act('start');
  return !error.value && !confirmSave.value;
}
defineExpose({ blocked, blockedReason, start });
</script>

<template>
  <aside class="w-2/5 min-w-0 space-y-4 overflow-x-hidden overflow-y-auto p-4 text-sm select-text [overflow-wrap:anywhere]" aria-label="Tugas coding">
    <details v-if="history.length" class="task-detail">
      <summary>Riwayat tugas</summary>
      <button v-for="item in history" :key="item.id" class="mt-1 block w-full rounded-md px-2 py-1.5 text-left break-words hover:bg-slate-800" @click="inspect(item.id)">{{ item.task }} <span class="text-xs text-slate-400">· {{ statusLabels[item.status] ?? item.status }}</span></button>
      <button v-if="more && selected" @click="fetchHistory(selected, page + 1).catch(report)">Tampilkan lagi</button>
    </details>
    <dialog :open="confirmSave" class="relative space-y-2 rounded-xl border border-slate-700 bg-slate-900 p-4 text-slate-100" aria-label="Simpan perubahan">
      <p>Simpan perubahan editor agar Agent membaca versi terbaru.</p>
      <button :disabled="busy" @click="act('start')">Simpan &amp; mulai</button>
      <button :disabled="busy" @click="confirmSave = false">Batal</button>
    </dialog>
    <p v-if="error" role="alert" class="rounded-xl border border-rose-700 bg-rose-950/40 p-3 text-rose-200">{{ error }}</p>
    <div v-if="!run && !history.length" class="rounded-2xl border border-indigo-500/30 bg-slate-900 p-4">
      <h3 class="font-semibold text-indigo-200">Apa yang ingin Anda kerjakan?</h3>
      <p class="mt-1 text-slate-300">Coba “Perbaiki fungsi penjumlahan, lalu jalankan tes.”</p>
    </div>
    <section v-if="run" class="space-y-4">
      <h3 class="ml-auto w-fit max-w-[85%] rounded-2xl rounded-tr-sm bg-indigo-700 px-4 py-2.5 font-medium whitespace-pre-wrap break-words text-white">{{ run.task }}</h3>
      <div class="task-card">
      <header class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-700/70 pb-3">
        <span class="font-semibold text-indigo-200"><i class="fa-solid fa-robot mr-2" aria-hidden="true"></i>Senopati</span>
        <span class="rounded-full bg-indigo-500/15 px-2.5 py-1 text-xs text-indigo-200">{{ emptyReview ? 'Selesai tanpa perubahan' : (statusLabels[run.status] ?? run.status) }}</span>
      </header>
      <p v-if="project.id === 'ide' && run.status === 'applied'" class="text-emerald-300">Perubahan sudah ditulis ke folder-generated.</p>
      <p v-else-if="project.id === 'ide' && run.status === 'conflict'" class="text-amber-300">Perubahan belum ditulis. Lihat riwayat langkah untuk detail konflik.</p>
      <p v-else-if="project.id === 'ide' && ['queued', 'running'].includes(run.status)" class="text-xs text-slate-400">Senopati bekerja di salinan. Perubahan terverifikasi ditulis otomatis ke folder-generated.</p>
      <p v-if="emptyReview" class="text-slate-300">Tugas berhenti tanpa perubahan file. Anda bisa langsung mengirim tugas baru.</p>
      <div v-if="run.status === 'queued' && run.stop_reason === 'worker_launch_failed'" role="alert">
        Worker gagal dimulai. Tugas masih dalam antrean.
        <button :disabled="busy" @click="act('retry-worker')">Coba lagi</button>
      </div>
      <p v-else-if="['running','queued'].includes(run.status)" role="status">
        {{ last?.message.split('\n')[0].slice(0,180) ?? 'Menunggu worker' }}
        <template v-if="last?.kind === 'provider_wait' && last.time_ms"> · giliran {{ last.turn }} · {{ Math.max(0, Math.floor((clock - last.time_ms) / 1000)) }} detik</template>
      </p>
      <details v-if="run.plan" class="task-detail"><summary>Rencana tugas</summary>
        <h4>{{ run.plan.goal }}</h4>
        <p>{{ run.plan.files.join(', ') }}</p>
        <ol><li v-for="step in run.plan.steps" :key="step">{{ step }}</li></ol>
        <ul><li v-for="(criterion, i) in run.plan.criteria" :key="i">
          {{ criterion.description }} · {{ criterion.check === 'manual' ? 'Perlu pemeriksaan manusia' : criterion.check }}
          {{ run.evidence?.evidence[i] }}
        </li></ul>
      </details>
      <p v-if="run.evidence">{{ run.evidence.summary }}</p>
      <p v-for="(check, name) in run.checks" :key="name" class="rounded-md bg-slate-950 px-3 py-2 text-xs" :class="check.exit_code === 0 ? 'text-emerald-300' : 'text-amber-300'">{{ name }} · {{ check.exit_code === 0 ? 'Lulus' : 'Gagal' }} (exit {{ check.exit_code }})</p>
      <label v-if="run.status === 'awaiting_input'">{{ run.question }}
        <textarea v-model="answer" class="block w-full bg-slate-800" />
        <button :disabled="busy || !answer.trim()" @click="act('answer')">Lanjutkan</button>
      </label>
      <details class="task-detail"><summary>Riwayat langkah dan transkrip</summary>
      <p v-if="run.stop_reason" class="text-xs text-slate-400">Alasan berhenti: {{ run.stop_reason }}</p>
      <label class="block"><input v-model="showTrace" type="checkbox"> Tampilkan transkrip Agent dan Senopati</label>
      <p v-if="showTrace && run.events.some(e => e.kind === 'provider_done') && !run.events.some(e => traceLabels[e.kind])">Transkrip tidak tersedia untuk giliran lama.</p>
      <section v-if="run.events.some(e => e.kind.startsWith('raw_'))" aria-label="Raw HTTP Senopati">
        <h4>Raw HTTP Senopati</h4>
        <a v-for="e in run.events.filter(e => e.kind.startsWith('raw_'))" :key="e.id"
          :href="`/ide/api/harness/runs/${run.id}/events/${e.id}/body`" target="_blank" rel="noopener"
          class="block underline">Giliran {{ e.turn }} · {{ e.message }}</a>
      </section>
      <details v-for="event in timeline" :key="event.id" :open="event.kind === 'tool_error' || event.kind === 'error'">
        <summary>{{ event.turn ? 'Giliran ' + event.turn + ' · ' : '' }}{{ event.kind === 'requested' ? 'Diminta · ' : '' }}{{ traceLabels[event.kind] || event.message.split('\n')[0].slice(0,180) }}
          {{ event.duration_ms == null ? '' : (event.duration_ms / 1000).toFixed(1) + ' dtk' }} {{ event.exit_code == null ? '' : '(exit ' + event.exit_code + ')' }}</summary>
        <pre class="max-w-full whitespace-pre-wrap [overflow-wrap:anywhere]">{{ event.message }}</pre>
      </details>
      </details>
      <details v-if="run.patch" class="task-detail"><summary>Patch mentah</summary><pre class="max-h-64 overflow-auto p-2 text-xs">{{ run.patch }}</pre></details>
      <button v-for="change in run.changes" :key="change.path" class="block rounded-md border border-slate-700 px-3 py-1.5 text-left text-indigo-200 hover:bg-slate-800" :disabled="change.before === null" @click="inspecting = change">{{ change.path }}</button>
      <DiffViewer v-if="inspecting" visible review-only :target-path="inspecting.path" :original-code="inspecting.before ?? ''" :modified-code="inspecting.after ?? ''" @close="inspecting = undefined" />
      <div class="flex flex-wrap gap-2">
        <button v-if="['queued','running'].includes(run.status)" class="task-button" :disabled="busy" @click="act('cancel')">Batalkan tugas</button>
        <button v-if="run.status === 'verified'" class="task-button bg-indigo-700 text-white" :disabled="busy" @click="act('apply')">Terapkan perubahan</button>
        <button v-if="!emptyReview && ['verified','review','conflict','awaiting_input'].includes(run.status)" class="task-button" :disabled="busy" @click="act('reject')">{{ run.status === 'awaiting_input' ? 'Akhiri tugas' : 'Tolak perubahan' }}</button>
      </div>
      </div>
    </section>
  </aside>
</template>

<style scoped>
.task-card {
  display: grid;
  grid-template-columns: minmax(0, 1fr);
  gap: 0.75rem;
  border: 1px solid #334155;
  border-radius: 0.25rem 1rem 1rem 1rem;
  background: #111a2b;
  padding: 1rem;
}

.task-detail {
  border: 1px solid #334155;
  border-radius: 0.5rem;
  background: #0b1220;
  padding: 0.625rem 0.75rem;
}

.task-detail summary {
  cursor: pointer;
  color: #c7d2fe;
}

.task-button {
  border: 1px solid #475569;
  border-radius: 0.5rem;
  padding: 0.375rem 0.75rem;
  font-size: 0.8125rem;
}

.task-button:disabled {
  opacity: 0.5;
}
</style>
