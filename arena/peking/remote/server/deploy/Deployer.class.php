<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'util.log.Logger',
    'remote.server.deploy.DeployException',
    'remote.server.container.StatelessSessionBeanContainer',
    'remote.server.naming.NamingDirectory',
    'remote.server.ContainerInvocationHandler',
    'remote.reflect.InterfaceUtil',
    'util.log.Traceable'
  );

  define('DEPLOY_LOOKUP_KEY',         'lookupName');
  define('DEPLOY_PEERCLASS_KEY',      'peerClass');
  define('DEPLOY_HOMEINTERFACE_KEY',  'homeInterface');

  /**
   * Deployer
   *
   * @purpose  Deployer
   */
  class Deployer extends Object implements Traceable {
    var
      $cat    = NULL;
  
    /**
     * Deploy
     *
     * @param   remote.server.deploy.Deployable deployment
     */
    public function deployBean($deployment) {
      if (is('IncompleteDeployment', $deployment)) {
        throw(new DeployException(
          'Incomplete deployment originating from '.$deployment->origin, 
          $deployment->cause
        ));
      }

      $this->cat && $this->cat->info($this->getClassName(), 'Begin deployment of', $deployment);

      $cl= $deployment->getClassLoader();
      $impl= $cl->loadClass($deployment->getImplementation());
      $interface= $cl->loadClass($deployment->getInterface());

      $directoryName= $deployment->getDirectoryName();

      // Fetch naming directory
      $directory= NamingDirectory::getInstance();

      // Create beanContainer
      // TBI: Check which kind of bean container has to be created
      $beanContainer= StatelessSessionBeanContainer::forClass($impl);
      $this->cat && $beanContainer->setTrace($this->cat);

      // Create invocation handler
      $invocationHandler= new ContainerInvocationHandler();
      $invocationHandler->setContainer($beanContainer);

      // Now bind into directory
      $directory->bind($directoryName, Proxy::newProxyInstance(
        $cl,
        array($interface),
        $invocationHandler
      ));
      
      $this->cat && $this->cat->info($this->getClassName(), 'End deployment of', $impl->getName(), 'with ND entry', $directoryName);

      return $beanContainer;
    }
    
    /**
     * Set a trace for debugging
     *
     * @param   util.log.LogCategory cat
     */
    public function setTrace($cat) { 
      $this->cat= $cat;
    }
  } 
?>
