import re

REQUIRED_COLUMNS = (
    "DT_GERACAO",
    "HH_GERACAO",
    "ANO_ELEICAO",
    "NM_TIPO_ELEICAO",
    "NR_TURNO",
    "CD_ELEICAO",
    "DS_ELEICAO",
    "DT_ELEICAO",
    "TP_ABRANGENCIA",
    "SG_UF",
    "SG_UE",
    "NM_UE",
    "CD_CARGO",
    "DS_CARGO",
    "SQ_CANDIDATO",
    "NR_CANDIDATO",
    "NM_CANDIDATO",
    "NM_URNA_CANDIDATO",
    "NR_CPF_CANDIDATO",
    "NM_EMAIL",
    "CD_SITUACAO_CANDIDATURA",
    "DS_SITUACAO_CANDIDATURA",
    "NR_PARTIDO",
    "SG_PARTIDO",
    "NM_PARTIDO",
    "SG_FEDERACAO",
    "NM_COLIGACAO",
    "DS_COMPOSICAO_COLIGACAO",
    "DS_NACIONALIDADE",
    "SG_UF_NASCIMENTO",
    "NM_MUNICIPIO_NASCIMENTO",
    "DT_NASCIMENTO",
    "NR_IDADE_DATA_POSSE",
    "NR_TITULO_ELEITORAL_CANDIDATO",
    "CD_GENERO",
    "DS_GRAU_INSTRUCAO",
    "DS_ESTADO_CIVIL",
    "CD_COR_RACA",
    "DS_OCUPACAO",
    "VR_DESPESA_MAX_CAMPANHA",
    "CD_SIT_TOT_TURNO",
    "DS_SIT_TOT_TURNO",
    "ST_REELEICAO",
    "ST_DECLARAR_BENS",
    "NR_PROCESSO",
    "ST_CANDIDATO_INSERIDO_URNA",
    "ST_SUBSTITUIDO",
    "SQ_SUBSTITUIDO",
)

_COLUMN_NAME = re.compile(r"^[A-Z0-9_]+$")


def ensure_columns(header: list[str]) -> None:
    missing = [column for column in REQUIRED_COLUMNS if column not in header]
    if missing:
        raise ValueError("colunas obrigatórias ausentes: " + ", ".join(missing))

    if len(header) != len(set(header)):
        raise ValueError("cabeçalho com coluna duplicada")

    for column in header:
        if not _COLUMN_NAME.fullmatch(column):
            raise ValueError(f"coluna inválida: {column}")
