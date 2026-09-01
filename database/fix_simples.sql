-- =====================================================
-- FIX SIMPLES - Execute um por um no phpMyAdmin
-- Se der erro "Duplicate column", IGNORE e continue
-- =====================================================

USE criancafeliz;

-- 1. Adicionar endereco
ALTER TABLE Responsavel ADD COLUMN endereco VARCHAR(255);

-- 2. Adicionar numero
ALTER TABLE Responsavel ADD COLUMN numero VARCHAR(20);

-- 3. Adicionar complemento
ALTER TABLE Responsavel ADD COLUMN complemento VARCHAR(100);

-- 4. Adicionar bairro
ALTER TABLE Responsavel ADD COLUMN bairro VARCHAR(100);

-- 5. Adicionar cidade
ALTER TABLE Responsavel ADD COLUMN cidade VARCHAR(100);

-- 6. Adicionar cep
ALTER TABLE Responsavel ADD COLUMN cep VARCHAR(10);

-- Ver resultado
SELECT 'Colunas adicionadas com sucesso!' as mensagem;
DESCRIBE Responsavel;
