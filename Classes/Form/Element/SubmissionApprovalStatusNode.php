<?php declare(strict_types=1);

namespace Cpsit\EventSubmission\Form\Element;

use Cpsit\EventSubmission\Domain\Model\Job;
use Cpsit\EventSubmission\Form\FormElementAttributesTrait;
use Cpsit\EventSubmission\Form\RegistrableInterface;
use Cpsit\EventSubmission\Form\RegistrableTrait;
use Cpsit\EventSubmission\Type\SubmissionStatus;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageRendererResolver;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
class SubmissionApprovalStatusNode extends AbstractFormElement implements RegistrableInterface
{
    use FormElementAttributesTrait;
    use RegistrableTrait;

    public const NODE_ID = 1690799109;
    public const NODE_NAME = 'eventSubmissionApprovalStatus';
    public const NODE_PRIORITY = 30;
    public const DEFAULT_LANGUAGE_FILE = 'LLL:EXT:event_submission/Resources/Private/Language/locallang_db.xlf';

    /**
     * @inheritDoc
     */
    public function render(): array
    {
        $fieldInformationResult = $this->renderFieldInformation();
        $fieldInformationHtml = $fieldInformationResult['html'];
        $resultArray = $this->mergeChildReturnIntoExistingResult(
            $this->initializeResultArray(),
            $fieldInformationResult,
            false
        );

        $html = [];
        $html[] = $this->renderStatusMessage();
        $html[] = '<div class="formengine-field-item t3js-formengine-field-item">';
        $html[] = $fieldInformationHtml;
        $html[] = '<div >';
        $html[] = '</div>';
        $html[] = '</div>';
        $resultArray['html'] = implode(LF, $html);

        return $resultArray;
    }

    /**
     * @return string
     */
    protected function renderStatusMessage(): string
    {
        $row = $this->data['databaseRow'];
        $languageService = $this->getLanguageService();

        $header = $languageService->sL(
            static::DEFAULT_LANGUAGE_FILE . ':approval_status.header'
        );

        $messageKey = 'submissionApprovalPending';

        if ($row[Job::FIELD_IS_DONE] === 0 && $row[Job::FIELD_APPROVED] === 1) {
            $messageKey = 'submissionApprovedEventCreationPending';
        }

        if ($row[Job::FIELD_IS_DONE] === 1 && $row[Job::FIELD_APPROVED] === 1) {
            $messageKey = 'submissionApprovedEventCreated';
        }
        // not sure why this field contains an array ¯\_(ツ)_/¯
        if ($row[Job::FIELD_STATUS][0] === (string)SubmissionStatus::WITHDRAWN) {
            $messageKey = 'submissionWithdrawn';
        }
        $message = $languageService->sL(
            static::DEFAULT_LANGUAGE_FILE . ':approval_status.message.' . $messageKey
        );
        $flashMessage = GeneralUtility::makeInstance(FlashMessage::class,
            $message,
            $header,
            ContextualFeedbackSeverity::INFO,
            false
        );
        return GeneralUtility::makeInstance(FlashMessageRendererResolver::class)
            ->resolve()
            ->render([$flashMessage]);
    }
}
