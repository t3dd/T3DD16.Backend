<?php
namespace TYPO3\Sessions\Service;

use TYPO3\Sessions\Domain\Model\AcceptedSession;
class CreateTimetableService {


	/**
	 * Maximal number of "Dev" sessions for a session time
	 * For consider topics needed
	 *
	 * @var integer
	 */
	protected $maxDevSessionsByTimeslot = 4;

	/**
	 * Maximal number of "DevOps" sessions for a session time
	 * For consider topics needed
	 *
	 * @var integer
	 */
	protected $maxDevopsSessionsByTimeslot = 3;

	/**
	 * Maximal number of "Design" sessions for a session time
	 * For consider topics needed
	 *
	 * @var integer
	 */
	protected $maxDesignSessionsByTimeslot = 3;

	/**
	 * Maximal number of "Community" sessions for a session time
	 * For consider topics needed
	 *
	 * @var integer
	 */
	protected $maxCommunitySessionsByTimeslot = 2;

	/**
	 * Config array
	 *
	 * @var array
	 */
	protected $config;

	/**
	 * Room array
	 *
	 * @var array
	 */
	protected $rooms;

	/**
	 * Sessions array
	 *
	 * @var array
	 */
	protected $sessions;

	/**
	 * The matrix of the timetable
	 *
	 * @var array
	 */
	protected $matrix;

	/**
	 * Check for topics
	 *
	 * @var boolean
	 */
	protected $considerTopics;

	/**
	 * Highest amount of time slots
	 *
	 * @var integer
	 */
	protected $maxTimeSlots = 0;

	/**
	 * All unassigned sessions
	 *
	 * @var array
	 */
	protected $unassignedSessions = array();

	/**
	 * All assigned sessions
	 *
	 * @var array
	 */
	protected $assignedSessions = array();


	/**
	 * Start the service
	 *
	 * @param array $config
	 * @param array $sessions
	 * @param array $rooms
	 * @param integer $iterations
	 * @param boolean $considerTopics
	 * @return boolean
	 */
	public function generateTimetable($config, $sessions, $rooms, $iterations = 1, $considerTopics = false)
	{
		// Initialise service
		$this->initialiseService($config, $sessions, $rooms, $considerTopics);

		$firstIteration = true;

		while($iterations > 0)
		{
			// Shuffle sessions array if it is not the first iteration
			if(!$firstIteration)
			{
				$this->shuffleSessions();
			}

			// Start creation timetable
			$success = $this->startCreateTimetable();
//			\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($this->matrix);die;

			if($success)
			{
				return true;
			}

			$iterations--;
			$firstIteration = false;
		}
//		\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($this->matrix);die;

		return false;
	}

	/**
	 * Start the creation of the timetable
	 *
	 * @return boolean
	 */
	protected function startCreateTimetable()
	{
		// Generate timetable matrix
		$this->generateMatrix();

		// Try to find a spot for each session
		foreach($this->sessions as $session)
		{
			$success = $this->findSpot($session);
			if(!$success)
			{
				$this->unassignedSessions[] = $session;
			}
			else
			{
				$this->assignedSessions[] = $session;
			}
			// TODO: Problem: Verteilung kann auf den Schluss zu, zu Engpässen führen...
			// z.B. Speaker 2 hat 2 Vorträge mit ganz schlechte Votes und es sind nur noch Sonntag früh frei...
		}

//		\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($this->matrix);die;
		return count($this->unassignedSessions) === 0;
	}

	/**
	 * Initialise the service and set service variables
	 *
	 * @param array $config
	 * @param array $sessions
	 * @param array $rooms
	 * @param boolean $considerTopics
	 * @return void
	 */
	protected function initialiseService($config, $sessions, $rooms, $considerTopics)
	{
		$this->config = $config;
		$this->sessions = $sessions;
		$this->rooms = $rooms;
		$this->considerTopics = $considerTopics;
	}

	/**
	 * Generates the empty timetable matrix
	 * @return void
	 */
	protected function generateMatrix()
	{
		$matrix = array();
		foreach($this->config['roomAndTimeData'] as $dayKey => $day)
		{
			// Set maxTimeSlots
			if($day['timeSlots'] > $this->maxTimeSlots)
			{
				$this->maxTimeSlots = $day['timeSlots'];
			}

			// Generate Matrix
			for($i = 0; $i < $day['timeSlots']; $i++)
			{
				for($j = 0; $j < $day['rooms']; $j++)
				{
					$matrix[$dayKey][$i][] = "";
				}
			}
		}
		$this->matrix = $matrix;
	}

	/**
	 * Check for free spot for given session
	 *
	 * @param AcceptedSession $session
	 * @param integer $dayIterator
	 * @param integer $roomIterator
	 * @param integer $timeIterator
	 * @return boolean
	 */
	protected function findSpot(AcceptedSession $session, $dayIterator = 0, $roomIterator = 0, $timeIterator = 0)
	{
		// Set timeIndex
		// Start with highest time slots
		$timeIndex = (count($this->matrix[$dayIterator]) - 1) - $timeIterator;

		// If slot exists and it is free
		if(array_key_exists($timeIndex, $this->matrix[$dayIterator]) && $this->matrix[$dayIterator][$timeIndex][$roomIterator] == '')
		{
			// Check other rooms for Speaker overlay
			$spotOk = $this->checkOtherRoomsForSpeakerOverlay($session, $dayIterator, $timeIndex);

			// Consider topics?
			if($this->considerTopics)
			{
				// Checks if session topic would exceed max topics per time slot
				// TODO: Warte auf Session Model Anpassung ($session->getTopicGroup())
//				$spotOk = $this->checkMaxTopicsForTimeSlot($session, $dayIterator, $timeIndex);
			}

			// Slot found
			if($spotOk)
			{
				// Set room, start and end for the session
				$this->setRoomAndDatesForSession($session, $dayIterator, $timeIndex, $roomIterator);
				$this->matrix[$dayIterator][$timeIndex][$roomIterator] = $session;
				return true;
			}
		}

		// No free slot found
		// Check next day or next time or next room
		$dayIterator++;
		if($dayIterator >= count($this->matrix))
		{
			$dayIterator = 0;
			$timeIterator++;
		}
		if($timeIterator >= $this->maxTimeSlots)
		{
			$timeIterator = 0;
			$timeIndex = (count($this->matrix[$dayIterator]) - 1) - $timeIterator;
			$roomIterator++;
			// No slot found -> Cancel
			if($roomIterator >= count($this->matrix[$dayIterator][$timeIndex]))
			{
				return false;
			}
		}

		// Check next slot
		return $this->findSpot($session, $dayIterator, $roomIterator, $timeIterator);
	}

