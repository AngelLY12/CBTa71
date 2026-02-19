const API_BASE = `${(import.meta.env.PUBLIC_API_BASE_URL ?? (() => { throw new Error('Falta PUBLIC_API_BASE_URL'); })()).replace(/\/$/, '')}/v1`;

function firstUrl(value) {
  if (!value) return null;
  if (typeof value === 'string') {
    if (value.startsWith('http://') || value.startsWith('https://')) return value;
    return null;
  }
  if (Array.isArray(value)) {
    for (const item of value) {
      const found = firstUrl(item);
      if (found) return found;
    }
    return null;
  }
  if (typeof value === 'object') {
    const direct = value.url || value.receipt_url || value.signed_url || value.link || value.href;
    const directFound = firstUrl(direct);
    if (directFound) return directFound;

    const nestedKeys = ['data', 'result', 'receipt', 'payload'];
    for (const key of nestedKeys) {
      if (key in value) {
        const found = firstUrl(value[key]);
        if (found) return found;
      }
    }
  }
  return null;
}

export const prerender = false;

export async function GET({ params, request }) {
  const paymentId = params?.paymentId;
  const requestUrl = new URL(request.url);
  const mode = (requestUrl.searchParams.get('mode') || '').toLowerCase();
  const openMode = mode === 'open';

  if (!paymentId) {
    return new Response(JSON.stringify({ success: false, message: 'paymentId requerido', errors: {} }), {
      status: 400,
      headers: { 'Content-Type': 'application/json' }
    });
  }

  const auth = request.headers.get('authorization') || '';
  const role = request.headers.get('x-user-role') || 'student';
  const permission = request.headers.get('x-user-permission') || 'view.receipt';

  if (!auth) {
    return new Response(JSON.stringify({ success: false, message: 'Unauthenticated', errors: {} }), {
      status: 401,
      headers: { 'Content-Type': 'application/json' }
    });
  }

  const upstreamUrl = `${API_BASE}/payments/history/receipt/${paymentId}`;

  try {
    const upstreamRes = await fetch(upstreamUrl, {
      method: 'GET',
      redirect: 'manual',
      headers: {
        'Authorization': auth,
        'Accept': 'application/json',
        'X-User-Role': role,
        'X-User-Permission': permission
      }
    });

    const contentType = upstreamRes.headers.get('content-type') || '';
    const locationHeader = upstreamRes.headers.get('location');

    if (locationHeader && [301, 302, 303, 307, 308].includes(upstreamRes.status)) {
      if (openMode) {
        return Response.redirect(locationHeader, 302);
      }

      const expiresIn = (() => {
        try {
          const url = new URL(locationHeader);
          return Number(url.searchParams.get('X-Goog-Expires') || url.searchParams.get('Expires') || 0) || null;
        } catch {
          return null;
        }
      })();

      return new Response(JSON.stringify({
        success: true,
        data: {
          url: locationHeader,
          expires_in: expiresIn,
          content_type: 'text/html'
        },
        message: 'Recibo generado correctamente'
      }), {
        status: 200,
        headers: {
          'Content-Type': 'application/json',
          'Cache-Control': 'no-store'
        }
      });
    }

    if (contentType.includes('application/json')) {
      const bodyText = await upstreamRes.text();
      const parsed = (() => {
        try {
          return JSON.parse(bodyText);
        } catch {
          return null;
        }
      })();

      if (parsed && upstreamRes.ok) {
        const url = firstUrl(parsed);
        if (url) {
          if (openMode) {
            return Response.redirect(url, 302);
          }

          const data = parsed?.data && typeof parsed.data === 'object' ? parsed.data : {};
          return new Response(JSON.stringify({
            ...parsed,
            success: parsed?.success ?? true,
            data: {
              ...data,
              url,
              content_type: data.content_type || 'text/html'
            }
          }), {
            status: upstreamRes.status,
            headers: {
              'Content-Type': 'application/json',
              'Cache-Control': 'no-store'
            }
          });
        }
      }

      if (openMode) {
        return new Response(bodyText || JSON.stringify({ success: false, message: 'No se encontró URL de recibo', errors: {} }), {
          status: upstreamRes.status,
          headers: {
            'Content-Type': 'application/json',
            'Cache-Control': 'no-store'
          }
        });
      }

      return new Response(bodyText, {
        status: upstreamRes.status,
        headers: {
          'Content-Type': 'application/json',
          'Cache-Control': 'no-store'
        }
      });
    }

    if (upstreamRes.ok) {
      if (openMode) {
        const htmlBody = await upstreamRes.text();
        return new Response(htmlBody, {
          status: 200,
          headers: {
            'Content-Type': contentType || 'text/html; charset=utf-8',
            'Cache-Control': 'no-store'
          }
        });
      }

      return new Response(JSON.stringify({
        success: true,
        data: {
          url: upstreamRes.url,
          expires_in: null,
          content_type: contentType || 'text/html'
        },
        message: 'Recibo generado correctamente'
      }), {
        status: 200,
        headers: {
          'Content-Type': 'application/json',
          'Cache-Control': 'no-store'
        }
      });
    }

    const errorText = await upstreamRes.text();
    return new Response(errorText || JSON.stringify({ success: false, message: 'Error al generar el recibo', errors: {} }), {
      status: upstreamRes.status,
      headers: {
        'Content-Type': 'application/json',
        'Cache-Control': 'no-store'
      }
    });
  } catch (error) {
    return new Response(JSON.stringify({ success: false, message: 'Error al generar el recibo', errors: {} }), {
      status: 500,
      headers: { 'Content-Type': 'application/json' }
    });
  }
}
