<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses(
    'lang.apidoc.parser.GenericParser',
    'lang.apidoc.CommentFactory'
  );

  /**
   * Implementation of GenericParser for classes from within
   * the XP framework
   *
   * @see      xp://lang.apidoc.parser.GenericParser
   * @purpose  Parses classes
   */
  class ClassParser extends GenericParser {
    public
      $config   = 'class',
      $comments = array(),
      $defines  = array();
    
    /**
     * Parse
     *
     * @access  public
     * @param   util.log.LogCategory CAT default NULL a log category to print debug to
     * @return  array an associative array containing comments and defines
     */
    public function parse($cat= NULL) {
      $this->comments= array(
        APIDOC_COMMENT_FILE     => array(),
        APIDOC_COMMENT_CLASS    => array(),
        APIDOC_COMMENT_FUNCTION => array()
      );
      $this->defines= array();
      
      if (FALSE === parent::parse($cat)) return FALSE;
      return array(
        'comments' => $this->comments,
        'defines'  => $this->defines
      );
    }
    
    /**
     * Callback function for defines
     *
     * @access  protected
     * @param   string const
     * @param   string val
     */
    protected function setDefine($const, $val) {
      $this->defines[substr($const, 1, -1)]= $val;
    }

    /**
     * Callback function for the "file comment" (this is the comment at
     * the top of the file)
     *
     * @access  protected
     * @param   string str the comment's content
     */
    protected function setFileComment($str) {
      $comment= CommentFactory::factory(APIDOC_COMMENT_FILE);
      $comment->fromString($str);
      $this->comments[APIDOC_COMMENT_FILE]= $comment;
    }
    
    /**
     * Callback function for the "class comment" (this is the comment
     * right above the class declaration)
     *
     * @access  protected
     * @param   string class the class' name
     * @param   string extends what this class extends
     * @param   string str the comment's content
     */
    protected function setClassComment($class, $extends, $str) {
      $comment= CommentFactory::factory(APIDOC_COMMENT_CLASS);
      $comment->fromString($str);
      $comment->setClassName($class);
      $comment->setExtends($extends);
      $this->comments[APIDOC_COMMENT_CLASS]= $comment;
    }
    
    /**
     * Callback function for "function comments" (these are the comments
     * above a function declaration)
     *
     * @access  protected
     * @param   string function the function's name
     * @param   string str the comment's content
     * @param   bool returnsReference default FALSE TRUE when this function returns its value by reference
     */
    protected function setFunctionComment($function, $str, $returnsReference= FALSE) {
      $comment= CommentFactory::factory(APIDOC_COMMENT_FUNCTION);
      $comment->fromString($str);
      $comment->return->reference= $returnsReference;
      $this->comments[APIDOC_COMMENT_FUNCTION][$function]= $comment;
    }
  }
?>
