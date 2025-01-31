<?php

namespace SqlOrganize\Sql;

class Entity
{
    protected string $entityName;
    protected Db $db;
    protected array $errors = [];
    protected string $label = "";
    protected int $status = 0;
    protected int $index = 0;

    public function __construct(db $db, string $entityName)
    {
        $this->db = $db;
        $this->entityName = $entityName;
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function getDb(): Db
    {
        return $this->db;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function setIndex(int $index): void
    {
        $this->index = $index;
    }

    public function validate(): bool
    {
        $this->errors = [];
        
        // Aquí puedes agregar reglas de validación específicas
        
        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public static function createFromId(Db $db, string $entityName, int $id): ?self
    {
        $db->getPdo().
        $stmt = $db->prepare("SELECT * FROM $entityName WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $entity = new self($db, $entityName);
            foreach ($data as $key => $value) {
                if (property_exists($entity, $key)) {
                    $entity->$key = $value;
                }
            }
            return $entity;
        }
        return null;
    }
}
