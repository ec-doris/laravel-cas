import http from 'node:http';
import { randomUUID } from 'node:crypto';

const host = process.env.CAS_STUB_HOST ?? '127.0.0.1';
const port = Number(process.env.CAS_STUB_PORT ?? '9800');

const users = new Map([
  [
    'casuser',
    {
      password: 'Mellon',
      principal: 'casuser',
      attributes: {
        email: 'cas.user@example.test',
        firstName: 'Cas',
        lastName: 'User',
        departmentNumber: 'DIGIT.A.4',
      },
    },
  ],
]);

const tickets = new Map();

const server = http.createServer(async (request, response) => {
  try {
    await route(request, response);
  } catch (error) {
    response.writeHead(500, { 'content-type': 'application/json; charset=utf-8' });
    response.end(JSON.stringify({
      error: error instanceof Error ? error.message : 'Unexpected CAS stub error.',
    }));
  }
});

server.listen(port, host, () => {
  process.stdout.write(`CAS stub listening on http://${host}:${port}/cas\n`);
});

function sendHtml(response, statusCode, html) {
  response.writeHead(statusCode, { 'content-type': 'text/html; charset=utf-8' });
  response.end(html);
}

function sendJson(response, statusCode, payload) {
  response.writeHead(statusCode, { 'content-type': 'application/json; charset=utf-8' });
  response.end(JSON.stringify(payload));
}

function redirect(response, location) {
  response.writeHead(302, { location });
  response.end();
}

async function route(request, response) {
  const origin = `http://${host}:${port}`;
  const url = new URL(request.url ?? '/', origin);

  if (request.method === 'GET' && url.pathname === '/cas/__health') {
    sendJson(response, 200, { status: 'ok' });

    return;
  }

  if (request.method === 'GET' && url.pathname === '/cas/login') {
    sendHtml(response, 200, renderLoginPage({
      service: url.searchParams.get('service') ?? '',
      error: '',
    }));

    return;
  }

  if (request.method === 'POST' && url.pathname === '/cas/login') {
    const body = await readFormBody(request);
    const service = body.get('service') ?? '';
    const username = body.get('username') ?? '';
    const password = body.get('password') ?? '';
    const user = users.get(username);

    if (service === '' || !user || user.password !== password) {
      sendHtml(response, 401, renderLoginPage({
        service,
        error: 'Invalid credentials. Use casuser / Mellon.',
      }));

      return;
    }

    const ticket = `ST-${randomUUID()}`;
    tickets.set(ticket, {
      service,
      user,
    });

    const callbackUrl = new URL(service);
    callbackUrl.searchParams.set('ticket', ticket);
    redirect(response, callbackUrl.toString());

    return;
  }

  if (request.method === 'GET' && url.pathname === '/cas/logout') {
    const service = url.searchParams.get('service');

    if (service) {
      redirect(response, service);

      return;
    }

    sendHtml(response, 200, '<h1>Logged out</h1>');

    return;
  }

  if (request.method === 'GET' && url.pathname === '/cas/p3/serviceValidate') {
    const ticket = url.searchParams.get('ticket') ?? '';
    const service = url.searchParams.get('service') ?? '';
    const issuedTicket = tickets.get(ticket);

    if (!issuedTicket) {
      sendJson(response, 401, buildFailureResponse('INVALID_TICKET', `Ticket ${ticket} is unknown.`));

      return;
    }

    if (issuedTicket.service !== service) {
      sendJson(
        response,
        401,
        buildFailureResponse('INVALID_SERVICE', 'Ticket validation attempted with a different service URL.')
      );

      return;
    }

    tickets.delete(ticket);
    sendJson(response, 200, {
      serviceResponse: {
        authenticationSuccess: {
          user: issuedTicket.user.principal,
          attributes: issuedTicket.user.attributes,
        },
      },
    });

    return;
  }

  sendJson(response, 404, {
    error: 'Not found.',
    path: url.pathname,
  });
}

function buildFailureResponse(code, description) {
  return {
    serviceResponse: {
      authenticationFailure: {
        code,
        description,
      },
    },
  };
}

function renderLoginPage({ service, error }) {
  return `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Local CAS Login</title>
  <style>
    :root {
      color-scheme: light;
      font-family: Georgia, "Times New Roman", serif;
      --bg: #f3efe6;
      --card: #fffdf8;
      --ink: #1f2430;
      --muted: #5f6875;
      --accent: #8f331f;
      --line: #d9cdb8;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      min-height: 100vh;
      display: grid;
      place-items: center;
      background:
        radial-gradient(circle at top left, rgba(13, 76, 146, 0.12), transparent 32%),
        radial-gradient(circle at bottom right, rgba(143, 51, 31, 0.12), transparent 30%),
        var(--bg);
      color: var(--ink);
    }
    main {
      width: min(460px, calc(100vw - 32px));
      padding: 32px;
      background: var(--card);
      border: 1px solid var(--line);
      border-radius: 24px;
      box-shadow: 0 24px 60px rgba(31, 36, 48, 0.08);
    }
    h1 {
      margin: 0 0 10px;
      font-size: 2.5rem;
      line-height: 0.95;
    }
    p {
      margin: 0 0 18px;
      color: var(--muted);
      line-height: 1.5;
    }
    label {
      display: block;
      margin-bottom: 14px;
      font-size: 0.95rem;
    }
    input {
      width: 100%;
      margin-top: 6px;
      border: 1px solid var(--line);
      border-radius: 14px;
      padding: 12px 14px;
      font: inherit;
    }
    button {
      width: 100%;
      border: 0;
      border-radius: 999px;
      padding: 13px 16px;
      background: var(--accent);
      color: #fff;
      font: inherit;
      font-weight: 700;
      cursor: pointer;
    }
    .hint, .error {
      border-radius: 16px;
      padding: 12px 14px;
      margin-bottom: 18px;
    }
    .hint {
      background: rgba(13, 76, 146, 0.08);
    }
    .error {
      background: rgba(143, 51, 31, 0.12);
      color: var(--accent);
    }
  </style>
</head>
<body>
  <main>
    <h1>Local CAS Login</h1>
    <p>This protocol test server issues real service tickets for the Workbench app.</p>
    <div class="hint">Use <strong>casuser</strong> / <strong>Mellon</strong>.</div>
    ${error ? `<div class="error">${escapeHtml(error)}</div>` : ''}
    <form method="post" action="/cas/login">
      <input type="hidden" name="service" value="${escapeHtml(service)}">
      <label>
        Username
        <input name="username" type="text" autocomplete="username" required>
      </label>
      <label>
        Password
        <input name="password" type="password" autocomplete="current-password" required>
      </label>
      <button type="submit">Sign in</button>
    </form>
  </main>
</body>
</html>`;
}

function escapeHtml(value) {
  return value
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;');
}

async function readFormBody(request) {
  const chunks = [];

  for await (const chunk of request) {
    chunks.push(Buffer.isBuffer(chunk) ? chunk : Buffer.from(chunk));
  }

  return new URLSearchParams(Buffer.concat(chunks).toString('utf8'));
}
