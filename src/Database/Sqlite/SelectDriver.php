<?php
namespace DrupalHeadless\Database\Sqlite;

use DrupalHeadless\Database\SelectQuery;

/**
 * @file
 * Select builder for SQLite embedded database engine.
 */

/**
 * @addtogroup database
 * @{
 */

/**
 * SQLite specific query builder for SELECT statements.
 */
class SelectQuery_sqlite extends SelectQuery {
  public function forUpdate($set = TRUE) {
    // SQLite does not support FOR UPDATE so nothing to do.
    return $this;
  }
}

/**
 * @} End of "addtogroup database".
 */


