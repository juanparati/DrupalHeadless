<?php

namespace DrupalHeadless\Entity;

use DrupalHeadless\Database\DatabaseCondition;
use DrupalHeadless\Database\DatabaseConnection;
use DrupalHeadless\Entity\Model\ModelInterface;


class EntityController
{

    /**
     * Entity
     *
     * @var ModelInterface
     */
    protected $entity;


    /**
     * The current bundle
     *
     * @var string
     */
    protected $bundle;


    /**
     * Database connection
     *
     * @var DatabaseConnection
     */
    protected $db;


    /**
     * @var \DrupalHeadless\Database\SelectQueryInterface
     */
    protected $st;


    /**
     * Merged relations
     *
     * @var array
     */
    protected $relations = array();


    /**
     * Merged relations for revision
     *
     * @var array
     */
    protected $relations_revisions = array();


    /**
     * Language
     *
     * @var string
     */
    protected $language = 'und';


    /**
     * Singleton instances
     *
     * @var array
     */
    protected static $_instance = array();


    /**
     * EntityController constructor.
     *
     * @param DatabaseConnection $db
     * @param ModelInterface $entity
     * @param $bundle
     */
    public function __construct(DatabaseConnection $db, ModelInterface $entity, $bundle, $language = 'und')
    {
        $this->db       = $db;
        $this->bundle   = $bundle;
        $this->entity   = $entity;
        $this->language = $language;

        // Build dynamic field relations
        if ($this->entity->info()['fieldable'] && empty($this->entity->dynamic_relations()))
            $this->_build_dynamic_relations();
    }


    /**
     * Singleton constructor
     *
     * @param DatabaseConnection $db
     * @param ModelInterface $entity
     * @param $bundle
     * @return static
     */
    public static function entity(DatabaseConnection $db, ModelInterface $entity, $bundle)
    {

        if (!isset(static::$_instance[$entity->info()['table']][$bundle]))
            return static::$_instance[$entity->info()['table']][$bundle] = new static($db, $entity, $bundle);

        return static::$_instance[$entity->info()['table']][$bundle];

    }


    /**
     * Load the entity relations
     *
     * @param string|array   $include_fields    Fields to include
     * @return $this
     */
    public function load($include_fields = '*')
    {

        $static_relations  = $this->entity->static_relations();
        $dynamic_relations = $this->entity->dynamic_relations();


        if (isset($static_relations['children']))
            $this->relations += $static_relations['children'];

        if (isset($dynamic_relations['children']))
            $this->relations += $dynamic_relations['children'];

        if (isset($static_relations['revision']))
            $this->relations_revisions += $static_relations['revision'];

        if (isset($dynamic_relations['revision']))
            $this->relations_revisions += $dynamic_relations['revision'];


        foreach ($this->relations as $krelation => $gfield)
        {

            if ($include_fields != '*')
            {
                $found = false;

                if (is_array($include_fields))
                {

                    foreach ($include_fields as $field)
                    {
                        if ($field[0] == '!' && substr($field, 1) == $krelation)
                            break;

                        if ($field === '*' || $field == $krelation)
                        {
                            $found = true;
                            break;
                        }
                    }
                }


                if (!$found)
                {
                    unset($this->relations[$krelation]);
                    continue;
                }
            }

        }


        return $this;

    }


    /**
     * Begin a new query
     *
     * @return $this
     */
    public function select()
    {

        $this->st = $this->db->select($this->entity->info()['table'], 'main');
        $this->st->fields('main');

        if (!empty($this->relations))
        {

            foreach ($this->relations as $krelation => $gfield)
            {

                $this->st->leftJoin($gfield['table'], $krelation, $this->_buildJoinLink('main', $krelation, $gfield));

                foreach ($gfield['fields'] as $kfield => $field)
                {

                    // Those elements with cardinality are going to be retrieved later as an array
                    if ($gfield['cardinality'] != 1)
                        continue;

                    $this->st->addField($krelation, $field);
                }


            }

        }


        return $this;
    }


