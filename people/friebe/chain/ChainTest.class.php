<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'util.profiling.unittest.TestCase',
    'util.log.Logger'
  );
  include('chain-function.php');

  /**
   * Tests chain
   *
   * @purpose  TestCase
   */
  class ChainTest extends TestCase {

    /**
     * Helper method that returns two logcategories in an array
     *
     * @access  protected
     * @return  util.log.LogCategory[2]
     */
    function getCategories() {
      return array(new LogCategory(), new LogCategory());
    }
      
    /**
     * Tests Logger::getInstance()->getCategory();
     *
     * @access  public
     */
    #[@test]
    function defaultLoggerCategory() {
      $cat= &chain(Logger::getInstance(), 'getCategory()');
      $this->assertClass($cat, 'util.log.LogCategory');
    }

    /**
     * Tests Logger::getInstance()->getCategory()->getClass()->getName()
     *
     * @access  public
     */
    #[@test]
    function defaultLoggerCategoryClassName() {
      $name= chain(Logger::getInstance(), 'getCategory()', 'getClass()', 'getName()');
      $this->assertEquals($name, 'util.log.LogCategory');
    }

    /**
     * Tests Logger::getInstance()->getCategory($this->getClassName());
     *
     * @access  public
     */
    #[@test]
    function classLoggerCategory() {
      $cat= &chain(Logger::getInstance(), 'getCategory(', $this->getClassName(), ')');
      $this->assertClass($cat, 'util.log.LogCategory');
    }

    /**
     * Tests $this->getCategories()[1]
     *
     * @access  public
     */
    #[@test]
    function constantArrayOffset() {
      $cat= &chain($this->getCategories(), '[1]');
      $this->assertClass($cat, 'util.log.LogCategory');
    }

    /**
     * Tests $this->getCategories()[1]
     *
     * @access  public
     */
    #[@test]
    function dynamicArrayOffset() {
      $i= 0;
      $cat= &chain($this->getCategories(), '[', $i, ']');
      $this->assertClass($cat, 'util.log.LogCategory');
    }

    /**
     * Tests XPClass::forName($class)->newInstance() will throw an 
     * exception if $class is not existant
     *
     * @access  public
     */
    #[@test, @expect('lang.ClassNotFoundException')]
    function exceptionsBreakChain() {
      chain(XPClass::forName('@@NOTEXISTANTCLASS@@'), 'newInstance()');
    }

    /**
     * Tests NULL doesn't cause fatal errors (e.g. $instance->toString()
     * where $instance is NULL)
     *
     * @access  public
     */
    #[@test, @expect('lang.NullPointerException')]
    function nullThrowsNPE() {
      chain($instance= NULL, 'toString()');
    }
  }
?>
