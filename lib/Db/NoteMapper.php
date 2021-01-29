<?php

namespace OCA\Enotes\Db;

use OCP\IDbConnection;
use OCP\AppFramework\Db\QBMapper;
use OCP\AppFramework\Db\Entity;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class NoteMapper extends QBMapper {
	public function __construct(IDbConnection $db) {
		parent::__construct($db, 'enote_note', Note::class);
	}

	public function insert(Entity $note): Entity {
		try {
			return parent::insert($note);
		} catch (UniqueConstraintViolationException $e) {
			// Don't throw an exception if entity is already stored
			return $this->findByHash($note->getHash());
		}
	}

	public function findByHash(string $hash) {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('hash', $qb->createNamedParameter($hash))
			);

		return $this->findEntity($qb);
	}
}
