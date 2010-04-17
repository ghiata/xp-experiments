<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('xp.compiler.ast.Node');

  /**
   * The "try (...) { block }" statement - Automatic Resource Management
   *
   */
  class ArmNode extends xp�compiler�ast�Node {
    public $assignments;
    public $statements;
    
    /**
     * Constructor
     *
     * @param   xp.compiler.ast.Node[] assignment
     * @param   xp.compiler.ast.Node[] statements
     */
    public function __construct(array $assignments, array $statements) {
      $this->assignments= $assignments;
      $this->statements= $statements;
    }
  }
?>
