<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('xp.compiler.types.Method', 'xp.compiler.types.Constructor', 'xp.compiler.types.Field');

  /**
   * Abstract base class
   *
   */
  abstract class Types extends Object {
    const 
      CLASS_KIND        = 1,
      INTERFACE_KIND    = 2,
      ENUM_KIND         = 3;
    
    const
      UNKNOWN_KIND      = 0;

    /**
     * Returns name
     *
     * @return  string
     */
    public abstract function name();

    /**
     * Returns parent type
     *
     * @return  xp.compiler.types.Types
     */
    public abstract function parent();

    /**
     * Returns literal for use in code
     *
     * @return  string
     */
    public abstract function literal();

    /**
     * Returns type kind (one of the *_KIND constants).
     *
     * @return  string
     */
    public abstract function kind();

    /**
     * Returns whether a constructor exists
     *
     * @return  bool
     */
    public abstract function hasConstructor();

    /**
     * Returns the constructor
     *
     * @return  xp.compiler.types.Constructor
     */
    public abstract function getConstructor();

    /**
     * Returns whether a method with a given name exists
     *
     * @param   string name
     * @return  bool
     */
    public abstract function hasMethod($name);
    
    /**
     * Returns a method by a given name
     *
     * @param   string name
     * @return  xp.compiler.types.Method
     */
    public abstract function getMethod($name);

    /**
     * Returns a field by a given name
     *
     * @param   string name
     * @return  bool
     */
    public abstract function hasField($name);
    
    /**
     * Returns a field by a given name
     *
     * @param   string name
     * @return  xp.compiler.types.Field
     */
    public abstract function getField($name);
  }
?>