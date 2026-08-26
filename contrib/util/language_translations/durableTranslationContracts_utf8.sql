-- Generated from contrib/util/language_translations/contracts/.
-- Contracts: 32
-- Do not edit this generated file directly.

-- Contract: about-product-neutral-v1
-- SHA256: 5b4785869cc6f8adb98c0d63451e02c24b67586ac47fa040e74c468167507b33

INSERT INTO `lang_constants` (`constant_name`)
SELECT 'About %s' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = 'About %s');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = 'About %s'
);

-- Carry forward: About (suffix)
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, CONCAT(`src`.`definition`, ' %s')
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'About'
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: api-fhir-system-scopes-neutral-v1
-- SHA256: e4af7bb3c973584b024fddb42f270981b69068a48366895678d0e811dc9828d7

INSERT INTO `lang_constants` (`constant_name`)
SELECT 'Enable %s FHIR System Scopes.' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = 'Enable %s FHIR System Scopes.');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = 'Enable %s FHIR System Scopes.'
);

-- Carry forward: Enable OpenEMR FHIR System Scopes. (neutralise "OpenEMR")
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, REPLACE(`src`.`definition`, 'OpenEMR', '%s')
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'Enable OpenEMR FHIR System Scopes.'
      AND CHAR_LENGTH(`d`.`definition`) - CHAR_LENGTH(REPLACE(`d`.`definition`, 'OpenEMR', '')) = CHAR_LENGTH('OpenEMR')
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: api-portal-rest-help-neutral-v1
-- SHA256: a6bfbf3bf1ce4c99b0ec2afed034537a50502cc510d9ce621219ba3f298db891

INSERT INTO `lang_constants` (`constant_name`)
SELECT 'Enable %s Patient Portal RESTful API.' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = 'Enable %s Patient Portal RESTful API.');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = 'Enable %s Patient Portal RESTful API.'
);

-- Carry forward: Enable OpenEMR Patient Portal RESTful API. (neutralise "OpenEMR")
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, REPLACE(`src`.`definition`, 'OpenEMR', '%s')
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'Enable OpenEMR Patient Portal RESTful API.'
      AND CHAR_LENGTH(`d`.`definition`) - CHAR_LENGTH(REPLACE(`d`.`definition`, 'OpenEMR', '')) = CHAR_LENGTH('OpenEMR')
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: api-portal-rest-toggle-neutral-v1
-- SHA256: ef33e4028899f6c0a634f4d7ece7349746079201d83e344483452e2b8dc9a00c

INSERT INTO `lang_constants` (`constant_name`)
SELECT 'Enable %s Patient Portal REST API (EXPERIMENTAL)' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = 'Enable %s Patient Portal REST API (EXPERIMENTAL)');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = 'Enable %s Patient Portal REST API (EXPERIMENTAL)'
);

-- Carry forward: Enable OpenEMR Patient Portal REST API (EXPERIMENTAL) (neutralise "OpenEMR")
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, REPLACE(`src`.`definition`, 'OpenEMR', '%s')
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'Enable OpenEMR Patient Portal REST API (EXPERIMENTAL)'
      AND CHAR_LENGTH(`d`.`definition`) - CHAR_LENGTH(REPLACE(`d`.`definition`, 'OpenEMR', '')) = CHAR_LENGTH('OpenEMR')
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: api-standard-fhir-help-neutral-v1
-- SHA256: 8061e8a1f67306098c6e8f8625487cf74dc7f2b1a50100271edb8778e1b57feb

INSERT INTO `lang_constants` (`constant_name`)
SELECT 'Enable %s Standard FHIR RESTful API.' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = 'Enable %s Standard FHIR RESTful API.');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = 'Enable %s Standard FHIR RESTful API.'
);

-- Carry forward: Enable OpenEMR Standard FHIR RESTful API. (neutralise "OpenEMR")
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, REPLACE(`src`.`definition`, 'OpenEMR', '%s')
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'Enable OpenEMR Standard FHIR RESTful API.'
      AND CHAR_LENGTH(`d`.`definition`) - CHAR_LENGTH(REPLACE(`d`.`definition`, 'OpenEMR', '')) = CHAR_LENGTH('OpenEMR')
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: api-standard-fhir-toggle-neutral-v1
-- SHA256: 20016110f53b5a0b3fa879bc90fa4d4030c4a900ec85a9a75240818bd196c7b6

