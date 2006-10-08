<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  uses('net.xp_framework.tools.vm.unittest.emit.php5.AbstractEmitterTest');

  /**
   * Tests PHP5 emitter
   *
   * @purpose  Unit Test
   */
  class StringEmitterTest extends AbstractEmitterTest {

    /**
     * Tests a single-quoted string
     *
     * @access  public
     */
    #[@test]
    function singleQuotedString() {
      $this->assertSourcecodeEquals(
        'echo \'Hello\';',
        $this->emit('echo \'Hello\';')
      );
    }

    /**
     * Tests a single-quoted string with escaped single-quote inside
     *
     * @access  public
     */
    #[@test]
    function singleQuotedStringWithEscapedSingleQuote() {
      $this->assertSourcecodeEquals(
        'echo \'He\\\'s lazy\';',
        $this->emit('echo \'He\\\'s lazy\';')
      );
    }

    /**
     * Tests a single-quoted string
     *
     * @access  public
     */
    #[@test]
    function doubleQuotedString() {
      $this->assertSourcecodeEquals(
        'echo \'Hello\';',
        $this->emit('echo "Hello";')
      );
    }

    /**
     * Tests a single-quoted string with escaped double-quotes inside
     *
     * @access  public
     */
    #[@test]
    function doubleQuotedStringWithEscapedDoubleQuotes() {
      $this->assertSourcecodeEquals(
        'echo \'"Hello", he said\';',
        $this->emit('echo "\"Hello\", he said";')
      );
    }

    /**
     * Tests a single-quoted string with single-quote inside
     *
     * @access  public
     */
    #[@test]
    function doubleQuotedStringWithSingleQuote() {
      $this->assertSourcecodeEquals(
        'echo \'He\\\'s lazy\';',
        $this->emit('echo "He\'s lazy";')
      );
    }

    /**
     * Tests a hex-escape (\x[0-9A-Fa-f]{1,2})
     *
     * @access  public
     */
    #[@test]
    function hexEscape() {
      $this->assertSourcecodeEquals(
        'echo \'ga\';',
        $this->emit('echo "\x67a";')
      );
    }

    /**
     * Tests an octal escape (\[0-7]{1,3})
     *
     * @access  public
     */
    #[@test]
    function octalEscape() {
      $this->assertSourcecodeEquals(
        'echo \'ga\';',
        $this->emit('echo "\147a";')
      );
    }
  }
?>
