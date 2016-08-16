<?php

namespace TYPO3\Sessions\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\JsonView;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3\Sessions\Domain\Model\Room;
use TYPO3\Sessions\Domain\Model\ScheduledSession;
use TYPO3\Sessions\Utility\PlanningUtility;

/**
 * Class SessionModuleController
 * @package TYPO3\Sessions\Controller
 */
class ApiModuleController extends ActionController
{
    /**
     * BackendTemplateView Container
     *
     * @var TemplateView
     */
    protected $defaultViewObjectName = JsonView::class;

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
     * @var \TYPO3\Sessions\Domain\Repository\AcceptedSessionRepository
     */
    protected $acceptedSessionRepository;

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
        if ($view instanceof TemplateView) {
            parent::initializeView($view);
        }
    }

    /**
     * Convenience method. Simplifies swapping process in FE.
     *
     * @param \TYPO3\Sessions\Domain\Model\ScheduledSession $first
     * @param \TYPO3\Sessions\Domain\Model\ScheduledSession $second
     * @return string
     */
    public function swapSessionsAction(ScheduledSession $first, ScheduledSession $second)
    {
        // first swap the data...
        $swapData = [
            'begin' => $first->getBegin(),
            'end'   => $first->getEnd(),
            'room' => $first->getRoom()
        ];
        $first->setBegin($second->getBegin());
        $first->setEnd($second->getEnd());
        $first->setRoom($second->getRoom());

        $second->setBegin($swapData['begin']);
        $second->setEnd($swapData['end']);
        $second->setRoom($swapData['room']);

        /** @var PlanningUtility $planningUtility */
        $planningUtility = GeneralUtility::makeInstance(PlanningUtility::class);
        $firstCollides = $planningUtility->getCollidingSessions($first, [$second->getUid()]);
        $secondCollides = $planningUtility->getCollidingSessions($second, [$first->getUid()]);
        $errors = [];
        if(is_array($firstCollides)) {
            foreach($firstCollides as $session) {
                /** @var ScheduledSession $session */
                $errors[] = 'One speaker of the session "'.$first->getTitle().'" already speaks at session "'.$session->getTitle().'"';
            }
        }
        if(is_array($secondCollides)) {
            foreach($secondCollides as $session) {
                /** @var ScheduledSession $session */
                $errors[] = 'One speaker of the session "'.$second->getTitle().'" already speaks at session "'.$session->getTitle().'"';
            }
        }
        if(count($errors) === 0) {
            $this->sessionRepository->update($first);
            $this->sessionRepository->update($second);
            /** @var PersistenceManager $persistenceManager */
            $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
            $persistenceManager->persistAll();
        }
        return !empty($errors) ? $this->customErrorAction($errors) : '{}';
    }

    public function initializeSwapSessionsAction()
    {
        $this->adjustSessionPropertyMappingConfiguration('first');
        $this->adjustSessionPropertyMappingConfiguration('second');
    }

    /**
     * @param $start \DateTime
     * @param $end \DateTime
     * @return string
     */
    public function analyzeAction(\DateTime $start, \DateTime $end)
    {
        $sessions = $this->sessionRepository->findByStartAndEnd($start, $end);
        $result = [];
        foreach($sessions as $session) {
            $result[] = [
                'uid' => $session['uid'],
                'title' => $session['title'],
                'data' => $this->getTopicMap($session['uid'])
            ];
        }
        $this->view->assign('value', $result);
    }

    protected function getTopicMap($uid)
    {
        if(!MathUtility::canBeInterpretedAsInteger($uid)) {
            throw new \RuntimeException('Given $uid must be a valid integer');
        }
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $db */
        $db = $GLOBALS['TYPO3_DB'];
        $stmt = $db->prepare_SELECTquery('tx_sessions_domain_model_topic_group.title, tx_sessions_domain_model_topic_group.color, COUNT(*) AS count',
            'tx_sessions_domain_model_topic_group
LEFT JOIN tx_sessions_topic_group_topic_mm ON tx_sessions_topic_group_topic_mm.uid_local = tx_sessions_domain_model_topic_group.uid
LEFT JOIN tx_sessions_domain_model_topic ON tx_sessions_domain_model_topic.uid = tx_sessions_topic_group_topic_mm.uid_foreign
LEFT JOIN tx_sessions_session_record_mm ON tx_sessions_session_record_mm.uid_foreign = tx_sessions_domain_model_topic.uid
LEFT JOIN tx_sessions_domain_model_session ON tx_sessions_domain_model_session.uid = tx_sessions_session_record_mm.uid_local',
            'tx_sessions_session_record_mm.tablenames = \'tx_sessions_domain_model_topic\'
AND tx_sessions_domain_model_session.uid = :uid',
            'tx_sessions_domain_model_topic_group.uid','','',[':uid' => $uid]);

        if($stmt->execute()) {
            $result = [];
            while($row = $stmt->fetch(\TYPO3\CMS\Core\Database\PreparedStatement::FETCH_ASSOC)) {
                $result[] = [
                    'value' => $row['count'],
                    'label' => $row['title'],
                    'color' => $row['color']
                ];
            }
            return $result;
        }
        return [];
    }

    /**
     * Assigns the only template based HTML view.
     */
    protected function initializeInfoAction()
    {
        $this->defaultViewObjectName = TemplateView::class;
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
        $this->view->assign('value', ['success' => $updated]);
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

        /** @var PlanningUtility $planningUtility */
        $planningUtility = GeneralUtility::makeInstance(PlanningUtility::class);

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
                'description' => $session ['description'],
                'speakers' => $planningUtility->getSpeakers($session['uid'])
            );
        }
        $this->view->assign('value', $result);
    }


    /**
     * Get all rooms for FullCalendar
     */
    public function listRoomsAction()
    {
        $result = array_map(
            function(Room $room) {
                return array_merge(
                    $room->jsonSerialize(),
                    ['id' => $room->getUid()]
                );
            },
            $this->roomRepository->findAll()->toArray()
        );
        $this->view->assign('value', $result);
    }

    public function initializeUpdateSessionAction()
    {
        $this->adjustSessionPropertyMappingConfiguration($this->resourceArgumentName);

        if ($this->request->hasArgument('secondSession')) {
            $this->adjustSessionPropertyMappingConfiguration('secondSession');
        }
    }

    /**
     * @param \TYPO3\Sessions\Domain\Model\ScheduledSession $session
     * @return string
     */
    public function unscheduleSessionAction(ScheduledSession $session)
    {
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $db */
        $db = $GLOBALS['TYPO3_DB'];
        $res = $db->exec_UPDATEquery('tx_sessions_domain_model_session', 'uid = '.$session->getUid(),
            ['type' => \TYPO3\Sessions\Domain\Model\AcceptedSession::class]);
        return 'success';
    }

    public function initializeUnscheduleSessionAction()
    {
        $this->adjustSessionPropertyMappingConfiguration($this->resourceArgumentName);
    }

    public function initializeScheduleSessionAction()
    {
        $this->adjustSessionPropertyMappingConfiguration($this->resourceArgumentName);
    }

    /*public function errorAction(){
        var_dump($this->request->getOriginalRequestMappingResults()->forProperty('session')->getFlattenedErrors());
    }*/

    /**
     * @param \TYPO3\Sessions\Domain\Model\AcceptedSession $session
     * @validate $session \TYPO3\Sessions\Domain\Validator\SpeakerCollisionValidator
     * @return string
     */
    public function scheduleSessionAction(\TYPO3\Sessions\Domain\Model\AcceptedSession $session)
    {
        // update properties
        $this->acceptedSessionRepository->update($session);
        /** @var PersistenceManager $persistenceManager */
        $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
        $persistenceManager->persistAll();
        // change type manually after extbase updated the object
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $db */
        $db = $GLOBALS['TYPO3_DB'];
        $res = $db->exec_UPDATEquery('tx_sessions_domain_model_session', 'uid = '.$session->getUid(),
            ['type' => ScheduledSession::class]);

        return 'success';
    }

    /**
     * Update session
     *
     * @param \TYPO3\Sessions\Domain\Model\ScheduledSession $session
     * @validate $session \TYPO3\Sessions\Domain\Validator\SpeakerCollisionValidator
     * @return string
     */
    public function updateSessionAction($session)
    {
        $this->sessionRepository->update($session);
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
     * @param $errors array
     * @return string
     */
    protected function customErrorAction($errors)
    {
        $response = ['errors' => []];
        foreach($errors as $error) {
            $response['errors'][] = [
                'title' => $error
            ];
        }
        if($this->response instanceof \TYPO3\CMS\Extbase\Mvc\Web\Response) {
            $this->response->setStatus(400);
        }
        $this->view->assign('value', $response);
    }

    /**
     * The error action basically handles validation errors.
     *
     * Because there might be a various number, the result always is a collection
     * of errors.
     * Since there might be other errors than those related to the actual resource
     * argument, the error collection is encapsulated in a meta container which
     * also names the property name of the resource argument.
     *
     * E.g.:
     *
     * return [
     *     'errors' => [
     *         // That's the resource this RESTfull controller targets
     *         [
     *             'code' => '1387390192',
     *             'title' => 'You are not allowed to update comments without changes.',
     *             'source' => ['pointer' => 'comment'],
     *         ],
     *         // That's some other action argument but not the actual resource.
     *         [
     *             'code' => '123456789',
     *             'message' => 'Order only allows "ASC" and "DESC" but "ANY" given.',
     *             'source' => ['parameter' => 'sort'],
     *         ],
     *     ],
     * ];
     *
     * @return string
     */
    protected function errorAction()
    {

        $response = ['errors' => []];

        foreach ($this->arguments->getValidationResults()->getFlattenedErrors() as $fullQualifiedPropertyPath => $propertyErrors) {
            /** @var \TYPO3\CMS\Extbase\Error\Error $propertyError */
            foreach ($propertyErrors as $propertyError) {
                $response['errors'][] = [
                    'code' => $propertyError->getCode(),
                    'title' => $propertyError->render(),
                    'source' => ['pointer' => $fullQualifiedPropertyPath],
                ];
            }
        }

        if($this->response instanceof \TYPO3\CMS\Extbase\Mvc\Web\Response) {
            $this->response->setStatus(400);
        }

        $this->view->assign('value', $response);
    }

    /*
     *  INJECTIONS
     */

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

    /**
     * @param \TYPO3\Sessions\Domain\Repository\AcceptedSessionRepository $acceptedSessionRepository
     */
    public function injectAcceptedSessionRepository(\TYPO3\Sessions\Domain\Repository\AcceptedSessionRepository $acceptedSessionRepository)
    {
        $this->acceptedSessionRepository = $acceptedSessionRepository;
    }

}
