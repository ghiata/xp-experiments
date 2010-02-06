<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('tests.convert.AbstractConversionTest');

  /**
   * Tests newinstance() is rewritten
   *
   * @see      xp://tests.convert.AbstractConversionTest
   */
  class NewInstanceTest extends AbstractConversionTest {

    /**
     * Test
     *
     */
    #[@test]
    public function newRunnable() {
      $this->assertConversion(
        '$r= new lang.Runnable() { public void run() { /* ... */ }};',
        '$r= newinstance("lang.Runnable", array(), "{ public function run() { /* ... */ }}");',
        SourceConverter::ST_FUNC_BODY
      );
    }

  }
?>
