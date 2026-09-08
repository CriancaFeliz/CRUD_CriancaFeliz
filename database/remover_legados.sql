-- =====================================================
-- REMOÇÃO DE TABELAS E VIEWS LEGADAS / NÃO UTILIZADAS
-- Sistema Criança Feliz
-- =====================================================
-- Execute este script no phpMyAdmin para limpar o banco
-- de dados de estruturas obsoletas que não são mais usadas
-- pela aplicação atual.
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Tabelas de presença/sessões antigas (substituídas por frequencia_dia e frequencia_oficina)
DROP TABLE IF EXISTS `presenca`;
DROP TABLE IF EXISTS `sessao`;
DROP TABLE IF EXISTS `dias_atendimento`;
DROP TABLE IF EXISTS `encontro`;

-- 2. Tabelas de psicologia antigas (substituídas por anotacao_psicologica)
DROP TABLE IF EXISTS `faltas_psicologia`;

-- 3. Tabelas de permissões e fichas legadas
DROP TABLE IF EXISTS `usuario_permissoes`;
DROP TABLE IF EXISTS `permissoes`;
DROP TABLE IF EXISTS `ficha_acolhimento`;

-- 4. Views legadas / não utilizadas
DROP TABLE IF EXISTS `estatisticas_frequencia`;
DROP VIEW IF EXISTS `estatisticas_frequencia`;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- LIMPEZA CONCLUÍDA
-- =====================================================
