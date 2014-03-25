<?php
class Stream extends \lang\Object {
  protected $elements;

  protected function __construct($elements) {
    $this->elements= $elements;
  }

  public static function of($elements) {
    if ($elements instanceof \Closure) {
      return new self($elements());
    } else {
      return new self($elements);
    }
  }

  public function toArray() {
    $return= [];
    foreach ($this->elements as $element) {
      $return[]= $element;
    }
    return $return;
  }

  public function count() {
    $return= 0;
    foreach ($this->elements as $element) {
      $return++;
    }
    return $return;
  }

  public function sum() {
    $return= 0;
    foreach ($this->elements as $element) {
      $return+= $element;
    }
    return $return;
  }

  public function filter($predicate) {
    $func= function() use($predicate) {
      foreach ($this->elements as $element) {
        if ($predicate($element)) yield $element;
      }
    };
    return new self($func());
  }

  public function map($function) {
    $func= function() use($function) {
      foreach ($this->elements as $element) {
        yield $function($element);
      }
    };
    return new self($func());
  }
}