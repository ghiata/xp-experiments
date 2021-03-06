<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('peer.net.dns.Record');

  /**
   * NAPTR record
   *
   * @see   http://en.wikipedia.org/wiki/NAPTR_record
   */
  class NAPTRRecord extends peer�net�dns�Record {
    protected $target, $priority, $weight;
    
    /**
     * Creates a new NS record
     *
     * @param   string name
     * @param   int ttl
     * @param   int priority
     * @param   int weight
     * @param   int port
     * @param   string target
     */
    public function __construct($name, $ttl, $order, $pref, $flags, $service, $regex, $replacement) {
      parent::__construct($name, $ttl);
      $this->order= $order;
      $this->pref= $pref;
      $this->flags= $flags;
      $this->service= $service;
      $this->regex= $regex;
      $this->replacement= $replacement;
    }

    /**
     * Returns priority
     *
     * @return  int
     */
    public function getPriority() {
      return $this->priority;
    }

    /**
     * Returns preference
     *
     * @return  int
     */
    public function getPreference() {
      return $this->pref;
    }

    /**
     * Returns flags
     *
     * @return  string
     */
    public function getFlags() {
      return $this->flags;
    }

    /**
     * Returns service
     *
     * @return  string
     */
    public function getService() {
      return $this->service;
    }

    /**
     * Returns regex
     *
     * @return  string
     */
    public function getRegex() {
      return $this->regex;
    }

    /**
     * Returns replacement
     *
     * @return  string
     */
    public function getReplacement() {
      return $this->replacement;
    }

    /**
     * Returns whether a given object is equal to this record
     *
     * @param   var cmp
     * @return  bool
     */
    public function equals($cmp) {
      return (
        $cmp instanceof self && 
        $this->name === $cmp->name && 
        $this->ttl === $cmp->ttl && 
        $this->order === $cmp->order &&
        $this->pref === $cmp->pref &&
        $this->flags === $cmp->flags &&
        $this->service === $cmp->service &&
        $this->regex === $cmp->regex &&
        $this->replacement === $cmp->replacement
      );
    }

    /**
     * Creates a string representation of this record
     *
     * @return  string
     */
    public function toString() {
      return $this->getClassName().'('.$this->name.' ttl '.$this->ttl.' '.$this->flags.': "'.$this->service.'" "'.$this->regex.'" "'.$this->replacement.'", order= '.$this->order.' pref= '.$this->pref.');';
    }
  }
?>
