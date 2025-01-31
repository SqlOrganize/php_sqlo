<?php

namespace SqlOrganize\Sql;

use Exception;

abstract class Db {
    protected Config $config;
    protected array $entities;

    public function __construct(Config $config, ISchema $schema) {
        $this->config = $config;
        $this->entities = $schema->entities;

        foreach ($this->entities as $e) {
            $e->db = $this;
            foreach ($e->fields as $key => $f) {
                $f->db = $this;
            }
        }
    }

    public function getConfig(): Config {
        return $this->config;
    }

    public function getEntitiesMetadata(): array {
        return $this->entities;
    }

    public function getFieldsMetadataByEntityName(string $entityName): array {
        if (!isset($this->entities[$entityName])) {
            throw new Exception("La entidad $entityName no existe");
        }
        return $this->entities[$entityName]->fields;
    }

    public function getFieldMetadata(string $entityName, string $fieldName): FieldMetadata {
        $fields = $this->getFieldsMetadataByEntityName($entityName);
        return $fields[$fieldName] ?? new FieldMetadata();
    }

    public function getEntityNames(): array {
        return array_keys($this->entities);
    }

    public function getFieldNamesByEntityName(string $entityName): array {
        $fields = array_keys($this->getFieldsMetadataByEntityName($entityName));
        if (($key = array_search($this->config->id, $fields)) !== false) {
            unset($fields[$key]);
        }
        array_unshift($fields, $this->config->id);
        return $fields;
    }

    public function getFieldNamesWithoutIdByEntityName(string $entityName): array {
        $fields = array_keys($this->getFieldsMetadataByEntityName($entityName));
        if (($key = array_search($this->config->id, $fields)) !== false) {
            unset($fields[$key]);
        }
        return $fields;
    }

    public function getFieldNamesWithRelByEntityName(string $entityName): array {
        return array_merge($this->getFieldNamesByEntityName($entityName), $this->getFieldNamesRelByEntityName($entityName));
    }

    public function getFieldNamesRelByEntityName(string $entityName): array {
        $fieldNamesR = [];
        $entity = $this->getEntityMetadataByName($entityName);
        
        if (!empty($entity->relations)) {
            foreach ($entity->relations as $fieldId => $er) {
                $fieldNamesR[] = $fieldId . "__" . $this->config->id;
                foreach ($this->getFieldNamesWithoutIdByEntityName($er->refEntityName) as $fieldName) {
                    $fieldNamesR[] = $fieldId . "__" . $fieldName;
                }
            }
        }
        return $fieldNamesR;
    }

    public function getEntityMetadataByName(string $entityName): EntityMetadata {
        if (!isset($this->entities[$entityName])) {
            throw new Exception("La entidad $entityName no existe");
        }
        return $this->entities[$entityName];
    }

    public function getPdo(): PDO {
        
    }

    abstract public function getPersistSql(): PersistSql;
    abstract public function getSelectSql(): SelectSql;

    public function getCacheSql(): CacheSql {
        return new CacheSql($this);
    }

    public function getMapping(string $entityName, ?string $fieldId = null): EntityMapping {
        return new EntityMapping($this, $entityName, $fieldId);
    }

    public function keyDeconstruction(string $entityName, string $key): array {
        $i = strpos($key, "__");
        if ($i === false) {
            throw new Exception("Invalid key format");
        }
        
        $fieldId = substr($key, 0, $i);
        $refEntityName = $this->getEntityMetadataByName($entityName)->relations[$fieldId]->refEntityName;
        $fieldName = substr($key, $i + strlen("__"));
        
        return [$fieldId, $fieldName, $refEntityName];
    }
}
