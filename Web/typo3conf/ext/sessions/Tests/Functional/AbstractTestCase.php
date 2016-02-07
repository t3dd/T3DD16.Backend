<?php
namespace T3DD\Sessions\Tests\Functional;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Tests\FunctionalTestCase;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\Sessions\Domain\Model\AnySession;
use TYPO3\Sessions\Utility\PlanningUtility;

/**
 * Class AbstractTestCase
 * @package T3DD\Sessions\Tests\Functional
 */
class AbstractTestCase extends FunctionalTestCase
{
    /**
     * @var array
     */
    protected $testExtensionsToLoad = array(
        'typo3conf/ext/sessions',
    );

    /**
     * Sets up this test case.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->importDataSet(__DIR__ . '/Fixtures/Xml/fe_users.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Xml/tx_sessions_domain_model_session.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Xml/tx_sessions_session_record_mm.xml');

        ExtensionManagementUtility::addTypoScriptSetup('
            <INCLUDE_TYPOSCRIPT: source="FILE:EXT:sessions/Configuration/TypoScript/setup.txt">
            config.tx_extbase.persistence < plugin.tx_sessions.persistence
        ');
    }
}