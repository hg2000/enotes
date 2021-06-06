<?php

namespace OCA\Enotes;

use OCA\Enotes\Db\Settings;

class MailAdapterFixture
{
	protected Settings $defaultSettings;

	public function __construct()
	{

		$this->defaultSettings = new Settings();
		$this->defaultSettings->setMailAccounts([
			['id' => 1,
				'email' => 'mail1@xyz.de',
				'active' => true
			],
			[
				'id' => 2,
				'email' => 'mail2@xyz.de',
				'active' => true
			],
			[
				'id' => 3,
				'email' => 'mail4@xyz.de',
				'active' => true
			]
		]);
	}

	public function getDefaultSettings(): Settings
	{
		return $this->defaultSettings;
	}
}
