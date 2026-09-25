import argparse
import sys
from pathlib import Path

from elections_etl.pipeline import ingest_consulta_cand


def main(argv: list[str] | None = None) -> int:
    parser = argparse.ArgumentParser(prog="elections-etl")
    subparsers = parser.add_subparsers(dest="command", required=True)

    ingest = subparsers.add_parser("ingest")
    ingest.add_argument("--year", type=int, required=True)
    ingest.add_argument("--dataset", required=True)
    ingest.add_argument("--file", type=Path)

    args = parser.parse_args(argv)
    if args.dataset != "consulta_cand":
        print(f"dataset não suportado neste corte: {args.dataset}", file=sys.stderr)
        return 1

    try:
        rows = ingest_consulta_cand(args.year, args.file)
    except Exception as exc:
        print(f"erro: {exc}", file=sys.stderr)
        return 1

    print(f"ingestao concluida: {rows} candidaturas")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
