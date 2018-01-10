<?php
/**
 * Created by PhpStorm.
 * User: Mateusz "IcyMat" Kolasa
 */

namespace IcyMat\ORM;

use IcyMat\ORM\Exceptions\DatabaseException;
use IcyMat\ORM\Exceptions\Exception;
use IcyMat\ORM\Exceptions\ORMNotFoundException;

class EntityManager {

	/**
	 * @var \PDO
	 */
	private $pdo;

	/**
	 * @var array
	 */
	private $lastInsertedIds = [
		0 => 0
	];

	/**
	 * EntityManager constructor.
	 * @param \PDO $pdo
	 */
	public function __construct(\PDO $pdo) {
		$this->pdo = $pdo;
		$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}

	/**
	 * @param BaseEntity $entity
	 * @throws DatabaseException
	 */
	public function save(BaseEntity $entity) {
		$values = array();

		if($entity->getId() == null) {
			$fields = array();
			$variables = array();
			foreach($entity->toArray() as $field => $value) {
				$fields[] = "`" . $field . "`";
				$variables[] = ':' . $field;
				$values[':' . $field] = $value;
			}

			$this->executeQuery('INSERT INTO `' . $entity->getName() . '` (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $variables) . ')', $values, $entity->getName());
			$class = new \ReflectionClass('IcyMat\ORM\BaseEntity');
			$property = $class->getProperty("id");
			$property->setAccessible(true);
			$property->setValue($entity, $this->lastInsertedIds[$entity->getName()]);
			$property->setAccessible(false);

			return;
		}

		$update = array();
		foreach($entity->toArray() as $field => $value) {
			$update[] = "`" . $field . "` = :" . $field;
			$values[':' . $field] = $value;
		}

		$values[':id'] = $entity->getId();

		$this->executeQuery('UPDATE `' . $entity->getName() . '` SET ' . implode(', ', $update) . ' WHERE id = :id LIMIT 1', $values);
	}

	/**
	 * @param $entityName
	 * @param $id
	 * @return static
	 * @throws DatabaseException
	 * @throws ORMNotFoundException
	 */
	public function find($entityName, $id) {
		/** @var BaseEntity $entity */
		$entity = new $entityName();

		$data = $this->executeQuery('SELECT * FROM `' . $entity->getName() . '` WHERE id = :id LIMIT 1', array(':id' => $id));
		if($data->rowCount() == 0) {
			throw new ORMNotFoundException('`' . $entityName . '`` with id = ' . $id . ' not found');
		}

		$entity->setData($data->fetch(\PDO::FETCH_ASSOC));

		return $entity;
	}

	/**
	 * @param string $entityName
	 * @return ArrayCollection
	 * @throws DatabaseException
	 * @throws ORMNotFoundException
	 */
	public function findAll($entityName) {
		/** @var BaseEntity $entity */
		$entity = new $entityName();

		$data = $this->executeQuery('SELECT * FROM `' . $entity->getName() . '`');
		if($data->rowCount() == 0) {
			throw new ORMNotFoundException('Any `' . $entityName . '` not found');
		}

		$arr = new ArrayCollection();

		foreach($data->fetchAll(\PDO::FETCH_ASSOC) as $entityData) {
			/** @var BaseEntity $object */
			$object = new $entityName();
			$object->setData($entityData);
			$arr->add($object);
		}

		return $arr;
	}

