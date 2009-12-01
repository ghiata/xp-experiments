<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'util.collections.HashTable', 
    'xp.compiler.types.ResolveException',
    'xp.compiler.types.TypeName',
    'xp.compiler.ast.ArrayNode',
    'xp.compiler.ast.MapNode',
    'xp.compiler.ast.StringNode',
    'xp.compiler.ast.IntegerNode',
    'xp.compiler.ast.HexNode',
    'xp.compiler.ast.DecimalNode',
    'xp.compiler.ast.NullNode',
    'xp.compiler.ast.BooleanNode',
    'xp.compiler.ast.ComparisonNode',
    'xp.compiler.types.Method',
    'xp.compiler.types.Types',
    'xp.compiler.types.TypeReference', 
    'xp.compiler.types.TypeReflection', 
    'xp.compiler.types.TypeDeclaration'
  );

  /**
   * Represents the current scope
   *
   * @test    xp://tests.types.ScopeTest
   */
  abstract class Scope extends Object {
    protected $task= NULL;
    protected $types= NULL;
    protected $extensions= array();
    protected $resolved= array();
    protected $packages= array('lang');
    
    public $enclosing= NULL;
    public $importer= NULL;
    public $declarations= array();
    public $imports= array();
    public $used= array();
    public $package= NULL;
    public $statics= array();

    /**
     * Constructor
     *
     */
    public function __construct() {
      $this->types= create('new util.collections.HashTable<xp.compiler.ast.Node, xp.compiler.types.TypeName>()');
      $this->resolved= create('new util.collections.HashTable<lang.types.String, xp.compiler.types.Types>()');
    }
    
    /**
     * Enter a child scope
     *
     * @param   xp.compiler.types.Scope child
     * @return  xp.compiler.types.Scope child
     */
    public function enter(self $child) {
      $child->enclosing= $this;
      
      // Copy everything except types which are per-scope
      $child->importer= $this->importer;
      $child->task= $this->task;
      $child->package= $this->package;
      
      // Reference arrays - TODO: Refactor and use Vectors instead
      $child->resolved= &$this->resolved;
      $child->extensions= &$this->extensions;
      $child->declarations= &$this->declarations;
      $child->imports= &$this->imports;
      $child->packages= &$this->packages;
      $child->used= &$this->used;
      $child->statics= &$this->statics;

      return $child;
    }

    /**
     * Add a type to resolved
     *
     * @param   string type
     * @param   xp.compiler.types.Types resolved
     */
    public function addResolved($type, Types $resolved) {
      $this->resolved[$type]= $resolved;
    }
    
    /**
     * Add an extension method
     *
     * @param   xp.compiler.types.Types type
     * @param   xp.compiler.types.Method method
     */
    public function addExtension(Types $type, xp�compiler�types�Method $method) {
      $this->extensions[$type->name().$method->name]= $method;
    }
    
    /**
     * Add a type import
     *
     * @param   string import fully qualified class name
     * @throws  xp.compiler.types.ResolveException
     */
    public function addTypeImport($import) {
      $p= strrpos($import, '.');
      $this->imports[substr($import, $p+ 1)]= $import;
      $this->resolveType(new TypeName($import));
    }

    /**
     * Add a package import
     *
     * @param   string import fully qualified package name
     * @throws  xp.compiler.types.ResolveException
     */
    public function addPackageImport($import) {
      try {
        $this->packages[]= $this->task->locatePackage($import);
      } catch (ElementNotFoundException $e) {
        throw new ResolveException('Cannot import non-existant package '.$import, 507, $e);
      }
    }
    
    /**
     * Helper method for hasExtension() and getExtension()
     *
     * @param   xp.compiler.types.Types type
     * @param   string name method name
     * @return  string
     */
    protected function lookupExtension(Types $type, $name) {
    
      // Check parent chain
      do {
        $k= $type->name().$name;
        if (isset($this->extensions[$k])) return $k;
      } while ($type= $type->parent());
      
      // Nothing found
      return NULL;
    }
    
    /**
     * Return whether an extension method is available
     *
     * @param   xp.compiler.types.Types type
     * @param   string name method name
     * @return  bool
     */
    public function hasExtension(Types $type, $name) {
      return NULL !== $this->lookupExtension($type, $name);
    }

    /**
     * Get an extension method
     *
     * @param   xp.compiler.types.Types type
     * @param   string name method name
     * @return  xp.compiler.types.Method
     */
    public function getExtension(Types $type, $name) {
      if ($k= $this->lookupExtension($type, $name)) {
        return $this->extensions[$k];
      } else {
        return NULL;
      }
    }

    /**
     * Resolve a static call. Return TRUE if the target is a function
     * (e.g. key()), a xp.compiler.types.Method instance if it's a static 
     * method (Map::key()).
     *
     * @param   string name
     * @return  var
     */
    public function resolveStatic($name) {
      foreach ($this->statics[0] as $lookup => $type) {
        if (TRUE === $type && $this->importer->hasFunction($lookup, $name)) {
          return TRUE;
        } else if ($type instanceof Types && $type->hasMethod($name)) {
          $m= $type->getMethod($name);
          if (Modifiers::isStatic($m->modifiers)) return $m;
        }
      }
      return NULL;
    }
    

    /**
     * Resolve a type name
     *
     * @param   xp.compiler.types.TypeName name
     * @param   bool register
     * @return  xp.compiler.types.Types resolved
     * @throws  xp.compiler.types.ResolveException
     */
    public function resolveType(TypeName $name, $register= TRUE) {
      $cl= ClassLoader::getDefault();
      if ($name->isArray()) {
        $resolved= $this->resolveType($name->arrayComponentType());
        return new TypeReference(new TypeName($resolved->name().'[]'), $resolved->kind());
      } else if (!$name->isClass()) {
        return new TypeReference($name, Types::PRIMITIVE_KIND);
      }
      if ('self' === $name->name || ($this->declarations && $name->name === $this->declarations[0]->name->name)) {
        switch ($decl= $this->declarations[0]) {
          case $decl instanceof ClassNode: 
            $parent= $this->resolveType($decl->parent ? $decl->parent : new TypeName('lang.Object'));
            break;
          case $decl instanceof EnumNode:
            $parent= $this->resolveType($decl->parent ? $decl->parent : new TypeName('lang.Enum'));
            break;
          case $decl instanceof InterfaceNode:
            $parent= NULL;
            break;
        }

        // FIXME: Imports= array() -> Maybe refactor TypeDeclaration to use 
        // not ParseTree but an optimized version?
        return new TypeDeclaration(new ParseTree($this->package, array(), $decl), $parent);
      } else if ('parent' === $name->name && $this->declarations) {
        switch ($decl= $this->declarations[0]) {
          case $decl instanceof ClassNode: 
            return $this->resolveType($decl->parent ? $decl->parent : new TypeName('lang.Object'));
          case $decl instanceof EnumNode:
            return $this->resolveType($decl->parent ? $decl->parent : new TypeName('lang.Enum'));
          default:
            return new TypeReference($name, Types::UNKNOWN_KIND);
        }
      } else if ('xp' === $name->name) {
        return new TypeReference($name, Types::UNKNOWN_KIND);
      } else if (strpos($name->name, '.')) {
        $qualified= $name->name;
      } else if (isset($this->imports[$name->name])) {
        $qualified= $this->imports[$name->name];
      } else {
        $lookup= $this->package
          ? array_merge($this->packages, array($this->package->name))
          : $this->packages
        ;
        try {
          $qualified= $this->task->locateClass($lookup, $name->name);
        } catch (ElementNotFoundException $e) {
          throw new ResolveException('Cannot resolve '.$name->toString(), 423, $e);
        }
      }
      
      
      // Locate class. If the classloader already knows this class,
      // we can simply use this class. TODO: Use specialized 
      // JitClassLoader?
      if (!$this->resolved->containsKey($qualified)) {
        if ($cl->providesClass($qualified)) {
          try {
            $this->resolved[$qualified]= new TypeReflection(XPClass::forName($qualified));
          } catch (Throwable $e) {
            throw new ResolveException('Class loader error for '.$name->toString(), 507, $e);
          }
        } else {
          try {
            $type= $this->task->newSubTask($qualified)->run($this);
          } catch (CompilationException $e) {
            throw new ResolveException('Cannot resolve '.$name->toString(), 424, $e);
          } catch (Throwable $e) {
            throw new ResolveException('Cannot resolve '.$name->toString(), 507, $e);
          }
          $this->resolved[$qualified]= $type;
        }
        $register && $this->used[]= new TypeName($qualified);
      }
      
      return $this->resolved[$qualified];
    }
    
    /**
     * Set type
     *
     * @param   xp.compiler.ast.Node node
     * @param   xp.compiler.types.TypeName type
     */
    public function setType(xp�compiler�ast�Node $node, TypeName $type) {
      $this->types->put($node, $type);
    }
    
    /**
     * Return a type for a given node
     *
     * @param   xp.compiler.ast.Node node
     * @return  xp.compiler.types.TypeName
     */
    public function typeOf(xp�compiler�ast�Node $node) {
      if ($node instanceof ArrayNode) {
        return new TypeName('var[]');       // FIXME: Component type
      } else if ($node instanceof MapNode) {
        return new TypeName('[var:var]');   // FIXME: Component type
      } else if ($node instanceof StringNode) {
        return new TypeName('string');
      } else if ($node instanceof NaturalNode) {
        return new TypeName('int');
      } else if ($node instanceof DecimalNode) {
        return new TypeName('double');
      } else if ($node instanceof NullNode) {
        return new TypeName('lang.Object');
      } else if ($node instanceof BooleanNode) {
        return new TypeName('bool');
      } else if ($node instanceof ComparisonNode) {
        return new TypeName('bool');
      } else if ($node instanceof InstanceCreationNode) {
        return $node->type;
      } else if ($this->types->containsKey($node)) {
        return $this->types[$node];
      }
      return TypeName::$VAR;
    }
  }
?>
