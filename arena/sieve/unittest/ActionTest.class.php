<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('unittest.SieveParserTestCase');

  /**
   * TestCase
   *
   * @see      xp://peer.sieve.ActionFactory
   * @purpose  Unittest
   */
  class ActionTest extends SieveParserTestCase {

    /**
     * Test "keep"
     *
     * @see      xp://peer.sieve.KeepAction
     */
    #[@test]
    public function keep() {
      $action= $this->parseCommandSetFrom('
        if size :under 1M { keep; } else { discard; }
      ')->commandAt(0)->commands[0];
      $this->assertClass($action, 'peer.sieve.KeepAction');
    }

    /**
     * Test "keep" takes no arguments
     *
     * @see      xp://peer.sieve.KeepAction
     */
    #[@test, @expect('text.parser.generic.ParseException')]
    public function keepWithArgument() {
      $this->parse('keep "whatever";');
    }

    /**
     * Test "discard"
     *
     * @see      xp://peer.sieve.DiscardAction
     */
    #[@test]
    public function discard() {
      $action= $this->parseCommandSetFrom('
        if header :contains ["from"] ["idiot@example.com"] { discard; }
      ')->commandAt(0)->commands[0];
      $this->assertClass($action, 'peer.sieve.DiscardAction');
    }

    /**
     * Test "discard" takes no arguments
     *
     * @see      xp://peer.sieve.DiscardAction
     */
    #[@test, @expect('text.parser.generic.ParseException')]
    public function discardWithArgument() {
      $this->parse('discard "bar";');
    }
 
    /**
     * Test "stop"
     *
     * @see      xp://peer.sieve.StopAction
     */
    #[@test]
    public function stop() {
      $action= $this->parseCommandSetFrom('
        if size :over 1M { stop; }
      ')->commandAt(0)->commands[0];
      $this->assertClass($action, 'peer.sieve.StopAction');
    }

    /**
     * Test "stop" takes no arguments
     *
     * @see      xp://peer.sieve.StopAction
     */
    #[@test, @expect('text.parser.generic.ParseException')]
    public function stopWithArgument() {
      $this->parse('stop "immediately";');
    }
 
    /**
     * Test "reject"
     *
     * @see      xp://peer.sieve.RejectAction
     */
    #[@test]
    public function reject() {
      $action= $this->parseCommandSetFrom('
        if size :over 1M { reject "Your message is too big"; }
      ')->commandAt(0)->commands[0];
      $this->assertClass($action, 'peer.sieve.RejectAction');
      $this->assertEquals('Your message is too big', $action->reason);
    }

    /**
     * Test "redirect"
     *
     * @see      xp://peer.sieve.RedirectAction
     */
    #[@test]
    public function redirect() {
      $action= $this->parseCommandSetFrom('
        if size :over 1M { redirect "bart@example.com"; }
      ')->commandAt(0)->commands[0];
      $this->assertClass($action, 'peer.sieve.RedirectAction');
      $this->assertEquals('bart@example.com', $action->target);
    }

    /**
     * Test "fileinto"
     *
     * @see      xp://peer.sieve.FileIntoAction
     */
    #[@test]
    public function fileinto() {
      $action= $this->parseCommandSetFrom('
        if size :over 1M { fileinto "INBOX.huge"; }
      ')->commandAt(0)->commands[0];
      $this->assertClass($action, 'peer.sieve.FileIntoAction');
      $this->assertEquals('INBOX.huge', $action->mailbox);
    }
  }
?>
