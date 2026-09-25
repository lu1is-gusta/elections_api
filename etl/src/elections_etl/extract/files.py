import csv
import zipfile
from pathlib import Path

from elections_etl.extract.columns import ensure_columns


def read_header(path: Path) -> list[str]:
    with path.open("r", encoding="latin-1", newline="") as handle:
        try:
            header = next(csv.reader(handle, delimiter=";"))
        except StopIteration as exc:
            raise ValueError(f"CSV vazio: {path}") from exc

    if header:
        header[0] = header[0].lstrip("\ufeff").removeprefix("ï»¿").strip()
        header = [column.strip() for column in header]

    return header


def validate_headers(paths: list[Path]) -> list[str]:
    if not paths:
        raise ValueError("nenhum CSV para ingerir")

    headers = [read_header(path) for path in paths]
    ensure_columns(headers[0])

    for other in headers[1:]:
        if other != headers[0]:
            raise ValueError("CSVs do zip com cabeçalhos diferentes")

    return headers[0]


def collect_csvs(source: Path, work: Path) -> list[Path]:
    if source.suffix.lower() == ".csv":
        return [source]

    if source.suffix.lower() != ".zip":
        raise ValueError("arquivo deve ser .zip ou .csv")

    work.mkdir(parents=True, exist_ok=True)
    extracted: list[Path] = []
    seen: set[str] = set()

    with zipfile.ZipFile(source) as archive:
        names = [
            name
            for name in archive.namelist()
            if name.lower().endswith(".csv")
            and not name.startswith("__MACOSX")
            and not Path(name).name.startswith(".")
        ]
        if not names:
            raise ValueError("zip sem CSV")

        for name in names:
            target = (work / Path(name).name).resolve()
            if not target.is_relative_to(work.resolve()):
                raise ValueError(f"caminho inválido no zip: {name}")
            if target.name in seen:
                raise ValueError(f"CSV duplicado no zip: {target.name}")
            seen.add(target.name)

            with archive.open(name) as src, target.open("wb") as dst:
                dst.write(src.read())
            extracted.append(target)

    return sorted(extracted)
