<?php

use DrupalHeadless\Database\Database;
use DrupalHeadless\Entity\EntityController as EC;


class EntityTest extends PHPUnit_Framework_TestCase
{

    /**
     * Database connection
     *
     * @var DrupalHeadless\Database\DatabaseConnection
     */
    protected $db;


    /**
     * Test data
     *
     * @var array
     */
    protected $data = array(
        'testInsert' => array(
            'type'      => 'article',
            'language'  => 'und',
            'title'     => 'Last Inserted',
            'uid'       => 1,
            'status'    => 1,
            'created'   => 1467837270,
            'changed'   => 1467837280,
        )
    );



    /**
     * Setup
     */
    protected function setUp()
    {

        Database::addConnectionInfo('default', 'default', array(
            'driver'    => 'mysql',
            'database'  => 'travis_ci_drupal',
            'username'  => 'root',
            'password'  => '',
            'host'      => '127.0.0.1',
            'prefix'    => 'drupal_'
        ));

        $this->last_inserted_id = null;

        $this->db = Database::getConnection('default', 'default');

        parent::setUp();
    }


    /**
     * TearDown (On tests finish)
     */
    protected function tearDown()
    {
        Database::closeConnection('default', 'default');

        parent::tearDown(); // TODO: Change the autogenerated stub
    }


    /**
     * Test Insert
     *
     * @return \DrupalHeadless\Database\DatabaseStatementInterface|int
     * @throws Exception
     */
    /*
    public function testInsertNode()
    {

        $ne = EC::entity($this->db, new DrupalHeadless\Entity\Model\Node(), 'article')->load();

        $ne->

        $last_id = $this->db->insert('node')->fields($this->data['testInsert'])->execute();

        $this->assertNotFalse($last_id);
        $this->assertNotNull($last_id);

        return $last_id;

    }
    */


    /**
     * Test single select
     *
     * @return int
     */
    public function testSingleSelect()
    {

        $ne = EC::entity($this->db, new DrupalHeadless\Entity\Model\Node(), 'article')->load();
        $fields = $ne->select()->fetchAll();

        $this->assertArraySubset($this->data['testInsert'], $fields, false, 'Inserted data do not match');
    }


    /**
     * Test single condition delete
     */
    /*
    public function testSingleDelete()
    {
        $affected = $this->db->delete('node')->condition('title', $this->data['testInsert']['title'])->execute();

        $this->assertEquals(1, $affected);
    }



    public function testCustomQuery()
    {
        $result = $this->db->query('SELECT COUNT(*) AS total FROM {node} WHERE uid = :uid', array('uid' => 1));

        $result = $result->fetchField();

        $this->assertGreaterThan(1, $result);
    }
    */



}