CREATE OR REPLACE FUNCTION pg_temp.tse_text(v text) RETURNS text
LANGUAGE sql IMMUTABLE AS $fn$
  SELECT NULLIF(NULLIF(NULLIF(btrim(v), ''), '#NULO#'), '#NE#')
$fn$
-- %%
CREATE OR REPLACE FUNCTION pg_temp.tse_bool(v text) RETURNS boolean
LANGUAGE sql IMMUTABLE AS $fn$
  SELECT CASE pg_temp.tse_text(v)
    WHEN 'S' THEN true
    WHEN 'N' THEN false
    ELSE NULL
  END
$fn$
-- %%
CREATE OR REPLACE FUNCTION pg_temp.tse_digits(v text) RETURNS text
LANGUAGE sql IMMUTABLE AS $fn$
  SELECT NULLIF(regexp_replace(pg_temp.tse_text(v), '[^0-9]', '', 'g'), '')
$fn$
-- %%
CREATE OR REPLACE FUNCTION pg_temp.tse_int(v text) RETURNS integer
LANGUAGE sql IMMUTABLE AS $fn$
  SELECT CASE
    WHEN pg_temp.tse_digits(v) ~ '^[0-9]+$' THEN pg_temp.tse_digits(v)::integer
    ELSE NULL
  END
$fn$
-- %%
CREATE OR REPLACE FUNCTION pg_temp.tse_smallint(v text) RETURNS smallint
LANGUAGE sql IMMUTABLE AS $fn$
  SELECT CASE
    WHEN pg_temp.tse_digits(v) ~ '^[0-9]{1,4}$' THEN pg_temp.tse_digits(v)::smallint
    ELSE NULL
  END
$fn$
-- %%
CREATE OR REPLACE FUNCTION pg_temp.tse_bigint(v text) RETURNS bigint
LANGUAGE sql IMMUTABLE AS $fn$
  SELECT CASE
    WHEN pg_temp.tse_digits(v) ~ '^[0-9]+$' THEN pg_temp.tse_digits(v)::bigint
    ELSE NULL
  END
$fn$
-- %%
CREATE OR REPLACE FUNCTION pg_temp.tse_date(v text) RETURNS date
LANGUAGE sql IMMUTABLE AS $fn$
  SELECT CASE
    WHEN pg_temp.tse_text(v) ~ '^[0-9]{2}/[0-9]{2}/[0-9]{4}$'
      THEN to_date(pg_temp.tse_text(v), 'DD/MM/YYYY')
    ELSE NULL
  END
$fn$
-- %%
CREATE OR REPLACE FUNCTION pg_temp.tse_numeric(v text) RETURNS numeric
LANGUAGE sql IMMUTABLE AS $fn$
  SELECT CASE
    WHEN pg_temp.tse_text(v) IS NULL THEN NULL
    WHEN strpos(pg_temp.tse_text(v), ',') > 0
      THEN replace(replace(pg_temp.tse_text(v), '.', ''), ',', '.')::numeric
    WHEN pg_temp.tse_text(v) ~ '^[0-9]+(\.[0-9]+)?$' THEN pg_temp.tse_text(v)::numeric
    ELSE NULL
  END
$fn$
-- %%
CREATE OR REPLACE FUNCTION pg_temp.tse_cpf(v text) RETURNS char(11)
LANGUAGE sql IMMUTABLE AS $fn$
  SELECT CASE
    WHEN pg_temp.tse_digits(v) ~ '^[0-9]{11}$' THEN pg_temp.tse_digits(v)::char(11)
    ELSE NULL
  END
$fn$
-- %%
CREATE OR REPLACE FUNCTION pg_temp.tse_uf(v text) RETURNS char(2)
LANGUAGE sql IMMUTABLE AS $fn$
  SELECT CASE
    WHEN char_length(upper(pg_temp.tse_text(v))) = 2 THEN upper(pg_temp.tse_text(v))::char(2)
    ELSE NULL
  END
