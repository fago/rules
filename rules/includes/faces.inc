<?php
// $Id$

/**
 * @file Extendable Object Faces API. Provided by the faces module.
 */

/**
 * Interface for extendable objects.
 */
interface FacesExtendableInterface {

  /**
   * Extend the object by a class to implement the given interfaces.
   *
   * @param $interface
   *   The interface name or an array of interface names.
   * @param $class
   *   The extender class implementing the given interfaces.
   * @param $include
   *   An optional array describing the file to include before invoking the
   *   class. The array entries known are 'type', 'module', and 'name'
   *   matching the parameters of module_load_include(). Only 'module' is
   *   required as 'type' defaults to 'inc' and 'name' to NULL.
   */
  public function extendByClass($interface, $class, array $include = array());
  
  /**
   * Extend the object by the given functions to implement the given
   * interface. There has to be an implementation function for each method of
   * the interface.
   *
   * @param $interface
   *   The interface name.
   * @param $methods
   *   An array, where the keys are methods of the given interface and the
   *   values the callback functions to use.
   * @param $includes
   *   An optional array to describe files to include before invoking the
   *   callbacks. You may pass a single array describing one include for all
   *   callbacks or an array of arrays, keyed by the method names. Look at the
   *   extendByClass() $include parameter for more details about how to
   *   describe a single file.
   */
  public function extend($interface, array $methods = array(), array $includes = array());
  
  
  /**
   * Override the implementation of an extended method.
   *
   * @param $methods
   *   An array of methods of the interface, that should be overriden, where
   *   the keys are methods to override and the values the callback functions
   *   to use.
   * @param $includes
   *   An optional array to describe files to include before invoking the
   *   callbacks. You may pass a single array describing one include for all
   *   callbacks or an array of arrays, keyed by the method names. Look at the
   *   extendByClass() $include parameter for more details about how to
   *   describe a single file.
   */
  public function override(array $methods = array(), array $includes = array());
  
  /**
   * Returns whether the object can face as the given interface, thus it returns
   * TRUE if this oject has been extended by an appropriate implementation.
   *
   * @param $interface
   *   Optional. A interface to test for. If it's omitted, all interfaces that
   *   the object can be faced as are returned.
   * @return
   *   Whether the object can face as the interface or an array of interface names.
   */
  public function facesAs($interface = NULL);
  
  /**
   * Destroys all references to created instances so that PHP's garbage
   * collection can do its work. This is needed as PHP's gc has troubles with
   * circular references until PHP < 5.3.
   */
  public function destroy();
  
  /**
   * Calls a method. In contrast to calling a method directly this allows
   * passing arguments by reference. Use this instead of direcly calling
   * __call().
   *
   * @param $name
   *   The method name.
   * @param $arguments
   *   An array of arguments to pass to the method.
   */
  public function call($name, array $arguments = array());
}



interface FacesExtenderInterface {

  /**
   * Constructor.
   * @param $object The extended object.
   */
  function __construct(FacesExtendableInterface $object);
  
}



abstract class FacesExtender implements FacesExtenderInterface {
  
  protected $object;
  
  function __construct(FacesExtendableInterface $object) {
    $this->object = $object;
  }
}



/**
 * Class providing an implementation of FacesExtendableInterface.
 * As the extendable object may have incorporated faces, we extend FacesObjectFace.
 */
abstract class FacesExtendable implements FacesExtendableInterface {

  protected $facesMethods = array();
  protected $faces = array();
  protected $facesClassInstances = array();
  protected $facesIncludes = array();
  static protected $facesIncluded = array();

  /**
   * Wraps calls to module_load_include() to prevent multiple inclusions.
   *
   * @see module_load_include()
   */
  protected static function load_include($args) {
    $args += array('type' => 'inc', 'module' => '', 'name' => NULL);
    $key = implode(':', $args);
    if (!isset(self::$facesIncluded[$key])) {
      self::$facesIncluded[$key] = TRUE;
      module_load_include($args['type'], $args['module'], $args['name']);
    }
  }