INSERT INTO `lang_constants` (`constant_name`)
SELECT 'Enable %s Standard FHIR REST API' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = 'Enable %s Standard FHIR REST API');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = 'Enable %s Standard FHIR REST API'
);

-- Carry forward: Enable OpenEMR Standard FHIR REST API (neutralise "OpenEMR")
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, REPLACE(`src`.`definition`, 'OpenEMR', '%s')
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'Enable OpenEMR Standard FHIR REST API'
      AND CHAR_LENGTH(`d`.`definition`) - CHAR_LENGTH(REPLACE(`d`.`definition`, 'OpenEMR', '')) = CHAR_LENGTH('OpenEMR')
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: api-standard-rest-help-neutral-v1
-- SHA256: 70271bb48b4ea261cf72092a00c638daa3e1f1d372c1c39ad228d691d20bc825

INSERT INTO `lang_constants` (`constant_name`)
SELECT 'Enable %s Standard RESTful API.' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = 'Enable %s Standard RESTful API.');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = 'Enable %s Standard RESTful API.'
);

-- Carry forward: Enable OpenEMR Standard RESTful API. (neutralise "OpenEMR")
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, REPLACE(`src`.`definition`, 'OpenEMR', '%s')
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'Enable OpenEMR Standard RESTful API.'
      AND CHAR_LENGTH(`d`.`definition`) - CHAR_LENGTH(REPLACE(`d`.`definition`, 'OpenEMR', '')) = CHAR_LENGTH('OpenEMR')
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: api-standard-rest-toggle-neutral-v1
-- SHA256: d6d7301c314e4f889f34b1a173232c9006b8967b08437b43f7b2b6d344161163

INSERT INTO `lang_constants` (`constant_name`)
SELECT 'Enable %s Standard REST API' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = 'Enable %s Standard REST API');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = 'Enable %s Standard REST API'
);

-- Carry forward: Enable OpenEMR Standard REST API (neutralise "OpenEMR")
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, REPLACE(`src`.`definition`, 'OpenEMR', '%s')
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'Enable OpenEMR Standard REST API'
      AND CHAR_LENGTH(`d`.`definition`) - CHAR_LENGTH(REPLACE(`d`.`definition`, 'OpenEMR', '')) = CHAR_LENGTH('OpenEMR')
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: backup-dump-database-neutral-v1
-- SHA256: f339001f9c1aae2ea32abf61149d171bee94623a64860d3d5d0cdbea70aeae60

INSERT INTO `lang_constants` (`constant_name`)
SELECT 'Dumping %s database' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = 'Dumping %s database');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = 'Dumping %s database'
);

-- Carry forward: Dumping OpenEMR database (neutralise "OpenEMR")
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, REPLACE(`src`.`definition`, 'OpenEMR', '%s')
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'Dumping OpenEMR database'
      AND CHAR_LENGTH(`d`.`definition`) - CHAR_LENGTH(REPLACE(`d`.`definition`, 'OpenEMR', '')) = CHAR_LENGTH('OpenEMR')
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: backup-dump-error-neutral-v1
-- SHA256: 83f9e95fff94ec8b3419ffe583718bdef2f7dfee81a7f8e2f168ea7ce0341b56

INSERT INTO `lang_constants` (`constant_name`)
SELECT 'An error occurred while dumping %s web directory tree' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = 'An error occurred while dumping %s web directory tree');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = 'An error occurred while dumping %s web directory tree'
);

-- Carry forward: An error occurred while dumping OpenEMR web directory tree (neutralise "OpenEMR")
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, REPLACE(`src`.`definition`, 'OpenEMR', '%s')
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'An error occurred while dumping OpenEMR web directory tree'
      AND CHAR_LENGTH(`d`.`definition`) - CHAR_LENGTH(REPLACE(`d`.`definition`, 'OpenEMR', '')) = CHAR_LENGTH('OpenEMR')
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: backup-dump-webtree-neutral-v1
-- SHA256: 9fb708c60c16e520733983609105b237051dd0cf402641565f9712b263eecd07

INSERT INTO `lang_constants` (`constant_name`)
SELECT 'Dumping %s web directory tree' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = 'Dumping %s web directory tree');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = 'Dumping %s web directory tree'
);

