import express from 'express';
import { chromium } from 'playwright';
import Redis from 'ioredis';
import { exec } from 'child_process';

const app = express();
app.use(express.json());

const sessions = new Map();
const COMPLETE = 'complete';
const TIMEOUT = 'timeout';
const redis = new Redis(process.env.REDIS_URL || 'redis://talentmatch-redis:6379');
const REDIS_PREFIX = 'jobvision_browser_sessions:';

// ── ONE-TIME SETUP: Xvfb + x11vnc + websockify (shared across sessions) ──
exec('Xvfb :99 -screen 0 1024x768x24 &>/dev/null &', () => {});
setTimeout(() => {
    process.env.DISPLAY = ':99';
    exec('x11vnc -display :99 -rfbport 5900 -forever -quiet &>/dev/null &', () => {});
    setTimeout(() => {
        exec('websockify --web=/usr/share/novnc 6080 localhost:5900 &>/dev/null &', () => {});
    }, 500);
}, 1500);

app.post('/sessions', async (req, res) => {
    const id = req.body.session_id || ('sess_' + Date.now() + '_' + Math.random().toString(36).slice(2, 8));
    const accountUrl = req.body.account_url || 'https://account.jobvision.ir';
    const session = new BrowserSession(id, accountUrl);
    sessions.set(id, session);
    session.start().catch(() => {});
    res.json({ sessionId: id });
});

app.get('/sessions/:id/vnc', (req, res) => {
    const session = sessions.get(req.params.id);
    if (!session) return res.status(404).json({ error: 'Session not found' });
    // Extract hostname only (strip port) so the iframe URL is valid
    const host = req.get('host')?.split(':')[0] || 'localhost';
    res.send(`<iframe src="http://${host}:6080/?path=/" style="width:100%;height:100%;border:none;"></iframe>`);
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
    res.json({ status: session.state, cookies: session.cookies });
});

app.delete('/sessions/:id', (req, res) => {
    const session = sessions.get(req.params.id);
    if (session) session.cancel();
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

app.listen(9999, () => console.log('Browser agent on port 9999 with Xvfb/noVNC support'));