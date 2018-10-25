<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class C_info_pasien extends CI_Controller {
function __construct(){
		parent::__construct();	
		$this->load->helper(array('form','url', 'text_helper','date'));
		$this->load->database();	
		$this->load->model('m_user');
		$this->load->model('m_pasien');
		$this->load->library('session');
		$this->load->library(array('Pagination','user_agent','session','form_validation','upload'));
		$this->load->helper(array('form','url', 'text_helper','date'));
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
	public function index(){
			$data['query'] = $this->m_pasien->m_tampil_pasien();
	
	$data['num_results'] = $this->m_pasien->hitung_tkdn();
	$data['num_results_tkln'] = $this->m_pasien->hitung_tkln();
		$this->load->view("v_info_pasien",$data);
	}

}
