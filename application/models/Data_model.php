<?php use JetBrains\PhpStorm\ArrayShape;

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property Ion_auth|Ion_auth_model    $ion_auth       The ION Auth spark
 * @property CI_DB_query_builder        $db             DataBase Query Helper
 * @property Audit_model                $Audit_model    DataBase Query Helper
 */
class Data_model extends CI_Model {
    // Constants
    /** @type string | array
     * Actions to Authorization Access for
     */
    public const AUTH_ACTIONS = ['View' => 'view', 'Add' => 'add', 'Edit' => 'edit', 'Delete' => 'delete'];

    /** @type string | array
     * Entities to Authorization Access for
     */
    public const AUTH_ENTITIES = ['Volunteers' => 'volunteer', 'Opportunities' => 'opportunities', 'Centers' => 'centers'];

    /** @type string | array
     * Tables
     */
    public const TABLES = [
        'Centers' => 'centers',
        'Opportunities' => 'opportunities',
        'OppVolunteers' => 'opportunities_volunteers',
        'Permissions' => 'roles_permissions',
        'Users' => 'users',
        'Roles' => 'users_roles',
        'Volunteers' => 'volunteers_data',
    ];


    //------ View ------
    /** Load Centers
     * @return array Centers Data Query Result
     */
    public function LoadCenters() : array {
        $query = $this->db->get('centers');
        return $query->result_array();
    }

    /** Load Volunteer By User ID
     * @param $id   int|bool    ID of the User | False for current User
     * @return array Volunteer Data Query Result
     */
    public function LoadVolunteer(int|bool $id = false) : array {
        $query = $this->db->select('users.*, volunteers_data.*')
                        ->join($this::TABLES['Users'], 'users.id = '.$this::TABLES['Volunteers'].'.user_id')
                        ->where($this::TABLES['Volunteers'].'.user_id', $id ?? $this->ion_auth->get_user_id())->get($this::TABLES['Volunteers']);
        return $query->result_array()[0];
    }

    /** Load Volunteer That Can be Added to an Opportunity
     * @param $id   int|bool    ID of the Opportunity
     * @return array Volunteer Data Query Result
     */
    public function loadOpportunityOptions(int $id) : array {
        if(empty($id)) return [];
        // First Get those already involved
        $a = $this->db->select('users.id')->where('opportunities_volunteers.opportunity_id =', $id)
                    ->join('opportunities_volunteers', 'opportunities_volunteers.volunteer_id = users.id', 'left')
                    ->get('users');
        $arr = array();
        foreach ($a->result_array() as $vol) { $arr[] = $vol['id']; }

        // Then get the others
        $q = $this->db->select('users.id, users.username, users.first_name, users.last_name, users.email')->where_in('users.role', array(3, 4, 6));
        $q = (count($arr) > 0) ? $q->where_not_in('users.id', $arr) : $q;
        $q = $q->get('users');
        // Return the Result
        return $q->result_array();
    }

    /** Volunteers that are available to join that Opportunities
     * @param int $id   Volunteer ID
     * @return array    Result array
     */
    public function loadOpportunityVolunteerOptions(int $id) : array {
        // First Get Volunteers that are on the current Opportunity
        $a = $this->db->select('opportunities_volunteers.volunteer_id')
                ->where('opportunities_volunteers.opportunity_id =', $id)
                ->get('opportunities_volunteers');
        // Convert the result into a simple, usable array
        $arr = array();
        foreach ($a->result_array() as $vol) { $arr[] = $vol['volunteer_id']; }

        if(count($arr) > 0) {
            // Not Get Users not already involved with the opportunity
            $q = $this->db->select('users.id, CONCAT(`users`.`first_name`, " ", `users`.`last_name`) as name')
                ->join('user_roles', 'user_roles.id = users.role', 'left')
                ->like('user_roles.name', 'volunteer')
                ->where_not_in('users.id', $arr)
                ->get('users');
        } else {
            $q = $this->db->select('users.id, CONCAT(`users`.`first_name`, " ", `users`.`last_name`) as name')
                ->join('user_roles', 'user_roles.id = users.role', 'left')
                ->like('user_roles.name', 'volunteer')
                ->get('users');
        }

        return $q->result_array();
    }

    /** Opportunities that match specified filter criteria
     * @param int $center_id  ID of Center (or 0 if no center selected)
     * @param int $date_filter  1 if selected, 0 if not
     * @param string $search_string input user wishes to search for an appropriate opportunity with
     * @return array    Result array
     */
    public function filterOpportunities (int $center_id,int $date_filter, string $search_string) : array {

        $this->db->select("opportunities.id, opportunities.name, opportunities.date, centers.name as center, centers.location");
        $this->db->from('opportunities');
        $this->db->join('centers', 'centers.id = opportunities.center_id', 'left');

        if ($center_id !== 0) {
            $this->db->where('opportunities.center_id', $center_id);
        }
        //if false, default "all" centers selected

        if ($date_filter !== 0) {
            $currentDate = date("Y-m-d");
            $oldestDate = date("Y-m-d", strtotime(" -60 days"));

            $this->db->where('opportunities.date <=', $currentDate);
            $this->db->where('opportunities.date >=', $oldestDate);
        }
        //if false, default "all" dates selected

        if (!empty($search_string)) {
            $this->db->like('opportunities.name', $search_string);
        }
        //if empty, search string was left blank

        $query = $this->db->get();

        return $query->result_array();
    }

