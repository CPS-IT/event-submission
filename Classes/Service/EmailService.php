<?php declare(strict_types=1);

namespace Cpsit\EventSubmission\Service;

use Exception;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Throwable;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class EmailService
{
    public function __construct(
        private readonly MailerInterface $mailer
    )
    {}

    public function sendMail(ServerRequestInterface $request, string $template, array $parameter, Address $recipient, ?Address $sender = null, string $subject = ''): bool
    {
        $sender = $sender ?? $this->generateDefaultSender($request);
        if (!$sender) {
            throw new Exception('No sender Provided for mail sending');
        }

        // Use StandaloneView for simple template rendering
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(
            GeneralUtility::getFileAbsFileName($template)
        );
        $view->assignMultiple($parameter);
        $htmlBody = $view->render();

        // Use regular Email instead of FluidEmail
        $email = GeneralUtility::makeInstance(Email::class);
        $email
            ->to($recipient)
            ->from($sender)
            ->subject($subject)
            ->html($htmlBody);

        try {
            $this->mailer->send($email);
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function generateDefaultSender(ServerRequestInterface $request): ?Address
    {
        /** @var Site $site */
        $site = $request->getAttribute('site');
        $senderConfig = $site->getConfiguration()['mail']['senderHeader'] ?? [];
        $mail = $senderConfig['from'] ?? null;
        $name = $senderConfig['fromName'] ?? null;
        if (!$mail) {
            return null;
        }

        return new Address($mail, $name ?? '');
    }
}
