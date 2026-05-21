-- =====================================================
-- FIX: Adicionar campos de endereço na tabela Responsavel
-- =====================================================
-- Execute este script no phpMyAdmin
-- Se der erro "Duplicate column", ignore - significa que o campo já existe
-- =====================================================

-- Adicionar campo endereco
ALTER TABLE Responsavel 
ADD COLUMN endereco VARCHAR(255) AFTER parentesco;

-- Adicionar campo numero
ALTER TABLE Responsavel 
ADD COLUMN numero VARCHAR(20) AFTER endereco;

-- Adicionar campo complemento
ALTER TABLE Responsavel 
ADD COLUMN complemento VARCHAR(100) AFTER numero;

-- Adicionar campo bairro
ALTER TABLE Responsavel 
ADD COLUMN bairro VARCHAR(100) AFTER complemento;

-- Adicionar campo cidade
ALTER TABLE Responsavel 
ADD COLUMN cidade VARCHAR(100) AFTER bairro;

-- Adicionar campo cep
ALTER TABLE Responsavel 
ADD COLUMN cep VARCHAR(10) AFTER cidade;

-- Mensagem de sucesso
SELECT 'Campos de endereço adicionados com sucesso na tabela Responsavel!' as mensagem;
