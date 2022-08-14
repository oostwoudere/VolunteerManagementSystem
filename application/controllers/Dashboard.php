<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property Ion_auth|Ion_auth_model    $ion_auth           The ION Auth Spark Tool Set
 * @property CI_DB_query_builder        $db                 The Database Query Helper
 * @property Data_model                 $data_model         The Data Model
 * @property CI_Session                 $session            The Session Data Helper
 * @property CI_Upload                  $upload             The File Upload Helper
 * @property CI_Input                   $input              The Data Input Helper
 * @property CI_Lang                    $lang               The Language Handler
 */
class Dashboard extends CI_Controller {
    private const ROLES = ['Dev' => 'developer', 'Admin' => 'administrator', 'Vol' => 'volunteer', 'Def' => 'Default'];
    private string $upload_path;
    private array $data;

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('data_model');
        $this->load->library(['ion_auth', 'upload']);
        $this->load->helper(['url', 'language']);
        $this->lang->load('auth');

        $this->upload_path = realpath(APPPATH . '../uploads');
        $uploadConfig = [
            'file_name'     => 'currentUpload',
            'upload_path'   => realpath(APPPATH . '../uploads'),
            'allowed_types' => 'jpg|jpeg|gif|png|pdf',
            'overwrite'     => true,
        ];
        $this->upload->initialize($uploadConfig);

