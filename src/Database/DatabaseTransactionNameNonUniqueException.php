<?php

namespace DrupalHeadless\Database;


/**
 * Exception thrown when a savepoint or transaction name occurs twice.
 */
class DatabaseTransactionNameNonUniqueException extends \Exception { }