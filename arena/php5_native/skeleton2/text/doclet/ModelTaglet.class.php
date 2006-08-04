<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('text.doclet.ModelTag', 'text.doclet.Taglet');

  /**
   * A taglet that represents the @model tag. 
   *
   * @see      xp://TagletManager
   * @purpose  Taglet
   */
  class ModelTaglet extends Object implements Taglet {
     
    /**
     * Create tag from text
     *
     * @access  public
     * @param   &Doc holder
     * @param   string kind
     * @param   string text
     * @return  &Tag
     */ 
    public function &tagFrom(&$holder, $kind, $text) {
      return new ModelTag($kind, $text);
    }

  } 
?>
