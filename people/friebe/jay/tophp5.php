<?php
  require('lang.base.php');
  uses(
    'net.xp_framework.tools.vm.Parser',
    'net.xp_framework.tools.vm.Lexer',
    'net.xp_framework.tools.vm.emit.php5.Php5Emitter',
    'util.cmd.Console', 
    'io.File', 
    'io.FileUtil',
    'util.cmd.ParamString'
  );
  define('MODIFIER_NATIVE', 8);   // See lang.XPClass
  
  // {{{ compile
  $p= &new ParamString();
  $in= $p->value(1);
  
  $lexer= &new Lexer(file_get_contents($in), $in);
  $out= &new File($p->value('out', 'o', str_replace('.xp', '.php5', $in)));
  
  $parser= &new Parser($lexer);
  $nodes= $parser->yyparse($lexer);
  
  if ($parser->hasErrors()) {
    Console::writeLine('!!! Errors have occured');
    foreach ($parser->getErrors() as $error) {
      Console::writeLine('- ', $error->toString());
    }
    exit(1);
  }
  
  // Dump AST if specified
  $p->exists('ast') && Console::writeLine(VNode::stringOf($nodes));
  
  $emitter= &new Php5Emitter();
  $emitter->emitAll($nodes);
  
  try(); {
    FileUtil::setContents($out, $emitter->getResult());
  } if (catch('IOException', $e)) {
    $e->printStackTrace();
    exit(-1);
  }
  
  Console::writeLine('---> ', $out->getURI());
  // }}}
?>
