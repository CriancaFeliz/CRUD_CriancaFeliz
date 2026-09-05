-- =====================================================
-- UPGRADE DE PRODUCAO: BASE LEGADA -> APLICACAO ATUAL
-- =====================================================
-- Destino: banco ja selecionado no phpMyAdmin.
-- Esta migration nao recria nem remove tabelas de dados. Ela:
--   1. normaliza nomes de tabelas usados pela aplicacao atual;
--   2. preserva dados de acolhimento na tabela principal de atendidos;
--   3. adiciona somente colunas e objetos que faltam.
--
-- Pre-requisito: exportacao SQL validada antes da execucao.
-- Rollback em caso de falha: restaure o dump completo da exportacao.

SET @cf_database = DATABASE();

DELIMITER $$

DROP PROCEDURE IF EXISTS `_cf_rename_table_if_safe`$$

CREATE PROCEDURE `_cf_rename_table_if_safe`(
    IN p_from_name VARCHAR(64),
    IN p_to_name VARCHAR(64)
)
BEGIN
    DECLARE v_from_count INT DEFAULT 0;
    DECLARE v_to_count INT DEFAULT 0;
    DECLARE v_message VARCHAR(255);

    IF @cf_database IS NULL THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Selecione o banco alvo antes de executar a migration.';
    END IF;

    SELECT COUNT(*) INTO v_from_count
      FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = @cf_database
       AND TABLE_TYPE = 'BASE TABLE'
       AND BINARY TABLE_NAME = BINARY p_from_name;

    SELECT COUNT(*) INTO v_to_count
      FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = @cf_database
       AND TABLE_TYPE = 'BASE TABLE'
       AND BINARY TABLE_NAME = BINARY p_to_name;

    IF v_from_count = 1 AND v_to_count = 0 THEN
        SET @cf_rename_sql = CONCAT(
            'RENAME TABLE `', REPLACE(p_from_name, '`', '``'),
            '` TO `', REPLACE(p_to_name, '`', '``'), '`'
        );
        PREPARE cf_rename_stmt FROM @cf_rename_sql;
        EXECUTE cf_rename_stmt;
        DEALLOCATE PREPARE cf_rename_stmt;
    ELSEIF v_from_count = 1 AND v_to_count = 1 THEN
        SET v_message = CONCAT(
            'Conflito de tabelas: ', p_from_name,
            ' e ', p_to_name, ' coexistem. Nada foi mesclado.'
        );
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_message;
    END IF;
END$$

CALL `_cf_rename_table_if_safe`('Agenda', 'agenda')$$
CALL `_cf_rename_table_if_safe`('Anotacao_Psicologica', 'anotacao_psicologica')$$
CALL `_cf_rename_table_if_safe`('Atendido', 'atendido')$$
CALL `_cf_rename_table_if_safe`('Desligamento', 'desligamento')$$
CALL `_cf_rename_table_if_safe`('Despesas', 'despesas')$$
CALL `_cf_rename_table_if_safe`('Documento', 'documento')$$
CALL `_cf_rename_table_if_safe`('Familia', 'familia')$$
CALL `_cf_rename_table_if_safe`('Ficha_Socioeconomico', 'ficha_socioeconomico')$$
CALL `_cf_rename_table_if_safe`('Frequencia_Dia', 'frequencia_dia')$$
CALL `_cf_rename_table_if_safe`('Frequencia_Oficina', 'frequencia_oficina')$$
CALL `_cf_rename_table_if_safe`('Log', 'log')$$
CALL `_cf_rename_table_if_safe`('Oficina', 'oficina')$$
CALL `_cf_rename_table_if_safe`('Responsavel', 'responsavel')$$
CALL `_cf_rename_table_if_safe`('Usuario', 'usuario')$$

DROP PROCEDURE `_cf_rename_table_if_safe`$$

DELIMITER ;

-- A aplicacao atual consolida os dados de acolhimento em `atendido`.
-- A tabela Ficha_Acolhimento original e mantida como historico.
ALTER TABLE `atendido`
    ADD COLUMN IF NOT EXISTS `telefone` VARCHAR(20) NULL,
    ADD COLUMN IF NOT EXISTS `email` VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS `encaminha_por` VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS `queixa_principal` TEXT NULL,
    ADD COLUMN IF NOT EXISTS `escola` VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS `periodo` VARCHAR(50) NULL,
    ADD COLUMN IF NOT EXISTS `ponto_referencia` VARCHAR(200) NULL,
    ADD COLUMN IF NOT EXISTS `cras` VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS `ubs` VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS `cad_unico` VARCHAR(50) NULL,
    ADD COLUMN IF NOT EXISTS `acolhimento_responsavel` VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS `acolhimento_funcao` VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS `carimbo` TEXT NULL;