    //------ Add -------
    /** Add to Table
     *
     * Also Adds record to Audit Log
     * @param string    $table  Name of the Table
     * @param array     $data   Data for the new project
     * @return bool     Whether this succeeded
     */
    public function AddToTable(string $tableName, array $data) : bool {
        $title = ucfirst(strtolower($tableName));
        if($this->db->insert($tableName, $data)){
            // Insert Success into the audit log.
            $this->AuditAction('Insert '.$title, $title.' creation', true);
            return true;
        } else {
            // Insert Fail into the audit log.
            $this->AuditAction('Insert '.$title, $title.' creation');
            return false;
        }
    }

    //------ Edit ------
    /** Edits a Project
     *
     * Also Adds record to Audit Log
     * @param string $table  Table Name
     * @param array $data   Change Data
     * @param mixed     $value  Identification value
     * @param string $key    Identification Key (Default: 'id')
     * @return bool     Whether this succeeded
     */
    public function EditTableData(string $table, array $data, mixed $value, string $key = 'id') : bool {
        if($this->db->update($table, $data, [$key => $value])){
            // Insert Success into the audit log.
            $this->AuditAction("Edit {$table}", "{$table} ({$key}: $value) modification", true);
            return true;
        } else {
            // Insert Fail into the audit log.
            $this->AuditAction("Edit {$table}", "{$table} ({$key}: $value) modification.");
            return false;
        }
    }

    //----- Delete -----
    /** Delete a Table Entry
     *
     * Also Adds record to Audit Log
     * @param string        $table  Name of the Table
     * @param int|string    $id     ID of the Entry to delete
     * @return bool     Whether this was successful
     */
    public function DeleteFromTable(string $table, int|string $id) : bool {
        $title = ucfirst(strtolower($table));
        if($this->db->delete($table, ['id' => $id])){
            //Insert Success into the audit log.
            $this->AuditAction("Delete {$table}", "{$title} {$id} deletion", true);
            return true;
        } else {
            //Insert Fail into the audit log.
            $this->AuditAction("Delete {$table}", "{$title} {$id} deletion");
            return false;
        }
    }

    /** Delete All Opportunity Volunteers
     *
     * Also Adds record to Audit Log
     * @param int|string    $id     ID of the Opportunity
     * @return bool     Whether this was successful
     */
    public function DeleteOpportunityVolunteers(int|string $id) : bool {
        if($this->db->delete($this::TABLES['OppVolunteers'], ['opportunity_id' => $id])){
            //Insert Success into the audit log.
            $this->AuditAction('Delete All Opportunity Volunteers', 'All Opportunity Volunteers Deletion', true);
            return true;
        } else {
            //Insert Fail into the audit log.
            $this->AuditAction('Delete All Opportunity Volunteers', 'All Opportunity Volunteers Deletion');
            return false;
        }
    }

    //---- AuditAction --
    /**
     * @param string $action    Action | Ex: Delete Item
     * @param string $data      Data for Success/Fail | Ex: Item Deletion -> Item Deletion Failed & Item Deletion Succeeded
     * @param bool $success     If Action Succeeded | Default: Failed
     * @param string $location  Controller/Model the Action was performed in | Default: Data
     * @param int $user_id      User ID or 0 for Current User ID | Default: 0
     */
    public function AuditAction(string $action, string $data, bool $success = false, string $location = 'Data', int $user_id = 0) : void {
        $msg = ($success) ? " Succeeded!" : " Failed!";
        $this->Audit_model->LogItem(
            array(
                'location'  => $location,
                'action'    => $action,
                'data'      => "{$data} {$msg}",
                'user_id'   => ($user_id <= 0) ? $this->ion_auth->get_user_id() : $user_id,
                'status'    => ($success) ? 'success' : 'danger',
            )
        );
    }