    /**
     * Insert a new entity element
     *
     * @param array $columns
     * @return \DrupalHeadless\Database\DatabaseStatementInterface|int
     */
    public function insert(array $columns)
    {
        return $this->db->insert($this->entity->info()['table'])->fields($columns)->execute();
    }


    /**
     * Delete record(s) from a fieldset
     *
     * @param $fieldset
     * @param $entity_id
     * @param int $delta
     * @return $this
     */
    public function deleteFieldset($fieldset, $entity_id, $delta = 0)
    {
        $this->st = $this->db->delete($this->relations[$fieldset]['table']);

        $this->_build_fieldset_op_conditions($fieldset, $entity_id, $delta);

        return $this;
    }


    /**
     * Insert a new record into the fieldset
     *
     * @param $fieldset
     * @param $entity_id
     * @param array $columns
     * @param int $delta
     * @return $this
     */
    public function insertFieldset($fieldset, $entity_id, array $columns, $delta = 0, $revision = false)
    {
        $this->st = $this->db->insert($this->relations[$fieldset]['table']);

        $field_values = [];

        $index_name = $this->entity->info()['keys']['id'];

        // Add fields link fields
        foreach ($this->relations[$fieldset]['keys'] as $key)
        {
            list($entity_key, $fieldset_key) = explode(':', $key);

            if ($entity_key == $index_name)
                $entity_key = $entity_id;

            if ($entity_key[0] === '#')
                $entity_key = substr($entity_key, 1);

            if ($fieldset_key[0] == '#')
                $fieldset_key = substr($fieldset_key, 1);

            $field_values[$fieldset_key] = $entity_key;

        }


        if (!isset($this->relations[$fieldset]['language']) || $this->relations[$fieldset]['language'] !== false)
            $field_values['language'] = $this->language;

        if ($delta !== false)
            $field_values['delta'] = $delta;


        foreach ($columns as $k => $value)
        {
            if (isset($this->relations[$fieldset]['fields'][$k]))
                $field_values[$this->relations[$fieldset]['fields'][$k]] = $value;
        }


        // Get last multified ID
        /*
        if (isset($this->relations[$fieldset]['type']) && $this->relations[$fieldset]['type'] == 'multifield')
        {
            $max_id = $this->db->select('variable', 'var')
                ->addField('var', 'value')
                ->condition('var.name', 'multifield_max_id')
                ->execute()
                ->fetchColumn();

            $max_id = $max_id === false ? 0 : unserialize(max_id);

            $max_id++;

            $field_values[$this->relations[$fieldset]['fields'][$fieldset . '_id']] = $max_id;
        }
        */


        /*
        if ($revision !== false)
            $field_values['revision_id'] = $revision;
        */


        $this->st->fields($field_values);
        $status = $this->st->execute();


        /*
        if (!$status && $revision !== false)
        {
            $field_values['entity_id'] = $this->db->lastInsertId();

            $this->st = $this->db->insert($this->relations_revisions[$fieldset]['table']);
            $this->st->fields($field_values);
            $status = $this->st->execute();
        }
        */


        return $status;


    }

    /**
     * Update a fieldset
     *
     * @param $fieldset
     * @param $entity_id
     * @param array $columns
     * @param int $delta
     * @return $this
     */
    public function updateFieldset($fieldset, $entity_id, array $columns, $delta = 0)
    {

        $this->st = $this->db->update($this->relations[$fieldset]['table']);

        $this->_build_fieldset_op_conditions($fieldset, $entity_id, $delta);

        $field_values = array();

        foreach ($columns as $k => $value)
        {
            if (isset($this->relations[$fieldset]['fields'][$k]))
                $field_values[$this->relations[$fieldset]['fields'][$k]] = $value;
        }


        if (!empty($field_values))
            $this->st->fields($field_values);


        return $this->execute();
    }


    public function fields($values)
    {
        $this->st->fields($values);

        return $this;
    }


    /**
     * Add a query condition
     *
     * @param $property
     * @param $value
     * @param string $condition
     * @return $this
     */
    public function propertyCondition($property, $value, $condition = '=')
    {
        $this->st->condition('main.' . $property, $value, $condition);

        return $this;
    }


