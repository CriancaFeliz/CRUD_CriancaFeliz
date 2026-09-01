-- Migration: Add benefit columns to Ficha_Socioeconomico table
-- Description: This migration adds missing benefit flag columns if they don't exist
-- Date: 2025-12-08
-- 
-- If you receive an error that a column already exists, it's safe to ignore it.
-- The application will handle missing columns gracefully.

-- Add benefit columns to Ficha_Socioeconomico if they don't exist
-- Run these statements one by one in phpMyAdmin or MySQL client

ALTER TABLE Ficha_Socioeconomico ADD COLUMN bolsa_familia TINYINT(1) DEFAULT 0 COMMENT 'Flag: Has Bolsa Família benefit (0=no, 1=yes)';

ALTER TABLE Ficha_Socioeconomico ADD COLUMN auxilio_brasil TINYINT(1) DEFAULT 0 COMMENT 'Flag: Has Auxílio Brasil benefit (0=no, 1=yes)';

ALTER TABLE Ficha_Socioeconomico ADD COLUMN bpc TINYINT(1) DEFAULT 0 COMMENT 'Flag: Has BPC benefit (0=no, 1=yes)';

ALTER TABLE Ficha_Socioeconomico ADD COLUMN auxilio_emergencial TINYINT(1) DEFAULT 0 COMMENT 'Flag: Has Auxílio Emergencial benefit (0=no, 1=yes)';

ALTER TABLE Ficha_Socioeconomico ADD COLUMN seguro_desemprego TINYINT(1) DEFAULT 0 COMMENT 'Flag: Has Seguro Desemprego benefit (0=no, 1=yes)';

ALTER TABLE Ficha_Socioeconomico ADD COLUMN aposentadoria TINYINT(1) DEFAULT 0 COMMENT 'Flag: Has Aposentadoria benefit (0=no, 1=yes)';

-- Verify columns were created
SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_DEFAULT, COLUMN_COMMENT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'Ficha_Socioeconomico' 
AND COLUMN_NAME IN ('bolsa_familia', 'auxilio_brasil', 'bpc', 'auxilio_emergencial', 'seguro_desemprego', 'aposentadoria')
ORDER BY COLUMN_NAME;
