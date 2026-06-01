-- =====================================================
-- MIGRACAO DE SCHEMA - CRIANCA FELIZ
-- =====================================================
USE criancafeliz;

-- 1. Garantir suporte a hashes modernos de senha
ALTER TABLE `usuario` MODIFY COLUMN `Senha` varchar(255) DEFAULT NULL;
ALTER TABLE `usuario` ADD COLUMN IF NOT EXISTS `foto_perfil` varchar(255) DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `used_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_hash` (`token_hash`),
  KEY `email` (`email`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Oficializar tabela usada pela área psicológica
CREATE TABLE IF NOT EXISTS `anotacao_psicologica` (
  `id_anotacao` int(11) NOT NULL AUTO_INCREMENT,
  `id_atendido` int(11) NOT NULL,
  `id_psicologo` int(11) NOT NULL,
  `data_anotacao` datetime DEFAULT current_timestamp(),
  `tipo` enum('Consulta','Avaliação','Evolução','Observação') DEFAULT 'Consulta',
  `titulo` varchar(200) DEFAULT NULL,
  `conteudo` text DEFAULT NULL,
  `humor` int(11) DEFAULT NULL,
  `observacoes_comportamentais` text DEFAULT NULL,
  `recomendacoes` text DEFAULT NULL,
  `proxima_sessao` date DEFAULT NULL,
  `anexos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_anotacao`),
  KEY `id_atendido` (`id_atendido`),
  KEY `id_psicologo` (`id_psicologo`),
  CONSTRAINT `anotacao_psicologica_ibfk_1` FOREIGN KEY (`id_atendido`) REFERENCES `atendido` (`idatendido`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `anotacao_psicologica_ibfk_2` FOREIGN KEY (`id_psicologo`) REFERENCES `usuario` (`idusuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Remover tabelas obsoletas se existirem
DROP TABLE IF EXISTS `presenca`;
DROP TABLE IF EXISTS `sessao`;
DROP TABLE IF EXISTS `frequencia`;

-- 4. Garantir coluna numero_comodos e remover nr_comodos e faixa_etaria
ALTER TABLE `Ficha_Socioeconomico` ADD COLUMN IF NOT EXISTS `numero_comodos` INT DEFAULT 0;
ALTER TABLE `Ficha_Socioeconomico` DROP COLUMN IF EXISTS `nr_comodos`;
ALTER TABLE `Atendido` DROP COLUMN IF EXISTS `faixa_etaria`;

-- 5. Atualizar triggers de auditoria (remover nr_comodos)
DROP TRIGGER IF EXISTS `log_ficha_socioeconomico_insert`;
DROP TRIGGER IF EXISTS `log_ficha_socioeconomico_update`;
DROP TRIGGER IF EXISTS `log_ficha_socioeconomico_delete`;

DELIMITER $$

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
        'numero_comodos', NEW.numero_comodos,
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
        'aposentadoria', NEW.aposentadoria,
        'assistente_social', NEW.assistente_social,
        'cadunico', NEW.cadunico,
        'cond_residencia', NEW.cond_residencia,
        'nr_veiculos', NEW.nr_veiculos,
        'observacoes', NEW.observacoes
    ),
    'INSERT',
    'ficha_socioeconomico',
    @usuario_id,
    NEW.idficha,
    'NOVO_REGISTRO',
    @ip_usuario
);
$$

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
    CONCAT(
        'Ficha Socioeconômica alterada - ',
        IF(COALESCE(OLD.nome_menor, '') != COALESCE(NEW.nome_menor, ''), CONCAT('Menor: ', OLD.nome_menor, ' → ', NEW.nome_menor, ' | '), ''),
        IF(COALESCE(OLD.entrevistado, '') != COALESCE(NEW.entrevistado, ''), CONCAT('Entrevistado: ', OLD.entrevistado, ' → ', NEW.entrevistado, ' | '), ''),
        IF(COALESCE(OLD.numero_comodos, 0) != COALESCE(NEW.numero_comodos, 0), CONCAT('Cômodos: ', COALESCE(OLD.numero_comodos, 0), ' → ', COALESCE(NEW.numero_comodos, 0), ' | '), ''),
        IF(COALESCE(OLD.renda_familiar, 0) != COALESCE(NEW.renda_familiar, 0), CONCAT('Renda: R$ ', COALESCE(OLD.renda_familiar, 0), ' → R$ ', COALESCE(NEW.renda_familiar, 0), ' | '), ''),
        IF(COALESCE(OLD.qtd_pessoas, 0) != COALESCE(NEW.qtd_pessoas, 0), CONCAT('Pessoas: ', COALESCE(OLD.qtd_pessoas, 0), ' → ', COALESCE(NEW.qtd_pessoas, 0), ' | '), ''),
        IF(COALESCE(OLD.construcao, '') != COALESCE(NEW.construcao, ''), CONCAT('Construção: ', OLD.construcao, ' → ', NEW.construcao, ' | '), ''),
        IF(COALESCE(OLD.moradia, '') != COALESCE(NEW.moradia, ''), CONCAT('Moradia: ', OLD.moradia, ' → ', NEW.moradia, ' | '), ''),
        IF(COALESCE(OLD.residencia, '') != COALESCE(NEW.residencia, ''), CONCAT('Residência: ', OLD.residencia, ' → ', NEW.residencia, ' | '), ''),
        IF(COALESCE(OLD.agua, 0) != COALESCE(NEW.agua, 0), CONCAT('Água: ', OLD.agua, ' → ', NEW.agua, ' | '), ''),
        IF(COALESCE(OLD.esgoto, 0) != COALESCE(NEW.esgoto, 0), CONCAT('Esgoto: ', OLD.esgoto, ' → ', NEW.esgoto, ' | '), ''),
        IF(COALESCE(OLD.energia, 0) != COALESCE(NEW.energia, 0), CONCAT('Energia: ', OLD.energia, ' → ', NEW.energia, ' | '), ''),
        IF(COALESCE(OLD.bolsa_familia, 0) != COALESCE(NEW.bolsa_familia, 0), CONCAT('Bolsa Família: ', OLD.bolsa_familia, ' → ', NEW.bolsa_familia, ' | '), ''),
        IF(COALESCE(OLD.auxilio_brasil, 0) != COALESCE(NEW.auxilio_brasil, 0), CONCAT('Auxílio Brasil: ', OLD.auxilio_brasil, ' → ', NEW.auxilio_brasil, ' | '), ''),
        IF(COALESCE(OLD.bpc, 0) != COALESCE(NEW.bpc, 0), CONCAT('BPC: ', OLD.bpc, ' → ', NEW.bpc, ' | '), ''),
        IF(COALESCE(OLD.renda_per_capita, 0) != COALESCE(NEW.renda_per_capita, 0), CONCAT('Renda Per Capita: R$ ', COALESCE(OLD.renda_per_capita, 0), ' → R$ ', COALESCE(NEW.renda_per_capita, 0), ' | '), '')
    ),
    JSON_OBJECT(
        'nome_menor', IF(COALESCE(OLD.nome_menor, '') != COALESCE(NEW.nome_menor, ''), OLD.nome_menor, NULL),
        'entrevistado', IF(COALESCE(OLD.entrevistado, '') != COALESCE(NEW.entrevistado, ''), OLD.entrevistado, NULL),
        'numero_comodos', IF(COALESCE(OLD.numero_comodos, 0) != COALESCE(NEW.numero_comodos, 0), OLD.numero_comodos, NULL),
        'renda_familiar', IF(COALESCE(OLD.renda_familiar, 0) != COALESCE(NEW.renda_familiar, 0), OLD.renda_familiar, NULL),
        'qtd_pessoas', IF(COALESCE(OLD.qtd_pessoas, 0) != COALESCE(NEW.qtd_pessoas, 0), OLD.qtd_pessoas, NULL),
        'construcao', IF(COALESCE(OLD.construcao, '') != COALESCE(NEW.construcao, ''), OLD.construcao, NULL),
        'moradia', IF(COALESCE(OLD.moradia, '') != COALESCE(NEW.moradia, ''), OLD.moradia, NULL),
        'residencia', IF(COALESCE(OLD.residencia, '') != COALESCE(NEW.residencia, ''), OLD.residencia, NULL),
        'agua', IF(COALESCE(OLD.agua, 0) != COALESCE(NEW.agua, 0), OLD.agua, NULL),
        'esgoto', IF(COALESCE(OLD.esgoto, 0) != COALESCE(NEW.esgoto, 0), OLD.esgoto, NULL),
        'energia', IF(COALESCE(OLD.energia, 0) != COALESCE(NEW.energia, 0), OLD.energia, NULL),
        'bolsa_familia', IF(COALESCE(OLD.bolsa_familia, 0) != COALESCE(NEW.bolsa_familia, 0), OLD.bolsa_familia, NULL),
        'auxilio_brasil', IF(COALESCE(OLD.auxilio_brasil, 0) != COALESCE(NEW.auxilio_brasil, 0), OLD.auxilio_brasil, NULL),
        'bpc', IF(COALESCE(OLD.bpc, 0) != COALESCE(NEW.bpc, 0), OLD.bpc, NULL),
        'renda_per_capita', IF(COALESCE(OLD.renda_per_capita, 0) != COALESCE(NEW.renda_per_capita, 0), OLD.renda_per_capita, NULL)
    ),
    JSON_OBJECT(
        'nome_menor', IF(COALESCE(OLD.nome_menor, '') != COALESCE(NEW.nome_menor, ''), NEW.nome_menor, NULL),
        'entrevistado', IF(COALESCE(OLD.entrevistado, '') != COALESCE(NEW.entrevistado, ''), NEW.entrevistado, NULL),
        'numero_comodos', IF(COALESCE(OLD.numero_comodos, 0) != COALESCE(NEW.numero_comodos, 0), NEW.numero_comodos, NULL),
        'renda_familiar', IF(COALESCE(OLD.renda_familiar, 0) != COALESCE(NEW.renda_familiar, 0), NEW.renda_familiar, NULL),
        'qtd_pessoas', IF(COALESCE(OLD.qtd_pessoas, 0) != COALESCE(NEW.qtd_pessoas, 0), NEW.qtd_pessoas, NULL),
        'construcao', IF(COALESCE(OLD.construcao, '') != COALESCE(NEW.construcao, ''), NEW.construcao, NULL),
        'moradia', IF(COALESCE(OLD.moradia, '') != COALESCE(NEW.moradia, ''), NEW.moradia, NULL),
        'residencia', IF(COALESCE(OLD.residencia, '') != COALESCE(NEW.residencia, ''), NEW.residencia, NULL),
        'agua', IF(COALESCE(OLD.agua, 0) != COALESCE(NEW.agua, 0), NEW.agua, NULL),
        'esgoto', IF(COALESCE(OLD.esgoto, 0) != COALESCE(NEW.esgoto, 0), NEW.esgoto, NULL),
        'energia', IF(COALESCE(OLD.energia, 0) != COALESCE(NEW.energia, 0), NEW.energia, NULL),
        'bolsa_familia', IF(COALESCE(OLD.bolsa_familia, 0) != COALESCE(NEW.bolsa_familia, 0), NEW.bolsa_familia, NULL),
        'auxilio_brasil', IF(COALESCE(OLD.auxilio_brasil, 0) != COALESCE(NEW.auxilio_brasil, 0), NEW.auxilio_brasil, NULL),
        'bpc', IF(COALESCE(OLD.bpc, 0) != COALESCE(NEW.bpc, 0), NEW.bpc, NULL),
        'renda_per_capita', IF(COALESCE(OLD.renda_per_capita, 0) != COALESCE(NEW.renda_per_capita, 0), NEW.renda_per_capita, NULL)
    ),
    'UPDATE',
    'ficha_socioeconomico',
    @usuario_id,
    NEW.idficha,
    'MULTIPLOS_CAMPOS',
    @ip_usuario
);
$$

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
        'numero_comodos', OLD.numero_comodos,
        'construcao', OLD.construcao,
        'moradia', OLD.moradia,
        'residencia', OLD.residencia,
        'agua', OLD.agua,
        'esgoto', OLD.esgoto,
        'energia', OLD.energia,
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
$$

DELIMITER ;
