import express from 'express';
import { chromium } from 'playwright';
import Redis from 'ioredis';
import { exec } from 'child_process';
import { createServer } from 'http';
import { WebSocketServer } from 'ws';
import { once } from 'events';
import { createRequire } from 'module';
const require = createRequire(import.meta.url);

const app = express();
app.use(express.json());

const sessions = new Map();
const COMPLETE = 'complete';
const TIMEOUT = 'timeout';
const redis = new Redis(process.env.REDIS_URL || 'redis://redis:6379');
const REDIS_PREFIX = 'jobvision_browser_sessions:';

const NO_VNC_ROOT = '/usr/share/novnc';
const VNC_HOST = '127.0.0.1';
const VNC_PORT = 5900;
const WEBSOCKIFY_PORT = 6080;
const BROWSER_AGENT_PORT = 9999;
const DISPLAY = ':99';

const log = (stage, message, sessionId = '') => {
    console.log(JSON.stringify({
        stage,
        session_id: sessionId,
        message,
        timestamp: new Date().toISOString(),
    }));
};

// ── ONE-TIME SETUP: Xvfb + x11vnc + websockify (shared across sessions) ──
const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));

async function waitForPort(port, host = VNC_HOST, timeoutMs = 10000) {
    const started = Date.now();
    while (Date.now() - started < timeoutMs) {
        try {
            const net = require('net');
            const socket = net.createConnection({ host, port });
            await once(socket, 'connect');
            socket.destroy();
            return true;
        } catch {
            await sleep(250);
        }
    }
    return false;
}

async function startVncStack() {
    try {
        exec('Xvfb ' + DISPLAY + ' -screen 0 1024x768x24', (error) => {
            if (error) log('vnc_setup', 'Xvfb failed to start', error.message);
        });

        await sleep(1500);
        process.env.DISPLAY = DISPLAY;

        exec('x11vnc -display ' + DISPLAY + ' -rfbport ' + VNC_PORT + ' -forever -quiet', (error) => {
            if (error) log('vnc_setup', 'x11vnc failed to start', error.message);
        });

        await waitForPort(VNC_PORT);
        log('vnc_setup', 'x11vnc ready', DISPLAY);

        exec('websockify --web=' + NO_VNC_ROOT + ' ' + WEBSOCKIFY_PORT + ' ' + VNC_HOST + ':' + VNC_PORT, (error) => {
            if (error) log('vnc_setup', 'websockify failed to start', error.message);
        });

        await waitForPort(WEBSOCKIFY_PORT);
        log('vnc_setup', 'websockify ready', WEBSOCKIFY_PORT);
    } catch (error) {
        log('vnc_setup', 'VNC stack failed to start', error.message);
    }
}

startVncStack();

app.get('/health', (req, res) => { res.status(200).json({ status: 'ok' }); });

app.post('/sessions', async (req, res) => {
    const id = req.body.session_id || ('sess_' + Date.now() + '_' + Math.random().toString(36).slice(2, 8));
    const accountUrl = req.body.account_url || 'https://account.jobvision.ir';
    const session = new BrowserSession(id, accountUrl);
    sessions.set(id, session);
    session.start().catch((error) => {
        log('session', 'session start failed', error.message, id);
    });
    log('session', 'session created', id);
    res.json({ sessionId: id });
});

function readNovncHtml() {
    const fs = require('fs');
    const path = require('path');
    const vncHtmlPath = path.join(NO_VNC_ROOT, 'vnc.html');
    try {
        return fs.readFileSync(vncHtmlPath, 'utf8');
    } catch {
        return '';
    }
}

app.get('/sessions/:id/vnc', (req, res) => {
    const session = sessions.get(req.params.id);
    if (!session) return res.status(404).json({ error: 'Session not found' });

    const html = readNovncHtml()
        .replace(/<\?xml[^?]*\?>/g, '')
        .replace(/class="noVNC_loading"/g, 'class="noVNC_loading" data-autoconnect="true"');

    const modifiedHtml = html.replace(
        /<title>noVNC<\/title>/,
        '<title>noVNC - ' + req.params.id + '</title>'
    );

    log('vnc', 'serving noVNC HTML', req.params.id);
    res.setHeader('Content-Type', 'text/html; charset=utf-8');
    res.send(modifiedHtml);
});

const server = createServer(app);

// WebSocket upgrade handling for VNC
const wss = new WebSocketServer({ noServer: true });

