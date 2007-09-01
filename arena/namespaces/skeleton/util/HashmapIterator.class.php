<?php
/* This class is part of the XP framework
 *
 * $Id: HashmapIterator.class.php 10611 2007-06-13 15:40:01Z friebe $ 
 */

  namespace util;

  ::uses('util.XPIterator');

  /**
   * Iterator over the keys of a Hashmap object
   *
   * Usage code snippet:
   * <code>
   *   // ...
   *   for ($i= $hash->iterator(); $i->hasNext(); ) {
   *     $key= $i->next();
   *     var_dump($key, $hash->get($key));
   *   }
   *   // ...
   * </code>
   *
   * @see      xp://util.Hashmap
   * @purpose  Iterator
   */
  class HashmapIterator extends lang::Object implements XPIterator {
    public
      $_hash    = NULL,
      $_key     = FALSE;
  
    /**
     * Constructor
     *
     * @param   array hash
     * @see     xp://util.Hashmap#iterator
     */
    public function __construct($hash) {
      $this->_hash= (array) $hash;
      reset($this->_hash);
      $this->_key= key($this->_hash);
    }
  
    /**
     * Returns true if the iteration has more elements. (In other words, 
     * returns true if next would return an element rather than throwing 
     * an exception.)
     *
     * @return  bool
     */
    public function hasNext() {
      return !is_null($this->_key);
    }
    
    /**
     * Returns the next element in the iteration.
     *
     * @return  mixed
     * @throws  util.NoSuchElementException when there are no more elements
     */
    public function next() {
      if (is_null($this->_key)) throw(new NoSuchElementException('No more elements'));
      $oldkey= $this->_key;
      next($this->_hash);
      $this->_key= key($this->_hash);
      return $this->_hash[$oldkey];
    }
  } 
?>
