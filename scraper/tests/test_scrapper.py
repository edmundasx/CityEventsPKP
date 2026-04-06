from __future__ import annotations

import csv
import json
import sqlite3
import sys
from pathlib import Path

import pytest

sys.path.append(str(Path(__file__).resolve().parents[1]))

from event_scrapper import VilniusEventsFullScraper


@pytest.fixture
def sample_events() -> list[dict]:
    return [
        {
            "id": 1,
            "pavadinimas": "Koncertas Vingio parke",
            "data_laikas": "2026-04-10 19:00",
            "vieta": "Vingio parkas",
            "kategorija": "Muzika",
            "nuoroda": "https://www.vilnius-events.lt/renginys-1",
            "paveiksliukas": "https://www.vilnius-events.lt/img-1.jpg",
        },
        {
            "id": 2,
            "pavadinimas": "Teatro vakaras",
            "data_laikas": "2026-04-11 18:30",
            "vieta": "Nacionalinis dramos teatras",
            "kategorija": "Teatras",
            "nuoroda": "https://www.vilnius-events.lt/renginys-2",
            "paveiksliukas": "https://www.vilnius-events.lt/img-2.jpg",
        },
    ]


@pytest.fixture
def scraper_without_browser() -> VilniusEventsFullScraper:
    """
    Apeiname __init__, nes jis paleidžia Chrome webdriver.
    Šitiems testams reikia tik save_results().
    """
    return VilniusEventsFullScraper.__new__(VilniusEventsFullScraper)


def test_save_results_creates_csv_json_and_db(
    tmp_path: Path,
    monkeypatch: pytest.MonkeyPatch,
    scraper_without_browser: VilniusEventsFullScraper,
    sample_events: list[dict],
) -> None:
    monkeypatch.chdir(tmp_path)

    base_filename = "test_events"
    scraper_without_browser.save_results(sample_events, base_filename)

    csv_file = tmp_path / f"{base_filename}.csv"
    json_file = tmp_path / f"{base_filename}.json"
    db_file = tmp_path / f"{base_filename}.db"

    assert csv_file.exists(), "CSV failas nebuvo sukurtas"
    assert json_file.exists(), "JSON failas nebuvo sukurtas"
    assert db_file.exists(), "SQLite DB failas nebuvo sukurtas"

    with csv_file.open("r", encoding="utf-8-sig", newline="") as f:
        reader = csv.DictReader(f)
        rows = list(reader)

    assert len(rows) == 2, "CSV faile turi būti 2 įrašai"
    assert reader.fieldnames == [
        "id",
        "pavadinimas",
        "data_laikas",
        "vieta",
        "kategorija",
        "nuoroda",
        "paveiksliukas",
    ]


def test_saved_json_and_sqlite_have_expected_data(
    tmp_path: Path,
    monkeypatch: pytest.MonkeyPatch,
    scraper_without_browser: VilniusEventsFullScraper,
    sample_events: list[dict],
) -> None:
    monkeypatch.chdir(tmp_path)

    base_filename = "test_events"
    scraper_without_browser.save_results(sample_events, base_filename)

    json_file = tmp_path / f"{base_filename}.json"
    db_file = tmp_path / f"{base_filename}.db"

    with json_file.open("r", encoding="utf-8") as f:
        json_data = json.load(f)

    assert len(json_data) == 2, "JSON faile turi būti 2 įrašai"
    assert json_data[0]["pavadinimas"] == sample_events[0]["pavadinimas"]
    assert json_data[1]["vieta"] == sample_events[1]["vieta"]
    assert set(json_data[0].keys()) == {
        "id",
        "pavadinimas",
        "data_laikas",
        "vieta",
        "kategorija",
        "nuoroda",
        "paveiksliukas",
    }

    conn = sqlite3.connect(db_file)
    try:
        rows = conn.execute(
            """
            SELECT id, pavadinimas, data_laikas, vieta, kategorija, nuoroda, paveiksliukas
            FROM renginiai
            ORDER BY id
            """
        ).fetchall()
    finally:
        conn.close()

    assert len(rows) == 2, "SQLite DB turi būti 2 įrašai"
    assert rows[0][0] == sample_events[0]["id"]
    assert rows[0][1] == sample_events[0]["pavadinimas"]
    assert rows[1][3] == sample_events[1]["vieta"]
    assert rows[1][4] == sample_events[1]["kategorija"]