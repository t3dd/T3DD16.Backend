<?php

namespace TYPO3\Sessions\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Fluid\View\TemplateView;

/**
 * Class SessionModuleController
 * @package TYPO3\Sessions\Controller
 */
class ApiModuleController extends ActionController
{

    /**
     * @var TemplateView
     */
    protected $view;

    /**
     * BackendTemplateView Container
     *
     * @var TemplateView
     */
    protected $defaultViewObjectName = TemplateView::class;

    /**
     * Mapping between slugs and concrete classes
     * @var array
     */
    public static $slugClassMap = [
        'proposed' => \TYPO3\Sessions\Domain\Model\ProposedSession::class,
        'declined' => \TYPO3\Sessions\Domain\Model\DeclinedSession::class,
        'accepted' => \TYPO3\Sessions\Domain\Model\AcceptedSession::class,
    ];

    /**
     * @var string
     */
    protected $resourceArgumentName = 'session';

    /**
     * @var \TYPO3\Sessions\Domain\Repository\RoomRepository
     */
    protected $roomRepository;

    /**
     * @var \TYPO3\Sessions\Domain\Repository\ScheduledSessionRepository
     */
    protected $sessionRepository;

    /**
     * Initializes the module view.
     *
     * @param ViewInterface $view The view
     * @return void
     *
     * @throws
     */
    protected function initializeView(ViewInterface $view)
    {
        // Skip, if view is initialized in non-backend context
        if (!($view instanceof TemplateView)) {
            return;
        }
        parent::initializeView($view);
    }

    /**
     *
     * @param \TYPO3\Sessions\Domain\Model\AnySession $session
     * @return string
     */
    public function infoAction($session)
    {
        $this->view->assign('session', $session);
    }

    /**
     * @param int $id
     * @param string $type
     * @return string
     */
    public function toggleAction($id, $type)
    {
        if(!in_array($type, array_keys(self::$slugClassMap))) {
            throw new \InvalidArgumentException('type parameter must be one of the following: '.implode(array_keys(self::$slugClassMap)));
        }
        $id = (int) $id;
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $db */
        $db = $GLOBALS['TYPO3_DB'];
        $updated = $db->exec_UPDATEquery('tx_sessions_domain_model_session', "uid = {$id}", ['type' => self::$slugClassMap[$type]]);
        if($this->response instanceof \TYPO3\CMS\Extbase\Mvc\Web\Response) {
            $this->response->setHeader('Content-Type', 'application/json', true);
        }
        return json_encode(['success' => $updated]);
    }


    /**
     * Get all sessions for FullCalendar
     *
     * @return String
     */
    public function listSessionsAction()
    {
        $allSessionFlatArray = $this->sessionRepository->findAllFlat();

        $result = array();

        // Session properties
        $sessionWhitelist = array('uid', 'title', 'date', 'begin', 'end', 'room', 'description');
        // Generate session data
        foreach($allSessionFlatArray as $session)
        {
            $tempSessionArray = array();
            foreach($sessionWhitelist as $property)
            {
                $tempSessionArray[$property] = $session[$property];
            }
//            $result[] = $tempSessionArray;
            $result[] = array('id' => $session['uid'],
                'resourceId' => $session['room'],
                'start' => $session['begin'],
                'end' => $session ['end'],
                'title' => $session ['title'],
                'description' => $session ['description']);
        }
        return json_encode($result);

    }


    /**
     * Get all rooms for FullCalendar
     *
     * @return String
     */
    public function listRoomsAction()
    {
        $allRoomFlatArray = $this->roomRepository->findAllFlat();

        $result = array();

        // Generate room data
        foreach($allRoomFlatArray as $room)
        {
            $result[] = array('id' => $room['uid'], 'title' => $room['title']);
        }
        return json_encode($result);

    }

    public function initializeUpdateSessionAction()
    {
        $this->adjustSessionPropertyMappingConfiguration($this->resourceArgumentName);

        if ($this->request->hasArgument('secondSession')) {
            $this->adjustSessionPropertyMappingConfiguration('secondSession');
        }
    }

    /*public function errorAction(){
        var_dump($this->request->getOriginalRequestMappingResults()->forProperty('session')->getFlattenedErrors());
    }*/

    /**
     * Update session
     *
     * @param \TYPO3\Sessions\Domain\Model\ScheduledSession $session
     * @param \TYPO3\Sessions\Domain\Model\ScheduledSession $secondSession
     * @return string
     */
    public function updateSessionAction($session, $secondSession = null)
    {
        /* // Find session object
         $sessionObject = $this->sessionRepository->findByUid($session['uid']);
         // Set session properties

         if(!is_null($sessionObject)) {
             foreach ($session as $propertyName => $value) {
                 if($propertyName != 'uid')
                     $sessionObject->{'set'.ucwords($propertyName)}($value);
             }
             $this->sessionRepository->update($sessionObject);
         }
         $this->redirect('listSessionAndRoomsForCalender');*/

        //var_dump($session);die;
        $this->sessionRepository->update($session);
        if($secondSession != null) {
            $this->sessionRepository->update($secondSession);
        }
//        return $session->getTitle();
        return 'success';
    }

    protected function adjustSessionPropertyMappingConfiguration($propertyName) {
        /** @var \TYPO3\CMS\Extbase\Mvc\Controller\MvcPropertyMappingConfiguration $propertyMappingConfiguration */
        $propertyMappingConfiguration = $this->arguments[$propertyName]->getPropertyMappingConfiguration();
        $propertyMappingConfiguration->setTypeConverterOption(PersistentObjectConverter::class,
            PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED, true);
        $propertyMappingConfiguration->forProperty('begin')->setTypeConverterOption(DateTimeConverter::class,
            DateTimeConverter::CONFIGURATION_DATE_FORMAT, DATE_ISO8601);
        $propertyMappingConfiguration->forProperty('end')->setTypeConverterOption(DateTimeConverter::class,
            DateTimeConverter::CONFIGURATION_DATE_FORMAT, DATE_ISO8601);
        $propertyMappingConfiguration->allowProperties('date', 'begin', 'end', 'room', 'title');
        $propertyMappingConfiguration->skipUnknownProperties();

    }

    /**
     * @param \TYPO3\Sessions\Domain\Repository\RoomRepository $roomRepository
     */
    public function injectRoomRepository(\TYPO3\Sessions\Domain\Repository\RoomRepository $roomRepository)
    {
        $this->roomRepository = $roomRepository;
    }

    /**
     * @param \TYPO3\Sessions\Domain\Repository\ScheduledSessionRepository $sessionRepository
     */
    public function injectSessionRepository(\TYPO3\Sessions\Domain\Repository\ScheduledSessionRepository$sessionRepository)
    {
        $this->sessionRepository = $sessionRepository;
    }

}
