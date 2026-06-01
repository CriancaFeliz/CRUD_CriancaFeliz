-- =====================================================
-- MIGRAÇÃO DO BANCO DE DADOS - CRIANÇA FELIZ
-- Versão Alinhada com SETUP_COMPLETO_FINAL.sql
-- =====================================================

CREATE DATABASE IF NOT EXISTS criancafeliz CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE criancafeliz;

-- =====================================================
-- TABELAS
-- =====================================================

CREATE TABLE IF NOT EXISTS `agenda` (
  `id_notificacao` int(11) NOT NULL AUTO_INCREMENT,
  `mensagem` varchar(255) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `lida` tinyint(1) DEFAULT NULL,
  `data_envio` datetime DEFAULT NULL,
  PRIMARY KEY (`id_notificacao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `responsavel` (
  `idresponsavel` int(11) NOT NULL AUTO_INCREMENT,
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
  `cep` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`idresponsavel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `usuario` (
  `idusuario` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `Senha` varchar(255) DEFAULT NULL,
  `nivel` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`idusuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `atendido` (
  `idatendido` int(11) NOT NULL AUTO_INCREMENT,
  `status` varchar(20) DEFAULT 'Ativo',
  `data_cadastro` date DEFAULT NULL,
  `data_acolhimento` date DEFAULT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `rg` varchar(20) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `id_responsavel` int(11) DEFAULT NULL,
  PRIMARY KEY (`idatendido`),
  KEY `id_responsavel` (`id_responsavel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  KEY `id_psicologo` (`id_psicologo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `desligamento` (
  `id_desligamento` int(11) NOT NULL AUTO_INCREMENT,
  `id_atendido` int(11) NOT NULL,
  `motivo` varchar(100) NOT NULL,
  `tipo_motivo` enum('idade','excesso_faltas','pedido_familia','transferencia','outros') NOT NULL,
  `data_desligamento` date NOT NULL,
  `observacao` text DEFAULT NULL,
  `automatico` tinyint(1) DEFAULT 0,
  `pode_retornar` tinyint(1) DEFAULT 1,
  `desligado_por` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_desligamento`),
  UNIQUE KEY `unique_desligamento` (`id_atendido`),
  KEY `desligado_por` (`desligado_por`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `ficha_socioeconomico` (
  `idficha` int(11) NOT NULL AUTO_INCREMENT,
  `id_atendido` int(11) NOT NULL,
  `nome_menor` varchar(255) DEFAULT NULL,
  `entrevistado` varchar(255) DEFAULT NULL,
  `residencia` varchar(100) DEFAULT NULL,
  `construcao` varchar(100) DEFAULT NULL,
  `numero_comodos` int(11) DEFAULT NULL,
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
  `data_atualizacao` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`idficha`),
  UNIQUE KEY `id_atendido` (`id_atendido`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `despesas` (
  `id_despesa` int(11) NOT NULL AUTO_INCREMENT,
  `id_ficha` int(11) NOT NULL,
  `valor_despesa` decimal(10,2) DEFAULT 0,
  `tipo_renda` varchar(100) DEFAULT NULL,
  `valor_renda` decimal(10,2) DEFAULT 0,
  `data_criacao` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_despesa`),
  KEY `id_ficha` (`id_ficha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `dias_atendimento` (
  `id_dia` int(11) NOT NULL AUTO_INCREMENT,
  `data_atendimento` date NOT NULL,
  `descricao` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_dia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `documento` (
  `iddocumento` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(50) DEFAULT NULL,
  `arquivo` varchar(255) DEFAULT NULL,
  `data_upload` datetime DEFAULT NULL,
  `IDatendido` int(11) DEFAULT NULL,
  PRIMARY KEY (`iddocumento`),
  KEY `IDatendido` (`IDatendido`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `encontro` (
  `id_encontro` int(11) NOT NULL AUTO_INCREMENT,
  `Dataencontro` date DEFAULT NULL,
  `ID_usuario` int(11) DEFAULT NULL,
  `evolucao` varchar(255) DEFAULT NULL,
  `id_atendido` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_encontro`),
  KEY `ID_usuario` (`ID_usuario`),
  KEY `id_atendido` (`id_atendido`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `familia` (
  `id_familia` int(11) NOT NULL AUTO_INCREMENT,
  `id_ficha` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `parentesco` varchar(100) NOT NULL,
  `data_nasc` date DEFAULT NULL,
  `formacao` varchar(100) DEFAULT NULL,
  `renda` decimal(10,2) DEFAULT 0,
  `data_criacao` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_familia`),
  KEY `id_ficha` (`id_ficha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `frequencia_dia` (
  `id_frequencia_dia` int(11) NOT NULL AUTO_INCREMENT,
  `id_atendido` int(11) NOT NULL,
  `data` date NOT NULL,
  `status` enum('P','F','J') NOT NULL,
  `justificativa` text DEFAULT NULL,
  `observacao` text DEFAULT NULL,
  `registrado_por` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_frequencia_dia`),
  UNIQUE KEY `unique_frequencia_dia` (`id_atendido`,`data`),
  KEY `registrado_por` (`registrado_por`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `oficina` (
  `id_oficina` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `dia_semana` enum('Segunda','Terça','Quarta','Quinta','Sexta','Sábado','Domingo') DEFAULT NULL,
  `horario_inicio` time DEFAULT NULL,
  `horario_fim` time DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_oficina`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `frequencia_oficina` (
  `id_frequencia` int(11) NOT NULL AUTO_INCREMENT,
  `id_atendido` int(11) NOT NULL,
  `id_oficina` int(11) NOT NULL,
  `data` date NOT NULL,
  `status` enum('P','F','J') NOT NULL,
  `justificativa` text DEFAULT NULL,
  `observacao` text DEFAULT NULL,
  `registrado_por` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_frequencia`),
  UNIQUE KEY `unique_frequencia` (`id_atendido`,`id_oficina`,`data`),
  KEY `registrado_por` (`registrado_por`),
  KEY `idx_oficina` (`id_oficina`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `log` (
  `id_log` int(11) NOT NULL AUTO_INCREMENT,
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
  `dados_completos` longtext DEFAULT NULL,
  PRIMARY KEY (`id_log`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- RESTRIÇÕES DE CHAVE ESTRANGEIRA (FOREIGN KEYS)
-- =====================================================

ALTER TABLE `atendido` ADD CONSTRAINT `atendido_ibfk_1` FOREIGN KEY (`id_responsavel`) REFERENCES `responsavel` (`idresponsavel`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `anotacao_psicologica`
  ADD CONSTRAINT `anotacao_psicologica_ibfk_1` FOREIGN KEY (`id_atendido`) REFERENCES `atendido` (`idatendido`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `anotacao_psicologica_ibfk_2` FOREIGN KEY (`id_psicologo`) REFERENCES `usuario` (`idusuario`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `desligamento` 
  ADD CONSTRAINT `desligamento_ibfk_1` FOREIGN KEY (`id_atendido`) REFERENCES `atendido` (`idatendido`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `desligamento_ibfk_2` FOREIGN KEY (`desligado_por`) REFERENCES `usuario` (`idusuario`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `despesas` ADD CONSTRAINT `despesas_ibfk_1` FOREIGN KEY (`id_ficha`) REFERENCES `ficha_socioeconomico` (`idficha`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `documento` ADD CONSTRAINT `documento_ibfk_1` FOREIGN KEY (`IDatendido`) REFERENCES `atendido` (`idatendido`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `encontro` 
  ADD CONSTRAINT `encontro_ibfk_1` FOREIGN KEY (`ID_usuario`) REFERENCES `usuario` (`idusuario`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `encontro_ibfk_2` FOREIGN KEY (`id_atendido`) REFERENCES `atendido` (`idatendido`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `familia` ADD CONSTRAINT `familia_ibfk_1` FOREIGN KEY (`id_ficha`) REFERENCES `ficha_socioeconomico` (`idficha`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `ficha_socioeconomico` ADD CONSTRAINT `ficha_socioeconomico_ibfk_1` FOREIGN KEY (`id_atendido`) REFERENCES `atendido` (`idatendido`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `frequencia_dia` 
  ADD CONSTRAINT `frequencia_dia_ibfk_1` FOREIGN KEY (`id_atendido`) REFERENCES `atendido` (`idatendido`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frequencia_dia_ibfk_2` FOREIGN KEY (`registrado_por`) REFERENCES `usuario` (`idusuario`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `frequencia_oficina` 
  ADD CONSTRAINT `frequencia_oficina_ibfk_1` FOREIGN KEY (`id_atendido`) REFERENCES `atendido` (`idatendido`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frequencia_oficina_ibfk_2` FOREIGN KEY (`id_oficina`) REFERENCES `oficina` (`id_oficina`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frequencia_oficina_ibfk_3` FOREIGN KEY (`registrado_por`) REFERENCES `usuario` (`idusuario`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `log` ADD CONSTRAINT `log_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`idusuario`) ON DELETE CASCADE ON UPDATE CASCADE;

-- =====================================================
-- DADOS INICIAIS
-- =====================================================

INSERT IGNORE INTO `usuario` (`idusuario`, `nome`, `email`, `Senha`, `nivel`, `status`) VALUES
(1, 'Administrador', 'admin@criancafeliz.org', '$2y$12$GZIwUJ/.t.yQ7DICaT137OQgjAv.OsOx8BAp6eah81iSXcejwxwz6', 'admin', 'Ativo');

INSERT IGNORE INTO `responsavel` (`idresponsavel`, `nome`, `cpf`, `telefone`, `email`, `parentesco`) VALUES
(1, 'Maria Souza', '123.456.789-00', '(11) 91234-5678', 'maria.souza@example.com', 'Mãe'),
(2, 'João Pereira', '987.654.321-00', '(11) 99876-5432', 'joao.pereira@example.com', 'Pai');

INSERT IGNORE INTO `atendido` (`idatendido`, `status`, `data_cadastro`, `data_acolhimento`, `nome`, `data_nascimento`, `cpf`, `id_responsavel`) VALUES
(1, 'Ativo', '2025-10-18', '2025-10-18', 'Ana Beatriz Silva', '2012-05-14', '111.222.333-44', 1),
(2, 'Ativo', '2025-10-18', '2025-10-18', 'Carlos Eduardo Santos', '2010-09-02', NULL, 2),
(3, 'Ativo', '2025-10-18', '2025-10-18', 'Luiza Ferreira', '2013-03-28', NULL, NULL);

INSERT IGNORE INTO `oficina` (`id_oficina`, `nome`, `descricao`, `dia_semana`, `horario_inicio`, `horario_fim`, `ativo`) VALUES
(1, 'Reforço Escolar', 'Aulas de reforço para crianças', 'Terça', '14:00:00', '16:00:00', 1),
(2, 'Artes', 'Oficina de artes e artesanato', 'Terça', '14:00:00', '16:00:00', 1),
(3, 'Esportes', 'Atividades esportivas', 'Quarta', '14:00:00', '16:00:00', 1),
(4, 'Música', 'Aulas de música e canto', 'Quinta', '14:00:00', '16:00:00', 1),
(5, 'Dança', 'Oficina de dança', 'Sexta', '14:00:00', '16:00:00', 1),
(6, 'Teatro', 'Oficina de teatro', 'Sábado', '09:00:00', '11:00:00', 1);

-- Nota: Triggers e Procedures adicionais devem ser instalados através
-- do arquivo SETUP_COMPLETO_FINAL.sql pelo phpMyAdmin se necessário.
