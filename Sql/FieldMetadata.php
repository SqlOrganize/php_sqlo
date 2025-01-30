<?php

namespace SqlOrganize\Sql;

class FieldMetadata
{
    public Db $db;
    public string $name;
    public ?string $alias = null;
    public string $entityName;
    public ?string $refEntityName = null;
    public ?string $refFieldName = "id";
    public string $dataType = "varchar"; //tipo de datos del motor
    public string $type = "string"; //tipo de datos del lenguaje
    public mixed $defaultValue;  
    public array $checks = [];
    public array $resets = [];

    public function getEntityMetadata(): EntityMetadata
    {
        return $this->db->getEntityMetadataByName($this->entityName);
    }

    public function getRefEntityMetadata(): ?EntityMetadata
    {
        return $this->refEntityName ? $this->db->getEntityMetadataByName($this->refEntityName) : null;
    }

    public function isRequired(): bool
    {
        $entity = $this->db->getEntityMetadataByName($this->entityName);
        return in_array($this->name, $entity->notNull ?? []);
    }

    public function isUnique(): bool
    {
        $entity = $this->db->getEntityMetadataByName($this->entityName);
        if (in_array($this->name, $entity->unique ?? [])) {
            return true;
        }
        if (in_array($this->name, $entity->pk ?? []) && count($entity->pk) === 1) {
            return true;
        }
        return false;
    }
}
