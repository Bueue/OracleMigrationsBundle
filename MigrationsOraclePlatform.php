<?php

namespace Bueue\OracleMigrationsBundle;

use Doctrine\DBAL\Platforms\OraclePlatform;
use Doctrine\DBAL\Schema\Identifier;

class MigrationsOraclePlatform extends OraclePlatform
{

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

    public function getCreateMvListTableIndexesSQL($mvName)
    {
        return "
        CREATE materialized VIEW $mvName
        AS
          SELECT uind.index_name                                   AS name,
            uind.index_type                                        AS type,
            DECODE( uind.uniqueness, 'NONUNIQUE', 0, 'UNIQUE', 1 ) AS is_unique,
            uind_col.column_name                                   AS column_name,
            uind_col.column_position                               AS column_pos,
            ucon.constraint_type                                   AS is_primary,
            uind_col.table_name
          FROM user_indexes uind
          INNER JOIN user_ind_columns uind_col ON uind.index_name = uind_col.index_name
          LEFT JOIN user_constraints ucon ON ucon.constraint_name = uind.index_name
          ORDER BY uind_col.column_position ASC";
    }

    public function getCreateMvListTableForeignKeysSQL($mvName)
    {
        return "
        CREATE materialized VIEW $mvName
        AS
          SELECT alc.constraint_name,
                alc.DELETE_RULE,
                to_lob(alc.search_condition) search_condition,
                cols.column_name \"local_column\",
                cols.position,
                r_alc.table_name \"references_table\",
                r_cols.column_name \"foreign_column\",
                alc.table_name
           FROM user_cons_columns cols
          LEFT JOIN user_constraints alc
             ON alc.constraint_name = cols.constraint_name
          LEFT JOIN user_constraints r_alc
             ON alc.r_constraint_name = r_alc.constraint_name
          LEFT JOIN user_cons_columns r_cols
             ON r_alc.constraint_name = r_cols.constraint_name
            AND cols.position = r_cols.position
          WHERE alc.constraint_name = cols.constraint_name
            AND alc.constraint_type = 'R'
          ORDER BY alc.constraint_name ASC, cols.position ASC";
    }

    public function getCreateMvListTableColumnsSQL($mvName, $database)
    {
        $database = $this->normalizeIdentifier($database);

        return "
        CREATE materialized VIEW $mvName
        AS
          SELECT c.IDENTITY_COLUMN,
            c.EVALUATION_EDITION,
            c.UNUSABLE_BEFORE,
            c.UNUSABLE_BEGINNING,
            --c.OWNER,
            c.TABLE_NAME,
            c.COLUMN_NAME,
            c.DATA_TYPE,
            c.DATA_TYPE_MOD,
            c.DATA_TYPE_OWNER,
            c.DATA_LENGTH,
            c.DATA_PRECISION,
            c.DATA_SCALE,
            c.NULLABLE,
            c.COLUMN_ID,
            c.DEFAULT_LENGTH,
            to_lob(c.DATA_DEFAULT) AS DATA_DEFAULT,
            c.NUM_DISTINCT,
            c.LOW_VALUE,
            c.HIGH_VALUE,
            c.DENSITY,
            c.NUM_NULLS,
            c.NUM_BUCKETS,
            c.LAST_ANALYZED,
            c.SAMPLE_SIZE,
            c.CHARACTER_SET_NAME,
            c.CHAR_COL_DECL_LENGTH,
            c.GLOBAL_STATS,
            c.USER_STATS,
            c.AVG_COL_LEN,
            c.CHAR_LENGTH,
            c.CHAR_USED,
            c.V80_FMT_IMAGE,
            c.DATA_UPGRADED,
            c.HISTOGRAM,
            c.DEFAULT_ON_NULL,
            d.comments
          FROM all_tab_columns c
          INNER JOIN all_col_comments d
          ON d.TABLE_NAME   = c.TABLE_NAME
          AND d.COLUMN_NAME = c.COLUMN_NAME
          WHERE c.owner = '{$database->getName()}'
          ORDER BY c.column_name";
    }

    public function getCreateMvTableNameIndexSQL($mvName, $mvIndexName)
    {
        return "CREATE INDEX $mvIndexName ON $mvName (TABLE_NAME)";
    }

    public function normalizeIdentifier($name)
    {
        $identifier = new Identifier($name);

        return $identifier->isQuoted() ? $identifier : new Identifier(strtoupper($name));
    }

    public function getRefreshMvSQL($mvName)
    {
        return "BEGIN
                   dbms_mview.refresh('$mvName');
               END;";
    }

    public function getCheckRefreshMvSQL($mvName)
    {
        return "SELECT COUNT(*)
                FROM user_objects
                WHERE last_ddl_time >
                  (SELECT last_refresh_date
                  FROM all_mviews
                  WHERE mview_name = '$mvName'
                  )";
    }

    public function getCheckExistsMvSQL($mvName)
    {
        return "SELECT COUNT(*) FROM all_mviews WHERE mview_name = '$mvName'";
    }
}
