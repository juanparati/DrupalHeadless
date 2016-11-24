<?php

namespace DrupalHeadless\Entity\Model;


class Node implements ModelInterface
{
    
    use ModelBase;

    protected $info = array(
        'entity'         => 'node',
        'table'          => 'node',
        'fieldable'      => true,
        'revision_table' => 'node_revision',
        'keys'           => array(
            'id'            => 'nid',
            'revision'      => 'vid'
        ),
    );

    protected $static_relations = array
    (
        'children' => array
        (
            'body'    => array
            (
                'table'         => 'field_data_body',
                'cardinality'   => -1,
                'keys'          => array
                (
                    'nid:entity_id',
                    'type:bundle',
                    '#node:entity_type'
                ),
                'fields'        => array
                (
                    'value'     => 'body_value'
                )
            ),
            'taxonomy_index'    => array
            (
                'table'         => 'taxonomy_index',
                'cardinality'   => -1,
                'keys'          => array
                (
                    'nid:nid'
                ),
                'fields'        => array
                (
                    'tid'       => 'tid'
                )
            )
        ),
        'revision' => array(
        )
    );

    protected $dynamic_relations = array();

}