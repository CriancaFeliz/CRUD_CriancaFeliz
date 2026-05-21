-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 28/10/2025 às 00:16
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

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `atendido`
--
ALTER TABLE `atendido`
  ADD PRIMARY KEY (`idatendido`),
  ADD KEY `id_responsavel` (`id_responsavel`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `atendido`
--
ALTER TABLE `atendido`
  MODIFY `idatendido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `atendido`
--
ALTER TABLE `atendido`
  ADD CONSTRAINT `atendido_ibfk_1` FOREIGN KEY (`id_responsavel`) REFERENCES `responsavel` (`idresponsavel`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
