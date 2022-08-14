<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Resume_model extends CI_Model {

    public function AddSkill($params) {
        return ($this->db->insert('skills', $params));
    }

    public function AddPortfolioItem($params) {
        return ($this->db->insert('portfolio', $params));
    }
}
