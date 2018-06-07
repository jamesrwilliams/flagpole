<?php 

  /**
  * Flag Class
  *
  * Used for creating feature flags.
  *
  * @package   Feature Flags
  * @author    James Williams <james@jamesrwilliams.co.uk>
  * @link      https://github.com/jamesrwilliams/feature-flags
  * @copyright 2018 James Williams
  */
  class Flag {

    private $enforced;
    private $name;
    public $key;
    private $description;

    function __construct($_key, $_name, $_enforced, $_description){

      $this->enforced = $_enforced;
      $this->name = ( $_name ? $_name : '' );
      $this->key = $_key;
      $this->description = $_description;

    }

    /**
     * Display or retrieve the flag key.
     *
     * @param boolean $echo
     * @return string|void Current flag key if $echo is false.
     */
    function get_key( $echo = true ){

      $key = $this->key;

      if( $echo ){

        echo $key;

      } else {

        return $key;

      }

    }

    /**
     * Display or retrieve the flag name.
     *
     * @param boolean $echo
     * @return string|void Current flag key if $echo is false.
     */
    function get_name( $echo = true ){

      $name = $this->name;

      if( $echo ){

        echo $name;

      } else {

        return $name;

      }

    }

    /**
     * Display or retrieve the flag name.
     *
     * @param boolean $echo
     * @return string|void Current flag key if $echo is false.
     */
    function get_description( $echo = true ){

      $description = $this->description;

      if( $echo ){

        echo $description;

      } else {

        return $description;

      }

    }
    
    /**
     * Retrieve the status of a flag's enforced state.
     *
     * @return boolean The status of if a flag is enforced or not.
     */
    function get_enforced(){

      return $this->enforced;

    }

  }