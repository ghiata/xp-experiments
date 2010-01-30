<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('xp.compiler.ast.Node');
  
  /**
   * Verifies a given node
   *
   */
  interface Check {

    /**
     * Return node this check works on
     *
     * @return  lang.XPClass<? extends xp.compiler.ast.Node>
     */
    public function node();
    
    /**
     * Execute this check
     *
     * @param   xp.compiler.ast.Node node
     * @return  bool
     */
    public function verify(xp�compiler�ast�Node $node);
  }
?>
