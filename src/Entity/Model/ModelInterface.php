<?php

namespace DrupalHeadless\Entity\Model;


interface ModelInterface
{


    /**
     * Entity getter/setter
     *
     * @param null $value
     * @return null|string
     */
    public function info($value = null);



    /**
     * Static relations getter/setter
     *
     * @param null $value
     * @return null|string
     */
    public function static_relations($value = null);


    /**
     * Dynamic relations getter/setter
     *
     * @param null $value
     * @return array|null
     */
    public function dynamic_relations($value = null);


}