$fn$
-- %%
CREATE OR REPLACE FUNCTION pg_temp.tse_ascii(v text) RETURNS text
LANGUAGE sql IMMUTABLE AS $fn$
  SELECT upper(translate(
    COALESCE(pg_temp.tse_text(v), ''),
    'ÁÀÂÃÉÊÍÓÔÕÚÜÇáàâãéêíóôõúüç',
    'AAAAEEIOOOUUCAAAAEEIOOOUUC'
  ))
$fn$
-- %%
CREATE TEMP TABLE norm ON COMMIT DROP AS
SELECT *
FROM (
  SELECT
    CASE
      WHEN pg_temp.tse_text(s."DT_GERACAO") IS NULL OR pg_temp.tse_text(s."HH_GERACAO") IS NULL THEN NULL
      ELSE to_timestamp(
        pg_temp.tse_text(s."DT_GERACAO") || ' ' || pg_temp.tse_text(s."HH_GERACAO"),
        'DD/MM/YYYY HH24:MI:SS'
      )
    END AS source_extracted_at,
    CASE
      WHEN pg_temp.tse_digits(s."ANO_ELEICAO") ~ '^[0-9]{4}$'
        THEN pg_temp.tse_digits(s."ANO_ELEICAO")::smallint
      ELSE NULL
    END AS year,
    pg_temp.tse_int(s."CD_ELEICAO") AS tse_election_id,
    COALESCE(pg_temp.tse_text(s."DS_ELEICAO"), 'Eleição ' || pg_temp.tse_text(s."ANO_ELEICAO")) AS election_name,
    CASE
      WHEN strpos(pg_temp.tse_ascii(s."NM_TIPO_ELEICAO"), 'SUPLEMENTAR') > 0 THEN 'suplementar'
      WHEN strpos(pg_temp.tse_ascii(s."NM_TIPO_ELEICAO"), 'ORDINARIA') > 0 THEN 'ordinaria'
      ELSE NULL
    END AS kind,
    CASE pg_temp.tse_ascii(s."TP_ABRANGENCIA")
      WHEN 'MUNICIPAL' THEN 'municipal'
      WHEN 'ESTADUAL' THEN 'estadual'
      WHEN 'FEDERAL' THEN 'federal'
      ELSE NULL
    END AS scope,
    pg_temp.tse_date(s."DT_ELEICAO") AS election_date,
    pg_temp.tse_smallint(s."NR_TURNO") AS turn,
    pg_temp.tse_uf(s."SG_UF") AS uf,
    CASE
      WHEN upper(pg_temp.tse_text(s."SG_UE")) = 'BR' THEN 'BR'
      WHEN upper(pg_temp.tse_text(s."SG_UE")) = upper(pg_temp.tse_text(s."SG_UF"))
        AND char_length(upper(pg_temp.tse_text(s."SG_UE"))) = 2
        THEN upper(pg_temp.tse_text(s."SG_UE"))
      ELSE pg_temp.tse_text(s."SG_UE")
    END AS ue_code,
    COALESCE(pg_temp.tse_text(s."NM_UE"), pg_temp.tse_text(s."SG_UE"), 'NÃO INFORMADO') AS unit_name,
    pg_temp.tse_smallint(s."CD_CARGO") AS office_code,
    COALESCE(pg_temp.tse_text(s."DS_CARGO"), pg_temp.tse_text(s."CD_CARGO"), 'NÃO INFORMADO') AS office_name,
    pg_temp.tse_bigint(s."SQ_CANDIDATO") AS sq,
    pg_temp.tse_int(s."NR_CANDIDATO") AS ballot_number,
    COALESCE(
      pg_temp.tse_text(s."NM_CANDIDATO"),
      pg_temp.tse_text(s."NM_URNA_CANDIDATO"),
      'NÃO INFORMADO'
    ) AS civil_name,
    COALESCE(
      pg_temp.tse_text(s."NM_URNA_CANDIDATO"),
      pg_temp.tse_text(s."NM_CANDIDATO"),
      'NÃO INFORMADO'
    ) AS ballot_name,
    pg_temp.tse_cpf(s."NR_CPF_CANDIDATO") AS cpf,
    pg_temp.tse_int(s."CD_SITUACAO_CANDIDATURA") AS status_code,
    pg_temp.tse_text(s."DS_SITUACAO_CANDIDATURA") AS status,
    pg_temp.tse_smallint(s."NR_PARTIDO") AS party_number,
    upper(pg_temp.tse_text(s."SG_PARTIDO")) AS party_acronym,
    COALESCE(pg_temp.tse_text(s."NM_PARTIDO"), pg_temp.tse_text(s."SG_PARTIDO"), 'NÃO INFORMADO') AS party_name,
    pg_temp.tse_text(s."SG_FEDERACAO") AS federation_acronym,
    pg_temp.tse_text(s."NM_COLIGACAO") AS coalition_name,
    pg_temp.tse_text(s."DS_COMPOSICAO_COLIGACAO") AS coalition_composition,
    pg_temp.tse_text(s."DS_NACIONALIDADE") AS nationality,
    pg_temp.tse_uf(s."SG_UF_NASCIMENTO") AS birth_uf,
    pg_temp.tse_text(s."NM_MUNICIPIO_NASCIMENTO") AS birth_city,
    pg_temp.tse_date(s."DT_NASCIMENTO") AS birth_date,
    pg_temp.tse_smallint(s."NR_IDADE_DATA_POSSE") AS age_at_election,
    pg_temp.tse_digits(s."NR_TITULO_ELEITORAL_CANDIDATO") AS voter_id,
    pg_temp.tse_smallint(s."CD_GENERO") AS gender_code,
    pg_temp.tse_text(s."DS_GRAU_INSTRUCAO") AS education,
    pg_temp.tse_text(s."DS_ESTADO_CIVIL") AS marital_status,
    pg_temp.tse_smallint(s."CD_COR_RACA") AS race_code,
    pg_temp.tse_text(s."DS_OCUPACAO") AS occupation,
    pg_temp.tse_numeric(s."VR_DESPESA_MAX_CAMPANHA") AS max_campaign_expense,
    pg_temp.tse_int(s."CD_SIT_TOT_TURNO") AS result_code,
    pg_temp.tse_text(s."DS_SIT_TOT_TURNO") AS result,
    pg_temp.tse_bool(s."ST_REELEICAO") AS is_reelection,
    pg_temp.tse_bool(s."ST_DECLARAR_BENS") AS declared_assets,
    pg_temp.tse_text(s."NR_PROCESSO") AS process_number,
    pg_temp.tse_bool(s."ST_CANDIDATO_INSERIDO_URNA") AS inserted_on_ballot,
    pg_temp.tse_bool(s."ST_SUBSTITUIDO") AS replaced,
    pg_temp.tse_bigint(s."SQ_SUBSTITUIDO") AS replaced_sq,
    lat.extra
  FROM staging_consulta_cand s
  CROSS JOIN LATERAL (
    SELECT COALESCE(jsonb_object_agg(t.key, to_jsonb(t.value)), '{}'::jsonb) AS extra
    FROM jsonb_each_text(to_jsonb(s)) AS t(key, value)
    WHERE NOT (t.key = ANY (ARRAY[
      'DT_GERACAO', 'HH_GERACAO', 'ANO_ELEICAO', 'NM_TIPO_ELEICAO', 'NR_TURNO',
      'CD_ELEICAO', 'DS_ELEICAO', 'DT_ELEICAO', 'TP_ABRANGENCIA',
      'SG_UF', 'SG_UE', 'NM_UE', 'CD_CARGO', 'DS_CARGO',
      'SQ_CANDIDATO', 'NR_CANDIDATO', 'NM_CANDIDATO', 'NM_URNA_CANDIDATO',
      'NR_CPF_CANDIDATO', 'NM_EMAIL', 'NR_TITULO_ELEITORAL_CANDIDATO',
      'CD_SITUACAO_CANDIDATURA', 'DS_SITUACAO_CANDIDATURA',
      'NR_PARTIDO', 'SG_PARTIDO', 'NM_PARTIDO',
      'SG_FEDERACAO', 'NM_COLIGACAO', 'DS_COMPOSICAO_COLIGACAO',
      'DS_NACIONALIDADE', 'SG_UF_NASCIMENTO', 'NM_MUNICIPIO_NASCIMENTO', 'DT_NASCIMENTO',
      'NR_IDADE_DATA_POSSE', 'CD_GENERO', 'DS_GRAU_INSTRUCAO', 'DS_ESTADO_CIVIL',
      'CD_COR_RACA', 'DS_OCUPACAO', 'VR_DESPESA_MAX_CAMPANHA',
      'CD_SIT_TOT_TURNO', 'DS_SIT_TOT_TURNO', 'ST_REELEICAO', 'ST_DECLARAR_BENS',
      'NR_PROCESSO', 'ST_CANDIDATO_INSERIDO_URNA', 'ST_SUBSTITUIDO', 'SQ_SUBSTITUIDO'
    ]::text[]))
      AND btrim(t.value) <> ''
      AND btrim(t.value) <> '#NULO#'
      AND btrim(t.value) <> '#NE#'
  ) lat
) typed
WHERE year IS NOT NULL
  AND tse_election_id IS NOT NULL
  AND sq IS NOT NULL
  AND turn IS NOT NULL
