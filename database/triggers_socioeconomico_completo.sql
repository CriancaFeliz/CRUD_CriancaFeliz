-- =====================================================
-- TRIGGERS COMPLETOS PARA FICHA_SOCIOECONOMICO
-- =====================================================

DELIMITER $$

-- DROP TRIGGERS ANTIGOS
DROP TRIGGER IF EXISTS `log_ficha_socioeconomico_insert`;
DROP TRIGGER IF EXISTS `log_ficha_socioeconomico_update`;
DROP TRIGGER IF EXISTS `log_ficha_socioeconomico_delete`;

-- =====================================================
-- TRIGGER INSERT - CAPTURA TODOS OS CAMPOS
-- =====================================================

CREATE TRIGGER `log_ficha_socioeconomico_insert` AFTER INSERT ON `Ficha_Socioeconomico` FOR EACH ROW
BEGIN
    INSERT INTO `log` (
        `data_alteracao`,
        `registro_alt`,
        `valor_anterior`,
        `valor_atual`,
        `acao`,
        `tabela_afetada`,
        `id_usuario`,
        `id_registro`,
        `campo_alterado`,
        `ip_usuario`
    ) VALUES (
        NOW(),
        CONCAT('Nova Ficha Socioeconômica criada - Menor: ', COALESCE(NEW.nome_menor, 'N/A')),
        NULL,
        JSON_OBJECT(
            'idficha', NEW.idficha,
            'id_atendido', NEW.id_atendido,
            'nome_menor', NEW.nome_menor,
            'entrevistado', NEW.entrevistado,
            'residencia', NEW.residencia,
            'construcao', NEW.construcao,
            'numero_comodos', NEW.numero_comodos,
            'nr_comodos', NEW.nr_comodos,
            'assistente_social', NEW.assistente_social,
            'cadunico', NEW.cadunico,
            'agua', NEW.agua,
            'esgoto', NEW.esgoto,
            'energia', NEW.energia,
            'renda_familiar', NEW.renda_familiar,
            'renda_per_capita', NEW.renda_per_capita,
            'qtd_pessoas', NEW.qtd_pessoas,
            'cond_residencia', NEW.cond_residencia,
            'moradia', NEW.moradia,
            'nr_veiculos', NEW.nr_veiculos,
            'observacoes', NEW.observacoes,
            'bolsa_familia', NEW.bolsa_familia,
            'auxilio_brasil', NEW.auxilio_brasil,
            'bpc', NEW.bpc,
            'auxilio_emergencial', NEW.auxilio_emergencial,
            'seguro_desemprego', NEW.seguro_desemprego,
            'aposentadoria', NEW.aposentadoria
        ),
        'INSERT',
        'ficha_socioeconomico',
        @usuario_id,
        NEW.idficha,
        'NOVO_REGISTRO',
        @ip_usuario
    );
END$$

-- =====================================================
-- TRIGGER UPDATE - CAPTURA TODOS OS CAMPOS
-- =====================================================

CREATE TRIGGER `log_ficha_socioeconomico_update` AFTER UPDATE ON `Ficha_Socioeconomico` FOR EACH ROW
BEGIN
    INSERT INTO `log` (
        `data_alteracao`,
        `registro_alt`,
        `valor_anterior`,
        `valor_atual`,
        `acao`,
        `tabela_afetada`,
        `id_usuario`,
        `id_registro`,
        `campo_alterado`,
        `ip_usuario`
    ) VALUES (
        NOW(),
        CONCAT('Ficha Socioeconômica alterada - Menor: ', COALESCE(NEW.nome_menor, 'N/A')),
        JSON_OBJECT(
            'idficha', OLD.idficha,
            'nome_menor', OLD.nome_menor,
            'entrevistado', OLD.entrevistado,
            'residencia', OLD.residencia,
            'construcao', OLD.construcao,
            'numero_comodos', OLD.numero_comodos,
            'agua', OLD.agua,
            'esgoto', OLD.esgoto,
            'energia', OLD.energia,
            'renda_familiar', OLD.renda_familiar,
            'renda_per_capita', OLD.renda_per_capita,
            'qtd_pessoas', OLD.qtd_pessoas,
            'cond_residencia', OLD.cond_residencia,
            'moradia', OLD.moradia,
            'nr_veiculos', OLD.nr_veiculos,
            'bolsa_familia', OLD.bolsa_familia,
            'auxilio_brasil', OLD.auxilio_brasil,
            'bpc', OLD.bpc,
            'auxilio_emergencial', OLD.auxilio_emergencial,
            'seguro_desemprego', OLD.seguro_desemprego,
            'aposentadoria', OLD.aposentadoria
        ),
        JSON_OBJECT(
            'idficha', NEW.idficha,
            'nome_menor', NEW.nome_menor,
            'entrevistado', NEW.entrevistado,
            'residencia', NEW.residencia,
            'construcao', NEW.construcao,
            'numero_comodos', NEW.numero_comodos,
            'agua', NEW.agua,
            'esgoto', NEW.esgoto,
            'energia', NEW.energia,
            'renda_familiar', NEW.renda_familiar,
            'renda_per_capita', NEW.renda_per_capita,
            'qtd_pessoas', NEW.qtd_pessoas,
            'cond_residencia', NEW.cond_residencia,
            'moradia', NEW.moradia,
            'nr_veiculos', NEW.nr_veiculos,
            'bolsa_familia', NEW.bolsa_familia,
            'auxilio_brasil', NEW.auxilio_brasil,
            'bpc', NEW.bpc,
            'auxilio_emergencial', NEW.auxilio_emergencial,
            'seguro_desemprego', NEW.seguro_desemprego,
            'aposentadoria', NEW.aposentadoria
        ),
        'UPDATE',
        'ficha_socioeconomico',
        @usuario_id,
        NEW.idficha,
        'MULTIPLOS_CAMPOS',
        @ip_usuario
    );
END$$

-- =====================================================
-- TRIGGER DELETE - CAPTURA TODOS OS CAMPOS
-- =====================================================

CREATE TRIGGER `log_ficha_socioeconomico_delete` AFTER DELETE ON `Ficha_Socioeconomico` FOR EACH ROW
BEGIN
    INSERT INTO `log` (
        `data_alteracao`,
        `registro_alt`,
        `valor_anterior`,
        `valor_atual`,
        `acao`,
        `tabela_afetada`,
        `id_usuario`,
        `id_registro`,
        `campo_alterado`,
        `ip_usuario`
    ) VALUES (
        NOW(),
        CONCAT('Ficha Socioeconômica deletada - Menor: ', COALESCE(OLD.nome_menor, 'N/A')),
        JSON_OBJECT(
            'idficha', OLD.idficha,
            'nome_menor', OLD.nome_menor,
            'entrevistado', OLD.entrevistado,
            'residencia', OLD.residencia,
            'construcao', OLD.construcao,
            'numero_comodos', OLD.numero_comodos,
            'renda_familiar', OLD.renda_familiar,
            'renda_per_capita', OLD.renda_per_capita,
            'qtd_pessoas', OLD.qtd_pessoas,
            'bolsa_familia', OLD.bolsa_familia,
            'auxilio_brasil', OLD.auxilio_brasil,
            'bpc', OLD.bpc
        ),
        NULL,
        'DELETE',
        'ficha_socioeconomico',
        @usuario_id,
        OLD.idficha,
        'REGISTRO_DELETADO',
        @ip_usuario
    );
END$$

DELIMITER ;

-- =====================================================
-- FIM DO SCRIPT
-- =====================================================
-- Triggers criados com sucesso!
