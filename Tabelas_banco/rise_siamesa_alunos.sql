-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Tempo de geração: 21-Jan-2026 às 01:43
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `rise_crm`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `rise_siamesa_alunos`
--

CREATE TABLE `rise_siamesa_alunos` (
  `id` int(11) NOT NULL,
  `unidade_id` int(11) NOT NULL,
  `responsavel_id` int(11) NOT NULL,
  `nome_aluno` varchar(150) NOT NULL,
  `nascimento_aluno` date DEFAULT NULL,
  `rg_aluno` varchar(20) DEFAULT NULL,
  `cpf_aluno` varchar(14) DEFAULT NULL,
  `turma` enum('08:30-11:00','11:30-14:00','14:30-17:00') NOT NULL,
  `curso` varchar(100) DEFAULT NULL,
  `tamanho_camisa` varchar(10) DEFAULT NULL,
  `valor_mensalidade` decimal(10,2) NOT NULL,
  `data_matricula` date DEFAULT NULL,
  `data_inicio` date DEFAULT NULL,
  `status` enum('Ativo','Cancelado') DEFAULT 'Ativo',
  `deleted` tinyint(1) DEFAULT 0,
  `horario` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Extraindo dados da tabela `rise_siamesa_alunos`
--

INSERT INTO `rise_siamesa_alunos` (`id`, `unidade_id`, `responsavel_id`, `nome_aluno`, `nascimento_aluno`, `rg_aluno`, `cpf_aluno`, `turma`, `curso`, `tamanho_camisa`, `valor_mensalidade`, `data_matricula`, `data_inicio`, `status`, `deleted`, `horario`) VALUES
(1, 0, 1, 'Josezinho', NULL, NULL, NULL, '08:30-11:00', NULL, NULL, 150.00, NULL, '2026-01-09', 'Ativo', 0, NULL),
(2, 0, 2, 'Lucas Fernandes', '2026-01-07', NULL, NULL, '11:30-14:00', NULL, NULL, 150.00, NULL, '2026-01-09', 'Ativo', 0, NULL),
(3, 0, 3, 'Teste1', '2019-05-08', NULL, NULL, '11:30-14:00', NULL, 'P', 150.00, NULL, '2026-01-09', 'Ativo', 1, NULL),
(50, 0, 1, 'Tonin', '2026-01-01', NULL, NULL, '08:30-11:00', NULL, 'GG', 15000.00, '2026-01-09', '2026-01-09', 'Ativo', 0, NULL),
(51, 0, 53, 'Nebias Tenebras12`1231', '2026-01-09', NULL, NULL, '08:30-11:00', NULL, 'P', 15000.00, '2026-01-09', '2026-01-09', 'Cancelado', 0, NULL),
(52, 0, 1, 'Berlim_)123', '2026-01-07', NULL, NULL, '08:30-11:00', NULL, 'P', 150.00, '2026-01-09', '2026-01-09', 'Ativo', 0, NULL),
(53, 1, 1, 'Tiago-Frank', '2026-01-01', NULL, NULL, '08:30-11:00', NULL, 'GG', 150.00, '2026-01-10', '2026-01-10', 'Ativo', 0, NULL),
(54, 1, 56, 'Teste_12/01', '2026-01-07', NULL, NULL, '11:30-14:00', NULL, 'M', 150.00, '2026-01-12', '2026-01-12', 'Ativo', 0, NULL),
(55, 1, 57, 'Jose12/0111', '2026-01-07', '121313131', '55544236666', '08:30-11:00', NULL, 'P', 150.00, '2026-01-12', '2026-01-12', 'Ativo', 0, NULL),
(56, 1, 58, 'Josezinho Junior Frank123', '2026-01-08', '12312313131', '11235567567', '11:30-14:00', NULL, 'P', 267.00, '2026-01-12', '2026-01-12', 'Ativo', 0, NULL),
(57, 1, 59, 'Lucas Davi', '2015-10-20', '', '', '', NULL, '6', 237.00, '2026-01-12', '2025-02-05', 'Ativo', 0, NULL),
(58, 1, 60, 'Miguel Oliveira', '2016-02-12', '', '', '', NULL, '8', 200.00, '2026-01-12', '2025-02-10', 'Ativo', 0, NULL),
(59, 1, 61, 'Junior kleber da silva', '2006-09-18', '123112-45', '007545334852', '08:30-11:00', NULL, '12', 150.00, '2026-01-20', '2026-01-20', 'Ativo', 0, NULL);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `rise_siamesa_alunos`
--
ALTER TABLE `rise_siamesa_alunos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `responsavel_id` (`responsavel_id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `rise_siamesa_alunos`
--
ALTER TABLE `rise_siamesa_alunos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `rise_siamesa_alunos`
--
ALTER TABLE `rise_siamesa_alunos`
  ADD CONSTRAINT `rise_siamesa_alunos_ibfk_1` FOREIGN KEY (`responsavel_id`) REFERENCES `rise_siamesa_responsaveis` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
