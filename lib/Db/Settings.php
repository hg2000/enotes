<?php

namespace OCA\Enotes\Db;

use OCA\Enotes\Service\MailService;
use OCP\AppFramework\Db\Entity;

class Settings extends Entity implements \JsonSerializable {

	/**
	 * @var
	 */
	protected $userId;

	/**
	 * @var MailService
	 */
	protected $mailService;

	/**
	 * @var string
	 */
	protected $mailAccounts;

	public function setMailAccounts($accounts) {

		if (is_array($accounts)) {
			$this->mailAccounts = $this->serialize($accounts);
		} else {
			$this->mailAccounts = $accounts;
		}
		$this->markFieldUpdated('mailAccounts');
	}

	public function getMailAccountsArray(): array {

		if (!$this->mailAccounts) {
			return [];
		}
		return $this->unserialize($this->mailAccounts);
	}

	public function setTypes($types) {

		if (is_array($types)) {
			$this->types = $this->serialize($types);
		} else {
			$this->types = $types;
		}
		$this->markFieldUpdated('types');
	}

	public function getTypesArray(): array {

		if (!$this->types) {
			return [];
		}
		return $this->unserialize($this->types);
	}

	/**
	 * Turns array into list
	 *
	 * @param array $array
	 * @return string
	 */
	protected function serialize(array $array): string {

		return serialize($array);
	}

	/**
	 * Turns list into array
	 *
	 * @param string $string
	 * @return array
	 */
	protected function unserialize(string $string): array {
		return unserialize($string);
	}

	/**
	 * Adjusts the settings state so that it fits to the default settings
	 *
	 * @param array $defaultSettings
	 */
	public function mapWithDefaultSettings(array $defaultSettings) {

		$mailAccounts = $this->getMailAccountsArray();
		if (empty($mailAccounts)) {
			$this->setMailAccounts($defaultSettings['mailAccounts']);
		} else {
			$resultMailAccounts = [];
			foreach ($defaultSettings['mailAccounts'] as $defaultMailAccount) {
				foreach ($mailAccounts as $mailAccount) {
					if ($defaultMailAccount['email'] === $mailAccount['email']) {
						$defaultMailAccount['active'] = $mailAccount['active'];
						break;
					}
				}
				$resultMailAccounts[] = $defaultMailAccount;
			}
			$this->setMailAccounts($resultMailAccounts);
		}

		$this->setTypes($defaultSettings['types']);
	}

	public function jsonSerialize() {
		$obj = new \StdClass();
		$obj->mailAccounts = $this->getMailAccountsArray();
		$obj->types = $this->getTypesArray();
		return $obj;
	}
}
