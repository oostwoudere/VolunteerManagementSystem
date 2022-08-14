<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property Ion_auth|Ion_auth_model    $ion_auth           The ION Auth spark
 * @property CI_Form_validation         $form_validation    The form validation library
 * @property CI_DB_query_builder        $db                 The Database Query Helper
 * @property Data_model                 $data_model         The Data Model
 * @property CI_Config                  $config             The Language Handler
 * @property CI_Upload                  $upload             The Upload Helper
 * @property CI_Input                   $input              The Input Handler
 * @property CI_Lang                    $lang               The Language Handler
 */
class Data extends CI_Controller {
    private string $upload_path = '' ;

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'language']);
        $this->load->library(['ion_auth', 'form_validation', 'upload']);
        $this->load->model('data_model');
        $this->lang->load('auth');

        $this->upload_path = realpath(APPPATH . '../uploads');
        $uploadConfig = [
            'upload_path' => realpath(APPPATH . '../uploads'),
            'allowed_types' => 'jpg|jpeg|gif|png|pdf',
            'overwrite' => true,
        ];
        $this->upload->initialize($uploadConfig);
    }

    /********************** AJAX ENDPOINTS **********************/
    //------ View ------
    function loadOpportunityVolunteerOptions () : bool {
        $auth = $this->data_model->has_authorization_to($this->data_model::AUTH_ACTIONS['View'], $this->data_model::AUTH_ENTITIES['Opportunities']);
        if($auth['Success']){
            $response = ['Success' => false];

            if (empty($this->input->post('id'))) {
                $response['Message'] = 'Missing Opportunity ID';
            } else {
                $id = $this->input->post('id');
                $q = $this->db->select('CONCAT(users.first_name, " ", users.last_name) as name, users.id')
                    ->join('users', 'users.id = opportunities_volunteers.volunteer_id', 'left')
                    ->where('opportunities_volunteers.opportunity_id !=', $id)
                    ->get('opportunities_volunteers');

                $response['Success'] = true;
                $response['data'] = $q->result_array();
            }
            echo json_encode($response);
            return $response['Success'];
        } else {
            echo json_encode($auth);
            return false;
        }
    }

    function loadOpportunityOptions () : bool {
        $auth = $this->data_model->has_authorization_to($this->data_model::AUTH_ACTIONS['Edit'], $this->data_model::AUTH_ENTITIES['Opportunities']);
        if($auth['Success']){
            $response = ['Success' => false];

            if (empty($this->input->post('id'))) {
                $response['Message'] = 'Missing Opportunity ID';
            } else {
                $id = $this->input->post('id');
                $q = $this->data_model->loadOpportunityOptions($id);

                $response['Success'] = true;
                $response['data'] = $q;
            }
            echo json_encode($response);
            return $response['Success'];
        } else {
            echo json_encode($auth);
            return false;
        }
    }

    //------ Add -------
    function AddVolunteer(): bool {
        $auth = $this->data_model->has_authorization_to($this->data_model::AUTH_ACTIONS['Add'], $this->data_model::AUTH_ENTITIES['Volunteers']);
        if($auth['Success']){
            // Validate Form
            $response = ['Success' => false];

//        $passRegex = "/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9])(?=.{10,})/g";
//            $min_length = $this->config->item('min_password_length', 'ion_auth');
            $max_t_length = 250;
            $max_l_length = 100;

            // Setup Form Validation
            $valid = true;
            $validationErrors = [];

            // All the Fields
            $fieldList = [  // TYPES: ALPHA, NUMERIC, ALPHANUMERIC, BASIC
                ['name' => 'First Name',                'id' => 'first_name',   'type' => 'alpha',          'max' => $max_l_length, 'required' => true,  ],
                ['name' => 'Last Name',                 'id' => 'last_name',    'type' => 'alpha',          'max' => $max_l_length, 'required' => true,  ],
                ['name' => 'Username',                  'id' => 'username',     'type' => 'alpha',          'max' => $max_l_length, 'required' => true,  ],
                ['name' => 'Email',                     'id' => 'email',        'type' => 'email',          'max' => $max_l_length, 'required' => true,  ],
                ['name' => 'Password',                  'id' => 'password',     'type' => 'basic',          'max' => $max_l_length, 'required' => true,  ],
                ['name' => 'Confirm Password',          'id' => 'pass_confirm', 'type' => 'basic',          'max' => $max_l_length, 'required' => true,  ],
                ['name' => 'Address',                   'id' => 'address',      'type' => 'alphanumeric',   'max' => $max_l_length, 'required' => false, ],
                ['name' => 'Home Phone',                'id' => 'home',         'type' => 'numeric',        'max' => $max_l_length, 'required' => false, ],
                ['name' => 'Work Phone',                'id' => 'work',         'type' => 'numeric',        'max' => $max_l_length, 'required' => false, ],
                ['name' => 'Cell Phone',                'id' => 'cell',         'type' => 'numeric',        'max' => $max_l_length, 'required' => false, ],
                ['name' => 'Centers',                   'id' => 'centers',      'type' => 'basic',          'max' => $max_l_length, 'required' => false, ],
                ['name' => 'Skills',                    'id' => 'skills',       'type' => 'basic',          'max' => $max_t_length, 'required' => false, ],
                ['name' => 'Availability',              'id' => 'available',    'type' => 'basic',          'max' => $max_t_length, 'required' => false, ],
                ['name' => 'License',                   'id' => 'licenses',     'type' => 'basic',          'max' => $max_t_length, 'required' => false, ],
                ['name' => 'Educational Background',    'id' => 'background',   'type' => 'basic',          'max' => $max_t_length, 'required' => false, ],
                ['name' => 'Emergency Name',            'id' => 'e_name',       'type' => 'alpha',          'max' => $max_l_length, 'required' => false, ],
                ['name' => 'Emergency Phone',           'id' => 'e_phone',      'type' => 'numeric',        'max' => $max_l_length, 'required' => false, ],
                ['name' => 'Emergency Email',           'id' => 'e_email',      'type' => 'email',          'max' => $max_l_length, 'required' => false, ],
                ['name' => 'Emergency Address',         'id' => 'e_address',    'type' => 'alphanumeric',   'max' => $max_l_length, 'required' => false, ],
            ];
            // Check All
            foreach ($fieldList as $field) {
                $validationData = $this->ValidateField($this->input->post($field['id']), $field['type'], $field['name'], $field['max'], $field['required']);
                if(!$validationData['valid']){
                    $valid = false;
                    $validationErrors[] = ['id' => $field['id'], 'data' => $validationData['data']];
                }
            }
            // Check Password Confirmation
            if($this->input->post('password') != $this->input->post('pass_confirm')) {
                $valid = false;
                $validationErrors[] = ['id' => 'pass_confirm', 'data' => 'Password Confirmation must match password'];
            }

            // Finalize Validation
            $response['valid'] = $valid;

            // If Valid -> Add User First
            if ($response['valid'] !== true) {
                $response['Message'] = 'Form Invalid';
                $response['errorsData'] = $validationErrors;
            } else {
                // Add User
                // Generate Secure Password
                $salt = $this->GenSalt();
                $salted_password = $salt . $this->input->post('pass');

                // Collect Data into one object
                $user_data = [
                    'first_name' => $this->input->post('first_name'),
                    'last_name' => $this->input->post('last_name'),
                    'username' => $this->input->post('username'),
                    'email' => strtolower($this->input->post('email')),
                    'salt' => $salt,
                    'password' => hash('sha256', $salted_password),
                    'active' => 1,
                    'role' => 6,
                ];

                // Attempt to Add User
                $table = $this->data_model::TABLES['Users'];
                if(!$this->data_model->AddToTable($table, $user_data)) {
                    $response['Message'] = 'Failed At Database - Add User ';
                    $response['data'] = $user_data;
                } else if($this->ion_auth->get_user_id_from_username($user_data['username']) === false) {
                    $response['Message'] = 'Failed At Add - User Could Not Be Found ';
                    $response['data'] = $user_data;
                } else {
                    // If User Made -> Add Volunteer Data
                    // Get User Id for linking
                    $user_id = $this->ion_auth->get_user_id_from_username($user_data['username']);

                    // Volunteer Data Object
                    $volunteerData = [
                        'skills' => $this->input->post('skills') ?? '',
                        'available' => $this->input->post('available') ?? '',
                        'address' => $this->input->post('address') ?? '',
                        'home' => $this->input->post('home') ?? '',
                        'work' => $this->input->post('work') ?? '',
                        'cell' => $this->input->post('cell') ?? '',
                        'background' => $this->input->post('background') ?? '',
                        'licenses' => $this->input->post('licenses') ?? '',
                        'e_name' => $this->input->post('e_name') ?? '',
                        'e_phone' => $this->input->post('e_phone') ?? '',
                        'e_email' => $this->input->post('e_email') ?? '',
                        'e_address' => $this->input->post('e_address') ?? '',
                        'drivers' => '',
                        'social' => '',
                        'user_id' => $user_id,
                    ];

                    if(!$this->data_model->AddToTable($this->data_model::TABLES['Volunteers'], $volunteerData)){
                        $response['Message'] = 'Failed At Database - Add Volunteer to User ';
                        $response['data'] = ['user' => $user_data, 'volunteer' => $volunteerData];
                    } else {
                        // If Volunteer Made -> Attempt File Upload
                        /*if(empty($this->input->post('drivers')) && empty($this->input->post('social'))) { */
                            $response['Success'] = true;
                            $response['data'] = ['id' => $user_id];
                            $response['Message'] = "Volunteer Creation Succeeded";
                        // TODO: Fix the upload section
                        /*} else {
                            $driverSuccess = false;
                            $this->upload_path = (empty($this->upload_path)) ? realpath(APPPATH . '../uploads') : $this->upload_path;

                            // If Drivers
                            if(empty($this->input->post('drivers'))) {
                                $driverSuccess = true;
                            } else {
                                // Attempt Drivers Upload
                                $drivers = "{$this->upload_path}{$user_id}_drivers";
                                $this->upload->set_filename($this->upload_path, $user_id.'_drivers');
                                if(!$this->upload->do_upload('drivers')) {
                                    $response['errorsData'] = $this->upload->error_msg;
                                    $response['Message'] = "Volunteer Creation Failed At Drivers License Upload ";
                                    $response['data'] = [
                                        'id' => $user_id,
                                        'path' => $this->upload_path,
                                        'drivers' => [
                                            'post' => $this->input->post('drivers'),
                                            'data' => $this->upload->data(),
                                            'drivers' => $drivers,
                                        ],
                                        'uploadData' => $this->upload->data(),
                                    ];
                                    $this->data_model->AuditAction('Upload Drivers', 'Drivers Upload');
                                } else {
                                    // Attempt Update Profile
                                    if(!$this->data_model->EditTableData($this->data_model::TABLES['Volunteers'], ['drivers' => $drivers], $user_id)){
                                        $response['data'] = ['id' => $user_id];
                                        $response['Message'] = "Volunteer Creation Failed At Drivers Upload ";
                                    } else {
                                        $driverSuccess =  true;
                                    }
                                }
                            }

                            // If Social
                            if(empty($this->input->post('social'))) {
                                $response['Success'] = $driverSuccess;
                                if($driverSuccess) {
                                    $response['data'] = ['id' => $user_id];
                                    $response['Message'] = "Volunteer Creation Succeeded";
                                }
                            } else {
                                $social = "{$this->upload_path}{$user_id}_social";
                                // Attempt Social Upload
                                $this->upload->set_filename($this->upload_path, $user_id.'_social');
                                if(!$this->upload->do_upload('social')) {
                                    $this->data_model->AuditAction('Upload Social', 'Social Upload');
                                    $socialErrorData = ['post' => $this->input->post('social'), 'social' => $social, 'data' => $this->upload->data(), ];
                                    if($driverSuccess) { // No Data to Override
                                        $response['Message'] = "Volunteer Creation Failed At Social Upload";
                                        $response['data'] = [
                                            'path' => $this->upload_path,
                                            'social' => $socialErrorData,
                                            'id' => $user_id,
                                        ];
                                        $response['errorsData'] = ['social' => $this->upload->error_msg];
                                    } else {    // Save in Addition to Driver Data
                                        $response['Message'] .= "And Social Upload";
                                        $response['data']['social'] = $social;
                                        $response['errorsData']['social'] = $this->upload->error_msg;
                                    }
                                } else {
                                    // Attempt Update Profile
                                    if(!$this->data_model->EditTableData($this->data_model::TABLES['Volunteers'], ['social' => $social], $user_id)){
                                        if($driverSuccess) { // No Data to Override
                                            $response['Message'] = "Volunteer Creation Failed At Social Upload";
                                            $response['errorsData'] = ['social' => $social];
                                        } else {    // Save in Addition to Driver Data
                                            $response['Message'] .= "And Social Upload";
                                            $response['errorsData']['social'] = $social;
                                        }
                                    } else {
                                        $response['Success'] = $driverSuccess;
                                        if($driverSuccess) { // No Data
                                            $response['data'] = ['id' => $user_id];
                                            $response['Message'] = "Volunteer Creation Succeeded";
                                        }
                                    }
                                }
                            }
                        } */
                    }
                }
            }

            echo json_encode($response);
            return $response['Success'];
        } else {
            echo json_encode($auth);
            return false;
        }
    }

    function AddVolunteerToOpportunity() : bool {
        $auth = $this->data_model->has_authorization_to($this->data_model::AUTH_ACTIONS['Edit'], $this->data_model::AUTH_ENTITIES['Opportunities']);
        if($auth['Success']){
            $response = ['Success' => false];

            if (empty($this->input->post('volunteer')) || empty($this->input->post('opportunity'))) {
                $response['Message'] = 'Missing Key Values: ';
                $response['Message'] .= (empty($this->input->post('volunteer'))) ? 'Volunteer, ' : '';
                $response['Message'] .= (empty($this->input->post('opportunity'))) ? 'Opportunity, ' : '';
                $response['Message'] = substr($response['Message'], 0, -2);
            } else {
                $volunteer = $this->input->post('volunteer');
                $opportunity = $this->input->post('opportunity');
                $data = ['opportunity_id' => $opportunity, 'volunteer_id', $volunteer];
                if(!$this->data_model->AddToTable($this->data_model::TABLES['OppVolunteers'], $data)){
                    $response['Message'] = 'Failed At Database';
                    $response['data'] = $data;
                } else {
                    $response['Success'] = true;
                    $response['Message'] = 'Added Volunteer Successfully';
                }
            }

            echo json_encode($response);
            return $response['Success'];
        } else {
            echo json_encode($auth);
            return false;
        }
    }

    function AddOpportunity(): bool {
        $auth = $this->data_model->has_authorization_to($this->data_model::AUTH_ACTIONS['Add'], $this->data_model::AUTH_ENTITIES['Opportunities']);
        if($auth['Success']){
            // Validate Form
            $response = ['Success' => false];

            // Setup Form Validation
            $valid = true;
            $validationErrors = [];

            // All the Fields
            $fieldList = [  // TYPES: ALPHA, NUMERIC, ALPHANUMERIC, BASIC
                ['name' => 'Name',      'id' => 'name',   'type' => 'alpha',    'max' => 100,   'required' => true,  ],
                ['name' => 'Date',      'id' => 'date',   'type' => 'basic',    'max' => 100,   'required' => true,  ],
                ['name' => 'Center',    'id' => 'center', 'type' => 'numeric',  'max' => 5,     'required' => true,  ],
            ];
            // Check All
            foreach ($fieldList as $field) {
                $validationData = $this->ValidateField($this->input->post($field['id']), $field['type'], $field['name'], $field['max'], $field['required']);
                if(!$validationData['valid']){
                    $valid = false;
                    $validationErrors[] = ['id' => $field['id'], 'data' => $validationData['data']];
                }
            }

            // Finalize Validation
            $response['valid'] = $valid;

            // If Valid -> Add User First
            if ($response['valid'] !== true) {
                $response['Message'] = 'Form Invalid';
                $response['errorsData'] = $validationErrors;
            } else {
                // Collect Data into one object
                $data = [
                    'name' => $this->input->post('name'),
                    'date' => $this->input->post('date'),
                    'center_id' => $this->input->post('center'),
                ];
                $response['data'] = $data;

                // Attempt to Add Opportunity
                if (!$this->data_model->AddToTable($this->data_model::TABLES['Opportunities'], $data)) {
                    $response['Message'] = 'Opportunity Creation Failed At Database';
                } else {
                    $response['Success'] = true;
                    $response['Message'] = 'Opportunity Creation Succeeded';
                }
            }

            echo json_encode($response);
            return $response['Success'];
        } else {
            echo json_encode($auth);
            return false;
        }
    }
    //------ Edit ------
    function EditOpportunity(): bool {
        $auth = $this->data_model->has_authorization_to($this->data_model::AUTH_ACTIONS['Edit'], $this->data_model::AUTH_ENTITIES['Opportunities']);
        if($auth['Success']){
            // Validate Form
            $response = ['Success' => false];

            // Setup Form Validation
            $valid = true;
            $validationErrors = [];

            // All the Fields
            $fieldList = [  // TYPES: ALPHA, NUMERIC, ALPHANUMERIC, BASIC
                ['name' => 'ID',        'id' => 'id',           'type' => 'numeric',  'max' => 10,    'required' => true,  ],
                ['name' => 'Name',      'id' => 'name',         'type' => 'alpha',    'max' => 100,   'required' => true,  ],
                ['name' => 'Date',      'id' => 'date',         'type' => 'basic',    'max' => 100,   'required' => true,  ],
                ['name' => 'Center',    'id' => 'center',       'type' => 'numeric',  'max' => 10,    'required' => true,  ],
                ['name' => 'Volunteers','id' => 'volunteers',   'type' => 'basic',    'max' => 500,   'required' => false, ],
            ];
            // Check All
            $validation = $this->ValidateFieldList($fieldList);

            // Finalize Validation
            $response['valid'] = $validation['valid'];

            // If Valid -> Add User First
            if ($response['valid'] !== true) {
                $response['Message'] = 'Form Invalid';
                $response['errorsData'] = $validation['validationErrors'];
            } else {
                // Collect Data into one object
                $data = [
                    'name' => $this->input->post('name'),
                    'date' => $this->input->post('date'),
                    'center_id' => $this->input->post('center'),
                ];
                $opportunityID = $this->input->post('id');
                $response['data'] = $data;

                // Attempt to Edit Opportunity
                if (!$this->data_model->EditTableData($this->data_model::TABLES['Opportunities'], $data, $opportunityID)) {
                    $response['Message'] = 'Opportunity Modification Failed At Database';
                } else {
                    $volunteers = $this->input->post('volunteers');
                    $vols = [];
                    if(strlen($volunteers) === 0) {
                        $response['Success'] = true;
                        $response['Message'] = 'Opportunity Modification Succeeded';
                    } else{
                        if(!$this->data_model->DeleteOpportunityVolunteers($opportunityID)) {
                            $response['Message'] = 'Opportunity Modification Failed At Removing the Opportunity Volunteer';
                        } else {
                            if(strlen($volunteers) <= 2) {
                                $vols = [intval($volunteers)];
                            } else {
                                $vols = array_filter(explode(',', $volunteers));
                            }
                            $success = true; $errorCollection = [];
                            foreach ($vols as $vol) {
                                $data = [ 'opportunity_id' => $opportunityID, 'volunteer_id' => $vol ];
                                if(!$this->data_model->AddToTable($this->data_model::TABLES['OppVolunteers'], $data)) {
                                    $errorCollection[] = $vol;
                                    $success = false;
                                }
                            }

                            if(!$success) {
                                $response['Message'] = 'Opportunity Modification Failed At Database';
                                $response['data'] = $errorCollection;
                            } else {
                                $response['Success'] = true;
                                $response['Message'] = 'Opportunity Modification Succeeded';
                            }
                        }
                    }

                }
            }

            echo json_encode($response);
            return $response['Success'];
        } else {
            echo json_encode($auth);
            return false;
        }
    }

    //----- Delete -----
    function DeleteVolunteer(): bool {
        $auth = $this->data_model->has_authorization_to($this->data_model::AUTH_ACTIONS['Delete'], $this->data_model::AUTH_ENTITIES['Volunteers']);
        if($auth['Success']){
            // Validate Form
            $response = ['Success' => false];
            if(!empty($this->input->get('id'))){
                $volunteer_id = intval($this->input->get('id'));

                // TODO: Convert to/Or Create deactivate
                if($this->data_model->DeleteFromTable($this->data_model::TABLES['Users'], $volunteer_id)) {
                    $response['Success'] = true;
                    $response['Message'] = "Volunteer {$volunteer_id} Deletion Succeeded";
                } else {
                    $response['Message'] = "Deletion Failed at the DB";
                }
            } else {
                $response['Message'] = 'Missing Volunteer Id';
            }

            echo json_encode($response);
            return $response['Success'];
        } else {
            echo json_encode($auth);
            return false;
        }
    }

    function DeleteOpportunity(): bool {
        $auth = $this->data_model->has_authorization_to($this->data_model::AUTH_ACTIONS['Delete'], $this->data_model::AUTH_ENTITIES['Opportunities']);
        if($auth['Success']){
            // Validate Form
            $response = ['Success' => false];
            if(!empty($this->input->post('id'))){
                $id = intval($this->input->post('id'));

                if($this->data_model->DeleteFromTable($this->data_model::TABLES['Opportunities'], $id)) {
                    $response['Success'] = true;
                    $response['Message'] = "Opportunity {$id} Deletion Succeeded";
                } else {
                    $response['Message'] = "Opportunity Deletion Failed at the DB";
                }
            } else {
                $response['Message'] = 'Missing Opportunity ID';
            }

            echo json_encode($response);
            return $response['Success'];
        } else {
            echo json_encode($auth);
            return false;
        }
    }
