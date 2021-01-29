<?php
declare(strict_types=1);
/**
 * @copyright Copyright (c) 2019 John Molakvoæ <skjnldsv@protonmail.com>
 *
 * @author John Molakvoæ <skjnldsv@protonmail.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Enotes\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use OCP\Util;

use OCA\Mail\Contracts\IMailManager;
use OCA\Mail\Contracts\IMailSearch;
use OCA\Mail\Service\AccountService;
use OCA\Mail\Service\AliasesService;

use OCA\Enotes\Service\MailService;
use OCA\Enotes\Service\NoteService;

class PageController extends Controller {

	protected $appName;

	/**
	 * @var IMailManager
	 */
	protected $mailManager;

	/**
	 * @var string
	 */
	protected $currentUserId;

	/**
	 * @var AccountService
	 */
	protected $accountService;

	/**
	 * @var MailService
	 */
	protected $mailService;

	/**
	 * @var NoteService
	 */
	protected $noteService;

	public function __construct(
		$appName,
		IRequest $request,
		IMailManager $mailManager,
		AccountService $accountService,
		AliasesService $aliasesService,
		IMailSearch $mailSearch,
		MailService $mailService,
		NoteService $noteService,
		?string $UserId
	) {
		parent::__construct($appName, $request);
		$this->appName = $appName;
		$this->mailManager = $mailManager;
		$this->accountService = $accountService;
		$this->currentUserId = $UserId;
		$this->aliasesService = $aliasesService;
		$this->mailSearch = $mailSearch;
		$this->mailService = $mailService;
		$this->noteService = $noteService;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index() {
		Util::addScript($this->appName, 'enotes-main');
		Util::addStyle($this->appName, 'icons');
		Util::addStyle($this->appName, 'app');

		$response = new TemplateResponse($this->appName, 'main');
		return $response;
	}
}
