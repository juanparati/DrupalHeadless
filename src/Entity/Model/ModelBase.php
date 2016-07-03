<?php

namespace DrupalHeadless\Entity\Model;


/**
 * Trait ModelBase
 *
 * Instead of an abstract class a trait is used in order to avoid an anti-pattern related to the php static biding.
 * Using a trait the static properties of each entity are not going to be overwritten.
 *
 * @package DrupalHeadless\Entity\Model
 */
trait ModelBase
{


    /**
     * Entity getter/setter
     *
     * @param null $value
     * @return null|string
     */
    public function info($value = null)
    {

        if ($value)
            return static::$info = $value;
        else
            return static::$info;
    }


    /**
     * Static relations getter/setter
     *
     * @param null $value
     * @return null|string
     */
    public function static_relations($value = null)
    {

        if ($value)
            return static::$static_relations = $value;
        else
            return static::$static_relations;
    }


    /**
     * Dynamic relations getter/setter
     *
     * @param null $value
     * @return array|null
     */
    public function dynamic_relations($value = null)
    {
        if ($value)
            return static::$dynamic_relations = $value;
        else
            return static::$dynamic_relations;
    }


}