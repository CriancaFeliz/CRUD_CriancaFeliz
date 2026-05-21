-- SQL commands to run one-by-one in phpMyAdmin (copy/paste each command and run)
-- Date: 2025-12-08
-- If you get "Column already exists" for any command, IGNORE and continue to next.

-- 1) Add benefit flags
ALTER TABLE Ficha_Socioeconomico ADD COLUMN bolsa_familia TINYINT(1) DEFAULT 0;
ALTER TABLE Ficha_Socioeconomico ADD COLUMN auxilio_brasil TINYINT(1) DEFAULT 0;
ALTER TABLE Ficha_Socioeconomico ADD COLUMN bpc TINYINT(1) DEFAULT 0;
ALTER TABLE Ficha_Socioeconomico ADD COLUMN auxilio_emergencial TINYINT(1) DEFAULT 0;
ALTER TABLE Ficha_Socioeconomico ADD COLUMN seguro_desemprego TINYINT(1) DEFAULT 0;
ALTER TABLE Ficha_Socioeconomico ADD COLUMN aposentadoria TINYINT(1) DEFAULT 0;

-- 2) Infra / utilidades
ALTER TABLE Ficha_Socioeconomico ADD COLUMN agua TINYINT(1) DEFAULT 0;
ALTER TABLE Ficha_Socioeconomico ADD COLUMN esgoto TINYINT(1) DEFAULT 0;
ALTER TABLE Ficha_Socioeconomico ADD COLUMN energia TINYINT(1) DEFAULT 0;

-- 3) Habitação e counts
ALTER TABLE Ficha_Socioeconomico ADD COLUMN moradia VARCHAR(100) DEFAULT NULL;
ALTER TABLE Ficha_Socioeconomico ADD COLUMN cond_residencia VARCHAR(100) DEFAULT NULL;
ALTER TABLE Ficha_Socioeconomico ADD COLUMN nr_comodos INT DEFAULT 0;
ALTER TABLE Ficha_Socioeconomico ADD COLUMN nr_veiculos INT DEFAULT 0;

-- 4) Identificação / metadados / observações
ALTER TABLE Ficha_Socioeconomico ADD COLUMN entrevistado VARCHAR(255) DEFAULT NULL;
ALTER TABLE Ficha_Socioeconomico ADD COLUMN observacoes TEXT DEFAULT NULL;

-- 5) Campos adicionais solicitados
ALTER TABLE Ficha_Socioeconomico ADD COLUMN nome_menor VARCHAR(255) DEFAULT NULL;
ALTER TABLE Ficha_Socioeconomico ADD COLUMN assistente_social VARCHAR(255) DEFAULT NULL;
ALTER TABLE Ficha_Socioeconomico ADD COLUMN cadunico VARCHAR(10) DEFAULT NULL;
ALTER TABLE Ficha_Socioeconomico ADD COLUMN renda_per_capita DECIMAL(10,2) DEFAULT NULL;

-- 6) (Se ainda não adicionada) data_acolhimento na tabela Atendido
ALTER TABLE Atendido ADD COLUMN data_acolhimento DATE DEFAULT NULL;

-- 7) Verify (run to list created columns)
SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_DEFAULT, IS_NULLABLE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'Ficha_Socioeconomico'
  AND COLUMN_NAME IN (
    'bolsa_familia','auxilio_brasil','bpc','auxilio_emergencial','seguro_desemprego','aposentadoria',
    'agua','esgoto','energia','moradia','cond_residencia','nr_comodos','nr_veiculos',
    'entrevistado','observacoes','nome_menor','assistente_social','cadunico','renda_per_capita'
  )
ORDER BY COLUMN_NAME;

-- 8) Verify Atendido
SHOW COLUMNS FROM Atendido LIKE 'data_acolhimento';