-- %%
CREATE INDEX ON norm (cpf)
-- %%
CREATE INDEX ON norm (voter_id)
-- %%
CREATE INDEX ON norm (year, tse_election_id, sq, turn)
-- %%
ANALYZE norm
-- %%
CREATE TEMP TABLE norm_one ON COMMIT DROP AS
SELECT DISTINCT ON (year, tse_election_id, sq, turn)
  *
FROM norm
ORDER BY year, tse_election_id, sq, turn, source_extracted_at DESC NULLS LAST
-- %%
DROP TABLE IF EXISTS staging_consulta_cand
-- %%
INSERT INTO elections (year, tse_election_id, name, kind, scope, election_date, created_at, updated_at)
SELECT DISTINCT ON (year, tse_election_id)
  year,
  tse_election_id,
  election_name,
  kind,
  scope,
  election_date,
  now(),
  now()
FROM norm_one
ORDER BY year, tse_election_id, source_extracted_at DESC NULLS LAST
ON CONFLICT (year, tse_election_id) DO UPDATE
SET
  name = EXCLUDED.name,
  kind = EXCLUDED.kind,
  scope = EXCLUDED.scope,
  election_date = EXCLUDED.election_date,
  updated_at = now()
-- %%
INSERT INTO offices (tse_code, name, sphere, created_at, updated_at)
SELECT DISTINCT ON (office_code)
  office_code,
  office_name,
  CASE
    WHEN office_code IN (1, 2, 5, 6, 9, 10) THEN 'federal'
    WHEN office_code IN (3, 4, 7, 8) THEN 'estadual'
    WHEN office_code IN (11, 12, 13) THEN 'municipal'
  END,
  now(),
  now()
