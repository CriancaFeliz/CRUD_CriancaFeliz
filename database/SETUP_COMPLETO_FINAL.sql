-- =====================================================
-- SETUP COMPLETO - CRIANÇA FELIZ
-- =====================================================
-- Script único para setup 100% funcional do projeto
-- Execute este arquivo uma única vez no phpMyAdmin
-- Versão: 1.0 - Dezembro 2025
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- =====================================================
-- PARTE 1: BANCO DE DADOS BASE
-- =====================================================

DELIMITER $$

CREATE DEFINER=`root`@`localhost` PROCEDURE `DesligarPorExcessoFaltas` ()   BEGIN
    INSERT INTO Desligamento (id_atendido, motivo, tipo_motivo, data_desligamento, automatico)
    SELECT 
        a.idatendido,
        'Desligamento automático por excesso de faltas',
        'excesso_faltas',
        CURDATE(),
        TRUE
    FROM 
        Atendido a
    LEFT JOIN 
        Frequencia_Dia fd ON a.idatendido = fd.id_atendido
    WHERE 
        a.status = 'Ativo'
        AND NOT EXISTS (SELECT 1 FROM Desligamento d WHERE d.id_atendido = a.idatendido)
    GROUP BY 
        a.idatendido
    HAVING 
        COUNT(CASE WHEN fd.status = 'F' THEN 1 END) >= 3;
        
    UPDATE Atendido a
    INNER JOIN Desligamento d ON a.idatendido = d.id_atendido
    SET a.status = 'Desligado'
    WHERE d.automatico = TRUE;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `RegistrarFaltaAutomatica` (IN `p_id_atendido` INT, IN `p_data` DATE)   BEGIN
    INSERT INTO Frequencia_Dia (id_atendido, data, status)
    VALUES (p_id_atendido, p_data, 'F')
    ON DUPLICATE KEY UPDATE 
        status = IF(status = 'P', status, 'F');
END$$

DELIMITER ;

-- =====================================================
-- TABELAS
-- =====================================================

