<?php
namespace TYPO3\Sessions\Controller;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Backend\View\BackendTemplateView;
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
     * @var BackendTemplateView
     */
    protected $view;

    /**
     * BackendTemplateView Container
     *
     * @var BackendTemplateView
     */
    protected $defaultViewObjectName = BackendTemplateView::class;

    /**
     * Mapping between slugs and concrete classes
     * @var array
     */
    protected $slugClassMap = [
        'proposed' => \TYPO3\Sessions\Domain\Model\ProposedSession::class,
        'declined' => \TYPO3\Sessions\Domain\Model\DeclinedSession::class,
        'accepted' => \TYPO3\Sessions\Domain\Model\AcceptedSession::class,
    ];

    /**
     * Blacklist for actions which don't want/need the menu
     * @var array
     */
    protected $actionsWithoutMenu = [];

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
        if($this->actionMethodName === 'indexAction') {
            $view->getModuleTemplate()->getPageRenderer()->addCssFile($extPath.'fullcalendar.min.css');
            $view->getModuleTemplate()->getPageRenderer()->addCssFile($extPath.'scheduler.min.css');
            $view->getModuleTemplate()->getPageRenderer()->loadRequireJsModule('TYPO3/CMS/Sessions/fullcalendar');
            $view->getModuleTemplate()->getPageRenderer()->loadRequireJsModule('TYPO3/CMS/Sessions/scheduler');
            $view->getModuleTemplate()->getPageRenderer()->loadRequireJsModule('TYPO3/CMS/Sessions/SessionModule');
            $view->getModuleTemplate()->getPageRenderer()->addRequireJsConfiguration([
                'shim'  => [
                    'TYPO3/CMS/Sessions/scheduler' => [
                        'deps'  =>  ['TYPO3/CMS/Sessions/fullcalendar']
                    ]
                ]
            ]);
        }

        if($this->actionMethodName === 'acceptanceAction') {
            $view->getModuleTemplate()->getPageRenderer()->addCssFile($extPath.'sma.css');
        }

        if(!in_array($this->actionMethodName, $this->actionsWithoutMenu)) {
            $this->generateModuleMenu();
            $this->generateModuleButtons();
        }
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
                'acceptance' => [
                        'controller' => 'SessionModule',
                        'action' => 'acceptance',
                        'parameters' => [
                            'type' => 'proposed'
                        ],
                        'label' => $this->getLanguageService()->sL('LLL:EXT:sessions/Resources/Private/Language/locallang.xml:module.menu.item.acceptance')
                ]
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
            if(!isset($menuItemConfig['parameters'])) {
                $menuItemConfig['parameters'] = [];
            }
            $menuItem = $menu->makeMenuItem()
                ->setTitle($menuItemConfig['label'])
                ->setHref($this->getHref($menuItemConfig['controller'], $menuItemConfig['action'], $menuItemConfig['parameters']))
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
     *
     * @param \TYPO3\Sessions\Domain\Model\AnySession $session
     * @return string
     */
    public function infoAction($session)
    {
        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $view */
        $view = $this->objectManager->get(\TYPO3\CMS\Fluid\View\StandaloneView::class);
        $view->setTemplatePathAndFilename(\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:sessions/Resources/Private/Templates/SessionModule/Info.html'));
        $view->assign('session', $session);
        return $view->render();
    }

    /**
     * @param int $id
     * @param string $type
     * @return string
     */
    public function updatesessiontypeAction($id, $type)
    {
        if(!in_array($type, array_keys($this->slugClassMap))) {
            throw new \InvalidArgumentException('type parameter must be one of the folloging: '.implode(array_keys($this->slugClassMap)));
        }
        $id = (int) $id;
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $db */
        $db = $GLOBALS['TYPO3_DB'];
        $updated = $db->exec_UPDATEquery('tx_sessions_domain_model_session', "uid = {$id}", ['type' => $this->slugClassMap[$type]]);
        if($this->response instanceof \TYPO3\CMS\Extbase\Mvc\Web\Response) {
            $this->response->setHeader('Content-Type', 'application/json', true);
        }
        return json_encode(['success' => $updated]);
    }

    /**
     * @param string $type = 'proposed'
     * @throws \InvalidArgumentException
     */
    public function acceptanceAction($type = 'proposed')
    {
        if(!in_array($type, array_keys($this->slugClassMap))) {
            throw new \InvalidArgumentException('type parameter must be one of the folloging: '.implode(array_keys($this->slugClassMap)));
        }
        $this->view->assign('samModuleConfig', json_encode([
            'updateUrl' => $this->getHref('SessionModule', 'updatesessiontype', [
                'id' => '###id###',
                'type' => '###type###'
            ])
        ]));
        $this->view->assign('type', $type);
        $this->view->assign('sessions', $this->getFlatSessionObjects($type));
    }

    /**
     *
     */
    protected function getFlatSessionObjects($type)
    {
        $sessions = [];
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $db */
        $db = $GLOBALS['TYPO3_DB'];
        $stmt = $db->prepare_SELECTquery('uid AS __identity, title, description, votes',
            'tx_sessions_domain_model_session',
            ' type = :type AND deleted = 0 '.\TYPO3\CMS\Backend\Utility\BackendUtility::BEenableFields('tx_sessions_domain_model_session'),
            '', ' votes DESC ', '', [':type' => $this->slugClassMap[$type]]);
        if($stmt->execute()) {
            while($row = $stmt->fetch(\TYPO3\CMS\Core\Database\PreparedStatement::FETCH_ASSOC)) {
                $sessions[] = $row;
            }
            $stmt->free();
        }
        return $sessions;
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
