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

    private function normalizeIdentifier($name)
    {
        $identifier = new Identifier($name);

        return $identifier->isQuoted() ? $identifier : new Identifier(strtoupper($name));
    }
}
