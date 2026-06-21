"""RTL Persian PDF rendering helpers for llfs daily reports."""

from __future__ import annotations

import html
import re
from pathlib import Path

import markdown

HEADING_FA = {
    "git history": "تاریخچه Git",
    "main code/doc changes": "تغییرات اصلی",
    "operational notes": "یادداشت‌های عملیاتی",
    "recommended next steps": "گام‌های بعدی",
    "linked docs": "پیوست‌های مرتبط",
    "linked docs (activity log)": "پیوست‌ها",
    "linked docs (project activity log)": "پیوست‌ها",
    "prompt buckets": "پرامپت‌ها",
    "source": "منبع",
    "follow-up for exact cost": "محاسبه دقیق",
    "top models per prompt window": "مدل‌ها",
    "cursor / cost": "هزینه Cursor",
    "cursor / usage cost": "هزینه Cursor",
    "debops health dashboard": "داشبورد سلامت",
    "debops activity log (transcript 1241c6d0)": "گزارش فعالیت",
    "debops project activity log closeout": "فعالیت پروژه",
    "کارهای انجام‌شده": "کارهای انجام‌شده",
    "سلامت استیج (پایان روز)": "سلامت استیج",
    "فردا / follow-up": "پیگیری",
    "پرامپت‌های کلیدی": "پرامپت‌های کلیدی",
}

LABEL_FA = {
    "**repo:**": "**مخزن:**",
    "**focus:**": "**موضوع:**",
    "**workspace:**": "**محیط کار:**",
    "**transcript:**": "**شناسه چت:**",
    "**period:**": "**بازه:**",
    "**محیط:**": "**محیط:**",
    "**تمرکز:**": "**تمرکز:**",
}

TABLE_HEADER_FA = {
    "date": "تاریخ",
    "commit": "کامیت",
    "message": "پیام",
    "path": "مسیر",
    "summary": "خلاصه",
    "note": "یادداشت",
    "prompt topic": "موضوع",
    "coverage": "وضعیت",
    "notes": "یادداشت",
    "events": "رویداد",
    "total tokens": "توکن",
    "cost status": "هزینه",
    "cost": "هزینه",
    "included": "Included",
    "free": "Free",
    "timing": "زمان",
    "topic": "موضوع",
    "سرویس": "سرویس",
    "url": "URL",
    "http": "HTTP",
    "فایل/بخش": "بخش",
    "تغییر": "تغییر",
    "عملیات runtime": "عملیات",
}

SECTION_DROP = frozenset(
    {
        "follow-up for exact cost",
        "top models per prompt window",
        "محاسبه دقیق",
        "مدل‌ها",
    }
)

DROP_BULLET = re.compile(
    r"^-\s+(?:"
    r"Wrote llfs|"
    r"Documented limitation|"
    r"Exact usage cost remains|"
    r"Push to debops|"
    r"Created focused commit"
    r").+$",
    re.MULTILINE | re.IGNORECASE,
)

MOJIBAKE = str.maketrans(
    {
        "\ufffd": "—",
        "\x97": "—",
        "\x96": "–",
        "�": "—",
    }
)

LINK_PATH = re.compile(
    r"^(?:-\s*)?`((?:\.\./)*(?:chats|cursor|reports|docs)/[^`]+\.(?:md|csv|sh))`(?:\s*[—–-]\s*(.+))?$",
    re.MULTILINE,
)
MARKDOWN_LINK = re.compile(
    r"\[([^\]]+)\]\(((?:\.\./)*(?:chats|cursor|reports|docs)/[^)]+\.(?:md|csv))\)"
)
HEADING = re.compile(r"^(#{1,3})\s+(.+)$", re.MULTILINE)
CODE_BLOCK = re.compile(r"```[\s\S]*?```", re.MULTILINE)
DIV_RTL = re.compile(
    r'<div\s+dir="rtl"[^>]*>\s*|\s*</div>\s*$',
    re.IGNORECASE | re.MULTILINE,
)
TABLE_ROW = re.compile(r"^\|(.+)\|\s*$", re.MULTILINE)
PHRASE_FA = (
    (re.compile(r"\bClosed out\b", re.I), "بستن چت"),
    (re.compile(r"\bCreated focused commit\b", re.I), "کامیت"),
    (re.compile(r"\bwas rejected\b", re.I), "رد شد"),
    (re.compile(r"\bwas not executed\b", re.I), "انجام نشد"),
    (re.compile(r"\bRecommended next steps\b", re.I), "گام‌های بعدی"),
    (re.compile(r"\bOperational notes\b", re.I), "یادداشت‌های عملیاتی"),
    (re.compile(r"\bnot_measured\b"), "اندازه‌گیری نشده"),
    (re.compile(r"\bIncluded/Free only\b", re.I), "Included"),
    (re.compile(r"\boutside_csv_range\b"), "خارج از CSV"),
    (re.compile(r"\bcovered\b"), "پوشش دارد"),
    (re.compile(r"\bestimated_daily_split\b"), "برآورد روزانه"),
    (re.compile(r"\bbest effort\b", re.I), "برآورد"),
)
GARBLED_LINE = re.compile(r"^\s*\|?\s*(\?+\s*)+\|?")


