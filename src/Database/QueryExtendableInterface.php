<?php

namespace DrupalHeadless\Database;


/**
 * Interface for extendable query objects.
 *
 * "Extenders" follow the "Decorator" OOP design pattern.  That is, they wrap
 * and "decorate" another object.  In our case, they implement the same interface
 * as select queries and wrap a select query, to which they delegate almost all
 * operations.  Subclasses of this class may implement additional methods or
 * override existing methods as appropriate.  Extenders may also wrap other
 * extender objects, allowing for arbitrarily complex "enhanced" queries.
 */
interface QueryExtendableInterface {

    /**
     * Enhance this object by wrapping it in an extender object.
     *
     * @param $extender_name
     *   The base name of the extending class.  The base name will be checked
     *   against the current database connection to allow driver-specific subclasses
     *   as well, using the same logic as the query objects themselves.  For example,
     *   PagerDefault_mysql is the MySQL-specific override for PagerDefault.
     * @return QueryExtendableInterface
     *   The extender object, which now contains a reference to this object.
     */
    public function extend($extender_name);
}
