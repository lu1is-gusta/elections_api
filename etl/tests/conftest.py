import os

import pytest

from elections_etl.db import connect

_TRUNCATE = """
TRUNCATE TABLE
    candidacy_details,
    candidacy_assets,
    candidacy_links,
    candidacies,
    legislative_activities,
    political_mandates,
    person_identifiers,
    people,
    parties,
    electoral_units,
    offices,
    elections,
    ingestion_runs
RESTART IDENTITY CASCADE
"""


@pytest.fixture(scope="session")
def db_conn():
    database = os.environ.get("DB_DATABASE", "")
    if "test" not in database:
        pytest.fail(
            "DB_DATABASE precisa ser um banco de teste (o nome deve conter 'test'). "
            "A fixture apaga as tabelas de produto."
        )

    try:
        connection = connect()
    except Exception as exc:
        pytest.fail(str(exc))

    with connection.cursor() as cursor:
        cursor.execute("SELECT to_regclass('public.candidacies')")
        if cursor.fetchone()[0] is None:
            pytest.fail(
                "Tabela candidacies ausente. Aplique as migrations Laravel no Postgres de teste "
                "(DB_CONNECTION=pgsql php artisan migrate)."
            )
    yield connection
    connection.close()


@pytest.fixture
def clean_db(db_conn):
    db_conn.execute(_TRUNCATE)
    db_conn.execute("DROP TABLE IF EXISTS staging_consulta_cand")
    db_conn.commit()
    yield db_conn
