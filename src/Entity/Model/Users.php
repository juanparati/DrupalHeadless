<?php

namespace DrupalHeadless\Entity\Model;


class Users implements ModelInterface
{

    use ModelBase;

    protected $info = array(
        'entity'         => 'user',
        'table'          => 'users',
        'fieldable'      => true,
        'keys'           => array(
            'id'            => 'uid'
        ),
    );

    protected $static_relations = array
    (
        'children' => array
        (
        ),
        'revision' => array
        (
        )
    );

    protected $dynamic_relations = array();

}