<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Auth
 * @property Ion_auth|Ion_auth_model    $ion_auth           The ION Auth spark
 * @property CI_DB_query_builder        $db                 The Database Query Helper
 * @property CI_Form_validation         $form_validation    The form validation library
 * @property Audit_model                $Audit_model        The Audit Log Handler
 * @property CI_Session                 $session            The Session Handler
 * @property CI_Config                  $config             The Language Handler
 * @property CI_Upload                  $upload             The Upload Helper
 * @property CI_Input                   $input              The Input Handler
 * @property CI_Lang                    $lang               The Language Handler
 */
class Auth extends CI_Controller {
    const USER_TABLE = 'users';
    const VOLUNTEER_TABLE = 'volunteers_data';

	public $data = [];

	public function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->library(['ion_auth', 'form_validation']);
		$this->load->helper(['url', 'language']);
		$this->lang->load('auth');

		$this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
	}

	public function UserLogin() {
        if ($this->ion_auth->logged_in()) !$this->attemptReturn() ?? redirect('/', 'refresh');

        $user = $this->input->post('user');

        if ($this->ion_auth->login($user, $this->input->post('pass'), true)) {
            $this->session->set_flashdata('message', $this->ion_auth->messages());

            //Insert successful login into the audit log.
            $this->Audit_model->LogItem(
                array(
                    'location' => 'Auth',
                    'action' => 'User Login',
                    'data' => "$user Logged In Successfully!",
                    'user_id' => $this->session->userdata('user_id'),
                    'status' => 'success',
                )
            );

            if(!$this->attemptReturn())
                redirect('/dashboard', 'refresh');
        } else {
            $this->session->set_flashdata('message', $this->ion_auth->errors());

            if ($user) {
                //Insert failed login into the audit log.
                $this->Audit_model->LogItem(
                    array(
                        'location' => 'Auth',
                        'action' => 'Login',
                        'data' => "User {$user} failed login attempt!",
                        'user_id' => 'n/a',
                        'status' => 'danger',
                    )
                );
            }
        }

        $this->load->view('page-login');
    }

    public function UserLogout() {
        if ($this->ion_auth->logged_in()) {
            //Insert successful logout into the audit log.
            $this->Audit_model->LogItem(
                array(
                    'location' => 'Auth',
                    'action' => 'User Logout',
                    'data' => $this->session->userdata('username') . " Logged out successfully!",
                    'user_id' => $this->session->userdata('user_id'),
                    'status' => 'success',
                )
            );
            $this->ion_auth->logout();
            redirect('/', 'refresh');
        } else {
            //Insert failed logout into the audit log.
            $this->Audit_model->LogItem(
                array(
                    'location' => 'Auth',
                    'action' => 'User Logout',
                    'data' => $this->session->userdata('username') . " failed to logout!",
                    'user_id' => $this->session->userdata('user_id'),
                    'status' => 'danger',
                )
            );

            $this->session->set_flashdata('message', $this->ion_auth->errors());
        }

        $this->load->view('page-login');
    }

    public function UserRegistration() {
        // Stop if Already Logged In
        if ($this->ion_auth->logged_in()) !$this->attemptReturn() ?? redirect('/', 'refresh');

        $passRegex = "/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9])(?=.{10,})/";
        $min_length = $this->config->item('min_password_length', 'ion_auth');

        // Setup Form Validation
        $this->form_validation->set_rules('first_name', $this->lang->line('create_user_validation_fname_label'), 'required|alpha_numeric_spaces');
        $this->form_validation->set_rules('last_name', $this->lang->line('create_user_validation_lname_label'), 'required|alpha_numeric_spaces');

        $this->form_validation->set_rules('username', $this->lang->line('create_user_validation_username_label'), 'required|is_unique[users.username]');
        $this->form_validation->set_rules('email', $this->lang->line('create_user_validation_email_label'), 'required|valid_email|is_unique[users.email]');

        $this->form_validation->set_rules('pass', $this->lang->line('create_user_validation_password_label'), "required|min_length[{$min_length}]|regex_match[{$passRegex}]|matches[pass_confirm]");
        $this->form_validation->set_rules('pass_confirm', $this->lang->line('create_user_validation_password_confirm_label'), 'required');

        // Run Validation
        if ($this->form_validation->run() === TRUE) {
            // Clean Up Inputs
            $email = strtolower($this->input->post('email'));
            // Generate Secure Password
            $salt = $this->GenSalt();
            $salted_password = $salt . $this->input->post('pass');

            $user_data = [
                'first_name' => $this->input->post('first_name'),
                'last_name' => $this->input->post('last_name'),
                'username' => $this->input->post('username'),
                'email' => $email,
                'salt' => $salt,
                'password' => hash('sha256', $salted_password),
                'active' => 1,
                'role' => 6,
            ];

            // Insert User
            if ($this->InsertIntoTable(Auth::USER_TABLE, $user_data)) {
                $this->session->set_flashdata('message', 'Registration Successful');
                redirect("/login", 'refresh');
            } else {
                $this->session->set_flashdata('message', 'Registration Failed');
            }
        } else {
            $this->session->set_flashdata('message', $this->ion_auth->messages());
        }

        $this->data['post_data'] = $this->input->post();
        $this->data['message'] = $this->session->flashdata('message');

        $this->load->view('page-register', $this->data);
    }


    // TODO: Test + Verify File Uploading + Setup For Other Access
    public function VolunteerRegistration() {
        // Stop if Already Logged In
//        if ($this->ion_auth->logged_in())  redirect('/', 'refresh');
//        $passRegex = "/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9])(?=.{10,})/g";
//        $min_length = $this->config->item('min_password_length', 'ion_auth');
        $max_t_length = $this->config->item('max_text_length', 'ion_auth');
        $max_l_length = $this->config->item('max_line_length', 'ion_auth');

        // Setup Form Validation
        $this->form_validation->set_rules('first_name', "first_name",   "required|alpha_space|max_length[{$max_l_length}]");
        $this->form_validation->set_rules('last_name',  "last_name",    "required|alpha_space|max_length[{$max_l_length}]");

        $this->form_validation->set_rules('username',   "username", "required|is_unique[users.username]|max_length[{$max_l_length}]");
        $this->form_validation->set_rules('email',      "email",    "required|valid_email|is_unique[users.email]|max_length[{$max_l_length}]");

        $this->form_validation->set_rules('pass',       "pass",             "required||max_length[{$max_l_length}]"); // |min_length[{$min_length}]|regex_match[{$passRegex}]
        $this->form_validation->set_rules('pass_confirm',"pass_confirm",    "matches[pass]");

        $this->form_validation->set_rules('skills',     "skills",    "alpha_numeric_punct|max_length[{$max_t_length}]");
        $this->form_validation->set_rules('available',  "available", "alpha_numeric_punct|max_length[{$max_t_length}]");

        $this->form_validation->set_rules('address',    "address",  "required|alpha_numeric_space||max_length[{$max_t_length}]");
        $this->form_validation->set_rules('home',       "home",     "alpha_numeric_space|max_length[{$max_l_length}]");
        $this->form_validation->set_rules('work',       "work",     "alpha_numeric_space|max_length[{$max_l_length}]");
        $this->form_validation->set_rules('cell',       "cell",     "alpha_numeric_space|max_length[{$max_l_length}]");
        $this->form_validation->set_rules('center',     "center",   "alpha_numeric_punct|max_length[{$max_l_length}]");

        $this->form_validation->set_rules('background', "background",   "alpha_numeric_punct|max_length[{$max_t_length}]");
        $this->form_validation->set_rules('licenses',   "licenses",     "alpha_numeric_punct|max_length[{$max_t_length}]");

        $this->form_validation->set_rules('e_name',     "e_name",   "alpha_space|max_length[{$max_l_length}]");
        $this->form_validation->set_rules('e_phone',    "e_phone",  "alpha_numeric_space|max_length[{$max_l_length}]");
        $this->form_validation->set_rules('e_email',    "e_email",  "valid_email");
        $this->form_validation->set_rules('e_address',  "e_address", "alpha_numeric_space");

        $this->form_validation->set_rules('drivers',    "drivers", "uploaded[drivers]|ext_in[drivers.png.jpg.jpeg.gif.pdf]");
        $this->form_validation->set_rules('social',     "social",   "uploaded[social]|ext_in[social.png.jpg.jpeg.gif.pdf]");

        // Run Validation
        if ($this->form_validation->run() === TRUE) {
            // Clean Up Inputs
            $email = strtolower($this->input->post('email'));
            // Generate Secure Password
            $salt = $this->GenSalt();
            $salted_password = $salt . $this->input->post('pass');

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

            // Insert User First
            if ($this->InsertIntoTable(Auth::USER_TABLE, $user_data)) {
                // If User Created Successfully -> Build Volunteer Data
                // First, Get User to Link Data to
                $user = $this->ion_auth->get_user_id_from_username($user_data['username']);

                // TODO: MOVE FILE UPLOAD AFTER VOLUNTEER DATA UPLOAD (THEN UPDATE VOLUNTEER DATA)
                // Attempt File Upload(s)
                $drivers = '';
                $social = '';
                if(!empty($this->input->post('drivers'))) {
                    $this->upload->set_filename('./uploads/', $user.'_drivers');
                    if($this->upload->do_upload('drivers')) {
                        $drivers = "./uploads/{$user}_drivers";
                    }
                }
                if(!empty($this->input->post('social'))) {
                    $this->upload->set_filename('./uploads/', $user.'_social');
                    if($this->upload->do_upload('social')) {
                        $social = "./uploads/{$user}_social";
                    }
                }

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
                    'drivers' => $drivers,
                    'social' => $social,
                    'user_id' => $user,
                ];

                if($this->InsertIntoTable(Auth::VOLUNTEER_TABLE, $volunteerData)) {
                    $this->session->set_flashdata('message', 'Registration Successful');
                    redirect("/login", 'refresh');
                } else {
                    $this->session->set_flashdata('message', 'Registration Failed At Volunteer Upload');
                }
            } else {
                $this->session->set_flashdata('message', 'Registration Failed');
            }
        } else {
            $this->session->set_flashdata('message', $this->ion_auth->messages());
        }

        $this->data['post_data'] = $this->input->post();
        $this->data['message'] = $this->session->flashdata('message');

        $this->load->view('page-register-volunteer', $this->data);
    }

    /** Insert Data Into DB Table
     * @param $table    string      Table Name
     * @param $data     array   Table Data
     * @return bool Whether the data successfully was inserted into the table
     */
    public function InsertIntoTable($table, $data): bool {
        if (empty($table) || empty($data)) return false;

        if ($this->db->insert($table, $data)) {
            //Insert successful register into the audit log.
            $this->Audit_model->LogItem(
                array(
                    'location' => 'Auth',
                    'action' => "Insert {$table}",
                    'data' => "{$table} created successfully!",
                    'user_id' => ($this->ion_auth->get_user_id()) ?? 'n/a',
                    'status' => 'success',
                )
            );
            return true;
        } else {
            //Insert failed registration into the audit log.
            $this->Audit_model->LogItem(
                array(
                    'location' => 'Auth',
                    'action' => "Insert {$table}",
                    'data' => "{$table} creation failed!",
                    'user_id' => ($this->ion_auth->get_user_id()) ?? 'n/a',
                    'status' => 'danger',
                )
            );
            return false;
        }
    }


    public function TestUploads () {
        if(!$this->ion_auth->logged_in()) { $this->redirectReturnHere('/login'); }

        $this->form_validation->set_rules('drivers', 'Document', 'required|callback_file_test_d');
        $this->form_validation->set_rules('social', 'Document', 'required|callback_file_test_s');

//        $this->form_validation->set_rules('drivers', $this->lang->line('create_user_validation_drivers_label'), 'required|uploaded[drivers]|ext_in[drivers.png.jpg.jpeg.gif.pdf]');
//        $this->form_validation->set_rules('social', $this->lang->line('create_user_validation_social_label'), 'required|uploaded[social]|ext_in[social.png.jpg.jpeg.gif.pdf]');

//        $this->form_validation->set_message('uploaded', 'Must Be Uploaded.');
//        $this->form_validation->set_message('ext_in', 'Must Be Image or PDF (PNG, JPG, GIF, PDF).');

        if ($this->form_validation->run() === TRUE) {
            // Use My User
            $user = $this->ion_auth->get_user_id();

            // Attempt File Upload(s)
            $drivers = '';
            $social = '';
//            if (!empty($this->input->post('drivers'))) {
//                $this->upload->set_filename('./uploads/', "{$user}_drivers");
//                if($this->upload->do_upload('drivers')) {
//                    $drivers = "./uploads/{$user}_drivers";
//                }
//            }
//            if (!empty($this->input->post('social'))) {
//                $this->upload->set_filename('./uploads/', "{$user}_social");
//                if ($this->upload->do_upload('social')) {
//                    $social = "./uploads/{$user}_social";
//                }
//            }
//
//            $vol_data = [
//                'user_id' => $user,
//                'drivers' => $drivers,
//                'social' => $social,
//            ];
//
//            if($this->InsertIntoTable('volunteers_data', $vol_data)) {
//                redirect('/');
//            } else {
//                $this->session->set_flashdata('message', $this->ion_auth->messages());
//            }
            $this->session->set_flashdata('message', $_FILES['drivers']['name']);
        } else {
            $this->session->set_flashdata('message', $this->ion_auth->messages());
        }

        $this->data['post_data'] = $this->input->post();
        $this->data['message'] = $this->session->flashdata('message');

        $this->load->view('page-test', $this->data);
    }

    function file_test_d (){
        if (empty($_FILES['drivers']['name'])) {
            $this->form_validation->set_message('file_test_d', 'Please select file.');
            return false;
        }else{
            return true;
        }
    }

    function file_test_s (){
        if (empty($_FILES['social']['name'])) {
            $this->form_validation->set_message('file_test_s', 'Please select file.');
            return false;
        }else{
            return true;
        }
    }





    public function InsertUser($data) {
        if ($this->db->insert('users', $data)) {
            //Insert successful register into the audit log.
            $this->Audit_model->LogItem(
                array(
                    'location' => 'Auth',
                    'action' => 'Insert User',
                    'data' => "User {$data['username']} created successfully!",
                    'user_id' => 'n/a',
                    'status' => 'success',
                )
            );
            return true;
        } else {
            //Insert failed registration into the audit log.
            $this->Audit_model->LogItem(
                array(
                    'location' => 'Auth',
                    'action' => 'Insert User',
                    'data' => "User {$data['username']} registration failed!",
                    'user_id' => 'n/a',
                    'status' => 'danger',
                )
            );
            return false;
        }
    }

    private function CreateCompany($company_name){
        $this->db->insert('companies', ['name' => $company_name]);
        $companies = $this->db->select('id')->where('name', $company_name)->limit(1)->get('companies');
        $company = "";
        foreach ($companies->result() as $comp) { $company = $comp->id; }
        return $company;
    }

    /**
    *
    */
    public function EditUser() {
        if (!$this->ion_auth->logged_in()) {
            $this->session->set_flashdata('message', 'Profile Edits Failed');
        } else {
            // Validate Form
            $this->form_validation->set_rules('first_name', $this->lang->line('edit_user_validation_fname_label'), 'trim|required');
            $this->form_validation->set_rules('last_name', $this->lang->line('edit_user_validation_lname_label'), 'trim|required');
            $this->form_validation->set_rules('email', $this->lang->line('edit_user_validation_email_label'), 'trim|required|valid_email');

            if($this->input->post('password') != null){
                $this->form_validation->set_rules('password', $this->lang->line('edit_user_validation_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[passconf]');
                $this->form_validation->set_rules('passconf', $this->lang->line('edit_user_validation_password_confirm_label'), 'required');
                $pass = true;
            } else {
                $pass = false;
            }

            // If Valid -> Edit current User
            if ($this->form_validation->run() === TRUE) {
                $email = strtolower($this->input->post('email'));
                $company_id = filter_var($this->input->post('company'), FILTER_SANITIZE_NUMBER_INT);
                if($company_id <= 0){
                    $company_id = $this->CreateCompany(htmlspecialchars($this->input->post('companyCustom')));
                }

                if($pass) {
                    $password = $this->input->post('password');
                    $salt = $this->GenSalt();
                    $salted_password = $salt . $password;

                    $user_data = [
                        'first_name' => $this->input->post('first_name'),
                        'last_name' => $this->input->post('last_name'),
                        'username' => $this->input->post('username'),
                        'email' => $email,
                        'salt' => $salt,
                        'password' => hash('sha256', $salted_password),
                        'company' => $company_id,
                        'phone' => $this->input->post('phone'),
                    ];
                } else {
                    $user_data = [
                        'first_name' => $this->input->post('first_name'),
                        'last_name' => $this->input->post('last_name'),
                        'username' => $this->input->post('username'),
                        'email' => $email,
                        'company' => $company_id,
                        'phone' => $this->input->post('phone'),
                    ];
                }

                if ($this->UpdateUser($user_data, $this->ion_auth->get_user_id())) {
                    $this->session->set_flashdata('message', 'Profile Edited Successful');
                } else {
                    $this->session->set_flashdata('message', 'Registration Failed');
                }
            }
        }

        $this->data['post_data'] = $this->input->post();
        $this->data['message'] = $this->session->flashdata('message');

        $this->load->view('pages/dashboard/page-profile', $this->data);
    }

    private function UpdateUser($data, $id) : bool {
        if($this->db->update('users', $data, ['id' => $id])) {
            return true;
        } else {
            return false;
        }
    }


    private function GenSalt($len = 32) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789`~!@#$%^&*()-=_+';
        $l = strlen($chars) - 1;
        $str = '';
        for ($i = 0; $i < $len; ++$i) {
            $str .= $chars[rand(0, $l)];
        }
        return $str;
    }


    //------------- START - Return Helpers ----------------
    private function attemptReturn() : bool {
        if( $this->session->userdata('referrer_url') ) {
            //Store in a variable so that can unset the session
            $redirect_back = $this->session->userdata('referrer_url');
            $this->session->unset_userdata('referrer_url');
            redirect( $redirect_back );
            return true;
        } else {
            return false;
        }
    }

    private function redirectReturnHere($destination = '/') {
        $this->session->set_userdata('referrer_url', current_url());
        redirect($destination);
    }
    //-------------- END - Return Helpers -----------------


    /**
	 * Redirect if needed, otherwise display the user list
	 */
	public function index()
	{

		if (!$this->ion_auth->logged_in())
		{
			// redirect them to the login page
			redirect('auth/login', 'refresh');
		}
		else if (!$this->ion_auth->is_admin()) // remove this elseif if you want to enable this for non-admins
		{
			// redirect them to the home page because they must be an administrator to view this
			show_error('You must be an administrator to view this page.');
		}
		else
		{
			$this->data['title'] = $this->lang->line('index_heading');
			
			// set the flash data error message if there is one
			$this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

			//list the users
			$this->data['users'] = $this->ion_auth->users()->result();
			
			//USAGE NOTE - you can do more complicated queries like this
			//$this->data['users'] = $this->ion_auth->where('field', 'value')->users()->result();
			
			foreach ($this->data['users'] as $k => $user)
			{
				$this->data['users'][$k]->groups = $this->ion_auth->get_users_groups($user->id)->result();
			}

			$this->_render_page('auth' . DIRECTORY_SEPARATOR . 'index', $this->data);
		}
	}

	/**
	 * Change password
	 */
	public function change_password()
	{
		$this->form_validation->set_rules('old', $this->lang->line('change_password_validation_old_password_label'), 'required');
		$this->form_validation->set_rules('new', $this->lang->line('change_password_validation_new_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[new_confirm]');
		$this->form_validation->set_rules('new_confirm', $this->lang->line('change_password_validation_new_password_confirm_label'), 'required');

		if (!$this->ion_auth->logged_in())
		{
			redirect('auth/login', 'refresh');
		}

		$user = $this->ion_auth->user()->row();

		if ($this->form_validation->run() === FALSE)
		{
			// display the form
			// set the flash data error message if there is one
			$this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

			$this->data['min_password_length'] = $this->config->item('min_password_length', 'ion_auth');
			$this->data['old_password'] = [
				'name' => 'old',
				'id' => 'old',
				'type' => 'password',
			];
			$this->data['new_password'] = [
				'name' => 'new',
				'id' => 'new',
				'type' => 'password',
				'pattern' => '^.{' . $this->data['min_password_length'] . '}.*$',
			];
			$this->data['new_password_confirm'] = [
				'name' => 'new_confirm',
				'id' => 'new_confirm',
				'type' => 'password',
				'pattern' => '^.{' . $this->data['min_password_length'] . '}.*$',
			];
			$this->data['user_id'] = [
				'name' => 'user_id',
				'id' => 'user_id',
				'type' => 'hidden',
				'value' => $user->id,
			];

			// render
			$this->_render_page('auth' . DIRECTORY_SEPARATOR . 'change_password', $this->data);
		}
		else
		{
			$identity = $this->session->userdata('identity');

			$change = $this->ion_auth->change_password($identity, $this->input->post('old'), $this->input->post('new'));

			if ($change)
			{
				//if the password was successfully changed
				$this->session->set_flashdata('message', $this->ion_auth->messages());
				$this->logout();
			}
			else
			{
				$this->session->set_flashdata('message', $this->ion_auth->errors());
				redirect('auth/change_password', 'refresh');
			}
		}
	}

	/**
	 * Forgot password
	 */
	public function forgot_password()
	{
		$this->data['title'] = $this->lang->line('forgot_password_heading');
		
		// setting validation rules by checking whether identity is username or email
		if ($this->config->item('identity', 'ion_auth') != 'email')
		{
			$this->form_validation->set_rules('identity', $this->lang->line('forgot_password_identity_label'), 'required');
		}
		else
		{
			$this->form_validation->set_rules('identity', $this->lang->line('forgot_password_validation_email_label'), 'required|valid_email');
		}


		if ($this->form_validation->run() === FALSE)
		{
			$this->data['type'] = $this->config->item('identity', 'ion_auth');
			// setup the input
			$this->data['identity'] = [
				'name' => 'identity',
				'id' => 'identity',
			];

			if ($this->config->item('identity', 'ion_auth') != 'email')
			{
				$this->data['identity_label'] = $this->lang->line('forgot_password_identity_label');
			}
			else
			{
				$this->data['identity_label'] = $this->lang->line('forgot_password_email_identity_label');
			}

			// set any errors and display the form
			$this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');
			$this->_render_page('auth' . DIRECTORY_SEPARATOR . 'forgot_password', $this->data);
		}
		else
		{
			$identity_column = $this->config->item('identity', 'ion_auth');
			$identity = $this->ion_auth->where($identity_column, $this->input->post('identity'))->users()->row();

			if (empty($identity))
			{

				if ($this->config->item('identity', 'ion_auth') != 'email')
				{
					$this->ion_auth->set_error('forgot_password_identity_not_found');
				}
				else
				{
					$this->ion_auth->set_error('forgot_password_email_not_found');
				}

				$this->session->set_flashdata('message', $this->ion_auth->errors());
				redirect("auth/forgot_password", 'refresh');
			}

			// run the forgotten password method to email an activation code to the user
			$forgotten = $this->ion_auth->forgotten_password($identity->{$this->config->item('identity', 'ion_auth')});

			if ($forgotten)
			{
				// if there were no errors
				$this->session->set_flashdata('message', $this->ion_auth->messages());
				redirect("auth/login", 'refresh'); //we should display a confirmation page here instead of the login page
			}
			else
			{
				$this->session->set_flashdata('message', $this->ion_auth->errors());
				redirect("auth/forgot_password", 'refresh');
			}
		}
	}

	/**
	 * Reset password - final step for forgotten password
	 *
	 * @param string|null $code The reset code
	 */
	public function reset_password($code = NULL)
	{
		if (!$code)
		{
			show_404();
		}

		$this->data['title'] = $this->lang->line('reset_password_heading');
		
		$user = $this->ion_auth->forgotten_password_check($code);

		if ($user)
		{
			// if the code is valid then display the password reset form

			$this->form_validation->set_rules('new', $this->lang->line('reset_password_validation_new_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[new_confirm]');
			$this->form_validation->set_rules('new_confirm', $this->lang->line('reset_password_validation_new_password_confirm_label'), 'required');

			if ($this->form_validation->run() === FALSE)
			{
				// display the form

				// set the flash data error message if there is one
				$this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

				$this->data['min_password_length'] = $this->config->item('min_password_length', 'ion_auth');
				$this->data['new_password'] = [
					'name' => 'new',
					'id' => 'new',
					'type' => 'password',
					'pattern' => '^.{' . $this->data['min_password_length'] . '}.*$',
				];
				$this->data['new_password_confirm'] = [
					'name' => 'new_confirm',
					'id' => 'new_confirm',
					'type' => 'password',
					'pattern' => '^.{' . $this->data['min_password_length'] . '}.*$',
				];
				$this->data['user_id'] = [
					'name' => 'user_id',
					'id' => 'user_id',
					'type' => 'hidden',
					'value' => $user->id,
				];
				$this->data['csrf'] = $this->_get_csrf_nonce();
				$this->data['code'] = $code;

				// render
				$this->_render_page('auth' . DIRECTORY_SEPARATOR . 'reset_password', $this->data);
			}
			else
			{
				$identity = $user->{$this->config->item('identity', 'ion_auth')};

				// do we have a valid request?
				if ($this->_valid_csrf_nonce() === FALSE || $user->id != $this->input->post('user_id'))
				{

					// something fishy might be up
					$this->ion_auth->clear_forgotten_password_code($identity);

					show_error($this->lang->line('error_csrf'));

				}
				else
				{
					// finally change the password
					$change = $this->ion_auth->reset_password($identity, $this->input->post('new'));

					if ($change)
					{
						// if the password was successfully changed
						$this->session->set_flashdata('message', $this->ion_auth->messages());
						redirect("auth/login", 'refresh');
					}
					else
					{
						$this->session->set_flashdata('message', $this->ion_auth->errors());
						redirect('auth/reset_password/' . $code, 'refresh');
					}
				}
			}
		}
		else
		{
			// if the code is invalid then send them back to the forgot password page
			$this->session->set_flashdata('message', $this->ion_auth->errors());
			redirect("auth/forgot_password", 'refresh');
		}
	}

	/**
	 * Activate the user
	 *
	 * @param int         $id   The user ID
	 * @param string|bool $code The activation code
	 */
	public function activate($id, $code = FALSE)
	{
		$activation = FALSE;

		if ($code !== FALSE)
		{
			$activation = $this->ion_auth->activate($id, $code);
		}
		else if ($this->ion_auth->is_admin())
		{
			$activation = $this->ion_auth->activate($id);
		}

		if ($activation)
		{
			// redirect them to the auth page
			$this->session->set_flashdata('message', $this->ion_auth->messages());
			redirect("auth", 'refresh');
		}
		else
		{
			// redirect them to the forgot password page
			$this->session->set_flashdata('message', $this->ion_auth->errors());
			redirect("auth/forgot_password", 'refresh');
		}
	}

	/**
	 * Deactivate the user
	 *
	 * @param int|string|null $id The user ID
	 */
	public function deactivate($id = NULL)
	{
		if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin())
		{
			// redirect them to the home page because they must be an administrator to view this
			show_error('You must be an administrator to view this page.');
		}

		$id = (int)$id;

		$this->load->library('form_validation');
		$this->form_validation->set_rules('confirm', $this->lang->line('deactivate_validation_confirm_label'), 'required');
		$this->form_validation->set_rules('id', $this->lang->line('deactivate_validation_user_id_label'), 'required|alpha_numeric');

		if ($this->form_validation->run() === FALSE)
		{
			// insert csrf check
			$this->data['csrf'] = $this->_get_csrf_nonce();
			$this->data['user'] = $this->ion_auth->user($id)->row();
			$this->data['identity'] = $this->config->item('identity', 'ion_auth');

			$this->_render_page('auth' . DIRECTORY_SEPARATOR . 'deactivate_user', $this->data);
		}
		else
		{
			// do we really want to deactivate?
			if ($this->input->post('confirm') == 'yes')
			{
				// do we have a valid request?
				if ($this->_valid_csrf_nonce() === FALSE || $id != $this->input->post('id'))
				{
					show_error($this->lang->line('error_csrf'));
				}

				// do we have the right userlevel?
				if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin())
				{
					$this->ion_auth->deactivate($id);
				}
			}

			// redirect them back to the auth page
			redirect('auth', 'refresh');
		}
	}

	/**
	* Redirect a user checking if is admin
	*/
	public function redirectUser(){
		if ($this->ion_auth->is_admin()){
			redirect('auth', 'refresh');
		}
		redirect('/', 'refresh');
	}

	/**
	 * Edit a user
	 *
	 * @param int|string $id
	 */
	public function edit_user($id)
	{
		$this->data['title'] = $this->lang->line('edit_user_heading');

		if (!$this->ion_auth->logged_in() || (!$this->ion_auth->is_admin() && !($this->ion_auth->user()->row()->id == $id)))
		{
			redirect('auth', 'refresh');
		}

		$user = $this->ion_auth->user($id)->row();
		$groups = $this->ion_auth->groups()->result_array();
		$currentGroups = $this->ion_auth->get_users_groups($id)->result_array();
			
		//USAGE NOTE - you can do more complicated queries like this
		//$groups = $this->ion_auth->where(['field' => 'value'])->groups()->result_array();
	

		// validate form input
		$this->form_validation->set_rules('first_name', $this->lang->line('edit_user_validation_fname_label'), 'trim|required');
		$this->form_validation->set_rules('last_name', $this->lang->line('edit_user_validation_lname_label'), 'trim|required');
		$this->form_validation->set_rules('phone', $this->lang->line('edit_user_validation_phone_label'), 'trim');
		$this->form_validation->set_rules('company', $this->lang->line('edit_user_validation_company_label'), 'trim');

		if (isset($_POST) && !empty($_POST))
		{
			// do we have a valid request?
			if ($this->_valid_csrf_nonce() === FALSE || $id != $this->input->post('id'))
			{
				show_error($this->lang->line('error_csrf'));
			}

			// update the password if it was posted
			if ($this->input->post('password'))
			{
				$this->form_validation->set_rules('password', $this->lang->line('edit_user_validation_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[password_confirm]');
				$this->form_validation->set_rules('password_confirm', $this->lang->line('edit_user_validation_password_confirm_label'), 'required');
			}

			if ($this->form_validation->run() === TRUE)
			{
				$data = [
					'first_name' => $this->input->post('first_name'),
					'last_name' => $this->input->post('last_name'),
					'company' => $this->input->post('company'),
					'phone' => $this->input->post('phone'),
				];

				// update the password if it was posted
				if ($this->input->post('password'))
				{
					$data['password'] = $this->input->post('password');
				}

				// Only allow updating groups if user is admin
				if ($this->ion_auth->is_admin())
				{
					// Update the groups user belongs to
					$this->ion_auth->remove_from_group('', $id);
					
					$groupData = $this->input->post('groups');
					if (isset($groupData) && !empty($groupData))
					{
						foreach ($groupData as $grp)
						{
							$this->ion_auth->add_to_group($grp, $id);
						}

					}
				}

				// check to see if we are updating the user
				if ($this->ion_auth->update($user->id, $data))
				{
					// redirect them back to the admin page if admin, or to the base url if non admin
					$this->session->set_flashdata('message', $this->ion_auth->messages());
					$this->redirectUser();

				}
				else
				{
					// redirect them back to the admin page if admin, or to the base url if non admin
					$this->session->set_flashdata('message', $this->ion_auth->errors());
					$this->redirectUser();

				}

			}
		}

		// display the edit user form
		$this->data['csrf'] = $this->_get_csrf_nonce();

		// set the flash data error message if there is one
		$this->data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

		// pass the user to the view
		$this->data['user'] = $user;
		$this->data['groups'] = $groups;
		$this->data['currentGroups'] = $currentGroups;

		$this->data['first_name'] = [
			'name'  => 'first_name',
			'id'    => 'first_name',
			'type'  => 'text',
			'value' => $this->form_validation->set_value('first_name', $user->first_name),
		];
		$this->data['last_name'] = [
			'name'  => 'last_name',
			'id'    => 'last_name',
			'type'  => 'text',
			'value' => $this->form_validation->set_value('last_name', $user->last_name),
		];
		$this->data['company'] = [
			'name'  => 'company',
			'id'    => 'company',
			'type'  => 'text',
			'value' => $this->form_validation->set_value('company', $user->company),
		];
		$this->data['phone'] = [
			'name'  => 'phone',
			'id'    => 'phone',
			'type'  => 'text',
			'value' => $this->form_validation->set_value('phone', $user->phone),
		];
		$this->data['password'] = [
			'name' => 'password',
			'id'   => 'password',
			'type' => 'password'
		];
		$this->data['password_confirm'] = [
			'name' => 'password_confirm',
			'id'   => 'password_confirm',
			'type' => 'password'
		];

		$this->_render_page('auth/edit_user', $this->data);
	}

	/**
	 * Create a new group
	 */
	public function create_group()
	{
		$this->data['title'] = $this->lang->line('create_group_title');

		if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin())
		{
			redirect('auth', 'refresh');
		}

		// validate form input
		$this->form_validation->set_rules('group_name', $this->lang->line('create_group_validation_name_label'), 'trim|required|alpha_dash');

		if ($this->form_validation->run() === TRUE)
		{
			$new_group_id = $this->ion_auth->create_group($this->input->post('group_name'), $this->input->post('description'));
			if ($new_group_id)
			{
				// check to see if we are creating the group
				// redirect them back to the admin page
				$this->session->set_flashdata('message', $this->ion_auth->messages());
				redirect("auth", 'refresh');
			}
			else
            		{
				$this->session->set_flashdata('message', $this->ion_auth->errors());
            		}			
		}
			
		// display the create group form
		// set the flash data error message if there is one
		$this->data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

		$this->data['group_name'] = [
			'name'  => 'group_name',
			'id'    => 'group_name',
			'type'  => 'text',
			'value' => $this->form_validation->set_value('group_name'),
		];
		$this->data['description'] = [
			'name'  => 'description',
			'id'    => 'description',
			'type'  => 'text',
			'value' => $this->form_validation->set_value('description'),
		];

		$this->_render_page('auth/create_group', $this->data);
		
	}

	/**
	 * Edit a group
	 *
	 * @param int|string $id
	 */
	public function edit_group($id)
	{
		// bail if no group id given
		if (!$id || empty($id))
		{
			redirect('auth', 'refresh');
		}

		$this->data['title'] = $this->lang->line('edit_group_title');

		if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin())
		{
			redirect('auth', 'refresh');
		}

		$group = $this->ion_auth->group($id)->row();

		// validate form input
		$this->form_validation->set_rules('group_name', $this->lang->line('edit_group_validation_name_label'), 'trim|required|alpha_dash');

		if (isset($_POST) && !empty($_POST))
		{
			if ($this->form_validation->run() === TRUE)
			{
				$group_update = $this->ion_auth->update_group($id, $_POST['group_name'], array(
					'description' => $_POST['group_description']
				));

				if ($group_update)
				{
					$this->session->set_flashdata('message', $this->lang->line('edit_group_saved'));
					redirect("auth", 'refresh');
				}
				else
				{
					$this->session->set_flashdata('message', $this->ion_auth->errors());
				}				
			}
		}

		// set the flash data error message if there is one
		$this->data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

		// pass the user to the view
		$this->data['group'] = $group;

		$this->data['group_name'] = [
			'name'    => 'group_name',
			'id'      => 'group_name',
			'type'    => 'text',
			'value'   => $this->form_validation->set_value('group_name', $group->name),
		];
		if ($this->config->item('admin_group', 'ion_auth') === $group->name) {
			$this->data['group_name']['readonly'] = 'readonly';
		}
		
		$this->data['group_description'] = [
			'name'  => 'group_description',
			'id'    => 'group_description',
			'type'  => 'text',
			'value' => $this->form_validation->set_value('group_description', $group->description),
		];

		$this->_render_page('auth' . DIRECTORY_SEPARATOR . 'edit_group', $this->data);
	}

	/**
	 * @return array A CSRF key-value pair
	 */
	public function _get_csrf_nonce()
	{
		$this->load->helper('string');
		$key = random_string('alnum', 8);
		$value = random_string('alnum', 20);
		$this->session->set_flashdata('csrfkey', $key);
		$this->session->set_flashdata('csrfvalue', $value);

		return [$key => $value];
	}

	/**
	 * @return bool Whether the posted CSRF token matches
	 */
	public function _valid_csrf_nonce(){
		$csrfkey = $this->input->post($this->session->flashdata('csrfkey'));
		if ($csrfkey && $csrfkey === $this->session->flashdata('csrfvalue'))
		{
			return TRUE;
		}
			return FALSE;
	}

	/**
	 * @param string     $view
	 * @param array|null $data
	 * @param bool       $returnhtml
	 *
	 * @return mixed
	 */
	public function _render_page($view, $data = NULL, $returnhtml = FALSE)//I think this makes more sense
	{
		$viewdata = (empty($data)) ? $this->data : $data;

		$view_html = $this->load->view($view, $viewdata, $returnhtml);

		// This will return html on 3rd argument being true
		if ($returnhtml)
		{
			return $view_html;
		}
	}

}
