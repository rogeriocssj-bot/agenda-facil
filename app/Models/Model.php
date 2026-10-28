<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

/** Classe base dos models (camada M do MVC). */
abstract class Model
{
    protected PDO $db;
    protected string $tabela;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::conexao();
    }

    public function buscar(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->tabela} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->tabela} WHERE id = :id");
        return $stmt->execute(['id' => $id]) && $stmt->rowCount() > 0;
    }
}
