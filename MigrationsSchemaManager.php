<?php

namespace GOT\OracleMigrationsBundle;

use Doctrine\DBAL\Schema\OracleSchemaManager;

class MigrationsSchemaManager extends OracleSchemaManager
{
    const MV_PREFIX_COLS = 'MV_TAB_COLS';
    const MV_PREFIX_FKS  = 'MV_TAB_FKS';
    const MV_PREFIX_IDXS = 'MV_TAB_IDXS';

}
