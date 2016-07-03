<?php

namespace DrupalHeadless\Database;


/**
 * General class for an abstracted TRUNCATE operation.
 */
class TruncateQuery extends Query {

    /**
     * The table to truncate.
     *
     * @var string
     */
    protected $table;

    /**
     * Constructs a TruncateQuery object.
     *
     * @param DatabaseConnection $connection
     *   A DatabaseConnection object.
     * @param string $table
     *   Name of the table to associate with this query.
     * @param array $options
     *   Array of database options.
     */
    public function __construct(DatabaseConnection $connection, $table, array $options = array()) {
        $options['return'] = Database::RETURN_AFFECTED;
        parent::__construct($connection, $options);
        $this->table = $table;
    }

    /**
     * Implements QueryConditionInterface::compile().
     */
    public function compile(DatabaseConnection $connection, QueryPlaceholderInterface $queryPlaceholder) {
        return $this->condition->compile($connection, $queryPlaceholder);
    }

    /**
     * Implements QueryConditionInterface::compiled().
     */
    public function compiled() {
        return $this->condition->compiled();
    }

    /**
     * Executes the TRUNCATE query.
     *
     * @return
     *   Return value is dependent on the database type.
     */
    public function execute() {
        return $this->connection->query((string) $this, array(), $this->queryOptions);
    }

    /**
     * Implements PHP magic __toString method to convert the query to a string.
     *
     * @return string
     *   The prepared statement.
     */
    public function __toString() {
        // Create a sanitized comment string to prepend to the query.
        $comments = $this->connection->makeComment($this->comments);

        // In most cases, TRUNCATE is not a transaction safe statement as it is a
        // DDL statement which results in an implicit COMMIT. When we are in a
        // transaction, fallback to the slower, but transactional, DELETE.
        // PostgreSQL also locks the entire table for a TRUNCATE strongly reducing
        // the concurrency with other transactions.
        if ($this->connection->inTransaction()) {
            return $comments . 'DELETE FROM {' . $this->connection->escapeTable($this->table) . '}';
        }
        else {
            return $comments . 'TRUNCATE {' . $this->connection->escapeTable($this->table) . '} ';
        }
    }
}