	/**
	 * Set room, start and end for the given session
	 *
	 * @param AcceptedSession $session
	 * @param integer $dayIterator
	 * @param integer $timeIndex
	 * @param integer $roomIterator
	 * @return void
	 */
	protected function setRoomAndDatesForSession(AcceptedSession $session, $dayIterator, $timeIndex, $roomIterator)
	{
		// First day only afternoon time slots
		if($dayIterator == 0)
		{
			$timeIndex++;
		}

		// Get data from config
		$day = $this->config['dates'][$dayIterator];
		$begin = $this->config['timeSlots'][$timeIndex]['begin'];
		$end = $this->config['timeSlots'][$timeIndex]['end'];

		// Set room
		$session->setRoom($this->rooms[$roomIterator]);

		// Set begin
		$beginDateTime = \DateTime::createFromFormat('d.m.Y H:i', $day.' '.$begin);
		$session->setBegin($beginDateTime);

		// Set end
		$endDateTime = \DateTime::createFromFormat('d.m.Y H:i', $day.' '.$end);
		$session->setEnd($endDateTime);
	}

	/**
	 * Checks speaker overlay in time slot
	 *
	 * @param AcceptedSession $session
	 * @param integer $dayIterator
	 * @param integer $timeIndex
	 * @return boolean
	 */
	protected function checkOtherRoomsForSpeakerOverlay(AcceptedSession $session, $dayIterator, $timeIndex)
	{
		$spotOk = true;

		// Check other rooms for speaker overlay
		foreach($this->matrix[$dayIterator][$timeIndex] as $otherRoom) {
			// Other room is still free
			if ($otherRoom == '')
			{
				continue;
			}
			else // Session in room
			{
				// Check speaker overlay
				$sessionSpeakers = $session->getSpeakers()->toArray();
				$countSessionSpeakers = count($sessionSpeakers);
				/**
				 * @var AcceptedSession $otherRoom
				 */
				$otherSessionSpeakers = $otherRoom->getSpeakers()->toArray();
				$countOtherSessionSpeakers = count($otherSessionSpeakers);
				$uniqueArrayCount = count(array_unique(array_merge($sessionSpeakers, $otherSessionSpeakers)));

				// Speaker not available
				if ($uniqueArrayCount != ($countSessionSpeakers + $countOtherSessionSpeakers)) {
					$spotOk = false;
					break;
				}
			}
		}

		return $spotOk;
	}

	/**
	 * Checks if session topic would exceed max topics per time slot
	 *
	 * @param AcceptedSession $session
	 * @param integer $dayIterator
	 * @param integer $timeIndex
	 * @return boolean
	 */
	protected function checkMaxTopicsForTimeSlot(AcceptedSession $session, $dayIterator, $timeIndex)
	{
		$spotOk = true;

		$countDev = 0;
		$countDevOps = 0;
		$countDesign = 0;
		$countCommunity = 0;

		// Check topic for each room
		foreach($this->matrix[$dayIterator][$timeIndex] as $otherRoom)
		{
			if($otherRoom == '')
			{
				continue;
			}

			/**
			 * @var AcceptedSession $otherRoom
			 */
			switch($otherRoom->getTopicGroup()->getTitle())
			{
				case 'Dev':
					$countDev++;
					break;
				case 'DevOps':
					$countDevOps++;
					break;
				case 'Design':
					$countDesign++;
					break;
				case 'Community':
					$countCommunity++;
					break;
			}
		}

		switch($session->getTopicGroup()->getTitle())
		{
			case 'Dev':
				if($countDev >= $this->maxDevSessionsByTimeslot)
					$spotOk = false;
				break;
			case 'DevOps':
				if($countDevOps >= $this->maxDevopsSessionsByTimeslot)
					$spotOk = false;
				break;
			case 'Design':
				if($countDesign >= $this->maxDesignSessionsByTimeslot)
					$spotOk = false;
				break;
			case 'Community':
				if($countDesign >= $this->maxCommunitySessionsByTimeslot)
					$spotOk = false;
				break;
		}

		return $spotOk;
	}

	/**
	 * Shuffle the first 9 sessions
	 *
	 * @return void
	 */
	protected function shuffleSessions()
	{
		// Get first 9 sessions
		$firstSessions = array_slice($this->sessions, 0, 9);
		// Shuffle first 9 sessions
		shuffle($firstSessions);
		// Integrate shuffled sessions in total session array
		$this->sessions = array_splice($this->sessions, 0, 9, $firstSessions);
	}

	/**
	 * Get all unassignedSessions
	 *
	 * @return array
	 */
	public function getUnassignedSessions(){
		return $this->unassignedSessions;
	}

	/**
	 * Get all assignedSessions
	 *
	 * @return array
	 */
	public function getAssignedSessions(){
		return $this->assignedSessions;
	}

}