FROM norm_one
WHERE office_code IS NOT NULL
ORDER BY office_code, length(office_name) DESC
ON CONFLICT (tse_code) DO UPDATE
SET
  name = EXCLUDED.name,
  sphere = EXCLUDED.sphere,
  updated_at = now()
-- %%
INSERT INTO electoral_units (tse_ue_code, uf, name, kind, created_at, updated_at)
SELECT DISTINCT ON (ue_code)
  ue_code,
  NULL,
  COALESCE(NULLIF(unit_name, ''), 'Brasil'),
  'pais',
  now(),
  now()
FROM norm_one
WHERE ue_code = 'BR'
ORDER BY ue_code, length(unit_name) DESC
ON CONFLICT (tse_ue_code) DO UPDATE
SET
  name = EXCLUDED.name,
  kind = EXCLUDED.kind,
  updated_at = now()
-- %%
INSERT INTO electoral_units (tse_ue_code, uf, name, kind, created_at, updated_at)
SELECT code, code, name, 'uf', now(), now()
FROM (
  SELECT DISTINCT ON (code)
    code,
    name
  FROM (
    SELECT btrim(uf::text) AS code, btrim(uf::text) AS name, 0 AS preference
    FROM norm
    WHERE uf IS NOT NULL AND btrim(uf::text) <> 'BR'
    UNION ALL
    SELECT ue_code, unit_name, 1
    FROM norm
    WHERE ue_code = btrim(uf::text)
      AND uf IS NOT NULL
      AND btrim(uf::text) <> 'BR'
  ) AS listed
  ORDER BY code, preference DESC, length(name) DESC
) AS picked
ON CONFLICT (tse_ue_code) DO UPDATE
SET
  name = EXCLUDED.name,
  uf = EXCLUDED.uf,
  kind = EXCLUDED.kind,
  updated_at = now()