def is_garbled_line(line: str) -> bool:
    stripped = line.strip()
    if len(stripped) < 3:
        return False
    if GARBLED_LINE.match(stripped):
        return True
    q = stripped.count("?")
    if q >= 4 and q / max(len(stripped), 1) > 0.12:
        return True
    return False


def sanitize_garbled_text(text: str) -> str:
    out: list[str] = []
    for line in text.splitlines():
        if is_garbled_line(line):
            continue
        line = line.replace("�", "—")
        line = re.sub(r"\s\?\s", " → ", line)
        line = re.sub(r"ext\?", "ext→", line)
        line = re.sub(r"MikroTik \?", "MikroTik →", line)
        line = re.sub(r"group \? root", "group ≠ root", line, flags=re.I)
        out.append(line)
    return "\n".join(out)


def fix_text(text: str) -> str:
    text = text.translate(MOJIBAKE)
    text = sanitize_garbled_text(text)
    for pattern, repl in PHRASE_FA:
        text = pattern.sub(repl, text)
    return text


def link_label(path_str: str) -> str:
    name = Path(path_str.replace("\\", "/")).name.lower()
    if "closeout" in name:
        return "خلاصه چت"
    if "prompts" in name:
        return "پرامپت‌ها"
    if "cost" in name or "usage" in name:
        return "هزینه Cursor"
    if "todo" in name or "chat-open" in name:
        return "کارهای باز"
    if "daily" in name:
        return "گزارش Git"
    if "summary" in name:
        return "خلاصه مصرف"
    if "activity-log" in name:
        return "گزارش فعالیت"
    if name.endswith(".csv"):
        return "CSV مصرف"
    return Path(name).stem.replace("-", " ")[:36] or "پیوست"


def short_path(path_str: str, max_len: int = 52) -> str:
    clean = path_str.replace("\\", "/")
    if len(clean) <= max_len:
        return clean
    parent = str(Path(clean).parent)
    name = Path(clean).name
    if len(name) >= max_len - 3:
        return "…/" + name[-(max_len - 2) :]
    budget = max_len - len(name) - 1
    if len(parent) > budget:
        parent = "…/" + parent[-budget:]
    return f"{parent}/{name}"


def persian_title_from_path(path: Path) -> str:
    stem = path.stem
    for suffix in ("-daily-git-report", "-git-daily", "-daily"):
        if stem.endswith(suffix):
            stem = stem[: -len(suffix)]
            break
    stem = re.sub(
        r"^\d{4}-\d{2}-\d{2}(?:[-_]\d{4}-\d{2}-\d{2}|-\d{2})?(?:[-_])?",
        "",
        stem,
    )
    topic = stem.replace("-", " ").strip() or "گزارش"
    topic = TOPIC_FA.get(topic.lower(), topic)
    date_match = re.search(r"(\d{4}-\d{2}-\d{2})", path.name)
    date = date_match.group(1) if date_match else ""
    if date:
        return f"{topic} · {date}"
    return topic


def translate_headings(text: str) -> str:
    def repl(match: re.Match[str]) -> str:
        level = match.group(1)
        title = match.group(2).strip()
        key = re.split(r"[—–|]", title)[0].strip().lower()
        fa = HEADING_FA.get(key, title)
        return f"{level} {fa}"

    return HEADING.sub(repl, text)


