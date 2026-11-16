export async function streamPrompt(url: string, body: unknown, csrf: string, onEvent: (event: string, data: any) => void) {
  const response = await fetch(url, {
    method: 'POST',
    headers: { Accept: 'text/event-stream, application/json;q=0.9', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
    body: JSON.stringify(body),
  });
  if (!response.ok || !response.headers.get('content-type')?.includes('text/event-stream') || !response.body) {
    const data = await response.json().catch(() => ({}));
    throw new Error(data.error || data.message || 'Chat stream unavailable (HTTP ' + response.status + ')');
  }
  const reader = response.body.pipeThrough(new TextDecoderStream()).getReader();
  let buffer = '';
  try {
    while (true) {
      const { value, done } = await reader.read();
      if (done) throw new Error('Koneksi terputus sebelum respons selesai.');
      buffer += value;
      const frames = buffer.split(/\r?\n\r?\n/);
      buffer = frames.pop()!;
      for (const frame of frames) {
        const lines = frame.split(/\r?\n/);
        const event = lines.find(line => line.startsWith('event:'))?.slice(6).trim();
        if (!event) continue;
        const data = JSON.parse(lines.filter(line => line.startsWith('data:')).map(line => line.slice(5)).join('\n'));
        if (event === 'error') throw new Error(data.error || 'Agent error');
        if (event === 'done') return data;
        onEvent(event, data);
      }
    }
  } finally {
    await reader.cancel();
    reader.releaseLock();
  }
}
