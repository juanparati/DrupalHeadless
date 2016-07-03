DrupalHeadless
==============


Description
-----------

The DrupalHeadless project provides the following components as a compatible PSR-4 package:

 - The original Drupal 7 Database Abstraction Layer
 - A replacement for the Entity Field Query
 
This package was created in order to provide an elegant and suitable way of interact with Drupal 7 databases without the requeriment of install a full Drupal 7 stack.
DrupalHeadless can used as with standalone scripts or can be deployed via composer in different solutions like for example Drupal 8.



How to use the database abstraction layer?
------------------------------------------

If you are familiar with Drupal 7 Database API, then it is going to be easy for you to use the DrupalHeadless database abstraction layer because it is based in the original abstraction layer.

### Database connection and query


     use \DrupalHeadless\Database\Database;
     
     // Set connection info
     Database::addConnectionInfo('default', 'default', [
         'driver'    => 'mysql',
         'database'  => 'drupal',
         'username'  => 'root',
         'password'  => 'root',
         'host'      => '127.0.0.1',
         'prefix'    => 'drupal_'
     ]);
     
     $query = Database::getConnection('default', 'default');
     
     $st = $query->select('node', 'n');
     
     $result = $st->fields('n')
         ->range(0, 10)
         ->execute()
         ->fetchAll();
 
 
 As an alternative it is also possible to use the statement helper:
 
    
     use \DrupalHeadless\Database\Database;
     use \DrupalHeadless\Database\DatabaseHelper as DH; 
    
     // Set connection info
     Database::addConnectionInfo('default', 'default', [
         'driver'    => 'mysql',
         'database'  => 'drupal',
         'username'  => 'root',
         'password'  => 'root',
         'host'      => '127.0.0.1',
         'prefix'    => 'drupal_'
     ]);
     
     
     $result = DH::select('node', 'n')
         ->fields('n')
         ->range(0, 10)
         ->execute()
         ->fetchAllAssoc('nid');
     
 
 
How to use the Entity Field Query?
----------------------------------


### Select (and retrieve all the fields)
        
        
        use \DrupalHeadless\Database\Database;
        use \DrupalHeadless\Entity\EntityController as EC;
        use \DrupalHeadless\Entity\Model;
        
        
        // Set connection info
        Database::addConnectionInfo('default', 'default', [
            'driver'    => 'mysql',
            'database'  => 'drupal',
            'username'  => 'root',
            'password'  => 'root',
            'host'      => '127.0.0.1',
            'prefix'    => 'drupal_'
        ]);
        
        
        $connection = Database::getConnection('default', 'default');
        
        
        // Search the first 5 nodes with bundle "article" that contain the word "Test" into the body field
        $result_node = EC::entity($connection, new Model\Node(), 'article')
            ->load()
            ->fieldCondition('body', 'value', '%Test%', 'LIKE')
            ->range(0, 5)
            ->fetchAll();
        
        
        // Search the user with UID = 1 
        $result_user = EC::entity($connection, new Model\Users(), 'user')
            ->load()
            ->propertyCondition('uid', 1)
            ->fetch();
         

### Insert 


    use \DrupalHeadless\Database\Database;
    use \DrupalHeadless\Entity\EntityController as EC;
    use \DrupalHeadless\Entity\Model;
    
    
    // Set connection info
    Database::addConnectionInfo('default', 'default', [
        'driver'    => 'mysql',
        'database'  => 'drupal',
        'username'  => 'root',
        'password'  => 'root',
        'host'      => '127.0.0.1',
        'prefix'    => 'drupal_'
    ]);
    
    
    $connection = Database::getConnection('default', 'default');
    
    
    $entity = EC::entity($connection, new Model\Node(), 'article');
    
    
    // Load entity node and bundle "article" which "title" is equal to "This is a test"
    $result_node = $entity->load()
        ->propertyCondition('title', 'This is test')
        ->fetch();
    
    
    // Insert into the custom/dynamic field the value "myvideo"
    $entity = $entity->insertFieldset('field_videoid', $result_node->nid, ['value' => 'myvideo'], 0);
    
    



