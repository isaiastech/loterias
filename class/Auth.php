<?php

namespace class;

use Exception;

class Auth
{
    private $db;

    public function __construct()
    {
        $this->db = new Conexao();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Realiza login do usuário
     */
    public function login(string $email, string $senha): bool
    {
        $sql = "SELECT id, nome, email, senha FROM usuarios WHERE email = ?";
        $result = $this->db->getResultFromQuery($sql, [$email]);

        if ($result->num_rows !== 1) {
            return false;
        }

        $usuario = $result->fetch_assoc();

        if (!password_verify($senha, $usuario['senha'])) {
            return false;
        }

        // Login OK
        $_SESSION['auth'] = [
            'logado' => true,
            'id'     => $usuario['id'],
            'nome'   => $usuario['nome'],
            'email'  => $usuario['email'],
        ];

        return true;
    }

    /**
     * Verifica se usuário está autenticado
     */
    public function check(): bool
    {
        return isset($_SESSION['auth']['logado']) && $_SESSION['auth']['logado'] === true;
    }

    /**
     * Retorna usuário autenticado
     */
    public function user(): ?array
    {
        return $this->check() ? $_SESSION['auth'] : null;
    }

    /**
     * Exige autenticação (protege páginas)
     */
    public function requireAuth(): void
    {
        if (!$this->check()) {
            header("Location: login.php");
            exit;
        }
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        unset($_SESSION['auth']);
        session_destroy();
    }
}
