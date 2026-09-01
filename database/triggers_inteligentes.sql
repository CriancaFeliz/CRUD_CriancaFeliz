-- =====================================================
-- TRIGGERS INTELIGENTES - CAPTURA APENAS MUDANÇAS
-- =====================================================

-- Desabilitar triggers antigos
DROP TRIGGER IF EXISTS `log_ficha_socioeconomico_insert`;
DROP TRIGGER IF EXISTS `log_ficha_socioeconomico_update`;
DROP TRIGGER IF EXISTS `log_ficha_socioeconomico_delete`;

-- =====================================================
-- TRIGGER INSERT - NOVO REGISTRO
-- =====================================================

CREATE TRIGGER `log_ficha_socioeconomico_insert` AFTER INSERT ON `Ficha_Socioeconomico` FOR EACH ROW
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
    `ip_usuario`,
    `dados_completos`
) VALUES (
    NOW(),
    CONCAT('Nova Ficha Socioeconômica criada - Menor: ', COALESCE(NEW.nome_menor, 'N/A')),
    NULL,
    JSON_OBJECT(
        'nome_menor', NEW.nome_menor,
        'entrevistado', NEW.entrevistado,
        'renda_familiar', NEW.renda_familiar,
        'renda_per_capita', NEW.renda_per_capita,
        'qtd_pessoas', NEW.qtd_pessoas,
        'numero_comodos', COALESCE(NEW.numero_comodos, NEW.nr_comodos),
        'construcao', NEW.construcao,
        'residencia', NEW.residencia,
        'moradia', NEW.moradia,
        'agua', NEW.agua,
        'esgoto', NEW.esgoto,
        'energia', NEW.energia,
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
    @ip_usuario,
    JSON_OBJECT(
        'renda_familiar', NEW.renda_familiar,
        'qtd_pessoas', NEW.qtd_pessoas,
        'numero_comodos', COALESCE(NEW.numero_comodos, NEW.nr_comodos)
    )
);

-- =====================================================
-- TRIGGER UPDATE - CAPTURA APENAS MUDANÇAS
-- =====================================================

CREATE TRIGGER `log_ficha_socioeconomico_update` AFTER UPDATE ON `Ficha_Socioeconomico` FOR EACH ROW
BEGIN
    DECLARE v_campos_mudados VARCHAR(1000);
    DECLARE v_valor_anterior JSON;
    DECLARE v_valor_atual JSON;
    
    SET v_campos_mudados = '';
    SET v_valor_anterior = JSON_OBJECT();
    SET v_valor_atual = JSON_OBJECT();
    
    -- Verificar cada campo e capturar apenas os que mudaram
    
    IF COALESCE(OLD.nome_menor, '') != COALESCE(NEW.nome_menor, '') THEN
        SET v_campos_mudados = CONCAT(v_campos_mudados, 'nome_menor, ');
        SET v_valor_anterior = JSON_SET(v_valor_anterior, '$.nome_menor', OLD.nome_menor);
        SET v_valor_atual = JSON_SET(v_valor_atual, '$.nome_menor', NEW.nome_menor);
    END IF;
    
    IF COALESCE(OLD.entrevistado, '') != COALESCE(NEW.entrevistado, '') THEN
        SET v_campos_mudados = CONCAT(v_campos_mudados, 'entrevistado, ');
        SET v_valor_anterior = JSON_SET(v_valor_anterior, '$.entrevistado', OLD.entrevistado);
        SET v_valor_atual = JSON_SET(v_valor_atual, '$.entrevistado', NEW.entrevistado);
    END IF;
    
    IF COALESCE(OLD.renda_familiar, 0) != COALESCE(NEW.renda_familiar, 0) THEN
        SET v_campos_mudados = CONCAT(v_campos_mudados, 'renda_familiar, ');
        SET v_valor_anterior = JSON_SET(v_valor_anterior, '$.renda_familiar', OLD.renda_familiar);
        SET v_valor_atual = JSON_SET(v_valor_atual, '$.renda_familiar', NEW.renda_familiar);
    END IF;
    
    IF COALESCE(OLD.renda_per_capita, 0) != COALESCE(NEW.renda_per_capita, 0) THEN
        SET v_campos_mudados = CONCAT(v_campos_mudados, 'renda_per_capita, ');
        SET v_valor_anterior = JSON_SET(v_valor_anterior, '$.renda_per_capita', OLD.renda_per_capita);
        SET v_valor_atual = JSON_SET(v_valor_atual, '$.renda_per_capita', NEW.renda_per_capita);
    END IF;
    
    IF COALESCE(OLD.qtd_pessoas, 0) != COALESCE(NEW.qtd_pessoas, 0) THEN
        SET v_campos_mudados = CONCAT(v_campos_mudados, 'qtd_pessoas, ');
        SET v_valor_anterior = JSON_SET(v_valor_anterior, '$.qtd_pessoas', OLD.qtd_pessoas);
        SET v_valor_atual = JSON_SET(v_valor_atual, '$.qtd_pessoas', NEW.qtd_pessoas);
    END IF;
    
    IF COALESCE(OLD.numero_comodos, OLD.nr_comodos, 0) != COALESCE(NEW.numero_comodos, NEW.nr_comodos, 0) THEN
        SET v_campos_mudados = CONCAT(v_campos_mudados, 'numero_comodos, ');
        SET v_valor_anterior = JSON_SET(v_valor_anterior, '$.numero_comodos', COALESCE(OLD.numero_comodos, OLD.nr_comodos));
        SET v_valor_atual = JSON_SET(v_valor_atual, '$.numero_comodos', COALESCE(NEW.numero_comodos, NEW.nr_comodos));
    END IF;
    
    IF COALESCE(OLD.construcao, '') != COALESCE(NEW.construcao, '') THEN
        SET v_campos_mudados = CONCAT(v_campos_mudados, 'construcao, ');
        SET v_valor_anterior = JSON_SET(v_valor_anterior, '$.construcao', OLD.construcao);
        SET v_valor_atual = JSON_SET(v_valor_atual, '$.construcao', NEW.construcao);
    END IF;
    
    IF COALESCE(OLD.residencia, '') != COALESCE(NEW.residencia, '') THEN
        SET v_campos_mudados = CONCAT(v_campos_mudados, 'residencia, ');
        SET v_valor_anterior = JSON_SET(v_valor_anterior, '$.residencia', OLD.residencia);
        SET v_valor_atual = JSON_SET(v_valor_atual, '$.residencia', NEW.residencia);
    END IF;
    
    IF COALESCE(OLD.moradia, '') != COALESCE(NEW.moradia, '') THEN
        SET v_campos_mudados = CONCAT(v_campos_mudados, 'moradia, ');
        SET v_valor_anterior = JSON_SET(v_valor_anterior, '$.moradia', OLD.moradia);
        SET v_valor_atual = JSON_SET(v_valor_atual, '$.moradia', NEW.moradia);
    END IF;
    
    IF COALESCE(OLD.agua, 0) != COALESCE(NEW.agua, 0) THEN
        SET v_campos_mudados = CONCAT(v_campos_mudados, 'agua, ');
        SET v_valor_anterior = JSON_SET(v_valor_anterior, '$.agua', OLD.agua);
        SET v_valor_atual = JSON_SET(v_valor_atual, '$.agua', NEW.agua);
    END IF;
    
    IF COALESCE(OLD.esgoto, 0) != COALESCE(NEW.esgoto, 0) THEN
        SET v_campos_mudados = CONCAT(v_campos_mudados, 'esgoto, ');
        SET v_valor_anterior = JSON_SET(v_valor_anterior, '$.esgoto', OLD.esgoto);
        SET v_valor_atual = JSON_SET(v_valor_atual, '$.esgoto', NEW.esgoto);
    END IF;
    
    IF COALESCE(OLD.energia, 0) != COALESCE(NEW.energia, 0) THEN
        SET v_campos_mudados = CONCAT(v_campos_mudados, 'energia, ');
        SET v_valor_anterior = JSON_SET(v_valor_anterior, '$.energia', OLD.energia);
        SET v_valor_atual = JSON_SET(v_valor_atual, '$.energia', NEW.energia);
    END IF;
    
    IF COALESCE(OLD.bolsa_familia, 0) != COALESCE(NEW.bolsa_familia, 0) THEN
        SET v_campos_mudados = CONCAT(v_campos_mudados, 'bolsa_familia, ');
        SET v_valor_anterior = JSON_SET(v_valor_anterior, '$.bolsa_familia', OLD.bolsa_familia);
        SET v_valor_atual = JSON_SET(v_valor_atual, '$.bolsa_familia', NEW.bolsa_familia);
    END IF;
    
    IF COALESCE(OLD.auxilio_brasil, 0) != COALESCE(NEW.auxilio_brasil, 0) THEN
        SET v_campos_mudados = CONCAT(v_campos_mudados, 'auxilio_brasil, ');
        SET v_valor_anterior = JSON_SET(v_valor_anterior, '$.auxilio_brasil', OLD.auxilio_brasil);
        SET v_valor_atual = JSON_SET(v_valor_atual, '$.auxilio_brasil', NEW.auxilio_brasil);
    END IF;
    
    IF COALESCE(OLD.bpc, 0) != COALESCE(NEW.bpc, 0) THEN
        SET v_campos_mudados = CONCAT(v_campos_mudados, 'bpc, ');
        SET v_valor_anterior = JSON_SET(v_valor_anterior, '$.bpc', OLD.bpc);
        SET v_valor_atual = JSON_SET(v_valor_atual, '$.bpc', NEW.bpc);
    END IF;
    
    IF COALESCE(OLD.auxilio_emergencial, 0) != COALESCE(NEW.auxilio_emergencial, 0) THEN
        SET v_campos_mudados = CONCAT(v_campos_mudados, 'auxilio_emergencial, ');
        SET v_valor_anterior = JSON_SET(v_valor_anterior, '$.auxilio_emergencial', OLD.auxilio_emergencial);
        SET v_valor_atual = JSON_SET(v_valor_atual, '$.auxilio_emergencial', NEW.auxilio_emergencial);
    END IF;
    
    IF COALESCE(OLD.seguro_desemprego, 0) != COALESCE(NEW.seguro_desemprego, 0) THEN
        SET v_campos_mudados = CONCAT(v_campos_mudados, 'seguro_desemprego, ');
        SET v_valor_anterior = JSON_SET(v_valor_anterior, '$.seguro_desemprego', OLD.seguro_desemprego);
        SET v_valor_atual = JSON_SET(v_valor_atual, '$.seguro_desemprego', NEW.seguro_desemprego);
    END IF;
    
    IF COALESCE(OLD.aposentadoria, 0) != COALESCE(NEW.aposentadoria, 0) THEN
        SET v_campos_mudados = CONCAT(v_campos_mudados, 'aposentadoria, ');
        SET v_valor_anterior = JSON_SET(v_valor_anterior, '$.aposentadoria', OLD.aposentadoria);
        SET v_valor_atual = JSON_SET(v_valor_atual, '$.aposentadoria', NEW.aposentadoria);
    END IF;
    
    -- Se houve mudanças, registrar no log
    IF v_campos_mudados != '' THEN
        -- Remover última vírgula
        SET v_campos_mudados = TRIM(TRAILING ', ' FROM v_campos_mudados);
        
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
            `ip_usuario`,
            `dados_completos`
        ) VALUES (
            NOW(),
            CONCAT('Ficha Socioeconômica alterada - Campos: ', v_campos_mudados),
            v_valor_anterior,
            v_valor_atual,
            'UPDATE',
            'ficha_socioeconomico',
            @usuario_id,
            NEW.idficha,
            v_campos_mudados,
            @ip_usuario,
            JSON_OBJECT(
                'campos_alterados', v_campos_mudados,
                'total_campos', JSON_LENGTH(v_valor_anterior)
            )
        );
    END IF;
