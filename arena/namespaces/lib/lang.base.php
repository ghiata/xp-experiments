<?php
/* This file provides the core for the XP framework
 * 
 * $Id$
 */

  // {{{ final class xp
  final class xp {
    const CLASS_FILE_EXT= '.class.php';

    public static $registry  = array(
      'errors'     => array(),
      'sapi'       => array(),
      'class.xp'   => '<xp>',
      'class.null' => '<null>',
    );

    // {{{ public string loadClass0(string name)
    //     Loads a class by its fully qualified name
    function loadClass0($class) {
      if (isset(xp::$registry['classloader.'.$class])) {
        return substr(array_search($class, xp::$registry), 6);
      }

      $package= NULL;
      foreach (xp::$registry['classpath'] as $path) {

        // If path is a directory and the included file exists, load it
        if (is_dir($path) && file_exists($f= $path.DIRECTORY_SEPARATOR.strtr($class, '.', DIRECTORY_SEPARATOR).xp::CLASS_FILE_EXT)) {
          if (FALSE === ($r= include($f))) {
            xp::error('Cannot bootstrap class '.$class.' (from file "'.$f.'")');
          }
          
          xp::$registry['classloader.'.$class]= 'lang.FileSystemClassLoader://'.$path;
          break;
        } else if (is_file($path) && file_exists($f= 'xar://'.$path.'?'.strtr($class, '.', '/').xp::CLASS_FILE_EXT)) {

          // To to load via bootstrap class loader, if the file cannot provide the class-to-load
          // skip to the next include_path part
          if (FALSE === ($r= include($f))) {
            continue;
          }

          xp::$registry['classloader.'.$class]= 'lang.archive.ArchiveClassLoader://'.$path;
          break;
        }
      }
      
      // Verify the requested class could be loaded
      if (!isset(xp::$registry['classloader.'.$class])) {
        xp::error('Cannot bootstrap class '.$class.' (include_path= '.get_include_path().')');
      }

      // Register class name and call static initializer if available
      $name= ($package ? strtr($package, '.', '�').'�' : '').xp::reflect($class);
      xp::$registry['class.'.$name]= $class;
      is_callable(array($name, '__static')) && call_user_func(array($name, '__static'));
      
      return $name;
    }
    // }}}

    // {{{ public string nameOf(string name)
    //     Returns the fully qualified name
    static function nameOf($name) {
      if (!($n= xp::registry('class.'.$name))) {
        return $name ? 'php.'.$name : NULL;
      }
      return $n;
    }
    // }}}

    // {{{ public string typeOf(mixed arg)
    //     Returns the fully qualified type name
    static function typeOf($arg) {
      return is_object($arg) ? xp::nameOf(get_class($arg)) : gettype($arg);
    }
    // }}}

    // {{{ public string stringOf(mixed arg [, string indent default ''])
    //     Returns a string representation of the given argument
    static function stringOf($arg, $indent= '') {
      static $protect= array();
      
      if (is_string($arg)) {
        return '"'.$arg.'"';
      } else if (is_bool($arg)) {
        return $arg ? 'true' : 'false';
      } else if (is_null($arg)) {
        return 'null';
      } else if ($arg instanceof null) {
        return '<null>';
      } else if (is_int($arg) || is_float($arg)) {
        return (string)$arg;
      } else if ($arg instanceof \lang\Generic && !isset($protect[$arg->hashCode()])) {
        $protect[$arg->hashCode()]= TRUE;
        $s= $arg->toString();
        unset($protect[$arg->hashCode()]);
        return $s;
      } else if (is_array($arg)) {
        $ser= serialize($arg);
        if (isset($protect[$ser])) return '->{:recursion:}';
        $protect[$ser]= TRUE;
        $r= "[\n";
        foreach (array_keys($arg) as $key) {
          $r.= $indent.'  '.$key.' => '.xp::stringOf($arg[$key], $indent.'  ')."\n";
        }
        unset($protect[$ser]);
        return $r.$indent.']';
      } else if (is_object($arg)) {
        $ser= serialize($arg);
        if (isset($protect[$ser])) return '->{:recursion:}';
        $protect[$ser]= TRUE;
        $r= xp::nameOf(get_class($arg))." {\n";
        $vars= (array)$arg;
        foreach (array_keys($vars) as $key) {
          $r.= $indent.'  '.$key.' => '.xp::stringOf($vars[$key], $indent.'  ')."\n";
        }
        unset($protect[$ser]);
        return $r.$indent.'}';
      } else if (is_resource($arg)) {
        return 'resource(type= '.get_resource_type($arg).', id= '.(int)$arg.')';
      }
    }
    // }}}

    // {{{ public void gc()
    //     Runs the garbage collector
    static function gc() {
      xp::$registry['errors']= array();
    }
    // }}}

    // {{{ public <null> null()
    //     Runs a fatal-error safe version of NULL
    static function null() {
      return xp::$registry['null'];
    }
    // }}}

    // {{{ public bool errorAt(string file [, int line)
    //     Returns whether an error occured at the specified position
    static function errorAt($file, $line= -1) {
      $errors= xp::$registry['errors'];
      
      // If no line is given, check for an error in the file
      if ($line < 0) return !empty($errors[$file]);
      
      // Otherwise, check for an error in the file on a certain line
      return !empty($errors[$file][$line]);
    }
    // }}}
    
    // {{{ public mixed sapi(string* sapis)
    //     Sets an SAPI
    static function sapi() {
      foreach ($a= func_get_args() as $name) {
        foreach (xp::$registry['classpath'] as $path) {
          $filename= 'sapi'.DIRECTORY_SEPARATOR.strtr($name, '.', DIRECTORY_SEPARATOR).'.sapi.php';
          if (is_dir($path) && file_exists($f= $path.DIRECTORY_SEPARATOR.$filename)) {
            require_once($f);
            continue 2;
          } else if (is_file($path) && file_exists($f= 'xar://'.$path.'?'.strtr($filename, DIRECTORY_SEPARATOR, '/'))) {
            require_once($f);
            continue 2;
          }
        }
        
        xp::error('Cannot open SAPI '.$name.' (include_path='.get_include_path().')');
      }
      xp::$registry['sapi']= $a;
    }
    // }}}
    
    // {{{ internal mixed registry(mixed args*)
    //     Stores static data
    static function registry() {
      switch (func_num_args()) {
        case 0: return xp::$registry;
        case 1: return @xp::$registry[func_get_arg(0)];
        case 2: xp::$registry[func_get_arg(0)]= func_get_arg(1); break;
      }
      return NULL;
    }
    // }}}
    
    // {{{ internal string reflect(string str)
    //     Retrieve PHP conformant name for fqcn
    static function reflect($str) {
      return str_replace('.', '\\', $str);
    }
    // }}}

    // {{{ internal void error(string message)
    //     Throws a fatal error and exits with exitcode 61
    static function error($message) {
      restore_error_handler();
      trigger_error($message, E_USER_ERROR);
      exit(0x3d);
    }
  }
  // }}}

  // {{{ final class null
  class null {

    // {{{ public object __construct(void)
    //     Constructor to avoid magic __call invokation
    public function __construct() {
      if (isset(xp::$registry['null'])) {
        throw new \lang\IllegalAccessException('Cannot create new instances of xp::null()');
      }
    }
    
    // {{{ public void __clone(void)
    //     Clone interceptor
    public function __clone() {
      throw new \lang\NullPointerException('Object cloning intercepted.');
    }
    // }}}
    
    // {{{ magic mixed __call(string name, mixed[] args)
    //     Call proxy
    function __call($name, $args) {
      throw new \lang\NullPointerException('Method.invokation('.$name.')');
    }
    // }}}

    // {{{ magic void __set(string name, mixed value)
    //     Set proxy
    function __set($name, $value) {
      throw new \lang\NullPointerException('Property.write('.$name.')');
    }
    // }}}

    // {{{ magic mixed __get(string name)
    //     Set proxy
    function __get($name) {
      throw new \lang\NullPointerException('Property.read('.$name.')');
    }
    // }}}
  }
  // }}}
  // {{{ final class xploader
  class xarloader {
    public
      $position     = 0,
      $archive      = '',
      $filename     = '';
      
    // {{{ static mixed[] acquire(string archive)
    //     Archive instance handling pool function, opens an archive and reads header only once
    static function acquire($archive) {
      static $archives= array();
      static $unpack= array(
        1 => 'a80id/a80*filename/a80*path/V1size/V1offset/a*reserved',
        2 => 'a240id/V1size/V1offset/a*reserved'
      );
      
      if (!isset($archives[$archive])) {
        $archives[$archive]= array();
        $current= &$archives[$archive];
        $current['handle']= fopen($archive, 'rb');
        $header= unpack('a3id/c1version/V1indexsize/a*reserved', fread($current['handle'], 0x0100));
        if ('CCA' != $header['id']) raise('lang.FormatException', 'Malformed archive '.$archive);
        for ($current['index']= array(), $i= 0; $i < $header['indexsize']; $i++) {
          $entry= unpack(
            $unpack[$header['version']], 
            fread($current['handle'], 0x0100)
          );
          $current['index'][$entry['id']]= array($entry['size'], $entry['offset']);
        }
      }

      return $archives[$archive];
    }
    // }}}
    
    // {{{ function bool stream_open(string path, string mode, int options, string opened_path)
    //     Open the given stream and check if file exists
    function stream_open($path, $mode, $options, $opened_path) {
      sscanf($path, 'xar://%[^?]?%[^$]', $archive, $file);
      $this->archive= urldecode($archive);
      $this->filename= $file;
      
      $current= self::acquire($this->archive);
      return isset($current['index'][$this->filename]);
    }
    // }}}
    
    // {{{ string stream_read(int count)
    //     Read $count bytes up-to-length of file
    function stream_read($count) {
      $current= self::acquire($this->archive);
      if (!isset($current['index'][$this->filename])) return FALSE;
      if ($current['index'][$this->filename][0] == $this->position || 0 == $count) return FALSE;

      fseek($current['handle'], 0x0100 + sizeof($current['index']) * 0x0100 + $current['index'][$this->filename][1] + $this->position, SEEK_SET);
      $bytes= fread($current['handle'], min($current['index'][$this->filename][0]- $this->position, $count));
      $this->position+= strlen($bytes);
      return $bytes;
    }
    // }}}
    
    // {{{ bool stream_eof()
    //     Returns whether stream is at end of file
    function stream_eof() {
      $current= self::acquire($this->archive);
      return $this->position >= $current['index'][$this->filename][0];
    }
    // }}}
    
    // {{{ <string,int> stream_stat()
    //     Retrieve status of stream
    function stream_stat() {
      $current= self::acquire($this->archive);
      return array(
        'size'  => $current['index'][$this->filename][0]
      );
    }
    // }}}

    // {{{ bool stream_seek(int offset, int whence)
    //     Callback for fseek
    function stream_seek($offset, $whence) {
      switch ($whence) {
        case SEEK_SET: $this->position= $offset; break;
        case SEEK_CUR: $this->position+= $offset; break;
        case SEEK_END: 
          $current= self::acquire($this->archive);
          $this->position= $current['index'][$this->filename][0] + $offset; 
          break;
      }
      return TRUE;
    }
    // }}}
    
    // {{{ int stream_tell
    //     Callback for ftell
    function stream_tell() {
      return $this->position;
    }
    // }}}
    
    // {{{ <string,int> url_stat(string path)
    //     Retrieve status of url
    function url_stat($path) {
      sscanf($path, 'xar://%[^?]?%[^$]', $archive, $file);
      $current= self::acquire(urldecode($archive));

      return isset($current['index'][$file]) 
        ? array('size' => $current['index'][$file][0])
        : FALSE
      ;
    }
    // }}}
  }
  // }}}

  // {{{ internal void __error(int code, string msg, string file, int line)
  //     Error callback
  function __error($code, $msg, $file, $line) {
    if (0 == error_reporting() || is_null($file)) return;

    if (E_RECOVERABLE_ERROR == $code) {
      throw new \lang\IllegalArgumentException($msg.' @ '.$file.':'.$line);
    } else {
      @xp::$registry['errors'][$file][$line][$msg]++;
    }
  }
  // }}}

  // {{{ void uses (string* args)
  //     Uses one or more classes
  function uses() {
    foreach (func_get_args() as $str) xp::$registry['loader']->loadClass0($str);
  }
  // }}}
  
  // {{{ void raise (string classname, mixed* args)
  //     throws an exception by a given class name
  function raise($classname) {
    try {
      $class= \lang\XPClass::forName($classname);
    } catch (\lang\ClassNotFoundException $e) {
      xp::error($e->getMessage());
    }
    
    $a= func_get_args();
    throw call_user_func_array(array($class, 'newInstance'), array_slice($a, 1));
  }
  // }}}

  // {{{ void finally (void)
  //     Syntactic sugar. Intentionally empty
  function finally() {
  }
  // }}}

  // {{{ Generic cast (Generic expression, string type)
  //     Casts an expression.
  function cast(\lang\Generic $expression= NULL, $type) {
    if (NULL === $expression) {
      return xp::null();
    } else if (\lang\XPClass::forName($type)->isInstance($expression)) {
      return $expression;
    }

    raise('lang.ClassCastException', 'Cannot cast '.xp::typeOf($expression).' to '.$type);
   }

  // {{{ proto bool is(string class, lang.Object object)
  //     Checks whether a given object is of the class, a subclass or implements an interface
  function is($class, $object) {
    if (NULL === $class) return $object instanceof null;

    $class= xp::reflect($class);
    return $object instanceof $class;
  }
  // }}}

  // {{{ proto void delete(&lang.Object object)
  //     Destroys an object
  function delete(&$object) {
    $object= NULL;
  }
  // }}}

  // {{{ proto void with(expr)
  //     Syntactic sugar. Intentionally empty
  function with() {
  }
  // }}}
  
  // {{{ proto mixed ref(mixed object)
  //     Creates a "reference" to an object
  function ref(&$object) {
    return array(&$object);
  }
  // }}}

  // {{{ proto &mixed deref(&mixed expr)
  //     Dereferences an expression
  function &deref(&$expr) {
    if (is_array($expr)) return $expr[0]; else return $expr;
  }
  // }}}

  // {{{ proto lang.Object newinstance(string classname, mixed[] args, string bytes)
  //     Anonymous instance creation
  function newinstance($classname, $args, $bytes) {
    static $u= 0;

    $class= xp::reflect($classname);
    if (!class_exists($class) && !interface_exists($class)) {
      xp::error(xp::stringOf(new \lang\Error('Class "'.$classname.'" does not exist')));
      // Bails
    }

    $name= substr($class, strrpos($class, '::')+ 2).'�'.(++$u);
    
    // Checks whether an interface or a class was given
    $cl= \lang\DynamicClassLoader::instanceFor(__FUNCTION__);
    if (interface_exists($class)) {
      $cl->setClassBytes($name, 'class '.$name.' extends \lang\Object implements '.$class.' '.$bytes);
    } else {
      $cl->setClassBytes($name, 'class '.$name.' extends '.$class.' '.$bytes);
    }

    $cl->loadClass0($name);

    // Build paramstr for evaluation
    for ($paramstr= '', $i= 0, $m= sizeof($args); $i < $m; $i++) {
      $paramstr.= ', $args['.$i.']';
    }
    return eval('return new '.$name.'('.substr($paramstr, 2).');');
  }
  // }}}

  // {{{ lang.Generic create(mixed spec)
  //     Creates a generic object
  function create($spec) {
    if ($spec instanceof \lang\Generic) return $spec;

    sscanf($spec, 'new %[^<]<%[^>]>', $classname, $types);
    $class= xp::reflect($classname);
    
    // Check whether class is generic
    if (!property_exists($class, '__generic')) {
      throw new \lang\IllegalArgumentException('Class '.$classname.' is not generic');
    }
    
    // Instanciate without invoking the constructor and pass type information. 
    // This is done so that the constructur can already use generic types.
    $__id= microtime();
    $instance= unserialize('O:'.strlen($class).':"'.$class.'":1:{s:4:"__id";s:'.strlen($__id).':"'.$__id.'";}');
    foreach (explode(',', $types) as $type) {
      $instance->__generic[]= xp::reflect(trim($type));
    }
    
    // Call constructor if available
    if (is_callable(array($instance, '__construct'))) {
      $a= func_get_args();
      call_user_func_array(array($instance, '__construct'), array_slice($a, 1));
    }

    return $instance;
  }
  // }}}

  // {{{ void __autoload(string fqcn)
  //     Class loading interception
  function __autoload($fqcn) {
    uses(str_replace('\\', '.', $fqcn));
  }
  // }}}

  // {{{ initialization
  error_reporting(E_ALL);
  
  // Get rid of magic quotes 
  get_magic_quotes_gpc() && xp::error('[xp::core] magic_quotes_gpc enabled');
  ini_set('magic_quotes_runtime', FALSE);
  
  // Constants
  define('LONG_MAX', PHP_INT_MAX);
  define('LONG_MIN', -PHP_INT_MAX - 1);

  // Hooks
  set_error_handler('__error');
  
  // Registry initialization
  xp::$registry['null']= new null();
  xp::$registry['loader']= new xp();
  xp::$registry['classpath']= array_filter(array_map('realpath', explode(PATH_SEPARATOR, get_include_path())));

  // Register stream wrapper for .xar class loading
  stream_wrapper_register('xar', 'xarloader');
  
  // Bootstrapping
  uses(
    'lang.Generic',
    'lang.Object',
    'lang.StackTraceElement',
    'lang.Throwable',
    'lang.Error',
    'lang.XPException',
    'lang.IClassLoader',
    'lang.Type',
    'lang.XPClass',
    'lang.NullPointerException',
    'lang.IllegalAccessException',
    'lang.IllegalArgumentException',
    'lang.IllegalStateException',
    'lang.ClassNotFoundException',
    'lang.FormatException',
    'lang.FileSystemClassLoader',
    'lang.archive.ArchiveClassLoader',
    'lang.ClassLoader',
    'lang.reflect.Modifiers'
  );
  // }}}
?>
