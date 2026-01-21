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
-- Estrutura da tabela `rise_siamesa_cobrancas`
--

CREATE TABLE `rise_siamesa_cobrancas` (
  `id` int(11) NOT NULL,
  `aluno_id` int(11) NOT NULL,
  `vencimento` date NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `competencia` varchar(20) NOT NULL,
  `status` enum('Pago','Pendente','Atrasado') DEFAULT 'Pendente',
  `tipo` enum('Mensalidade','Inscrição','Material') DEFAULT 'Mensalidade',
  `data_pagamento` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Extraindo dados da tabela `rise_siamesa_cobrancas`
--

INSERT INTO `rise_siamesa_cobrancas` (`id`, `aluno_id`, `vencimento`, `valor`, `competencia`, `status`, `tipo`, `data_pagamento`) VALUES
(1, 1, '2026-01-09', 150.00, '01/2026', 'Pago', 'Mensalidade', NULL),
(2, 1, '2026-02-09', 150.00, '02/2026', 'Pago', 'Mensalidade', '2026-01-09 05:02:50'),
(3, 1, '2026-03-09', 150.00, '03/2026', 'Pendente', 'Mensalidade', NULL),
(4, 1, '2026-04-09', 150.00, '04/2026', 'Pendente', 'Mensalidade', NULL),
(5, 1, '2026-05-09', 150.00, '05/2026', 'Pendente', 'Mensalidade', NULL),
(6, 1, '2026-06-09', 150.00, '06/2026', 'Pendente', 'Mensalidade', NULL),
(7, 2, '2026-01-09', 150.00, '01/2026', 'Pago', 'Mensalidade', NULL),
(8, 2, '2026-02-09', 150.00, '02/2026', 'Pendente', 'Mensalidade', NULL),
(9, 2, '2026-03-09', 150.00, '03/2026', 'Pendente', 'Mensalidade', NULL),
(10, 2, '2026-04-09', 150.00, '04/2026', 'Pendente', 'Mensalidade', NULL),
(11, 2, '2026-05-09', 150.00, '05/2026', 'Pendente', 'Mensalidade', NULL),
(12, 2, '2026-06-09', 150.00, '06/2026', 'Pendente', 'Mensalidade', NULL),
(13, 2, '2026-02-09', 67.00, '02/2026', 'Pago', '', '2026-01-09 05:32:24'),
(14, 3, '2026-01-09', 150.00, '01/2026', 'Pago', 'Mensalidade', '2026-01-09 05:32:30'),
(15, 3, '2026-02-09', 150.00, '02/2026', 'Pendente', 'Mensalidade', NULL),
(16, 3, '2026-03-09', 150.00, '03/2026', 'Pendente', 'Mensalidade', NULL),
(17, 3, '2026-04-09', 150.00, '04/2026', 'Pendente', 'Mensalidade', NULL),
(18, 3, '2026-05-09', 150.00, '05/2026', 'Pendente', 'Mensalidade', NULL),
(19, 3, '2026-06-09', 150.00, '06/2026', 'Pendente', 'Mensalidade', NULL),
(20, 3, '2026-02-09', 67.00, '02/2026', 'Pendente', '', NULL),
(303, 50, '2026-01-09', 15000.00, '01/2026', 'Pendente', 'Mensalidade', NULL),
(304, 50, '2026-02-09', 15000.00, '02/2026', 'Pendente', 'Mensalidade', NULL),
(305, 50, '2026-03-09', 15000.00, '03/2026', 'Pendente', 'Mensalidade', NULL),
(306, 50, '2026-04-09', 15000.00, '04/2026', 'Pendente', 'Mensalidade', NULL),
(307, 50, '2026-05-09', 15000.00, '05/2026', 'Pendente', 'Mensalidade', NULL),
(308, 50, '2026-06-09', 15000.00, '06/2026', 'Pendente', 'Mensalidade', NULL),
(309, 50, '2026-01-09', 67.00, '01/2026', 'Pendente', '', NULL),
(310, 51, '2026-01-09', 15000.00, '01/2026', 'Pendente', 'Mensalidade', NULL),
(311, 51, '2026-02-09', 15000.00, '02/2026', 'Pendente', 'Mensalidade', NULL),
(312, 51, '2026-03-09', 15000.00, '03/2026', 'Pendente', 'Mensalidade', NULL),
(313, 51, '2026-04-09', 15000.00, '04/2026', 'Pendente', 'Mensalidade', NULL),
(314, 51, '2026-05-09', 15000.00, '05/2026', 'Pendente', 'Mensalidade', NULL),
(315, 51, '2026-06-09', 15000.00, '06/2026', 'Pendente', 'Mensalidade', NULL),
(316, 51, '2026-01-09', 67.00, '01/2026', 'Pendente', '', NULL),
(317, 52, '2026-01-09', 150.00, '01/2026', 'Pendente', 'Mensalidade', NULL),
(318, 52, '2026-02-09', 150.00, '02/2026', 'Pendente', 'Mensalidade', NULL),
(319, 52, '2026-03-09', 150.00, '03/2026', 'Pendente', 'Mensalidade', NULL),
(320, 52, '2026-04-09', 150.00, '04/2026', 'Pendente', 'Mensalidade', NULL),
(321, 52, '2026-05-09', 150.00, '05/2026', 'Pendente', 'Mensalidade', NULL),
(322, 52, '2026-06-09', 150.00, '06/2026', 'Pendente', 'Mensalidade', NULL),
(323, 52, '2026-01-09', 67.00, '01/2026', 'Pendente', '', NULL),
(324, 53, '2026-01-10', 150.00, '01/2026', 'Pendente', 'Mensalidade', NULL),
(325, 53, '2026-02-10', 150.00, '02/2026', 'Pendente', 'Mensalidade', NULL),
(326, 53, '2026-03-10', 150.00, '03/2026', 'Pendente', 'Mensalidade', NULL),
(327, 53, '2026-04-10', 150.00, '04/2026', 'Pendente', 'Mensalidade', NULL),
(328, 53, '2026-05-10', 150.00, '05/2026', 'Pendente', 'Mensalidade', NULL),
(329, 53, '2026-06-10', 150.00, '06/2026', 'Pendente', 'Mensalidade', NULL),
(330, 53, '2026-01-10', 67.00, '01/2026', 'Pendente', '', NULL),
(331, 54, '2026-01-12', 150.00, '01/2026', 'Pendente', 'Mensalidade', NULL),
(332, 54, '2026-02-12', 150.00, '02/2026', 'Pendente', 'Mensalidade', NULL),
(333, 54, '2026-03-12', 150.00, '03/2026', 'Pendente', 'Mensalidade', NULL),
(334, 54, '2026-04-12', 150.00, '04/2026', 'Pendente', 'Mensalidade', NULL),
(335, 54, '2026-05-12', 150.00, '05/2026', 'Pendente', 'Mensalidade', NULL),
(336, 54, '2026-06-12', 150.00, '06/2026', 'Pendente', 'Mensalidade', NULL),
(337, 54, '2026-01-12', 67.00, '01/2026', 'Pendente', '', NULL),
(338, 55, '2026-01-12', 150.00, '01/2026', 'Pendente', 'Mensalidade', NULL),
(339, 55, '2026-02-12', 150.00, '02/2026', 'Pendente', 'Mensalidade', NULL),
(340, 55, '2026-03-12', 150.00, '03/2026', 'Pendente', 'Mensalidade', NULL),
(341, 55, '2026-04-12', 150.00, '04/2026', 'Pendente', 'Mensalidade', NULL),
(342, 55, '2026-05-12', 150.00, '05/2026', 'Pendente', 'Mensalidade', NULL),
(343, 55, '2026-06-12', 150.00, '06/2026', 'Pendente', 'Mensalidade', NULL),
(344, 55, '2026-01-12', 67.00, '01/2026', 'Pendente', '', NULL),
(345, 56, '2026-01-12', 267.00, '01/2026', 'Pendente', 'Mensalidade', NULL),
(346, 56, '2026-02-12', 267.00, '02/2026', 'Pendente', 'Mensalidade', NULL),
(347, 56, '2026-03-12', 267.00, '03/2026', 'Pendente', 'Mensalidade', NULL),
(348, 56, '2026-04-12', 267.00, '04/2026', 'Pendente', 'Mensalidade', NULL),
(349, 56, '2026-05-12', 267.00, '05/2026', 'Pendente', 'Mensalidade', NULL),
(350, 56, '2026-06-12', 267.00, '06/2026', 'Pendente', 'Mensalidade', NULL),
(351, 56, '2026-01-12', 67.00, '01/2026', 'Pendente', '', NULL),
(352, 59, '2026-01-20', 150.00, '01/2026', 'Pendente', 'Mensalidade', NULL),
(353, 59, '2026-02-20', 150.00, '02/2026', 'Pendente', 'Mensalidade', NULL),
(354, 59, '2026-03-20', 150.00, '03/2026', 'Pendente', 'Mensalidade', NULL),
(355, 59, '2026-04-20', 150.00, '04/2026', 'Pendente', 'Mensalidade', NULL),
(356, 59, '2026-05-20', 150.00, '05/2026', 'Pendente', 'Mensalidade', NULL),
(357, 59, '2026-06-20', 150.00, '06/2026', 'Pendente', 'Mensalidade', NULL),
(358, 59, '2026-01-20', 67.00, '01/2026', 'Pendente', '', NULL);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `rise_siamesa_cobrancas`
--
ALTER TABLE `rise_siamesa_cobrancas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aluno_id` (`aluno_id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `rise_siamesa_cobrancas`
--
ALTER TABLE `rise_siamesa_cobrancas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=359;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `rise_siamesa_cobrancas`
--
ALTER TABLE `rise_siamesa_cobrancas`
  ADD CONSTRAINT `rise_siamesa_cobrancas_ibfk_1` FOREIGN KEY (`aluno_id`) REFERENCES `rise_siamesa_alunos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