-- %%
INSERT INTO electoral_units (tse_ue_code, uf, name, kind, parent_id, created_at, updated_at)
SELECT DISTINCT ON (n.ue_code)
  n.ue_code,
  btrim(n.uf::text),
  n.unit_name,
  'municipio',
  parent.id,
  now(),
  now()
FROM norm n
JOIN electoral_units AS parent
  ON parent.tse_ue_code = btrim(n.uf::text)
 AND parent.kind = 'uf'
WHERE n.ue_code IS NOT NULL
  AND n.uf IS NOT NULL
  AND n.ue_code <> btrim(n.uf::text)
  AND n.ue_code <> 'BR'
ORDER BY n.ue_code, length(n.unit_name) DESC
ON CONFLICT (tse_ue_code) DO UPDATE
SET
  uf = EXCLUDED.uf,
  name = EXCLUDED.name,
  kind = EXCLUDED.kind,
  parent_id = EXCLUDED.parent_id,
  updated_at = now()
-- %%
INSERT INTO parties (number, acronym, name, created_at, updated_at)
SELECT DISTINCT ON (party_number, party_acronym)
  party_number,
  party_acronym,
  party_name,
  now(),
  now()
FROM norm_one
WHERE party_number IS NOT NULL
  AND party_acronym IS NOT NULL
ORDER BY party_number, party_acronym, length(party_name) DESC
ON CONFLICT (number, acronym) DO UPDATE
SET
  name = EXCLUDED.name,
  updated_at = now()
-- %%
UPDATE people AS p
SET
  civil_name = src.civil_name,
  ballot_name = src.ballot_name,
  birth_date = src.birth_date,
  birth_uf = src.birth_uf,
  birth_city = src.birth_city,
  gender_code = src.gender_code,
  race_code = src.race_code,
  updated_at = now()
FROM (
  SELECT DISTINCT ON (cpf)
    cpf,
    civil_name,
    ballot_name,
    birth_date,
    birth_uf,
    birth_city,
    gender_code,
    race_code
  FROM norm
  WHERE cpf IS NOT NULL
  ORDER BY cpf, source_extracted_at DESC NULLS LAST, turn DESC
) AS src
JOIN person_identifiers AS pi ON pi.cpf = src.cpf
WHERE p.id = pi.person_id
-- %%
UPDATE person_identifiers AS pi
SET
  voter_id = src.voter_id,
  updated_at = now()
