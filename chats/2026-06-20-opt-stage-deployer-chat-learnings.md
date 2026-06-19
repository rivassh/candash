# یادگیری‌ها — چت devops / stage-deployer / prompt-metrics

**Session:** `b8973106-6874-49f9-8474-e93f694cb515`  
**Workspace:** `/opt` (infra2)  
**تاریخ:** 2026-06-17 … 2026-06-20

---

## چیزهایی که احتمالاً «بلد نبودی» (یا نیاز به تثبیت داشت)

### 1. GitLab ≠ VPN

وقتی push fail شد، **VPN** علت درست نبود. از infra2:

- مسیر kernel به `192.168.160.30` از bridge Docker `192.168.160.0/20` می‌رفت.
- وقتی bridge **`linkdown`** بود → `No route to host`.
- GitLab از مرورگر/شبکهٔ دیگر reachable بود، ولی **از همین VM نه**.

**درس:** اول `nc -zv 192.168.160.30 2222` و `ip route get 192.168.160.30` — نه حدس VPN.

### 2. پروژه جابجا شده — remote قدیمی

`prompts/stage-deployer` → **`infra/stage-deployer`**.  
GitLab خودش در push می‌گوید URL جدید را ست کن.

### 3. `main` protected

push مستقیم به `main` رد می‌شود. الگوی درست: **`release/x.y.z` + MR**.

### 4. `devops` از قبل وجود داشت

ریپوی `infra/devops` خالی نبود (ESXi, MikroTik, VoIP). کار ما **merge/rebase** بود، نه repo تازه.

### 5. سیاست `/opt`

ریشه `/opt` فقط **پوشهٔ پروژه** — نه `.cursor/`، `index.html`، `scripts/` پراکنده. ابزار Cursor رفت زیر `stage-deployer/devops/`.

### 6. هزینه Cursor ≠ شناسه چت

CSV export **conversation id ندارد**. هزینهٔ هر prompt فقط با **بازه زمانی + مدل** تقریب زده می‌شود؛ ابزار محلی `measure-prompts.py` هم **تخمینی** است (bytes÷4).

### 7. امنیت session

چسباندن `WorkosCursorSessionToken` در چت = لو رفتن session. باید **rotate** شود؛ در git commit نشود.

### 8. Submodule workflow

ترتیب: push **devops** → bump pointer در parent → push **branch** (نه main protected).

---

## چیزهایی که خوب بلد بودی

- remote و SSH URL درست (`192.168.160.30:2222`)
- push قبلاً از همین سرور کار می‌کرد (reflog `update by push`)
- وقتی GitLab up شد، سریع گفتید «در دسترس قرار گرفت» — تشخیص درست مشکل شبکه vs سرویس

---

## یک خط برای چت بعدی

> قبل از push: `nc` + remote URL + protected branch + submodule order.
