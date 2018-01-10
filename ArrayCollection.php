<?php
/**
 * Created by PhpStorm.
 * User: Mateusz "IcyMat" Kolasa
 */

namespace IcyMat\ORM;

class ArrayCollection {

	/**
	 * @var array
	 */
	protected $array;

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

		return $this;
	}

	/**
	 * @param $value
	 * @return $this
	 */
	public function add($value) {
		$this->array[] = $value;

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
	
}