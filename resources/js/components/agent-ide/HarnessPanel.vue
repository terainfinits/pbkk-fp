<!-- Fitur: Harness tugas coding
connect with ../../../../app/Console/Commands/RegisterWorkspace.php, ../../../../routes/harness.php, ../../pages/AgentIdeApp.vue
connect with ./DiffViewer.vue -->
<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import DiffViewer from './DiffViewer.vue';

type Workspace = { id: number; name: string; latest: number | null };
type RunEvent = { id: number; kind: string; message: string; exit_code: number | null };
type Change = { path: string; before: string | null; after: string | null };
type Run = { id: number; status: string; task: string; patch: string | null; events: RunEvent[]; changes: Change[] };
const inspecting = ref<Change>();

const workspaces = ref<Workspace[]>([]);
const selected = ref<number>();
const run = ref<Run>();
const task = ref('');
const error = ref('');
const busy = ref(false);
let timer: ReturnType<typeof setTimeout>;
let generation = 0;
const active = computed(() => ['queued', 'running', 'cancelling', 'review', 'conflict'].includes(run.value?.status ?? ''));
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
  try {
    const data: Run = await request('runs/' + id + '?after=' + (previous[previous.length - 1]?.id ?? 0));
    if (generation !== version) return;
    run.value = { ...data, events: [...previous, ...data.events] };
    error.value = '';
    if (!['queued','running','cancelling'].includes(data.status)) return;
  } catch (e) {
    if (generation !== version) return;
    report(e);
  }
  timer = setTimeout(() => load(id, version), 1500);
}
watch(selected, id => {
  generation++;
  clearTimeout(timer);
  run.value = undefined;
  inspecting.value = undefined;
  const latest = workspaces.value.find(w => w.id === id)?.latest;
  if (latest) void load(latest, generation);
});
onMounted(async () => {
  try {
    workspaces.value = await request('workspaces');
    selected.value = workspaces.value[0]?.id;
  } catch (e) { report(e); }
});
onUnmounted(() => { generation++; clearTimeout(timer); });
async function act(action: string) {
  busy.value = true;
  error.value = '';
  const version = ++generation;
  clearTimeout(timer);
  let id = run.value?.id;
  try {
    if (action === 'start') {
      id = (await request('workspaces/' + selected.value + '/runs', { task: task.value })).id;
      run.value = undefined;
      const space = workspaces.value.find(w => w.id === selected.value);
      if (space) space.latest = id ?? null;
    } else await request('runs/' + id + '/' + action, {});
    if (id) await load(id, version);
  } catch (e) {
    if (id) await load(id, version);
    report(e);
  } finally { busy.value = false; }
}
</script>

<template>
  <aside class="w-2/5 overflow-auto space-y-3 p-4 select-text" aria-label="Tugas coding">
    <label class="block">Workspace
      <select v-model="selected" :disabled="busy" class="block bg-slate-800">
        <option v-for="space in workspaces" :key="space.id" :value="space.id">{{ space.name }}</option>
      </select>
    </label>
    <p v-if="!workspaces.length">Daftarkan repo dengan <code>php artisan harness:register</code>.</p>
    <textarea v-model="task" aria-label="Instruksi tugas" placeholder="Apa yang perlu diubah?" class="block w-full bg-slate-800 p-2" />
    <button :disabled="!selected || busy || active || !task.trim()" @click="act('start')">Mulai tugas</button>
    <p v-if="error" role="alert">{{ error }}</p>
    <section v-if="run" class="space-y-2">
      <h3>{{ run.task }}</h3>
      <p>Status: {{ run.status }}</p>
      <details v-for="event in run.events" :key="event.id" :open="event.kind !== 'tool'">
        <summary>{{ event.kind }} {{ event.exit_code == null ? '' : '(exit ' + event.exit_code + ')' }}</summary>
        <pre class="whitespace-pre-wrap">{{ event.message }}</pre>
      </details>
      <pre v-if="run.patch" class="overflow-auto bg-slate-950 p-2">{{ run.patch }}</pre>
      <p v-else-if="run.status === 'review'">Tidak ada perubahan file.</p>
      <button v-for="change in run.changes" :key="change.path" class="block underline" :disabled="change.before === null" @click="inspecting = change">{{ change.path }}</button>
      <DiffViewer v-if="inspecting" visible review-only :target-path="inspecting.path" :original-code="inspecting.before ?? ''" :modified-code="inspecting.after ?? ''" @close="inspecting = undefined" />
      <button v-if="['queued','running'].includes(run.status)" :disabled="busy" @click="act('cancel')">Batalkan</button>
      <button v-if="run.status === 'review'" :disabled="busy" @click="act('apply')">Terapkan</button>
      <button v-if="['review','conflict'].includes(run.status)" :disabled="busy" @click="act('reject')">Tolak</button>
    </section>
  </aside>
</template>