//    //------ Edit ------
//    function EditProjectData(): bool {
//        $auth = $this->data_model->has_authorization_for($this->data_model::AUTH_ACTIONS['Edit']);
//        if($auth['Success']){
//            $response = ['Success' => false];
//            if(!empty($this->input->get('id')) && !empty($this->input->get('prop')) && !empty($this->input->get('value'))) {
//                $id = intval($this->input->get('id'));
//                $prop = $this->input->get('prop');
//                $value = $this->input->get('value');
//
//                if((stripos($prop, 'description') !== FALSE || stripos($prop, 'name') !== FALSE || stripos($prop, 'template_id') !== FALSE)
//                    && strlen($value) >= 1) {
//                    if($this->data_model->EditProjectData($id, $prop, $value)){
//                        $response['Success'] = true;
//                        $response['Message'] = "Project {$id}: {$prop} Updated to  {$value} ";
//                    } else {
//                        $response['Message'] = 'Error Adding to Database';
//                    }
//                } else {
//                    $response['Message'] = "Property or Value not sufficient ({$prop}, {$value})";
//                }
//            } else {
//                $response['Message'] = 'Missing ID or Property or Value';
//            }
//
//            echo json_encode($response);
//            return $response['Success'];
//        } else {
//            echo json_encode($auth);
//            return false;
//        }
//    }

    /**********************       END      **********************/

    //--- Security Utility ---
    private function GenSalt (int $len = 32): string {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789`~!@#$%^&*()-=_+';
        $l = strlen($chars) - 1;
        $str = '';
        for ($i = 0; $i < $len; ++$i) {
            $str .= $chars[rand(0, $l)];
        }
        return $str;
    }

    //--- For Testing ---
    public function CheckPermission () : bool {
        $response = ['Success' => false];
        if(!empty($this->input->post('entity')) && !empty($this->input->post('action')) && !empty($this->input->post('user'))) {
            $response = $this->data_model->has_authorization_to($this->input->post('entity'), $this->input->post('action'), $this->input->post('user'));
        } else {
            $response['Message'] = 'Missing ';
            $response['Message'] .= (empty($this->input->post('entity'))) ? ' Entity,' : '';
            $response['Message'] .= (empty($this->input->post('action'))) ? ' Action,' : '';
            $response['Message'] .= (empty($this->input->post('user'))) ? ' User ' : '';
            $response['Message'] = substr($response['Message'], 0, -1);
        }
        $response['data'] = $response['Message'];

        echo json_encode($response);
        return $response['Success'];
    }

    public function getActions() : bool {
        $response = ['Success' => true, 'Message' => 'Items'];
        $response['data'] = ['Actions' => $this->data_model::AUTH_ACTIONS, 'Entities' => $this->data_model::AUTH_ENTITIES];

        echo json_encode($response);
        return $response['Success'];
    }

    //------ Validation ------
    private function ValidateFieldList ($fieldList) {
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