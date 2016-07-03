<?php
namespace DrupalHeadless\Database\Sqlite;

use DrupalHeadless\Database\InsertQuery;
use DrupalHeadless\Database\TruncateQuery;
use DrupalHeadless\Database\UpdateQuery;
use DrupalHeadless\Database\DeleteQuery;

/**
 * @file
 * Query code for SQLite embedded database engine.
 */

/**
 * @addtogroup database
 * @{
 */

/**
 * SQLite specific implementation of InsertQuery.
 *
 * We ignore all the default fields and use the clever SQLite syntax:
 *   INSERT INTO table DEFAULT VALUES
 * for degenerated "default only" queries.
 */
class InsertQuery_sqlite extends InsertQuery {

  public function execute() {
    if (!$this->preExecute()) {
      return NULL;
    }
    if (count($this->insertFields)) {
      return parent::execute();
    }
    else {
      return $this->connection->query('INSERT INTO {' . $this->table . '} DEFAULT VALUES', array(), $this->queryOptions);
    }
  }

  public function __toString() {
    // Create a sanitized comment string to prepend to the query.
    $comments = $this->connection->makeComment($this->comments);

    // Produce as many generic placeholders as necessary.
    $placeholders = array_fill(0, count($this->insertFields), '?');

    // If we're selecting from a SelectQuery, finish building the query and
    // pass it back, as any remaining options are irrelevant.
    if (!empty($this->fromQuery)) {
      $insert_fields_string = $this->insertFields ? ' (' . implode(', ', $this->insertFields) . ') ' : ' ';
      return $comments . 'INSERT INTO {' . $this->table . '}' . $insert_fields_string . $this->fromQuery;
    }

    return $comments . 'INSERT INTO {' . $this->table . '} (' . implode(', ', $this->insertFields) . ') VALUES (' . implode(', ', $placeholders) . ')';
  }

}

/**
 * SQLite specific implementation of UpdateQuery.
 *
 * SQLite counts all the rows that match the conditions as modified, even if they
 * will not be affected by the query. We workaround this by ensuring that
 * we don't select those rows.
 *
 * A query like this one:
 *   UPDATE test SET col1 = 'newcol1', col2 = 'newcol2' WHERE tid = 1
 * will become:
 *   UPDATE test SET col1 = 'newcol1', col2 = 'newcol2' WHERE tid = 1 AND (col1 <> 'newcol1' OR col2 <> 'newcol2')
 */
class UpdateQuery_sqlite extends UpdateQuery {
  public function execute() {
    if (!empty($this->queryOptions['sqlite_return_matched_rows'])) {
      return parent::execute();
    }

    // Get the fields used in the update query.
    $fields = $this->expressionFields + $this->fields;

    // Add the inverse of the fields to the condition.
    $condition = new DatabaseCondition('OR');
    foreach ($fields as $field => $data) {
      if (is_array($data)) {
        // The field is an expression.
        $condition->where($field . ' <> ' . $data['expression']);
        $condition->isNull($field);
      }
      elseif (!isset($data)) {
        // The field will be set to NULL.
        $condition->isNotNull($field);
      }
      else {
        $condition->condition($field, $data, '<>');
        $condition->isNull($field);
      }
    }
    if (count($condition)) {
      $condition->compile($this->connection, $this);
      $this->condition->where((string) $condition, $condition->arguments());
    }
    return parent::execute();
  }

}

/**
 * SQLite specific implementation of DeleteQuery.
 *
 * When the WHERE is omitted from a DELETE statement and the table being deleted
 * has no triggers, SQLite uses an optimization to erase the entire table content
 * without having to visit each row of the table individually.
 *
 * Prior to SQLite 3.6.5, SQLite does not return the actual number of rows deleted
 * by that optimized "truncate" optimization.
 */
class DeleteQuery_sqlite extends DeleteQuery {
  public function execute() {
    if (!count($this->condition)) {
      $total_rows = $this->connection->query('SELECT COUNT(*) FROM {' . $this->connection->escapeTable($this->table) . '}')->fetchField();
      parent::execute();
      return $total_rows;
    }
    else {
      return parent::execute();
    }
  }
}

/**
 * SQLite specific implementation of TruncateQuery.
 *
 * SQLite doesn't support TRUNCATE, but a DELETE query with no condition has
 * exactly the effect (it is implemented by DROPing the table).
 */
class TruncateQuery_sqlite extends TruncateQuery {
  public function __toString() {
    // Create a sanitized comment string to prepend to the query.
    $comments = $this->connection->makeComment($this->comments);

    return $comments . 'DELETE FROM {' . $this->connection->escapeTable($this->table) . '} ';
  }
}

/**
 * @} End of "addtogroup database".
 */
