<?php
namespace TYPO3\Sso\Security\Authentication\Token;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\TypeHandlingUtility;

class Typo3OrgSsoToken
{

    /**
     * @var array
     */
    protected $credentials = array('username' => '', 'signature' => '');

    /**
     * @var bool
     */
    protected $valid = false;

    /**
     * Updates the username and password credentials from the POST vars, if the POST parameters are available.
     *
     * @return void
     */
    public function updateCredentials() {
        foreach (['version', 'user', 'tpa_id', 'expires', 'action', 'flags', 'userdata', 'signature'] as $argumentName) {
            $getArguments[$argumentName] = GeneralUtility::_GP($argumentName);
        }

        if (!empty($getArguments['user'])
            && !empty($getArguments['signature'])
            && !empty($getArguments['expires'])
            && !empty($getArguments['version'])
            && !empty($getArguments['tpa_id'])
            && !empty($getArguments['action'])
            && !empty($getArguments['flags'])
            && !empty($getArguments['userdata'])) {

            $this->credentials['username'] = $getArguments['user'];
            $this->credentials['signature'] = TypeHandlingUtility::hex2bin($getArguments['signature']);
            $this->credentials['expires'] = $getArguments['expires'];
            $this->credentials['version'] = $getArguments['version'];
            $this->credentials['tpaId'] = $getArguments['tpa_id'];
            $this->credentials['action'] = $getArguments['action'];
            $this->credentials['flags'] = $getArguments['flags'];
            $this->credentials['userdata'] = $getArguments['userdata'];
            $this->valid = true;
        }
    }

    /**
     * @return array $credentials The needed credentials to authenticate this token
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->valid;
    }

}