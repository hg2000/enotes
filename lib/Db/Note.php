<?php
declare(strict_types=1);

namespace OCA\Enotes\Db;

use OCP\AppFramework\Db\Entity;

class Note extends Entity implements \JsonSerializable {

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var string
	 */
	protected $location;

	/**
	 * @var string
	 */
	protected $content;

	/**
	 * @var int
	 */
	protected $bookId;

	/**
	 * @var string
	 */
	protected $hash;

	public function __toString() {
		return $this->hash;
	}

	public function __construct() {
		$this->addType('id', 'integer');
		$this->addType('type', 'string');
		$this->addType('location', 'string');
		$this->addType('content', 'text');
		$this->addType('bookId', 'integer');
		$this->addType('hash', 'string');
	}

	/**
	 * @param string $type
	 * @return Note
	 */
	public function setType(string $type) {
		parent::setType($type);
		$this->updateHash();
	}

	/**
	 * @param string $location
	 * @return Note
	 */
	public function setLocation(string $location) {
		parent::setLocation($location);
		$this->updateHash();
	}

	/**
	 * @param string $content
	 * @return Note
	 */
	public function setContent(string $content) {
		parent::setContent($content);
		$this->updateHash();
	}

	/**
	 * @param string $bookId
	 * @return Note
	 */
	public function setBookId(int $bookId) {
		parent::setBookId($bookId);
		$this->updateHash();
	}

	public function updateHash() {
		if ($this->getBookId() && $this->getLocation() && $this->getContent() && $this->getType()) {
			$this->setHash(hash('sha256',
				$this->getBookId() .
				$this->getLocation() .
				$this->getContent() .
				$this->getType()));
		}
	}

	public function jsonSerialize() {
		$obj = new \StdClass();
		$obj->type = $this->getType();
		$obj->content = $this->getContent();
		$obj->bookId = $this->getBookId();
		$obj->location = $this->getLocation();
		return $obj;
	}
}
