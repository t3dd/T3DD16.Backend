<?php
namespace TYPO3\Sessions\Controller;

use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\Sessions\Domain\Model\Session;

class SessionController extends AbstractRestController
{

    /**
     * @var string
     */
    protected $resourceArgumentName = 'session';

    /**
     * @var \TYPO3\Sessions\Domain\Repository\SessionRepository
     * @inject
     */
    protected $sessionRepository;

    /**
     * @var \TYPO3\Sso\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $frontendUserRepository;

    /**
     * @return string
     */
    public function listAction()
    {
        $this->addTableCacheTags();

        return json_encode($this->sessionRepository->findAll()->toArray());
    }

    /**
     * @param Session $session
     * @return string
     */
    public function showAction(Session $session)
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
     * @param Session $session
     * @validate $session \TYPO3\Sessions\Domain\Validator\ActiveUserValidator
     * @return string
     */
    public function createAction(Session $session)
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
     * @param Session $session
     * @validate $session \TYPO3\Sessions\Domain\Validator\ActiveUserValidator
     * @validate $session \TYPO3\Sessions\Domain\Validator\SessionOwnerValidator
     * @return string
     */
    public function updateAction(Session $session)
    {
        $this->sessionRepository->update($session);

        return json_encode($session);
    }

    protected function addTableCacheTags()
    {
        $this->getTypoScriptFrontendController()->addCacheTags([
            'tx_sessions_domain_model_session',
            'tx_sessions_domain_model_room'
        ]);
    }

    /**
     * @param Session $session
     */
    protected function addCacheTags(Session $session)
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
