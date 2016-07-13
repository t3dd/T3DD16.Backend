<?php
namespace TYPO3\Sessions\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class AbstractRestController extends ActionController
{

    /**
     * @var string
     */
    protected $resourceArgumentName = 'object';

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface
     * @inject
     */
    protected $persistenceManager;

    /**
     * Convert POST, PUT and DELETE to user_int
     *
     * @return string
     */
    public function indexAction()
    {
        $contentObject = $this->configurationManager->getContentObject();
        if ($contentObject->getUserObjectType() === ContentObjectRenderer::OBJECTTYPE_USER) {
            $contentObject->convertToUserIntObject();

            return '';
        }
    }

    /**
     * @inheritdoc
     */
    protected function resolveActionMethodName()
    {
        $this->mapRawGetData();
        if ($this->request->getControllerActionName() === 'index') {
            $actionName = 'index';
            switch ($this->request->getMethod()) {
                case 'HEAD':
                case 'GET' :
                    $actionName = ($this->request->hasArgument($this->resourceArgumentName) && $this->request->getArgument($this->resourceArgumentName) !== '') ? 'show' : 'list';
                    break;
            }
            $contentObject = $this->configurationManager->getContentObject();
            if ($contentObject->getUserObjectType() === ContentObjectRenderer::OBJECTTYPE_USER_INT) {
                switch ($this->request->getMethod()) {
                    case 'POST' :
                        $actionName = 'create';
                        break;
                    case 'PUT' :
                        if (!$this->request->hasArgument($this->resourceArgumentName)) {
                            $this->throwStatus(400, null, 'No resource specified');
                        }
                        $actionName = 'update';
                        break;
                    case 'DELETE' :
                        if (!$this->request->hasArgument($this->resourceArgumentName)) {
                            $this->throwStatus(400, null, 'No resource specified');
                        }
                        $actionName = 'delete';
                        break;
                }
            }

            $this->request->setControllerActionName($actionName);
        }

        return parent::resolveActionMethodName();
    }

    protected function initializeAction()
    {
        $this->mapRawPostData();
        parent::initializeAction();
    }

    /**
     * The error action basically handles validation errors.
     *
     * Because there might be a various number, the result always is a collection
     * of errors.
     * Since there might be other errors than those related to the actual resource
     * argument, the error collection is encapsulated in a meta container which
     * also names the property name of the resource argument.
     *
     * E.g.:
     *
     * return [
     *     'errors' => [
     *         // That's the resource this RESTfull controller targets
     *         [
     *             'code' => '1387390192',
     *             'title' => 'You are not allowed to update comments without changes.',
     *             'source' => ['pointer' => 'comment'],
     *         ],
     *         // That's some other action argument but not the actual resource.
     *         [
     *             'code' => '123456789',
     *             'message' => 'Order only allows "ASC" and "DESC" but "ANY" given.',
     *             'source' => ['parameter' => 'sort'],
     *         ],
     *     ],
     * ];
     *
     * @return string
     */
    protected function errorAction()
    {

        $response = ['errors' => []];

        foreach ($this->arguments->getValidationResults()->getFlattenedErrors() as $fullQualifiedPropertyPath => $propertyErrors) {
            /** @var \TYPO3\CMS\Extbase\Error\Error $propertyError */
            foreach ($propertyErrors as $propertyError) {
                $response['errors'][] = [
                    'code' => $propertyError->getCode(),
                    'title' => $propertyError->render(),
                    'source' => ['pointer' => $fullQualifiedPropertyPath],
                ];
            }
        }

        $this->response->setStatus(400);

        return json_encode($response);
    }

    protected function mapRawGetData()
    {
        $data = GeneralUtility::_GET($this->resourceArgumentName);
        if ($data) {
            $this->request->setArgument($this->resourceArgumentName, $data);
        }

    }

    protected function mapRawPostData()
    {

        foreach (array('ACCEPT', 'HTTP_ACCEPT') as $acceptFieldNameOptions) {
            if (array_key_exists($acceptFieldNameOptions, $_SERVER) && strpos($_SERVER[$acceptFieldNameOptions],
                    'application/json') !== false
            ) {
                $this->request->setFormat('json');
            }
        }


        if (array_key_exists('CONTENT_TYPE', $_SERVER)) {

            if (strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
                $data = json_decode(file_get_contents('php://input'), true);

            } else {
                $data = null;
            }

            if (is_array($data)) {
                /*
                 * Passing everything directly to the request avoids argument namespaces.
                 */
                $this->request->setArgument($this->resourceArgumentName, $data);
            }
        }

    }

    /**
     * Redirects the web request to another uri.
     *
     * NOTE: This method only supports web requests and will throw an exception
     * if used with other request types.
     *
     * @param mixed $uri Either a string representation of a URI or a \TYPO3\Flow\Http\Uri object
     * @param integer $delay (optional) The delay in seconds. Default is no delay.
     * @param integer $statusCode (optional) The HTTP status code for the redirect. Default is "303 See Other"
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @api
     */
    protected function redirectToUri($uri, $delay = 0, $statusCode = 303)
    {
        // the parent method throws the exception, but we need to act afterwards
        // thus the code in catch - it's the expected state
        try {
            parent::redirectToUri($uri, $delay, $statusCode);
        } catch (\TYPO3\CMS\Extbase\Mvc\Exception\StopActionException $exception) {
            if ($this->request->getFormat() === 'json') {
                $this->response->setContent('');
            }
            throw $exception;
        }
    }
}