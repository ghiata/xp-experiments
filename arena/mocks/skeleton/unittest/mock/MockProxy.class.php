<?php

/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('lang.reflect.InvocationHandler',
       'lang.reflect.Proxy',
       'unittest.mock.RecordState');

  /**
   * A mock proxy.
   *
   * @purpose 
   */
  class MockProxy extends Proxy implements InvocationHandler {
    private
      $mockState= null;

    public function __construct() {
      parent::__construct($this);
      $this->mockState= new RecordState();
    }
    
    /**
     * Processes a method invocation on a proxy instance and returns
     * the result.
     *
     * @param   lang.reflect.Proxy proxy
     * @param   string method the method name
     * @param   var* args an array of arguments
     * @return  var
     */
    public function invoke($proxy, $method, $args) {
      return $this->mockState->handleInvocation($method, $args);
    }

    /**
     * Indicates whether this proxy is in recoding state
     *
     * @return boolean
     */
    public function isRecording() {
        return $this->mockState instanceof RecordState;
    }

    /**
     * Indicates whether this proxy is in replaying state
     *
     * @return boolean
     */
    public function isReplaying() {
        return !$this->isRecording();
    }

    /**
     * Switches state to replay mode
     */
    public function replay() {
        $this->mockState= new ReplayState();
    }
  }