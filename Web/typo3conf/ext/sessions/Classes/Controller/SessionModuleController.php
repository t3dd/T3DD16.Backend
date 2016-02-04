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
        $view->getModuleTemplate()->getPageRenderer()->addRequireJsConfiguration([
            'shim'  => [
                'TYPO3/CMS/Sessions/scheduler' => [
                    'deps'  =>  ['TYPO3/CMS/Sessions/fullcalendar']
                ]
            ]
        ]);

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
                'acceptance' => [
                        'controller' => 'SessionModule',
                        'action' => 'acceptance',
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

    public function acceptanceAction()
    {
        $objM = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        /** @var \TYPO3\Sessions\Domain\Repository\AcceptedSessionRepository $accSessRepo */
        $accSessRepo = $objM->get('TYPO3\Sessions\Domain\Repository\AcceptedSessionRepository');
        /** @var \TYPO3\Sessions\Domain\Repository\SessionRepository $sessRepo */
        $sessRepo = $objM->get('TYPO3\Sessions\Domain\Repository\SessionRepository');
        $this->view->assign('sessions', [
            'proposed' => $sessRepo->findAll(),
            'accepted'  =>  $accSessRepo->findAll()
        ]);
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
