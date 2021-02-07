<?php
declare(strict_types=1);

namespace OCA\Enotes\Controller;

use OCP\AppFramework\Http;
use OCP\IRequest;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Controller;
use OCP\Util;
use OCP\IL10N;

use OCA\Mail\Contracts\IMailManager;
use OCA\Mail\Contracts\IMailSearch;
use OCA\Mail\Service\AccountService;
use OCA\Mail\Service\AliasesService;

use OCA\Enotes\Service\MailService;
use OCA\Enotes\Service\NoteService;
use OCA\Enotes\Db\BookMapper;
use Psr\Http\Message\ResponseInterface;


class NoteController extends Controller {

	/**
	 * @var
	 */
	protected $appName;

	/**
	 * @var IL10N
	 */
	protected $l;
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

	/**
	 * @var BookMapper
	 */
	protected $bookMapper;

	public function __construct(
		$appName,
		IRequest $request,
		IMailManager $mailManager,
		IL10N $l,
		IMailSearch $mailSearch,
		AccountService $accountService,
		AliasesService $aliasesService,
		MailService $mailService,
		NoteService $noteService,
		BookMapper $bookMapper,
		?string $UserId
	) {
		parent::__construct($appName, $request);
		$this->appName = $appName;
		$this->mailManager = $mailManager;
		$this->l = $l;
		$this->accountService = $accountService;
		$this->currentUserId = $UserId;
		$this->aliasesService = $aliasesService;
		$this->mailSearch = $mailSearch;
		$this->mailService = $mailService;
		$this->noteService = $noteService;
		$this->bookMapper = $bookMapper;
		$this->deviceId = 'Kindle';
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function list() {

		Util::addScript($this->appName, 'enotes-main');
		Util::addStyle($this->appName, 'icons');

		$books = $this->bookMapper->findByUserId($this->currentUserId);
		if (!empty($books)) {
			return new JSONResponse(json_encode($books));
		}
		return $this->scanMails();
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function scanMails() {

		try {
			$attachments = $this->mailService->getMailAttachments();
			$books = [];
			$id = 0;
			foreach ($attachments as $attachment) {
				$csv = $attachment->getContents();
				$book = $this->noteService->parseCsv($csv, $this->deviceId);
				$book->setId($id);
				$books[] = $book;
				$id++;
			}
			$books = $this->noteService->removeDuplicates($books);
			return new DataResponse(json_encode($books));

		} catch (\Exception $e) {
			$message = $this->l->t('error.sync') . $e->getMessage();
			return new JSONResponse($message, Http::STATUS_CONFLICT);
		}
	}

	public function getDummyCsv2() {

		return <<<EOT
﻿"Ihre Kindle-Notizen für:",,,
"DAS ZEITALTER DES ÜBERWACHUNGSKAPITALISMUS",,,
"von Shoshana Zuboff, Bernhard Schmid",,,
"Kostenlose sofortige Kindle-Vorschau:",,,
"https://amzn.eu/f5tbW50",,,
----------------------------------------------,,,
,,,
"Anmerkungstyp","Position","Markiert?","Anmerkung"
"Markierung (Gelb)","Seite 63","","Was das Ganze so unerträglich macht, ist der Umstand, dass zwar die ökonomische und soziale Ungleichheit zum vorindustriellen »feudalen« Muster zurückgekehrt ist, aber nicht der Mensch."
"Markierung (Gelb)","Seite 99","","Verhaltensüberschuss voranzutreiben.43 Ich beschränke mich hier auf ein 2003 von drei der Topinformatiker des Unternehmens eingereichtes Patent mit dem Titel »Die Generierung von Nutzerinformationen zur Nutzung in der zielgerichteten Werbung«."
"Markierung (Gelb)","Seite 125","","Larry Page hatte begriffen, dass die menschliche Erfahrung Googles unberührter Wald sein könnte: Rohstoff, der sich ohne zusätzliche Kosten online und zu sehr geringen Kosten draußen in der realen Welt, wo »Sensoren billig« sind, extrahieren ließ. Ist sie erst einmal extrahiert, gewinnt man daraus Verhaltensdaten und generiert so den Überschuss, der die Basis für eine ganz neue Klasse marktwirtschaftlichen Austauschs bildet. Der Überwachungskapitalismus hat seinen Ursprung in diesem Akt digitaler Enteignung; ins Leben gerufen haben ihn die Ungeduld überakkumulierter Investitionen und zwei Unternehmer, die fest entschlossen waren, »Teil des Systems« zu werden."
"Markierung (Gelb)","Seite 137","","Ist ein »Vermittler« nämlich gleichzeitig auch ein Überwachungskapitalist, postet er eben nicht mehr nur anderer Leute Inhalte. Vielmehr sind diese Inhalte zugleich auch der Rohstoff für die fortwährende Anhäufung von Verhaltensüberschuss."
"Markierung (Gelb)","Seite 208","","Anerkennung der neuen Fakten durch andere bekräftigt oder geschwächt. Laut Searle ist »die gesamte institutionelle Realität und daher … die gesamte menschliche Zivilisation … das Produkt von … Deklarativa«.5"
"Markierung (Gelb)","Seite 210","","Wir beanspruchen menschliche Erfahrung als herrenlosen Rohstoff. Auf der Basis dieses Anspruchs können wir Rechte, Interessen, Kenntnisnahme und Verständnis der Betroffenen ignorieren. Auf der Basis unseres Anspruchs bestehen wir auf das Recht, die Erfahrung des Einzelnen in Verhaltensdaten umzuwandeln. Aus unserem rechtlichen Anspruch auf dieses herrenlose Rohmaterial ergibt sich das Recht auf den Besitz aller aus menschlichem Verhalten gewonnenen Verhaltensdaten. Aus unserem Recht, Daten zu erfassen und in Besitz zu nehmen, ergibt sich das Recht, zu wissen, was uns diese Daten enthüllen. Aus unserem Recht zu erfassen, zu besitzen und zu wissen, ergibt sich das Recht, darüber zu entscheiden, wie und wozu wir unser Wissen einsetzen. Aus unserem Recht, zu erfassen, zu besitzen, zu wissen und zu entscheiden, ergibt sich das Recht, die Bedingungen zu bestimmen, die uns das Recht, zu erfassen, zu besitzen, zu wissen und zu entscheiden, bewahren."
"Markierung (Gelb)","Seite 215","","dass zurzeit selbst die Grundelemente der Zivilisation wie etwa »Sprache, Kulturgüter, Traditionen, Institutionen, Regeln und Gesetze … digitalisiert und zum ersten Mal explizit in sichtbaren Code umgesetzt werden«, um dann über den Filter »intelligenter Algorithmen«, wie sie heute zur Regelung einer rapide wachsenden Zahl von kommerziellen, staatlichen und sozialen Funktionen eingesetzt werden, an die Gesellschaft zurückzugehen.17 Die wesentlichen Fragen stellen sich uns dabei bei jedem Schritt: Wer weiß? Wer entscheidet? Wer entscheidet, wer entscheidet?"
"Markierung (Gelb)","Seite 225","","Die beispiellose Konzentration von Wissen sorgt für eine nicht weniger beispiellose Konzentration von Macht –"
"Markierung (Gelb)","Seite 226","","Auf der »Angebotsseite« bedienten die Überwachungskapitalisten sich geschickt des gesamten Arsenals der Deklaration zur Legitimierung ihrer Autorität in dieser neuen und ungeschützten digitalen Welt. Die Deklarationen gestatteten ihnen, zu nehmen, ohne zu fragen. Sie camouflierten ihre Absichten mit unlesbaren Maschinenoperationen, gingen in rasendem Tempo vor, hielten die Hand über ihre Praktiken, meisterten die rhetorische Irreführung, lehrten Hilflosigkeit, bemächtigten sich ganz bewusst kultureller Zeichen und Symbole, die wir mit der Thematik der Zweiten Moderne verbinden – Befähigung, Teilhabe, Stimme, Individualisierung und Zusammenarbeit –, und appellierten unverblümt an die Frustrationen der Individuen der Zweiten Moderne, wie sie aus der Kollision zwischen individuellen Sehnsüchten und institutioneller Gleichgültigkeit entstehen."
"Markierung (Gelb)","Seite 260","","Jede Unvermeidlichkeitsdoktrin birgt einen Virus mit dem Zeug zur Waffe: einen moralischen Nihilismus, der darauf programmiert ist, selbstverantwortliches menschliches Handeln aufs Korn zu nehmen und jeglichen Widerstand ebenso aus dem Text menschlicher Möglichkeiten zu löschen wie jegliche Kreativität."
"Markierung (Gelb)","Seite 268","","Wer weiß? Wer entscheidet? Wer entscheidet, wer entscheidet?"
"Markierung (Gelb)","Seite 270","","»Rendition« bezeichnet die konkreten operativen Praktiken der Enteignung, mit denen das Überwachungskapital menschliche Erfahrung als Rohstoff für die Verdatung und alle darauf folgenden Operationen beansprucht – von der Herstellung bis zum Verkauf."
"Markierung (Gelb)","Seite 285","","Es legt beredtes Zeugnis über das Versagen des Gesundheitswesens bei der Versorgung der Individuen der Zweiten Moderne ab, dass wir auf Gesundheitsdaten und einschlägigen Rat per Telefon zugreifen, während dieser Taschencomputer auf aggressive Weise auf uns zugreift."
"Markierung (Gelb)","Seite 286","","Die Richtlinien der Behörden meinen es durchaus gut, übersehen aber die unbequeme Wahrheit, dass die Überwachungskapitalisten sich mit Transparenz und Datenschutz so schwertun wie die frühen Industriekapitalisten mit der Verbesserung der Arbeitsbedingungen, der Abschaffung der Kinderarbeit oder der Verkürzung des Arbeitstags."
EOT;

	}

	public function getDummyCsv() {


		return <<<EOT
﻿"Ihre Kindle-Notizen für:",,,
"MESOPOTAMIEN: DIE FRÜHEN HOCHKULTUREN AN EUPHRAT UND TIGRIS (BECK'SCHE REIHE 2877)",,,
"von Karen Radner",,,
"Kostenlose sofortige Kindle-Vorschau:",,,
"https://amzn.eu/bdPDWb9",,,
----------------------------------------------,,,
,,,
"Anmerkungstyp","Position","Markiert?","Anmerkung"
"Markierung (Gelb)","Position 196","","Südmesopotamiens geographische Lage am Persischen Golf an der Mündung der Flüsse Euphrat und Tigris, die es mit Anatolien und Westiran verbinden, machte die Region zu einem idealen Zwischenhändler."
"Markierung (Gelb)","Position 207","","Man kann nicht einfach davon ausgehen, dass sämtliche Technologien automatisch übernommen werden sowie Kontakte zwischen zwei Regionen oder Kulturen bestehen."
"Markierung (Gelb)","Position 567","","Es war Ur-Namma, der als König von Ur, der wichtigsten Hafenstadt am Persischen Golf, wieder weite Teile Südmesopotamiens zur politischen Einheit zusammenführte und seinen Nachfolgern einen stabilen und straff organisierten Staat vererbte."
EOT;

	}
}
