<?php

namespace GOT\OracleMigrationsBundle\Model;

use Doctrine\DBAL\Schema\AbstractAsset;

class MaterializedView extends AbstractAsset
{
    const TYPE_TAB_COLS = 1;
    const TYPE_TAB_FKS  = 2;
    const TYPE_TAB_IDXS = 3;

    protected $_prefix;
    protected $_name;
    protected $_conn;
    protected $_database;
    protected $_platform;
    protected $_type;

    public function __construct($prefix, $conn, $platform, $type)
    {
        if (!$conn) {
            throw new \InvalidArgumentException("Invalid Connection");
        }

        $this->_prefix   = $prefix;
        $this->_conn     = $conn;
        $this->_database = $this->_conn->getDatabase();
        $this->_platform = $platform;
        $this->_type     = $type;

        $this->_name = $this->getMvName($this->_prefix, $this->_database);
        $this->_setName($this->_name);

        $createdOrRefreshed = $this->createOrRefresh();
    }

    /**
     * Create a name for MaterializedView
     * @param  string $prefix
     * @param  string $database
     * @return string
     */
    protected function getMvName($prefix, $database)
    {
        if (!$database) {
            throw new \InvalidArgumentException("Invalid database");
        }

        $database = $this->_platform->normalizeIdentifier($database);

        $mvName = $prefix . '_' . $database->getName();

        if (mb_strlen($mvName) > 30) {
            throw new \LengthException("Materialized view name too long (\"$mvName\"), please choose shorter prefix");
        }

        return $mvName;
    }

    /**
     * Create a name for MaterializedView index.
     * @param  string $prefix
     * @param  string $database
     * @return string
     */
    protected function getMvIndexName()
    {
        $mvIndexName = 'IDX_' . $this->_name . '_1';

        if (mb_strlen($mvIndexName) > 30) {
            throw new \LengthException("Materialized view index name too long (\"$mvIndexName\"), please choose shorter prefix");
        }

        return $mvIndexName;
    }

    /**
     * Executes the sql to create the materialized view.
     * @param   $_conn
     * @return  MaterializedView
     */
    public function create()
    {
        # Get the SQL create from the platform
        switch ($this->_type) {
            case self::TYPE_TAB_COLS:
                $sql = $this->_platform->getCreateMvListTableColumnsSQL($this->_name, $this->_conn->getDatabase());
                break;
            case self::TYPE_TAB_FKS:
                $sql = $this->_platform->getCreateMvListTableForeignKeysSQL($this->_name);
                break;
            case self::TYPE_TAB_IDXS:
                $sql = $this->_platform->getCreateMvListTableIndexesSQL($this->_name);
                break;
        }

        # Create Materialized View
        $this->executeQuery($sql);
        # Create Materialized View index
        $this->executeQuery(
            $this->_platform->getCreateMvTableNameIndexSQL(
                $this->_name,
                $this->getMvIndexName()
            ));

        return $this;
    }

    /**
     * Executes the refresh of a materialiazed view.
     * @param   $_conn
     * @return  boolean Return true if the materialized view is refreshed.
     */
    public function refresh()
    {
        $updated = false;
        if (!$this->isUpdated()) {
            $this->executeQuery($this->_platform->getRefreshMvSQL($this->_name));
            $updated = true;
        }

        return $updated;
    }

    /**
     * Check if the materialized view contains the last DDL entries.
     * @param $_conn
     * @return boolean
     */
    public function isUpdated()
    {
        return (bool) !$this->_conn->fetchColumn($this->_platform->getCheckRefreshMvSQL($this->_name));
    }

    /**
     * Check if the materialized view exists on database.
     * @return boolean
     */
    public function exists()
    {
        return (bool) $this->_conn->fetchColumn($this->_platform->getCheckExistsMvSQL($this->_name));
    }

    /**
     * Retrieves all requested records
     * @return array
     */
    public function fetchAll($tableName)
    {
        switch ($this->_type) {
            case self::TYPE_TAB_COLS:
                $sql = $this->_platform->getMvListTableColumnsSQL($this->_name, $tableName);
                break;
            case self::TYPE_TAB_FKS:
                $sql = $this->_platform->getMvListTableForeignKeysSQL($this->_name, $tableName);
                break;
            case self::TYPE_TAB_IDXS:
                $sql = $this->_platform->getMvListTableIndexesSQL($this->_name, $tableName);
                break;
        }

        return $this->_conn->fetchAll($sql);
    }

    /**
     * Return the type of materialized view.
     * @return int one of constants declared in this class.
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Execute a query
     * @param  string $sql
     * @return \Doctrine\DBAL\Driver\Statement
     */
    protected function executeQuery($sql)
    {
        return $this->_conn->executeQuery($sql);
    }

    /**
     * Create if the materialized view does not exist or refresh if is necessary.
     * @return boolean Return true if materialized view is created or refreshed.
     */
    protected function createOrRefresh()
    {
        $created   = false;
        $refreshed = false;
        if (!$this->exists()) {
            $this->create();
            $created = true;
        } else {
            $refreshed = $this->refresh();
        }

        return ($created || $refreshed);
    }

}
