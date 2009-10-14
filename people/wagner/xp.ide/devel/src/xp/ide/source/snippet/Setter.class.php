<?php
/* This class is part of the XP framework
 *
 * $Id: ClassPathScanner.class.php 11282 2009-07-22 14:44:48Z ruben $ 
 */
  $package="xp.ide.source.snippet";

  uses(
    'xp.ide.source.element.Classmethod',
    'xp.ide.source.element.Classmethodparam',
    'xp.ide.source.element.Apidoc',
    'xp.ide.source.element.ApidocDirective',
    'xp.ide.source.Scope',
    'xp.ide.source.Snippet'
  );

  /**
   * source representation
   * base object
   *
   * @purpose  IDE
   */
  class xp�ide�source�snippet�Setter extends xp�ide�source�Snippet {

    public function __construct($name, $type, $xtype, $dim) {
      $this->element= new xp�ide�source�element�Classmethod('set'.ucfirst($name));
      $this->element->setApidoc(new xp�ide�source�element�Apidoc(sprintf('set member $%s'.PHP_EOL, $name)));
      $this->element->getApidoc()->setDirectives($this->getApidocParams($name, $type, $xtype, $dim));
      $this->element->setParams($this->getParams($name, $type, $xtype, $dim));
      $this->element->setContent(sprintf('$this->%1$s= $%1$s;', $name));
    }

    protected function getParams($name, $type, $xtype, $dim) {
      return array(new xp�ide�source�element�Classmethodparam($name));
    }

    protected function getApidocParams($name, $type, $xtype, $dim) {
      return array(new xp�ide�source�element�ApidocDirective(sprintf('@param %s %s', $type, $name)));
    }
  }

?>