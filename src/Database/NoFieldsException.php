<?php

namespace DrupalHeadless\Database;


/**
 * Exception thrown if an insert query doesn't specify insert or default fields.
 */
class NoFieldsException extends \Exception {}