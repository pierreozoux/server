<?php
/**
 * @copyright Copyright (c) 2019 Julius Härtl <jus@bitgrid.net>
 *
 * @author Julius Härtl <jus@bitgrid.net>
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */
declare(strict_types=1);

namespace OCA\Settings\Controller;

use OCP\IConfig;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IGroupManager;
use OCP\INavigationManager;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUserSession;

class HelpController extends Controller {

	/** @var IConfig */
	private $config;
	/** @var INavigationManager */
	private $navigationManager;
	/** @var IUserSession */
	private $urlGenerator;
	/** @var IGroupManager */
	private $groupManager;

	/** @var string */
	private $userId;

	private const TRANSLATED_LANGUAGES = [
		'en',
		'fr',
    ];
	
	/**
	* @param IConfig $config
	*/

	public function __construct(
		IConfig $config,
		string $appName,
		IRequest $request,
		INavigationManager $navigationManager,
		IURLGenerator $urlGenerator,
		string $userId,
		IGroupManager $groupManager
	) {
		parent::__construct($appName, $request);
		$this->config = $config;
		$this->navigationManager = $navigationManager;
		$this->urlGenerator = $urlGenerator;
		$this->userId = $userId;
		$this->groupManager = $groupManager;
	}

	/**
	 * @return TemplateResponse
	 *
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function help(string $mode = 'user'): TemplateResponse {
		$this->navigationManager->setActiveEntry('help');
		
		if(!isset($mode) || $mode !== 'admin') {
			$mode = 'user';
		}
		
		if($mode == 'user') {
			$userConfLang = $this->config->getUserValue($this->userId, 'core', 'lang');

			if (in_array($userConfLang, TRANSLATED_LANGUAGES)) {
				$lang = $userConfLang;
			} else {
				$lang = 'en';
			}
			$documentationUrl = $this->urlGenerator->getAbsoluteURL(
				$this->urlGenerator->linkTo('core', 'doc/' . $lang . '/' . $mode . '/index.html')
			);
		} else {
			$documentationUrl = $this->urlGenerator->getAbsoluteURL(
				$this->urlGenerator->linkTo('core', 'doc/' . $mode . '/index.html')
			);
		}

		$urlUserDocs = $this->urlGenerator->linkToRoute('settings.Help.help', ['mode' => 'user']);
		$urlAdminDocs = $this->urlGenerator->linkToRoute('settings.Help.help', ['mode' => 'admin']);

		$response = new TemplateResponse('settings', 'help', [
			'admin' => $this->groupManager->isAdmin($this->userId),
			'url' => $documentationUrl,
			'urlUserDocs' => $urlUserDocs,
			'urlAdminDocs' => $urlAdminDocs,
			'mode' => $mode,
		]);
		$policy = new ContentSecurityPolicy();
		$policy->addAllowedFrameDomain('\'self\'');
		$response->setContentSecurityPolicy($policy);
		return $response;

	}

}