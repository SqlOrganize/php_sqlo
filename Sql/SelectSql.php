<?php

namespace SqlOrganize\Sql;

use Exception;

class SelectSql {
    protected Db $db;

    public function __construct(Db $db) {
        $this->db = $db;
    }

    public function getDb(): Db {
        return $this->db;
    }

    public function sqlUnique(Entity $entity) {
        $row = $entity->toArray();
        $metadata = $this->db->getEntityMetadataByName($entity->entityName);

        if (empty($row)) {
            throw new Exception("El parámetro de condición única está vacío");
        }

        $whereUniqueList = [];
        foreach ($metadata->unique as $fieldName) {
            foreach ($row as $key => $value) {
                $k = str_replace("$", "", $key);
                if ($k === $fieldName) {
                    if (empty($value)) {
                        continue;
                    }
                    $whereUniqueList[] = "$k = :$k";
                    break;
                }
            }
        }

        $w = empty($whereUniqueList) ? '' : "(" . implode(") OR (", $whereUniqueList) . ")";

        foreach ($metadata->uniqueMultiple as $um) {
            $ww = $this->sqlUniqueMultiple($um, $row);
            if (!empty($ww)) {
                $w .= empty($w) ? $ww : " OR " . $ww;
            }
        }

        if (empty($w)) {
            throw new Exception("No se pudo definir condición de campo único con el parámetro indicado");
        }

        return "SELECT DISTINCT " . $this->sqlFieldsSimple($entity->entityName) .
               " FROM " . $this->sqlFrom($entity->entityName) .
               " WHERE " . $w;
    }

    protected function sqlUniqueMultiple($fields, $params) : string {
        if (empty($fields)) return "";

        $whereMultipleList = [];
        foreach ($fields as $field) {
            if (isset($params[$field])) {
                $value = $params[$field];
                if ($value === null) {
                    $whereMultipleList[] = "$field IS NULL";
                } else {
                    $whereMultipleList[] = "$field = :$field";
                }
            } else {
                return "";
            }
        }
        return "(" . implode(") AND (", $whereMultipleList) . ")";
    }

    protected function sqlFieldsSimple($entityName) {
        $metadata = $this->db->getEntityMetadataByName($entityName);
        return implode(", ", array_map(fn($f) => $this->db->getMapping($entityName).Map($f), $metadata->getFieldNames()));
    }

    protected function sqlFields($entityName) {
        $fields = $this->db->getFieldNamesRelByEntityName($entityName);
        $sql = "";

        foreach ($fields as $fieldName) {
            if (strpos($fieldName, "__") !== false) {
                $ff = explode("__", $fieldName);
                $refEntityName = $this->db->getEntityMetadataByName($entityName)->relations[$ff[0]]['refEntityName'];
                $sql .= $this->db->getMapping($refEntityName, $ff[0])->map($ff[1]) . " AS '" . $fieldName . "', ";
            } else {
                $sql .= $this->db->getMapping($entityName)->map($fieldName) . ", ";
            }
        }
        $sql = rtrim($sql, ',');

        return $sql;
    }

    protected function sqlFrom($entityName) {
        $metadata = $this->db->getEntityMetadataByName($entityName);
        return "FROM " . $metadata->getSchemaNameAlias();
    }

    public function byId($entityName) {
        return "SELECT DISTINCT " . $this->sqlFieldsSimple($entityName) .
               " FROM " . $this->sqlFrom($entityName) .
               " WHERE id = :id";
    }

    public function byIds($entityName) {
        return "SELECT DISTINCT " . $this->sqlFieldsSimple($entityName) .
               " FROM " . $this->sqlFrom($entityName) .
               " WHERE id IN (:ids)";
    }

    public function byIdsAll($entityName) {
        return "SELECT DISTINCT " . $this->sqlFields($entityName) .
               " FROM " . $this->sqlFrom($entityName) .
               " WHERE id IN (:ids)";
    }

    public function byKey($entityName, $key) {
        return "SELECT DISTINCT " . $this->sqlFieldsSimple($entityName) .
               " FROM " . $this->sqlFrom($entityName) .
               " WHERE $key = :key";
    }

    public function existsKey($entityName, $key) {
        return "SELECT 1 FROM " . $this->sqlFrom($entityName) . " WHERE $key = :key";
    }
}
