<?php
namespace TYPO3\Sso\Security\Authentication\Provider;

use TYPO3\Sso\Domain\Model\FrontendUser;
use TYPO3\Sso\Security\Authentication\Token\Typo3OrgSsoToken;

class Typo3OrgSsoProvider
{

    /**
     * @var \TYPO3\Sso\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $frontendUserRepository;

    /**
     * @var \TYPO3\Sso\Service\AuthenticationService
     * @inject
     */
    protected $authenticationService;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface
     * @inject
     */
    protected $persistenceManager;

    /**
     * @var \TYPO3\Sso\Configuration\ConfigurationManager
     * @inject
     */
    protected $configurationManager;

    /**
     * @param Typo3OrgSsoToken $authenticationToken
     * @return bool
     */
    public function authenticate(Typo3OrgSsoToken $authenticationToken)
    {
        /** @var $account FrontendUser */
        $account = null;
        $credentials = $authenticationToken->getCredentials();

        if (is_array($credentials) && isset($credentials['username'])) {
            $account = $this->frontendUserRepository->findOneByUsername($credentials['username']);
        }

        $authenticated = false;
        $authenticationData = 'version=' . $credentials['version'] . '&user=' . $credentials['username'] . '&tpa_id=' . $credentials['tpaId'] . '&expires=' . $credentials['expires'] . '&action=' . $credentials['action'] . '&flags=' . $credentials['flags'] . '&userdata=' . $credentials['userdata'];
        $authenticationDataIsValid = $this->verifySignature($authenticationData, $credentials['signature']);

        if ($authenticationDataIsValid && $credentials['expires'] > time()) {
            $userdata = $this->parseUserdata($credentials['userdata']);
            if (!is_object($account)) {
                $account = $this->createAccount($userdata);
                $this->frontendUserRepository->add($account);
            } elseif (is_object($account)) {
                $account = $this->updateAccount($account, $userdata);
                $this->frontendUserRepository->update($account);
            }
            $this->persistenceManager->persistAll();
            $this->authenticationService->registerSession($account);
            $authenticated = true;
        }

        return $authenticated;
    }

    /**
     * @param string $authenticationData
     * @param string $signature
     * @return bool
     */
    protected function verifySignature($authenticationData, $signature)
    {
        $publicKey = $this->configurationManager->getExtensionSetting('server.rsaPublicKey');
        $verifyResult = openssl_verify($authenticationData, $signature, $publicKey);
        return $verifyResult === 1;
    }

    /**
     * @param string $userdata
     * @return array
     */
    protected function parseUserdata($userdata)
    {
        $result = array();
        foreach (explode('|', base64_decode($userdata)) as $line) {
            list($key, $value) = explode('=', $line, 2);
            switch ($key) {
                case 'username':
                case 'name':
                case 'email':
                    $result[$key] = $value;
                    break;
                case 'tx_t3ouserimage_img_hash':
                    break;
            }
        }

        return $result;
    }

    /**
     * @param array $userdata
     * @return FrontendUser
     */
    protected function createAccount(array $userdata)
    {
        if (!isset($userdata['username'])) {
            return null;
        }

        $account = new FrontendUser();
        $account->setUsername($userdata['username']);
        $this->updateAccount($account, $userdata);

        return $account;
    }

    /**
     * @param FrontendUser $account
     * @param array $userdata
     * @return FrontendUser
     */
    protected function updateAccount(FrontendUser $account, array $userdata)
    {
        if (isset($userdata['name'])) {
            $account->setName($userdata['name']);
        }
        if (isset($userdata['email'])) {
            $account->setEmail($userdata['email']);
        }

        return $account;
    }

}