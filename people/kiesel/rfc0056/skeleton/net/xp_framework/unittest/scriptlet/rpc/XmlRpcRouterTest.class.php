<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'util.profiling.unittest.TestCase',
    'net.xp_framework.unittest.scriptlet.rpc.XmlRpcRouterMock'
  );

  /**
   * (Insert class' description here)
   *
   * @ext      extension
   * @see      reference
   * @purpose  purpose
   */
  class XmlRpcRouterTest extends TestCase {
    
    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  
     */
    function setUp() {
      xp::gc();
      $this->router= &new XmlRpcRouterMock(new ClassLoader('net.xp_framework.unittest.scriptlet.rpc.impl'));
      $this->router->setMockMethod(HTTP_POST);
      $this->router->setMockData('<?xml version="1.0" encoding="iso-8859-1"?>
        <methodCall>
          <methodName>DummyRpcImplementation.getImplementationName</methodName>
          <params/>
        </methodCall>
      ');
    }
    
    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  
     */
    #[@test]
    function basicPostRequest() {
      $this->router->init();
      $response= &$this->router->process();
      
      $this->assertEquals(200, $response->statusCode);
    }

    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  
     */
    #[@test, @expect('scriptlet.HttpScriptletException')]
    function basicGetRequest() {
      $this->router->setMockMethod(HTTP_GET);
      $this->router->init();
      $response= &$this->router->process();
    }
    
    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  
     */
    #[@test]
    function callNonexistingClass() {
      $this->router->setMockData('<?xml version="1.0" encoding="iso-8859-1"?>
        <methodCall>
          <methodName>ClassDoesNotExist.getImplementationName</methodName>
          <params/>
        </methodCall>
      ');
      
      $this->router->init();
      $response= &$this->router->process();
      
      $this->assertEquals(500, $response->statusCode);
    }
    
    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  
     */
    #[@test]
    function callNonexistingMethod() {
      $this->router->setMockData('<?xml version="1.0" encoding="iso-8859-1"?>
        <methodCall>
          <methodName>DummyRpcImplementation.methodDoesNotExist</methodName>
          <params/>
        </methodCall>
      ');
      
      $this->router->init();
      $response= &$this->router->process();
      
      $this->assertEquals(500, $response->statusCode);
    }

    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  
     */
    #[@test]
    function callNonWebmethodMethod() {
      $this->router->setMockData('<?xml version="1.0" encoding="iso-8859-1"?>
        <methodCall>
          <methodName>DummyRpcImplementation.methodExistsButIsNotAWebmethod</methodName>
          <params/>
        </methodCall>
      ');
      
      $this->router->init();
      $response= &$this->router->process();
      
      $this->assertEquals(500, $response->statusCode);
    }

    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  
     */
    #[@test]
    function callFailingMethod() {
      $this->router->setMockData('<?xml version="1.0" encoding="iso-8859-1"?>
        <methodCall>
          <methodName>DummyRpcImplementation.giveMeFault</methodName>
          <params/>
        </methodCall>
      ');
      
      $this->router->init();
      $response= &$this->router->process();
      $this->assertEquals(500, $response->statusCode);

      // Check for correct fault code
      $message= &XmlRpcMessage::fromString($response->getContent());
      $fault= &$message->getFault();
      $this->assertEquals(403, $fault->getFaultcode());
    }
  }
?>
