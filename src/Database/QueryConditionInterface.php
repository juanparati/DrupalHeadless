<?php

namespace DrupalHeadless\Database;



/**
 * Interface for a conditional clause in a query.
 */
interface QueryConditionInterface {

    /**
     * Helper function: builds the most common conditional clauses.
     *
     * This method can take a variable number of parameters. If called with two
     * parameters, they are taken as $field and $value with $operator having a
     * value of IN if $value is an array and = otherwise.
     *
     * Do not use this method to test for NULL values. Instead, use
     * QueryConditionInterface::isNull() or QueryConditionInterface::isNotNull().
     *
     * @param $field
     *   The name of the field to check. If you would like to add a more complex
     *   condition involving operators or functions, use where().
     * @param $value
     *   The value to test the field against. In most cases, this is a scalar.
     *   For more complex options, it is an array. The meaning of each element in
     *   the array is dependent on the $operator.
     * @param $operator
     *   The comparison operator, such as =, <, or >=. It also accepts more
     *   complex options such as IN, LIKE, or BETWEEN. Defaults to IN if $value is
     *   an array, and = otherwise.
     *
     * @return QueryConditionInterface
     *   The called object.
     *
     * @see QueryConditionInterface::isNull()
     * @see QueryConditionInterface::isNotNull()
     */
    public function condition($field, $value = NULL, $operator = NULL);

    /**
     * Adds an arbitrary WHERE clause to the query.
     *
     * @param $snippet
     *   A portion of a WHERE clause as a prepared statement. It must use named
     *   placeholders, not ? placeholders.
     * @param $args
     *   An associative array of arguments.
     *
     * @return QueryConditionInterface
     *   The called object.
     */
    public function where($snippet, $args = array());

    /**
     * Sets a condition that the specified field be NULL.
     *
     * @param $field
     *   The name of the field to check.
     *
     * @return QueryConditionInterface
     *   The called object.
     */
    public function isNull($field);

    /**
     * Sets a condition that the specified field be NOT NULL.
     *
     * @param $field
     *   The name of the field to check.
     *
     * @return QueryConditionInterface
     *   The called object.
     */
    public function isNotNull($field);

    /**
     * Sets a condition that the specified subquery returns values.
     *
     * @param SelectQueryInterface $select
     *   The subquery that must contain results.
     *
     * @return QueryConditionInterface
     *   The called object.
     */
    public function exists(SelectQueryInterface $select);

    /**
     * Sets a condition that the specified subquery returns no values.
     *
     * @param SelectQueryInterface $select
     *   The subquery that must not contain results.
     *
     * @return QueryConditionInterface
     *   The called object.
     */
    public function notExists(SelectQueryInterface $select);

    /**
     * Gets a complete list of all conditions in this conditional clause.
     *
     * This method returns by reference. That allows alter hooks to access the
     * data structure directly and manipulate it before it gets compiled.
     *
     * The data structure that is returned is an indexed array of entries, where
     * each entry looks like the following:
     * @code
     * array(
     *   'field' => $field,
     *   'value' => $value,
     *   'operator' => $operator,
     * );
     * @endcode
     *
     * In the special case that $operator is NULL, the $field is taken as a raw
     * SQL snippet (possibly containing a function) and $value is an associative
     * array of placeholders for the snippet.
     *
     * There will also be a single array entry of #conjunction, which is the
     * conjunction that will be applied to the array, such as AND.
     */
    public function &conditions();

    /**
     * Gets a complete list of all values to insert into the prepared statement.
     *
     * @return
     *   An associative array of placeholders and values.
     */
    public function arguments();

    /**
     * Compiles the saved conditions for later retrieval.
     *
     * This method does not return anything, but simply prepares data to be
     * retrieved via __toString() and arguments().
     *
     * @param $connection
     *   The database connection for which to compile the conditionals.
     * @param $queryPlaceholder
     *   The query this condition belongs to. If not given, the current query is
     *   used.
     */
    public function compile(DatabaseConnection $connection, QueryPlaceholderInterface $queryPlaceholder);

    /**
     * Check whether a condition has been previously compiled.
     *
     * @return
     *   TRUE if the condition has been previously compiled.
     */
    public function compiled();
}