<?php
namespace TYPO3\T3DD16\Hooks;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class TypoScriptFrontendHook implements SingletonInterface
{

    /**
     * Those request methods influence the cache identifier of the current page.
     *
     * If the given REQUEST_METHOD doesn't match any of those, the first of this
     * list is taken instead.
     *
     * @var array
     */
    protected $allowedRequestMethods = array('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS');

    /**
     * Those request formats influence the cache identifier of the current page.
     *
     * If the given ACCEPT header doesn't match any of those, the first of this
     * list is taken instead.
     *
     * @var array
     */
    protected $allowedRequestFormats = array('text/html', 'application/json', 'text/json');

    /**
     * Add the REQUEST_METHOD string to the cache identifier.
     *
     * @param array $params
     * @param TypoScriptFrontendController $typoScriptFrontendController
     */
    public function createHashBase(array &$params, TypoScriptFrontendController $typoScriptFrontendController)
    {

        $params['hashParameters']['REQUEST_METHOD'] = strtoupper($_SERVER['REQUEST_METHOD']);
        if (!in_array($params['hashParameters']['REQUEST_METHOD'], $this->allowedRequestMethods)) {
            $params['hashParameters']['REQUEST_METHOD'] = current($this->allowedRequestMethods);
        }

        $params['hashParameters']['HTTP_ACCEPT'] = current($this->allowedRequestFormats);
        foreach ($this->allowedRequestFormats as $allowedRequestFormat) {
            foreach (array('ACCEPT', 'HTTP_ACCEPT') as $acceptFieldNameOptions) {
                if (array_key_exists($acceptFieldNameOptions, $_SERVER) && strpos($_SERVER[$acceptFieldNameOptions],
                        $allowedRequestFormat) !== false
                ) {
                    $params['hashParameters']['HTTP_ACCEPT'] = $allowedRequestFormat;
                    break(2);
                }
            }
        }

    }

}