END;

-- =====================================================
-- TRIGGER DELETE - REGISTRO DELETADO
-- =====================================================

CREATE TRIGGER `log_ficha_socioeconomico_delete` AFTER DELETE ON `Ficha_Socioeconomico` FOR EACH ROW
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
    `ip_usuario`,
    `dados_completos`
) VALUES (
    NOW(),
    CONCAT('Ficha Socioeconômica deletada - Menor: ', COALESCE(OLD.nome_menor, 'N/A')),
    JSON_OBJECT(
        'nome_menor', OLD.nome_menor,
        'entrevistado', OLD.entrevistado,
        'renda_familiar', OLD.renda_familiar,
        'qtd_pessoas', OLD.qtd_pessoas,
        'numero_comodos', COALESCE(OLD.numero_comodos, OLD.nr_comodos)
    ),
    NULL,
    'DELETE',
    'ficha_socioeconomico',
    @usuario_id,
    OLD.idficha,
    'REGISTRO_DELETADO',
    @ip_usuario,
    JSON_OBJECT(
        'renda_familiar', OLD.renda_familiar,
        'qtd_pessoas', OLD.qtd_pessoas,
        'numero_comodos', COALESCE(OLD.numero_comodos, OLD.nr_comodos)
    )
);

-- =====================================================
-- FIM DO SCRIPT
-- =====================================================
-- Triggers inteligentes criados com sucesso!
-- Agora apenas as mudanças serão registradas nos logs