TOPIC_FA = {
    "debops health dashboard": "داشبورد سلامت debops",
    "debops infra audit": "ممیزی infra",
    "debops gitlab zabbix": "GitLab و Zabbix",
    "nexus openvpn": "Nexus و OpenVPN",
    "gps activity": "GPS / activity",
    "stage deployer": "stage-deployer",
    "debops project activity log": "گزارش فعالیت پروژه",
    "debops activity log": "گزارش فعالیت",
}


def translate_labels(text: str) -> str:
    out = text
    for en, fa in LABEL_FA.items():
        out = re.sub(re.escape(en), fa, out, flags=re.IGNORECASE)
    return out


def translate_table_headers(text: str) -> str:
    lines = text.splitlines()
    out: list[str] = []
    for i, line in enumerate(lines):
        row_match = TABLE_ROW.match(line)
        if row_match and i + 1 < len(lines) and re.match(
            r"^\|\s*[-:| ]+\|\s*$", lines[i + 1]
        ):
            cells = [cell.strip() for cell in row_match.group(1).split("|")]
            translated = []
            for cell in cells:
                key = cell.lower().strip("`")
                translated.append(TABLE_HEADER_FA.get(key, cell))
            out.append("| " + " | ".join(translated) + " |")
        else:
            out.append(line)
    return "\n".join(out)


def drop_sections(text: str) -> str:
    lines = text.splitlines()
    out: list[str] = []
    skip = False
    skip_level = 0

    for line in lines:
        heading = HEADING.match(line)
        if heading:
            title = heading.group(2).strip().lower()
            if any(title.startswith(prefix) or prefix in title for prefix in SECTION_DROP):
                skip = True
                skip_level = len(heading.group(1))
                continue
            if skip and len(heading.group(1)) <= skip_level:
                skip = False

        if skip:
            continue
        out.append(line)

    return "\n".join(out)


def drop_noise_bullets(text: str) -> str:
    return DROP_BULLET.sub("", text)


def collapse_source_section(text: str, *, best_effort: bool) -> str:
    if not best_effort:
        return text
    return re.sub(
        r"## Source\s*\n+(?:- .+\n+)+",
        "> **وضعیت:** برآورد تقریبی — CSV تازه import نشده.\n\n",
        text,
        count=1,
        flags=re.IGNORECASE,
    )


def compact_linked_docs(text: str) -> tuple[str, list[tuple[str, str]]]:
    refs: list[tuple[str, str]] = []
    seen: set[str] = set()

    def add(path: str, title: str | None = None) -> None:
        clean = path.replace("\\", "/")
        while clean.startswith("../"):
            clean = clean[3:]
        if clean in seen:
            return
        seen.add(clean)
        refs.append((title or link_label(clean), clean))

    for match in LINK_PATH.finditer(text):
        add(match.group(1), match.group(2))

    for match in MARKDOWN_LINK.finditer(text):
        add(match.group(2), match.group(1))

    text = LINK_PATH.sub("", text)
    text = MARKDOWN_LINK.sub("", text)
    text = re.sub(r"## Linked docs[^\n]*\n(?:\s*\n)?", "", text, flags=re.IGNORECASE)
    text = re.sub(r"^---\s*\n", "", text, count=1, flags=re.MULTILINE)
    return text.strip(), refs


def soften_code_blocks(text: str, *, max_lines: int = 5) -> str:
    def repl(match: re.Match[str]) -> str:
        block = match.group(0)
        lines = block.splitlines()
        if len(lines) <= max_lines + 2:
            inner = lines[1:-1] if len(lines) > 2 else []
            if inner:
                cmd = inner[0].strip()
                if len(cmd) > 72:
                    cmd = cmd[:69] + "…"
                return f"\n> **دستور:** `{cmd}`\n"
            return ""
        first = lines[1].strip() if len(lines) > 1 else "…"
        if len(first) > 72:
            first = first[:69] + "…"
        return f"\n> **دستور:** `{first}` — (جزئیات در مخزن)\n"

    return CODE_BLOCK.sub(repl, text)


def strip_leading_h1(text: str) -> str:
    return re.sub(r"^#\s+.+\n+", "", text, count=1, flags=re.MULTILINE)


