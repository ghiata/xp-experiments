<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('io.streams.OutputStreamWriter', 'util.profiling.Timer');

  /**
   * (Insert class' description here)
   *
   * @purpose  purpose
   */
  class DefaultListener extends Object {
    protected
      $writer    = NULL,
      $started   = 0,
      $failed    = 0,
      $succeeded = 0,
      $timer     = NULL,
      $failures  = array();
    
    /**
     * (Insert method's description here)
     *
     * @param   io.streams.OutputStreamWriter writer
     */
    public function __construct(OutputStreamWriter $writer) {
      $this->writer= $writer;
      $this->timer= new Timer();
    }

    /**
     * Called when compilation starts
     *
     * @param   io.File src
     */
    public function compilationStarted(File $src) {
      $this->started++;
    }
  
    /**
     * Called when a compilation finishes successfully.
     *
     * @param   io.File src
     * @param   io.File compiled
     */
    public function compilationSucceeded(File $src, File $compiled) {
      $this->writer->write('.');
      $this->succeeded++;
    }

    /**
     * Called when parsing fails
     *
     * @param   io.File src
     * @param   lang.Throwable reason
     */
    public function parsingFailed(File $src, Throwable $reason) {
      $this->writer->write('P');
      $this->failed++;
      $this->failures[$src->getURI()]= $reason;
    }

    /**
     * Called when emitting fails
     *
     * @param   io.File src
     * @param   lang.Throwable reason
     */
    public function emittingFailed(File $src, Throwable $reason) {
      $this->writer->write('E');
      $this->failed++;
      $this->failures[$src->getURI()]= $reason;
    }

    /**
     * Called when compilation fails
     *
     * @param   io.File src
     * @param   lang.Throwable reason
     */
    public function compilationFailed(File $src, Throwable $reason) {
      $this->writer->write('F');
      $this->failed++;
      $this->failures[$src->getURI()]= $reason;
    }

    /**
     * Called when a run starts.
     *
     */
    public function runStarted() {
      $this->failed= $this->succeeded= $this->started= 0;
      $this->writer->write('[');
      $this->timer->start();
    }
    
    /**
     * Called when a test run finishes.
     *
     */
    public function runFinished() {
      $this->timer->stop();
      $this->writer->writeLine(']');
      $this->writer->writeLine();
      
      if ($this->failed > 0) {
        foreach ($this->failures as $uri => $reason) {
          $this->writer->writeLine('* ', basename($uri), ': ', $reason->compoundMessage());
          $this->writer->writeLine();
        }
      }
      
      // Summary
      $this->writer->writeLinef('Done: %d/%d compiled, %d failed', $this->succeeded, $this->started, $this->failed);
      $this->writer->writeLinef(
        'Memory used: %.2f kB (%.2f kB peak)',
        memory_get_usage(TRUE) / 1024,
        memory_get_peak_usage(TRUE) / 1024
      );
      $this->writer->writeLinef(
        'Time taken: %.2f seconds (%.3f avg)',
        $this->timer->elapsedTime(),
        $this->timer->elapsedTime() / $this->started
      );
    }
  }
?>
