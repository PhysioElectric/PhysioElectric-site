const rid = () => crypto.randomUUID();

export async function api(path, body, { signal } = {}) {
  const t0 = performance.now();
  const res = await fetch(path, {
    method: body ? "POST" : "GET",
    headers: {
      "Content-Type": "application/json",
      "X-Request-Id": rid(),
      "X-Lab-Client": "physio-glass/1",
    },
    body: body ? JSON.stringify(body) : undefined,
    signal,
  });
  const ms = Math.round(performance.now() - t0);
  let json = null;
  try {
    json = await res.json();
  } catch {
    json = null;
  }
  if (!res.ok) {
    const err = new Error(json?.message || json?.error || `HTTP ${res.status}`);
    err.status = res.status;
    err.payload = json;
    err.ms = ms;
    throw err;
  }
  return { ...json, netMs: ms };
}

export function labSocket(board) {
  const proto = location.protocol === "https:" ? "wss" : "ws";
  return new WebSocket(`${proto}://${location.host}/ws/lab?board=${encodeURIComponent(board)}`);
}