-- Carry forward: Dumping OpenEMR web directory tree (neutralise "OpenEMR")
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, REPLACE(`src`.`definition`, 'OpenEMR', '%s')
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'Dumping OpenEMR web directory tree'
      AND CHAR_LENGTH(`d`.`definition`) - CHAR_LENGTH(REPLACE(`d`.`definition`, 'OpenEMR', '')) = CHAR_LENGTH('OpenEMR')
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: database-upgrade-neutral-v1
-- SHA256: 518b4abddfeef759bc849a1ab1391efec5e5967f9d43ff9ba843771350dabd54

INSERT INTO `lang_constants` (`constant_name`)
SELECT '%s Database Upgrade' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = '%s Database Upgrade');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = '%s Database Upgrade'
);

INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, 3, 'Actualización base de datos %s' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `lang_definitions`
    WHERE `cons_id` = @openemr_translation_contract_cons_id AND `lang_id` = 3
);

INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, 4, 'Actualización base de datos %s' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `lang_definitions`
    WHERE `cons_id` = @openemr_translation_contract_cons_id AND `lang_id` = 4
);

INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, 5, '%s Datenbank Update' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `lang_definitions`
    WHERE `cons_id` = @openemr_translation_contract_cons_id AND `lang_id` = 5
);

INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, 6, '%s DB upgrade' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `lang_definitions`
    WHERE `cons_id` = @openemr_translation_contract_cons_id AND `lang_id` = 6
);

INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, 7, 'שדרוג מסד נתונים של %s' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `lang_definitions`
    WHERE `cons_id` = @openemr_translation_contract_cons_id AND `lang_id` = 7
);

INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, 8, 'Mettre à jour la base de donnée de %s' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `lang_definitions`
    WHERE `cons_id` = @openemr_translation_contract_cons_id AND `lang_id` = 8
);

INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, 11, '%s数据库升级' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `lang_definitions`
    WHERE `cons_id` = @openemr_translation_contract_cons_id AND `lang_id` = 11
);

INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, 12, '%s數據庫升級' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `lang_definitions`
    WHERE `cons_id` = @openemr_translation_contract_cons_id AND `lang_id` = 12
);

INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, 13, 'Модернизация базы данных %s' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `lang_definitions`
    WHERE `cons_id` = @openemr_translation_contract_cons_id AND `lang_id` = 13
);

INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, 14, '%s տվյալների բազայի թարմացում' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `lang_definitions`
    WHERE `cons_id` = @openemr_translation_contract_cons_id AND `lang_id` = 14
);

INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, 16, 'Upgrade Database %s' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `lang_definitions`
    WHERE `cons_id` = @openemr_translation_contract_cons_id AND `lang_id` = 16
);

INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, 17, 'Ενημέρωση βάσης δεδομένων του %s' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `lang_definitions`
    WHERE `cons_id` = @openemr_translation_contract_cons_id AND `lang_id` = 17
);

INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, 19, '%s Base de Dados Actualizada' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `lang_definitions`
    WHERE `cons_id` = @openemr_translation_contract_cons_id AND `lang_id` = 19
);

INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, 20, 'Atualização do banco de dados %s' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `lang_definitions`
    WHERE `cons_id` = @openemr_translation_contract_cons_id AND `lang_id` = 20
);

INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, 21, '%s Base de Dados Actualizada' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `lang_definitions`
    WHERE `cons_id` = @openemr_translation_contract_cons_id AND `lang_id` = 21
);

INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, 22, 'ترقية قاعدة بيانات %s' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `lang_definitions`
    WHERE `cons_id` = @openemr_translation_contract_cons_id AND `lang_id` = 22
);

INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, 24, '%s Veri Tabanı Güncellemesi' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `lang_definitions`
    WHERE `cons_id` = @openemr_translation_contract_cons_id AND `lang_id` = 24
);

INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, 27, 'Aggiornamento Database di %s' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `lang_definitions`
    WHERE `cons_id` = @openemr_translation_contract_cons_id AND `lang_id` = 27
);

INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, 28, '%s डेटाबेस नवीनीकरण' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `lang_definitions`
    WHERE `cons_id` = @openemr_translation_contract_cons_id AND `lang_id` = 28
);

INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, 29, 'Actualizarea bazei de date %s' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `lang_definitions`
    WHERE `cons_id` = @openemr_translation_contract_cons_id AND `lang_id` = 29
);

INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, 30, '%s Cơ sở dữ liệu Nâng cấp' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `lang_definitions`
    WHERE `cons_id` = @openemr_translation_contract_cons_id AND `lang_id` = 30
);

INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, 33, 'Upgrade databáze %s' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `lang_definitions`
    WHERE `cons_id` = @openemr_translation_contract_cons_id AND `lang_id` = 33
);

INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, 34, 'Оновлення бази даних %s' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `lang_definitions`
    WHERE `cons_id` = @openemr_translation_contract_cons_id AND `lang_id` = 34
);

INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, 37, 'ارتقاء پایگاه داده %s' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `lang_definitions`
    WHERE `cons_id` = @openemr_translation_contract_cons_id AND `lang_id` = 37
);

INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, 40, '%sデータベースアップグレード' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `lang_definitions`
    WHERE `cons_id` = @openemr_translation_contract_cons_id AND `lang_id` = 40
);

INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, 47, '%s डेटाबेस अपग्रेड' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `lang_definitions`
    WHERE `cons_id` = @openemr_translation_contract_cons_id AND `lang_id` = 47
);

INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, 50, '%s डेटाबेस नवीनीकरण' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `lang_definitions`
    WHERE `cons_id` = @openemr_translation_contract_cons_id AND `lang_id` = 50
);

INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, 59, '%s dummy' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `lang_definitions`
    WHERE `cons_id` = @openemr_translation_contract_cons_id AND `lang_id` = 59
);

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: dataloads-unrecognised-file-neutral-v1
-- SHA256: cb1bc75394e5771a3a6cdaae25b5f4fbe55200733296fdabf18e243934550410

INSERT INTO `lang_constants` (`constant_name`)
SELECT '%s does not recognize the incoming file in the contrib directory. This is most likely because you need to configure the release in the supported_external_dataloads table in the MySQL database.' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = '%s does not recognize the incoming file in the contrib directory. This is most likely because you need to configure the release in the supported_external_dataloads table in the MySQL database.');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = '%s does not recognize the incoming file in the contrib directory. This is most likely because you need to configure the release in the supported_external_dataloads table in the MySQL database.'
);

-- Carry forward: OpenEMR does not recognize the incoming file in the contrib directory. This is most likely because you need to configure the release in the supported_external_dataloads table in the MySQL database. (neutralise "OpenEMR")
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, REPLACE(`src`.`definition`, 'OpenEMR', '%s')
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'OpenEMR does not recognize the incoming file in the contrib directory. This is most likely because you need to configure the release in the supported_external_dataloads table in the MySQL database.'
      AND CHAR_LENGTH(`d`.`definition`) - CHAR_LENGTH(REPLACE(`d`.`definition`, 'OpenEMR', '')) = CHAR_LENGTH('OpenEMR')
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: error-400-neutral-v1
-- SHA256: eccb4f65d6ca08ec29139ccabcb4f6e367e4aff1bffb56ae1d68d1b9f98f10d2

INSERT INTO `lang_constants` (`constant_name`)
SELECT '%s 400 Error' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = '%s 400 Error');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = '%s 400 Error'
);

-- Carry forward: Thiqa 400 Error (neutralise "Thiqa")
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, REPLACE(`src`.`definition`, 'Thiqa', '%s')
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'Thiqa 400 Error'
      AND CHAR_LENGTH(`d`.`definition`) - CHAR_LENGTH(REPLACE(`d`.`definition`, 'Thiqa', '')) = CHAR_LENGTH('Thiqa')
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: error-404-neutral-v1
-- SHA256: a01cdff783d1e2ba9eb962815b3ee94c79c89a4458a29db468f8c96e0f30b119

INSERT INTO `lang_constants` (`constant_name`)
SELECT '%s 404 Error' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = '%s 404 Error');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = '%s 404 Error'
);

-- Carry forward: Thiqa 404 Error (neutralise "Thiqa")
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, REPLACE(`src`.`definition`, 'Thiqa', '%s')
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'Thiqa 404 Error'
      AND CHAR_LENGTH(`d`.`definition`) - CHAR_LENGTH(REPLACE(`d`.`definition`, 'Thiqa', '')) = CHAR_LENGTH('Thiqa')
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: formdata-escaping-error-neutral-v1
-- SHA256: bff6dea750e29828e7194d9db802e5a5b81e8bed2d1ed100e5fc329e06ee0c3b

INSERT INTO `lang_constants` (`constant_name`)
SELECT 'There was an %s SQL Escaping ERROR of the following string' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = 'There was an %s SQL Escaping ERROR of the following string');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = 'There was an %s SQL Escaping ERROR of the following string'
);

