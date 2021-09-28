-- ---------------------------------------------------------------------------------------------------------------------
-- ZADANIE
-- ---------------------------------------------------------------------------------------------------------------------

-- Máte jednoduchú tabuľku s primárnym kľúčom a hodnotou v druhom stĺpci. Niektoré z týchto hodnôt môžu byť duplicitné.
-- Napíšte prosím SQL query, ktorá vráti všetky riadky z tabuľky s duplicitnými hodnotami (*celé* riadky).

-- tabuľka duplicates - zadanie
CREATE TABLE `duplicates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- testovacie hodnoty
INSERT INTO `duplicates` (`id`, `value`) VALUES
(1,    1),
(2,    2),
(3,    3),
(4,    2),
(5,    4),
(6,    4),
(7,    5),
(8,    6),
(9,    6),
(10,    2);

-- požadovaný výstup pre správne napísaný SELECT
--   +----+-------+
-- | id | value |
-- +----+-------+
-- |  2 |     2 |
-- |  4 |     2 |
-- |  5 |     4 |
-- |  6 |     4 |
-- |  8 |     6 |
-- |  9 |     6 |
-- | 10 |     2 |
-- +----+-------+

-- ---------------------------------------------------------------------------------------------------------------------
-- RIESENIE
-- ---------------------------------------------------------------------------------------------------------------------

-- Výsledný SELECT - vyberie všetky riadky, kde value sa v celej tabuľke nachádza viac ako raz.
-- Najskôr vyhľadám duplicitné hodnoty cez GROUP BY ich identifikujem a potom vyberiem riadky na
-- ktorých sa nachádza duplicita.
SELECT
    dupl.id,
    dupl.value
FROM
    duplicates AS dupl
INNER JOIN
    (SELECT
        dupval.value
    FROM
        duplicates dupval
    GROUP BY
        dupval.value
    HAVING
        COUNT(*) > 1
    ) AS dupval ON dupval.value = dupl.value;

-- ---------------------------------------------------------------------------------------------------------------------
-- TESTOVANIE
-- ---------------------------------------------------------------------------------------------------------------------

-- Cieľom je overiť fungovanie navrhovaného SELECTu v prípade tabuľky s veľkým počtom riadkov.
-- Pre velké počty riadkov navrhujem nad stĺpcom value definovať index.
-- Vytvorím teda dve tabuľky, kde jedna má nad stĺpcom value index (group by tak bude rýchlejší) a druhá nie.
-- 
-- Pri testovaní rozdielu (tabulka s indexom vs. bez) pri počte záznamov 100 tis. bol rozdiel minimálny
-- (doba trvania skriptu), pri počte záznamov 1 mio bol rozdiel pod 2 sekundy (3.5s vs. 5s).
-- Do oboch tabulike je preto vložených 10 mio riadkov,  s hodnotami value v rozsahu 1-100000000 kde je
-- rozdiel významný. Index má svoju cenu a to miesto na disku, ale pomôže k rýchlosti.

-- Najskôr si pripravím dáta - tu je procedúra pre generovanie zadaného počtu riadkov v rozmedzí hodnôt min, max
-- do tabuľky duplicates a porovnávacej duplicates2 (s indexom).
--
-- Na vstupe je názov tabuľky, do ktorej sa vkladajú hodnody (tableName), počet riadkov ktoré chcem vytvoriť
-- (numRows), minimálna hodnota pre value (minnumber), maximálna hodnota pre value (maxNumber).

DROP PROCEDURE IF EXISTS RandNum;
DELIMITER $$
CREATE PROCEDURE RandNum(IN table_name VARCHAR(255), IN num_rows INT, IN min_number INT, IN max_number INT)
BEGIN
    DECLARE i INT;
    SET i = 1;
    START TRANSACTION;
    WHILE i <= num_rows DO
        IF table_name = 'duplicates' THEN
            INSERT INTO duplicates (`value`) VALUES (RAND() * (max_number - min_number) + min_number);
        ELSEIF table_name = 'duplicates2' THEN
            INSERT INTO duplicates2 (`value`) VALUES (RAND() * (max_number - min_number) + min_number);
        END IF;
        SET i = i + 1;
    END WHILE;
    COMMIT;
END$$
DELIMITER ;

-- testovanie
-- do oboch tabuliek vložím 10 mio riadkov

DROP TABLE IF EXISTS duplicates;
DROP TABLE IF EXISTS duplicates2;

CREATE TABLE `duplicates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `duplicates2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `value_idx` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- vkladanie do duplictes2 (table s indexom) je podstatne pomalšie kvôli vytváraniu indexu

CALL RandNum('duplicates', 10000000, 1, 100000000);
CALL RandNum('duplicates2', 10000000, 1, 100000000);

-- o niečo rýchlejšia varianta pre vkladanie hodnôt do duplicates2 je dropnúť index, vložiť náhodné hodnoty a znova
-- ho vytvoriť (zaujímavé to je ak je v tabuľke viac ako niekoľko miliónov záznamov, pri 100 tis. rozdiel 
-- podstatne menší)

TRUNCATE TABLE duplicates2;
ALTER TABLE duplicates2 DROP INDEX `value_idx`;
CALL RandNum('duplicates2', 10000000, 1, 100000000);
ALTER TABLE duplicates2 ADD INDEX `value_idx` (`value`);

-- teraz porovnanie queries
-- cca 45-50 sekúnd pre tabuľku duplicates
-- log: [2021-09-28 09:00:45] 500 rows retrieved starting from 1 in 47 s 777 ms (execution: 47 s 755 ms, fetching: 22 ms)
SELECT
    dupl.id,
    dupl.value
FROM
    duplicates AS dupl
INNER JOIN
    (SELECT
         dupval.value
    FROM
        duplicates dupval
    GROUP BY
        dupval.value
    HAVING
        COUNT(*) > 1
    ) AS dupval ON dupval.value = dupl.value;

-- cca 0.03 - 0.1 sekundy
-- log: [2021-09-28 09:01:05] 500 rows retrieved starting from 1 in 166 ms (execution: 14 ms, fetching: 152 ms)
SELECT
    dupl.id,
    dupl.value
FROM
    duplicates2 AS dupl
INNER JOIN
    (SELECT
        dupval.value
    FROM
        duplicates2 dupval
    GROUP BY
        dupval.value
    HAVING
        COUNT(*) > 1
    ) AS dupval ON dupval.value = dupl.value;

-- Pri malom počte riadkov  je rozdiel pri výbere duplicít medzi tabuľkou bez indexu nad stĺpcom
-- s duplicitami a tabuľkou  s indexom nad týmto stĺpcom malý. Z rastúcim počtom záznamov index pomože
-- zásadne zvýšiť rýchlosť výberu údajov. Použitie indexu v testovacom prípade stálo cca 150MB priestoru
-- na disku, ale skráti výber údajo niekoľko tisíc násobne.