	/**
	 * @param $entityName
	 * @param array $conditions
	 * @param array $variables
	 * @param string $parameter
	 * @return BaseEntity
	 * @throws DatabaseException
	 * @throws Exception
	 * @throws ORMNotFoundException
	 */
	public function findOneBy($entityName, array $conditions = array(), array $variables = array(), $parameter = 'AND') {
		/** @var BaseEntity $entity */
		$entity = new $entityName();

		$queryData = [];
		$prepareQuery = [];

		if(!empty($conditions) && count($conditions)) {
			if($this->isAssocArray($conditions)) {
				foreach($conditions as $key => $val) {
					if($val !== null) {
						$queryData[':' . $key] = $val;
						$prepareQuery[] = '`' . $key . '` = :' . $key;
					} else {
						$prepareQuery[] = '`' . $key . '` IS NULL';
					}
				}
			} else {
				foreach($conditions as $key => $val) {
					if($variables[$key] !== null) {
						$queryData[':' . $val] = $variables[$key];
						$prepareQuery[] = '`' . $val . '` = :' . $val;
					} else {
						$prepareQuery[] = '`' . $val . '` IS NULL';
					}
				}
			}
		}

		$where = count($prepareQuery) == 0 ? '' : ' WHERE (' . implode(') ' . $parameter . ' (', $prepareQuery) . ')';

		$data = $this->executeQuery('SELECT * FROM ' . $entity->getName() . $where, $queryData);
		if($data->rowCount() == 0) {
			throw new ORMNotFoundException('Any `' . $entityName . '` not found');
		}

		$entity->setData($data->fetch(\PDO::FETCH_ASSOC));

		return $entity;
	}

	/**
	 * @param string $entityName
	 * @param array $conditions
	 * @param array $variables
	 * @param string $parameter
	 * @return ArrayCollection
	 * @throws DatabaseException
	 * @throws ORMNotFoundException
	 */
	public function where($entityName, array $conditions = array(), array $variables = array(), $parameter = 'AND') {
		/** @var BaseEntity $entity */
		$entity = new $entityName();

		$queryData = array();
		$prepareQuery = array();

		if(!empty($conditions) && count($conditions)) {
			if($this->isAssocArray($conditions)) {
				foreach($conditions as $key => $val) {
					if($val !== null) {
						$queryData[':' . $key] = $val;
						$prepareQuery[] = '`' . $key . '` = :' . $key;
					} else {
						$prepareQuery[] = '`' . $key . '` IS NULL';
					}
				}
			} else {
				foreach($conditions as $key => $val) {
					if($variables[$key] !== null) {
						$queryData[':' . $val] = $variables[$key];
						$prepareQuery[] = '`' . $val . '` = :' . $val;
					} else {
						$prepareQuery[] = '`' . $val . '` IS NULL';
					}
				}
			}
		}

		$where = count($prepareQuery) == 0 ? '' : ' WHERE (' . implode(') ' . $parameter . ' (', $prepareQuery) . ')';

		$data = $this->executeQuery('SELECT * FROM `' . $entity->getName() . '`' . $where, $queryData);
		if($data->rowCount() == 0) {
			throw new ORMNotFoundException('Any `' . $entityName . '`` not found');
		}

		$arr = new ArrayCollection();

		foreach($data->fetchAll(\PDO::FETCH_ASSOC) as $entityData) {
			/** @var BaseEntity $object */
			$object = new $entityName();
			$object->setData($entityData);
			$arr->add($object);
		}

		return $arr;
	}

	/**
	 * @param BaseEntity $entity
	 * @throws Exception
	 * @throws DatabaseException
	 */
	final public function remove(BaseEntity $entity) {
		if($entity->getId() == null) {
			throw new Exception('Cannot remove not saved entity');
		}

		$this->executeQuery('DELETE FROM `' . $entity->getName() . '` WHERE id = :id LIMIT 1', array(':id' => $entity->getId()));
	}

	/**
	 * @param array $arr
	 * @return bool
	 */
	private function isAssocArray(array $arr) {
		if (array() === $arr) return false;

		return array_keys($arr) !== range(0, count($arr) - 1);
	}

	/**
	 * @param $query
	 * @param array $parameters
	 * @return \PDOStatement
	 * @throws DatabaseException
	 */
	private function executeQuery($query, array $parameters = array(), $tableName = 0) {
		$this->pdo->beginTransaction();

		try {
			$query = $this->pdo->prepare($query);
			$query->execute($parameters);
		} catch(\PDOException $e) {
			$this->pdo->rollBack();
			throw new DatabaseException($e->getMessage());
		}

		$this->lastInsertedIds[$tableName] = $this->pdo->lastInsertId();
		$this->pdo->commit();

		return $query;
	}
}