def trim_table_rows(text: str, *, max_rows: int = 10) -> str:
    lines = text.splitlines()
    out: list[str] = []
    i = 0
    while i < len(lines):
        line = lines[i]
        if TABLE_ROW.match(line) and i + 1 < len(lines) and re.match(r"^\|\s*[-:| ]+\|\s*$", lines[i + 1]):
            header = line
            sep = lines[i + 1]
            rows: list[str] = []
            j = i + 2
            while j < len(lines) and TABLE_ROW.match(lines[j]):
                rows.append(lines[j])
                j += 1
            if len(rows) > max_rows:
                kept = rows[:max_rows]
                note = f"\n> *{len(rows) - max_rows} ردیف دیگر در فایل markdown مخزن.*\n"
                out.extend([header, sep, *kept, note.strip()])
            else:
                out.extend([header, sep, *rows])
            i = j
            continue
        out.append(line)
        i += 1
    return "\n".join(out)


def simplify_cost_table(text: str) -> str:
    lines = text.splitlines()
    out: list[str] = []
    i = 0
    while i < len(lines):
        line = lines[i]
        if TABLE_ROW.match(line) and "prompt topic" in line.lower():
            header_cells = [c.strip().lower() for c in line.split("|")[1:-1]]
            keep_idx = [
                idx
                for idx, cell in enumerate(header_cells)
                if cell not in {"notes", "included", "free", "cost status"}
            ]
            if not keep_idx:
                out.append(line)
                i += 1
                continue
            sep = lines[i + 1] if i + 1 < len(lines) else ""
            i += 2
            new_header = [header_cells[j] for j in keep_idx]
            out.append("| " + " | ".join(TABLE_HEADER_FA.get(c, c) for c in new_header) + " |")
            out.append("| " + " | ".join("---" for _ in keep_idx) + " |")
            while i < len(lines) and TABLE_ROW.match(lines[i]):
                cells = [c.strip() for c in lines[i].split("|")[1:-1]]
                picked = [cells[j] if j < len(cells) else "" for j in keep_idx]
                out.append("| " + " | ".join(picked) + " |")
                i += 1
            continue
        out.append(line)
        i += 1
    return "\n".join(out)


def appendix_html(refs: list[tuple[str, str]]) -> str:
    if not refs:
        return ""
    items = []
    for title, path in refs:
        items.append(
            "<li>"
            f'<span class="ref-title">{html.escape(title)}</span>'
            f'<span class="ref-path">{html.escape(short_path(path))}</span>'
            "</li>"
        )
    return (
        '<section class="appendix">'
        "<h2>پیوست‌های مرتبط</h2>"
        '<p class="appendix-note">مسیر فایل در مخزن llfs — برای باز کردن در Cursor/IDE.</p>'
        f'<ul class="ref-list">{"".join(items)}</ul>'
        "</section>"
    )


def prepare_daily_markdown(text: str) -> tuple[str, list[tuple[str, str]]]:
    text = fix_text(text)
    text = DIV_RTL.sub("", text)
    text = strip_leading_h1(text)
    text = translate_labels(text)
    text, refs = compact_linked_docs(text)
    text = drop_noise_bullets(text)
    text = soften_code_blocks(text)
    text = drop_sections(text)
    text = trim_table_rows(text, max_rows=8)
    text = translate_table_headers(text)
    text = translate_headings(text)
    return text.strip(), refs


def prepare_cost_markdown(text: str) -> str:
    text = fix_text(text)
    text = DIV_RTL.sub("", text)
    best_effort = "not_measured" in text.lower() or "best effort" in text.lower()
    text = collapse_source_section(text, best_effort=best_effort)
    text = drop_sections(text)
    text = simplify_cost_table(text)
    text = soften_code_blocks(text, max_lines=3)
    text = trim_table_rows(text, max_rows=6)
    text = translate_table_headers(text)
    text = translate_headings(text)
    text = re.sub(
        r"^# .+$",
        "# هزینه Cursor" + (" (برآورد)" if best_effort else ""),
        text,
        count=1,
        flags=re.MULTILINE,
    )
    return text.strip()


def md_to_html(text: str) -> str:
    return markdown.markdown(
        text,
        extensions=["extra", "tables", "fenced_code", "sane_lists"],
        output_format="html5",
    )