wss.on('connection', (ws, req) => {
    const url = req.url || '';
    const match = url.match(/\/(websockify|vnc-ws)/);

    if (!match) {
        ws.close(4000, 'Invalid WebSocket path');
        return;
    }

    const query = url.split('?')[1] || '';
    const params = new URLSearchParams(query);
    const sessionId = params.get('session_id') || '';

    log('vnc', 'WebSocket connection opened', sessionId);

    const wsProxy = new (require('ws'))('ws://' + VNC_HOST + ':' + WEBSOCKIFY_PORT + '/');

    wsProxy.on('open', () => {
        log('vnc', 'WebSocket proxy connected to websockify', sessionId);
    });

    wsProxy.on('message', (data) => {
        if (ws.readyState === ws.OPEN) ws.send(data);
    });

    ws.on('message', (data) => {
        if (wsProxy.readyState === ws.OPEN) wsProxy.send(data);
    });

    ws.on('close', () => {
        wsProxy.close();
        log('vnc', 'WebSocket connection closed', sessionId);
    });

    wsProxy.on('close', () => {
        ws.close();
        log('vnc', 'WebSocket proxy closed', sessionId);
    });

    wsProxy.on('error', (error) => {
        log('vnc', 'WebSocket proxy error: ' + error.message, sessionId);
        ws.close(1011, 'Proxy error');
    });

    ws.on('error', (error) => {
        log('vnc', 'WebSocket client error: ' + error.message, sessionId);
    });
});

server.on('upgrade', (request, socket, head) => {
    if (request.url?.includes('/websockify') || request.url?.includes('/vnc-ws')) {
        wss.handleUpgrade(request, socket, head, (ws) => {
            wss.emit('connection', ws, request);
        });
    } else {
        socket.destroy();
    }
});

app.get('/sessions/:id/screenshot', async (req, res) => {
    const session = sessions.get(req.params.id);
    if (!session || !session.page) return res.status(404).json({ error: 'Not found' });
    try {
        const buf = await session.page.screenshot({ type: 'png' });
        res.json({ data: buf.toString('base64') });
    } catch { res.status(500).json({ error: 'Screenshot failed' }); }
});

app.get('/sessions/:id/status', (req, res) => {
    const session = sessions.get(req.params.id);
    if (!session) return res.status(404).json({ error: 'Not found' });

    const vncReady = session.state === 'waiting_for_login' || session.state === COMPLETE;

    res.json({
        status: session.state,
        cookies: session.cookies,
        vnc_ready: vncReady,
        backend_ready: session.browser !== null,
    });
});

app.delete('/sessions/:id', (req, res) => {
    const session = sessions.get(req.params.id);
    if (session) {
        log('session', 'deleting session', req.params.id);
        session.cancel();
    }
    res.json({ ok: true });
});

class BrowserSession {
    constructor(id, accountUrl) {
        this.id = id; this.accountUrl = accountUrl; this.state = 'starting';
        this.browser = null; this.context = null; this.page = null;
        this.cookies = []; this.cancelled = false;
        this.timeout = setTimeout(() => this.expire(), 10 * 60 * 1000);
    }

    async start() {
        // Ensure display is set (one-time setup already ran, but be safe)
        if (!process.env.DISPLAY) process.env.DISPLAY = ':99';

        // Launch Chromium headed (plays on Xvfb :99)
        this.browser = await chromium.launch({
            headless: false,
            args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage'],
            executablePath: '/usr/bin/chromium',
        });
        this.context = await this.browser.newContext();
        this.page = await this.context.newPage();
        this.state = 'waiting_for_login';
        await this.page.goto(this.accountUrl).catch(() => {});
        this.pollLogin();
    }

    async pollLogin() {
        if (this.cancelled || this.state === COMPLETE) return;
        try {
            const cookies = await this.context.cookies();
            const required = ['__smid', 'SERVERID', 'user'];
            this.cookies = required.map(name => cookies.find(c => c.name === name)).filter(Boolean);
            if (this.cookies.length === 3) {
                this.state = COMPLETE;
                await this.saveCookies();
                this.close();
            }
        } catch {}
        setTimeout(() => this.pollLogin(), 2000);
    }

    async saveCookies() {
        try {
            await redis.set(`${REDIS_PREFIX}${this.id}:cookies`, JSON.stringify(
                this.cookies.map(c => ({ name: c.name, value: c.value, domain: c.domain }))
            ));
            await redis.set(`${REDIS_PREFIX}${this.id}:status`, COMPLETE);
        } catch {}
    }

    expire() { if (this.state !== COMPLETE) { this.state = TIMEOUT; this.close(); } }

    async close() {
        clearTimeout(this.timeout);
        try { if (this.browser) await this.browser.close(); } catch {}
        sessions.delete(this.id);
    }

    cancel() { this.cancelled = true; this.close(); }
}

server.listen(9999, () => console.log('Browser agent on port 9999 with Xvfb/noVNC support'));