FROM (
  SELECT DISTINCT ON (cpf)
    cpf,
    voter_id
  FROM norm
  WHERE cpf IS NOT NULL
    AND voter_id IS NOT NULL
  ORDER BY cpf, source_extracted_at DESC NULLS LAST, turn DESC
) AS src
WHERE pi.cpf = src.cpf
  AND src.voter_id IS DISTINCT FROM pi.voter_id
  AND NOT EXISTS (
    SELECT 1
    FROM person_identifiers AS other
    WHERE other.voter_id = src.voter_id
      AND other.person_id <> pi.person_id
  )
-- %%
CREATE TEMP TABLE new_by_cpf ON COMMIT DROP AS
SELECT
  nextval(pg_get_serial_sequence('people', 'id')) AS person_id,
  src.cpf,
  CASE
    WHEN src.voter_id IS NULL THEN NULL
    WHEN EXISTS (
      SELECT 1 FROM person_identifiers AS pi WHERE pi.voter_id = src.voter_id
    ) THEN NULL
    WHEN row_number() OVER (PARTITION BY src.voter_id ORDER BY src.cpf) > 1 THEN NULL
    ELSE src.voter_id
  END AS voter_id,
  src.civil_name,
  src.ballot_name,
  src.birth_date,
  src.birth_uf,
  src.birth_city,
  src.gender_code,
  src.race_code
FROM (
  SELECT DISTINCT ON (cpf)
    cpf,
    voter_id,
    civil_name,
    ballot_name,
    birth_date,
    birth_uf,
    birth_city,
    gender_code,
    race_code
  FROM norm
  WHERE cpf IS NOT NULL
    AND NOT EXISTS (SELECT 1 FROM person_identifiers AS pi WHERE pi.cpf = norm.cpf)
  ORDER BY cpf, source_extracted_at DESC NULLS LAST, turn DESC
) AS src
-- %%
INSERT INTO people (
  id, civil_name, ballot_name, birth_date, birth_uf, birth_city,
  gender_code, race_code, created_at, updated_at
) {{people_id_clause}}
SELECT
  person_id, civil_name, ballot_name, birth_date, birth_uf, birth_city,
  gender_code, race_code, now(), now()
FROM new_by_cpf
-- %%
INSERT INTO person_identifiers (person_id, cpf, voter_id, created_at, updated_at)
SELECT person_id, cpf, voter_id, now(), now()
FROM new_by_cpf
-- %%
UPDATE people AS p
SET
  civil_name = src.civil_name,
  ballot_name = src.ballot_name,
  birth_date = src.birth_date,
  birth_uf = src.birth_uf,
  birth_city = src.birth_city,
  gender_code = src.gender_code,
  race_code = src.race_code,
  updated_at = now()
FROM (
  SELECT DISTINCT ON (voter_id)
    voter_id,
    civil_name,
    ballot_name,
    birth_date,
    birth_uf,
    birth_city,
    gender_code,
    race_code
  FROM norm
  WHERE cpf IS NULL
    AND voter_id IS NOT NULL
  ORDER BY voter_id, source_extracted_at DESC NULLS LAST, turn DESC
) AS src
JOIN person_identifiers AS pi ON pi.voter_id = src.voter_id
WHERE p.id = pi.person_id
-- %%
CREATE TEMP TABLE new_by_voter ON COMMIT DROP AS
SELECT
  nextval(pg_get_serial_sequence('people', 'id')) AS person_id,
  src.voter_id,
  src.civil_name,
  src.ballot_name,
  src.birth_date,
  src.birth_uf,
  src.birth_city,
  src.gender_code,
  src.race_code
FROM (
  SELECT DISTINCT ON (voter_id)
    voter_id,
    civil_name,
    ballot_name,
    birth_date,
    birth_uf,
    birth_city,
    gender_code,
    race_code
  FROM norm
  WHERE cpf IS NULL
    AND voter_id IS NOT NULL
    AND NOT EXISTS (
      SELECT 1 FROM person_identifiers AS pi WHERE pi.voter_id = norm.voter_id
    )
  ORDER BY voter_id, source_extracted_at DESC NULLS LAST, turn DESC
) AS src
-- %%
INSERT INTO people (
  id, civil_name, ballot_name, birth_date, birth_uf, birth_city,
  gender_code, race_code, created_at, updated_at
) {{people_id_clause}}
SELECT
  person_id, civil_name, ballot_name, birth_date, birth_uf, birth_city,
  gender_code, race_code, now(), now()
