<?php

namespace OCA\Enotes\Db;

use OCP\IDbConnection;
use OCP\AppFramework\Db\QBMapper;
use OCP\AppFramework\Db\Entity;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class BookMapper extends QBMapper {

	public function __construct(IDbConnection $db) {
		parent::__construct($db, 'enote_book', Book::class);
	}

	public function insert(Entity $book): Entity {
		try {
			return parent::insert($book);
		} catch (UniqueConstraintViolationException $e) {
			// Don't throw an exception if entity is already stored
			return $this->findByHash($book->getHash());
		}
	}

	/**
	 * Finds all books of the user and attaches the related notes to it
	 *
	 * @param $userId
	 * @return array
	 */
	public function findByUserId($userId) {

		$qb = $this->db->getQueryBuilder();
		$qb->select(
			'b.id as book_id', 'b.title as book_title', 'b.hash as book_hash', 'b.device_id as book_device_id',
			'n.id as note_id', 'n.content as note_content', 'n.type as note_type', 'n.location as note_location', 'n.book_id as note_book_id', 'n.hash as note_hash'
		)
			->from('enote_book', 'b')
			->join('b', 'enote_note', 'n', 'b.id = n.book_id')
			->where(
				$qb->expr()->eq('user_id', $qb->createNamedParameter($userId))
			);
		$cursor = $qb->execute();
		$books = [];

		while ($row = $cursor->fetch()) {
			$bookRow = [];
			$noteRow = [];
			foreach ($row as $key => $value) {
				if (str_starts_with($key, 'book_')) {
					$bookRow[substr($key, 5)] = $value;
				}
				if (str_starts_with($key, 'note_')) {
					$noteRow[substr($key, 5)] = $value;
				}
			}

			if (!key_exists($bookRow['id'], $books)) {
				$books[$bookRow['id']] = Book::fromRow($bookRow);
			}

			$note = Note::fromRow($noteRow);
			$books[$bookRow['id']]->addNote($note);
		}
		$cursor->closeCursor();
		return array_values($books);
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
