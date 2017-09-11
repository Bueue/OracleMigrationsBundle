<?php

namespace GOT\OracleMigrationsBundle;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\OracleSchemaManager;
use GOT\OracleMigrationsBundle\Model\MaterializedView;

class MigrationsSchemaManager extends OracleSchemaManager
{
    const MV_PREFIX_COLS = 'MV_TAB_COLS';
    const MV_PREFIX_FKS  = 'MV_TAB_FKS';
    const MV_PREFIX_IDXS = 'MV_TAB_IDXS';

    protected $_mViewTableColumns;
    protected $_mViewTableForeignKeys;
    protected $_mViewTableIndexes;

    public function __construct(\Doctrine\DBAL\Connection $conn, AbstractPlatform $platform = null)
    {
        parent::__construct($conn, $platform);
        $this->initializeMViews();
    }

    public function listTableColumns($table, $database = null)
    {
        if (!$database) {
            $database = $this->_conn->getDatabase();
        }

        $tableColumns = $this->_mViewTableColumns->fetchAll($table);

        return $this->_getPortableTableColumnList($table, $database, $tableColumns);
    }

    public function listTableForeignKeys($table, $database = null)
    {
        if (is_null($database)) {
            $database = $this->_conn->getDatabase();
        }

        $tableForeignKeys = $this->_mViewTableForeignKeys->fetchAll($table);

        return $this->_getPortableTableForeignKeysList($tableForeignKeys);
    }

    public function listTableIndexes($table)
    {
        $tableIndexes = $this->_mViewTableIndexes->fetchAll($table);

        return $this->_getPortableTableIndexesList($tableIndexes, $table);
    }

    protected function initializeMViews()
    {
        # Init table columns
        $this->_mViewTableColumns = new MaterializedView(
            self::MV_PREFIX_COLS,
            $this->_conn,
            $this->_platform,
            MaterializedView::TYPE_TAB_COLS
        );

        # Init table foreign keys
        $this->_mViewTableForeignKeys = new MaterializedView(
            self::MV_PREFIX_FKS,
            $this->_conn,
            $this->_platform,
            MaterializedView::TYPE_TAB_FKS
        );

        # Init table indexes
        $this->_mViewTableIndexes = new MaterializedView(
            self::MV_PREFIX_IDXS,
            $this->_conn,
            $this->_platform,
            MaterializedView::TYPE_TAB_IDXS
        );
    }
}
