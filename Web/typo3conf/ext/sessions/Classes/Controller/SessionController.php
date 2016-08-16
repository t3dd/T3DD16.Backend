<?php
namespace TYPO3\Sessions\Controller;

use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\Sessions\Domain\Model\AbstractSession;
use TYPO3\Sessions\Domain\Model\ProposedSession;

class SessionController extends AbstractRestController
{

    /**
     * @var string
     */
    protected $resourceArgumentName = 'session';

    /**
     * @inject
     * @var \TYPO3\Sessions\Domain\Repository\AnySessionRepository
     */
    protected $sessionRepository;

    /**
     * @inject
     * @var \TYPO3\Sessions\Domain\Repository\ScheduledSessionRepository
     */
    protected $scheduledSessionRepository;

    /**
     * @inject
     * @var \TYPO3\Sso\Domain\Repository\FrontendUserRepository
     */
    protected $frontendUserRepository;

    /**
     * Lists all(!) proposed, assigned, declined, ... sessions.
     *
     * @return string
     */
    public function listAction()
    {
        $this->addTableCacheTags();

        return json_encode($this->sessionRepository->findAll()->toArray());
    }

    /**
     * Lists all scheduled(!) sessions.
     *
     * @return string
     */
    public function listScheduledAction()
    {
        $this->addTableCacheTags();
        return json_encode($this->scheduledSessionRepository->findAll()->toArray());
    }

    /**
     * @param AbstractSession $session
     * @return string
     */
    public function showAction(AbstractSession $session)
    {
        $this->addCacheTags($session);

        return json_encode($session);
    }

    public function initializeCreateAction()
    {
        /** @var \TYPO3\CMS\Extbase\Mvc\Controller\MvcPropertyMappingConfiguration $propertyMappingConfiguration */
        $propertyMappingConfiguration = $this->arguments[$this->resourceArgumentName]->getPropertyMappingConfiguration();
        $propertyMappingConfiguration->setTypeConverterOption(PersistentObjectConverter::class,
            PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, true);
        $propertyMappingConfiguration->allowProperties('title', 'description');
        $propertyMappingConfiguration->skipUnknownProperties();
    }

    /**
     * @param ProposedSession $session
     * @validate $session \TYPO3\Sessions\Domain\Validator\ActiveUserValidator
     * @return string
     */
    public function createAction(ProposedSession $session)
    {
        $user = $this->frontendUserRepository->findCurrentUser();
        $session->addSpeaker($user);
        $this->sessionRepository->add($session);
        $this->persistenceManager->persistAll();

        return json_encode($session);
    }


    public function initializeUpdateAction()
    {
        /** @var \TYPO3\CMS\Extbase\Mvc\Controller\MvcPropertyMappingConfiguration $propertyMappingConfiguration */
        $propertyMappingConfiguration = $this->arguments[$this->resourceArgumentName]->getPropertyMappingConfiguration();
        $propertyMappingConfiguration->setTypeConverterOption(PersistentObjectConverter::class,
            PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED, true);
        $propertyMappingConfiguration->allowProperties('title', 'description');
        $propertyMappingConfiguration->skipUnknownProperties();
    }

    /**
     * @param AbstractSession $session
     * @validate $session \TYPO3\Sessions\Domain\Validator\ActiveUserValidator
     * @validate $session \TYPO3\Sessions\Domain\Validator\SessionOwnerValidator
     * @return string
     */
    public function updateAction(AbstractSession $session)
    {
        $this->sessionRepository->update($session);

        return json_encode($session);
    }

    protected function addTableCacheTags()
    {
        $this->getTypoScriptFrontendController()->addCacheTags([
            'tx_sessions_domain_model_session',
            'tx_sessions_domain_model_room',
            'tx_sessions_domain_model_vote'
        ]);
    }

    /**
     * @param AbstractSession $session
     */
    protected function addCacheTags(AbstractSession $session)
    {
        $cacheTags = [];
        $cacheTags[] = 'tx_sessions_domain_model_session_' . $session->getUid();
        $speakers = ObjectAccess::getProperty($session, 'speakers');
        foreach ($speakers as $speaker) {
            /** @var FrontendUser $speaker */
            $cacheTags[] = 'fe_users_' . $speaker->getUid();
        }
        if ($session->getRoom()) {
            $cacheTags[] = 'tx_sessions_domain_model_room_' . $session->getRoom()->getUid();
        }
        $this->getTypoScriptFrontendController()->addCacheTags($cacheTags);
    }

    /**
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }

}
