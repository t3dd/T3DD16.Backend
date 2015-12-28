<?php
namespace TYPO3\T3DD16\Domain\Model\DataTransfer;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @method getUid()
 * @method getTitle()
 * @method getNavTitle()
 * @method getDoktype()
 */
class Page implements \JsonSerializable
{

    /**
     * @var array
     */
    protected $row;

    /**
     * @var array<\TYPO3\T3DD16\Domain\Model\DataTransfer\Page>
     */
    protected $children;

    /**
     * @var bool
     */
    protected $noCheck = false;

    /**
     * @var bool
     */
    protected $includeSeparators = true;

    /**
     * @param $row
     * @param bool $noCheck
     * @param bool $includeSeparators
     */
    public function __construct($row, $noCheck = false, $includeSeparators = true)
    {
        $this->row = $row;
        $this->noCheck = (bool)$noCheck;
        $this->includeSeparators = (bool)$includeSeparators;
    }

    /**
     * @return string
     */
    public function getNavigationTitle()
    {
        return $this->getNavTitle() ?: $this->getTitle() ?: ' ';
    }

    /**
     * @return string
     */
    public function getType()
    {
        switch ($this->getDoktype()) {
            case 1:
                return 'page';
            case 3:
                return 'external';
            case 4:
                return 'shortcut';
            case 199:
                return 'separator';
            default:
                return 'page';
        }
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->getContentObjectRender()->typoLink_URL([
            'parameter' => $this->getUid()
        ]);
    }

    /**
     * @return array<\TYPO3\T3DD16\Domain\Model\DataTransfer\Page>
     */
    public function getChildren()
    {
        if ($this->children === null) {
            $this->children = [];
            foreach ($this->getTypoScriptFrontendController()->sys_page->getMenu($uid = $this->getUid(), $fields = '*',
                $sortField = 'sorting', $addWhere = 'AND nav_hide = 0', $checkShortcuts = false) as $pageRow) {
                $child = self::create($pageRow['uid'], $pageRow, $this->noCheck, $this->includeSeparators);
                if ($this->filterMenuPage($child)) {
                    $this->children[] = $child;
                }
            }
        }

        return $this->children;
    }

    /**
     * @param string $method
     * @param $arguments
     *
     * @return null
     */
    public function __call($method, $arguments)
    {
        if (GeneralUtility::isFirstPartOfStr($method, 'get')) {
            $name = lcfirst(substr($method, 3));
            $lowerCaseUnderscoredName = GeneralUtility::camelCaseToLowerCaseUnderscored($name);
            if (array_key_exists($name, $this->row)) {
                return $this->row[$name];
            }
            if (array_key_exists($lowerCaseUnderscoredName, $this->row)) {
                return $this->row[$lowerCaseUnderscoredName];
            }
            if (is_callable(array($this, $name))) {
                return $this->{$name}();
            }
        }

        return null;
    }

    /**
     * @param int $pageUid
     * @param null $row
     * @param boolean $noCheck
     * @param boolean $includeSeparators
     *
     * @throws \InvalidArgumentException
     * @return \TYPO3\T3DD16\Domain\Model\DataTransfer\Page
     */
    public static function create($pageUid, $row = null, $noCheck = false, $includeSeparators = false)
    {

        static $pages = array();

        $identifier = sprintf('%d-%d-%d', $pageUid, $noCheck, $includeSeparators);

        if (!$pages[$identifier]) {
            if ($row === null) {
                if ($noCheck) {
                    $row = $GLOBALS['TSFE']->sys_page->getPage_noCheck($pageUid);
                } else {
                    $row = $GLOBALS['TSFE']->sys_page->getPage($pageUid);
                }
            }

            $pages[$identifier] = GeneralUtility::makeInstance('TYPO3\\T3DD16\\Domain\\Model\\DataTransfer\\Page', $row,
                $noCheck, $includeSeparators);
        }

        return $pages[$identifier];
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'title' => $this->getNavigationTitle(),
            'type' => $this->getType(),
            'link' => trim($this->getLink(), '/'),
            'children' => $this->getChildren()
        ];
    }

    /**
     * @param Page $page
     * @return bool
     */
    protected function filterMenuPage(Page $page)
    {

        $pageRow = $page->row;

        // No valid page if the page is hidden inside menus and
        // it wasn't forced to show such entries
        if ($pageRow['nav_hide']) {
            return false;
        }
        // No valid page if the default language should be shown and the page settings
        // are excluding the visibility of the default language
        if (!$GLOBALS['TSFE']->sys_language_uid && GeneralUtility::hideIfDefaultLanguage($pageRow['l18n_cfg'])) {
            return false;
        }
        // No valid page if the alternative language should be shown and the page settings
        // are requiring a valid overlay but it doesn't exists
        $hideIfNotTranslated = GeneralUtility::hideIfNotTranslated($pageRow['l18n_cfg']);
        if ($GLOBALS['TSFE']->sys_language_uid && $hideIfNotTranslated && !$pageRow['_PAGES_OVERLAY']) {
            return false;
        }

        if ($page->getType() === 'separator' && !$page->includeSeparators) {
            return false;
        }

        return true;
    }


    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * @return ContentObjectRenderer
     */
    protected function getContentObjectRender()
    {
        return $this->getTypoScriptFrontendController()->cObj;
    }
}