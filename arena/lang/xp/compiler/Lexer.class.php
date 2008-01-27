<?php
/* This class is part of the XP framework's experiments
 *
 * $Id$ 
 */

  uses('text.StringTokenizer', 'text.parser.generic.AbstractLexer');
  
  $package= 'xp.compiler';

  /**
   * Lexer for XP language
   *
   * @see      xp://text.parser.generic.AbstractLexer
   * @purpose  Lexer
   */
  class xp�compiler�Lexer extends AbstractLexer {
    protected static
      $keywords  = array(
        'public'        => TOKEN_T_PUBLIC,
        'private'       => TOKEN_T_PRIVATE,
        'protected'     => TOKEN_T_PROTECTED,
        'static'        => TOKEN_T_STATIC,
        'final'         => TOKEN_T_FINAL,
        'abstract'      => TOKEN_T_ABSTRACT,
        'inline'        => TOKEN_T_INLINE,
        
        'package'       => TOKEN_T_PACKAGE,
        'import'        => TOKEN_T_IMPORT,
        'class'         => TOKEN_T_CLASS,
        'interface'     => TOKEN_T_INTERFACE,
        'enum'          => TOKEN_T_ENUM,
        'extends'       => TOKEN_T_EXTENDS,
        'implements'    => TOKEN_T_IMPLEMENTS,

        'operator'      => TOKEN_T_OPERATOR,
        'throws'        => TOKEN_T_THROWS,

        'property'      => TOKEN_T_PROPERTY,

        'throw'         => TOKEN_T_THROW,
        'try'           => TOKEN_T_TRY,
        'catch'         => TOKEN_T_CATCH,
        'finally'       => TOKEN_T_FINALLY,
        
        'return'        => TOKEN_T_RETURN,
        'new'           => TOKEN_T_NEW,
        
        'for'           => TOKEN_T_FOR,
        'foreach'       => TOKEN_T_FOREACH,
        'as'            => TOKEN_T_AS,
        'do'            => TOKEN_T_DO,
        'while'         => TOKEN_T_WHILE,
        'break'         => TOKEN_T_BREAK,
        'continue'      => TOKEN_T_CONTINUE,

        'if'            => TOKEN_T_IF,
        'else'          => TOKEN_T_ELSE,
        'switch'        => TOKEN_T_SWITCH,
        'case'          => TOKEN_T_CASE,
        'default'       => TOKEN_T_DEFAULT,
      );

    protected static
      $lookahead= array(
        '-' => array('->' => TOKEN_T_OBJECT_OPERATOR, '-=' => TOKEN_T_SUB_EQUAL, '--' => TOKEN_T_DEC),
        '>' => array('>=' => TOKEN_T_GE),
        '<' => array('<=' => TOKEN_T_SE),
        '+' => array('+=' => TOKEN_T_ADD_EQUAL, '++' => TOKEN_T_INC),
        '*' => array('*=' => TOKEN_T_MUL_EQUAL),
        '/' => array('/=' => TOKEN_T_DIV_EQUAL),
        '%' => array('%=' => TOKEN_T_MOD_EQUAL),
        '=' => array('==' => TOKEN_T_EQUALS, '=>' => TOKEN_T_DOUBLE_ARROW),
        '!' => array('!=' => TOKEN_T_NOT_EQUALS),
        ':' => array('::' => TOKEN_T_DOUBLE_COLON),
        '|' => array('||' => TOKEN_T_BOOLEAN_OR),
        '&' => array('&&' => TOKEN_T_BOOLEAN_AND),
      );

    const 
      DELIMITERS = " |&?!.:;,@%~=<>(){}[]#+-*/\"'\r\n\t";
    
          
    private
      $ahead = NULL;

    /**
     * Constructor
     *
     * @param   string input
     * @param   string source
     */
    public function __construct($input, $source) {
      $this->tokenizer= new StringTokenizer($input."\0", self::DELIMITERS, TRUE);
      $this->fileName= $source;
      $this->position= array(1, 1);   // Y, X
    }

    /**
     * Create a new node 
     *
     * @param   xp.compiler.ast.Node
     * @return  xp.compiler.ast.Node
     */
    public function create($n) {
      $n->position= $this->position;
      return $n;
    }
  
    /**
     * Advance this 
     *
     * @return  bool
     */
    public function advance() {
      do {
        if ($this->ahead) {
          $token= $this->ahead;
          $this->ahead= NULL;
        } else {
          $token= $this->tokenizer->nextToken(self::DELIMITERS);
        }
        
        // Check for whitespace
        if (FALSE !== strpos(" \n\r\t", $token)) {
          $l= substr_count($token, "\n");
          $this->position[1]= strlen($token) + ($l ? 1 : $this->position[1]);
          $this->position[0]+= $l;
          continue;
        }
        
        $this->position[1]+= strlen($this->value);
        if ("'" === $token{0} || '"' === $token{0}) {
          $this->token= TOKEN_T_STRING;
          $this->value= '';
          do {
            if ($token{0} === ($t= $this->tokenizer->nextToken($token{0}))) {
              // Empty string, e.g. "" or ''
              break;
            }
            $this->value.= $t;
            if ('\\' === $this->value{strlen($this->value)- 1}) {
              $this->value= substr($this->value, 0, -1).$this->tokenizer->nextToken($token{0});
              continue;
            } 
            $this->tokenizer->nextToken($token{0});
            break;
          } while ($this->tokenizer->hasMoreTokens());
        } else if ('$' === $token{0}) {
          $this->token= TOKEN_T_VARIABLE;
          $this->value= $token;
        } else if (isset(self::$keywords[$token])) {
          $this->token= self::$keywords[$token];
          $this->value= $token;
        } else if ('/' === $token{0}) {
          $ahead= $this->tokenizer->nextToken(self::DELIMITERS);
          if ('/' === $ahead) {           // Single-line comment
            $this->tokenizer->nextToken("\n");
            $this->position[1]= 1;
            $this->position[0]++;
            continue;
          } else if ('*' === $ahead) {    // Multi-line comment
            do { 
              $t= $this->tokenizer->nextToken('/'); 
              $l= substr_count($t, "\n");
              $this->position[1]= strlen($t) + ($l ? 1 : $this->position[1]);
              $this->position[0]+= $l;
            } while ('*' !== $t{strlen($t)- 1});
            $this->tokenizer->nextToken('/');
            continue;
          } else {
            $this->token= ord($token);
            $this->value= $token;
            $this->ahead= $ahead;
          }
        } else if (isset(self::$lookahead[$token])) {
          $ahead= $this->tokenizer->nextToken(self::DELIMITERS);
          $combined= $token.$ahead;
          if (isset(self::$lookahead[$token][$combined])) {
            $this->token= self::$lookahead[$token][$combined];
            $this->value= $combined;
          } else {
            $this->token= ord($token);
            $this->value= $token;
            $this->ahead= $ahead;
          }
        } else if (FALSE !== strpos(self::DELIMITERS, $token) && 1 == strlen($token)) {
          $this->token= ord($token);
          $this->value= $token;
        } else if (ctype_digit($token)) {
          $ahead= $this->tokenizer->nextToken(self::DELIMITERS);
          if ('.' === $ahead{0}) {
            $decimal= $this->tokenizer->nextToken(self::DELIMITERS);
            if (!ctype_digit($decimal)) {
              throw new FormatException('Illegal decimal number "'.$token.$ahead.$decimal.'"');
            }
            $this->token= TOKEN_T_DECIMAL;
            $this->value= $token.$ahead.$decimal;
          } else {
            $this->token= TOKEN_T_NUMBER;
            $this->value= $token;
            $this->ahead= $ahead;
          }
        } else if ('0' === $token{0} && 'x' === @$token{1}) {
          if (!ctype_xdigit(substr($token, 2))) {
            throw new FormatException('Illegal hex number "'.$token.'"');
          }
          $this->token= TOKEN_T_NUMBER;
          $this->value= $token;
        } else {
          $this->token= TOKEN_T_WORD;
          $this->value= $token;
        }
        
        break;
      } while (1);
      
      // fprintf(STDERR, "@ %d,%d: %d `%s`\n", $this->position[0], $this->position[1], $this->token, $this->value);
      return $this->tokenizer->hasMoreTokens();
    }
  }
?>