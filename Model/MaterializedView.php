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
     * Return the type of materialized view.
     * @return int one of constants declared in this class.
     */
    public function getType()
    {
        return $this->_type;
    }
}
