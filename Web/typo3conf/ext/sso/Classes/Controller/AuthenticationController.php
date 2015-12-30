<?php
namespace TYPO3\Sso\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\Sso\Security\Authentication\Token\Typo3OrgSsoToken;
use TYPO3\Sso\View\JsonView;

class AuthenticationController extends ActionController
{
    protected $defaultViewObjectName = JsonView::class;

    /**
     * @var \TYPO3\Sso\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $frontendUserRepository;

    /**
     * @var \TYPO3\Sso\Security\Authentication\Provider\Typo3OrgSsoProvider
     * @inject
     */
    protected $typo3OrgSsoProvider;

    /**
     * @var \TYPO3\Sso\Service\AuthenticationService
     * @inject
     */
    protected $authenticationService;

    public function loginAction()
    {
        $user = $this->frontendUserRepository->findCurrentUser();
        if ($user !== null) {
            $uri = $this->uriBuilder->reset()->setTargetPageType('1450887489')->setCreateAbsoluteUri(true)->uriFor('me');
        } else {
            $uri = $this->settings['ssoURL'];
        }

        $this->redirectToUri($uri);
    }

    public function initializeAuthenticateAction()
    {
        $token = new Typo3OrgSsoToken();
        $token->updateCredentials();
        $this->request->setArgument('token', $token);
    }

    /**
     * @param $token Typo3OrgSsoToken
     * @throws StopActionException
     */
    public function authenticateAction(Typo3OrgSsoToken $token)
    {
        if ($token->isValid() && $this->typo3OrgSsoProvider->authenticate($token)) {
            $this->response->setStatus(200);
            $this->response->setContent('<html><head><script>window.close();</script></head></html>');
            throw new StopActionException();
        }
    }

    public function logoutAction()
    {
        $user = $this->frontendUserRepository->findCurrentUser();
        if ($user !== null) {
            $this->authenticationService->unregisterSession();
        }
    }

    public function meAction()
    {
        $user = $this->frontendUserRepository->findCurrentUser();
        if ($user !== null) {
            $this->view->assign('value', $user);
        } else {
            $this->response->setStatus(401);
            $uri = $this->uriBuilder->reset()->setTargetPageType('1450887489')->setCreateAbsoluteUri(true)->uriFor('login');
            $this->view->assign('value', ['loginUrl' => $uri]);
        }
    }

}