<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('net.xp_framework.tools.vm.VNode');

  /**
   * InstanceOf
   *
   * @see   xp://net.xp_framework.tools.vm.nodes.VNode
   */ 
  class InstanceOfNode extends VNode {
    var
      $object,
      $type;
      
    /**
     * Constructor
     *
     * @access  public
     * @param   mixed object
     * @param   mixed type
     */
    function __construct($object, $type) {
      $this->object= $object;
      $this->type= $type;
    }  
  }
?>
