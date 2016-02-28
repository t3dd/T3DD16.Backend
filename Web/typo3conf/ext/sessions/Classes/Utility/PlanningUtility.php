<?php
namespace TYPO3\Sessions\Utility;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\Sessions\Domain\Model\AbstractSession;
use TYPO3\Sessions\Domain\Repository\AnySessionRepository;

class PlanningUtility implements SingletonInterface
{

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objM;

    /**
     * @var \TYPO3\Sessions\Domain\Repository\AnySessionRepository
     */
    protected $sessionRepository;

    /**
     * @var \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected $db;

    /**
     * @var string
     */
    protected $dbDateTimeFormat = 'Y-m-d H:i:s';

    /**
     * PlanningUtility constructor.
     */
    public function __construct()
    {
        $this->objM = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        $this->sessionRepository = $this->objM->get(AnySessionRepository::class);
        $this->db = $GLOBALS['TYPO3_DB'];
        $dateTimeFormats = $this->db->getDateTimeFormats('tx_sessions_domain_model_session');
        $this->dbDateTimeFormat = $dateTimeFormats['datetime']['format'];
    }

    /**
     * Fetches the speakers and returns them comma seperated
     * for displaying in Planning Module
     *
     * @param $uid int
     * @return string
     */
    public function getSpeakers($uid)
    {
        if(! \TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($uid)) {
            throw new \InvalidArgumentException('Param $uid must be an integer');
        }
        $res = $this->db->exec_SELECTquery('fe_users.username, fe_users.name',
            'tx_sessions_session_record_mm
            LEFT JOIN fe_users ON tx_sessions_session_record_mm.uid_foreign = fe_users.uid',
            ' tx_sessions_session_record_mm.uid_local = '.$uid.' AND tx_sessions_session_record_mm.tablenames = \'fe_users\' ',
            '',
            ' tx_sessions_session_record_mm.sorting ASC ');
        if($res === false) {
            return '';
        }
        $speakers = [];
        while($row = $res->fetch_assoc()) {
            $speakers[] = (empty($row['name'])) ? $row['username'] : $row['name'];
        }
        return implode(', ', $speakers);
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @param string|null $format a valid date format or null which will return raw \DateTime objects rather than formatted strings
     * @return array
     * @throws \LogicException
     */
    public function getDaysArray(\DateTime $start, \DateTime $end, $format = 'Y-m-d')
    {
        if($start > $end) {
            throw new \LogicException('the given start is after the given end');
        }
        $days = [];
        $interval = new \DateInterval('P1D');
        $range = new \DatePeriod($start, $interval, $end);
        foreach($range as $day) {
            /** @var \DateTime $day */
            $days[] = ($format === null) ? $day : $day->format($format);
        }
        return $days;
    }

    /**
     * Method checks whether the given session collides with an existing one.
     * Checks if an associated speaker is associated to another session which:
     * - starts during the given session
     * - ends during the given session
     * - start at the same time as the given session
     * - ends at the same time as the given session
     * - surrounds the given session completely
     *
     * @param AbstractSession $session
     * @param array $exclude array of uids which should be exluded in the check (the given session is excluded by default)
     * @return array|false colliding sessions or false if no session collides
     * @throws \InvalidArgumentException
     */
    public function getCollidingSessions(AbstractSession $session, $exclude = [])
    {
        if(!is_array($exclude)) {
            throw new \InvalidArgumentException('$exclude is not of type array. should be an array of uids');
        }
        $speakers = $session->getSpeakers();
        // array holding named placeholder values
        $params = [];
        // helper in order to prepare a dynamic IN statement
        $inStmt = [];
        $i = 0;
        foreach($speakers as $speaker) {
            /** @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $speaker */
            // build unique placeholder name
            $placeholder = ':idref' . $i++;
            // assign the correct uid to the placeholder
            $params[$placeholder] = $speaker->getUid();
            // "store" the dynamic placeholder
            $inStmt[] = $placeholder.' ';
        }
        // flatten the dynamic placeholders for later IN statement use
        $inStmt = implode(',', $inStmt);
        // helper for dynamic NOT IN statement
        if(count($exclude) > 0) {
            $i = 0;
            $excludeUids = array_merge([$session->getUid()], $exclude);
            $excludeInStmt = [];
            foreach($excludeUids as $eUid) {
                // build unique placeholder name
                $placeholder = ':excludessessions' . $i++;
                // assign the correct uid to the placeholder
                $params[$placeholder] = $eUid;
                // store the dynamic placeholder for later
                $excludeInStmt[] = $placeholder;
            }
            // flatten dynamic placeholders for usage in IN statement
            $excludeInStmt = implode(', ', $excludeInStmt);
        }
        // set the rest of param values (respect DMBS datetime format)
        $params[':start'] = $session->getBegin()->format($this->dbDateTimeFormat);
        $params[':end'] = $session->getEnd()->format($this->dbDateTimeFormat);
        $params[':excludedsession'] = $session->getUid();
        $params[':scheduledtype'] = \TYPO3\Sessions\Domain\Model\ScheduledSession::class;

        $stmt = $this->db->prepare_SELECTquery(' DISTINCT tx_sessions_domain_model_session.uid AS uid ',
            ' tx_sessions_domain_model_session
                LEFT JOIN tx_sessions_session_record_mm AS srmm ON tx_sessions_domain_model_session.uid = srmm.uid_local AND srmm.tablenames = \'fe_users\'
                LEFT JOIN fe_users AS user ON srmm.uid_foreign = user.uid
            ',
            ' user.uid IN ('.$inStmt.')
                AND (
                    /* this session starts while another session is running (start overlaps with other session) */
                    ( tx_sessions_domain_model_session.begin > :start AND tx_sessions_domain_model_session.begin < :end )
                    OR
                    /* this session ends while another session is running (end overlaps with other session) */
                    ( tx_sessions_domain_model_session.end > :start AND tx_sessions_domain_model_session.end < :end )
                    OR
                    /* this session starts at the same time */
                    tx_sessions_domain_model_session.begin = :start
                    OR
                    /* this session ends at the same time */
                    tx_sessions_domain_model_session.end = :end
                    OR
                    /* this session starts before and ends after */
                    (tx_sessions_domain_model_session.begin < :start AND tx_sessions_domain_model_session.end > :end)
                )
                AND tx_sessions_domain_model_session.uid ' . (isset($excludeInStmt) ? 'NOT IN(' . $excludeInStmt .')' : '<> :excludedsession') . '
                AND tx_sessions_domain_model_session.type = :scheduledtype
                '.\TYPO3\CMS\Backend\Utility\BackendUtility::BEenableFields('tx_sessions_domain_model_session').'
            ', '', ' tx_sessions_domain_model_session.uid DESC ', '', $params);

        if($stmt->execute() && $stmt->rowCount() > 0) {
            if($stmt->rowCount() === 1) {
                $row = $stmt->fetch();
                $stmt->free();
                return [$this->sessionRepository->findByUid($row['uid'])];
            }
            $uids = [];
            while($row = $stmt->fetch()) {
                $uids[] = $row['uid'];
            }
            $stmt->free();
            return $this->sessionRepository->findByUids($uids)->toArray();
        }
        return false;
    }

}
