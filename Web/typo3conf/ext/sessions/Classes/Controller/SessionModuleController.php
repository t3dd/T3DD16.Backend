<?php
namespace TYPO3\Sessions\Controller;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\Sessions\Domain\Model\Session;

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
     * @var \TYPO3\Sessions\Domain\Repository\SessionRepository
     * @inject
     */
    protected $sessionRepository;

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
     * Initializes the module view.
     *
     * @param ViewInterface $view The view
     * @return void
     */
    protected function initializeView(ViewInterface $view)
    {
        $extPath = $this->getRelativeExtensionPath().'Resources/Public/CSS/';
        // Skip, if view is initialized in non-backend context
        if (!($view instanceof BackendTemplateView)) {
            return;
        }

        parent::initializeView($view);
        $view->getModuleTemplate()->getPageRenderer()->addCssFile($extPath.'fullcalendar.min.css');
        $view->getModuleTemplate()->getPageRenderer()->addCssFile($extPath.'scheduler.min.css');
//        $view->getModuleTemplate()->getPageRenderer()->loadRequireJsModule('TYPO3/CMS/Sessions/jquery.min');
//        $view->getModuleTemplate()->getPageRenderer()->loadRequireJsModule('TYPO3/CMS/Sessions/moment');
        $view->getModuleTemplate()->getPageRenderer()->loadRequireJsModule('TYPO3/CMS/Sessions/fullcalendar');
        $view->getModuleTemplate()->getPageRenderer()->loadRequireJsModule('TYPO3/CMS/Sessions/scheduler');
        $view->getModuleTemplate()->getPageRenderer()->loadRequireJsModule('TYPO3/CMS/Sessions/SessionModule');

        $this->generateModuleMenu();
        $this->generateModuleButtons();
    }

    /**
     * @return string
     */
    protected function getRelativeExtensionPath() {
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
                'generateFirstSchedule' => [
                        'controller' => 'SessionModule',
                        'action' => 'generateFirstSchedule',
                        'label' => $this->getLanguageService()->sL('LLL:EXT:sessions/Resources/Private/Language/locallang.xml:module.menu.item.generateFirstSchedule')
                ],
        ];

        $menu = $this->view->getModuleTemplate()->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier('BackendUserModuleMenu');

        foreach ($menuItems as  $menuItemConfig) {
            if ($this->request->getControllerName() === $menuItemConfig['controller']) {
                $isActive = $this->request->getControllerActionName() === $menuItemConfig['action'] ? true : false;
            } else {
                $isActive = false;
            }
            $menuItem = $menu->makeMenuItem()
                ->setTitle($menuItemConfig['label'])
                ->setHref($this->getHref($menuItemConfig['controller'], $menuItemConfig['action']))
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

    public function indexAction()
    {

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
        $sessionWhitelist = array('uid', 'title', 'date', 'begin', 'end', 'room');
        // Generate session data
        foreach($allSessionFlatArray as $session)
        {
            $tempSessionArray = array();
            foreach($sessionWhitelist as $property)
            {
                $tempSessionArray[$property] = $session[$property];
            }
            $result[] = $tempSessionArray;
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
            $result[] = array('uid' => $room['uid'], 'title' => $room['title']);
        }
        return json_encode($result);

    }

    public function initializeUpdateSessionAction()
    {
        /** @var \TYPO3\CMS\Extbase\Mvc\Controller\MvcPropertyMappingConfiguration $propertyMappingConfiguration */
        $propertyMappingConfiguration = $this->arguments[$this->resourceArgumentName]->getPropertyMappingConfiguration();
        $propertyMappingConfiguration->setTypeConverterOption(PersistentObjectConverter::class,
            PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED, true);
        $propertyMappingConfiguration->allowProperties('date', 'begin', 'end', 'room', 'title');
        $propertyMappingConfiguration->skipUnknownProperties();
    }

    /*public function errorAction(){
        var_dump($this->request->getOriginalRequestMappingResults()->forProperty('session')->getFlattenedErrors());
    }*/

    /**
     * Update session
     *
     * @param \TYPO3\Sessions\Domain\Model\Session $session
     * @return String
     */
    public function updateSessionAction($session)
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

        var_dump($session);die;
    }

    public function generateFirstScheduleAction()
    {

    }

    /**
     * @param array $config
     * @param boolean $considerTopics
     * @param integer $iterations
     */
    public function createTimeTableAction($config, $considerTopics, $iterations)
    {
	    // Get all sessions
	    $sessions = $this->sessionRepository->findAll()->toArray();
	    // Get all rooms
	    $rooms = $this->roomRepository->findAllLimited(6)->toArray();
	    // Generate timetable with service
	    $success = $this->createTimetableService->generateTimetable($config, $sessions, $rooms, $iterations, $considerTopics);
	    $incompleteSessions = array();
	    if(!$success)
	    {
		    $incompleteSessions = $this->createTimetableService->getUnassignedSessions();
	    }

	    // Save changes on sessions
	    foreach($this->createTimetableService->getAssignedSessions() as $assignedSession)
	    {
		    $this->sessionRepository->update($assignedSession);
	    }

	    $this->redirect('index', '', '', array('incompleteSessions' => $incompleteSessions, 'creationDone' => true));
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
     * Generates first session timetable
     *
     * @param array $roomAndTimeData
     * @param boolean $considerTopics
     * @param array $timeSlots
     * @param array $dates
     * @return array
     */
    protected function generateTimetable($roomAndTimeData, $considerTopics, $timeSlots, $dates)
    {
        // Generate matrix
        $matrix = array();
        foreach($roomAndTimeData as $dayKey => $day)
        {
            for($i = 0; $i < $day['timeSlots']; $i++)
            {
                for($j = 0; $j < $day['rooms']; $j++)
                {
                    $matrix[$dayKey][$i][] = "";
                }
            }
        }
        // Get all sessions
        $sessions = $this->sessionRepository->findAll();
	    $incompleteSessions = array();

	    // Try to find a spot for each session
        foreach($sessions as $session)
        {
            $success = $this->findSpot($matrix, $session, $considerTopics, $matrix);
	        if(!$success)
	        {
		        $incompleteSessions[] = $session;
	        }
            //TODO: Wenn !$success, $session in Array speichern und später nochmal versuchen (Auslassen) --> Sinnvoll bei Topic True
	        // Problem: Verteilung kann auf den Schluss zu, zu Engpässen führen...
	        // z.B. Speaker 2 hat 2 Vorträge mit ganz schlechte Votes und es sind nur noch Sonntag früh frei...
        }
	    // TODO: Wenn Elemente in incompleteSessions vorhanden sind, dann die ersten 8 Sessions shuffeln und neuen Durchlauf starten
	    // Maximal "2" Durchläufe (danach evtl Anzahl von Shuffle Sessions um 2 erhöhen...) <-- Worst-Case
	    // Maximale Anzahl von Durchläufen als Variable mitgeben

	    // TODO: Generate Date and Time for Sessions (Global time array {1: {begin: zeit1, end: zeit2}, 2:...} Global date array {1: tag1, 2: tag2,...} <-- eigentlich reicht erster Tag
	    // Service Klasse anlegen und Klassenvariblen für Config-Sachen anlegen

        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($matrix);die;
	    return $incompleteSessions;
    }

    /**
     * Check for free spot for given session
     *
     * @param array $matrix
     * @param \TYPO3\Sessions\Domain\Model\Session $session
     * @param boolean $considerTopics
     * @param integer $dayIterator
     * @param integer $roomIterator
     * @param integer $timeIterator
     * @return boolean
     */
    protected function findSpot(&$matrix, &$session, $considerTopics, $dayIterator = 0, $roomIterator = 0, $timeIterator = -1)
    {
	    // Erster Aufruf von außerhalb
	    if($timeIterator == -1)
	    {
		    $timeIterator = count($matrix[$dayIterator]) - 1;
	    }

        if($matrix[$dayIterator][$timeIterator][$roomIterator] == '')
        {
            // Freier Slot, check andere Räume nach Speaker und Topic
            foreach($matrix[$dayIterator][$timeIterator] as $otherRoom)
            {
	            // Anderer Raum noch frei
	            if($otherRoom == '')
	            {
		            continue;
	            }
	            else
	            {
		            $sessionSpeakers = $session->getSpeakers()->toArray();
		            $countSessionSpeakers = count($sessionSpeakers);
		            /**
		             * @var Session $otherRoom
		             */
		            $otherSessionSpeakers = $otherRoom->getSpeakers()->toArray();
		            $countOtherSessionSpeakers = count($otherSessionSpeakers);
		            $uniqueArrayCount = count(array_merge($sessionSpeakers, $otherSessionSpeakers));
		            // Speaker nicht frei
		            if ($uniqueArrayCount != ($countSessionSpeakers + $countOtherSessionSpeakers))
		            {
			            break;
		            }
		            else
		            {
			            // Consider topics
			            if($considerTopics)
			            {
				            $countDev = 0;
				            $countDevOps = 0;
				            $countDesign = 0;
				            $countCommunity = 0;
				            // Topics von allen Räumen abfragen
				            foreach($matrix[$dayIterator][$timeIterator] as $otherRoom2)
				            {
					            if($otherRoom2 == '')
					            {
						            continue;
					            }
					            else
					            {
						            /**
						             * @var Session $otherRoom2
						             */
						            switch($otherRoom2->getTopics()->getTopicGroup()->getTitle())
						            {
							            case 'Dev':
								            $countDev++;
							            break;
							            case 'DevOps':
								            $countDevOps++;
							            break;
							            case 'Design':
								            $countDesign++;
							            break;
							            case 'Community':
								            $countCommunity++;
							            break;
						            }
					            }
				            }

				            $sessionApproved = false;
				            switch($session->getTopics()->getTopicGroup()->getTitle())
				            {
					            case 'Dev':
									if($countDev < 4)
										$sessionApproved = true;
					            break;
					            case 'DevOps':
						            if($countDevOps < 3)
							            $sessionApproved = true;
					            break;
					            case 'Design':
						            if($countDesign < 3)
							            $sessionApproved = true;
					            break;
					            case 'Community':
						            if($countDesign < 2)
							            $sessionApproved = true;
					            break;
				            }

				            if(!$sessionApproved)
				            {
					            break;
				            }

			            }

			            // Slot gefunden
			            // TODO: Zuweisen von Datum und Zeit
			            $matrix[$dayIterator][$timeIterator][$roomIterator] = $session;
			            return true;
		            }
	            }
            }
        }

        // Kein Freier Slot gefunden, check nächsten Tag
	    // Kein Tag mehr da, geh auch nächste Zeit
	    // Keine Zeit mehr frei, geh auf nächsten Raum
        $dayIterator++;
        if($dayIterator == count($matrix[$dayIterator]))
        {
            $dayIterator = 0;
            $timeIterator--;
            if($timeIterator == -1)
            {
                $timeIterator = count($matrix[$dayIterator]) - 1;
	            $roomIterator++;
	            // Kein Slot gefunden -> Abbruch
	            if($roomIterator == count($matrix[$dayIterator][$timeIterator]))
	            {
		            return false;
	            }
            }
        }

        $this->findSpot($matrix, $session, $considerTopics, $dayIterator, $roomIterator, $timeIterator);

    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

}