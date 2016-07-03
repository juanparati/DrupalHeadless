<?php
namespace DrupalHeadless\Database;

/**
 * Exception for when popTransaction() is called with no active transaction.
 */
class DatabaseTransactionNoActiveException extends \Exception { }