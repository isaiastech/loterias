<?php

namespace class;

use mysqli;
use Exception;

class Conexao
{
    private $conn;

    public function __construct()
    {
        $this->connect();
    }

    private function connect()
    {
        $envPath = realpath(__DIR__ . '/../config/.env');

        if (!$envPath) {
            throw new Exception("Erro: O arquivo de configuração '.env' não foi encontrado.");
        }

        $env = parse_ini_file($envPath);
        if (!$env) {
            throw new Exception("Erro: Não foi possível carregar o arquivo de configuração '.env'.");
        }

        $this->conn = new mysqli($env['host'], $env['username'], $env['password'], $env['database']);
        if ($this->conn->connect_error) {
            throw new Exception("Erro de conexão: " . $this->conn->connect_error);
        }
    }

    public function getConnection()
    {
        return $this->conn;
    }

    private function getParamTypes($params)
    {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } elseif (is_string($param)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
        }
        return $types;
    }

    public function getResultFromQuery($sql, $params = [])
    {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Erro ao preparar a consulta: " . $this->conn->error);
        }

        if ($params) {
            $types = $this->getParamTypes($params);
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            throw new Exception("Erro ao executar a consulta: " . $stmt->error);
        }

        return $stmt->get_result();
    }

    public function close()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
