<?php
namespace TYPO3\Sessions\Controller;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

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

}