FROM new_by_voter
-- %%
INSERT INTO person_identifiers (person_id, cpf, voter_id, created_at, updated_at)
SELECT person_id, NULL, voter_id, now(), now()
FROM new_by_voter
-- %%
UPDATE people AS p
SET
  civil_name = src.civil_name,
  ballot_name = src.ballot_name,
  birth_date = src.birth_date,
  birth_uf = src.birth_uf,
  birth_city = src.birth_city,
  gender_code = src.gender_code,
  race_code = src.race_code,
  updated_at = now()
FROM norm_one AS src
JOIN elections AS e
  ON e.year = src.year
 AND e.tse_election_id = src.tse_election_id
JOIN candidacies AS c
  ON c.election_id = e.id
 AND c.tse_sq_candidato = src.sq
 AND c.turn = src.turn
WHERE src.cpf IS NULL
  AND src.voter_id IS NULL
  AND p.id = c.person_id
-- %%
CREATE TEMP TABLE new_anon ON COMMIT DROP AS
SELECT
  nextval(pg_get_serial_sequence('people', 'id')) AS person_id,
  src.year,
  src.tse_election_id,
  src.sq,
  src.turn,
  src.civil_name,
  src.ballot_name,
  src.birth_date,
  src.birth_uf,
  src.birth_city,
  src.gender_code,
  src.race_code
FROM norm_one AS src
WHERE src.cpf IS NULL
  AND src.voter_id IS NULL
  AND NOT EXISTS (
    SELECT 1
    FROM elections AS e
    JOIN candidacies AS c ON c.election_id = e.id
    WHERE e.year = src.year
      AND e.tse_election_id = src.tse_election_id
      AND c.tse_sq_candidato = src.sq
      AND c.turn = src.turn
  )
-- %%
INSERT INTO people (
  id, civil_name, ballot_name, birth_date, birth_uf, birth_city,
  gender_code, race_code, created_at, updated_at
) {{people_id_clause}}
SELECT
  person_id, civil_name, ballot_name, birth_date, birth_uf, birth_city,
  gender_code, race_code, now(), now()
