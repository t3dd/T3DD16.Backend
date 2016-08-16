<?php
namespace TYPO3\Sessions\Controller;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\Sessions\Domain\Model\AcceptedSession;
use TYPO3\Sessions\Domain\Model\ScheduledSession;

/**
 * Class SessionModuleController
 * @package TYPO3\Sessions\Controller
 */
class SessionModuleController extends ActionController
{

    /**
     * @var string
     */
    protected $resourceArgumentName = 'session';

    /**
     * @var BackendTemplateView
     */
    protected $view;

    /**
     * @var \TYPO3\Sessions\Domain\Repository\ScheduledSessionRepository
     * @inject
     */
    protected $sessionRepository;

    /**
     * @var \TYPO3\Sessions\Domain\Repository\AcceptedSessionRepository
     * @inject
     */
    protected $acceptedSessionRepository;

    /**
     * @var \TYPO3\Sessions\Domain\Repository\AnySessionRepository
     * @inject
     */
    protected $anySessionRepository;

    /**
     * @var \TYPO3\Sessions\Domain\Repository\ScheduledSessionRepository
     * @inject
     */
    protected $scheduledSessionRepository;

    /**
     * @var \TYPO3\Sessions\Service\CreateTimetableService
     * @inject
     */
    protected $createTimetableService;

    /**
     * @var \TYPO3\Sessions\Domain\Repository\RoomRepository
     * @inject
     */
    protected $roomRepository;

    /**
     * BackendTemplateView Container
     *
     * @var BackendTemplateView
     */
    protected $defaultViewObjectName = BackendTemplateView::class;

    /**
     * Blacklist for actions which don't want/need the menu
     * @var array
     */
    protected $actionsWithoutMenu = [];

    /**
     * @var \TYPO3\Sessions\Utility\PlanningUtility
     */
    protected $utility;

    /**
     * Initializes the module view.
     *
     * @param ViewInterface $view The view
     * @return void
     */
    protected function initializeView(ViewInterface $view)
    {
        $extPath = $this->getRelativeExtensionPath() . 'Resources/Public/CSS/';
        // Skip, if view is initialized in non-backend context
        if (!($view instanceof BackendTemplateView)) {
            return;
        }

        parent::initializeView($view);
        if ($this->actionMethodName === 'indexAction') {
            $view->getModuleTemplate()->getPageRenderer()->addCssFile($extPath . 'fullcalendar.min.css');
            $view->getModuleTemplate()->getPageRenderer()->addCssFile($extPath . 'scheduler.min.css');
            $view->getModuleTemplate()->getPageRenderer()->addCssFile($extPath . 'index.css');
            $view->getModuleTemplate()->getPageRenderer()->loadRequireJsModule('TYPO3/CMS/Sessions/Contrib/fullcalendar');
            $view->getModuleTemplate()->getPageRenderer()->loadRequireJsModule('TYPO3/CMS/Sessions/Contrib/scheduler');
            $view->getModuleTemplate()->getPageRenderer()->addRequireJsConfiguration([
                'paths' => [
                    'sightglass' => $this->getRelativeExtensionPath() . 'Resources/Public/JavaScript/Contrib/sightglass'
                ],
                'shim' => [
                    'TYPO3/CMS/Sessions/Contrib/scheduler' => [
                        'deps' => ['TYPO3/CMS/Sessions/Contrib/fullcalendar']
                    ],
                    'TYPO3/CMS/Sessions/Contrib/rivets' => [
                        'deps' => ['sightglass']
                    ]
                ]
            ]);
        }

        if ($this->actionMethodName === 'manageAction') {
            $view->getModuleTemplate()->getPageRenderer()->addCssFile($extPath . 'manage.css');
            $view->getModuleTemplate()->getPageRenderer()->loadRequireJsModule('TYPO3/CMS/Sessions/Contrib/uri-templates');
        }

        if (!in_array($this->actionMethodName, $this->actionsWithoutMenu)) {
            $this->generateModuleMenu();
            $this->generateModuleButtons();
        }
    }

    /**
     * @return string
     */
    protected function getRelativeExtensionPath()
    {
        return ExtensionManagementUtility::extRelPath('sessions');
    }

