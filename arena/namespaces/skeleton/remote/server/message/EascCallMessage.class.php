<?php
/* This class is part of the XP framework
 *
 * $Id: EascCallMessage.class.php 9302 2007-01-16 17:01:53Z kiesel $ 
 */

  namespace remote::server::message;

  uses(
    'remote.server.message.EascMessage',
    'remote.server.RemoteObjectMap',
    'remote.protocol.SerializedData'
  );

  /**
   * EASC call message
   *
   * @purpose  Call message
   */
  class EascCallMessage extends EascMessage {

    /**
     * Get type of message
     *
     * @return  int
     */
    public function getType() {
      return REMOTE_MSG_CALL;
    }
    
    /**
     * Handle message
     *
     * @param   remote.server.EASCProtocol protocol
     * @return  mixed data
     */
    public function handle($protocol, $data) {
      $oid= unpack('Nzero/Noid', substr($data, 0, 8));
      $p= $protocol->context[remote::server::RemoteObjectMap::CTX_KEY]->getByOid($oid['oid']);

      $offset= 8;
      $method= $protocol->readString($data, $offset);
      
      $offset+= 2;  // ?
      $args= $protocol->serializer->valueOf(new remote::protocol::SerializedData($protocol->readString($data, $offset)), $protocol->context);
      $this->setValue(call_user_func_array(array($p, $method), $args->values));
    }
  }
?>