FROM new_anon
-- %%
WITH upserted AS (
  INSERT INTO candidacies (
    person_id, election_id, office_id, electoral_unit_id, party_id,
    tse_sq_candidato, turn, ballot_number, ballot_name, civil_name,
    uf, unit_name, office_name, party_acronym, party_number,
    status_code, status, result_code, result, is_elected,
    is_reelection, inserted_on_ballot, occupation, education, age_at_election,
    gender_code, race_code, photo_url, max_campaign_expense, declared_assets,
    source_extracted_at, created_at, updated_at
  )
  SELECT
    COALESCE(by_cpf.person_id, by_voter.person_id, existing.person_id, anon.person_id),
    e.id,
    o.id::smallint,
    eu.id,
    p.id::integer,
    n.sq,
    n.turn,
    n.ballot_number,
    n.ballot_name,
    n.civil_name,
    btrim(n.uf::text),
    n.unit_name,
    n.office_name,
    n.party_acronym,
    n.party_number,
    n.status_code,
    n.status,
    n.result_code,
    n.result,
    COALESCE((n.result_code IN (1, 2, 3)), false),
    n.is_reelection,
    n.inserted_on_ballot,
    n.occupation,
    n.education,
    n.age_at_election,
    n.gender_code,
    n.race_code,
    NULL,
    n.max_campaign_expense,
    n.declared_assets,
    n.source_extracted_at,
    now(),
    now()
  FROM norm_one AS n
  JOIN elections AS e
    ON e.year = n.year
   AND e.tse_election_id = n.tse_election_id
  JOIN offices AS o ON o.tse_code = n.office_code
  JOIN electoral_units AS eu ON eu.tse_ue_code = n.ue_code
  JOIN parties AS p
    ON p.number = n.party_number
   AND p.acronym = n.party_acronym
  LEFT JOIN person_identifiers AS by_cpf
    ON n.cpf IS NOT NULL
   AND by_cpf.cpf = n.cpf
  LEFT JOIN person_identifiers AS by_voter
    ON n.cpf IS NULL
   AND n.voter_id IS NOT NULL
   AND by_voter.voter_id = n.voter_id
  LEFT JOIN candidacies AS existing
    ON n.cpf IS NULL
   AND n.voter_id IS NULL
   AND existing.election_id = e.id
   AND existing.tse_sq_candidato = n.sq
   AND existing.turn = n.turn
  LEFT JOIN new_anon AS anon
    ON n.cpf IS NULL
   AND n.voter_id IS NULL
   AND anon.year = n.year
   AND anon.tse_election_id = n.tse_election_id
   AND anon.sq = n.sq
   AND anon.turn = n.turn
  ON CONFLICT (election_id, tse_sq_candidato, turn) DO UPDATE
  SET
    person_id = EXCLUDED.person_id,
    office_id = EXCLUDED.office_id,
    electoral_unit_id = EXCLUDED.electoral_unit_id,
    party_id = EXCLUDED.party_id,
    ballot_number = EXCLUDED.ballot_number,
    ballot_name = EXCLUDED.ballot_name,
    civil_name = EXCLUDED.civil_name,
    uf = EXCLUDED.uf,
    unit_name = EXCLUDED.unit_name,
    office_name = EXCLUDED.office_name,
    party_acronym = EXCLUDED.party_acronym,
    party_number = EXCLUDED.party_number,
    status_code = EXCLUDED.status_code,
    status = EXCLUDED.status,
    result_code = EXCLUDED.result_code,
    result = EXCLUDED.result,
    is_elected = EXCLUDED.is_elected,
    is_reelection = EXCLUDED.is_reelection,
    inserted_on_ballot = EXCLUDED.inserted_on_ballot,
    occupation = EXCLUDED.occupation,
    education = EXCLUDED.education,
    age_at_election = EXCLUDED.age_at_election,
    gender_code = EXCLUDED.gender_code,
    race_code = EXCLUDED.race_code,
    max_campaign_expense = EXCLUDED.max_campaign_expense,
    declared_assets = EXCLUDED.declared_assets,
    source_extracted_at = EXCLUDED.source_extracted_at,
    updated_at = now()
  RETURNING id
)
SELECT count(*)::int AS rows_upserted FROM upserted
-- %%
INSERT INTO candidacy_details (
  candidacy_id, coalition_name, coalition_composition, federation_acronym,
  nationality, marital_status, process_number, replaced, replaced_sq, extra
)
SELECT
  c.id,
  n.coalition_name,
  n.coalition_composition,
  n.federation_acronym,
  n.nationality,
  n.marital_status,
  n.process_number,
  n.replaced,
  n.replaced_sq,
  n.extra
FROM norm_one AS n
JOIN elections AS e
  ON e.year = n.year
 AND e.tse_election_id = n.tse_election_id
JOIN candidacies AS c
  ON c.election_id = e.id
 AND c.tse_sq_candidato = n.sq
 AND c.turn = n.turn
ON CONFLICT (candidacy_id) DO UPDATE
SET
  coalition_name = EXCLUDED.coalition_name,
  coalition_composition = EXCLUDED.coalition_composition,
  federation_acronym = EXCLUDED.federation_acronym,
  nationality = EXCLUDED.nationality,
  marital_status = EXCLUDED.marital_status,
  process_number = EXCLUDED.process_number,
  replaced = EXCLUDED.replaced,
  replaced_sq = EXCLUDED.replaced_sq,
  extra = EXCLUDED.extra