    /**
     * Generates module menu.
     */
    protected function generateModuleMenu()
    {
        $menuItems = [
            'index' => [
                'controller' => 'SessionModule',
                'action' => 'index',
                'label' => $this->getLanguageService()->sL('LLL:EXT:sessions/Resources/Private/Language/locallang.xml:module.menu.item.calendar')
            ],
            'manage' => [
                'controller' => 'SessionModule',
                'action' => 'manage',
                'parameters' => [
                    'type' => 'proposed'
                ],
                'label' => $this->getLanguageService()->sL('LLL:EXT:sessions/Resources/Private/Language/locallang.xml:module.menu.item.manage')
            ],
            'generateFirstSchedule' => [
                'controller' => 'SessionModule',
                'action' => 'generateFirstSchedule',
                'label' => $this->getLanguageService()->sL('LLL:EXT:sessions/Resources/Private/Language/locallang.xml:module.menu.item.generateFirstSchedule')
            ],
        ];

        $menu = $this->view->getModuleTemplate()->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier('BackendUserModuleMenu');

        foreach ($menuItems as $menuItemConfig) {
            if ($this->request->getControllerName() === $menuItemConfig['controller']) {
                $isActive = $this->request->getControllerActionName() === $menuItemConfig['action'] ? true : false;
            } else {
                $isActive = false;
            }
            if (!isset($menuItemConfig['parameters'])) {
                $menuItemConfig['parameters'] = [];
            }
            $menuItem = $menu->makeMenuItem()
                ->setTitle($menuItemConfig['label'])
                ->setHref($this->getHref($menuItemConfig['controller'], $menuItemConfig['action'],
                    $menuItemConfig['parameters']))
                ->setActive($isActive);
            $menu->addMenuItem($menuItem);
        }

        $this->view->getModuleTemplate()->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);
    }

    /**
     * Generates module buttons.
     *
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    protected function generateModuleButtons()
    {
        $buttonBar = $this->view->getModuleTemplate()->getDocHeaderComponent()->getButtonBar();
        $moduleName = $this->request->getPluginName();
        $getVars = $this->request->hasArgument('getVars') ? $this->request->getArgument('getVars') : [];
        $setVars = $this->request->hasArgument('setVars') ? $this->request->getArgument('setVars') : [];
        if (count($getVars) === 0) {
            $modulePrefix = strtolower('tx_' . $this->request->getControllerExtensionName() . '_' . $moduleName);
            $getVars = array('id', 'M', $modulePrefix);
        }
        $shortcutButton = $buttonBar->makeShortcutButton()
            ->setModuleName($moduleName)
            ->setGetVariables($getVars)
            ->setDisplayName('Sessions')
            ->setSetVariables($setVars);
        $buttonBar->addButton($shortcutButton);
    }

    /**
     *
     * @param array $incompleteSessions
     * @param boolean $creationDone
     */
    public function indexAction($incompleteSessions = array(), $creationDone = false)
    {
        $days = $this->utility->getDaysArray($this->settings['dd']['start'], $this->settings['dd']['end']);

        // manually grab accepted sessions since repository will return scheduled sessions as well (which is correct
        // according to domain modelling)
        $this->view->assign('unassigned', $this->getFlatSessionObjects('accepted'));

        $this->view->assign('jsconf', json_encode([
            'days' => $days,
            'links' => [
                'getsessions' => $this->getHref('ApiModule', 'listSessions'),
                'getrooms' => $this->getHref('ApiModule', 'listRooms'),
                'updatesession' => $this->getHref('ApiModule', 'updateSession'),
                'schedulesession' => $this->getHref('ApiModule', 'scheduleSession'),
                'unschedulesession' => $this->getHref('ApiModule', 'unscheduleSession'),
                'swapsessions' => $this->getHref('ApiModule', 'swapSessions'),
                'analyze' => $this->getHref('ApiModule', 'analyze', ['start' => '{start}', 'end' => '{end}'])
            ]
        ]));

        $this->view->assignMultiple(array(
            'incompleteSessions' => $incompleteSessions,
            'creationDone' => $creationDone
        ));
    }

    public function initializeIndexAction()
    {
        $this->checkAndTransformTypoScriptConfiguration();
    }

    protected function checkAndTransformTypoScriptConfiguration()
    {
        if (empty($this->settings['dd']['start']) || ($start = date_create($this->settings['dd']['start'])) === false) {
            throw new \TYPO3\CMS\Core\Resource\Exception\InvalidConfigurationException('Please check your TypoScript Configuration and set \'settings.dd.start\' to a valid date');
        }
        $this->settings['dd']['start'] = $start->setTime(0, 0, 0);
        if (empty($this->settings['dd']['end']) || ($end = date_create($this->settings['dd']['end'])) === false) {
            throw new \TYPO3\CMS\Core\Resource\Exception\InvalidConfigurationException('Please check your TypoScript Configuration and set \'settings.dd.start\' to a valid date');
        }
        $this->settings['dd']['end'] = $end->setTime(23, 59, 59);
        if ($this->settings['dd']['start'] > $this->settings['dd']['end']) {
            throw new \TYPO3\CMS\Core\Resource\Exception\InvalidConfigurationException('Developer Days should have started before ending (\'settings.dd.start\' is before \'settings.dd.end\')');
        }
    }

    /**
     * @param string $type = 'proposed'
     * @throws \InvalidArgumentException
     */
    public function manageAction($type = 'proposed')
    {
        if (!in_array($type, array_keys(ApiModuleController::$slugClassMap))) {
            throw new \InvalidArgumentException('type parameter must be one of the following: ' . implode(array_keys(ApiModuleController::$slugClassMap)));
        }
        $this->view->assign('manageConfig', json_encode([
            'updateUrl' => $this->getHref('ApiModule', 'toggle', [
                'id' => '{id}',
                'type' => '{type}'
            ])
        ]));
        $this->view->assign('type', $type);
        $this->view->assign('sessions', $this->getFlatSessionObjects($type));
    }

    /**
     * Fetches a simple array of sessions (with vote count not being transformed into an objectstorage)
     * for a simple list view.
     * @param $type
     * @return array
     */
    protected function getFlatSessionObjects($type)
    {
        $sessions = [];
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $db */
        $db = $GLOBALS['TYPO3_DB'];
        $stmt = $db->prepare_SELECTquery(
            'uid AS __identity, title, description, highlight, '
            . '(SELECT COUNT(tx_sessions_domain_model_vote.uid) '
            . 'FROM tx_sessions_domain_model_vote '
            . 'WHERE tx_sessions_domain_model_vote.session=tx_sessions_domain_model_session.uid) as votes',
            'tx_sessions_domain_model_session',
            'type = :type AND deleted = 0 '
            . BackendUtility::BEenableFields('tx_sessions_domain_model_session'),
            '', 'votes DESC ', '',
            [
                ':type' => ApiModuleController::$slugClassMap[$type]
            ]
        );
        if ($stmt->execute()) {
            while ($row = $stmt->fetch(\TYPO3\CMS\Core\Database\PreparedStatement::FETCH_ASSOC)) {
                $row['speakers'] = $this->utility->getSpeakers($row['__identity']);
                $row['json'] = json_encode($row);
                $sessions[] = $row;
            }
            $stmt->free();
        }
        return $sessions;
    }

    public function generateFirstScheduleAction()
    {

    }

    /**
     * Generate first timetable for sessions
     *
     * @param boolean $considerTopics
     * @param integer $iterations
     *
     * @validate $iterations NumberRangeValidator(minimum = 1, maximum = 20)
     */
    public function createTimeTableAction($considerTopics, $iterations)
    {
        // Generate Config Array
        // TODO: Extract to options or something else
        // Room and time slots
        $config['roomAndTimeData'][0]['timeSlots'] = 2;
        $config['roomAndTimeData'][0]['rooms'] = 6;
        $config['roomAndTimeData'][1]['timeSlots'] = 3;
        $config['roomAndTimeData'][1]['rooms'] = 6;
        $config['roomAndTimeData'][2]['timeSlots'] = 3;
        $config['roomAndTimeData'][2]['rooms'] = 6;
        $config['roomAndTimeData'][3]['timeSlots'] = 1;
        $config['roomAndTimeData'][3]['rooms'] = 6;
        // Begin and end of time slots
        $config['timeSlots'][0]['begin'] = "09:30";
        $config['timeSlots'][0]['end'] = "11:00";
        $config['timeSlots'][1]['begin'] = "14:00";
        $config['timeSlots'][1]['end'] = "15:30";
        $config['timeSlots'][2]['begin'] = "16:30";
        $config['timeSlots'][2]['end'] = "18:00";
        // Dates of the event
        $config['dates'][] = "01.09.2016";
        $config['dates'][] = "02.09.2016";
        $config['dates'][] = "03.09.2016";
        $config['dates'][] = "04.09.2016";

        // TODO: Alle ScheduledSessions umwandeln in AcceptedSessions

        // Get all sessions
        $sessions = $this->acceptedSessionRepository->getAllOrderByVoteCount()->toArray();
        // Get all rooms
        $rooms = $this->roomRepository->findAllLimited(6)->toArray();
        // Generate timetable with service
        $success = $this->createTimetableService->generateTimetable($config, $sessions, $rooms, $iterations,
            $considerTopics);

        $incompleteSessions = array();
        if (!$success) {
            $incompleteSessions = $this->createTimetableService->getUnassignedSessions();
        }

        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $db */
        $db = $GLOBALS['TYPO3_DB'];
        // Save changes on sessions

        /** @var AcceptedSession $assignedSession*/
        foreach ($this->createTimetableService->getAssignedSessions() as $assignedSession) {
            $this->anySessionRepository->update($assignedSession, true);
            $db->exec_UPDATEquery(
                'tx_sessions_domain_model_session',
                'uid = ' . $assignedSession->getUid(),
                ['type' => ScheduledSession::class]
            );
        }

        $this->redirect('index', 'SessionModule', 'sessions', ['incompleteSessions' => $incompleteSessions, 'creationDone' => true]);
    }

    /*public function errorAction(){
        var_dump($this->request->getOriginalRequestMappingResults()->forProperty('session')->getFlattenedErrors());
    }*/

    public function testAction()
    {

    }

    /**
     * Creates te URI for a backend action
     *
     * @param string $controller
     * @param string $action
     * @param array $parameters
     * @return string
     */
    protected function getHref($controller, $action, $parameters = [])
    {
        $uriBuilder = $this->objectManager->get(UriBuilder::class);
        $uriBuilder->setRequest($this->request);
        return $uriBuilder->reset()->uriFor($action, $parameters, $controller);
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

    /**
     * @param \TYPO3\Sessions\Utility\PlanningUtility $utility
     */
    public function injectUtility(\TYPO3\Sessions\Utility\PlanningUtility $utility)
    {
        $this->utility = $utility;
    }

}
