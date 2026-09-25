from pathlib import Path

import httpx

CONSULTA_CAND_URL = (
    "https://cdn.tse.jus.br/estatistica/sead/odsele/consulta_cand/consulta_cand_{year}.zip"
)


def download_consulta_cand(year: int, dest: Path) -> None:
    dest.parent.mkdir(parents=True, exist_ok=True)
    url = CONSULTA_CAND_URL.format(year=year)
    timeout = httpx.Timeout(60.0, read=300.0)

    with httpx.stream("GET", url, follow_redirects=True, timeout=timeout) as response:
        response.raise_for_status()
        with dest.open("wb") as handle:
            for chunk in response.iter_bytes():
                handle.write(chunk)
