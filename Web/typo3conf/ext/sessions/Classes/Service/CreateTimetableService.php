<?php
namespace TYPO3\Sessions\Service;

use TYPO3\Sessions\Domain\Model\Session;
class CreateTimetableService {

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
	public function generateTimetable($config, $sessions, $rooms, $iterations, $considerTopics = false)
	{
		// Initialise service
		$this->initialiseService($config, $sessions, $rooms, $considerTopics);

		$firstInteration = true;

		while($iterations != 0)
		{
			// Shuffle sessions array if it is not the first iteration
			if(!$firstInteration)
			{
				$this->shuffleSessions();
			}

			// Start creation timetable
			$success = $this->startCreateTimetable();

			if($success)
			{
				return true;
			}

			$iterations--;
			$firstInteration = false;
		}

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
	 * @param Session $session
	 * @param integer $dayIterator
	 * @param integer $roomIterator
	 * @param integer $timeIterator
	 * @return boolean
	 */
	protected function findSpot(Session $session, $dayIterator = 0, $roomIterator = 0, $timeIterator = -1)
	{
		// First start on function
		if($timeIterator == -1)
		{
			$timeIterator = count($this->matrix[$dayIterator]) - 1;
		}

		// If slot is free
		if($this->matrix[$dayIterator][$timeIterator][$roomIterator] == '')
		{
			// Check other rooms for speaker overlay and topics
			foreach($this->matrix[$dayIterator][$timeIterator] as $otherRoom)
			{
				// Other room is still free
				if($otherRoom == '')
				{
					continue;
				}
				else // Session in room
				{
					// Check speaker overlay
					$sessionSpeakers = $session->getSpeakers()->toArray();
					$countSessionSpeakers = count($sessionSpeakers);
					/**
					 * @var Session $otherRoom
					 */
					$otherSessionSpeakers = $otherRoom->getSpeakers()->toArray();
					$countOtherSessionSpeakers = count($otherSessionSpeakers);
					$uniqueArrayCount = count(array_unique(array_merge($sessionSpeakers, $otherSessionSpeakers)));

					// Speaker not available
					if ($uniqueArrayCount != ($countSessionSpeakers + $countOtherSessionSpeakers))
					{
						break;
					}
					else // Speaker is available
					{
						// Consider topics?
						if($this->considerTopics)
						{
							// TODO: Extract to function
							$countDev = 0;
							$countDevOps = 0;
							$countDesign = 0;
							$countCommunity = 0;
							// Topics von allen Räumen abfragen
							foreach($this->matrix[$dayIterator][$timeIterator] as $otherRoom2)
							{
								if($otherRoom2 == '')
								{
									continue;
								}

								/**
								 * @var Session $otherRoom2
								 */
								switch($otherRoom2->getTopics()->getTopicGroup()->getTitle())
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

							$sessionApproved = false;
							switch($session->getTopics()->getTopicGroup()->getTitle())
							{
								case 'Dev':
									if($countDev < 4)
										$sessionApproved = true;
									break;
								case 'DevOps':
									if($countDevOps < 3)
										$sessionApproved = true;
									break;
								case 'Design':
									if($countDesign < 3)
										$sessionApproved = true;
									break;
								case 'Community':
									if($countDesign < 2)
										$sessionApproved = true;
									break;
							}

							if(!$sessionApproved)
							{
								break;
							}

						}

						// Slot found
						// Set room, start and end for the session
						$this->setRoomAndDatesForSession($session, $dayIterator, $timeIterator, $roomIterator);
						$matrix[$dayIterator][$timeIterator][$roomIterator] = $session;
						return true;
					}
				}
			}
		}

		// No free slot found
		// Check next day or next time or next room
		$dayIterator++;
		if($dayIterator == count($this->matrix[$dayIterator]))
		{
			$dayIterator = 0;
			$timeIterator--;
			if($timeIterator == -1)
			{
				$timeIterator = count($this->matrix[$dayIterator]) - 1;
				$roomIterator++;
				// No slot found -> Cancel
				if($roomIterator == count($this->matrix[$dayIterator][$timeIterator]))
				{
					return false;
				}
			}
		}

		// Check next slot
		$this->findSpot($session, $dayIterator, $roomIterator, $timeIterator);

	}

	/**
	 * Set room, start and end for the given session
	 *
	 * @param Session $session
	 * @param integer $dayIterator
	 * @param integer $timeIterator
	 * @param integer $roomIterator
	 * @return void
	 */
	protected function setRoomAndDatesForSession(Session $session, $dayIterator, $timeIterator, $roomIterator)
	{
		// First day only afternoon time slots
		if($dayIterator == 0)
		{
			$timeIterator++;
		}

		// Get data from config
		$day = $this->config['dates'][$dayIterator];
		$begin = $this->config['timeSlots'][$timeIterator]['begin'];
		$end = $this->config['timeSlots'][$timeIterator]['end'];

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