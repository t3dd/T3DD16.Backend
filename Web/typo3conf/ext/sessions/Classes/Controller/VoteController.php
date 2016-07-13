<?php
namespace TYPO3\Sessions\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\Sessions\Domain\Model\AbstractSession;
use TYPO3\Sessions\Domain\Model\ProposedSession;

class VoteController extends AbstractRestController
{

    /**
     * @var string
     */
    protected $resourceArgumentName = 'session';

    /**
     * @var \TYPO3\Sessions\Domain\Repository\VoteRepository
     * @inject
     */
    protected $voteRepository;

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
        $user = $this->frontendUserRepository->findCurrentUser();
        return json_encode($this->voteRepository->findByUser($user)->toArray());
    }


    public function initializeCreateAction()
    {
        if ($this->request->hasArgument($this->resourceArgumentName)) {
            $vote = $this->request->getArgument($this->resourceArgumentName);
            $this->request->setArguments($vote);
        }
        /** @var \TYPO3\CMS\Extbase\Mvc\Controller\MvcPropertyMappingConfiguration $propertyMappingConfiguration */
        $propertyMappingConfiguration = $this->arguments[$this->resourceArgumentName]->getPropertyMappingConfiguration();
        $propertyMappingConfiguration->setTypeConverterOption(PersistentObjectConverter::class,
            PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, false);
        $propertyMappingConfiguration->setTypeConverterOption(PersistentObjectConverter::class,
            PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED, false);
        $propertyMappingConfiguration->skipUnknownProperties();
    }

    /**
     * @param AbstractSession $session
     * @return string
     */
    public function createAction(AbstractSession $session)
    {
        $user = $this->frontendUserRepository->findCurrentUser();
        $vote = $this->voteRepository->findOneByUserAndSession($user, $session);

        if (!$vote) {
            $vote = GeneralUtility::makeInstance(\TYPO3\Sessions\Domain\Model\Vote::class, $user, $session);
            $this->voteRepository->add($vote);
            $this->persistenceManager->persistAll();
        }

        return json_encode($vote);
    }


    public function initializeDeleteAction()
    {
        /** @var \TYPO3\CMS\Extbase\Mvc\Controller\MvcPropertyMappingConfiguration $propertyMappingConfiguration */
        $propertyMappingConfiguration = $this->arguments[$this->resourceArgumentName]->getPropertyMappingConfiguration();
        $propertyMappingConfiguration->setTypeConverterOption(PersistentObjectConverter::class,
            PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, false);
        $propertyMappingConfiguration->setTypeConverterOption(PersistentObjectConverter::class,
            PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED, false);
        $propertyMappingConfiguration->skipUnknownProperties();
    }

    /**
     * @param AbstractSession $session
     * @return string
     */
    public function deleteAction(AbstractSession $session)
    {
        $user = $this->frontendUserRepository->findCurrentUser();
        $vote = $this->voteRepository->findOneByUserAndSession($user, $session);

        if ($vote) {
            $this->voteRepository->remove($vote);
            $this->persistenceManager->persistAll();
        }

        return json_encode(true);
    }

    protected function addTableCacheTags()
    {
        $this->getTypoScriptFrontendController()->addCacheTags([
            'tx_sessions_domain_model_session',
            'tx_sessions_domain_model_room'
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
