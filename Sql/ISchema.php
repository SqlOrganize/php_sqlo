<?php

namespace SqlOrganize\Sql;

/**
 * Configuración del esquema de la base de datos
 */
interface ISchema
{
    /**
     * JSON con entidades del modelo
     * 
     * @var array
     */
    public function getEntities(): array;
}