def postprocess_content_html(content_html: str) -> str:
    content_html = re.sub(
        r"<table>",
        '<table class="data-table">',
        content_html,
    )
    content_html = re.sub(
        r"<th>([^<]+)</th>",
        lambda m: f"<th>{html.escape(TABLE_HEADER_FA.get(m.group(1).strip().lower(), m.group(1)))}</th>",
        content_html,
    )
    return content_html


HTML_TEMPLATE = """<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8" />
  <title>{title}</title>
  <style>
    @page {{
      size: A4;
      margin: 12mm 11mm 14mm;
    }}
    * {{ box-sizing: border-box; }}
    body {{
      margin: 0;
      font-family: "Vazirmatn", "Noto Sans Arabic", "DejaVu Sans", "Segoe UI", Tahoma, sans-serif;
      font-size: 10pt;
      line-height: 1.7;
      color: #1e293b;
      background: #eef2f7;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }}
    .sheet {{
      background: #fff;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 10px 40px rgba(15, 23, 42, 0.08);
    }}
    .hero {{
      background: linear-gradient(120deg, #0d9488 0%, #0f766e 55%, #115e59 100%);
      color: #fff;
      padding: 22px 24px 20px;
    }}
    .hero-badge {{
      display: inline-block;
      font-size: 8.5pt;
      font-weight: 600;
      letter-spacing: 0.04em;
      background: rgba(255,255,255,0.16);
      border: 1px solid rgba(255,255,255,0.22);
      border-radius: 999px;
      padding: 0.25em 0.75em;
      margin-bottom: 10px;
    }}
    .hero h1 {{
      margin: 0;
      font-size: 16pt;
      font-weight: 700;
      line-height: 1.45;
    }}
    .hero-sub {{
      margin-top: 8px;
      font-size: 8.5pt;
      opacity: 0.88;
      direction: ltr;
      text-align: left;
      word-break: break-all;
    }}
    .body {{
      padding: 20px 22px 18px;
    }}
    .content h2 {{
      font-size: 12pt;
      font-weight: 700;
      color: #0f766e;
      margin: 1.4em 0 0.55em;
      padding-bottom: 0.3em;
      border-bottom: 2px solid #ccfbf1;
      page-break-after: avoid;
    }}
    .content h2:first-child {{ margin-top: 0; }}
    .content h3 {{
      font-size: 10.5pt;
      color: #334155;
      margin: 1em 0 0.35em;
      page-break-after: avoid;
    }}
    .content p, .content li {{ color: #334155; }}
    .content ul {{ padding-right: 1.1em; margin: 0.35em 0 0.75em; }}
    .content blockquote {{
      margin: 0.65em 0;
      padding: 0.55em 0.85em;
      border-right: 4px solid #f59e0b;
      background: #fffbeb;
      color: #92400e;
      border-radius: 0 10px 10px 0;
      font-size: 9.5pt;
    }}
    .appendix {{
      margin-top: 1.2em;
      padding-top: 0.8em;
      border-top: 1px dashed #cbd5e1;
      page-break-inside: avoid;
    }}
    .appendix-note {{
      font-size: 8.5pt;
      color: #64748b;
      margin: 0 0 0.5em;
    }}
    .ref-list {{
      list-style: none;
      margin: 0;
      padding: 0;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 8px;
    }}
    .ref-list li {{
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      padding: 8px 10px;
      page-break-inside: avoid;
    }}
    .ref-title {{
      display: block;
      font-weight: 600;
      font-size: 9.5pt;
      color: #0f766e;
      margin-bottom: 3px;
    }}
    .ref-path {{
      display: block;
      font-size: 7.5pt;
      color: #64748b;
      direction: ltr;
      text-align: left;
      word-break: break-all;
      font-family: "DejaVu Sans Mono", monospace;
    }}
    .cost-card {{
      margin-top: 16px;
      border: 1px solid #bfdbfe;
      border-radius: 14px;
      overflow: hidden;
      page-break-inside: avoid;
    }}
    .cost-card-head {{
      background: linear-gradient(90deg, #eff6ff, #f0f9ff);
      color: #1e40af;
      font-weight: 700;
      padding: 10px 14px;
      font-size: 10.5pt;
      border-bottom: 1px solid #bfdbfe;
    }}
    .cost-card-body {{
      padding: 10px 14px 12px;
      font-size: 9.5pt;
    }}
    .cost-missing {{
      margin-top: 14px;
      padding: 10px 12px;
      background: #fffbeb;
      border: 1px solid #fde68a;
      border-radius: 12px;
      color: #92400e;
      font-size: 9.5pt;
    }}
    code {{
      font-family: "DejaVu Sans Mono", "Consolas", monospace;
      font-size: 8.5pt;
      background: #f1f5f9;
      padding: 0.1em 0.35em;
      border-radius: 4px;
      direction: ltr;
      unicode-bidi: embed;
      word-break: break-all;
    }}
    pre {{
      background: #0f172a;
      color: #e2e8f0;
      border-radius: 8px;
      padding: 8px 10px;
      direction: ltr;
      text-align: left;
      font-size: 8pt;
      white-space: pre-wrap;
    }}
    .data-table {{
      width: 100%;
      border-collapse: collapse;
      margin: 0.5em 0 0.85em;
      font-size: 8.8pt;
      table-layout: fixed;
      page-break-inside: avoid;
    }}
    .data-table th, .data-table td {{
      border: 1px solid #e2e8f0;
      padding: 0.4em 0.45em;
      vertical-align: top;
      word-wrap: break-word;
      overflow-wrap: anywhere;
    }}
    .data-table th {{
      background: #f8fafc;
      color: #0f172a;
      font-weight: 700;
    }}
    .data-table tr:nth-child(even) td {{ background: #fcfdff; }}
    .data-table td:first-child {{ width: 22%; }}
    .footer {{
      padding: 10px 22px 14px;
      font-size: 8pt;
      color: #94a3b8;
      text-align: center;
      border-top: 1px solid #f1f5f9;
    }}
    hr {{ display: none; }}
  </style>
</head>
<body>
  <div class="sheet">
    <header class="hero">
      <div class="hero-badge">گزارش روزانه · llfs</div>
      <h1>{title}</h1>
      <div class="hero-sub">{source_path}</div>
    </header>
    <div class="body">
      <main class="content">{body}</main>
    </div>
    <div class="footer">خروجی خودکار — پیوست‌ها مسیر فایل در مخزن هستند.</div>
  </div>
</body>
</html>
"""


