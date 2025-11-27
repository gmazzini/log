TRUNCATE TABLE aux2;

INSERT INTO aux2 (callsign, gram)
SELECT DISTINCT
       c.callsign,
       SUBSTRING(c.callsign, n.n, 2) AS gram
FROM (
    SELECT DISTINCT
           LEFT(UPPER(TRIM(callsign)), 6) AS callsign
    FROM log
    WHERE callsign IS NOT NULL
      AND callsign <> ''
) AS c
JOIN (
    SELECT 1 AS n UNION ALL
    SELECT 2 UNION ALL
    SELECT 3 UNION ALL
    SELECT 4 UNION ALL
    SELECT 5
) AS n
  ON n.n <= CHAR_LENGTH(c.callsign) - 1;

TRUNCATE TABLE aux3;

INSERT INTO aux3 (callsign, gram)
SELECT DISTINCT
       c.callsign,
       SUBSTRING(c.callsign, n.n, 3) AS gram
FROM (
    SELECT DISTINCT
           LEFT(UPPER(TRIM(callsign)), 6) AS callsign
    FROM log
    WHERE callsign IS NOT NULL
      AND callsign <> ''
) AS c
JOIN (
    SELECT 1 AS n UNION ALL
    SELECT 2 UNION ALL
    SELECT 3 UNION ALL
    SELECT 4
) AS n
  ON n.n <= CHAR_LENGTH(c.callsign) - 2;
