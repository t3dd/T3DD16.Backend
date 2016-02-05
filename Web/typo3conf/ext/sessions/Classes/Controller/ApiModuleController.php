<?php

namespace TYPO3\Sessions\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
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
            throw new \InvalidArgumentException('type parameter must be one of the folloging: '.implode(array_keys(self::$slugClassMap)));
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

}
