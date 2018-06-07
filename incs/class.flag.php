<?php 

  class Flag {

    private $status;
    private $name;
    private $key;
    private $description;

    function __construct($_key, $_name, $_status, $_description){

      $this->status = $_status;
      $this->name = ( $_name ? $_name : '' );
      $this->key = $_key;
      $this->description = $_description;

    }

    function key( $echo = true ){

      $key = $this->key;

      if( $echo ){

        echo $key;

      } else {

        return $key;

      }

    }

    function name( $echo = true ){

      $name = $this->name;

      if( $echo ){

        echo $name;

      } else {

        return $name;

      }

    }

    function status( $echo = true ){

      $status = $this->status;

      if( $echo ){

        echo $status;

      } else {

        return $status;

      }

    }

    function description( $echo = true ){

      $description = $this->description;

      if( $echo ){

        echo $description;

      } else {

        return $description;

      }

    }

  }