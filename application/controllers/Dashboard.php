<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property Ion_auth|Ion_auth_model    $ion_auth           The ION Auth Spark Tool Set
 * @property CI_DB_query_builder        $db                 The Database Query Helper
 * @property Data_model                 $data_model         The Data Model
 * @property CI_Session                 $session            The Session Data Helper
 * @property CI_Input                   $input              The Data Input Helper
 * @property CI_Lang                    $lang               The Language Handler
 */
class Dashboard extends CI_Controller {
    private const ROLES = ['Dev' => 'developer', 'Admin' => 'administrator', 'Vol' => 'volunteer', 'Def' => 'Default'];
    private array $data;

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('data_model');
        $this->load->library(['ion_auth']);
        $this->load->helper(['url', 'language']);

        $this->lang->load('auth');
        $this->data = [];
    }

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
            if($id) {
                $this->data['id'] = $id;
                $this->loadView('volunteer');
            } else {
                $this->data['centers'] = $this->data_model->LoadCenters();
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
            if($id) {
                $this->data['user_id'] = $id;
                $this->loadView('volunteer-edit');
            } else {
                $this->data['centers'] = $this->data_model->LoadCenters();
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

    /**
     * Loads Page with Default Header
     * @param string $page      Page Name (dashboard/page-$page)
     * @param string $styles    Styles (baseurl($styles)
     * @param string $scripts   Scripts (baseurl($scripts))
     */
    private function loadView (string $page = '', string $styles = '', string $scripts = '') {
        $this->data = empty($this->data) ? [] : $this->data;
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
}
