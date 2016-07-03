<?php

namespace DrupalHeadless\Entity\Model;


class Users implements ModelInterface
{

    use ModelBase;

    protected static $info = array(
        'entity'         => 'user',
        'table'          => 'users',
        'fieldable'      => true,
        'keys'           => array(
            'id'            => 'uid'
        ),
    );

    protected static $static_relations = array
    (
        'children' => array
        (
        ),
        'revision' => array
        (
        )
    );

    protected static $dynamic_relations = array();

}