    /**
     * Add a query condition for linked field
     * @param $fieldset
     * @param $column
     * @param $value
     * @param string $condition
     * @return $this
     */
    public function fieldCondition($fieldset, $column, $value, $condition = '=')
    {

        //_build_fieldset_op_conditions
        $column_name = empty($this->relations[$fieldset]['fields'][$column]) ? false : $this->relations[$fieldset]['fields'][$column];

        if ($column_name !== false)
            $this->st->condition($fieldset . '.' . $column_name, $value, $condition);

        return $this;
    }


    /**
     * Set the result range
     *
     * @param null $start
     * @param null $length
     * @return $this
     */
    public function range($start = null, $length = null)
    {
        $this->st->range($start, $length);

        return $this;
    }


    /**
     * Execute the entity query
     *
     * @return mixed
     */
    public function execute()
    {
        return $this->st->execute();
    }


    /**
     * Fetch multiple records
     *
     * @return mixed
     */
    public function fetchAll()
    {
        $result = $this->st->execute()->fetchAll();

        // Group fields
        foreach ($result as &$row)
            $this->_group_result_fields($row);

        // Get multi cardinal fields
        foreach ($result as &$row)
            $this->_query_multifield_record($row);



        return $result;
    }


    /**
     * Fetch one record
     *
     * @param int $type
     * @return mixed
     */
    public function fetch($type = \PDO::FETCH_OBJ)
    {
        $result = $this->st->execute()->fetch();

        $this->_query_multifield_record($result);

        return $result;
    }


    /**
     * Build the conditions in order to operate (DELETE and UPDATE) with the fieldsets
     *
     * @param $fieldset
     * @param $entity_id
     * @param int $delta
     */
    protected function _build_fieldset_op_conditions($fieldset, $entity_id, $delta = 0)
    {

        $index_name = $this->entity->info()['keys']['id'];

        // Add conditions
        foreach ($this->relations[$fieldset]['keys'] as $key)
        {
            list($entity_key, $fieldset_key) = explode(':', $key);

            if ($entity_key == $index_name)
                $entity_key = $entity_id;

            if ($entity_key[0] === '#')
                $entity_key = substr($entity_key, 1);

            if ($fieldset_key[0] == '#')
                $fieldset_key = substr($fieldset_key, 1);

            $this->st->condition($fieldset_key, $entity_key);

        }

        if ($this->relations[$fieldset]['cardinality'] != 1 && $delta !== false)
            $this->st->condition('delta', $delta);
    }


    /**
     * Group entity results by field
     *
     * @param $row
     */
    protected function _group_result_fields(&$row)
    {

        foreach ($this->relations as $kfieldset => $fieldset)
        {

            // Ignore fields with cardinality
            if ($fieldset['cardinality'] != 1)
                continue;

            foreach ($fieldset['fields'] as $kfield => $field)
            {
                if (property_exists($row, $field))
                {
                    if (!isset($row->{$kfieldset}))
                        $row->{$kfieldset} = new \stdClass();

                    $row->{$kfieldset}->$kfield = $row->{$field};
                    unset($row->{$field});
                }

            }

        }
    }


    /**
     * Retrieve and add an multifield result
     *
     * @param $row
     */
    protected function _query_multifield_record(&$row)
    {

        foreach ($this->relations as $kfieldset => $fieldset)
        {
            // Those elements with cardinality are going to be ignored because they were already retrieved
            if ($fieldset['cardinality'] == 1)
                continue;

            $st = $this->db->select($this->entity->info()['table'], 'main');

            $id_name = $this->entity->info()['keys']['id'];

            // Add keys
            $st->innerJoin($fieldset['table'], $kfieldset, $this->_buildJoinLink('main', $kfieldset, $fieldset));
            $st->condition('main.' . $id_name, $row->{$id_name}, '=');

            // Add fields
            foreach ($fieldset['fields'] as $kfield => $field)
                $st->addField($kfieldset, $field, $kfield);

            $row->{$kfieldset} = $st->execute()->fetchAll();

        }

    }



