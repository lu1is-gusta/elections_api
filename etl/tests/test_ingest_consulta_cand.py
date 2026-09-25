import zipfile
from pathlib import Path

import pytest

from elections_etl.db import connect
from elections_etl.load.runs import LOCK_KEY
from elections_etl.pipeline import ingest_consulta_cand

FIXTURE = Path(__file__).parent / "fixtures" / "consulta_cand_sample.csv"


def _zip(tmp_path: Path) -> Path:
    archive = tmp_path / "consulta_cand_2024.zip"
    with zipfile.ZipFile(archive, "w") as handle:
        handle.write(FIXTURE, "consulta_cand_2024_SP.csv")
    return archive


def _counts(connection) -> dict[str, int]:
    tables = (
        "elections",
        "offices",
        "electoral_units",
        "parties",
        "people",
        "person_identifiers",
        "candidacies",
        "candidacy_details",
    )
    counts = {}
    for table in tables:
        counts[table] = connection.execute(f"SELECT count(*) FROM {table}").fetchone()[0]
    return counts


def test_lock_impede_carga_simultanea(clean_db, tmp_path, monkeypatch):
    monkeypatch.setenv("ETL_DATA_DIR", str(tmp_path / "data"))
    holder = connect()
    assert holder.execute("SELECT pg_try_advisory_lock(%s)", (LOCK_KEY,)).fetchone()[0]
    holder.commit()
    try:
        with pytest.raises(RuntimeError, match="em andamento"):
            ingest_consulta_cand(2024, _zip(tmp_path))
    finally:
        holder.execute("SELECT pg_advisory_unlock(%s)", (LOCK_KEY,))
        holder.commit()
        holder.close()

    status, error = clean_db.execute(
        "SELECT status, error FROM ingestion_runs ORDER BY id DESC LIMIT 1"
    ).fetchone()
    assert status == "failed"
    assert "andamento" in error
    assert clean_db.execute("SELECT count(*) FROM candidacies").fetchone()[0] == 0


