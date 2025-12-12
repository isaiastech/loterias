-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mysql
-- Tempo de geração: 12/12/2025 às 21:32
-- Versão do servidor: 8.0.44
-- Versão do PHP: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `loteria`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `lotofacil`
--

CREATE TABLE `lotofacil` (
  `id` int NOT NULL,
  `concurso` int DEFAULT NULL,
  `data_sorteio` date DEFAULT NULL,
  `d01` tinyint DEFAULT NULL,
  `d02` tinyint DEFAULT NULL,
  `d03` tinyint DEFAULT NULL,
  `d04` tinyint DEFAULT NULL,
  `d05` tinyint DEFAULT NULL,
  `d06` tinyint DEFAULT NULL,
  `d07` tinyint DEFAULT NULL,
  `d08` tinyint DEFAULT NULL,
  `d09` tinyint DEFAULT NULL,
  `d10` tinyint DEFAULT NULL,
  `d11` tinyint DEFAULT NULL,
  `d12` tinyint DEFAULT NULL,
  `d13` tinyint DEFAULT NULL,
  `d14` tinyint DEFAULT NULL,
  `d15` tinyint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `lotofacil`
--

INSERT INTO `lotofacil` (`id`, `concurso`, `data_sorteio`, `d01`, `d02`, `d03`, `d04`, `d05`, `d06`, `d07`, `d08`, `d09`, `d10`, `d11`, `d12`, `d13`, `d14`, `d15`) VALUES
(1, 3560, '2025-12-11', 1, 3, 4, 6, 7, 8, 10, 11, 12, 13, 17, 18, 19, 23, 24),
(5, 3559, '2025-12-10', 1, 2, 6, 8, 9, 10, 11, 13, 14, 15, 16, 19, 20, 24, 25),
(6, 3558, '2025-12-09', 2, 3, 4, 7, 9, 12, 13, 14, 15, 18, 20, 22, 23, 24, 25),
(7, 3557, '2025-12-08', 5, 6, 7, 9, 10, 13, 14, 15, 16, 17, 19, 20, 21, 22, 24),
(8, 3556, '2025-12-06', 1, 2, 3, 4, 5, 7, 9, 10, 12, 14, 16, 17, 19, 21, 23),
(9, 3555, '2025-12-05', 1, 2, 3, 4, 7, 8, 10, 13, 14, 15, 18, 19, 20, 23, 24),
(10, 3554, '2025-12-04', 1, 3, 4, 5, 8, 9, 10, 11, 14, 15, 16, 18, 22, 23, 25),
(11, 3553, '2025-12-03', 1, 2, 3, 7, 8, 9, 12, 13, 16, 17, 19, 21, 22, 23, 24),
(12, 3552, '2025-12-02', 4, 5, 6, 7, 11, 12, 14, 15, 16, 17, 18, 20, 21, 23, 25),
(13, 3551, '2025-12-01', 1, 2, 3, 7, 8, 10, 11, 12, 15, 16, 17, 19, 21, 22, 25);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `lotofacil`
--
ALTER TABLE `lotofacil`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `concurso` (`concurso`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `lotofacil`
--
ALTER TABLE `lotofacil`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
