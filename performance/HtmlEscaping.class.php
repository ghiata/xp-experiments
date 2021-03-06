<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('lang.Enum', 'Profileable');

  /**
   * HTML escaping profiling
   *
   */
  abstract class HtmlEscaping extends Enum implements Profileable {
    public static
      $htmlspecialchars,
      $strtr,
      $str_replace,
      $iteration,
      $spanning,
      $preg_replace;
    
    protected $in= '<He said: "Hello & World">';

    static function __static() {
      self::$htmlspecialchars= newinstance(__CLASS__, array(0, 'htmlspecialchars'), '{
        static function __static() { }

        public function run($times) {
          $in= $this->in;
          for ($i= 0; $i < $times; $i++) {
            htmlspecialchars($in, ENT_COMPAT, "iso-8859-1");
          }
        }
      }');
      self::$strtr= newinstance(__CLASS__, array(1, 'strtr'), '{
        static function __static() { }

        public function run($times) {
          $in= $this->in;
          $r= array("&" => "&amp;", "\"" => "&quot;", "<" => "&lt;", ">" => "&gt;");
          for ($i= 0; $i < $times; $i++) {
            strtr($in, $r);
          }
        }
      }');
      self::$str_replace= newinstance(__CLASS__, array(2, 'str_replace'), '{
        static function __static() { }

        public function run($times) {
          $in= $this->in;
          $s= array("&",     "\"",     "<",    ">");
          $r= array("&amp;", "&quot;", "&lt;", "&gt;");
          for ($i= 0; $i < $times; $i++) {
            str_replace($s, $r, $in);
          }
        }
      }');
      self::$iteration= newinstance(__CLASS__, array(3, 'iteration'), '{
        static function __static() { }

        public function run($times) {
          $in= $this->in;
          $r= array("&" => "&amp;", "\"" => "&quot;", "<" => "&lt;", ">" => "&gt;");
          for ($i= 0; $i < $times; $i++) {
            $out= "";
            for ($p= 0, $s= strlen($in); $p < $s; $p++) {
              $c= $in{$p};
              if (isset($r[$c])) $out.= $r[$c]; else $out.= $c;
            }
          }
        }
      }');
      self::$spanning= newinstance(__CLASS__, array(4, 'spanning'), '{
        static function __static() { }

        public function run($times) {
          $in= $this->in;
          $r= array("&" => "&amp;", "\"" => "&quot;", "<" => "&lt;", ">" => "&gt;");
          for ($i= 0; $i < $times; $i++) {
            $out= "";
            $p= 0;
            $l= strlen($in);
            do {
              $s= strcspn($in, "&\"<>", $p);
              $out.= substr($in, $p, $s).$r[$in{$p+ $s}];
              $p+= $s+ 1;
            } while ($p < $l);
          }
        }
      }');
      self::$preg_replace= newinstance(__CLASS__, array(5, 'preg_replace'), '{
        static function __static() { }

        public function run($times) {
          $in= $this->in;
          $r= array("&" => "&amp;", "\"" => "&quot;", "<" => "&lt;", ">" => "&gt;");
          for ($i= 0; $i < $times; $i++) {
            preg_replace("/[&\"\<\>]/e", "\$r[\"\$0\"]", $in);
          }
        }
      }');
    }
    
    /**
     * Returns all enum members
     *
     * @return  lang.Enum[]
     */
    public static function values() {
      return parent::membersOf(__CLASS__);
    }
  }
?>
