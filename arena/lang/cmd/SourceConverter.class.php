<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * (Insert class' description here)
   *
   * @ext      extension
   * @see      reference
   * @purpose  purpose
   */
  class SourceConverter extends Object {
    protected
      $nameMap       = NULL;

    // XP tokens
    const
      T_USES          = 0xF001,
      T_NEWINSTANCE   = 0xF002,
      T_IS            = 0xF003,
      T_CREATE        = 0xF004,
      T_RAISE         = 0xF005,
      T_FINALLY       = 0xF006,
      T_DELETE        = 0xF007,
      T_WITH          = 0xF008,
      T_REF           = 0xF009,
      T_DEREF         = 0xF00A,
      T_CAST          = 0xF00B;
    
    // States
    const
      ST_INITIAL      = 'init',
      ST_DECL         = 'decl',
      ST_FUNC         = 'func',
      ST_FUNC_ARGS    = 'farg',
      ST_INTF         = 'intf',
      ST_CLASS        = 'clss',
      ST_EXTENDS      = 'extn',
      ST_USES         = 'uses',
      ST_ANONYMOUS    = 'anon',
      ST_NAMESPACE    = 'nspc',
      ST_FUNC_BODY    = 'body';
    
    const
      SEPARATOR       = '.';

    /**
     * (Insert method's description here)
     *
     */
    public function __construct() {
      $this->nameMap= create('new util.collections.HashTable<String, String>()');
    }

    /**
     * Returns a token array
     *
     * @param   mixed t
     * @return  array
     */
    protected function tokenOf($t, $last= NULL) {
      static $map= array(
        'uses'          => self::T_USES,
        'newinstance'   => self::T_NEWINSTANCE,
        'is'            => self::T_IS,
        'create'        => self::T_CREATE,
        'raise'         => self::T_RAISE,
        'cast'          => self::T_CAST,
        'finally'       => self::T_FINALLY,
        'delete'        => self::T_DELETE,
        'with'          => self::T_WITH,
        'ref'           => self::T_REF,
        'deref'         => self::T_DEREF,
      );

      $normalized= is_array($t) ? $t : array($t, $t);
      if (
        (!is_array($last) || $last[0] !== T_OBJECT_OPERATOR) &&
        T_STRING == $normalized[0] && isset($map[$normalized[1]])
      ) {
        $normalized[0]= $map[$normalized[1]];
      }
      return $normalized;
    }
    
    /**
     * Maps a name to its fully qualified name
     *
     * @param   string qname in dot-notation (package.Name)
     * @param   string namespace default NULL in colon-notation
     * @param   array<string, bool> imports
     * @param   string context
     * @return  string in colon-notation (package::Name)
     */
    protected function mapName($qname, $namespace= NULL, array $imports= array(), $context= '?') {
      if (NULL === ($mapped= $this->nameMap[$qname])) {

        // If the searched class resides in the same namespace, it does not
        // need to be fully qualified or mapped, so check for this
        $search= ($namespace !== NULL ? str_replace(self::SEPARATOR, '.', $namespace).'.' : '').$qname;
        if (!ClassLoader::getDefault()->findClass($search) instanceof IClassLoader) {
          throw new IllegalStateException('*** No mapping for '.$qname.' (current namespace: '.$namespace.', class= '.$context.')');
        }
        return $qname;
      }

      // Return local name if mapped name is in imports or current namespace
      if (isset($imports[(string)$mapped])) {
        return $imports[(string)$mapped]; 
      } else if (FALSE !== ($p= strrpos($mapped, self::SEPARATOR)) && $namespace == substr($mapped, 0, $p)) {
        return substr($mapped, $p+ 2);
      } else {
        return $mapped;
      }
    }

    /**
     * Convert sourcecode and return the computed version
     *
     * @param   string qname fully qualified name of class
     * @param   array t tokens as returned by token_get_call
     * @param   string initial default ST_INITIAL
     * @return  string converted sourcecode
     */
    public function convert($qname, array $t, $initial= self::ST_INITIAL) {

      // Calculate class and package name from qualified name
      $p= strrpos($qname, '.');
      $package= substr($qname, 0, $p);
      $namespace= str_replace('.', self::SEPARATOR, $package);
      $class= substr($qname, $p+ 1);
      
      // Tokenize file
      $state= array($initial);
      $imports= array();
      $out= '';
      for ($i= 0, $s= sizeof($t); $i < $s; $i++) {
        $token= $this->tokenOf($t[$i], ($i > 0 ? $t[$i- 1] : NULL));
        switch ($state[0].$token[0]) {
          case self::ST_INITIAL.T_OPEN_TAG: {
            continue 2;
          }

          case self::ST_DECL.T_CLOSE_TAG: {
            $i= $s;
            continue 2;
          }
        
          // Insert namespace declaration after "This class is part of..." file comment
          case self::ST_INITIAL.T_COMMENT: {
            $out.= $token[1]."\n\npackage ".str_replace('.', self::SEPARATOR, $namespace).';';
            array_unshift($state, self::ST_NAMESPACE);
            break;
          }
          
          // $package= 'package.name'; - swallow completely
          case self::ST_NAMESPACE.T_VARIABLE: {
            while (';' !== $t[$i] && $i < $s) { $i++; }
            break;
          }
          
          // Remember loaded classes in uses() for use as mapping
          case self::ST_NAMESPACE.self::T_USES: {
            $uses= array();
            array_unshift($state, self::ST_USES);
            $out= rtrim($out)."\n";
            break;
          }
          
          case self::ST_USES.T_CONSTANT_ENCAPSED_STRING: {
            $fqcn= self::SEPARATOR.str_replace('.', self::SEPARATOR, trim($token[1], "'"));
            $local= substr($fqcn, strrpos($fqcn, self::SEPARATOR)+ strlen(self::SEPARATOR));
            if ($local == $class) {
              // $this->err->writeLine('*** Name clash between ', $fqcn, ' and declared ', $class, ' in ', $qname, ', using qualified name for ', $fqcn);
              $imports[$fqcn]= $fqcn;
            } else if ($other= array_search($local, $imports)) {
              // $this->err->writeLine('*** Name clash between ', $fqcn, ' and other ', $other, ' in ', $qname, ', using qualified name for ', $fqcn);
              $imports[$fqcn]= $fqcn;
            } else {
              $uses[]= substr($fqcn, 1);
              $imports[$fqcn]= $local;
            }
            break;
          }
          
          case self::ST_USES.'(': case self::ST_USES.',': case self::ST_USES.')': 
          case self::ST_USES.T_WHITESPACE:
            // Swallow token
            break;
          
          case self::ST_USES.';': {
            foreach ($uses as $fqcn) {
              $out.= 'import '.$fqcn.";\n";
            }
            $uses= array();
            array_shift($state);
            break;
          }
          
          // class declaration - always use local name here!
          case self::ST_NAMESPACE.T_CLASS: case self::ST_NAMESPACE.T_INTERFACE: {
            $out.= 'public '.$token[1].' ';
            $declaration= $this->tokenOf($t[$i+ 2]);
            $out.= (FALSE !== $p= strrpos($declaration[1], '�')) ? substr($declaration[1], $p+ 1) : $declaration[1];
            $i+= 2;
            array_unshift($state, self::ST_DECL);
            break;
          }
          
          // extends X -> Remove if "Object" === X
          case self::ST_DECL.T_EXTENDS: {
            array_unshift($state, self::ST_EXTENDS);
            break;
          }
          
          case self::ST_EXTENDS.T_WHITESPACE: {
            break;
          }
          
          case self::ST_EXTENDS.T_STRING: {
            if ('Object' !== ($parent= $this->mapName($token[1], $namespace, $imports, $qname))) {
              $out.= 'extends '.$parent;
            } else {
              $out= rtrim($out);
            }
            array_shift($state);
            break;
          }
          
          // instanceof X, new X, catch(X $var)
          case self::ST_DECL.T_INSTANCEOF: 
          case self::ST_DECL.T_NEW: case self::ST_DECL.T_CATCH: {
            $out.= $token[1];
            array_unshift($state, self::ST_CLASS);
            break;
          }
          
          case self::ST_CLASS.T_STRING: {
            $out.= $this->mapName($token[1], $namespace, $imports, $qname);
            array_shift($state);
            break;
          }

          case self::ST_CLASS.T_VARIABLE: {
            $out.= $token[1];
            array_shift($state);
            break;
          }

          // implements X, Y
          case self::ST_DECL.T_IMPLEMENTS: {
            $out.= $token[1];
            array_unshift($state, self::ST_INTF);
            break;
          }
          
          case self::ST_INTF.T_STRING: {
            $out.= $this->mapName($token[1], $namespace, $imports, $qname);
            break;
          }
          
          case self::ST_INTF.'{': {
            $out.= $token[1];
            array_shift($state);
            break;
          }
          
          // X::y(), X::$y, X::const
          case self::ST_DECL.T_STRING: {
            $next= $this->tokenOf($t[$i+ 1]);
            if (T_DOUBLE_COLON == $next[0]) {
              $out.= $this->mapName($token[1], $namespace, $imports, $qname);

              // Swallow token after double colon
              // (fixes self::create() being rewritten to self::::create())
              $member= $this->tokenOf($t[$i+ 2]);
              $out.= '::'.$member[1];
              $i+= 2;

              // ClassLoader::defineClass('fully.qualified', 'parent.fqcn', array('interface.fqcns'), '{ source }');
              // ClassLoader::defineInterface('fully.qualified', array('parent.fqcns'), '{ source }');
              $complete= $token[1].self::SEPARATOR.$member[1];
              $converted= NULL;
              if ('ClassLoader::defineClass' == $token[1].'::'.$member[1] || 'ClassLoader::defineInterface' == $token[1].'::'.$member[1]) {
                do {
                  $next= $this->tokenOf($t[++$i]);
                  if (';' == $next[0]) {
                    $out.= $next[1];
                    break;
                  } else if (T_CONSTANT_ENCAPSED_STRING === $next[0] && '{' === $next[1]{1}) {
                    $quote= $next[1]{0};
                    $converted= $this->convert('', token_get_all('<?php '.trim($next[1], $quote).' ?>'), self::ST_DECL);
                    $out.= $quote.substr($converted, 6, -3).$quote;
                  } else {
                    $out.= $next[1];
                  }
                } while (!$converted && $i < $s);
              }
            } else {
              $out.= $token[1];
            }
            break;
          }
          
          case self::ST_DECL.T_VARIABLE: {
            $out.= 'var '.$token[1];
            break;
          }
          
          // Comment: parse @param / @return ...
          case self::ST_DECL.T_DOC_COMMENT: {
            $meta= NULL;
            preg_match_all(
              '/@([a-z]+)\s*([^<\r\n]+<[^>]+>|[^\r\n ]+) ?([^\r\n ]+)?/',
              $token[1], 
              $matches, 
              PREG_SET_ORDER
            );
            foreach ($matches as $match) {
              @$meta[$match[1]][]= $match[2];
            }
            $out.= str_replace("\n  ", "\n", $token[1]);
            break;
          }
          
          // function name(X $var, Y $type)
          case self::ST_DECL.T_FUNCTION: {
            array_unshift($state, self::ST_FUNC);
            break;
          }
          
          case self::ST_FUNC.T_WHITESPACE: {
            // Swallow
            break;
          }
          
          case self::ST_FUNC.T_STRING: {
            $brackets= 0;
            if ('__construct' !== $token[1]) {
              $out.= isset($meta['return']) ? $meta['return'][0].' ' : 'void ';
            }
            $out.= $token[1];
            array_unshift($state, self::ST_FUNC_ARGS);
            $type= 'var';
            break;
          }
          
          case self::ST_FUNC.'{': {
            $brackets= 0;
            $out.= ' {';
            array_shift($state);
            array_unshift($state, self::ST_FUNC_BODY);
            break;
          }

          case self::ST_FUNC_BODY.'{': {
            $out.= $token[1];
            $brackets++;
            break;
          }

          case self::ST_FUNC_BODY.'}': {
            $out.= $token[1];
            $brackets--;
            if ($brackets <= 0) {
              array_shift($state);
            }
            break;
          }
            
          case self::ST_FUNC.';': {
            $out.= ';';
            array_shift($state);
            break;
          }
          
          case self::ST_FUNC_ARGS.'(': {
            $out.= $token[1];
            $brackets++;
            break;
          }

          case self::ST_FUNC_ARGS.')': {
            $out.= $token[1];
            $brackets--;
            if ($brackets <= 0) {
              array_shift($state);
            }
            break;
          }
          
          case self::ST_FUNC_ARGS.T_STRING: {
            $nonClassTypes= array('array');   // Type hints that are not classes

            // Look ahead, decl(array $a, String $b, $c, $x= FALSE, $z= TRUE)
            // * $a -> array (non-class-type, yield array)
            // * $b -> String (map name)
            // * $c -> no type
            // * $x -> no type
            // * $z -> no type
            $ws= $this->tokenOf($t[$i+ 1]);
            $var= $this->tokenOf($t[$i+ 2]);
            if (T_WHITESPACE === $ws[0] && T_VARIABLE === $var[0]) {
              $type= in_array($token[1], $nonClassTypes) ? $token[1] : $this->mapName($token[1], $namespace, $imports, $qname);
            } else {
              $out.= $token[1];
            }
            break;
          }
          
          case self::ST_FUNC_ARGS.T_VARIABLE: {
            $out.= $type.' '.$token[1];
            break;
          }
          
          // Anonymous class creation - newinstance('fully.qualified', array(...), '{ source }');
          case self::ST_FUNC_BODY.self::T_NEWINSTANCE:  {
            $out.= self::SEPARATOR.'newinstance('.$t[$i+ 2][1];
            $i+= 2;
            array_unshift($state, self::ST_ANONYMOUS);
            break;
          }
          
          case self::ST_ANONYMOUS.T_CONSTANT_ENCAPSED_STRING: {
            $quote= $token[1]{0};
            $converted= $this->convert('', token_get_all('<?php '.trim($token[1], $quote).' ?>'), self::ST_DECL);
            $out.= $quote.substr($converted, 6, -3).$quote;
            array_shift($state);
            break;
          }
          
          // XP "keywords"
          case self::ST_ANONYMOUS.self::T_CREATE:case self::ST_FUNC_BODY.self::T_CREATE:
          case self::ST_ANONYMOUS.self::T_REF: case self::ST_FUNC_BODY.self::T_REF: 
          case self::ST_ANONYMOUS.self::T_DEREF: case self::ST_FUNC_BODY.self::T_DEREF:
          case self::ST_FUNC_BODY.self::T_RAISE: case self::ST_FUNC_BODY.self::T_FINALLY:
          case self::ST_FUNC_BODY.self::T_DELETE: case self::ST_FUNC_BODY.self::T_WITH: 
          case self::ST_FUNC_BODY.self::T_IS: case self::ST_FUNC_BODY.self::T_CAST: {
            $out.= $token[1];
            break;
          }
          
          // Replace concat operator with ~
          case self::ST_FUNC_BODY.'.': {
            $out.= ' ~ ';
            break;
          }
          
          // Replace object operator with "."
          case self::ST_FUNC_BODY.T_OBJECT_OPERATOR: {
            $out.= '.';
            break;
          }
          
          default: {
            $out.= str_replace("\n  ", "\n", $token[1]);
          }
        }
      }

      return $out;      
    }
  }
?>