def test_consulta_cand_upsert_idempotente(clean_db, tmp_path, monkeypatch):
    monkeypatch.setenv("ETL_DATA_DIR", str(tmp_path / "data"))
    archive = _zip(tmp_path)

    rows = ingest_consulta_cand(2024, archive)
    assert rows == 30

    connection = clean_db
    assert _counts(connection)["candidacies"] == 30
    assert _counts(connection)["candidacy_details"] == 30
    assert connection.execute("SELECT to_regclass('public.staging_consulta_cand')").fetchone()[0] is None

    people_columns = {
        row[0]
        for row in connection.execute(
            """
            SELECT column_name
            FROM information_schema.columns
            WHERE table_schema = 'public' AND table_name = 'people'
            """
        ).fetchall()
    }
    assert people_columns.isdisjoint({"cpf", "voter_id", "email"})

    career = connection.execute(
        """
        SELECT p.ballot_name, count(c.id)
        FROM people p
        JOIN person_identifiers pi ON pi.person_id = p.id
        JOIN candidacies c ON c.person_id = p.id
        WHERE pi.cpf = '11111111111'
        GROUP BY p.id, p.ballot_name
        """
    ).fetchall()
    assert career == [("MARIA 2024", 2)]

    years = connection.execute(
        """
        SELECT e.year, c.is_reelection
        FROM candidacies c
        JOIN elections e ON e.id = c.election_id
        JOIN person_identifiers pi ON pi.person_id = c.person_id
        WHERE pi.cpf = '11111111111'
        ORDER BY e.year
        """
    ).fetchall()
    assert years == [(2020, False), (2024, True)]

    turns = connection.execute(
        """
        SELECT turn, result_code, result, is_elected
        FROM candidacies
        WHERE tse_sq_candidato = 9001
        ORDER BY turn
        """
    ).fetchall()
    assert turns == [
        (1, 6, "2º TURNO", False),
        (2, 1, "ELEITO", True),
    ]

    elected = connection.execute(
        """
        SELECT result_code, bool_and(is_elected)
        FROM candidacies
        GROUP BY result_code
        ORDER BY result_code
        """
    ).fetchall()
    assert elected == [
        (1, True),
        (2, True),
        (3, True),
        (4, False),
        (5, False),
        (6, False),
    ]

    anon_people = connection.execute(
        """
        SELECT count(DISTINCT person_id)
        FROM candidacies
        WHERE tse_sq_candidato IN (9101, 9102)
        """
    ).fetchone()[0]
    assert anon_people == 2
    assert connection.execute(
        """
        SELECT count(*)
        FROM person_identifiers
        WHERE cpf IS NULL AND voter_id IS NULL
        """
    ).fetchone()[0] == 0

    leaked = connection.execute(
        """
        SELECT
          bool_or(extra ? 'NR_CPF_CANDIDATO'),
          bool_or(extra ? 'NR_TITULO_ELEITORAL_CANDIDATO'),
          bool_or(extra ? 'NM_EMAIL'),
          bool_or(extra::text LIKE '%11111111111%'),
          bool_or(extra::text LIKE '%111111111111%'),
          bool_or(extra::text LIKE '%sigilo@example.com%'),
          bool_or(extra ? 'DS_GENERO'),
          bool_or(extra->>'NM_SOCIAL_CANDIDATO' = 'MARIA DO POVO')
        FROM candidacy_details
        """
    ).fetchone()
    assert leaked == (False, False, False, False, False, False, True, True)

    people_text = connection.execute(
        """
        SELECT string_agg(concat_ws(' ', civil_name, ballot_name, birth_city), ' ')
        FROM people
        """
    ).fetchone()[0]
    assert "11111111111" not in people_text
    assert "sigilo@example.com" not in people_text
    assert "111111111111" not in people_text
    assert "JOÃO DA SILVA" in people_text

    expense = connection.execute(
        """
        SELECT max_campaign_expense, declared_assets, photo_url,
               to_char(source_extracted_at AT TIME ZONE 'America/Sao_Paulo', 'DD/MM/YYYY HH24:MI:SS')
        FROM candidacies
        WHERE tse_sq_candidato = 8002
        """
    ).fetchone()
    assert expense[0] == 1500.50
    assert expense[1] is True
    assert expense[2] is None
    assert expense[3] == "21/09/2026 03:15:00"

    detail = connection.execute(
        """
        SELECT d.coalition_name, d.coalition_composition, d.federation_acronym
        FROM candidacy_details d
        JOIN candidacies c ON c.id = d.candidacy_id
        WHERE c.tse_sq_candidato = 8002
        """
    ).fetchone()
    assert detail == ("FRENTE TESTE", "PT / PSB", "PT/PCDOB/PV")

    replaced = connection.execute(
        """
        SELECT d.replaced, d.replaced_sq, d.process_number
        FROM candidacy_details d
        JOIN candidacies c ON c.id = d.candidacy_id
        WHERE c.tse_sq_candidato = 9003
        """
    ).fetchone()
    assert replaced == (True, 321, "06001234520246260000")

    spheres = dict(
        connection.execute("SELECT tse_code, sphere FROM offices ORDER BY tse_code").fetchall()
    )
    assert spheres[1] == "federal"
    assert spheres[3] == "estadual"
    assert spheres[6] == "federal"
    assert spheres[11] == "municipal"
    assert spheres[13] == "municipal"

    suplementar = connection.execute(
        """
        SELECT kind, scope
        FROM elections
        WHERE year = 2024 AND tse_election_id = 700
        """
    ).fetchone()
    assert suplementar == ("suplementar", "municipal")

    city = connection.execute(
        """
        SELECT child.kind, parent.kind, parent.tse_ue_code
        FROM electoral_units child
        JOIN electoral_units parent ON parent.id = child.parent_id
        WHERE child.tse_ue_code = '71072'
        """
    ).fetchone()
    assert city == ("municipio", "uf", "SP")

    assert connection.execute(
        "SELECT kind FROM electoral_units WHERE tse_ue_code = 'BR'"
    ).fetchone()[0] == "pais"

    before = _counts(connection)
    assert ingest_consulta_cand(2024, archive) == 30
    assert _counts(connection) == before

    turns_after = connection.execute(
        """
        SELECT turn, result, is_elected
        FROM candidacies
        WHERE tse_sq_candidato = 9001
        ORDER BY turn
        """
    ).fetchall()
    assert turns_after == [(1, "2º TURNO", False), (2, "ELEITO", True)]

    runs = connection.execute(
        """
        SELECT status, count(*), bool_and(rows_upserted = 30)
        FROM ingestion_runs
        GROUP BY status
        ORDER BY status
        """
    ).fetchall()
    assert runs == [("success", 2, True)]
