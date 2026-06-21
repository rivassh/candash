"""Calculate Cursor usage from CSV + prompt timelines (approximate daily split)."""

from __future__ import annotations

import csv
import re
from collections import defaultdict
from dataclasses import dataclass
from datetime import date, datetime
from pathlib import Path


DATE_HEADING = re.compile(r"^###\s+(\d{4}-\d{2}-\d{2})", re.MULTILINE)
DATE_RANGE_HEADING = re.compile(
    r"^###\s+(\d{4}-\d{2}-\d{2})\s*[/–-]\s*(\d{4}-\d{2}-\d{2})",
    re.MULTILINE,
)
DATE_PLUS_HEADING = re.compile(r"^###\s+(\d{4}-\d{2}-\d{2})\+", re.MULTILINE)
TABLE_DATE = re.compile(r"^\|\s*(\d{4}-\d{2}-\d{2})", re.MULTILINE)
BULLET = re.compile(r"^-\s+.+", re.MULTILINE)


@dataclass
class PromptBucket:
    date: str
    topic: str
    events: int = 0
    total_tokens: int = 0
    included: int = 0
    free: int = 0
    coverage: str = "estimated_daily_split"
    cost_status: str = "Included/Free only"


@dataclass
class DayUsage:
    events: int = 0
    total_tokens: int = 0
    included: int = 0
    free: int = 0


def parse_int(value: str) -> int:
    clean = (value or "0").replace(",", "").strip()
    try:
        return int(float(clean))
    except ValueError:
        return 0


def load_csv_usage(csv_path: Path) -> dict[str, DayUsage]:
    by_day: dict[str, DayUsage] = defaultdict(DayUsage)
    with csv_path.open(newline="", encoding="utf-8") as handle:
        reader = csv.DictReader(handle)
        for row in reader:
            day = row["Date"][:10]
            usage = by_day[day]
            usage.events += 1
            usage.total_tokens += parse_int(row.get("Total Tokens", "0"))
            if (row.get("Cost") or "").strip() == "Free":
                usage.free += 1
            else:
                usage.included += 1
    return dict(by_day)


def pick_csv_for_range(root: Path, start: str, end: str) -> Path | None:
    exact = root / "cursor" / "usage" / f"{start}_{end}.csv"
    if exact.is_file():
        return exact

    generic: list[Path] = []
    for path in sorted(root.glob("cursor/usage/*.csv")):
        name = path.name
        if not re.match(r"^\d{4}-\d{2}-\d{2}_\d{4}-\d{2}-\d{2}\.csv$", name):
            continue
        csv_start, csv_end = path.stem.split("_", 1)
        if csv_start <= start and csv_end >= end:
            generic.append(path)
    if generic:
        return generic[0]

    fallback = root / "cursor" / "usage" / "2026-06-14_2026-06-20.csv"
    return fallback if fallback.is_file() else None


def _section_blocks(text: str) -> list[tuple[str, str]]:
    lines = text.splitlines()
    blocks: list[tuple[str, list[str]]] = []
    current_date = ""
    current_lines: list[str] = []

    for line in lines:
        m = DATE_HEADING.match(line) or DATE_RANGE_HEADING.match(line) or DATE_PLUS_HEADING.match(line)
        if m:
            if current_date and current_lines:
                blocks.append((current_date, current_lines))
            if DATE_RANGE_HEADING.match(line):
                current_date = m.group(1)
            elif DATE_PLUS_HEADING.match(line):
                current_date = m.group(1)
            else:
                current_date = m.group(1)
            current_lines = [line]
            continue
        if current_date:
            current_lines.append(line)

    if current_date and current_lines:
        blocks.append((current_date, current_lines))
    return [(d, "\n".join(body)) for d, body in blocks]


def parse_prompt_buckets(prompts_text: str) -> list[tuple[str, str]]:
    focus = prompts_text
    timeline = re.search(
        r"## Timeline[^\n]*\n([\s\S]*?)(?:\n## |\Z)",
        prompts_text,
        re.IGNORECASE,
    )
    if timeline:
        focus = timeline.group(1)
    elif "## User Prompts Covered" in prompts_text:
        table = re.search(
            r"## User Prompts Covered([\s\S]*?)(?:\n## |\Z)",
            prompts_text,
            re.IGNORECASE,
        )
        if table:
            focus = table.group(1)

    buckets: list[tuple[str, str]] = []

    for day, body in _section_blocks(focus):
        bullets = BULLET.findall(body)
        if bullets:
            for bullet in bullets:
                topic = bullet[2:].strip()
                topic = re.sub(r"\s+", " ", topic)
                if len(topic) > 120:
                    topic = topic[:117] + "…"
                buckets.append((day, topic))
            continue

        rows = []
        for line in body.splitlines():
            if line.startswith("|") and not line.startswith("|--") and "Date" not in line:
                cells = [c.strip() for c in line.strip("|").split("|")]
                if cells and re.match(r"\d{4}-\d{2}-\d{2}", cells[0]):
                    rows.append((cells[0][:10], cells[1] if len(cells) > 1 else "prompt"))
        for row_date, topic in rows:
            buckets.append((row_date, topic[:120]))

    if not buckets:
        for match in TABLE_DATE.finditer(prompts_text):
            buckets.append((match.group(1), "prompt"))

    return buckets


