<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('io.streams.InputStream', 'xml.XMLFormatException', 'xml.streams.XmlEvent');

  /**
   * Writes XML in a streaming fashion
   *
   * @ext      iconv
   * @test     xp://testXmlStreamReaderTest
   */
  class XmlStreamReader extends Object {
    protected $stream       = NULL;
    protected $events       = array();
    
    /**
     * Creates a new XML stream writer
     *
     * @param   io.streams.OutputStream stream
     */
    public function __construct(InputStream $stream) {
      $this->stream= $stream;
      $this->parser= xml_parser_create();
      
      // Set callbacks
      xml_set_object($this->parser, $this);
      xml_set_element_handler($this->parser, 'onStartElement', 'onEndElement');
      xml_set_character_data_handler($this->parser, 'onCData');
      xml_set_default_handler($this->parser, 'onDefault');
      xml_set_processing_instruction_handler($this->parser, 'onProcessingInstruction');
    }
    
    protected function onStartElement($parser, $name, $attr) {
      $this->events[]= XmlEvent::$START_ELEMENT;
    }

    protected function onEndElement($parser, $name) {
      $this->events[]= XmlEvent::$END_ELEMENT;
    }

    protected function onCData($parser, $text) {
      $this->events[]= XmlEvent::$CHARACTERS;
    }

    protected function onDefault($parser, $text) {
      $this->events[]= XmlEvent::$COMMENT;
    }
 
    protected function onProcessingInstruction($parser, $text) {
      $this->events[]= XmlEvent::$PROCESSING_INSTRUCTION;
    }
    
    /**
     * Checks whether there are more elements
     *
     * @return  bool
     */
    public function hasNext() {
      return $this->events || $this->stream->available() > 0;
    }
    
    /**
     * Returns next element
     *
     * @return  
     */
    public function next() {
      if (!$this->events) {
        if ($this->stream->available()) {
          $r= xml_parse($this->parser, $this->stream->read(), FALSE);
        } else {
          $r= xml_parse($this->parser, '', TRUE);
        }
        if (!$r) {
          $type= xml_get_error_code($this->parser);
          $line= xml_get_current_line_number($this->parser);
          $column= xml_get_current_column_number($this->parser);
          xml_parser_free($this->parser);
          libxml_clear_errors();
          throw new XMLFormatException(xml_error_string($type), $type, $this->stream->toString(), $line, $column);
        }
        if (!$this->events) return XmlEvent::$END_DOCUMENT;
      }
      return array_shift($this->events);
    }
  }
?>