def build_html_document(
    *,
    title: str,
    source_path: str,
    daily_html: str,
    appendix_refs: list[tuple[str, str]],
    cost_blocks: list[tuple[str, str]],
    missing_cost: bool,
) -> str:
    parts = [postprocess_content_html(daily_html)]
    parts.append(appendix_html(appendix_refs))

    if cost_blocks:
        for label, cost_html in cost_blocks:
            parts.append(
                f'<section class="cost-card">'
                f'<div class="cost-card-head">{html.escape(label)}</div>'
                f'<div class="cost-card-body">{postprocess_content_html(cost_html)}</div>'
                f"</section>"
            )
    elif missing_cost:
        parts.append(
            '<div class="cost-missing">'
            "<strong>هزینه Cursor:</strong> فایل cost مرتبط پیدا نشد."
            "</div>"
        )

    body = "\n".join(part for part in parts if part)
    return HTML_TEMPLATE.format(
        title=html.escape(title),
        source_path=html.escape(short_path(source_path, 64)),
        body=body,
    )


def prepare_executive_markdown(text: str) -> str:
    text = fix_text(text)
    text = DIV_RTL.sub("", text)
    text = strip_leading_h1(text)
    text = translate_table_headers(text)
    text = trim_table_rows(text, max_rows=12)
    return text.strip()


def build_executive_html(*, title: str, source_path: str, markdown_text: str) -> str:
    body = postprocess_content_html(md_to_html(prepare_executive_markdown(markdown_text)))
    template = HTML_TEMPLATE.replace(
        "گزارش روزانه · llfs",
        "گزارش Cursor · مدیریت",
    ).replace(
        "خروجی خودکار — پیوست‌ها مسیر فایل در مخزن هستند.",
        "خلاصه مصرف Cursor برای گزارش به مدیریت — هزینه نقدی $0 (Included).",
    )
    return template.format(
        title=html.escape(title),
        source_path=html.escape(short_path(source_path, 64)),
        body=body,
    )
