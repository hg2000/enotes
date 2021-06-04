<?php
declare(strict_types=1);

namespace OCA\Enotes\Db;

use OCP\AppFramework\Db\Entity;

class Settings extends Entity
{
	public string $userId = '';

	public $mailAccounts = [];

	public string $types = '';

	/**
	 * Maps the settings state so that it fits to the default settings
	 *
	 * @param array $defaultMailAccounts
	 * @return $this
	 */
	public function mergeWithDefaultMailaccounts(array $defaultMailAccounts): Settings
	{
		if (empty($this->mailAccounts)) {
			$this->setMailAccounts($defaultMailAccounts);
			return $this;
		}

		$idsUser = array_column($this->mailAccounts, 'id');
		$idsDefault = array_column($defaultMailAccounts, 'id');

		$mailAccountsUser = array_combine($idsUser, $this->mailAccounts);
		$mailAccountsDefault = array_combine($idsDefault, $defaultMailAccounts);

		$resultAccounts = [];
		foreach($mailAccountsDefault as $idDefault => $accountDefault) {
			$account = $accountDefault;
			if (in_array($idDefault, $idsUser)) {
				$account = $mailAccountsUser[$idDefault];
			}
			$resultAccounts[] = $account;
		}

		$this->setMailAccounts($resultAccounts);

		return $this;
	}
}
