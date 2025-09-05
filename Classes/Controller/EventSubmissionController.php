<?php declare(strict_types=1);

namespace Cpsit\EventSubmission\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class EventSubmissionController extends ActionController
{
    public function appAction(): ResponseInterface
    {
        /** @var ContentObjectRenderer $cObj */
        $cObj = $this->request->getAttribute('currentContentObject');
        $this->view->assignMultiple([
            'json' => json_decode($cObj->data['bodytext']??'{}', true, JSON_THROW_ON_ERROR),
            'validationHash' => $this->request->getQueryParams()['validationHash'] ?? null
        ]);

        return $this->htmlResponse();
    }
}
