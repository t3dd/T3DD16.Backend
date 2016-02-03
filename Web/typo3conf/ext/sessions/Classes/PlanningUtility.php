<?php
namespace TYPO3\Sessions;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class PlanningUtility implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objM;

    /**
     * @var \TYPO3\Sessions\Domain\Repository\SessionRepository
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
        $this->objM = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        $this->sessionRepository = $this->objM->get('TYPO3\Sessions\Domain\Repository\SessionRepository');
        $this->db = $GLOBALS['TYPO3_DB'];
        $dateTimeFormats = $this->db->getDateTimeFormats('tx_sessions_domain_model_session');
        $this->dbDateTimeFormat = $dateTimeFormats['datetime']['format'];
    }

    /**
     * Method checks whether the given session collides with an existing one.
     * Checks if an associated speaker is associated to another session which:
     * - starts during the given session
     * - ends during the given session
     *
     * @TODO check only planned sessions
     * @param \TYPO3\Sessions\Domain\Model\Session $session
     * @return array|false colliding sessions or false if no session collides
     */
    public function getCollidingSessions(\TYPO3\Sessions\Domain\Model\Session $session)
    {
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
        // set the rest of param values (respect DMBS datetime format)
        $params[':start'] = $session->getBegin()->format($this->dbDateTimeFormat);
        $params[':end'] = $session->getEnd()->format($this->dbDateTimeFormat);
        $params[':exludedsession'] = $session->getUid();

        $stmt = $this->db->prepare_SELECTquery(' DISTINCT session.uid AS uid ',
            ' tx_sessions_domain_model_session AS session
                LEFT JOIN tx_sessions_session_record_mm AS srmm ON session.uid = srmm.uid_local AND srmm.tablenames = \'fe_users\'
                LEFT JOIN fe_users AS user ON srmm.uid_foreign = user.uid
            ',
            ' user.uid IN ('.$inStmt.')
                AND (
                    /* this session starts while another session is running (start overlaps with other session) */
                    ( session.begin > :start AND session.begin < :end )
                    OR
                    /* this session ends while another session is running (end overlaps with other session) */
                    ( session.end > :start AND session.end < :end )
                    OR
                    /* this session starts at the same time */
                    session.begin = :start
                    OR
                    /* this session ends at the same time */
                    session.end = :end
                    OR
                    /* this session starts before and ends after */
                    (session.begin < :start AND session.end > :end)
                )
                AND session.uid <> :exludedsession
            ', '', ' session.uid DESC ', '', $params);

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
