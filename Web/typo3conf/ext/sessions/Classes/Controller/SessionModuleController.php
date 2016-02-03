<?php
namespace TYPO3\Sessions\Controller;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

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
//                'test' => [
//                        'controller' => 'SessionModule',
//                        'action' => 'test',
//                        'label' => $this->getLanguageService()->sL('LLL:EXT:sessions/Resources/Private/Language/locallang.xml:module.menu.item.test')
//                ],
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

    public function demoAction()
    {
        $session = $this->sessionRepository->findByUid(1);
        $this->view->assign('session', $session);
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

}