-- Carry forward: There was an OpenEMR SQL Escaping ERROR of the following string (neutralise "OpenEMR")
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, REPLACE(`src`.`definition`, 'OpenEMR', '%s')
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'There was an OpenEMR SQL Escaping ERROR of the following string'
      AND CHAR_LENGTH(`d`.`definition`) - CHAR_LENGTH(REPLACE(`d`.`definition`, 'OpenEMR', '')) = CHAR_LENGTH('OpenEMR')
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: general-http-error-neutral-v1
-- SHA256: 89d9a12105e7792495f71f8764eb8b476b221b1d348b6e28e4537ec90cc6365f

INSERT INTO `lang_constants` (`constant_name`)
SELECT '%s Error' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = '%s Error');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = '%s Error'
);

-- Carry forward: Error (prefix)
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, CONCAT('%s ', `src`.`definition`)
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'Error'
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

-- Carry forward: Thiqa Error (neutralise "Thiqa")
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, REPLACE(`src`.`definition`, 'Thiqa', '%s')
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'Thiqa Error'
      AND CHAR_LENGTH(`d`.`definition`) - CHAR_LENGTH(REPLACE(`d`.`definition`, 'Thiqa', '')) = CHAR_LENGTH('Thiqa')
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: installer-third-party-modules-neutral-v1
-- SHA256: d8e368f00739ab9aa1e9611b0d99d22e9c9b5aa05c456ff59a960384ff7c43b2

INSERT INTO `lang_constants` (`constant_name`)
SELECT 'Visit additional modules for %s developed and listed by third party vendors.' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = 'Visit additional modules for %s developed and listed by third party vendors.');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = 'Visit additional modules for %s developed and listed by third party vendors.'
);

-- Carry forward: Visit additional modules for OpenEMR developed and listed by third party vendors. (neutralise "OpenEMR")
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, REPLACE(`src`.`definition`, 'OpenEMR', '%s')
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'Visit additional modules for OpenEMR developed and listed by third party vendors.'
      AND CHAR_LENGTH(`d`.`definition`) - CHAR_LENGTH(REPLACE(`d`.`definition`, 'OpenEMR', '')) = CHAR_LENGTH('OpenEMR')
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

-- Carry forward: Visit additional modules for Thiqa developed and listed by third party vendors. (neutralise "Thiqa")
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, REPLACE(`src`.`definition`, 'Thiqa', '%s')
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'Visit additional modules for Thiqa developed and listed by third party vendors.'
      AND CHAR_LENGTH(`d`.`definition`) - CHAR_LENGTH(REPLACE(`d`.`definition`, 'Thiqa', '')) = CHAR_LENGTH('Thiqa')
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: installer-users-label-neutral-v1
-- SHA256: ebed562b4082202a7c3e778be837396f42de0f386f7e56d8d90216b724de0417

INSERT INTO `lang_constants` (`constant_name`)
SELECT '%s Users' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = '%s Users');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = '%s Users'
);

-- Carry forward: OpenEMR Users (neutralise "OpenEMR")
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, REPLACE(`src`.`definition`, 'OpenEMR', '%s')
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'OpenEMR Users'
      AND CHAR_LENGTH(`d`.`definition`) - CHAR_LENGTH(REPLACE(`d`.`definition`, 'OpenEMR', '')) = CHAR_LENGTH('OpenEMR')
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: insurance-companies-neutral-v1
-- SHA256: 734be7237475c624f03c293c77c4d08309c3b77be23810e1ab66a4f0e26bda3b

INSERT INTO `lang_constants` (`constant_name`)
SELECT 'Insurance Companies %s' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = 'Insurance Companies %s');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = 'Insurance Companies %s'
);

-- Carry forward: Insurance Companies (suffix)
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, CONCAT(`src`.`definition`, ' %s')
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'Insurance Companies'
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: ldap-login-embed-neutral-v1
-- SHA256: 12cab681d2be378a8031dfd4f1369512fab4bc8a8c4ac07a925b05d52fda8f8f

INSERT INTO `lang_constants` (`constant_name`)
SELECT 'Embed {login} where the %s login name of the user is to be; for example: uid={login},dc=example,dc=com' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = 'Embed {login} where the %s login name of the user is to be; for example: uid={login},dc=example,dc=com');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = 'Embed {login} where the %s login name of the user is to be; for example: uid={login},dc=example,dc=com'
);

