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
-- Estrutura da tabela `rise_siamesa_unidades`
--

CREATE TABLE `rise_siamesa_unidades` (
  `id` int(11) NOT NULL,
  `nome_unidade` varchar(100) NOT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `endereco` text DEFAULT NULL,
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  `deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `rise_siamesa_unidades`
--

INSERT INTO `rise_siamesa_unidades` (`id`, `nome_unidade`, `cidade`, `endereco`, `status`, `deleted`) VALUES
(1, 'Escola Siamesa_Cascavel', 'Cascavel', 'Rua dos Alfeneiros n4', 'ativo', 0);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `rise_siamesa_unidades`
--
ALTER TABLE `rise_siamesa_unidades`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `rise_siamesa_unidades`
--
ALTER TABLE `rise_siamesa_unidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