    //------ Auth ------
    /**
     * @param string $entity          Name of the entity being accessed (Ex: Volunteers, Dashboard, Opportunities)
     * @param string $action        Action to do to that entity
     * @param numeric|false $user   User Id to check, False for current user
     * @return array     Whether the user has authorization for that action ['Success' : bool, 'Message' : string]
     */
    public function has_authorization_to ($entity, $action, $user = false) : array {
        // Start with Response
        $response = ['Success' => false];

        // Give Default Values to Prevent breaks
        $user = (empty($user)) ? $this->ion_auth->get_user_id() : $user;
        $entity = $this->cleanEntity($entity);
        $action = $this->cleanAction($action);

        // Build and Execute SQL Query
//        $sql = "SELECT perm.`permission`
//                FROM `users` u
//                LEFT JOIN `user_roles` roles ON roles.id = u.role
//                LEFT JOIN `roles_permissions` perm ON perm.user_role = roles.id
//                WHERE u.id = $user AND perm.entity LIKE '$entity' AND perm.action LIKE '$action'
//                LIMIT 1";
//        $query = $this->db->query($sql);
        $query = $this->db->select('roles_permissions.permission')
                        ->join('user_roles', 'user_roles.id = users.role', 'left')
                        ->join('roles_permissions', 'roles_permissions.user_role = user_roles.id')
                        ->where('users.id', $user)
                        ->like('roles_permissions.entity', $entity)
                        ->like('roles_permissions.action', $action)
                        ->limit(1)
                        ->get('users');


        // Check if Permission exists
        if(count($query->result_array()) < 1) {
            $response['Message'] = 'Permission Not Found';
        } else {
            // Get Permission from Query
            $permission = $query->result_array()[0];

            // Verify Permission
            if (intval($permission['permission']) == 1) {
                $response['Success'] = true;
                $response['Message'] = 'Permission Granted';
            } else {
                // Return Error Information
                $response['Message'] = 'Permission Denied';
            }
        }

        return $response;
    }

    public function setupDB() {
        // TODO: Build this out to fix/establish perms
    }

    /**
     * @param string $entity    Entity to Clean
     * @return string If Entity is Value: Entity, or Default Value ('Volunteers')
     */
    private function cleanEntity($entity) {
        if(!empty($entity) && in_array( $entity, Data_model::AUTH_ENTITIES)) return $entity;
        // If Fails, return default value
        return Data_model::AUTH_ENTITIES['Volunteers'];
    }

    /**
     * @param string $action    Entity to Clean
     * @return string If Entity is Value: Entity, or Default Value ('Volunteers')
     */
    private function cleanAction($action) {
        if(!empty($action) && in_array( $action, Data_model::AUTH_ACTIONS)) return $action;
        // If Fails, return default value
        return Data_model::AUTH_ACTIONS['View'];
    }
    //--- Security Utility ---
    public function GenSalt (int $len = 32): string {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789`~!@#$%^&*()-=_+';
        $l = strlen($chars) - 1;
        $str = '';
        for ($i = 0; $i < $len; ++$i) {
            $str .= $chars[rand(0, $l)];
        }
        return $str;
    }

    //------ Validation ------
    /** Load Centers
     * @return array Centers Data Query Result
     */
    public function TestUserUsername($username) : string|int {
        $query = $this->db->select('count(*) as hits')->like('username', $username)->get('users');
        return $query->result_array()[0]['hits'];
    }

    /** Load Centers
     * @return array Centers Data Query Result
     */
    public function TestUserEmail($email) : string|int {
        $query = $this->db->select('count(*) as hits')->like('email', $email)->get('users');
        return $query->result_array()[0]['hits'];
    }

    #[ArrayShape(['valid' => "bool", 'errorsData' => "array"])]
    public function ValidateFieldList ($fieldList) : array {
        $valid = true; $validationErrors = [];
        foreach ($fieldList as $field) {
            $validationData = $this->ValidateField($this->input->post($field['id']), $field['type'], $field['name'], $field['max'], $field['required']);
            if(!$validationData['valid']) {
                $valid = false;
                $validationErrors[] = ['id' => $field['id'], 'data' => $validationData['data']];
            }
        }
        return ['valid' => $valid, 'errorsData' => $validationErrors];
    }

    private function ValidateField($value, string $type, string $name, int $max, bool $required = false) : array {
        if(empty($value)) return ['valid' => ($required !== true), 'data' => $name.' required.'];
        if(strlen($value) > $max) return ['valid' => false, 'data' => "{$name} Cannot Exceed {$max} Characters"];
        return match (strtolower($type)) {
            'email' => $this->validateEmail($value, $name),
            'alpha' => $this->validateAlphaSpaces($value, $name),
            'numeric' => $this->validateNumbersSpaces($value, $name),
            'alphanumeric' => $this->validateAlphaNumbers($value, $name),
            default => ['valid' => true],
        };
    }

    private function validateNumbersSpaces($value, $name) : array {
        if(preg_match('/[^0-9 ]/i', $value)) return ['valid' => false, 'data' => "{$name} Must Be Numbers and Spaces only"];
        return ['valid' => true];
    }

    private function validateAlphaSpaces($value, $name) : array {
        if(preg_match('/[^a-zA-Z \n]+/', $value)) return ['valid' => false, 'data' => "{$name} Must Be Letters and Spaces only"];
        return ['valid' => true];
    }

    private function validateAlphaNumbers($value, $name) : array {
        if(preg_match('/[^a-zA-Z0-9 \n]+/', $value)) return ['valid' => false, 'data' => "{$name} Must Be Letters, Numbers, and Spaces only"];
        return ['valid' => true];
    }

    private function validateEmail($value, $name) : array {
        if(!filter_var($value, FILTER_VALIDATE_EMAIL)) return ['valid' => false, 'data' => "{$name} Must Be A Valid Email (Ex: email@domain.com)"];
        return ['valid' => true];
    }

}
