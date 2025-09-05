<?php declare(strict_types=1);

namespace Cpsit\EventSubmission\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class EventSubmissionController extends ActionController
{
    public function appAction(): ResponseInterface
    {
        $this->view->assignMultiple([
            'rootPid' => $this->request->getAttribute('site')->getRootPageId(),
            'json' => ['ADD JSON BODY HERE!'],
            'settings' => $this->settings,
        ]);

        return $this->htmlResponse();
    }
}
