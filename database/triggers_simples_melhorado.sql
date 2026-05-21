-- =====================================================
-- TRIGGERS MELHORADOS - VERSÃO SIMPLIFICADA
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
    `ip_usuario`
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
        'bpc', NEW.bpc
    ),
    'INSERT',
    'ficha_socioeconomico',
    @usuario_id,
    NEW.idficha,
    'NOVO_REGISTRO',
    @ip_usuario
);

-- =====================================================
-- TRIGGER UPDATE - CAPTURA MUDANÇAS PRINCIPAIS
-- =====================================================

CREATE TRIGGER `log_ficha_socioeconomico_update` AFTER UPDATE ON `Ficha_Socioeconomico` FOR EACH ROW
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
    CASE 
        WHEN COALESCE(OLD.numero_comodos, OLD.nr_comodos, 0) != COALESCE(NEW.numero_comodos, NEW.nr_comodos, 0) 
            THEN CONCAT('Ficha alterada - Cômodos: ', COALESCE(OLD.numero_comodos, OLD.nr_comodos), ' → ', COALESCE(NEW.numero_comodos, NEW.nr_comodos))
        WHEN COALESCE(OLD.renda_familiar, 0) != COALESCE(NEW.renda_familiar, 0) 
            THEN CONCAT('Ficha alterada - Renda: R$ ', COALESCE(OLD.renda_familiar, 0), ' → R$ ', COALESCE(NEW.renda_familiar, 0))
        WHEN COALESCE(OLD.qtd_pessoas, 0) != COALESCE(NEW.qtd_pessoas, 0) 
            THEN CONCAT('Ficha alterada - Pessoas: ', COALESCE(OLD.qtd_pessoas, 0), ' → ', COALESCE(NEW.qtd_pessoas, 0))
        WHEN COALESCE(OLD.bolsa_familia, 0) != COALESCE(NEW.bolsa_familia, 0) 
            THEN 'Ficha alterada - Benefício: Bolsa Família'
        WHEN COALESCE(OLD.auxilio_brasil, 0) != COALESCE(NEW.auxilio_brasil, 0) 
            THEN 'Ficha alterada - Benefício: Auxílio Brasil'
        WHEN COALESCE(OLD.bpc, 0) != COALESCE(NEW.bpc, 0) 
            THEN 'Ficha alterada - Benefício: BPC'
        WHEN COALESCE(OLD.construcao, '') != COALESCE(NEW.construcao, '') 
            THEN CONCAT('Ficha alterada - Construção: ', COALESCE(OLD.construcao, 'N/A'), ' → ', COALESCE(NEW.construcao, 'N/A'))
        WHEN COALESCE(OLD.moradia, '') != COALESCE(NEW.moradia, '') 
            THEN CONCAT('Ficha alterada - Moradia: ', COALESCE(OLD.moradia, 'N/A'), ' → ', COALESCE(NEW.moradia, 'N/A'))
        ELSE 'Ficha Socioeconômica alterada'
    END,
    CASE 
        WHEN COALESCE(OLD.numero_comodos, OLD.nr_comodos, 0) != COALESCE(NEW.numero_comodos, NEW.nr_comodos, 0) 
            THEN JSON_OBJECT('numero_comodos', COALESCE(OLD.numero_comodos, OLD.nr_comodos))
        WHEN COALESCE(OLD.renda_familiar, 0) != COALESCE(NEW.renda_familiar, 0) 
            THEN JSON_OBJECT('renda_familiar', OLD.renda_familiar)
        WHEN COALESCE(OLD.qtd_pessoas, 0) != COALESCE(NEW.qtd_pessoas, 0) 
            THEN JSON_OBJECT('qtd_pessoas', OLD.qtd_pessoas)
        WHEN COALESCE(OLD.bolsa_familia, 0) != COALESCE(NEW.bolsa_familia, 0) 
            THEN JSON_OBJECT('bolsa_familia', OLD.bolsa_familia)
        WHEN COALESCE(OLD.auxilio_brasil, 0) != COALESCE(NEW.auxilio_brasil, 0) 
            THEN JSON_OBJECT('auxilio_brasil', OLD.auxilio_brasil)
        WHEN COALESCE(OLD.bpc, 0) != COALESCE(NEW.bpc, 0) 
            THEN JSON_OBJECT('bpc', OLD.bpc)
        WHEN COALESCE(OLD.construcao, '') != COALESCE(NEW.construcao, '') 
            THEN JSON_OBJECT('construcao', OLD.construcao)
        WHEN COALESCE(OLD.moradia, '') != COALESCE(NEW.moradia, '') 
            THEN JSON_OBJECT('moradia', OLD.moradia)
        ELSE JSON_OBJECT('info', 'Múltiplos campos alterados')
    END,
    CASE 
        WHEN COALESCE(OLD.numero_comodos, OLD.nr_comodos, 0) != COALESCE(NEW.numero_comodos, NEW.nr_comodos, 0) 
            THEN JSON_OBJECT('numero_comodos', COALESCE(NEW.numero_comodos, NEW.nr_comodos))
        WHEN COALESCE(OLD.renda_familiar, 0) != COALESCE(NEW.renda_familiar, 0) 
            THEN JSON_OBJECT('renda_familiar', NEW.renda_familiar)
        WHEN COALESCE(OLD.qtd_pessoas, 0) != COALESCE(NEW.qtd_pessoas, 0) 
            THEN JSON_OBJECT('qtd_pessoas', NEW.qtd_pessoas)
        WHEN COALESCE(OLD.bolsa_familia, 0) != COALESCE(NEW.bolsa_familia, 0) 
            THEN JSON_OBJECT('bolsa_familia', NEW.bolsa_familia)
        WHEN COALESCE(OLD.auxilio_brasil, 0) != COALESCE(NEW.auxilio_brasil, 0) 
            THEN JSON_OBJECT('auxilio_brasil', NEW.auxilio_brasil)
        WHEN COALESCE(OLD.bpc, 0) != COALESCE(NEW.bpc, 0) 
            THEN JSON_OBJECT('bpc', NEW.bpc)
        WHEN COALESCE(OLD.construcao, '') != COALESCE(NEW.construcao, '') 
            THEN JSON_OBJECT('construcao', NEW.construcao)
        WHEN COALESCE(OLD.moradia, '') != COALESCE(NEW.moradia, '') 
            THEN JSON_OBJECT('moradia', NEW.moradia)
        ELSE JSON_OBJECT('info', 'Múltiplos campos alterados')
    END,
    'UPDATE',
    'ficha_socioeconomico',
    @usuario_id,
    NEW.idficha,
    CASE 
        WHEN COALESCE(OLD.numero_comodos, OLD.nr_comodos, 0) != COALESCE(NEW.numero_comodos, NEW.nr_comodos, 0) THEN 'numero_comodos'
        WHEN COALESCE(OLD.renda_familiar, 0) != COALESCE(NEW.renda_familiar, 0) THEN 'renda_familiar'
        WHEN COALESCE(OLD.qtd_pessoas, 0) != COALESCE(NEW.qtd_pessoas, 0) THEN 'qtd_pessoas'
        WHEN COALESCE(OLD.bolsa_familia, 0) != COALESCE(NEW.bolsa_familia, 0) THEN 'bolsa_familia'
        WHEN COALESCE(OLD.auxilio_brasil, 0) != COALESCE(NEW.auxilio_brasil, 0) THEN 'auxilio_brasil'
        WHEN COALESCE(OLD.bpc, 0) != COALESCE(NEW.bpc, 0) THEN 'bpc'
        WHEN COALESCE(OLD.construcao, '') != COALESCE(NEW.construcao, '') THEN 'construcao'
        WHEN COALESCE(OLD.moradia, '') != COALESCE(NEW.moradia, '') THEN 'moradia'
        ELSE 'MULTIPLOS_CAMPOS'
    END,
    @ip_usuario
);

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
    `ip_usuario`
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
    @ip_usuario
);

-- =====================================================
-- FIM DO SCRIPT
-- =====================================================
-- Triggers melhorados criados com sucesso!
