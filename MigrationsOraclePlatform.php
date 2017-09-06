<?php

namespace GOT\OracleMigrationsBundle;

use Doctrine\DBAL\Platforms\OraclePlatform;
use Doctrine\DBAL\Schema\Identifier;

class MigrationsOraclePlatform extends OraclePlatform
{

    public function getMvName($prefix, $database)
    {
        if (!$database) {
            throw new \InvalidArgumentException("Invalid database");
        }

        $database = $this->normalizeIdentifier($database);

        $mvName = $prefix . '_' . $database->getName();

        if (mb_strlen($mvName) > 30) {
            throw new \LengthException("Materialized view name too long (\"$mvName\"), please choose shorter prefix");
        }

        return $mvName;
    }

    public function getMvListTableColumnsSQL($mvName, $table)
    {
        $table = $this->normalizeIdentifier($table);

        return "SELECT * FROM $mvName c WHERE c.table_name = '{$table->getName()}' ORDER BY c.column_name ASC";
    }

    public function getMvListTableForeignKeysSQL($mvName, $table)
    {
        $table = $this->normalizeIdentifier($table);

        return "SELECT * FROM $mvName c WHERE c.table_name = '{$table->getName()}' ORDER BY c.constraint_name ASC,c.position ASC";
    }

    public function getMvListTableIndexesSQL($mvName, $table)
    {
        $table = $this->normalizeIdentifier($table);

        return "SELECT * FROM $mvName c WHERE c.table_name = '{$table->getName()}' ORDER BY c.column_pos ASC";
    }
    private function normalizeIdentifier($name)
    {
        $identifier = new Identifier($name);

        return $identifier->isQuoted() ? $identifier : new Identifier(strtoupper($name));
    }
}
