DROP VIEW IF EXISTS `atendidos_com_alerta`;

CREATE VIEW `atendidos_com_alerta` AS
SELECT
    `a`.`idatendido` AS `idatendido`,
    `a`.`nome` AS `nome`,
    `a`.`cpf` AS `cpf`,
    COUNT(CASE WHEN `fd`.`status` = 'F' THEN 1 END) AS `total_faltas`,
    MAX(CASE WHEN `fd`.`status` = 'F' THEN `fd`.`data` END) AS `ultima_falta`,
    CASE
        WHEN COUNT(CASE WHEN `fd`.`status` = 'F' THEN 1 END) >= 3 THEN 'CRÍTICO'
        WHEN COUNT(CASE WHEN `fd`.`status` = 'F' THEN 1 END) = 2 THEN 'ALERTA'
        ELSE 'NORMAL'
    END AS `nivel_alerta`
FROM `atendido` `a`
LEFT JOIN `frequencia_dia` `fd`
    ON `a`.`idatendido` = `fd`.`id_atendido`
WHERE `a`.`status` = 'Ativo'
    AND NOT EXISTS (
        SELECT 1
        FROM `desligamento` `d`
        WHERE `d`.`id_atendido` = `a`.`idatendido`
        LIMIT 1
    )
GROUP BY `a`.`idatendido`, `a`.`nome`, `a`.`cpf`
HAVING COUNT(CASE WHEN `fd`.`status` = 'F' THEN 1 END) >= 2;
