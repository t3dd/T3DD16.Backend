<?php
namespace T3DD\Sessions\Tests\Unit\Service;

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

use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\Sessions\Domain\Model\Room;
use TYPO3\Sessions\Domain\Model\AcceptedSession;

/**
 * Class ScheduleServiceTest
 * @package T3DD\Sessions\Tests\Unit\Service
 */
class ScheduleServiceTest extends UnitTestCase
{
    /**
     * @var \TYPO3\Sessions\Service\CreateTimetableService
     */
    protected $subject;

    /**
     * Sets up this test case.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->subject = new \TYPO3\Sessions\Service\CreateTimetableService();
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
     * @param int $sessionAmount Amount of sessions to be created
     * @param float $recurringSpeakerPercentage Percentage of recurring speakers for all sessions
     * @param array $recurringSpeakersPerSessionPercentage Percentage of recurring speakers per session
     * @param bool $isPossibleAtAll Whether a valid result is possible at all, based on the given parameters
     *
     * + $recurringSpeakerPercentage
     *   Example: "0.1" would end up in 10% of all speakers doing multiple sessions
     * + $recurringSpeakersPerSessionPercentage
     *   The key defines the number of speakers per session, the value the percentage on all sessions.
     *   Example: "[2 => 0.2, 3 => 0.1]" would be 20% of all sessions have 2 speakers, 10% have 3 speakers
     *
     * @test
     * @dataProvider scheduledSessionsAreFeasibleDataProvider
     */
    public function scheduledSessionsAreFeasible(
        $sessionAmount,
        $recurringSpeakerPercentage,
        array $recurringSpeakersPerSessionPercentage,
        $isPossibleAtAll
    ) {
        $recurringSpeakerPercentage = $this->minMax($recurringSpeakerPercentage, 0.0, 1.0);
        $speakerAmount = $this->minMax((int)$sessionAmount * (1.0 - $recurringSpeakerPercentage), 1);

        $configuration = $this->createConfiguration();
        $roomMaximum = $this->determineRoomMaximum($configuration);
        $sessionMaximum = $this->determineSessionMaximum($configuration);

        $rooms = $this->createRooms($roomMaximum);
        $speakers = $this->createSpeakers($speakerAmount);
        $sessions = $this->createSessions($sessionAmount, $speakers, $recurringSpeakersPerSessionPercentage);

        // Execute service to schedule sessions
        $result = $this->subject->generateTimetable($configuration, $sessions, $rooms);
        $assignedSessions = $this->subject->getAssignedSessions();
        $unassignedSessions = $this->subject->getUnassignedSessions();

        $this->assertLessThanOrEqual(
            $sessionMaximum, count($assignedSessions),
            'Assigned sessions exceeds possible session amout'
        );
        $this->assertEquals(
            $sessionAmount, count($assignedSessions) + count($unassignedSessions),
            'Amount of assigned and unassigned sessions does not sum up to total session amount'
        );
        $this->assertEquals(
            $isPossibleAtAll && ($sessionAmount <= $sessionMaximum), $result,
            'Result value seems to be wrong'
        );
    }

    /**
     * @return array
     */
    public function scheduledSessionsAreFeasibleDataProvider()
    {
        return [
            'low amount, non-recurring' => [
                10, 0.0, [], true,
            ],
            'low amount, 10% recurring' => [
                10, 0.1, [], true,
            ],
            'low amount, 100% recurring' => [
                10, 1.0, [], false,
            ],
            'exact amount, non-recurring' => [
                54, 0.0, [], true,
            ],
            'exact amount, 10% recurring' => [
                54, 0.1, [], true,
            ],
            'exact amount, 100% recurring' => [
                54, 1.0, [], false,
            ],
            'exceeding amount, non-recurring' => [
                100, 0.0, [], true,
            ],
            'exceeding amount, 10% recurring' => [
                100, 0.1, [], true
            ],
            'exceeding amount, 100% recurring' => [
                100, 1.0, [], false
            ],
        ];
    }

    /**
     * @param float $value
     * @param float|null $minimum
     * @param float|null $maximum
     * @return float
     */
    protected function minMax($value, $minimum = null, $maximum = null)
    {
        if ($minimum !== null && $value < $minimum) {
            return $minimum;
        }

        if ($maximum !== null && $value > $maximum) {
            return $maximum;
        }

        return $value;
    }

    /**
     * @return array
     */
    protected function createConfiguration()
    {
        $configuration = [
            'roomAndTimeData' => [
                [ 'timeSlots' => 2, 'rooms' => 6 ],
                [ 'timeSlots' => 3, 'rooms' => 6 ],
                [ 'timeSlots' => 3, 'rooms' => 6 ],
                [ 'timeSlots' => 1, 'rooms' => 6 ],
            ],
            'timeSlots' => [
                [ 'begin' => '09:30', 'end' => '11:00' ],
                [ 'begin' => '14:00', 'end' => '15:30' ],
                [ 'begin' => '16:30', 'end' => '18:00' ],
            ],
            'dates' => [
                '01.09.2016',
                '02.09.2016',
                '03.09.2016',
                '04.09.2016',
            ],
        ];

        return $configuration;
    }

    /**
     * @param array $configuration
     * @return int
     */
    protected function determineRoomMaximum(array $configuration)
    {
        $roomValues = [];

        if (empty($configuration['roomAndTimeData'])) {
            return 0;
        }

        foreach ($configuration['roomAndTimeData'] as $roomAndTimeData) {
            $roomValues[] = (int)$roomAndTimeData['rooms'];
        }

        return max($roomValues);
    }

    /**
     * @param array $configuration
     * @return int
     */
    protected function determineSessionMaximum(array $configuration)
    {
        $sessionMaximum = 0;

        if (empty($configuration['roomAndTimeData'])) {
            return 0;
        }

        foreach ($configuration['roomAndTimeData'] as $roomAndTimeData) {
            $sessionMaximum += (int)$roomAndTimeData['timeSlots'] * (int)$roomAndTimeData['rooms'];
        }

        return $sessionMaximum;
    }

    /**
     * @param int $amount
     * @return Room[]
     */
    protected function createRooms($amount) {
        $rooms = [];

        for ($i = 1; $i <= $amount; $i++) {
            $room = new Room();
            $room->_setProperty('uid', $i + 1000);
            $room->setSize(50);
            $room->setTitle('Room #' . $i);
            $rooms[] = $room;
        }

        return $rooms;
    }

    /**
     * @param int $amount
     * @return FrontendUser[]
     */
    protected function createSpeakers($amount) {
        $speakers = [];

        for ($i = 1; $i <= $amount; $i++) {
            $speaker = new FrontendUser();
            $speaker->_setProperty('uid', $i + 2000);
            $speaker->setName('Speaker #' . $i);
            $speakers[] = $speaker;
        }

        return $speakers;
    }

    /**
     * @param int $amount
     * @param FrontendUser[] $speakers
     * @param array $recurringSpeakersPerSessionPercentage
     * @return AcceptedSession[]
     */
    protected function createSessions($amount, array $speakers, array $recurringSpeakersPerSessionPercentage = []) {
        $sessions = [];

        for ($i = 1; $i <= $amount; $i++) {
            $randomSpeaker = $speakers[array_rand($speakers)];

            $session = new AcceptedSession();
            $session->_setProperty('uid', $i + 3000);
            $session->_setProperty('votes', rand(0, 500));
            $session->setTitle('Session #' . $i);
            $session->addSpeaker($randomSpeaker);
            $sessions[] = $session;
        }

        return $sessions;
    }
}