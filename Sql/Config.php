<?php
namespace SqlOrganize\Sql;

class Config {

    /**
     * String de conexión con la base de datos
     */
    public string $connectionString;

    /**
     * Nombre del identificador único de las tablas
     * Todas las tablas deben tener un identificador
     */
    public string $id = "id";

    /**
     * Nombre de la base de datos
     */
    public string $dbName;
}