    /**
     * Build the definition for the dynamic fields
     */
    protected function _build_dynamic_relations()
    {

        // Retrieve fields configuration
        $st = $this->db->select('field_config_instance', 'fi');

        $st->join('field_config', 'fc', 'fc.id = fi.field_id');
        $st->condition('fi.bundle', $this->bundle, '=')
            ->condition('fi.entity_type', $this->entity->info()['entity'], '=')
            ->condition('fi.deleted', 0, '=')
            ->condition('fc.active', 1, '=')
            ->condition('fc.deleted', 0, '=');

        $st->addField('fi', 'field_name');
        $st->addField('fc', 'data');
        $st->addField('fc', 'cardinality');
        $st->addField('fc', 'type');

        $fields_ref = $st->execute()->fetchAll();

        $dynamic_relations = array('children' => array());

        foreach ($fields_ref as $field)
        {

            $schema = unserialize($field->data);

            // Ignore fields with storage different than sql
            if ($schema['storage']['type'] != 'field_sql_storage')
                continue;


            // Ignore fields without current fields
            if (!empty($schema['storage']['details']['sql']['FIELD_LOAD_CURRENT'])) {

                // Get table name
                $table = array_keys($schema['storage']['details']['sql']['FIELD_LOAD_CURRENT'])[0];

                $dynamic_relations['children'][$field->field_name]['table'] = $table;
                $dynamic_relations['children'][$field->field_name]['type']  = $field->type;

                $dynamic_relations['children'][$field->field_name]['cardinality'] = $field->cardinality;

                // Build key relations
                $dynamic_relations['children'][$field->field_name]['keys'][] = "{$this->entity->info()['keys']['id']}:entity_id";
                $dynamic_relations['children'][$field->field_name]['keys'][] = "#{$this->entity->info()['entity']}:entity_type";
                $dynamic_relations['children'][$field->field_name]['keys'][] = "#{$this->bundle}:bundle";


                // Copy value fields
                $dynamic_relations['children'][$field->field_name]['fields'] = $schema['storage']['details']['sql']['FIELD_LOAD_CURRENT'][$table];
            }


            if (!empty($schema['storage']['details']['sql']['FIELD_LOAD_REVISION'])) {

                // Get table name
                $table = array_keys($schema['storage']['details']['sql']['FIELD_LOAD_REVISION'])[0];

                $dynamic_relations['revision'][$field->field_name]['table'] = $table;
                $dynamic_relations['revision'][$field->field_name]['type']  = $field->type;

                $dynamic_relations['revision'][$field->field_name]['cardinality'] = $field->cardinality;

                // Build key relations
                $dynamic_relations['revision'][$field->field_name]['keys'][] = "{$this->entity->info()['keys']['id']}:entity_id";
                $dynamic_relations['revision'][$field->field_name]['keys'][] = "#{$this->entity->info()['entity']}:entity_type";
                $dynamic_relations['revision'][$field->field_name]['keys'][] = "#{$this->bundle}:bundle";


                // Copy value fields
                $dynamic_relations['revision'][$field->field_name]['fields'] = $schema['storage']['details']['sql']['FIELD_LOAD_REVISION'][$table];
            }

        }


        if (!empty($dynamic_relations))
        {
            $new_dynamic_relations = $this->entity->dynamic_relations();
            $new_dynamic_relations = $new_dynamic_relations + $dynamic_relations;
            $this->entity->dynamic_relations($new_dynamic_relations);
        }

    }


    /**
     * Build left join conditions
     *
     * @param $main_alias
     * @param string $link_alias
     * @param $field
     * @return string
     */
    protected function _buildJoinLink($main_alias, $link_alias = '', $field)
    {
        $condition = '';

        foreach ($field['keys'] as $key)
        {
            list($main_link, $join_link) = explode(':', $key);

            if ($main_link[0] === '#')
                $main_link = "'" . substr($main_link, 1) . "'";
            else
                $main_link = "$main_alias.$main_link";

            if ($join_link[0] == '#')
                $join_link = "'" . substr($join_link, 1) . "'";
            else
                $join_link = "$link_alias.$join_link";


            $condition .= empty($condition) ? '' : ' AND ';
            $condition .= "$join_link = $main_link";
        }

        return $condition;
    }

}
