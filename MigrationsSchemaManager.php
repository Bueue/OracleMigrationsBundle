<?php

namespace GOT\OracleMigrationsBundle;

use Doctrine\DBAL\Schema\OracleSchemaManager;

class MigrationsSchemaManager extends OracleSchemaManager
{
    const MV_PREFIX_COLS = 'MV_TAB_COLS';
    const MV_PREFIX_FKS  = 'MV_TAB_FKS';
    const MV_PREFIX_IDXS = 'MV_TAB_IDXS';

    public function listTableColumns($table, $database = null)
    {
        if (!$database) {
            $database = $this->_conn->getDatabase();
        }

        $mvTableColumnsName = $this->_platform->getMvName(self::MV_PREFIX_COLS, $database);

        $sql = $this->_platform->getMvListTableColumnsSQL($mvTableColumnsName, $table);

        $tableColumns = $this->_conn->fetchAll($sql);

        return $this->_getPortableTableColumnList($table, $database, $tableColumns);
    }

    public function listTableForeignKeys($table, $database = null)
    {
        if (is_null($database)) {
            $database = $this->_conn->getDatabase();
        }

        $mvTableForeignKeys = $this->_platform->getMvName(self::MV_PREFIX_FKS, $database);

        $sql              = $this->_platform->getMvListTableForeignKeysSQL($mvTableForeignKeys, $table);
        $tableForeignKeys = $this->_conn->fetchAll($sql);

        return $this->_getPortableTableForeignKeysList($tableForeignKeys);
    }

    public function listTableIndexes($table)
    {
        $mvTableIndexes = $this->_platform->getMvName(self::MV_PREFIX_IDXS, $this->_conn->getDatabase());

        $sql = $this->_platform->getMvListTableIndexesSQL($mvTableIndexes, $table);

        $tableIndexes = $this->_conn->fetchAll($sql);

        return $this->_getPortableTableIndexesList($tableIndexes, $table);
    }
}
