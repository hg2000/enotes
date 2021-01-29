<?php
declare(strict_types=1);

namespace OCA\Enotes\Service;

use OCA\Enotes\Db\BookMapper;
use OCA\Enotes\Db\NoteMapper;
use OCA\Enotes\Db\Note;
use OCA\Enotes\Db\Book;

class NoteService {

	/**
	 * @var BookMapper
	 */
	protected $bookMapper;

	/**
	 * @var string
	 */
	protected $currentUserId;

	public function __construct(
		BookMapper $bookMapper,
		NoteMapper $noteMapper,
		?string $UserId
	) {
		$this->bookMapper = $bookMapper;
		$this->noteMapper = $noteMapper;
		$this->currentUserId = $UserId;
	}

	public function parseCsv(string $csv, string $deviceId): Book {

		$titleCell = [1, 0];
		$noteRowStart = 8;
		$noteTypeCol = 0;
		$positionCol = 1;
		$noteCol = 3;

		$separator = ',';
		$enclosure = '\"';

		$rows = explode(PHP_EOL, $csv);
		$rows = array_map(function ($r) {
			return str_getcsv($r);

		}, $rows);

		$title = $rows[$titleCell[0]][$titleCell[1]];
		$book = new Book();
		$book->setDeviceId($deviceId);
		$book->setTitle($title);
		$book->setUserId($this->currentUserId);
		$book = $this->bookMapper->insert($book);
		$noteRows = array_slice($rows, $noteRowStart);
		$notes = [];

		foreach ($noteRows as $row) {
			$note = new Note();
			$note->setContent((string)$row[$noteCol] ?? '');
			$note->setType((string)$row[$noteTypeCol] ?? '');
			$note->setLocation((string)$row[$positionCol] ?? '');
			$note->setBookId($book->getId());

			if ($note->getContent()) {
				$this->noteMapper->insert($note);
				$notes[] = $note;
			}
		}
		$book->setNotes($notes);
		return $book;
	}

	/**
	 * Removes duplicate notes
	 * @param array $books
	 * @return array
	 */
	public function removeDuplicates(array $books): array {

		$booksResult = [];
		foreach ($books as $book) {
			if (array_key_exists($book->getHash(), $booksResult)) {
				$notes1 = $booksResult[$book->getHash()]->getNotes();
				$notes2 = $book->getNotes();
				$notes = array_unique(array_merge($notes1, $notes2));
				$booksResult[$book->getHash()]->setNotes(array_unique(array_merge($notes1, $book->getNotes())));
			} else
				$booksResult[$book->getHash()] = $book;
		}
		return $booksResult;
	}
}
