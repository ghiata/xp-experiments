<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'xp.ide.unittest.TestCase',
    'xp.ide.wrapper.Gedit',
    'xp.ide.XpIde',
    'io.streams.TextReader',
    'io.streams.TextWriter',
    'io.streams.MemoryInputStream',
    'io.streams.MemoryOutputStream'
  );

  /**
   * TestCase
   *
   * @see      reference
   * @purpose  purpose
   */
  class GeditWrapperTest extends xp�ide�unittest�TestCase {
    private
      $ide= NULL,
      $in=  NULL,
      $out= NULL,
      $err= NULL;

    /**
     * Sets up test case
     *
     */
    public function setUp() {
      $this->ide= new xp�ide�XpIde(
        $this->in= new TextReader(new MemoryInputStream('')),
        $this->out= new TextWriter(new MemoryOutputStream()),
        $this->err= new TextWriter(new MemoryOutputStream())
      );
      $this->wrapper= new xp�ide�wrapper�Gedit($this->ide);
    }

    /**
     * Test ide class
     *
     */
    #[@test]
    public function createAccessorsEmpty() {
      $this->wrapper->createAccessors();
      $this->assertEquals('', $this->wrapper->getOut()->getStream()->getBytes());
    }

    /**
     * Test ide class
     *
     */
    #[@test, @expect('lang.IllegalArgumentException')]
    public function createAccessorsNoSet() {
      $this->wrapper->setIn(new TextReader(new MemoryInputStream('in:string')));
      $this->wrapper->createAccessors();
      $this->assertEquals(
        $this->createSetter('in', 'string'),
        $this->wrapper->getOut()->getStream()->getBytes()
      );
    }

    /**
     * Test ide class
     *
     */
    #[@test]
    public function createSettterOne() {
      $this->wrapper->setIn(new TextReader(new MemoryInputStream('in:string::0:set')));
      $this->wrapper->createAccessors();
      $this->assertEquals(
        $this->createSetter('in', 'string'),
        $this->wrapper->getOut()->getStream()->getBytes()
      );
    }

    /**
     * Test ide class
     *
     */
    #[@test]
    public function createSetterTwo() {
      $this->wrapper->setIn(new TextReader(new MemoryInputStream('in:string::0:set'."\n".'out:string::0:set')));
      $this->wrapper->createAccessors();
      $this->assertEquals(
        $this->createSetter('in', 'string').
        $this->createSetter('out', 'string'),
        $this->wrapper->getOut()->getStream()->getBytes()
      );
    }

    /**
     * Test ide class
     *
     */
    #[@test]
    public function createGetterOne() {
      $this->wrapper->setIn(new TextReader(new MemoryInputStream('in:string::0:get')));
      $this->wrapper->createAccessors();
      $this->assertEquals(
        $this->createGetter('in', 'string'),
        $this->wrapper->getOut()->getStream()->getBytes()
      );
    }

    /**
     * Test ide class
     *
     */
    #[@test]
    public function createGetterTwo() {
      $this->wrapper->setIn(new TextReader(new MemoryInputStream('in:string::0:get'."\n".'out:string::0:get')));
      $this->wrapper->createAccessors();
      $this->assertEquals(
        $this->createGetter('in', 'string').
        $this->createGetter('out', 'string'),
        $this->wrapper->getOut()->getStream()->getBytes()
      );
    }

    /**
     * Test ide class
     *
     */
    #[@test]
    public function createSetterGetterOne() {
      $this->wrapper->setIn(new TextReader(new MemoryInputStream('in:string::0:set+get')));
      $this->wrapper->createAccessors();
      $this->assertEquals(
        $this->createSetter('in', 'string').
        $this->createGetter('in', 'string'),
        $this->wrapper->getOut()->getStream()->getBytes()
      );
    }

    /**
     * Test ide class
     *
     */
    #[@test]
    public function createSetterGetterTwo() {
      $this->wrapper->setIn(new TextReader(new MemoryInputStream('in:string::0:set+get'.PHP_EOL.'out:string::0:set+get')));
      $this->wrapper->createAccessors();
      $this->assertEquals(
        $this->createSetter('in', 'string').
        $this->createGetter('in', 'string').
        $this->createSetter('out', 'string').
        $this->createGetter('out', 'string'),
        $this->wrapper->getOut()->getStream()->getBytes()
      );
    }

    /**
     * Test ide class
     *
     */
    #[@test]
    public function createSetterGetterInt() {
      $this->wrapper->setIn(new TextReader(new MemoryInputStream('count:integer::0:set+get')));
      $this->wrapper->createAccessors();
      $this->assertEquals(
        $this->createSetter('count', 'integer').
        $this->createGetter('count', 'integer'),
        $this->wrapper->getOut()->getStream()->getBytes()
      );
    }

    /**
     * Test ide class
     *
     */
    #[@test]
    public function createSetterGetterBool() {
      $this->wrapper->setIn(new TextReader(new MemoryInputStream('final:boolean::0:set+get')));
      $this->wrapper->createAccessors();
      $this->assertEquals(
        $this->createSetter('final', 'boolean').
        $this->createGetter('final', 'boolean'),
        $this->wrapper->getOut()->getStream()->getBytes()
      );
    }

    /**
     * Test ide class
     *
     */
    #[@test]
    public function createSetterGetterObject() {
      $this->wrapper->setIn(new TextReader(new MemoryInputStream('root:object:lang.Object:0:set+get')));
      $this->wrapper->createAccessors();
      $this->assertEquals(
        $this->createSetter('root', 'lang.Object', 'Object').
        $this->createGetter('root', 'lang.Object'),
        $this->wrapper->getOut()->getStream()->getBytes()
      );
    }

    /**
     * Test ide class
     *
     */
    #[@test]
    public function createSetterGetterNamespaceObject() {
      $this->wrapper->setIn(new TextReader(new MemoryInputStream('ide:object:xp.ide.XpIde:0:set+get')));
      $this->wrapper->createAccessors();
      $this->assertEquals(
        $this->createSetter('ide', 'xp.ide.XpIde', 'xp�ide�XpIde').
        $this->createGetter('ide', 'xp.ide.XpIde'),
        $this->wrapper->getOut()->getStream()->getBytes()
      );
    }

    /**
     * Test ide class
     *
     */
    #[@test]
    public function createSetterGetterArray() {
      $this->wrapper->setIn(new TextReader(new MemoryInputStream('names:array:string:1:set+get')));
      $this->wrapper->createAccessors();
      $this->assertEquals(
        $this->createSetter('names', 'string[]', 'array').
        $this->createGetter('names', 'string[]'),
        $this->wrapper->getOut()->getStream()->getBytes()
      );
    }

    /**
     * Test ide class
     *
     */
    #[@test]
    public function createSetterGetterArrayDim2() {
      $this->wrapper->setIn(new TextReader(new MemoryInputStream('names:array:string:2:set+get')));
      $this->wrapper->createAccessors();
      $this->assertEquals(
        $this->createSetter('names', 'string[][]', 'array').
        $this->createGetter('names', 'string[][]'),
        $this->wrapper->getOut()->getStream()->getBytes()
      );
    }

    /**
     * Test ide class
     *
     */
    #[@test]
    public function createSetterGetterArrayObject() {
      $this->wrapper->setIn(new TextReader(new MemoryInputStream('names:array:lang.Object:1:set+get')));
      $this->wrapper->createAccessors();
      $this->assertEquals(
        $this->createSetter('names', 'lang.Object[]', 'array').
        $this->createGetter('names', 'lang.Object[]'),
        $this->wrapper->getOut()->getStream()->getBytes()
      );
    }

    /**
     * create a setter
     *
     * @param string name
     * @param string type
     * @param string hint
     */
    private function createSetter($name, $type, $hint= '') {
      return sprintf(
        '    /**'.PHP_EOL.
        '     * set member $%1$s'.PHP_EOL.
        '     * '.PHP_EOL.
        '     * @param %3$s %1$s'.PHP_EOL.
        '     */'.PHP_EOL.
        '    public function set%2$s('.($hint ? '%4$s ' : '').'$%1$s) {'.PHP_EOL.
        '      $this->%1$s= $%1$s;'.PHP_EOL.
        '    }'.PHP_EOL.PHP_EOL,
        $name, ucfirst($name), $type, $hint
      );
    }

    /**
     * create a getter
     *
     * @param string name
     * @param string type
     */
    private function createGetter($name, $type) {
      return sprintf(
        '    /**'.PHP_EOL.
        '     * get member $%1$s'.PHP_EOL.
        '     * '.PHP_EOL.
        '     * @return %3$s'.PHP_EOL.
        '     */'.PHP_EOL.
        '    public function %4$s%2$s() {'.PHP_EOL.
        '      return $this->%1$s;'.PHP_EOL.
        '    }'.PHP_EOL.PHP_EOL,
        $name, ucfirst($name), $type, ('boolean' == $type ? 'is' : 'get')
      );
    }

  }
?>
