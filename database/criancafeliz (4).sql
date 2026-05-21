-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 28/10/2025 às 00:17
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `criancafeliz`
--

DELIMITER $$
--
-- Procedimentos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `DesligarPorExcessoFaltas` ()   BEGIN
    -- Desligar atendidos com 3 ou mais faltas não justificadas
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
        
    -- Atualizar status dos atendidos desligados
    UPDATE Atendido a
    INNER JOIN Desligamento d ON a.idatendido = d.id_atendido
    SET a.status = 'Desligado'
    WHERE d.automatico = TRUE;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `RegistrarFaltaAutomatica` (IN `p_id_atendido` INT, IN `p_data` DATE)   BEGIN
    -- Registra falta no dia se não houver registro
    INSERT INTO Frequencia_Dia (id_atendido, data, status)
    VALUES (p_id_atendido, p_data, 'F')
    ON DUPLICATE KEY UPDATE 
        status = IF(status = 'P', status, 'F');
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `agenda`
--

CREATE TABLE `agenda` (
  `id_notificacao` int(11) NOT NULL,
  `mensagem` varchar(255) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `lida` tinyint(1) DEFAULT NULL,
  `data_envio` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `atendido`
--

CREATE TABLE `atendido` (
  `idatendido` int(11) NOT NULL,
  `status` varchar(20) DEFAULT NULL,
  `data_cadastro` date DEFAULT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `faixa_etaria` varchar(20) DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `rg` varchar(20) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `id_responsavel` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `atendido`
--

INSERT INTO `atendido` (`idatendido`, `status`, `data_cadastro`, `nome`, `data_nascimento`, `faixa_etaria`, `cpf`, `rg`, `endereco`, `foto`, `id_responsavel`) VALUES
(1, 'Ativo', '2025-10-18', 'Ana Beatriz Silva', '2012-05-14', '12-14', '111.222.333-44', 'MG-12.345.678', 'Rua das Flores, 123 - Centro', NULL, 1),
(2, 'Ativo', '2025-10-18', 'Carlos Eduardo Santos', '2010-09-02', '15-17', NULL, NULL, 'Av. Brasil, 456 - Bairro Alto', NULL, 2),
(3, 'Ativo', '2025-10-18', 'Luiza Ferreira', '2013-03-28', '12-14', NULL, NULL, 'Rua Nova, 789 - Jardim', NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `atendidos_com_alerta`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `atendidos_com_alerta` (
`idatendido` int(11)
,`nome` varchar(100)
,`cpf` varchar(14)
,`total_faltas` bigint(21)
,`ultima_falta` date
,`nivel_alerta` varchar(7)
);

-- --------------------------------------------------------

--
-- Estrutura para tabela `desligamento`
--

CREATE TABLE `desligamento` (
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

-- --------------------------------------------------------

--
-- Estrutura para tabela `despesas`
--

CREATE TABLE `despesas` (
  `id_despesa` int(11) NOT NULL,
  `valor_despesa` decimal(10,2) DEFAULT NULL,
  `tipo_renda` varchar(50) DEFAULT NULL,
  `valor_renda` decimal(10,2) DEFAULT NULL,
  `id_ficha` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `dias_atendimento`
--

CREATE TABLE `dias_atendimento` (
  `id_dia` int(11) NOT NULL,
  `data_atendimento` date NOT NULL,
  `descricao` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `dias_atendimento`
--

INSERT INTO `dias_atendimento` (`id_dia`, `data_atendimento`, `descricao`) VALUES
(1, '2025-10-18', 'Encontro semanal'),
(2, '2025-10-25', 'Encontro semanal');

-- --------------------------------------------------------

--
-- Estrutura para tabela `documento`
--

CREATE TABLE `documento` (
  `iddocumento` int(11) NOT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `arquivo` varchar(255) DEFAULT NULL,
  `data_upload` datetime DEFAULT NULL,
  `IDatendido` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `encontro`
--

CREATE TABLE `encontro` (
  `id_encontro` int(11) NOT NULL,
  `Dataencontro` date DEFAULT NULL,
  `ID_usuario` int(11) DEFAULT NULL,
  `evolucao` varchar(255) DEFAULT NULL,
  `id_atendido` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `estatisticas_frequencia`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `estatisticas_frequencia` (
`idatendido` int(11)
,`nome` varchar(100)
,`total_presencas` bigint(21)
,`total_faltas` bigint(21)
,`total_justificadas` bigint(21)
,`total_registros` bigint(21)
,`percentual_presenca` decimal(26,2)
);

-- --------------------------------------------------------

--
-- Estrutura para tabela `familia`
--

CREATE TABLE `familia` (
  `id_familia` int(11) NOT NULL,
  `id_ficha` int(11) DEFAULT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `parentesco` varchar(50) DEFAULT NULL,
  `data_nasc` date DEFAULT NULL,
  `formacao` varchar(100) DEFAULT NULL,
  `renda` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `ficha_socioeconomico`
--

CREATE TABLE `ficha_socioeconomico` (
  `idficha` int(11) NOT NULL,
  `agua` tinyint(1) DEFAULT NULL,
  `esgoto` tinyint(1) DEFAULT NULL,
  `renda_familiar` decimal(10,2) DEFAULT NULL,
  `energia` tinyint(1) DEFAULT NULL,
  `qtd_pessoas` int(11) DEFAULT NULL,
  `cond_residencia` varchar(100) DEFAULT NULL,
  `moradia` varchar(100) DEFAULT NULL,
  `nr_veiculos` int(11) DEFAULT NULL,
  `observacoes` varchar(255) DEFAULT NULL,
  `entrevistado` varchar(100) DEFAULT NULL,
  `residencia` varchar(100) DEFAULT NULL,
  `nr_comodos` int(11) DEFAULT NULL,
  `construcao` varchar(100) DEFAULT NULL,
  `id_atendido` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `frequencia_dia`
--

CREATE TABLE `frequencia_dia` (
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

--
-- Despejando dados para a tabela `frequencia_dia`
--

INSERT INTO `frequencia_dia` (`id_frequencia_dia`, `id_atendido`, `data`, `status`, `justificativa`, `observacao`, `registrado_por`, `created_at`, `updated_at`) VALUES
(1, 1, '2025-10-27', 'P', NULL, NULL, 1, '2025-10-27 21:35:07', '2025-10-27 22:20:14'),
(3, 1, '2025-10-20', 'F', '', NULL, 1, '2025-10-27 21:36:24', '2025-10-27 21:36:24'),
(5, 1, '2025-10-29', 'F', '', NULL, 1, '2025-10-27 21:51:35', '2025-10-27 21:51:35'),
(6, 2, '2025-10-27', 'P', NULL, NULL, 1, '2025-10-27 22:20:08', '2025-10-27 22:20:15'),
(8, 3, '2025-10-27', 'P', NULL, NULL, 1, '2025-10-27 22:20:10', '2025-10-27 22:20:16');

-- --------------------------------------------------------

--
-- Estrutura para tabela `frequencia_oficina`
--

CREATE TABLE `frequencia_oficina` (
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

--
-- Despejando dados para a tabela `frequencia_oficina`
--

INSERT INTO `frequencia_oficina` (`id_frequencia`, `id_atendido`, `id_oficina`, `data`, `status`, `justificativa`, `observacao`, `registrado_por`, `created_at`, `updated_at`) VALUES
(1, 1, 7, '2025-10-27', 'P', NULL, NULL, 1, '2025-10-27 21:34:34', '2025-10-27 21:34:55'),
(2, 2, 7, '2025-10-27', 'P', NULL, NULL, 1, '2025-10-27 21:34:36', '2025-10-27 21:34:36');

-- --------------------------------------------------------

--
-- Estrutura para tabela `log`
--

CREATE TABLE `log` (
  `id_log` int(11) NOT NULL,
  `data_alteracao` datetime DEFAULT NULL,
  `registro_alt` varchar(100) DEFAULT NULL,
  `valor_anterior` varchar(100) DEFAULT NULL,
  `valor_atual` varchar(100) DEFAULT NULL,
  `acao` varchar(50) DEFAULT NULL,
  `tabela_afetada` varchar(100) DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `log`
--

INSERT INTO `log` (`id_log`, `data_alteracao`, `registro_alt`, `valor_anterior`, `valor_atual`, `acao`, `tabela_afetada`, `id_usuario`) VALUES
(1, '2025-10-17 22:17:45', 'Novo registro em Usuario.idusuario = 1', NULL, 'Nome: Administrador, Email: admin@criancafeliz.org', 'INSERT', 'Usuario', NULL),
(2, '2025-10-17 22:34:01', 'Campo alterado em Usuario.idusuario = 1', 'Nome antigo: Administrador, Email antigo: admin@criancafeliz.org', 'Nome novo: Administrador, Email novo: admin@criancafeliz.org', 'UPDATE', 'Usuario', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `oficina`
--

CREATE TABLE `oficina` (
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

--
-- Despejando dados para a tabela `oficina`
--

INSERT INTO `oficina` (`id_oficina`, `nome`, `descricao`, `dia_semana`, `horario_inicio`, `horario_fim`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 'Reforço Escolar', 'Aulas de reforço para crianças', 'Terça', '14:00:00', '16:00:00', 1, '2025-10-27 21:25:42', '2025-10-27 21:33:41'),
(2, 'Artes', 'Oficina de artes e artesanato', 'Terça', '14:00:00', '16:00:00', 1, '2025-10-27 21:25:42', '2025-10-27 21:25:42'),
(3, 'Esportes', 'Atividades esportivas', 'Quarta', '14:00:00', '16:00:00', 1, '2025-10-27 21:25:42', '2025-10-27 21:25:42'),
(4, 'Música', 'Aulas de música e canto', 'Quinta', '14:00:00', '16:00:00', 1, '2025-10-27 21:25:42', '2025-10-27 21:25:42'),
(5, 'Dança', 'Oficina de dança', 'Sexta', '14:00:00', '16:00:00', 1, '2025-10-27 21:25:42', '2025-10-27 21:25:42'),
(6, 'Teatro', 'Oficina de teatro', 'Sábado', '09:00:00', '11:00:00', 1, '2025-10-27 21:25:42', '2025-10-27 21:25:42'),
(7, 'teste da silva', 'a', 'Segunda', '13:00:00', '14:00:00', 0, '2025-10-27 21:34:04', '2025-10-27 21:44:50');

-- --------------------------------------------------------

--
-- Estrutura para tabela `presenca`
--

CREATE TABLE `presenca` (
  `id_presenca` int(11) NOT NULL,
  `id_sessao` int(11) NOT NULL,
  `id_atendido` int(11) NOT NULL,
  `status` enum('PRESENTE','FALTA','JUSTIFICADA') NOT NULL,
  `justificativa` varchar(255) DEFAULT NULL,
  `registrado_por` int(11) DEFAULT NULL,
  `registrado_em` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `responsavel`
--

CREATE TABLE `responsavel` (
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

--
-- Despejando dados para a tabela `responsavel`
--

INSERT INTO `responsavel` (`idresponsavel`, `nome`, `cpf`, `rg`, `telefone`, `email`, `parentesco`, `endereco`, `numero`, `complemento`, `bairro`, `cidade`, `cep`) VALUES
(1, 'Maria Souza', '123.456.789-00', NULL, '(11) 91234-5678', 'maria.souza@example.com', 'Mãe', NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'João Pereira', '987.654.321-00', NULL, '(11) 99876-5432', 'joao.pereira@example.com', 'Pai', NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `sessao`
--

CREATE TABLE `sessao` (
  `id_sessao` int(11) NOT NULL,
  `data_sessao` date NOT NULL,
  `descricao` varchar(150) DEFAULT NULL,
  `criado_por` int(11) DEFAULT NULL,
  `data_criacao` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario`
--

CREATE TABLE `usuario` (
  `idusuario` int(11) NOT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `Senha` varchar(100) DEFAULT NULL,
  `nivel` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuario`
--

INSERT INTO `usuario` (`idusuario`, `nome`, `email`, `Senha`, `nivel`, `status`) VALUES
(1, 'Administrador', 'admin@criancafeliz.org', '$2y$10$qWMLn9zbVgS5WQhPuMrZue8CbVOxQ.bUOFSZH3BG0Wcdp7ciMTwMi', 'admin', 'Ativo');

--
-- Acionadores `usuario`
--
DELIMITER $$
CREATE TRIGGER `log_delete_all` AFTER DELETE ON `usuario` FOR EACH ROW BEGIN
    INSERT INTO Log (data_alteracao, registro_alt, valor_anterior, valor_atual, acao, tabela_afetada, id_usuario)
    VALUES (
        NOW(),
        CONCAT('Removido Usuario.idusuario = ', OLD.idusuario),
        CONCAT('Nome: ', OLD.nome, ', Email: ', OLD.email),
        NULL,
        'DELETE',
        'Usuario',
        @usuario_id
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `log_insert_all` AFTER INSERT ON `usuario` FOR EACH ROW BEGIN
    INSERT INTO Log (data_alteracao, registro_alt, valor_anterior, valor_atual, acao, tabela_afetada, id_usuario)
    VALUES (
        NOW(),
        CONCAT('Novo registro em Usuario.idusuario = ', NEW.idusuario),
        NULL,
        CONCAT('Nome: ', NEW.nome, ', Email: ', NEW.email),
        'INSERT',
        'Usuario',
        @usuario_id
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `log_update_all` AFTER UPDATE ON `usuario` FOR EACH ROW BEGIN
    INSERT INTO Log (data_alteracao, registro_alt, valor_anterior, valor_atual, acao, tabela_afetada, id_usuario)
    VALUES (
        NOW(),
        CONCAT('Campo alterado em Usuario.idusuario = ', OLD.idusuario),
        CONCAT('Nome antigo: ', OLD.nome, ', Email antigo: ', OLD.email),
        CONCAT('Nome novo: ', NEW.nome, ', Email novo: ', NEW.email),
        'UPDATE',
        'Usuario',
        @usuario_id
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para view `atendidos_com_alerta`
--
DROP TABLE IF EXISTS `atendidos_com_alerta`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `atendidos_com_alerta`  AS SELECT `a`.`idatendido` AS `idatendido`, `a`.`nome` AS `nome`, `a`.`cpf` AS `cpf`, count(case when `fd`.`status` = 'F' then 1 end) AS `total_faltas`, max(`fd`.`data`) AS `ultima_falta`, CASE WHEN count(case when `fd`.`status` = 'F' then 1 end) >= 3 THEN 'CRÍTICO' WHEN count(case when `fd`.`status` = 'F' then 1 end) = 2 THEN 'ALERTA' ELSE 'NORMAL' END AS `nivel_alerta` FROM (`atendido` `a` left join `frequencia_dia` `fd` on(`a`.`idatendido` = `fd`.`id_atendido`)) WHERE `a`.`status` = 'Ativo' AND !exists(select 1 from `desligamento` `d` where `d`.`id_atendido` = `a`.`idatendido` limit 1) GROUP BY `a`.`idatendido`, `a`.`nome`, `a`.`cpf` HAVING count(case when `fd`.`status` = 'F' then 1 end) >= 2 ;

-- --------------------------------------------------------

--
-- Estrutura para view `estatisticas_frequencia`
--
DROP TABLE IF EXISTS `estatisticas_frequencia`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `estatisticas_frequencia`  AS SELECT `a`.`idatendido` AS `idatendido`, `a`.`nome` AS `nome`, count(case when `fd`.`status` = 'P' then 1 end) AS `total_presencas`, count(case when `fd`.`status` = 'F' then 1 end) AS `total_faltas`, count(case when `fd`.`status` = 'J' then 1 end) AS `total_justificadas`, count(0) AS `total_registros`, round(count(case when `fd`.`status` = 'P' then 1 end) * 100.0 / nullif(count(0),0),2) AS `percentual_presenca` FROM (`atendido` `a` left join `frequencia_dia` `fd` on(`a`.`idatendido` = `fd`.`id_atendido`)) WHERE `a`.`status` = 'Ativo' GROUP BY `a`.`idatendido`, `a`.`nome` ;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `agenda`
--
ALTER TABLE `agenda`
  ADD PRIMARY KEY (`id_notificacao`);

--
-- Índices de tabela `atendido`
--
ALTER TABLE `atendido`
  ADD PRIMARY KEY (`idatendido`),
  ADD KEY `id_responsavel` (`id_responsavel`);

--
-- Índices de tabela `desligamento`
--
ALTER TABLE `desligamento`
  ADD PRIMARY KEY (`id_desligamento`),
  ADD UNIQUE KEY `unique_desligamento` (`id_atendido`),
  ADD KEY `desligado_por` (`desligado_por`),
  ADD KEY `idx_atendido_deslig` (`id_atendido`),
  ADD KEY `idx_tipo_motivo` (`tipo_motivo`),
  ADD KEY `idx_data_desligamento` (`data_desligamento`),
  ADD KEY `idx_automatico` (`automatico`);

--
-- Índices de tabela `despesas`
--
ALTER TABLE `despesas`
  ADD PRIMARY KEY (`id_despesa`),
  ADD KEY `id_ficha` (`id_ficha`);

--
-- Índices de tabela `dias_atendimento`
--
ALTER TABLE `dias_atendimento`
  ADD PRIMARY KEY (`id_dia`);

--
-- Índices de tabela `documento`
--
ALTER TABLE `documento`
  ADD PRIMARY KEY (`iddocumento`),
  ADD KEY `IDatendido` (`IDatendido`);

--
-- Índices de tabela `encontro`
--
ALTER TABLE `encontro`
  ADD PRIMARY KEY (`id_encontro`),
  ADD KEY `ID_usuario` (`ID_usuario`),
  ADD KEY `id_atendido` (`id_atendido`);

--
-- Índices de tabela `familia`
--
ALTER TABLE `familia`
  ADD PRIMARY KEY (`id_familia`),
  ADD KEY `id_ficha` (`id_ficha`);

--
-- Índices de tabela `ficha_socioeconomico`
--
ALTER TABLE `ficha_socioeconomico`
  ADD PRIMARY KEY (`idficha`),
  ADD UNIQUE KEY `id_atendido` (`id_atendido`);

--
-- Índices de tabela `frequencia_dia`
--
ALTER TABLE `frequencia_dia`
  ADD PRIMARY KEY (`id_frequencia_dia`),
  ADD UNIQUE KEY `unique_frequencia_dia` (`id_atendido`,`data`),
  ADD KEY `registrado_por` (`registrado_por`),
  ADD KEY `idx_atendido_dia` (`id_atendido`),
  ADD KEY `idx_data_dia` (`data`),
  ADD KEY `idx_status_dia` (`status`),
  ADD KEY `idx_data_status_dia` (`data`,`status`);

--
-- Índices de tabela `frequencia_oficina`
--
ALTER TABLE `frequencia_oficina`
  ADD PRIMARY KEY (`id_frequencia`),
  ADD UNIQUE KEY `unique_frequencia` (`id_atendido`,`id_oficina`,`data`),
  ADD KEY `registrado_por` (`registrado_por`),
  ADD KEY `idx_atendido` (`id_atendido`),
  ADD KEY `idx_oficina` (`id_oficina`),
  ADD KEY `idx_data` (`data`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_data_status` (`data`,`status`);

--
-- Índices de tabela `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Índices de tabela `oficina`
--
ALTER TABLE `oficina`
  ADD PRIMARY KEY (`id_oficina`),
  ADD KEY `idx_ativo` (`ativo`),
  ADD KEY `idx_dia_semana` (`dia_semana`);

--
-- Índices de tabela `presenca`
--
ALTER TABLE `presenca`
  ADD PRIMARY KEY (`id_presenca`),
  ADD UNIQUE KEY `uk_presenca_sessao_atendido` (`id_sessao`,`id_atendido`),
  ADD KEY `idx_presenca_atendido_status` (`id_atendido`,`status`),
  ADD KEY `idx_presenca_sessao_status` (`id_sessao`,`status`),
  ADD KEY `idx_presenca_registrado_por` (`registrado_por`);

--
-- Índices de tabela `responsavel`
--
ALTER TABLE `responsavel`
  ADD PRIMARY KEY (`idresponsavel`);

--
-- Índices de tabela `sessao`
--
ALTER TABLE `sessao`
  ADD PRIMARY KEY (`id_sessao`),
  ADD UNIQUE KEY `uk_sessao_data` (`data_sessao`),
  ADD KEY `idx_sessao_criado_por` (`criado_por`);

--
-- Índices de tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`idusuario`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `agenda`
--
ALTER TABLE `agenda`
  MODIFY `id_notificacao` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `atendido`
--
ALTER TABLE `atendido`
  MODIFY `idatendido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `desligamento`
--
ALTER TABLE `desligamento`
  MODIFY `id_desligamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `despesas`
--
ALTER TABLE `despesas`
  MODIFY `id_despesa` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `dias_atendimento`
--
ALTER TABLE `dias_atendimento`
  MODIFY `id_dia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `documento`
--
ALTER TABLE `documento`
  MODIFY `iddocumento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `encontro`
--
ALTER TABLE `encontro`
  MODIFY `id_encontro` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `familia`
--
ALTER TABLE `familia`
  MODIFY `id_familia` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ficha_socioeconomico`
--
ALTER TABLE `ficha_socioeconomico`
  MODIFY `idficha` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `frequencia_dia`
--
ALTER TABLE `frequencia_dia`
  MODIFY `id_frequencia_dia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `frequencia_oficina`
--
ALTER TABLE `frequencia_oficina`
  MODIFY `id_frequencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `log`
--
ALTER TABLE `log`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `oficina`
--
ALTER TABLE `oficina`
  MODIFY `id_oficina` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `presenca`
--
ALTER TABLE `presenca`
  MODIFY `id_presenca` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `responsavel`
--
ALTER TABLE `responsavel`
  MODIFY `idresponsavel` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `sessao`
--
ALTER TABLE `sessao`
  MODIFY `id_sessao` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuario`
--
ALTER TABLE `usuario`
  MODIFY `idusuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `atendido`
--
ALTER TABLE `atendido`
  ADD CONSTRAINT `atendido_ibfk_1` FOREIGN KEY (`id_responsavel`) REFERENCES `responsavel` (`idresponsavel`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `desligamento`
--
ALTER TABLE `desligamento`
  ADD CONSTRAINT `desligamento_ibfk_1` FOREIGN KEY (`id_atendido`) REFERENCES `atendido` (`idatendido`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `desligamento_ibfk_2` FOREIGN KEY (`desligado_por`) REFERENCES `usuario` (`idusuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `despesas`
--
ALTER TABLE `despesas`
  ADD CONSTRAINT `despesas_ibfk_1` FOREIGN KEY (`id_ficha`) REFERENCES `ficha_socioeconomico` (`idficha`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `documento`
--
ALTER TABLE `documento`
  ADD CONSTRAINT `documento_ibfk_1` FOREIGN KEY (`IDatendido`) REFERENCES `atendido` (`idatendido`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `encontro`
--
ALTER TABLE `encontro`
  ADD CONSTRAINT `encontro_ibfk_1` FOREIGN KEY (`ID_usuario`) REFERENCES `usuario` (`idusuario`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `encontro_ibfk_2` FOREIGN KEY (`id_atendido`) REFERENCES `atendido` (`idatendido`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `familia`
--
ALTER TABLE `familia`
  ADD CONSTRAINT `familia_ibfk_1` FOREIGN KEY (`id_ficha`) REFERENCES `ficha_socioeconomico` (`idficha`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `ficha_socioeconomico`
--
ALTER TABLE `ficha_socioeconomico`
  ADD CONSTRAINT `ficha_socioeconomico_ibfk_1` FOREIGN KEY (`id_atendido`) REFERENCES `atendido` (`idatendido`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `frequencia_dia`
--
ALTER TABLE `frequencia_dia`
  ADD CONSTRAINT `frequencia_dia_ibfk_1` FOREIGN KEY (`id_atendido`) REFERENCES `atendido` (`idatendido`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frequencia_dia_ibfk_2` FOREIGN KEY (`registrado_por`) REFERENCES `usuario` (`idusuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `frequencia_oficina`
--
ALTER TABLE `frequencia_oficina`
  ADD CONSTRAINT `frequencia_oficina_ibfk_1` FOREIGN KEY (`id_atendido`) REFERENCES `atendido` (`idatendido`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frequencia_oficina_ibfk_2` FOREIGN KEY (`id_oficina`) REFERENCES `oficina` (`id_oficina`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frequencia_oficina_ibfk_3` FOREIGN KEY (`registrado_por`) REFERENCES `usuario` (`idusuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `log`
--
ALTER TABLE `log`
  ADD CONSTRAINT `log_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`idusuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `presenca`
--
ALTER TABLE `presenca`
  ADD CONSTRAINT `fk_presenca_atendido` FOREIGN KEY (`id_atendido`) REFERENCES `atendido` (`idatendido`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_presenca_sessao` FOREIGN KEY (`id_sessao`) REFERENCES `sessao` (`id_sessao`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_presenca_usuario` FOREIGN KEY (`registrado_por`) REFERENCES `usuario` (`idusuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `sessao`
--
ALTER TABLE `sessao`
  ADD CONSTRAINT `fk_sessao_usuario` FOREIGN KEY (`criado_por`) REFERENCES `usuario` (`idusuario`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
