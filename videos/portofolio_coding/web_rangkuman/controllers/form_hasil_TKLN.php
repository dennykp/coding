<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Form_hasil_TKLN extends CI_Controller {

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
		$comp = array(
				"menuprofile" => $this->load->view("template/menuprofile",array(),true),
				"sidebar" => $this->load->view("template/sidebar",array(),true),
				"menufooter" => $this->load->view("template/menufooter",array(),true),
				"topnav" => $this->load->view("template/topnav",array(),true),
				"pagecontent" => $this->load->view("template/pagecontent",array(),true),
				"footer" => $this->load->view("template/footer",array(),true)
			);
		$this->load->view("template/index",$comp);
	}
	public function hasil_pemeriksaan_fisik_edit(){
		$comp = array(
				"pemeriksaanfisik" => $this->load->view("form_hasil/form_hasil_pfisiknjiwa_TKLN",array(),true),
				"pemeriksaankejiwaan" => $this->load->view("form_hasil/form_hasil_pfisiknjiwa_TKLN",array(),true)
			);
		$this->load->view("form_hasil/Form_hasil_TKLN",$comp);
	}
	public function form_daftar_TKDN(){
		$comp = array(
				"menuprofile" => $this->load->view("template/menuprofile",array(),true),
				"sidebar" => $this->load->view("template/sidebar",array(),true),
				"menufooter" => $this->load->view("template/menufooter",array(),true),
				"topnav" => $this->load->view("template/topnav",array(),true),
				"pagecontent" => $this->load->view("daftar/form_daftar_TKDN",array(),true),
				"footer" => $this->load->view("template/footer",array(),true)
			);
		$this->load->view("template/index",$comp);
	}

}
