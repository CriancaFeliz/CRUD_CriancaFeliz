-- =====================================================
-- SCRIPT PARA VERIFICAR E ADICIONAR COLUNAS FALTANTES
-- =====================================================

-- 1. VERIFICAR ESTRUTURA DA TABELA FICHA_SOCIOECONOMICO
-- =====================================================

-- Adicionar colunas se não existirem
ALTER TABLE `Ficha_Socioeconomico`
ADD COLUMN IF NOT EXISTS `nome_menor` VARCHAR(255) DEFAULT NULL AFTER `entrevistado`,
ADD COLUMN IF NOT EXISTS `construcao` VARCHAR(100) DEFAULT NULL AFTER `residencia`,
ADD COLUMN IF NOT EXISTS `numero_comodos` INT(11) DEFAULT NULL AFTER `construcao`,
ADD COLUMN IF NOT EXISTS `nr_comodos` INT(11) DEFAULT NULL AFTER `numero_comodos`,
ADD COLUMN IF NOT EXISTS `assistente_social` VARCHAR(255) DEFAULT NULL AFTER `nr_comodos`,
ADD COLUMN IF NOT EXISTS `cadunico` VARCHAR(100) DEFAULT NULL AFTER `assistente_social`,
ADD COLUMN IF NOT EXISTS `renda_per_capita` DECIMAL(10, 2) DEFAULT NULL AFTER `cadunico`,
ADD COLUMN IF NOT EXISTS `agua` TINYINT(1) DEFAULT 0 AFTER `renda_per_capita`,
ADD COLUMN IF NOT EXISTS `esgoto` TINYINT(1) DEFAULT 0 AFTER `agua`,
ADD COLUMN IF NOT EXISTS `energia` TINYINT(1) DEFAULT 0 AFTER `esgoto`,
ADD COLUMN IF NOT EXISTS `bolsa_familia` TINYINT(1) DEFAULT 0 AFTER `energia`,
ADD COLUMN IF NOT EXISTS `auxilio_brasil` TINYINT(1) DEFAULT 0 AFTER `bolsa_familia`,
ADD COLUMN IF NOT EXISTS `bpc` TINYINT(1) DEFAULT 0 AFTER `auxilio_brasil`,
ADD COLUMN IF NOT EXISTS `auxilio_emergencial` TINYINT(1) DEFAULT 0 AFTER `bpc`,
ADD COLUMN IF NOT EXISTS `seguro_desemprego` TINYINT(1) DEFAULT 0 AFTER `auxilio_emergencial`,
ADD COLUMN IF NOT EXISTS `aposentadoria` TINYINT(1) DEFAULT 0 AFTER `seguro_desemprego`,
ADD COLUMN IF NOT EXISTS `cond_residencia` VARCHAR(100) DEFAULT NULL AFTER `aposentadoria`,
ADD COLUMN IF NOT EXISTS `moradia` VARCHAR(100) DEFAULT NULL AFTER `cond_residencia`,
ADD COLUMN IF NOT EXISTS `nr_veiculos` INT(11) DEFAULT 0 AFTER `moradia`,
ADD COLUMN IF NOT EXISTS `observacoes` LONGTEXT DEFAULT NULL AFTER `nr_veiculos`;

-- 2. VERIFICAR ESTRUTURA DA TABELA FAMILIA
-- =====================================================

-- Criar tabela Familia se não existir
CREATE TABLE IF NOT EXISTS `Familia` (
  `id_familia` INT(11) NOT NULL AUTO_INCREMENT,
  `id_ficha` INT(11) NOT NULL,
  `nome` VARCHAR(255) NOT NULL,
  `parentesco` VARCHAR(100) NOT NULL,
  `data_nasc` DATE DEFAULT NULL,
  `formacao` VARCHAR(100) DEFAULT NULL,
  `renda` DECIMAL(10, 2) DEFAULT 0,
  `data_criacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_familia`),
  FOREIGN KEY (`id_ficha`) REFERENCES `Ficha_Socioeconomico`(`idficha`) ON DELETE CASCADE,
  INDEX `idx_id_ficha` (`id_ficha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. VERIFICAR ESTRUTURA DA TABELA DESPESAS
-- =====================================================

-- Criar tabela Despesas se não existir
CREATE TABLE IF NOT EXISTS `Despesas` (
  `id_despesa` INT(11) NOT NULL AUTO_INCREMENT,
  `id_ficha` INT(11) NOT NULL,
  `valor_despesa` DECIMAL(10, 2) DEFAULT 0,
  `tipo_renda` VARCHAR(100) DEFAULT NULL,
  `valor_renda` DECIMAL(10, 2) DEFAULT 0,
  `data_criacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_despesa`),
  FOREIGN KEY (`id_ficha`) REFERENCES `Ficha_Socioeconomico`(`idficha`) ON DELETE CASCADE,
  INDEX `idx_id_ficha` (`id_ficha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. CRIAR ÍNDICES PARA MELHOR PERFORMANCE
-- =====================================================

CREATE INDEX IF NOT EXISTS `idx_nome_menor` ON `Ficha_Socioeconomico` (`nome_menor`);
CREATE INDEX IF NOT EXISTS `idx_renda_familiar` ON `Ficha_Socioeconomico` (`renda_familiar`);
CREATE INDEX IF NOT EXISTS `idx_id_atendido` ON `Ficha_Socioeconomico` (`id_atendido`);
CREATE INDEX IF NOT EXISTS `idx_bolsa_familia` ON `Ficha_Socioeconomico` (`bolsa_familia`);
CREATE INDEX IF NOT EXISTS `idx_auxilio_brasil` ON `Ficha_Socioeconomico` (`auxilio_brasil`);

-- =====================================================
-- FIM DO SCRIPT
-- =====================================================
-- Colunas adicionadas/verificadas com sucesso!
-- Agora execute o script de triggers: limpar_e_recriar_triggers.sql
