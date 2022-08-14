<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Audit_model extends CI_Model {
    /***
     * Saves audit log information into the database.
     * @param array $params
     * @return bool
     */
    public function LogItem(array $params = array()) : bool {
        if (!empty($params)) {
            if ($this->db->insert('audit_log', $params)) { return true; }
        }

        return false;
    }

    /***
     * Pagination Fetch for audit logs.
     * @param int $bottom (pagination start)
     * @param int $top (pagination end)
     */
    public function fetchLog(int $bottom, int $top) {
        return $this->db->where("id >= $bottom AND <= $top")->limit(100)->get('audit_log')->result_array();
    }
}
