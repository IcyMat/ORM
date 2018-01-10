<?php
/**
 * Created by PhpStorm.
 * User: Mateusz "IcyMat" Kolasa
 */

namespace IcyMat\ORM;

class ORM {

	/**
	 * @var EntityManager
	 */
	private $em;

	/**
	 * @var \PDO
	 */
	private $pdo;

	/**
	 * @param \PDO $pdo
	 * @return ORM
	 */
	public function initializeEntityManager(\PDO $pdo) {
		$this->pdo = $pdo;
		$this->em = new EntityManager($this->pdo);

		return $this;
	}

	/**
	 * @return EntityManager
	 */
	public function getEntityManager() {
		return $this->em;
	}

	/**
	 * @return \PDO
	 */
	public function getPDO() {
		return $this->pdo;
	}
}