def find_prompts_file(cost_path: Path, root: Path) -> Path | None:
    stem = cost_path.stem
    stem = re.sub(r"-\d{4}-\d{2}-\d{2}(?:_\d{4}-\d{2}-\d{2})?-cost.*$", "", stem)
    stem = re.sub(r"-cost.*$", "", stem)
    patterns = [
        f"cursor/chats/*{stem}*prompts*.md",
        f"cursor/chats/*{stem.replace('debops-', 'debops-')}*prompts*.md",
    ]
    if "health-dashboard" in stem:
        patterns.insert(0, "cursor/chats/*health-dashboard*prompts*.md")
    if "activity-log" in stem and "project" not in stem:
        patterns.insert(0, "cursor/chats/*activity-log*prompts*.md")
    if "project-activity-log" in stem:
        patterns.insert(0, "cursor/chats/*project-activity-log*prompts*.md")

    seen: set[Path] = set()
    for pattern in patterns:
        for path in sorted(root.glob(pattern)):
            if path.resolve() not in seen:
                seen.add(path.resolve())
                return path
    return None


def estimate_buckets(
    buckets: list[tuple[str, str]],
    usage_by_day: dict[str, DayUsage],
) -> list[PromptBucket]:
    per_day: dict[str, list[tuple[str, str]]] = defaultdict(list)
    for day, topic in buckets:
        per_day[day].append((day, topic))

    results: list[PromptBucket] = []
    for day, items in sorted(per_day.items()):
        day_usage = usage_by_day.get(day)
        if not day_usage or not items:
            for _, topic in items:
                results.append(
                    PromptBucket(
                        date=day,
                        topic=topic,
                        coverage="outside_csv_range",
                        cost_status="No CSV row",
                    )
                )
            continue

        share = max(len(items), 1)
        ev_share = max(day_usage.events // share, 0)
        tok_share = day_usage.total_tokens // share
        inc_share = max(day_usage.included // share, 0)
        free_share = max(day_usage.free // share, 0)

        for _, topic in items:
            results.append(
                PromptBucket(
                    date=day,
                    topic=topic,
                    events=ev_share,
                    total_tokens=tok_share,
                    included=inc_share,
                    free=free_share,
                    coverage="estimated_daily_split",
                    cost_status="Included/Free only",
                )
            )
    return results


def format_tokens(value: int) -> str:
    return f"{value:,}"


def render_cost_markdown(
    *,
    title: str,
    csv_path: Path | None,
    method: str,
    buckets: list[PromptBucket],
    notes: list[str],
) -> str:
    total_tokens = sum(b.total_tokens for b in buckets)
    total_events = sum(b.events for b in buckets)
    lines = [
        f"# {title}",
        "",
        '<div dir="rtl" style="text-align: right;">',
        "",
        "## منبع",
        "",
        f"- CSV: `{csv_path.as_posix() if csv_path else 'n/a'}`",
        f"- روش: {method}",
        f"- **جمع توکن (برآورد):** {format_tokens(total_tokens)}",
        f"- **جمع رویداد:** {format_tokens(total_events)}",
        "- **هزینه نقدی در CSV:** $0.00 (Included/Free)",
        "",
        "> محدودیت: CSV شناسه conversation ندارد؛ اعداد با **تقسیم مصرف روزانه** بین پرامپت‌های همان چت برآورد شده‌اند.",
        "",
        "## پرامپت‌ها و مصرف",
        "",
        "| تاریخ | موضوع | پوشش | رویداد | توکن | هزینه |",
        "|---|---|---|---:|---:|---|",
    ]

    for bucket in buckets:
        topic = bucket.topic.replace("|", "/")
        lines.append(
            f"| {bucket.date} | {topic} | {bucket.coverage} | "
            f"{bucket.events} | {format_tokens(bucket.total_tokens)} | {bucket.cost_status} |"
        )

    if notes:
        lines.extend(["", "## یادداشت", ""])
        lines.extend(f"- {note}" for note in notes)

    lines.extend(["", "</div>", ""])
    return "\n".join(lines)


def calculate_cost_file(cost_path: Path, root: Path, *, force: bool = False) -> bool:
    text = cost_path.read_text(encoding="utf-8", errors="replace")
    if not force:
        if "not_measured" not in text.lower() and "best effort" not in text.lower():
            return False
        if re.search(r"\|\s*covered\s*\|", text, re.I) and "not_measured" not in text.lower():
            return False
        if re.search(r"estimated_daily_split", text):
            return False

    prompts_path = find_prompts_file(cost_path, root)
    if not prompts_path:
        return False

    prompts_text = prompts_path.read_text(encoding="utf-8", errors="replace")
    buckets_raw = parse_prompt_buckets(prompts_text)
    if not buckets_raw:
        return False

    dates = [b[0] for b in buckets_raw]
    csv_path = pick_csv_for_range(root, min(dates), max(dates))
    if not csv_path:
        return False

    usage = load_csv_usage(csv_path)
    buckets = estimate_buckets(buckets_raw, usage)

    title = cost_path.stem.replace("-", " ").replace("_", " ")
    notes = [
        f"فایل پرامپت: `{prompts_path.relative_to(root).as_posix()}`",
        "برای دقت بیشتر CSV تازه‌تر import کنید.",
    ]
    outside = [b.date for b in buckets if b.coverage == "outside_csv_range"]
    if outside:
        notes.append(f"تاریخ‌های خارج از CSV: {', '.join(sorted(set(outside)))}")

    rendered = render_cost_markdown(
        title=f"هزینه Cursor — {title}",
        csv_path=csv_path.relative_to(root),
        method="تقسیم مصرف روزانه اکانت بین پرامپت‌های این چت",
        buckets=buckets,
        notes=notes,
    )
    cost_path.write_text(rendered, encoding="utf-8")
    return True


def iter_cost_files(root: Path) -> list[Path]:
    files = sorted(root.glob("cursor/usage/*cost*.md"))
    return [p for p in files if "summary" not in p.name]


def calculate_all(root: Path, *, force: bool = False) -> list[Path]:
    updated: list[Path] = []
    for path in iter_cost_files(root):
        if calculate_cost_file(path, root, force=force):
            updated.append(path)
    return updated
