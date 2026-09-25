from pathlib import Path

import psycopg


def quote_ident(name: str) -> str:
    if not name.replace("_", "").isalnum() or not name.isupper():
        raise ValueError(f"coluna inválida: {name}")
    return f'"{name}"'


def load_staging(connection: psycopg.Connection, columns: list[str], paths: list[Path]) -> None:
    column_sql = ", ".join(f"{quote_ident(column)} TEXT" for column in columns)
    connection.execute("DROP TABLE IF EXISTS staging_consulta_cand")
    connection.execute(f"CREATE UNLOGGED TABLE staging_consulta_cand ({column_sql})")

    copy_sql = (
        "COPY staging_consulta_cand FROM STDIN WITH "
        "(FORMAT csv, HEADER true, DELIMITER ';', ENCODING 'LATIN1')"
    )

    for path in paths:
        with path.open("rb") as handle, connection.cursor() as cursor:
            with cursor.copy(copy_sql) as copy:
                while chunk := handle.read(1024 * 1024):
                    copy.write(chunk)

    connection.execute("ANALYZE staging_consulta_cand")


def drop_staging(connection: psycopg.Connection) -> None:
    connection.execute("DROP TABLE IF EXISTS staging_consulta_cand")
