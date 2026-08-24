-- Generated from contrib/util/language_translations/contracts/database-upgrade.json.
-- Contract: database-upgrade-neutral-v1
-- SHA256: 92546f143071e53e4721a07c2502ffa0e4a91f8402ea54a016d372a8e78ca62a
-- Do not edit this generated file directly.

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
SELECT @openemr_translation_contract_cons_id, 19, 'Actualização da base de dados %s' FROM DUAL
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
SELECT @openemr_translation_contract_cons_id, 21, 'Actualização da base de dados %s' FROM DUAL
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
