<?php

namespace DrupalHeadless\Database;


/**
 * Exception thrown if an object being modified doesn't exist yet.
 *
 * For example, this exception should be thrown whenever there is an attempt to
 * modify a database table, field, or index that does not currently exist in
 * the database schema.
 */
class DatabaseSchemaObjectDoesNotExistException extends \Exception {}