        $this->data = [];
    }

    /****************************** Navigation - Start *******************************/

    //-------------- Dashboard Pages --------------
	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
    public function index() {
        if (!$this->ion_auth->logged_in()) { $this->redirectReturnHere(); }

        $this->loadView('dashboard');
    }

    // View All Volunteers
    public function volunteers() {
        if (!$this->ion_auth->logged_in()) { $this->redirectReturnHere(); }
        if($this->checkRole([Dashboard::ROLES['Dev'], Dashboard::ROLES['Admin']])) {
            $this->loadView('volunteers');
        } else {
            redirect('/dashboard');
        }
    }

    // Add Or View Volunteer
    public function volunteer() {
        if (!$this->ion_auth->logged_in()) { $this->redirectReturnHere(); }
        if($this->checkRole([Dashboard::ROLES['Dev'], Dashboard::ROLES['Admin']])) {
            $id = empty($this->input->get('id')) ? 0 : intval($this->input->get('id'));
            $this->data['method'] = $this->input->method();
            $this->data['centers'] = $this->data_model->LoadCenters();
            if($id) {
                $this->data['id'] = $id;
                $this->loadView('volunteer');
            } else {
                // Detect Form Post
                $this->data['checkPOST'] = (strcmp(strtolower($this->data['method']), 'post') == 0);
                if ($this->data['checkPOST']) {
                    // Attempt Form Upload
                    $this->data['postData'] = $this->AddVolunteer();
                }

                $this->loadView('volunteer-add');
            }
        } else {
            redirect('/dashboard');
        }
    }

    // Edit or Add Volunteer
    public function volunteer_edit() {
        if (!$this->ion_auth->logged_in()) { $this->redirectReturnHere(); }
        // TODO: Do a check if this is the current user
        if($this->checkRole([Dashboard::ROLES['Dev'], Dashboard::ROLES['Admin']])) {
            $id = empty($this->input->get('id')) ? 0 : intval($this->input->get('id'));
            $id = ($id && $id != 0) ? $id : (empty($this->input->post('id')) ? 0 : intval($this->input->post('id')));
            $this->data['method'] = $this->input->method();
            $this->data['centers'] = $this->data_model->LoadCenters();
            if($id) {
                $this->data['user_id'] = $id;
                $this->data['volunteer'] = $this->data_model->LoadVolunteer($id);
                // Detect Form Post
                $this->data['checkPOST'] = (strcmp(strtolower($this->data['method']), 'post') == 0);
                if ($this->data['checkPOST']) {
                    // Attempt Form Upload
                    $this->data['postData'] = $this->EditVolunteer();
                }
                $this->loadView('volunteer-edit');
            } else {
                // Detect Form Post
                $this->data['checkPOST'] = (strcmp(strtolower($this->data['method']), 'post') == 0);
                if ($this->data['checkPOST']) {
                    // Attempt Form Upload
                    $this->data['postData'] = $this->AddVolunteer();
                }
                $this->loadView('volunteer-add');
            }
        } else {
            redirect('/dashboard');
        }
    }



    // View All Opportunities
    public function opportunities() {
        if (!$this->ion_auth->logged_in()) { $this->redirectReturnHere(); }
        if($this->checkRole([Dashboard::ROLES['Dev'], Dashboard::ROLES['Admin']])) {
            if(!empty($this->input->post('center'))) {
                $this->data = $this->input->post('center');
                $this->data['opportunities'] = $this->data_model->loadOpportunityVolunteerOptions(12);
            }

            $this->loadView('opportunities');
        } else {
            redirect('/dashboard');
        }
    }

    // Add Or View Opportunity
    public function opportunity() {
        if (!$this->ion_auth->logged_in()) { $this->redirectReturnHere(); }
        if($this->checkRole([Dashboard::ROLES['Dev'], Dashboard::ROLES['Admin']])) {
            $id = empty($this->input->get('id')) ? 0 : intval($this->input->get('id'));
            if($id) {
                $this->data['id'] = $id;
                $this->loadView('opportunity');
            } else {
                $this->data['centers'] = $this->data_model->LoadCenters();
                $this->loadView('opportunity-add');
            }
        } else {
            redirect('/dashboard');
        }
    }

    // Edit or Add Opportunity
    public function opportunity_edit() {
        if (!$this->ion_auth->logged_in()) { $this->redirectReturnHere(); }
        // TODO: Do a check if this is the current user
        if($this->checkRole([Dashboard::ROLES['Dev'], Dashboard::ROLES['Admin']])) {
            $id = empty($this->input->get('id')) ? 0 : intval($this->input->get('id'));
            $this->data['centers'] = $this->data_model->LoadCenters();
            if($id) {
                $this->data['id'] = $id;
                $this->loadView('opportunity-edit');
            } else {
                $this->loadView('opportunity-add');
            }
        } else {
            redirect('/dashboard');
        }
    }


    //--------------- Specialty/Dev ---------------

    public function audit() {
        if (!$this->ion_auth->logged_in()) { $this->redirectReturnHere(); }
        if($this->checkRole([Dashboard::ROLES['Dev']])) {
            $this->loadView('audit');
        } else {
            redirect('/dashboard');
        }
    }

    public function developer() {
        // TODO: Make this for control and db fixes
    }

    public function test() {
        if (!$this->ion_auth->logged_in()) { $this->redirectReturnHere(); }
        if($this->checkRole([Dashboard::ROLES['Dev']])) {
            $this->loadView('test');
        } else {
            redirect('/dashboard');
        }
    }

    /******************************* Navigation - END ********************************/

    /**************************** Volunteer Forms - Start ****************************/
    //-------------------- ADD --------------------
    private function AddVolunteer(): array {
        $auth = $this->data_model->has_authorization_to($this->data_model::AUTH_ACTIONS['Add'], $this->data_model::AUTH_ENTITIES['Volunteers']);
        if($auth['Success']){
            // Validate Form
            $response = ['Success' => false];
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
            $validation = $this->data_model->ValidateFieldList($fieldList);
            if(!$validation['valid']) {
                $valid = false;
                array_push($validationErrors, $validation['errorsData']);
            }

            // Check Username/Email for Uniqueness
            if($this->data_model->TestUserUsername($this->input->post('username')) != 0) {
                $valid = false;
                $validationErrors[] = ['id' => 'username', 'data' => 'Must Have a Unique Identity (Username Taken)'];
            }
            if($this->data_model->TestUserEmail($this->input->post('email')) != 0) {
                $valid = false;
                $validationErrors[] = ['id' => 'email',    'data' => 'Must Be Unique (Email Already in Use)'];
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
                $salt = $this->data_model->GenSalt();
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
                        'centers' => $this->input->post('centers') ?? '',
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
                        $driverSuccess = false;
                        $this->upload_path = (empty($this->upload_path)) ? realpath(APPPATH . '/../uploads') : $this->upload_path;

                        // Attempt Drivers Upload
                        $driversName = "{$user_id}_drivers";
                        $drivers = "{$this->upload_path}\\{$driversName}";
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
                            // Rename Uploaded File
                            $uploadData = $this->upload->data();
                            $drivers .= $uploadData['file_ext'];
                            $driversName .= $uploadData['file_ext'];
                            rename($uploadData['full_path'], $drivers);

                            // Attempt Update Profile
                            if(!$this->data_model->EditTableData($this->data_model::TABLES['Volunteers'], ['drivers' => $driversName], $user_id, 'user_id')){
                                $response['data'] = ['id' => $user_id];
                                $response['Message'] = "Volunteer Creation Failed At Drivers Upload ";
                            } else {
                                $driverSuccess =  true;
                            }
                        }

                        // If Social
                        $socialName = "{$user_id}_social";
                        $social = "{$this->upload_path}\\{$socialName}";
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
                            // Rename Uploaded File
                            $uploadData = $this->upload->data();
                            $social .= $uploadData['file_ext'];
                            $socialName .= $uploadData['file_ext'];
                            rename($uploadData['full_path'], $social);

                            // Attempt Update Profile
                            if(!$this->data_model->EditTableData($this->data_model::TABLES['Volunteers'], ['social' => $socialName], $user_id, 'user_id')){
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
                                    $response['data'] = [
                                        'id' => $user_id,
                                        'drivers' => $drivers,
                                        'social' => $social,
                                        'uploads' => $this->upload->error_msg,
                                        'uploadPath' => $this->upload_path,
                                    ];
                                    $response['Message'] = "Volunteer Creation Succeeded";
                                }
                            }
                        }
                    }
                }
            }

            return $response;
        } else {
            return $auth;
        }
    }

    //-------------------- EDIT -------------------
    private function EditVolunteer(): array {
        $auth = $this->data_model->has_authorization_to($this->data_model::AUTH_ACTIONS['Edit'], $this->data_model::AUTH_ENTITIES['Volunteers']);
        if($auth['Success']){
            // Validate Form
            $response = ['Success' => false];
            $max_t_length = 250;
            $max_l_length = 100;

            // Setup Form Validation
            $valid = true;
            $validationErrors = [];

            // All the Fields
            $editFieldList = [  // TYPES: ALPHA, NUMERIC, ALPHANUMERIC, BASIC
                ['name' => 'First Name',                'id' => 'first_name',   'type' => 'alpha',          'max' => $max_l_length, 'required' => true,  ],
                ['name' => 'Last Name',                 'id' => 'last_name',    'type' => 'alpha',          'max' => $max_l_length, 'required' => true,  ],
                ['name' => 'Username',                  'id' => 'username',     'type' => 'alpha',          'max' => $max_l_length, 'required' => true,  ],
                ['name' => 'Email',                     'id' => 'email',        'type' => 'email',          'max' => $max_l_length, 'required' => true,  ],
                ['name' => 'Password',                  'id' => 'password',     'type' => 'basic',          'max' => $max_l_length, 'required' => false, ],
                ['name' => 'Confirm Password',          'id' => 'pass_confirm', 'type' => 'basic',          'max' => $max_l_length, 'required' => false, ],
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
            $validation = $this->data_model->ValidateFieldList($editFieldList);
            if(!$validation['valid']) {
                $valid = false;
                array_push($validationErrors, $validation['errorsData']);
            }

            // Check Username/Email for Uniqueness
            if($this->data_model->TestUserUsername($this->input->post('username')) > 1) {
                $valid = false;
                $validationErrors[] = ['id' => 'username', 'data' => 'Must Have a Unique Identity (Username Taken)'];
            }
            if($this->data_model->TestUserEmail($this->input->post('email')) > 1) {
                $valid = false;
                $validationErrors[] = ['id' => 'email',    'data' => 'Must Be Unique (Email Already in Use)'];
            }

            // Check Password Confirmation
            if(strlen($this->input->post('password')) > 1) {
                if($this->input->post('password') != $this->input->post('pass_confirm')) {
                    $valid = false;
                    $validationErrors[] = ['id' => 'pass_confirm', 'data' => 'Password Confirmation must match password'];
                }
            }

            // Finalize Validation
            $response['valid'] = $valid;

            // If Valid -> Add User First
            if ($response['valid'] !== true) {
                $response['Message'] = 'Form Invalid';
                $response['errorsData'] = $validationErrors;
            } else {
                // Get User
                $user_id = $this->data['user_id'] ?? $this->input->get('id');

                // Collect Data into one object
                $user_data = [
                    'first_name' => $this->input->post('first_name'),
                    'last_name' => $this->input->post('last_name'),
                    'username' => $this->input->post('username'),
                    'email' => strtolower($this->input->post('email')),
                ];

                // Generate Secure Password
                if($this->input->post('password')) {
                    $salt = $this->data_model->GenSalt();
                    $salted_password = $salt . $this->input->post('pass');
                    $user_data['salt'] = $salt;
                    $user_data['password'] = hash('sha256', $salted_password);
                }

                // Attempt to Add User
                if(!$this->data_model->EditTableData($this->data_model::TABLES['Users'], $user_data, $user_id)) {
                    $response['Message'] = 'Failed At Database - Edit User Data';
                    $response['data'] = $user_data;
                } else {
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
                    ];

                    if(!$this->data_model->EditTableData($this->data_model::TABLES['Volunteers'], $volunteerData, $user_id,'user_id')){
                        $response['Message'] = 'Failed At Database - Edit Volunteer Data';
                        $response['data'] = ['user' => $user_data, 'volunteer' => $volunteerData];
                    } else {
                        // If Volunteer Made -> Attempt File Upload
                        $driverSuccess = false;
                        $this->upload_path = (empty($this->upload_path)) ? realpath(APPPATH . '/../uploads') : $this->upload_path;

                        // Attempt Drivers Upload
                        if(is_uploaded_file($_FILES['drivers']['name'])) {
                            $driversName = "{$user_id}_drivers";
                            $drivers = "{$this->upload_path}\\{$driversName}";
                            $this->upload->set_filename($this->upload_path, $user_id . '_drivers');
                            if (!$this->upload->do_upload('drivers')) {
                                $response['errorsData'] = $this->upload->error_msg;
                                $response['Message'] = "Volunteer Modification Failed At Drivers License Upload ";
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
                                // Rename Uploaded File
                                $uploadData = $this->upload->data();
                                $drivers .= $uploadData['file_ext'];
                                $driversName .= $uploadData['file_ext'];
                                // First Remove Previous File
                                if (file_exists($drivers) && file_exists($uploadData['full_path'])) {
                                    unlink($drivers);
                                }
                                // Then Rename the New File
                                rename($uploadData['full_path'], $drivers);

                                // Attempt Update Profile
                                if (!$this->data_model->EditTableData($this->data_model::TABLES['Volunteers'], ['drivers' => $driversName], $user_id, 'user_id')) {
                                    $response['data'] = ['id' => $user_id];
                                    $response['Message'] = "Volunteer Modification Failed At Drivers Upload ";
                                } else {
                                    $driverSuccess = true;
                                    $response['Message'] = '';
                                }
                            }
                        } else {
                            $driverSuccess = true;
                        }

                        if(is_uploaded_file($_FILES['social']['name'])) {
                            // If Social
                            $socialName = "{$user_id}_social";
                            $social = "{$this->upload_path}\\{$socialName}";
                            // Attempt Social Upload
                            $this->upload->set_filename($this->upload_path, $user_id . '_social');
                            if (!$this->upload->do_upload('social')) {
                                $this->data_model->AuditAction('Upload Social', 'Social Upload');
                                $socialErrorData = ['post' => $this->input->post('social'), 'social' => $social, 'data' => $this->upload->data(),];
                                if ($driverSuccess) { // No Data to Override
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
                                // Rename Uploaded File
                                $uploadData = $this->upload->data();
                                $social .= $uploadData['file_ext'];
                                $socialName .= $uploadData['file_ext'];
                                // First Remove Previous File
                                if (file_exists($social) && file_exists($uploadData['full_path'])) {
                                    unlink($social);
                                }
                                // Then Rename the New File
                                rename($uploadData['full_path'], $social);

                                // Attempt Update Profile
                                if (!$this->data_model->EditTableData($this->data_model::TABLES['Volunteers'], ['social' => $socialName], $user_id, 'user_id')) {
                                    if ($driverSuccess) { // No Data to Override
                                        $response['Message'] = "Volunteer Creation Failed At Social Upload";
                                        $response['errorsData'] = ['social' => $social];
                                    } else {    // Save in Addition to Driver Data
                                        $response['Message'] .= "And Social Upload";
                                        $response['errorsData']['social'] = $social;
                                    }
                                } else {
                                    $response['Success'] = $driverSuccess;
                                    if ($driverSuccess) { // No Data
                                        $response['data'] = [
                                            'id' => $user_id,
                                            'drivers' => $drivers ?? 'Not Uploaded',
                                            'social' => $social,
                                            'uploads' => $this->upload->error_msg,
                                            'uploadPath' => $this->upload_path,
                                        ];
                                        $response['Message'] = "Volunteer Creation Succeeded";
                                    }
                                }
                            }
                        } else {
                            $response['Success'] = $driverSuccess;
                            if ($driverSuccess) { // No Data
                                $response['data'] = ['id' => $user_id, 'uploads' => 'None'];
                                $response['Message'] = "Volunteer Creation Succeeded";
                            }
                        }
                    }
                }
            }

            return $response;
        } else {
            return $auth;
        }
    }

    /***************************** Volunteer Forms - END *****************************/

    /******************************** Utility - Start ********************************/
    //------------------ Helpers ------------------

    /**
     * Loads Page with Default Header
     * @param string $page      Page Name (dashboard/page-$page)
     * @param string $styles    Styles (baseurl($styles)
     * @param string $scripts   Scripts (baseurl($scripts))
     */
    private function loadView (string $page = '', string $styles = '', string $scripts = '') {
        $this->data['page'] = (strlen($page) !== 0) ? $page : 'dashboard';
        $this->data['styles'] = (strlen($styles) !== 0) ? $styles : false;
        $this->data['scripts'] = (strlen($scripts) !== 0) ? $scripts : false;
        $this->data['user_role'] = $this->getRole() ?? 'Default';

        $this->load->view('components/header', $this->data);
        $this->load->view("dashboard/page-{$this->data['page']}", $this->data);
        $this->load->view('components/footer');
    }

    /**
     * Get Current User Role (Name)
     * @return false|mixed  String of Current User Role Name | False if No User/User Role
     */
    private function getRole() : mixed {
        $query = $this->db->select('name')
                        ->join('users', 'users.role = user_roles.id', 'left')
                        ->where('users.id', $this->ion_auth->get_user_id())
                        ->limit(1)
                        ->get('user_roles');
        if(count($query->result_array()) !== 1) return false;
        return  $query->result_array()[0]['name'];
    }

    /** Check User Role
     * @param $accepted array    Accepted Field(s), False/Empty for "Default"
     * @return bool     Whether the current User role is accepted
     */
    private function checkRole($accepted) : bool {
        $accepted = is_array($accepted) ? $accepted : ['Default'];
        $role = $this->getRole();
        foreach ($accepted as $item) {
            if($role == $item) return true;
        }
        return false;
    }

    //------------------ Utility ------------------

    /** Redirect Return Here
     * Redirects to $destination <br>
     * After Destination Redirects Here <br>
     * Default: '/login/'   <- Goes to log in, after logging in, redirect back here
     * @param string $destination   String of Location (as if this was redirect( $destination )
     */
    private function redirectReturnHere($destination = '/login') {
        $this->session->set_userdata('referrer_url', current_url());
        redirect($destination);
    }
    /********************************* Utility - END *********************************/
}
