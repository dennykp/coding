<?php 

class M_user extends CI_Model{
	function __construct()
  {
   parent::__construct();
  }

	function auth_login($username,$password){
		$query=$this->db->query("SELECT * FROM user WHERE username='$username' AND password=MD5('$password') LIMIT 1");
		return $query;
	}




}