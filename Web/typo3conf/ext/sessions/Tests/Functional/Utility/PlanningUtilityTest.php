<?php
namespace T3DD\Sessions\Tests\Functional\Utility;

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

use T3DD\Sessions\Tests\Functional\AbstractTestCase;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\Sessions\Domain\Model\AnySession;
use TYPO3\Sessions\Utility\PlanningUtility;

/**
 * Class PlanningUtilityTest
 * @package T3DD\Sessions\Tests\Functional\Utility
 */
class PlanningUtilityTest extends AbstractTestCase
{
    /**
     * @var PlanningUtility
     */
    protected $subject;

    /**
     * Sets up this test case.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->subject = $this->getAccessibleMock(PlanningUtility::class, array('_dummy'), array());
    }

    /**
     * Tears down this test case.
     */
    protected function tearDown()
    {
        parent::tearDown();

        unset($this->subject);
    }

    /**
     * @param int $uid
     * @param string $begin
     * @param string $end
     * @param array $speakerIds
     * @param bool $expected
     *
     * @test
     * @dataProvider collidingSessionsAreDeterminedDataProvider
     */
    public function collidingSessionsAreDetermined($uid, $begin, $end, array $speakerIds, $exclude, $expected)
    {
        $beginDateTime = \DateTime::createFromFormat(DATE_ISO8601, $begin);
        $endDateTime = \DateTime::createFromFormat(DATE_ISO8601, $end);

        $session = new AnySession();
        $session->_setProperty('uid', $uid);
        $session->setBegin($beginDateTime);
        $session->setEnd($endDateTime);

        foreach ($speakerIds as $speakerId) {
            $speaker = new FrontendUser();
            $speaker->_setProperty('uid', $speakerId);
            $session->addSpeaker($speaker);
        }

        $result = $this->subject->getCollidingSessions($session, $exclude);
        $this->assertEquals($expected, (is_array($result) ? count($result) : $result));
    }

    /**
     * @return array
     */
    public function collidingSessionsAreDeterminedDataProvider()
    {
        return [
            'different speaker, before' => [
                999, '2016-09-01T12:00:00Z', '2016-09-01T13:00:00Z', [1,3], [], false,
            ],
            'different speaker, after' => [
                999, '2016-09-01T16:00:00Z', '2016-09-01T17:00:00Z', [1,3], [], false,
            ],
            'different speaker, start time intersects' => [
                999, '2016-09-01T14:30:00Z', '2016-09-01T15:30:00Z', [1,3], [], false,
            ],
            'different speaker, end time intersects' => [
                999, '2016-09-01T13:30:00Z', '2016-09-01T14:30:00Z', [1,3], [], false,
            ],
            'different speaker, session time intersects' => [
                999, '2016-09-01T14:15:00Z', '2016-09-01T14:45:00Z', [1,3], [], false,
            ],
            'different speaker, session time surrounds' => [
                999, '2016-09-01T13:00:00Z', '2016-09-01T17:00:00Z', [1,3], [], false,
            ],
            'different speaker, session time equals' => [
                999, '2016-09-01T14:00:00Z', '2016-09-01T15:00:00Z', [1,3], [], false,
            ],
            'same speaker, before' => [
                999, '2016-09-01T12:00:00Z', '2016-09-01T13:00:00Z', [2,3], [], false,
            ],
            'same speaker, after' => [
                999, '2016-09-01T16:00:00Z', '2016-09-01T17:00:00Z', [2,3], [], false,
            ],
            'same speaker, start time intersects' => [
                999, '2016-09-01T14:30:00Z', '2016-09-01T15:30:00Z', [2,3], [], 1,
            ],
            'same speaker, end time intersects' => [
                999, '2016-09-01T13:30:00Z', '2016-09-01T14:30:00Z', [2,3], [], 1,
            ],
            'same speaker, session time intersects' => [
                999, '2016-09-01T14:15:00Z', '2016-09-01T14:45:00Z', [2,3], [], 1,
            ],
            'same speaker, session time surrounds' => [
                999, '2016-09-01T13:00:00Z', '2016-09-01T17:00:00Z', [2,3], [], 1,
            ],
            'same speaker, session time equals' => [
                999, '2016-09-01T14:00:00Z', '2016-09-01T15:00:00Z', [2,3], [], 1,
            ],
            'same speaker, session time equals, but excluded' => [
                999, '2016-09-01T14:00:00Z', '2016-09-01T15:00:00Z', [2,3], [1,7,8], false,
            ],
        ];
    }
}