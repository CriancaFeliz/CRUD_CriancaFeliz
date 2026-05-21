-- Migration: Add data_acolhimento column to Atendido table
-- Description: Adds the acolhimento date column to track when the person was first attended
-- Date: 2025-12-08

-- Add the column if it doesn't exist
ALTER TABLE Atendido ADD COLUMN data_acolhimento DATE COMMENT 'Data when the person was first attended (acolhimento)';

-- Verify the column was created
SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_DEFAULT, IS_NULLABLE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'Atendido'
AND COLUMN_NAME = 'data_acolhimento';
