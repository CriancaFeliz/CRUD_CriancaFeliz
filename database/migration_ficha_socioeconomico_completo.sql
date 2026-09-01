-- ================================================================
-- MIGRAÇÃO COMPLETA: Corrigir Tabela Ficha_Socioeconomico
-- ================================================================
-- 
-- Este script adiciona TODAS as colunas faltantes na tabela
-- Ficha_Socioeconomico que a aplicação espera.
--
-- Se uma coluna já existir, você receberá um erro "Column already exists"
-- Isto é NORMAL e significa que a coluna já foi adicionada anteriormente.
-- Você pode ignorar esse erro e continuar com os próximos comandos.
--
-- Data: 2025-12-08
-- ================================================================

-- Verificar colunas antes da migração
SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_DEFAULT, IS_NULLABLE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'Ficha_Socioeconomico' 
ORDER BY COLUMN_NAME;

-- ================================================================
-- 1. COLUNAS DE BENEFÍCIOS SOCIAIS
-- ================================================================

ALTER TABLE Ficha_Socioeconomico ADD COLUMN bolsa_familia TINYINT(1) DEFAULT 0 COMMENT 'Flag: Recebe Bolsa Família';

ALTER TABLE Ficha_Socioeconomico ADD COLUMN auxilio_brasil TINYINT(1) DEFAULT 0 COMMENT 'Flag: Recebe Auxílio Brasil';

ALTER TABLE Ficha_Socioeconomico ADD COLUMN bpc TINYINT(1) DEFAULT 0 COMMENT 'Flag: Recebe BPC (Benefício de Prestação Continuada)';

ALTER TABLE Ficha_Socioeconomico ADD COLUMN auxilio_emergencial TINYINT(1) DEFAULT 0 COMMENT 'Flag: Recebe Auxílio Emergencial';

ALTER TABLE Ficha_Socioeconomico ADD COLUMN seguro_desemprego TINYINT(1) DEFAULT 0 COMMENT 'Flag: Recebe Seguro Desemprego';

ALTER TABLE Ficha_Socioeconomico ADD COLUMN aposentadoria TINYINT(1) DEFAULT 0 COMMENT 'Flag: Recebe Aposentadoria';

-- ================================================================
-- 2. COLUNAS DE INFRAESTRUTURA (caso faltando)
-- ================================================================

ALTER TABLE Ficha_Socioeconomico ADD COLUMN agua TINYINT(1) DEFAULT 0 COMMENT 'Tem acesso a água';

ALTER TABLE Ficha_Socioeconomico ADD COLUMN esgoto TINYINT(1) DEFAULT 0 COMMENT 'Tem acesso a esgoto';

ALTER TABLE Ficha_Socioeconomico ADD COLUMN energia TINYINT(1) DEFAULT 0 COMMENT 'Tem acesso a energia elétrica';

-- ================================================================
-- 3. COLUNAS DE HABITAÇÃO (caso faltando)
-- ================================================================

ALTER TABLE Ficha_Socioeconomico ADD COLUMN moradia VARCHAR(100) COMMENT 'Tipo de moradia (Casa, Apartamento, etc)';

ALTER TABLE Ficha_Socioeconomico ADD COLUMN cond_residencia VARCHAR(100) COMMENT 'Condição da moradia';

ALTER TABLE Ficha_Socioeconomico ADD COLUMN nr_comodos INT DEFAULT 0 COMMENT 'Número de cômodos';

ALTER TABLE Ficha_Socioeconomico ADD COLUMN nr_veiculos INT DEFAULT 0 COMMENT 'Número de veículos';

-- ================================================================
-- 4. COLUNAS DE IDENTIFICAÇÃO (caso faltando)
-- ================================================================

ALTER TABLE Ficha_Socioeconomico ADD COLUMN entrevistado VARCHAR(255) COMMENT 'Nome do entrevistado';

-- ================================================================
-- 5. COLUNAS DE OBSERVAÇÕES (caso faltando)
-- ================================================================

ALTER TABLE Ficha_Socioeconomico ADD COLUMN observacoes TEXT COMMENT 'Observações gerais da ficha';

-- ================================================================
-- 6. COLUNAS ADICIONAIS (nome do menor, assistente social, cadunico, renda per capita)
-- ================================================================

ALTER TABLE Ficha_Socioeconomico ADD COLUMN nome_menor VARCHAR(255) DEFAULT NULL COMMENT 'Nome do menor atendido';

ALTER TABLE Ficha_Socioeconomico ADD COLUMN assistente_social VARCHAR(255) DEFAULT NULL COMMENT 'Nome do assistente social';

ALTER TABLE Ficha_Socioeconomico ADD COLUMN cadunico VARCHAR(10) DEFAULT NULL COMMENT 'Cadastro Único (Sim/Não)';

ALTER TABLE Ficha_Socioeconomico ADD COLUMN renda_per_capita DECIMAL(10,2) DEFAULT NULL COMMENT 'Renda per capita calculada';

-- ================================================================
-- VERIFICAÇÃO FINAL
-- ================================================================

-- Verificar colunas após migração
SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_DEFAULT, IS_NULLABLE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'Ficha_Socioeconomico' 
ORDER BY COLUMN_NAME;

-- Contar linhas na tabela (para confirmar integridade)
SELECT COUNT(*) as total_fichas FROM Ficha_Socioeconomico;

-- ================================================================
-- FIM DA MIGRAÇÃO
-- ================================================================
