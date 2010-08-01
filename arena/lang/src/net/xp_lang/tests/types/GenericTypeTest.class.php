<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses(
    'unittest.TestCase',
    'xp.compiler.types.TypeName',
    'xp.compiler.types.GenericType',
    'xp.compiler.types.TypeReflection'
  );

  /**
   * TestCase
   *
   * @see   xp://xp.compiler.types.GenericType
   * @see   xp://util.collections.HashTable
   * @see   xp://util.collections.Vector
   */
  class GenericTypeTest extends TestCase {

    /**
     * Returns new util.collections.HashTable fixture
     *
     * @return  xp.compiler.types.GenericType
     */
    protected function newGenericHashTableType() {
      return new GenericType(
        new TypeReflection(XPClass::forName('util.collections.HashTable')), 
        array(new TypeName('string'), new TypeName('lang.Object'))
      );
    }    

    /**
     * Returns new util.collections.Vector fixture
     *
     * @return  xp.compiler.types.GenericType
     */
    protected function newGenericVectorType() {
      return new GenericType(
        new TypeReflection(XPClass::forName('util.collections.Vector')), 
        array(new TypeName('string'))
      );
    }    
    
    /**
     * Test HashTable indexer
     *
     */
    #[@test]
    public function hashTableIndexerType() {
      $this->assertEquals(new TypeName('lang.Object'), $this->newGenericHashTableType()->getIndexer()->type);
    }

    /**
     * Test HashTable indexer
     *
     */
    #[@test]
    public function hashTableIndexerParameters() {
      $this->assertEquals(new TypeName('string'), $this->newGenericHashTableType()->getIndexer()->parameter);
    }

    /**
     * Test HashTable method
     *
     */
    #[@test]
    public function hashTableGetMethodType() {
      $this->assertEquals(new TypeName('lang.Object'), $this->newGenericHashTableType()->getMethod('get')->returns);
    }

    /**
     * Test HashTable method
     *
     */
    #[@test]
    public function hashTableGetMethodParameters() {
      $this->assertEquals(array(new TypeName('string')), $this->newGenericHashTableType()->getMethod('get')->parameters);
    }

    /**
     * Test Vector indexer
     *
     */
    #[@test]
    public function vectorIndexerType() {
      $this->assertEquals(new TypeName('string'), $this->newGenericVectorType()->getIndexer()->type);
    }

    /**
     * Test Vector indexer
     *
     */
    #[@test]
    public function vectorIndexerParameters() {
      $this->assertEquals(new TypeName('int'), $this->newGenericVectorType()->getIndexer()->parameter);
    }

    /**
     * Test Vector method
     *
     */
    #[@test]
    public function vectorGetMethodType() {
      $this->assertEquals(new TypeName('string'), $this->newGenericVectorType()->getMethod('get')->returns);
    }

    /**
     * Test Vector method
     *
     */
    #[@test]
    public function vectorGetMethodParameters() {
      $this->assertEquals(array(new TypeName('int')), $this->newGenericVectorType()->getMethod('get')->parameters);
    }
  }
?>
