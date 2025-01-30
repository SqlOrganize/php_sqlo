<?php

namespace SqlOrganize\Sql;

class EntityMetadata {
    public Db $db;
    public string $name;
    public string $alias;
    public ?string $schema = null;
    public array $pk = [];
    public array $fk = [];
    protected array $_ref = [];
    protected array $_oor = []; //one to one (fk in ref)
    protected array $_om = []; //one to many (fk in ref)

    /**
     * Array dinamico para identificar a la entidad en un momento determinado.
     * Ejemplo: ["fecha_anio", "fecha_semestre","persona__numero_documento"]
     */
    public array $identifier = [];

    /**
     * Valores por defecto para ordenamiento
     * Ejemplo ["field1"=>"asc","field2"=>"desc",...];
     */
    public array $orderDefault = [];

    /**
     * nombres de campos principales
     */
    public array $main = [];
    
    /**
     * Valores unicos
     * Una entidad puede tener varios campos que determinen un valor único
     * Ejemplo: field1, field2, field3
     */
    public array $unique = [];
    public array $notNull = [];
    
    /**
     * Valores unicos multiples
     * Cada juego de valores unicos multiples se define como una Lista.
     */
    public array $uniqueMultiple = [];
    public array $tree = []; 
    public array $relations = [];
    public array $oo = []; //one to one
    public array $om = []; //one to many
    /**
     * Campo de Identificación
     *  - Si existe un solo campo pk, entonces la pk sera el id.
     *  - Si existe al menos un campo unique not null, se toma como id.     
     *  - Si existe multiples campos pk, se toman la concatenacion como id. 
     *  - Si existe multiples campos uniqueMultiple, se toman la concatenacion como id. 
     */
    public array $id = [];
    public array $fields = []; //array asociativo string => FieldMetadata
    
    public function getFieldNames(){
        return array_keys($this->fields);
    }

    public function getSchema(): string {
        return empty($this->schema) ? '' : $this->schema;
    }
    
    public function getSchemaName(): string {
        return $this->schema . $this->name;
    }
    
    public function getSchemaNameAlias(): string {
        return $this->schema . $this->name . " AS " . $this->alias;
    }
    
    protected function getFieldsMetadataByFieldNames(array $fieldNames): array {
        $fields = [];
        foreach ($fieldNames as $fieldName) {
            $fields[] = $this->db->getFieldMetadata($this->name, $fieldName);
        }
        return $fields;
    }
    
    public function getFieldsMetadata(): array {
        return $this->getFieldsMetadataByFieldNames(array_keys($this->fields));
    }
    
    public function getFieldsMetadataFk(): array {
        return $this->getFieldsMetadataByFieldNames($this->fk);
    }
    
    public function getFieldsMetadataRef(): array {
        if (!empty($this->_ref)) {
            return $this->_ref;
        }
        
        $this->_ref = [];
        $this->_oor = [];
        $this->_om = [];
        
        foreach ($this->db->getEntitiesMetadata() as $entityName => $entity) {
            foreach ($entity->fk as $fieldName) {
                $field = $this->db->getFieldMetadata($entityName, $fieldName);
                if ($field->refEntityName === $this->name) {
                    $this->_ref[] = $field;
                    if (in_array($field->name, $entity->unique)) {
                        $this->_oor[] = $field;
                    } else {
                        $this->_om[] = $field;
                    }
                }
            }
        }
        
        return $this->_ref;
    }
    
    public function getFieldsMetadataOor(): array {
        if (!empty($this->_oor)) {
            return $this->_oor;
        }
        
        $this->getFieldsMetadataRef();
        return $this->_oor;
    }
    
    public function getFieldsMetadataOm(): array {
        if (!empty($this->_om)) {
            return $this->_om;
        }
        
        $this->getFieldsMetadataRef();
        return $this->_om;
    }
}
