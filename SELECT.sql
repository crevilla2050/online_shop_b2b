    SELECT 
        `s`.`name` AS `estado`,
        `m`.`name` AS `municipio`,
        `c`.`name` AS `ciudad`,
        `se`.`name` AS `colonia`,
        `pc`.`code` AS `codigo_postal`
    FROM
        (((((`db_online_shop`.`tbl_catalogo_codigos_postales` `cp`
        JOIN `db_online_shop`.`tbl_estados` `s` ON ((`s`.`id_estado` = `cp`.`state_id`)))
        JOIN `db_online_shop`.`tbl_municipios` `m` ON ((`m`.`id_municipio` = `cp`.`municipality_id`)))
        JOIN `db_online_shop`.`tbl_ciudades` `c` ON ((`c`.`id_ciudad` = `cp`.`city_id`)))
        JOIN `db_online_shop`.`tbl_colonias` `se` ON ((`se`.`id_colonia` = `cp`.`settlement_id`)))
        JOIN `db_online_shop`.`tbl_codigos_postales` `pc` ON ((`pc`.`id_codigo_postal` = `cp`.`postal_code_id`)))