<?php

namespace DrupalHeadless\Entity\Model;


class Taxonomy implements ModelInterface
{

    use ModelBase;

    protected static $info = array(
        'entity'         => 'taxonomy_term',
        'table'          => 'taxonomy_term_data',
        'fieldable'      => true,
        'revision_table' => false,
        'keys'           => array(
            'id'            => 'tid'
        ),
    );

    protected static $static_relations = array
    (
        'parents'   => array
        (
            'node_type'         => array
            (
                'table'         => 'taxonomy_vocabulary',
                'keys'          => array
                (
                    'vid:vid'
                )
            )
        ),
        'children' => array
        (

            'term_hierarchy'    => array
            (
                'table'         => 'taxonomy_term_hierarchy',
                'cardinality'   => 1,
                'language'      => false,
                'keys'          => array
                (
                    'tid:tid'
                ),
                'fields'        => array
                (
                    'parent'    => 'parent'
                )
            )
        ),
        'revision' => array
        (
        )
    );
    
    protected static $dynamic_relations = array();
    
}