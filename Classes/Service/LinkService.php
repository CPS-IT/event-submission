<?php

namespace Cpsit\EventSubmission\Service;

use Cpsit\EventSubmission\Domain\Model\ApiResponseInterface;
use Cpsit\EventSubmission\Domain\Model\Job;
use Cpsit\EventSubmission\Exceptions\InvalidConfigurationException;
use Cpsit\EventSubmission\Exceptions\InvalidRecordException;
use Cpsit\EventSubmission\Exceptions\InvalidResponseException;
use Exception;
use JsonException;
use nn\t3;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;


/***************************************************************
 *  Copyright notice
 *
 *  (c) 2023 Dirk Wenzel <wenzel@cps-it.de>
 *  All rights reserved
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * A copy is found in the text file GPL.txt and important notices to the license
 * from the author is found in LICENSE.txt distributed with these scripts.
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
class LinkService implements ServiceInterface
{
    public const INVALID_JOB_RECORD_CODE = 1693411371;
    public const INVALID_JOB_RECORD_MESSAGE = 'Invalid job record: %s';
    public const MISSING_APP_PID_CODE = 1693464364;
    public const INVALID_APP_PID_MESSAGE = 'Invalid or missing configuration for settings.eventSubmission.appPid';

    public const INVALID_PAGE_LINK_RESPONSE_CODE = 1693491095;
    public const INVALID_PAGE_LINK_RESPONSE_MESSAGE = 'Invalid response for request for page link: %s.';

    /**
     * Provides a link for editing of an event submission in frontend.
     * Note: This link allows to change and delete the event submission any time!
     *
     * @param Job $job
     * @return string Link for editing in Frontend
     *
     * @throws InvalidConfigurationException
     * @throws InvalidRecordException
     * @throws InvalidResponseException
     * @throws SiteNotFoundException
     * @throws JsonException
     */
    public function createEventEditLink(Job $job): string
    {
        $this->assertValidStoragePid($job);
        $storagePageID = $job->getPid();

        // note: we might use a more sophisticated kind of token
        $editToken = $job->getUuid();
        $link = '';
        $site = GeneralUtility::makeInstance(SiteFinder::class)
            ->getSiteByPageId($storagePageID);
        $appPageId = $this->getAppPageId($site);

        /**
         * there are cases where the TypoScript Frontend is not properly initialized
         * in those cases we use an API-Request to determine the base url and path
         * to the page with our frontend plugin
         */
        // in frontend - get uri from site router:
        if (t3::Environment()->isFrontend()) {
            $link = (string)$site->getRouter()
                ->generateUri($appPageId, ['editToken' => $editToken]);
        }
        // not in frontend - use api request:
        if (!t3::Environment()->isFrontend()) {
            $apiUri = $site->getBase() . '/api/service/appPageLink/' . $appPageId;
            // todo we should probably cache this result
            /* added . "?" to avoid bug in app/web/typo3conf/ext/nnhelpers/Classes/Utilities/Request.php:273  Undefined array key "query"
                It is logged in internal_log_message as "ExceptionCode: 1 Message: Warning: Undefined array key "query""
            */
            $apiResponse = t3::Request()->GET($apiUri . "?");
            $data = json_decode($apiResponse['content'], true, 512, JSON_THROW_ON_ERROR);
            $this->assertValidPageLinkData($data);

            $link = $data['data']['appPageLink'] . '?editToken=' . $editToken;
        }


        return $link;
    }

    public function build(int $pid, array $arguments = [], bool $absolute = true): string
    {
        // Pid should not be 0 or less.
        if ($pid <= 0) {
            throw new Exception(
                'Invalid page id. URL could not be generated.',
                1689946420
            );
        }

        // Validation URL
        return \nn\t3::Http()->buildUri($pid, $arguments, $absolute);
    }
    /**
     * @param Job $job
     * @return void
     * @throws InvalidRecordException
     */
    protected function assertValidStoragePid(Job $job): void
    {
        if (null === $job->getPid()) {
            $message = sprintf(
                self::INVALID_JOB_RECORD_MESSAGE,
                ' Field $appId must not be null.'
            );
            throw new  InvalidRecordException(
                $message,
                self::INVALID_JOB_RECORD_CODE
            );
        }
    }

    /**
     * @param Site $site
     * @return int
     * @throws InvalidConfigurationException
     */
    protected function getAppPageId(Site $site): int
    {
        $siteConfiguration = $site->getConfiguration();

        if (
            !isset($siteConfiguration['settings']['eventSubmission']['appPid'])
            || !MathUtility::canBeInterpretedAsInteger($siteConfiguration['settings']['eventSubmission']['appPid'])
        ) {
            throw new InvalidConfigurationException(
                self::INVALID_APP_PID_MESSAGE,
                self::MISSING_APP_PID_CODE
            );
        }
        return (int)$siteConfiguration['settings']['eventSubmission']['appPid'];
    }

    /**
     * @param mixed $data
     * @return void
     * @throws InvalidResponseException
     */
    protected function assertValidPageLinkData(mixed $data): void
    {
        if (
            $data['code'] !== ApiResponseInterface::APP_PAGE_LINK_REQUEST_SUCCESS
            || !isset($data['data']['appPageLink'])
        ) {
            $message = sprintf(self::INVALID_PAGE_LINK_RESPONSE_MESSAGE, $data);
            throw new InvalidResponseException(
                $message,
                self::INVALID_PAGE_LINK_RESPONSE_CODE
            );
        }
    }
}
