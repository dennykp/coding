<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Data_pasien_harian extends CI_Controller {

function __construct(){
		parent::__construct();	
		$this->load->helper(array('form','url', 'text_helper','date'));
		$this->load->database();	
		$this->load->model('m_user');
		$this->load->model('m_pasien');
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
	 function pasien_harian(){
		 $data['num_results'] = $this->m_pasien->hitung_pasien_pending();
	 $data['num_results_done'] = $this->m_pasien->hitung_pasien_done();
		 $data['query'] = $this->m_pasien->m_tampil_pasien();
		$this->load->view('v_coba',$data);
			
			
	 }
	 	 function pasien_harian_lab(){
		 $data['query'] = $this->m_pasien->m_tampil_lab();
		$this->load->view('v_tampil_lab',$data);
			
			
	 }
	public function index(){
		
		$comp = array(
				"menuprofile" => $this->load->view("template/menuprofile",array(),true),
				"sidebar" => $this->load->view("template/sidebar",array(),true),
				"menufooter" => $this->load->view("template/menufooter",array(),true),
				"topnav" => $this->load->view("template/topnav",array(),true),
				"pagecontent" => redirect ('index.php/data_pasien_harian/pasien_harian'),
				"footer" => $this->load->view("template/footer",array(),true)
			);
		$this->load->view("template/index",$comp);
		
		
	}

	public function formhasil_TKLN(){
		$comp = array(
				"menuprofile" => $this->load->view("template/menuprofile",array(),true),
				"sidebar" => $this->load->view("template/sidebar",array(),true),
				"menufooter" => $this->load->view("template/menufooter",array(),true),
				"topnav" => $this->load->view("template/topnav",array(),true),
				"pagecontent" => $this->load->view("form_hasil/form_hasil_TKLN",array(),true),
				"footer" => $this->load->view("template/footer",array(),true)
			);
		$this->load->view("template/index",$comp);
	}

	public function formhasil_TKDN(){
		$comp = array(
				"menuprofile" => $this->load->view("template/menuprofile",array(),true),
				"sidebar" => $this->load->view("template/sidebar",array(),true),
				"menufooter" => $this->load->view("template/menufooter",array(),true),
				"topnav" => $this->load->view("template/topnav",array(),true),
				"pagecontent" => $this->load->view("form_hasil/form_hasil_TKLN",array(),true),
				"footer" => $this->load->view("template/footer",array(),true)
			);
		$this->load->view("template/index",$comp);
	}
}
