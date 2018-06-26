<?php
/**
 * Created by PhpStorm.
 * User: Mateusz "IcyMat" Kolasa
 */

namespace IcyMat\ORM;

class ArrayCollection implements \Iterator {

    /**
     * @var array
     */
    protected $array;

    /**
     * @var array
     */
    protected $keys = [];

    /**
     * @var int
     */
    private $iteratrionPosition = 0;

    /**
     * ArrayCollection constructor.
     * @param array $array
     */
    public function __construct(array $array = array()) {
        $this->array = $this->child($array);
    }

    /**
     * @param $array
     * @return array
     */
    private function child($array) {
        if(!empty($array))
            foreach($array as $index => $value) {
                if(is_array($value)) {
                    $array[$index] = new self($value);
                }
            }

        return $array;
    }

    /**
     * @return string
     */
    public function __toString() {
        return '<pre>'.print_r($this->array, true).'</pre>';
    }

    /**
     * @param $index
     * @return mixed|null
     */
    public function get($index) {
        if(array_key_exists($index, $this->array))
            return $this->array[$index];
        else
            return null;
    }

    /**
     * @param $index
     * @param $value
     * @return $this
     */
    public function set($index, $value) {
        $this->array[$index] = $value;
        $this->keys[] = $index;

        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function add($value) {
        $this->array[] = $value;
        $this->keys[] = count($this->array) - 1;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEmpty() {
        return empty($this->array);
    }

    /**
     * @return int
     */
    public function count() {
        return count($this->array);
    }

    /**
     * @return array
     */
    public function toArray() {
        return $this->array;
    }

    /**
     * @return mixed
     */
    public function current()
    {
        $key = $this->keys[$this->iteratrionPosition];

        return $this->array[$key];
    }

    /**
     * Get next key
     *
     * @return void
     */
    public function next()
    {
        $this->iteratrionPosition++;
    }

    /**
     * @return mixed
     */
    public function key()
    {
        return $this->keys[$this->iteratrionPosition];
    }

    /**
     * @return bool
     */
    public function valid()
    {
        $key = $this->keys[$this->iteratrionPosition];

        return isset($this->array[$key]);
    }

    /**
     * Reset position after iteration finished.
     *
     * @return void
     */
    public function rewind()
    {
        $this->iteratrionPosition = 0;
    }
}
