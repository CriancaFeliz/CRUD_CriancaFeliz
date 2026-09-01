-- =====================================================
-- FIX: Adicionar campo RG nas tabelas Responsavel e Atendido
-- =====================================================
-- Execute este script no phpMyAdmin se estiver dando erro de coluna RG não encontrada
-- Se der erro "Duplicate column", ignore - significa que o campo já existe
-- =====================================================

-- 1. Adicionar RG na tabela Responsavel (se não existir)
ALTER TABLE Responsavel 
ADD COLUMN rg VARCHAR(20) AFTER cpf;

-- 2. Adicionar RG na tabela Atendido (se não existir)
ALTER TABLE Atendido 
ADD COLUMN rg VARCHAR(20) AFTER cpf;

-- Mensagem de sucesso
SELECT 'Campos RG adicionados com sucesso nas tabelas Responsavel e Atendido!' as mensagem;
