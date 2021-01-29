<?php

namespace OCA\Enotes\Db;

use OCP\AppFramework\Db\Entity;

class Book extends Entity implements \JsonSerializable {

	/**
	 * Title of the book
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * Id of the device where the book is storeds
	 *
	 * @var string
	 */
	protected $deviceId;

	/**
	 * Id of the book owner
	 *
	 * @var string
	 */
	protected $userId;

	/**
	 * Hash to ensure uniqeness of the entity
	 *
	 * @var string
	 */
	protected $hash;

	/**
	 * Extracted notes from the book
	 *
	 * @var array
	 */
	protected $notes;

	public function __toString() {
		return $this->hash;
	}

	public function __construct() {
		$this->addType('id', 'integer');
		$this->addType('title', 'string');
		$this->addType('deviceId', 'string');
		$this->addType('userId', 'string');
		$this->addType('hash', 'string');
	}

	public function setTitle(string $title) {
		parent::setTitle($title);
		$this->updateHash();
	}

	public function setDeviceId(string $deviceId) {
		parent::setDeviceId($deviceId);
		$this->updateHash();
	}

	public function setUserId(string $userId) {
		parent::setUserId($userId);
		$this->updateHash();
	}

	public function getNotes(): array {
		return $this->notes;
	}

	public function updateHash(): bool {
		if ($this->getTitle() && $this->getDeviceId() && $this->getUserId()) {
			$this->setHash(
				hash('sha256',
					$this->getTitle() . $this->getDeviceId() . $this->getUserId()
				)
			);
			return true;
		}
		return false;
	}

	/**
	 * @param array $notes
	 * @return Package
	 */
	public function setNotes(array $notes): Book {
		$this->notes = $notes;
		return $this;
	}

	public function addNote(Note $note) {

		$this->notes[] = $note;
	}
	public function jsonSerialize() {
		return [
			'id' => $this->getId(),
			'title' => $this->getTitle(),
			'notes' => $this->getNotes()
		];
	}
}
