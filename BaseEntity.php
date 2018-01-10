<?php
/**
 * Created by PhpStorm.
 * User: Mateusz "IcyMat" Kolasa
 */

namespace IcyMat\ORM;

use IcyMat\ORM\Exceptions\Exception;

abstract class BaseEntity {
	/**
	 * @var string
	 */
	protected static $name;

	/**
	 * @var array
	 */
	protected $fields = array();

	/**
	 * @var null|int
	 */
	private $id = null;

	/**
	 * @var array
	 */
	private $data = array();

	/**
	 * @var bool
	 */
	private $isSet = false;

	/**
	 * BaseEntity setup.
	 */
	final private function setUp() {
		if($this->isSet) return;

		$this->isSet = true;

		foreach ($this->fields as $field) {
			$this->data[$field] = null;
		}
	}

	/**
	 * @param array $data
	 * @throws Exception
	 */
	final public function setData(array $data) {
		$this->setUp();

		foreach($data as $field => $value) {
			if($field == 'id') {
				if($this->id != null) {
					throw new Exception('You cannot override ID');
				}

				$this->id = $value;
				continue;
			}

			if(!in_array($field, $this->fields))
				throw new Exception('Field ' . $field . ' does not exists on ' . static::$name);

			$this->data[$field] = $value;
		}
	}

	/**
	 * @return array $data
	 */
	public function toArray() {
		$this->setUp();

		return $this->data;
	}

	/**
	 * @return string $name
	 */
	final public function getName() {
		$this->setUp();

		return static::$name;
	}
	
	final public function getId() {
		$this->setUp();

		return $this->id;
	}

	/**
	 * @param string $field
	 * @return mixed
	 * @throws Exception
	 */
	final public function get($field) {
		$this->setUp();

		if(!in_array($field, $this->fields) || !array_key_exists($field, $this->data)) {
			throw new Exception('Field ' . $field . ' does not exists in ' . static::$name);
		}

		return $this->data[$field];
	}

	/**
	 * @param string $field
	 * @param mixed $value
	 * @return $this
	 * @throws Exception
	 */
	final public function set($field, $value) {
		$this->setUp();
		
		if(!in_array($field, $this->fields) || !array_key_exists($field, $this->data)) {
			throw new Exception('Field ' . $field . ' does not exists in ' . static::$name);
		}

		$this->data[$field] = $value;

		return $this;
	}
}