-- Carry forward: Embed {login} where the OpenEMR login name of the user is to be; for example: uid={login},dc=example,dc=com (neutralise "OpenEMR")
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, REPLACE(`src`.`definition`, 'OpenEMR', '%s')
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'Embed {login} where the OpenEMR login name of the user is to be; for example: uid={login},dc=example,dc=com'
      AND CHAR_LENGTH(`d`.`definition`) - CHAR_LENGTH(REPLACE(`d`.`definition`, 'OpenEMR', '')) = CHAR_LENGTH('OpenEMR')
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: letter-write-privileges-neutral-v1
-- SHA256: 6075a18326feddb5f021c1b050703e9d7f59cf68698f18b1f578d47ebd06d10f

INSERT INTO `lang_constants` (`constant_name`)
SELECT 'Ensure %s has write privileges to directory' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = 'Ensure %s has write privileges to directory');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = 'Ensure %s has write privileges to directory'
);

-- Carry forward: Ensure OpenEMR has write privileges to directory (neutralise "OpenEMR")
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, REPLACE(`src`.`definition`, 'OpenEMR', '%s')
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'Ensure OpenEMR has write privileges to directory'
      AND CHAR_LENGTH(`d`.`definition`) - CHAR_LENGTH(REPLACE(`d`.`definition`, 'OpenEMR', '')) = CHAR_LENGTH('OpenEMR')
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: list-application-category-neutral-v1
-- SHA256: 10b8b627f89bf1e59a726f4c61843603c89ad5c61568bbe5f8298a2ebd376de6

INSERT INTO `lang_constants` (`constant_name`)
SELECT '%s Application Category' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = '%s Application Category');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = '%s Application Category'
);

-- Carry forward: OpenEMR Application Category (neutralise "OpenEMR")
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, REPLACE(`src`.`definition`, 'OpenEMR', '%s')
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'OpenEMR Application Category'
      AND CHAR_LENGTH(`d`.`definition`) - CHAR_LENGTH(REPLACE(`d`.`definition`, 'OpenEMR', '')) = CHAR_LENGTH('OpenEMR')
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: login-requires-javascript-neutral-v1
-- SHA256: 410f32529db746236f7355b664b2bcf9608ea1df3043d5921fe1a3e4e7226a3e

INSERT INTO `lang_constants` (`constant_name`)
SELECT '%s requires Javascript to perform user authentication.' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = '%s requires Javascript to perform user authentication.');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = '%s requires Javascript to perform user authentication.'
);

-- Carry forward: OpenEMR requires Javascript to perform user authentication. (neutralise "OpenEMR")
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, REPLACE(`src`.`definition`, 'OpenEMR', '%s')
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'OpenEMR requires Javascript to perform user authentication.'
      AND CHAR_LENGTH(`d`.`definition`) - CHAR_LENGTH(REPLACE(`d`.`definition`, 'OpenEMR', '')) = CHAR_LENGTH('OpenEMR')
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: mfa-register-device-neutral-v1
-- SHA256: 996ea79ec53dc1bea41777cf5e22713f384e48f83e821d0211050f9c79166b0f

INSERT INTO `lang_constants` (`constant_name`)
SELECT 'In order to register your device, please provide your %s login password' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = 'In order to register your device, please provide your %s login password');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = 'In order to register your device, please provide your %s login password'
);

-- Carry forward: In order to register your device, please provide your OpenEMR login password (neutralise "OpenEMR")
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, REPLACE(`src`.`definition`, 'OpenEMR', '%s')
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'In order to register your device, please provide your OpenEMR login password'
      AND CHAR_LENGTH(`d`.`definition`) - CHAR_LENGTH(REPLACE(`d`.`definition`, 'OpenEMR', '')) = CHAR_LENGTH('OpenEMR')
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: oauth-authorization-neutral-v1
-- SHA256: cbb10428f4d95a43c071617e5d44d565959086e10d66c4dde8fcf9f31f0f501c

INSERT INTO `lang_constants` (`constant_name`)
SELECT '%s Authorization' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = '%s Authorization');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = '%s Authorization'
);

-- Carry forward: Authorization (prefix)
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, CONCAT('%s ', `src`.`definition`)
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'Authorization'
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: oauth-login-neutral-v1
-- SHA256: bbbcf088c7d5314c45bc9bf8e2d22a7c0ecc299cae0b0e1e31e43509d06c6853

INSERT INTO `lang_constants` (`constant_name`)
SELECT '%s Login' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = '%s Login');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = '%s Login'
);

