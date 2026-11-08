const corsHeaders = {
  "Access-Control-Allow-Origin": "*",
  "Access-Control-Allow-Methods": "GET, POST, PUT, PATCH, DELETE, OPTIONS",
  "Access-Control-Allow-Headers": "Content-Type, Authorization",
  "Access-Control-Max-Age": "86400"
};

function json(data, status = 200, extraHeaders = {}) {
  return new Response(JSON.stringify(data), {
    status,
    headers: {
      "Content-Type": "application/json; charset=utf-8",
      "Cache-Control": "no-store",
      "X-Content-Type-Options": "nosniff",
      ...corsHeaders,
      ...extraHeaders
    }
  });
}

function backendOrigin(env) {
  const raw = (env.BACKEND_ORIGIN || "").trim();
  if (!raw) return null;
  try {
    const url = new URL(raw);
    if (url.protocol !== "https:" || url.username || url.password) return null;
    return url.origin;
  } catch {
    return null;
  }
}

function landingPage(isConfigured) {
  const apiState = isConfigured ? "Backend address configured" : "PHP backend not connected";
  const statusColor = isConfigured ? "#16c784" : "#f2b84b";
  return new Response(`<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="theme-color" content="#101820">
  <title>REALWIN Services</title>
  <style>
    *{box-sizing:border-box}body{margin:0;min-height:100vh;background:#101820;color:#f4f7fa;font:16px system-ui,Arial,sans-serif;display:grid;place-items:center;padding:24px}
    main{width:min(100%,620px);background:#1c2832;border:1px solid #354652;border-radius:22px;padding:28px}
    .brand{color:#16c784;font-weight:800;letter-spacing:2px;font-size:13px}
    h1{margin:12px 0 8px;font-size:30px}p{color:#bdc9d1;line-height:1.6}
    .state{margin:22px 0;padding:14px;border-radius:12px;background:#101820;border-left:4px solid ${statusColor}}
    .state strong{color:${statusColor}}code{color:#16c784;overflow-wrap:anywhere}
    a{color:#16c784}small{color:#9aaab5}
  </style>
</head>
<body><main>
  <div class="brand">REALWIN • SERVICE GATEWAY</div>
  <h1>REALWIN services</h1>
  <p>The Cloudflare edge gateway is deployed and responding. This endpoint is not the complete Android app or PHP application.</p>
  <div class="state"><strong>Gateway online</strong><br><span>${apiState}</span></div>
  <p>Health endpoint: <a href="/health"><code>/health</code></a></p>
  <p>API routes: <code>/api/*</code></p>
  <small>Entertainment points only. No cash deposits, wagering, withdrawals, or cash-out.</small>
</main></body></html>`, {
    status: 200,
    headers: {
      "Content-Type": "text/html; charset=utf-8",
      "Cache-Control": "no-store",
      "X-Content-Type-Options": "nosniff",
      "Content-Security-Policy": "default-src 'none'; style-src 'unsafe-inline'; base-uri 'none'; frame-ancestors 'none'"
    }
  });
}

export default {
  async fetch(request, env) {
    const url = new URL(request.url);
    const origin = backendOrigin(env);

    if (request.method === "OPTIONS") {
      return new Response(null, { status: 204, headers: corsHeaders });
    }

    if (url.pathname === "/" && request.method === "GET") {
      return landingPage(Boolean(origin));
    }

    if ((url.pathname === "/health" || url.pathname === "/api/health") && request.method === "GET") {
      return json({
        ok: true,
        service: "REALWIN Cloudflare Gateway",
        gateway: "online",
        backend: origin ? "configured" : "not_configured",
        ready: Boolean(origin),
        timestamp: new Date().toISOString()
      });
    }

    if (url.pathname === "/status" && request.method === "GET") {
      return json({
        service: "REALWIN",
        cloudflare_worker: "online",
        php_backend: origin ? "configured" : "not_configured",
        database: "not_verified"
      });
    }

    if (url.pathname === "/api" || url.pathname.startsWith("/api/")) {
      if (!origin) {
        return json({
          ok: false,
          error: "backend_not_configured",
          message: "Set BACKEND_ORIGIN to the HTTPS base URL of the deployed REALWIN PHP backend."
        }, 503);
      }

      const target = new URL(origin);
      target.pathname = url.pathname;
      target.search = url.search;
      try {
        const upstreamRequest = new Request(target.toString(), request);
        const upstream = await fetch(upstreamRequest, { redirect: "manual" });
        const headers = new Headers(upstream.headers);
        for (const [key, value] of Object.entries(corsHeaders)) headers.set(key, value);
        headers.set("Cache-Control", "no-store");
        headers.set("X-Content-Type-Options", "nosniff");
        return new Response(upstream.body, {
          status: upstream.status,
          statusText: upstream.statusText,
          headers
        });
      } catch (error) {
        console.error("REALWIN backend proxy error", error);
        return json({ ok: false, error: "backend_unavailable" }, 502);
      }
    }

    return json({ ok: false, error: "not_found" }, 404);
  }
};