  /**
   * Magic method.
   */
  function __call($name, $arguments) {
    if (isset($this->facesMethods[$name])) {
      $method = $this->facesMethods[$name];
      // Include code, if necessary.
      if (isset($this->facesIncludes[$name])) {
        self::load_include($this->facesIncludes[$name]);
        $this->facesIncludes[$name] = NULL;
      }
      // Invoke the callback or extender class.
      if (isset($method[0])) {
        // We pass the object reference and the name of the invoked method.
        array_push($arguments, $this);
        array_push($arguments, $name);
        return call_user_func_array($method[0], $arguments);
      }
      else {
        if (!isset($this->facesClassInstances[$method[1]])) {
          $this->facesClassInstances[$method[1]] = new $method[1]($this);
        }
        return call_user_func_array(array($this->facesClassInstances[$method[1]], $name), $arguments);
      }
    }
    throw new FacesExtendableException("This FacesExtendable doesn't implement the method $name.");
  }

  /**
   * Implementation of FacesExtendableInterface.
   */
  function call($name, array $args = array()) {
    if (method_exists($this, $name)) {
      return call_user_func_array(array($this, $name), $args);
    }
    return $this->__call($name, $args);
  }

  /**
   * Implementation of FacesExtendableInterface.
   */
  public function facesAs($interface = NULL) {
    if (!isset($interface)) {
      return array_values($this->faces);
    }
    return in_array($interface, $this->faces) || $this instanceof $interface;
  }

  /**
   * Implementation of FacesExtendableInterface.
   */
  public function extendByClass($interface, $className, array $includes = array()) {
    $interfaces = is_array($interface) ? $interface : array($interface);
    foreach ($interfaces as $interface) {
      if (!in_array($interface, class_implements($className))) {
        throw new FacesExtendableException("The class " . check_plain($className) . " doesn't implement the interface " . check_plain($interface) . ".");
      }
      $this->faces[$interface] = $interface;
      $face_methods = get_class_methods($interface);
      $this->addIncludes($face_methods, $includes);
      foreach ($face_methods as $method) {
        $this->facesMethods[$method] = array(1 => $className);
      }
    }
  }

  /**
   * Implementation of FacesExtendableInterface.
   */
  public function extend($interface, array $callbacks = array(), array $includes = array()) {
    $face_methods = get_class_methods($interface);
    if (array_diff($face_methods, array_keys($callbacks))) {
      throw new FacesExtendableException("Missing methods for implementing the interface " . check_plain($interface) . ".");
    }
    $this->faces[$interface] = $interface;
    $this->addIncludes($face_methods, $includes);
    foreach ($face_methods as $method) {
      $this->facesMethods[$method] = array(0 => $callbacks[$method]);
    }
  }

  /**
   * Implementation of FacesExtendableInterface.
   */
  public function override(array $callbacks = array(), array $includes = array()) {
    if (array_diff_key($callbacks, $this->facesMethods)) {
      throw new FacesExtendableException("A not implemented method is to be overridden.");
    }
    $this->addIncludes(array_keys($callbacks), $includes);
    foreach ($callbacks as $method => $callback) {
      $this->facesMethods[$method] = array(0 => $callback);
    }
  }

  /**
   * Adds in include files for the given methods while removing any old files.
   * If a single include file is described, it's added for all methods.
   */
  protected function addIncludes($methods, $includes) {
    $includes = isset($includes['module']) && is_string($includes['module']) ? array_fill_keys($methods, $includes) : $includes;
    $this->facesIncludes = $includes + array_diff_key($this->facesIncludes, array_flip($methods));
  }

  public function destroy() {
    // Avoid circular references.
    unset($this->facesClassInstances);
  }
  
  /**
   * Only serialize what is really necessary.
   */
  public function __sleep() {
    $properties = get_object_vars($this);
    unset($properties['facesClassInstances']);
    return array_keys($properties);
  }
}

/**
 * The Exception thrown by the FacesExtendable.
 */
class FacesExtendableException extends ErrorException {
  
}
