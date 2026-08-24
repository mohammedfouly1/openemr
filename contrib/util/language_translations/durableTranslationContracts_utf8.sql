-- Generated from contrib/util/language_translations/contracts/.
-- Contracts: 8
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

-- Contract: zend-application-neutral-v1
-- SHA256: 73f8a1d0aa416db3671035440233e8459d928bb06c5c33a88094daae86b23c87

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
-- SHA256: 6ea1a4b40a5c567f08b1eeb0e58e383c708d9ee6792f3cb1f810f0c52d42282b

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
