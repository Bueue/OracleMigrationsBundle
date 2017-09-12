<?php

namespace Bueue\OracleMigrationsBundle;

use Doctrine\DBAL\Driver\OCI8\Driver;

class MigrationsDriver extends Driver
{
    public function getName()
    {
        return 'MigrationsDriver';
    }

    public function getSchemaManager(\Doctrine\DBAL\Connection $conn)
    {
        return new MigrationsSchemaManager($conn);
    }

    public function getDatabasePlatform()
    {
        return new MigrationsOraclePlatform();
    }
}
