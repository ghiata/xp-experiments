<?php
  uses(
    'rdbms.sybasex.token.TdsToken',
    'rdbms.sybasex.TdsColumn',
    'rdbms.sybasex.TdsType'
  );

  $package= 'rdbms.sybasex.token';
  class rdbms�sybasex�token�TdsResultToken extends rdbms�sybasex�token�TdsToken {
    public function handle() {
      $headerSize= $this->readLength();
      $numberOfColumns= $this->readSmallInt();

      $this->cat && $this->cat->debug('Have', $numberOfColumns, 'columns');
      $this->context->newColumns();
      
      for ($i= 0; $i < $numberOfColumns; $i++) {
        $name= $this->data->read($this->readByte()); // first byte is name length
        $flags= $this->readByte();
        $userType= $this->readLong();
        $columnType= $this->readByte();
        $type= TdsType::byOrdinal($columnType);

        $this->cat && $this->cat->debug('Column', $name,
          'flags=', $flags,
          'usertype=', $userType,
          'columnType=', $columnType,
          'type=', $type,
          'size=', $type->size()
        );

        $columnSize= 0;
        switch ($type->size()) {
          case 4: {
            // TODO: Read table name for SYBTEXT and SYBIMAGE
            $columnSize= $this->readLong();
            break;
          }

          case 5: {
            $columnSize= $this->readLong();
            break;
          }

          case 2: {
            $columnSize= $this->readSmallInt();
            break;
          }

          case 1: {
            $columnSize= $this->readByte();
            break;
          }

          case 0: {
            $columnSize= $type->fixedSize();
            break;
          }
        }

        $columnPrecision= NULL;
        if ($type === TdsType::$SYBNUMERIC || $type === TdsType::$SYBDECIMAL) {
          $columnPrecision= $this->data->readByte();
          $columnSize= $this->data->readByte();

          $this->cat && $this->cat->debug('Column\'s precision=', $columnPrecision, ', length=', $columnSize);
        }

        $this->cat && $this->cat->debug('Column\'s size is', $columnSize, 'bytes');
        
        // Eat locale information
        $this->data->read($this->readByte()); // first byte is length

        // Store this in current context
        $this->context->addColumn(new TdsColumn(
          $name,
          $flags,
          $userType,
          $type,
          $columnSize,
          $columnPrecision
        ));
      }
      
      // Mark retrieving metadata has finished, now the result can be filled.
      $this->context->sealColumns();
    }
  }

?>