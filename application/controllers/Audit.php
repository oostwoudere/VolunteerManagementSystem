<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property Ion_auth|Ion_auth_model    $ion_auth           The ION Auth Spark Tool Set
 * @property Audit_model                $Audit_model        The Audit Model
 */
class Audit extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'language']);
    }

    /***
     * Inserts a row of data into the system audit log
     * @param array $data
     * @return bool
     */
    public function AuditLogAddition(array $data = array()) : bool {
        if ($this->Audit_model->LogItem($data)) {
            return true;
        }

        return false;
    }

    /***
     * Fetches the audit log from the database.
     * @param $page (pagination)
     * @return false|string
     */
    public function RequestAuditLog(int $page) {
        $limit = $page * 100;
        $log = $this->Audit_model->fetchLog($page, $limit);

        echo json_encode($log);
        return json_encode($log);
    }
}