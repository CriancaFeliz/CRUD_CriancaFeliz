-- =====================================================
-- DESABILITAR TRIGGERS DE ATENDIDO
-- =====================================================
-- Quando criamos uma ficha socioeconômica, o trigger de atendido
-- também é acionado, criando 2 logs. Vamos desabilitar.

DROP TRIGGER IF EXISTS `log_atendido_insert`;
DROP TRIGGER IF EXISTS `log_atendido_update`;
DROP TRIGGER IF EXISTS `log_atendido_delete`;

-- =====================================================
-- CRIAR TRIGGER APENAS PARA FICHA_SOCIOECONOMICO
-- =====================================================
-- Agora apenas a ficha socioeconômica será registrada nos logs

-- Verificar se os triggers de ficha_socioeconomico existem
-- Se não existirem, execute: triggers_socioeconomico_simples.sql

-- =====================================================
-- FIM DO SCRIPT
-- =====================================================
