-- Generated from contrib/util/language_translations/contracts/.
-- Contracts: 28
-- Do not edit this generated file directly.

-- Contract: about-product-neutral-v1
-- SHA256: 48b3ded633634fcfa7e29b25bb6780882aa93ba0187abf5785b5e74bbcc5fab7

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
-- SHA256: 0fcb24513ec81de212d351af9ed2ae124402f1750758859c65a53bb4496a8b1a

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
      AND LOCATE('OpenEMR', `d`.`definition`) > 0
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: api-portal-rest-help-neutral-v1
-- SHA256: 86c297f0e59ea6158611dcacaf0c29606168dce83d035cf964ab0444ee82a88b

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
      AND LOCATE('OpenEMR', `d`.`definition`) > 0
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: api-portal-rest-toggle-neutral-v1
-- SHA256: 6ba0265098f4fa1cf29c2f98df2dcb54b82deba484a13d479047e2b88b135529

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
      AND LOCATE('OpenEMR', `d`.`definition`) > 0
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: api-standard-fhir-help-neutral-v1
-- SHA256: 54fa18196b0efe34720e76bacccfeb1fba122c5415f74d80205f55b9a5cb851b

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
      AND LOCATE('OpenEMR', `d`.`definition`) > 0
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: api-standard-fhir-toggle-neutral-v1
-- SHA256: 4ec4644a5664d0faa33d4d9746a012db2c98fe28a0ce3d7ef867d1056abe21b4

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
      AND LOCATE('OpenEMR', `d`.`definition`) > 0
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: api-standard-rest-help-neutral-v1
-- SHA256: f6391dbafb2e3ea05ef3d36200e575c49d60a8abe5eb2bf86923b2c603705b68

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
      AND LOCATE('OpenEMR', `d`.`definition`) > 0
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: api-standard-rest-toggle-neutral-v1
-- SHA256: 206139699ec9d841682aae9825813cf37c200a7bc3f507dedacd8a601786dcbf

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
      AND LOCATE('OpenEMR', `d`.`definition`) > 0
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: backup-dump-database-neutral-v1
-- SHA256: 2129b56e66305684acb67bcccdf14572caf43798c049c5dc36ef51d1f7692a69

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
      AND LOCATE('OpenEMR', `d`.`definition`) > 0
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: backup-dump-error-neutral-v1
-- SHA256: 526c2601d2b6b5950a3c9cd970921e013b05a77822cae2c6b656df698475a06e

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
      AND LOCATE('OpenEMR', `d`.`definition`) > 0
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: backup-dump-webtree-neutral-v1
-- SHA256: 301f099e77163302351d2be93a99d3942a47e31bb0b203f39ec2cd7673a8e1d7

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
      AND LOCATE('OpenEMR', `d`.`definition`) > 0
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: database-upgrade-neutral-v1
-- SHA256: d3dae438a4e6c14754525101bf20149825177df63b2b0c1557e6d3155f7f3496

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
-- SHA256: e354cae7a200e00d329ca77c1a1f93b317b5a10386d661ee6bc65c6d4506850c

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
      AND LOCATE('OpenEMR', `d`.`definition`) > 0
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: formdata-escaping-error-neutral-v1
-- SHA256: c4c18162d4e1dc95deea1963a5659b35b203890b1b9945feafc4c2db554243ba

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
      AND LOCATE('OpenEMR', `d`.`definition`) > 0
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: installer-users-label-neutral-v1
-- SHA256: 11153194801c1094b3298ddb0580cc8342d0f33b230f00e2cbcfbae92d8eb560

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
      AND LOCATE('OpenEMR', `d`.`definition`) > 0
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: insurance-companies-neutral-v1
-- SHA256: aca4e84467af808cc4093fe2a4743cd3df15b5441f6030946838d157809c0838

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
-- SHA256: f991645de818e9977670a214a8865c96294c498f7e3c313b678f70adcfc3d0c6

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
      AND LOCATE('OpenEMR', `d`.`definition`) > 0
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: letter-write-privileges-neutral-v1
-- SHA256: 0d0af765efc43c95f50821f38e7e4eacc936de41816bce9fbb169d20cd06dfec

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
      AND LOCATE('OpenEMR', `d`.`definition`) > 0
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: list-application-category-neutral-v1
-- SHA256: 3707d073dc54ccb1248f387a77fc8904d263c3e9c0dfdbe2acb67f68de7b9f33

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
      AND LOCATE('OpenEMR', `d`.`definition`) > 0
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: login-requires-javascript-neutral-v1
-- SHA256: 2520bff5cb378441133aabce173391cc5c27235733d934202744b11c5f77fc5a

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
      AND LOCATE('OpenEMR', `d`.`definition`) > 0
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: mfa-register-device-neutral-v1
-- SHA256: 116808d026b68bd01af37160c712d20690c25f64f71158a28d0e764d67e95e11

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
      AND LOCATE('OpenEMR', `d`.`definition`) > 0
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: oauth-authorization-neutral-v1
-- SHA256: 272ea782d42fcc0b109937f9715535a2e3450dd5c231552ee78fc8df2dab5479

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
-- SHA256: 8a57396a8c180e2f7c759032c0a0f1d84867cf48ca805f30fdedaabc705616a5

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
-- SHA256: 3f5336356d388a6e2d934135a4213ba8a4c705498634e4a997a57e493493ee4e

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
-- SHA256: 0452b96910f8f44c3af9cedeb1c1c03747032c07be6c49d1603038d4140e2e80

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
      AND LOCATE('OpenEMR', `d`.`definition`) > 0
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: sphere-void-credit-password-neutral-v1
-- SHA256: a809b4cc6967237df47008052d5b87d62847ce9ddc0650a9c7aadcacfa74f755

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
      AND LOCATE('OpenEMR', `d`.`definition`) > 0
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: zend-application-neutral-v1
-- SHA256: a98fdb9e4f469013c24a3de879310fe22165666fdff794a799fed0e793d6a81a

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
      AND LOCATE('OpenEMR', `d`.`definition`) > 0
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;

-- Contract: zend-welcome-neutral-v1
-- SHA256: e9e5a3a7ef79d82a36375985b4715333cc100e6cec1cf8c8add439b1d8f62197

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
      AND LOCATE('OpenEMR', `d`.`definition`) > 0
      AND LOCATE('%', `d`.`definition`) = 0
) AS `src`
LEFT JOIN `lang_definitions` `existing`
    ON `existing`.`cons_id` = @openemr_translation_contract_cons_id
    AND `existing`.`lang_id` = `src`.`lang_id`
WHERE @openemr_translation_contract_cons_id IS NOT NULL
  AND `existing`.`def_id` IS NULL;

SET @openemr_translation_contract_cons_id = NULL;