-- Carry forward: Login (prefix)
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, CONCAT('%s ', `src`.`definition`)
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'Login'
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: product-registration-neutral-v1
-- SHA256: 9badc31646cdfd63362c4bc206e0f69876135cc33fdbb54cc148384f656cfc38

INSERT INTO `lang_constants` (`constant_name`)
SELECT '%s Product Registration' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = '%s Product Registration');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = '%s Product Registration'
);

-- Carry forward: Product Registration (prefix)
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, CONCAT('%s ', `src`.`definition`)
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'Product Registration'
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: smart-app-registration-neutral-v1
-- SHA256: 867ae4eddfe6b41587ded78f268299fa42d2b397e1eba3cdff40fc4a6927d582

INSERT INTO `lang_constants` (`constant_name`)
SELECT '%s App Registration' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = '%s App Registration');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = '%s App Registration'
);

-- Carry forward: OpenEMR App Registration (neutralise "OpenEMR")
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, REPLACE(`src`.`definition`, 'OpenEMR', '%s')
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'OpenEMR App Registration'
      AND CHAR_LENGTH(`d`.`definition`) - CHAR_LENGTH(REPLACE(`d`.`definition`, 'OpenEMR', '')) = CHAR_LENGTH('OpenEMR')
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: sphere-void-credit-password-neutral-v1
-- SHA256: 3df6577553b3b95eb99654ff3cd6f02d9855a086d44d61c9930570140d0ea775

INSERT INTO `lang_constants` (`constant_name`)
SELECT 'Sphere Void/Credit Confirmation Password. %s confirms pin/password before proceeding with void/credit.' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = 'Sphere Void/Credit Confirmation Password. %s confirms pin/password before proceeding with void/credit.');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = 'Sphere Void/Credit Confirmation Password. %s confirms pin/password before proceeding with void/credit.'
);

-- Carry forward: Sphere Void/Credit Confirmation Password. OpenEMR confirms pin/password before proceeding with void/credit. (neutralise "OpenEMR")
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, REPLACE(`src`.`definition`, 'OpenEMR', '%s')
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'Sphere Void/Credit Confirmation Password. OpenEMR confirms pin/password before proceeding with void/credit.'
      AND CHAR_LENGTH(`d`.`definition`) - CHAR_LENGTH(REPLACE(`d`.`definition`, 'OpenEMR', '')) = CHAR_LENGTH('OpenEMR')
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: zend-application-neutral-v1
-- SHA256: c8779a4bf36311792ecd1473110727fa408ca2284201efb53e2422b2a37ddd27

INSERT INTO `lang_constants` (`constant_name`)
SELECT '%s Application' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = '%s Application');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = '%s Application'
);

-- Carry forward: OpenEMR Application (neutralise "OpenEMR")
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, REPLACE(`src`.`definition`, 'OpenEMR', '%s')
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'OpenEMR Application'
      AND CHAR_LENGTH(`d`.`definition`) - CHAR_LENGTH(REPLACE(`d`.`definition`, 'OpenEMR', '')) = CHAR_LENGTH('OpenEMR')
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: zend-welcome-neutral-v1
-- SHA256: bb9f763bd5fe21c68fa3468b9a7804de90d4f5d420aa8228e6f7dc903ce2961f

INSERT INTO `lang_constants` (`constant_name`)
SELECT 'Welcome to %s' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lang_constants` WHERE BINARY `constant_name` = 'Welcome to %s');

SET @openemr_translation_contract_cons_id = (
    SELECT `cons_id` FROM `lang_constants`
    WHERE BINARY `constant_name` = 'Welcome to %s'
);

-- Carry forward: Welcome to OpenEMR (neutralise "OpenEMR")
INSERT INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT @openemr_translation_contract_cons_id, `src`.`lang_id`, REPLACE(`src`.`definition`, 'OpenEMR', '%s')
FROM (
    SELECT `d`.`lang_id` AS `lang_id`, `d`.`definition` AS `definition`
    FROM `lang_definitions` `d`
    INNER JOIN `lang_constants` `c` ON `c`.`cons_id` = `d`.`cons_id`
    WHERE BINARY `c`.`constant_name` = 'Welcome to OpenEMR'
      AND CHAR_LENGTH(`d`.`definition`) - CHAR_LENGTH(REPLACE(`d`.`definition`, 'OpenEMR', '')) = CHAR_LENGTH('OpenEMR')
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;
