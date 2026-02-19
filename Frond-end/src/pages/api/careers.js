export async function GET({ request }) {
  const url = new URL(request.url);
  const forceRefresh = url.searchParams.get('forceRefresh') || 'false';
  const apiBase = (import.meta.env.PUBLIC_API_BASE_URL ?? (() => { throw new Error('Falta PUBLIC_API_BASE_URL'); })()).replace(/\/$/, '');
  const upstreamUrl = new URL(`${apiBase}/v1/careers`);
  upstreamUrl.searchParams.set('forceRefresh', forceRefresh);

  const auth = request.headers.get('authorization') || '';
  const userRole = request.headers.get('x-user-role') || '';

  console.log('[PROXY] /api/careers llamado');
  console.log('[PROXY] Authorization header recibido:', auth ? `Bearer ${auth.substring(7, 20)}...` : 'NO PRESENTE');
  console.log('[PROXY] X-User-Role header:', userRole || 'NO PRESENTE');

  const upstreamRes = await fetch(upstreamUrl.toString(), {
    method: 'GET',
    headers: {
      'Accept': 'application/json',
      ...(auth ? { 'Authorization': auth } : {}),
      ...(userRole ? { 'X-User-Role': userRole } : {})
    }
  });

  console.log('[PROXY] Backend respondió:', upstreamRes.status, upstreamRes.statusText);
  
  const body = await upstreamRes.text();
  console.log('[PROXY] Body:', body.substring(0, 200));
  
  return new Response(body, {
    status: upstreamRes.status,
    headers: {
      'Content-Type': upstreamRes.headers.get('content-type') || 'application/json'
    }
  });
}