UPDATE `atendido` a
INNER JOIN `Ficha_Acolhimento` fa ON fa.id_atendido = a.idatendido
SET
    a.data_acolhimento = COALESCE(a.data_acolhimento, fa.data_acolhimento),
    a.encaminha_por = COALESCE(NULLIF(a.encaminha_por, ''), fa.encaminha_por),
    a.queixa_principal = COALESCE(NULLIF(a.queixa_principal, ''), fa.queixa_principal),
    a.escola = COALESCE(NULLIF(a.escola, ''), fa.escola),
    a.periodo = COALESCE(NULLIF(a.periodo, ''), fa.periodo),
    a.ponto_referencia = COALESCE(NULLIF(a.ponto_referencia, ''), fa.ponto_referencia),
    a.cras = COALESCE(NULLIF(a.cras, ''), fa.cras),
    a.ubs = COALESCE(NULLIF(a.ubs, ''), fa.ubs),
    a.cad_unico = COALESCE(NULLIF(a.cad_unico, ''), fa.cad_unico),
    a.acolhimento_responsavel = COALESCE(NULLIF(a.acolhimento_responsavel, ''), fa.acolhimento_responsavel),
    a.acolhimento_funcao = COALESCE(NULLIF(a.acolhimento_funcao, ''), fa.acolhimento_funcao),
    a.carimbo = COALESCE(NULLIF(a.carimbo, ''), fa.carimbo);

ALTER TABLE `ficha_socioeconomico`
    ADD COLUMN IF NOT EXISTS `numero_comodos` INT NULL;

UPDATE `ficha_socioeconomico`
SET `numero_comodos` = `nr_comodos`
WHERE `numero_comodos` IS NULL AND `nr_comodos` IS NOT NULL;

ALTER TABLE `usuario`
    MODIFY COLUMN `Senha` VARCHAR(255) NULL,
    ADD COLUMN IF NOT EXISTS `foto_perfil` VARCHAR(255) NULL;

ALTER TABLE `log`
    MODIFY COLUMN `registro_alt` VARCHAR(255) NULL,
    MODIFY COLUMN `valor_anterior` LONGTEXT NULL,
    MODIFY COLUMN `valor_atual` LONGTEXT NULL,
    ADD COLUMN IF NOT EXISTS `id_registro` INT NULL,
    ADD COLUMN IF NOT EXISTS `campo_alterado` VARCHAR(255) NULL,
    ADD COLUMN IF NOT EXISTS `ip_usuario` VARCHAR(45) NULL,
    ADD COLUMN IF NOT EXISTS `dados_completos` LONGTEXT NULL;

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(100) NOT NULL,
    `token_hash` CHAR(64) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `used_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `token_hash` (`token_hash`),
    KEY `email` (`email`),
    KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- A versao atual consulta esta view para os alertas de faltas.
DROP VIEW IF EXISTS `Atendidos_Com_Alerta`;
DROP VIEW IF EXISTS `atendidos_com_alerta`;

CREATE VIEW `atendidos_com_alerta` AS
SELECT
    a.idatendido,
    a.nome,
    a.cpf,
    COUNT(CASE WHEN fd.status = 'F' THEN 1 END) AS total_faltas,
    MAX(CASE WHEN fd.status = 'F' THEN fd.data END) AS ultima_falta,
    CASE
        WHEN COUNT(CASE WHEN fd.status = 'F' THEN 1 END) >= 3 THEN 'CRITICO'
        WHEN COUNT(CASE WHEN fd.status = 'F' THEN 1 END) = 2 THEN 'ALERTA'
        ELSE 'NORMAL'
    END AS nivel_alerta
FROM `atendido` a
LEFT JOIN `frequencia_dia` fd ON fd.id_atendido = a.idatendido
WHERE a.status = 'Ativo'
  AND NOT EXISTS (
      SELECT 1 FROM `desligamento` d
      WHERE d.id_atendido = a.idatendido
      LIMIT 1
  )
GROUP BY a.idatendido, a.nome, a.cpf
HAVING COUNT(CASE WHEN fd.status = 'F' THEN 1 END) >= 2;
