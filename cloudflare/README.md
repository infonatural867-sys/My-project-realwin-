# REALWIN Cloudflare gateway

This Worker is the edge/API gateway for the REALWIN PHP/MySQL backend. It is not a PHP runtime and does not replace the Android app.

## Routes
- `/` — deployment status page
- `/health` — Worker health and backend configuration status
- `/status` — service status JSON
- `/api/*` — forwards to the configured PHP backend

## Connect the PHP backend
Set the Worker variable `BACKEND_ORIGIN` to the exact HTTPS base URL where the REALWIN PHP API is deployed. Example format only: `https://your-domain.example`. Do not add `/api` to the value. Do not put database credentials or private keys here.

Until this variable is set, `/api/*` safely returns HTTP 503 with `backend_not_configured`. The health endpoint reports the missing connection rather than pretending the database is online.

## Deploy
The repository root contains `wrangler.jsonc`. Deploy with the current Wrangler CLI after authenticating to the intended Cloudflare account. Logs and traces are enabled.

## Scope
REALWIN points are non-cash entertainment points only. This gateway does not add payment, wagering, withdrawal, or cash-out features.
