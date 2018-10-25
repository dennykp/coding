<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {
function __construct(){
		parent::__construct();	
		$this->load->helper(array('form','url', 'text_helper','date'));
		$this->load->database();	
		$this->load->model('m_user');
		$this->load->model('m_pasien');
		$this->load->library(array('Pagination','user_agent','session','form_validation','upload'));
		$this->load->helper(array('form','url', 'text_helper','date'));
	}
	


	public function halaman_utama(){
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
	 function logout(){
        $this->session->sess_destroy();
        $url=base_url('index.php/login');
        redirect($url);
    }
function index(){
	
		$this->load->model('m_pasien');
		$data['num_results'] = $this->m_pasien->hitung_pasien();
	$this->load->view("v_indexlogin",$data);
}
function data_user(){
   
	$data['query'] = $this->m_pasien->m_tampil_user();
	$this->load->view('v_data_user',$data);
}
	function auth(){
        $username=htmlspecialchars($this->input->post('username',TRUE),ENT_QUOTES);
        $password=htmlspecialchars($this->input->post('password',TRUE),ENT_QUOTES);

        $cek_user=$this->m_user->auth_login($username,$password);

        if($cek_user->num_rows() > 0){ 
						$data=$cek_user->row_array();
        		$this->session->set_userdata('masuk',TRUE);
		         if($data['status']=='admin'){ //
		            $this->session->set_userdata('akses','admin');
		            $this->session->set_userdata('ses_id',$data['id']);
		            $this->session->set_userdata('ses_nama',$data['nama']);
					$this->session->set_userdata('ses_username',$data['username']);
					$this->session->set_userdata('ses_password',$data['password']);
					$this->session->set_userdata('ses_alamat',$data['alamat']);
					$this->session->set_userdata('ses_status',$data['status']);
					
		            redirect('index.php/home');
$this->session->set_flashdata('msg_login', 
                '<div class="alert alert-info"><h4>Login Berhasil !!</h4></div>'); 
		         }else if($data['status']=='radiologi'){
				  
		            $this->session->set_userdata('akses','radiologi');
					$this->session->set_userdata('ses_id',$data['id']);
		            $this->session->set_userdata('ses_nama',$data['nama']);
					$this->session->set_userdata('ses_username',$data['username']);
					$this->session->set_userdata('ses_password',$data['password']);
					$this->session->set_userdata('ses_alamat',$data['alamat']);
					$this->session->set_userdata('ses_status',$data['status']);
		           redirect('index.php/home');
		         }
		
         
					else if($data['status']=='lab'){
				  
		            $this->session->set_userdata('akses','lab');
					$this->session->set_userdata('ses_id',$data['id']);
		            $this->session->set_userdata('ses_nama',$data['nama']);
					$this->session->set_userdata('ses_username',$data['username']);
					$this->session->set_userdata('ses_password',$data['password']);
					$this->session->set_userdata('ses_alamat',$data['alamat']);
					$this->session->set_userdata('ses_status',$data['status']);
					
					
		             redirect('index.php/home');
		         }
				 else if($data['status']=='poliklinik'){
				  
		            $this->session->set_userdata('akses','poliklinik');
					$this->session->set_userdata('ses_id',$data['id']);
		            $this->session->set_userdata('ses_nama',$data['nama']);
					$this->session->set_userdata('ses_status',$data['status']);
				$this->session->set_userdata('ses_username',$data['username']);
					$this->session->set_userdata('ses_password',$data['password']);
					$this->session->set_userdata('ses_alamat',$data['alamat']);
		            redirect('index.php/home');
		         }
				 else if($data['status']=='frontoffice'){
				  
		            $this->session->set_userdata('akses','frontoffice');
					$this->session->set_userdata('ses_id',$data['id']);
		            $this->session->set_userdata('ses_nama',$data['nama']);
					$this->session->set_userdata('ses_alamat',$data['alamat']);
					$this->session->set_userdata('ses_username',$data['username']);
					$this->session->set_userdata('ses_password',$data['password']);
					$this->session->set_userdata('ses_status',$data['status']);
				
					
					
		            redirect('index.php/home');
					
		         }
				 
		}else{ 
							$url=base_url('index.php/login/index');
							$this->session->set_flashdata('msg', 
                '<div class="alert alert-warning alert-warning">
 <h6>
  Username/Password Salah !</h6>
</div>');   
							redirect($url);
					}
        

    }

}