CREATE TABLE IF NOT EXISTS `agenda` (
  `id_notificacao` int(11) NOT NULL,
  `mensagem` varchar(255) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `lida` tinyint(1) DEFAULT NULL,
  `data_envio` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `atendido` (
  `idatendido` int(11) NOT NULL,
  `status` varchar(20) DEFAULT NULL,
  `data_cadastro` date DEFAULT NULL,
  `data_acolhimento` date DEFAULT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `faixa_etaria` varchar(20) DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `rg` varchar(20) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `id_responsavel` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `desligamento` (
  `id_desligamento` int(11) NOT NULL,
  `id_atendido` int(11) NOT NULL,
  `motivo` varchar(100) NOT NULL,
  `tipo_motivo` enum('idade','excesso_faltas','pedido_familia','transferencia','outros') NOT NULL,
  `data_desligamento` date NOT NULL,
  `observacao` text DEFAULT NULL,
  `automatico` tinyint(1) DEFAULT 0,
  `pode_retornar` tinyint(1) DEFAULT 1,
  `desligado_por` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `despesas` (
  `id_despesa` int(11) NOT NULL,
  `id_ficha` int(11) NOT NULL,
  `valor_despesa` decimal(10,2) DEFAULT 0,
  `tipo_renda` varchar(100) DEFAULT NULL,
  `valor_renda` decimal(10,2) DEFAULT 0,
  `data_criacao` timestamp DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `dias_atendimento` (
  `id_dia` int(11) NOT NULL,
  `data_atendimento` date NOT NULL,
  `descricao` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `documento` (
  `iddocumento` int(11) NOT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `arquivo` varchar(255) DEFAULT NULL,
  `data_upload` datetime DEFAULT NULL,
  `IDatendido` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `encontro` (
  `id_encontro` int(11) NOT NULL,
  `Dataencontro` date DEFAULT NULL,
  `ID_usuario` int(11) DEFAULT NULL,
  `evolucao` varchar(255) DEFAULT NULL,
  `id_atendido` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `familia` (
  `id_familia` int(11) NOT NULL,
  `id_ficha` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `parentesco` varchar(100) NOT NULL,
  `data_nasc` date DEFAULT NULL,
  `formacao` varchar(100) DEFAULT NULL,
  `renda` decimal(10,2) DEFAULT 0,
  `data_criacao` timestamp DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `ficha_socioeconomico` (
  `idficha` int(11) NOT NULL,
  `id_atendido` int(11) NOT NULL,
  `nome_menor` varchar(255) DEFAULT NULL,
  `entrevistado` varchar(255) DEFAULT NULL,
  `residencia` varchar(100) DEFAULT NULL,
  `construcao` varchar(100) DEFAULT NULL,
  `numero_comodos` int(11) DEFAULT NULL,
  `nr_comodos` int(11) DEFAULT NULL,
  `assistente_social` varchar(255) DEFAULT NULL,
  `cadunico` varchar(100) DEFAULT NULL,
  `agua` tinyint(1) DEFAULT 0,
  `esgoto` tinyint(1) DEFAULT 0,
  `energia` tinyint(1) DEFAULT 0,
  `renda_familiar` decimal(10,2) DEFAULT 0,
  `renda_per_capita` decimal(10,2) DEFAULT NULL,
  `qtd_pessoas` int(11) DEFAULT 0,
  `cond_residencia` varchar(100) DEFAULT NULL,
  `moradia` varchar(100) DEFAULT NULL,
  `nr_veiculos` int(11) DEFAULT 0,
  `observacoes` longtext DEFAULT NULL,
  `bolsa_familia` tinyint(1) DEFAULT 0,
  `auxilio_brasil` tinyint(1) DEFAULT 0,
  `bpc` tinyint(1) DEFAULT 0,
  `auxilio_emergencial` tinyint(1) DEFAULT 0,
  `seguro_desemprego` tinyint(1) DEFAULT 0,
  `aposentadoria` tinyint(1) DEFAULT 0,
  `data_criacao` timestamp DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `frequencia_dia` (
  `id_frequencia_dia` int(11) NOT NULL,
  `id_atendido` int(11) NOT NULL,
  `data` date NOT NULL,
  `status` enum('P','F','J') NOT NULL COMMENT 'P=Presente, F=Falta, J=Justificada',
  `justificativa` text DEFAULT NULL,
  `observacao` text DEFAULT NULL,
  `registrado_por` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `frequencia_oficina` (
  `id_frequencia` int(11) NOT NULL,
  `id_atendido` int(11) NOT NULL,
  `id_oficina` int(11) NOT NULL,
  `data` date NOT NULL,
  `status` enum('P','F','J') NOT NULL COMMENT 'P=Presente, F=Falta, J=Justificada',
  `justificativa` text DEFAULT NULL,
  `observacao` text DEFAULT NULL,
  `registrado_por` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `log` (
  `id_log` int(11) NOT NULL,
  `data_alteracao` datetime DEFAULT NULL,
  `registro_alt` varchar(255) DEFAULT NULL,
  `valor_anterior` longtext DEFAULT NULL,
  `valor_atual` longtext DEFAULT NULL,
  `acao` varchar(50) DEFAULT NULL,
  `tabela_afetada` varchar(100) DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `id_registro` int(11) DEFAULT NULL,
  `campo_alterado` varchar(255) DEFAULT NULL,
  `ip_usuario` varchar(45) DEFAULT NULL,
  `dados_completos` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `oficina` (
  `id_oficina` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `dia_semana` enum('Segunda','Terça','Quarta','Quinta','Sexta','Sábado','Domingo') DEFAULT NULL,
  `horario_inicio` time DEFAULT NULL,
  `horario_fim` time DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `presenca` (
  `id_presenca` int(11) NOT NULL,
  `id_sessao` int(11) NOT NULL,
  `id_atendido` int(11) NOT NULL,
  `status` enum('PRESENTE','FALTA','JUSTIFICADA') NOT NULL,
  `justificativa` varchar(255) DEFAULT NULL,
  `registrado_por` int(11) DEFAULT NULL,
  `registrado_em` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `responsavel` (
  `idresponsavel` int(11) NOT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `rg` varchar(20) DEFAULT NULL,
  `telefone` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `parentesco` varchar(50) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `sessao` (
  `id_sessao` int(11) NOT NULL,
  `data_sessao` date NOT NULL,
  `descricao` varchar(150) DEFAULT NULL,
  `criado_por` int(11) DEFAULT NULL,
  `data_criacao` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `usuario` (
  `idusuario` int(11) NOT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `Senha` varchar(100) DEFAULT NULL,
  `nivel` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- PARTE 2: TRIGGERS PARA LOGS
-- =====================================================

DROP TRIGGER IF EXISTS `log_ficha_socioeconomico_insert`;
DROP TRIGGER IF EXISTS `log_ficha_socioeconomico_update`;
DROP TRIGGER IF EXISTS `log_ficha_socioeconomico_delete`;

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
        IF(COALESCE(OLD.numero_comodos, OLD.nr_comodos, 0) != COALESCE(NEW.numero_comodos, NEW.nr_comodos, 0), CONCAT('Cômodos: ', COALESCE(OLD.numero_comodos, OLD.nr_comodos), ' → ', COALESCE(NEW.numero_comodos, NEW.nr_comodos), ' | '), ''),
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
        'numero_comodos', IF(COALESCE(OLD.numero_comodos, OLD.nr_comodos, 0) != COALESCE(NEW.numero_comodos, NEW.nr_comodos, 0), COALESCE(OLD.numero_comodos, OLD.nr_comodos), NULL),
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
        'numero_comodos', IF(COALESCE(OLD.numero_comodos, OLD.nr_comodos, 0) != COALESCE(NEW.numero_comodos, NEW.nr_comodos, 0), COALESCE(NEW.numero_comodos, NEW.nr_comodos), NULL),
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
        'numero_comodos', COALESCE(OLD.numero_comodos, OLD.nr_comodos),
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

-- =====================================================
-- PARTE 3: ÍNDICES
-- =====================================================

ALTER TABLE `agenda` ADD PRIMARY KEY (`id_notificacao`);
ALTER TABLE `atendido` ADD PRIMARY KEY (`idatendido`), ADD KEY `id_responsavel` (`id_responsavel`);
ALTER TABLE `desligamento` ADD PRIMARY KEY (`id_desligamento`), ADD UNIQUE KEY `unique_desligamento` (`id_atendido`), ADD KEY `desligado_por` (`desligado_por`), ADD KEY `idx_atendido_deslig` (`id_atendido`), ADD KEY `idx_tipo_motivo` (`tipo_motivo`), ADD KEY `idx_data_desligamento` (`data_desligamento`), ADD KEY `idx_automatico` (`automatico`);
ALTER TABLE `despesas` ADD PRIMARY KEY (`id_despesa`), ADD KEY `id_ficha` (`id_ficha`);
ALTER TABLE `dias_atendimento` ADD PRIMARY KEY (`id_dia`);
ALTER TABLE `documento` ADD PRIMARY KEY (`iddocumento`), ADD KEY `IDatendido` (`IDatendido`);
ALTER TABLE `encontro` ADD PRIMARY KEY (`id_encontro`), ADD KEY `ID_usuario` (`ID_usuario`), ADD KEY `id_atendido` (`id_atendido`);
ALTER TABLE `familia` ADD PRIMARY KEY (`id_familia`), ADD KEY `id_ficha` (`id_ficha`);
ALTER TABLE `ficha_socioeconomico` ADD PRIMARY KEY (`idficha`), ADD UNIQUE KEY `id_atendido` (`id_atendido`), ADD INDEX `idx_nome_menor` (`nome_menor`), ADD INDEX `idx_renda_familiar` (`renda_familiar`);
ALTER TABLE `frequencia_dia` ADD PRIMARY KEY (`id_frequencia_dia`), ADD UNIQUE KEY `unique_frequencia_dia` (`id_atendido`,`data`), ADD KEY `registrado_por` (`registrado_por`), ADD KEY `idx_atendido_dia` (`id_atendido`), ADD KEY `idx_data_dia` (`data`), ADD KEY `idx_status_dia` (`status`), ADD KEY `idx_data_status_dia` (`data`,`status`);
ALTER TABLE `frequencia_oficina` ADD PRIMARY KEY (`id_frequencia`), ADD UNIQUE KEY `unique_frequencia` (`id_atendido`,`id_oficina`,`data`), ADD KEY `registrado_por` (`registrado_por`), ADD KEY `idx_atendido` (`id_atendido`), ADD KEY `idx_oficina` (`id_oficina`), ADD KEY `idx_data` (`data`), ADD KEY `idx_status` (`status`), ADD KEY `idx_data_status` (`data`,`status`);
ALTER TABLE `log` ADD PRIMARY KEY (`id_log`), ADD KEY `id_usuario` (`id_usuario`), ADD INDEX `idx_dados_completos` (`dados_completos`(100));
ALTER TABLE `oficina` ADD PRIMARY KEY (`id_oficina`), ADD KEY `idx_ativo` (`ativo`), ADD KEY `idx_dia_semana` (`dia_semana`);
ALTER TABLE `presenca` ADD PRIMARY KEY (`id_presenca`), ADD UNIQUE KEY `uk_presenca_sessao_atendido` (`id_sessao`,`id_atendido`), ADD KEY `idx_presenca_atendido_status` (`id_atendido`,`status`), ADD KEY `idx_presenca_sessao_status` (`id_sessao`,`status`), ADD KEY `idx_presenca_registrado_por` (`registrado_por`);
ALTER TABLE `responsavel` ADD PRIMARY KEY (`idresponsavel`);
ALTER TABLE `sessao` ADD PRIMARY KEY (`id_sessao`), ADD UNIQUE KEY `uk_sessao_data` (`data_sessao`), ADD KEY `idx_sessao_criado_por` (`criado_por`);
ALTER TABLE `usuario` ADD PRIMARY KEY (`idusuario`);

-- =====================================================
-- PARTE 4: AUTO_INCREMENT
-- =====================================================

ALTER TABLE `agenda` MODIFY `id_notificacao` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `atendido` MODIFY `idatendido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
ALTER TABLE `desligamento` MODIFY `id_desligamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `despesas` MODIFY `id_despesa` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `dias_atendimento` MODIFY `id_dia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `documento` MODIFY `iddocumento` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `encontro` MODIFY `id_encontro` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `familia` MODIFY `id_familia` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `ficha_socioeconomico` MODIFY `idficha` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `frequencia_dia` MODIFY `id_frequencia_dia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
ALTER TABLE `frequencia_oficina` MODIFY `id_frequencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
ALTER TABLE `log` MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `oficina` MODIFY `id_oficina` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
ALTER TABLE `presenca` MODIFY `id_presenca` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `responsavel` MODIFY `idresponsavel` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
ALTER TABLE `sessao` MODIFY `id_sessao` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `usuario` MODIFY `idusuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

-- =====================================================
-- PARTE 5: FOREIGN KEYS
-- =====================================================

ALTER TABLE `atendido` ADD CONSTRAINT `atendido_ibfk_1` FOREIGN KEY (`id_responsavel`) REFERENCES `responsavel` (`idresponsavel`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `desligamento` ADD CONSTRAINT `desligamento_ibfk_1` FOREIGN KEY (`id_atendido`) REFERENCES `atendido` (`idatendido`) ON DELETE CASCADE ON UPDATE CASCADE, ADD CONSTRAINT `desligamento_ibfk_2` FOREIGN KEY (`desligado_por`) REFERENCES `usuario` (`idusuario`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `despesas` ADD CONSTRAINT `despesas_ibfk_1` FOREIGN KEY (`id_ficha`) REFERENCES `ficha_socioeconomico` (`idficha`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `documento` ADD CONSTRAINT `documento_ibfk_1` FOREIGN KEY (`IDatendido`) REFERENCES `atendido` (`idatendido`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `encontro` ADD CONSTRAINT `encontro_ibfk_1` FOREIGN KEY (`ID_usuario`) REFERENCES `usuario` (`idusuario`) ON DELETE SET NULL ON UPDATE CASCADE, ADD CONSTRAINT `encontro_ibfk_2` FOREIGN KEY (`id_atendido`) REFERENCES `atendido` (`idatendido`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `familia` ADD CONSTRAINT `familia_ibfk_1` FOREIGN KEY (`id_ficha`) REFERENCES `ficha_socioeconomico` (`idficha`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `ficha_socioeconomico` ADD CONSTRAINT `ficha_socioeconomico_ibfk_1` FOREIGN KEY (`id_atendido`) REFERENCES `atendido` (`idatendido`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `frequencia_dia` ADD CONSTRAINT `frequencia_dia_ibfk_1` FOREIGN KEY (`id_atendido`) REFERENCES `atendido` (`idatendido`) ON DELETE CASCADE ON UPDATE CASCADE, ADD CONSTRAINT `frequencia_dia_ibfk_2` FOREIGN KEY (`registrado_por`) REFERENCES `usuario` (`idusuario`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `frequencia_oficina` ADD CONSTRAINT `frequencia_oficina_ibfk_1` FOREIGN KEY (`id_atendido`) REFERENCES `atendido` (`idatendido`) ON DELETE CASCADE ON UPDATE CASCADE, ADD CONSTRAINT `frequencia_oficina_ibfk_2` FOREIGN KEY (`id_oficina`) REFERENCES `oficina` (`id_oficina`) ON DELETE CASCADE ON UPDATE CASCADE, ADD CONSTRAINT `frequencia_oficina_ibfk_3` FOREIGN KEY (`registrado_por`) REFERENCES `usuario` (`idusuario`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `log` ADD CONSTRAINT `log_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`idusuario`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `presenca` ADD CONSTRAINT `fk_presenca_atendido` FOREIGN KEY (`id_atendido`) REFERENCES `atendido` (`idatendido`) ON DELETE CASCADE ON UPDATE CASCADE, ADD CONSTRAINT `fk_presenca_sessao` FOREIGN KEY (`id_sessao`) REFERENCES `sessao` (`id_sessao`) ON DELETE CASCADE ON UPDATE CASCADE, ADD CONSTRAINT `fk_presenca_usuario` FOREIGN KEY (`registrado_por`) REFERENCES `usuario` (`idusuario`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `sessao` ADD CONSTRAINT `fk_sessao_usuario` FOREIGN KEY (`criado_por`) REFERENCES `usuario` (`idusuario`) ON DELETE SET NULL ON UPDATE CASCADE;

-- =====================================================
-- PARTE 6: DADOS INICIAIS
-- =====================================================

INSERT IGNORE INTO `usuario` (`idusuario`, `nome`, `email`, `Senha`, `nivel`, `status`) VALUES
(1, 'Administrador', 'admin@criancafeliz.org', '$2y$10$qWMLn9zbVgS5WQhPuMrZue8CbVOxQ.bUOFSZH3BG0Wcdp7ciMTwMi', 'admin', 'Ativo');

INSERT IGNORE INTO `responsavel` (`idresponsavel`, `nome`, `cpf`, `telefone`, `email`, `parentesco`) VALUES
(1, 'Maria Souza', '123.456.789-00', '(11) 91234-5678', 'maria.souza@example.com', 'Mãe'),
(2, 'João Pereira', '987.654.321-00', '(11) 99876-5432', 'joao.pereira@example.com', 'Pai');

INSERT IGNORE INTO `atendido` (`idatendido`, `status`, `data_cadastro`, `data_acolhimento`, `nome`, `data_nascimento`, `faixa_etaria`, `cpf`, `id_responsavel`) VALUES
(1, 'Ativo', '2025-10-18', '2025-10-18', 'Ana Beatriz Silva', '2012-05-14', '12-14', '111.222.333-44', 1),
(2, 'Ativo', '2025-10-18', '2025-10-18', 'Carlos Eduardo Santos', '2010-09-02', '15-17', NULL, 2),
(3, 'Ativo', '2025-10-18', '2025-10-18', 'Luiza Ferreira', '2013-03-28', '12-14', NULL, NULL);

INSERT IGNORE INTO `oficina` (`id_oficina`, `nome`, `descricao`, `dia_semana`, `horario_inicio`, `horario_fim`, `ativo`) VALUES
(1, 'Reforço Escolar', 'Aulas de reforço para crianças', 'Terça', '14:00:00', '16:00:00', 1),
(2, 'Artes', 'Oficina de artes e artesanato', 'Terça', '14:00:00', '16:00:00', 1),
(3, 'Esportes', 'Atividades esportivas', 'Quarta', '14:00:00', '16:00:00', 1),
(4, 'Música', 'Aulas de música e canto', 'Quinta', '14:00:00', '16:00:00', 1),
(5, 'Dança', 'Oficina de dança', 'Sexta', '14:00:00', '16:00:00', 1),
(6, 'Teatro', 'Oficina de teatro', 'Sábado', '09:00:00', '11:00:00', 1);

-- =====================================================
-- FIM DO SETUP COMPLETO
-- =====================================================

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET CHARACTER_SET_COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- =====================================================
-- SCRIPT FINALIZADO COM SUCESSO!
-- =====================================================
-- Este script contém:
-- ✅ Banco de dados base completo
-- ✅ Todas as tabelas necessárias
-- ✅ Triggers para logs da ficha socioeconômica
-- ✅ Índices para performance
-- ✅ Foreign keys para integridade
-- ✅ Dados iniciais (usuário admin, responsáveis, atendidos, oficinas)
-- =====================================================
