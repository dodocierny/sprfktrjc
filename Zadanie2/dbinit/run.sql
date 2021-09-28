-- table without index on value
CREATE TABLE IF NOT EXISTS `duplicates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- table with index on value (will by added later)
CREATE TABLE IF NOT EXISTS  `duplicates2` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `value` int(11) NOT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- procedure for generating test data
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

-- fill duplicates table
CALL RandNum('duplicates', 10000000, 1, 100000000);

-- fill duplicates2 table and add index
CALL RandNum('duplicates2', 10000000, 1, 100000000);
ALTER TABLE duplicates2 ADD INDEX `value_idx` (`value`);