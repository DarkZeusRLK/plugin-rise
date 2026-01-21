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
-- Estrutura da tabela `rise_siamesa_responsaveis`
--

CREATE TABLE `rise_siamesa_responsaveis` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `nascimento` date DEFAULT NULL,
  `rg` varchar(20) DEFAULT NULL,
  `cpf` varchar(14) NOT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `numero` varchar(10) DEFAULT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `whats` varchar(20) DEFAULT NULL,
  `celular` varchar(20) DEFAULT NULL,
  `recado` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` varchar(200) NOT NULL,
  `deleted` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Extraindo dados da tabela `rise_siamesa_responsaveis`
--

INSERT INTO `rise_siamesa_responsaveis` (`id`, `nome`, `nascimento`, `rg`, `cpf`, `endereco`, `numero`, `complemento`, `bairro`, `cep`, `cidade`, `whats`, `celular`, `recado`, `email`, `status`, `deleted`) VALUES
(1, 'Tiago Frank', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, '43919192939', NULL, NULL, '', '', 0),
(2, 'João Pedro', NULL, NULL, '14132321231', NULL, NULL, NULL, NULL, NULL, NULL, '(43) 99989-1733', NULL, NULL, 'fernandesjoaopedro622@gmail.com', '', 0),
(3, 'Nebias', NULL, NULL, '131131313', NULL, NULL, NULL, NULL, NULL, NULL, '(43) 91919-2939', NULL, NULL, 'tiago_frank@gmail.com', '', 0),
(53, 'Tiago Frank1231', NULL, NULL, '17832721231', NULL, NULL, NULL, NULL, NULL, NULL, '11212313131', NULL, NULL, 'tonin_dyg1uetto@gmail.com', '', 0),
(56, 'Teste_12/01', NULL, NULL, '23132434343', NULL, NULL, NULL, NULL, NULL, NULL, '43111111111', NULL, NULL, 'feradsads@gmail.com', '', 0),
(57, 'Jose12/01', NULL, '1313123313', '34213123123', NULL, NULL, NULL, NULL, NULL, NULL, '31333333333', NULL, NULL, 'fernandes@gmail.com', '', 0),
(58, 'Josezinho Frank123', '2021-12-30', '904993311', '15678888888', 'Rua dos alfineiros', '123', 'Apto 01', 'centro', '86870000', 'Curitiba', '43111111112', '21233333333', '5132313131313131', 'josezinho@gmail.com', '', 0),
(59, 'Thiago Frank', '1985-05-10', '123456789', '11122233344', 'Rua Exemplo', '100', 'Apto 10', 'Centro', '80000000', 'Curitiba', '41999998888', '4133334444', NULL, 'thiago@email.com', 'Ativo', 0),
(60, 'Natalia Oliveira', '1990-08-15', '987654321', '99988877766', 'Av Brasil', '500', '', 'Batel', '81000000', 'Curitiba', '41988887777', '', NULL, 'natalia@email.com', 'Ativo', 0),
(61, 'João Kleber', '2007-09-18', '123123-41', '11443487715', 'Rua dos alfredos', '115', 'Apto 01', 'Centro', '86870000', 'Ivaiporã', '43999791433', '', '', 'fernandesjoaopedro622@gmail.com', '', 0);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `rise_siamesa_responsaveis`
--
ALTER TABLE `rise_siamesa_responsaveis`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cpf_unique` (`cpf`),
  ADD UNIQUE KEY `uc_whatsapp` (`whats`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `rise_siamesa_responsaveis`
--
ALTER TABLE `rise_siamesa_responsaveis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
