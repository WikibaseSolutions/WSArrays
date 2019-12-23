<?php

require_once 'WSArrays.php';

final class ComplexArrayWrapper extends ComplexArray {
    /**
     * @var array
     */
    private $arrays;

    /**
     * @var array
     */
    private $indices;

    /**
     * Name of the current array
     *
     * @var string
     */
    private $array_name;

    /**
     * Create a new ComplexArray class. Equivalent to $class = new ComplexArrayWrapper();.
     *
     * @signature ComplexArrayWrapper newFromVoid();
     * @return ComplexArrayWrapper
     */
    public static function newFromVoid() {
        return new ComplexArrayWrapper();
    }

    /**
     * Set query array to $array_name. Returns false on failure.
     *
     * @signature $this|bool from( string $array_name );
     * @param $array_name
     * @return $this|bool
     */
    public function from($array_name) {
        if(!is_string($array_name)) {
            return false;
        }

        if(!isset($this->arrays[$array_name])) {
            return false;
        }

        $this->reset();
        $this->array_name = $array_name;

        return $this;
    }

    /**
     * Set the index.
     *
     * @param array $indices
     * @return $this
     */
    public function on(array $indices) {
        $this->indices = $indices;

        return $this;
    }

    /**
     * Returns the value of the array.
     *
     * @signature array|bool getArrayValue( array $indices );
     * @return array|bool
     */
    public function get() {
        if(!$this->array_name) {
            return false;
        }

        if(!isset($this->arrays[$this->array_name])) {
            return false;
        }

        $array = $this->arrays[$this->array_name]->getArray();

        if(!$this->indices) {
            return $array;
        }

        foreach($this->indices as $index) {
            if(isset($array[$index])) {
                $array = $array[$index];
            } else {
                return false;
            }
        }

        return $array;
    }

    /**
     * Define a new or overwrite an existing array, or set a value of a sub array, given indices.
     *
     * @signature bool set( mixed $value );
     * @param $value
     * @return bool
     */
    public function set($value) {
        if(!$this->array_name) {
            return false;
        }

        $array = $this->arrays[$this->array_name]->getArray();

        $temp =& $array;
        if(!$this->indices) {
            $temp = $value;

            return true;
        }

        foreach($this->indices as $index) {
            if(!isset($temp[$index])) {
               $temp[$index] = [];
            }

            $temp = $temp[$index];
        }

        $temp = $value;

        $this->arrays[$this->array_name] = new ComplexArray( $array );

        return true;
    }

    public function reset() {
        unset($this->array_name);
        unset($this->indices);

        return $this;
    }

    /**
     * Unsets the array.
     *
     * @signature bool unsetArray();
     * @return bool
     */
    public function unsetArray() {
        if(!$this->array_name || $this->indices) {
            return false;
        }

        unset($this->arrays[$this->array_name]);

        return true;
    }
}