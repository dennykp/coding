<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mymodel extends CI_Model {

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
	public function getPagination($where = ""){
		$data = $this->db->query('select * from pagination '.$where);
		return $data->result_array();
		}

	public function insertPagination($tabelName, $dataPagination){
		$dataIn = $this->db->insert($tabelName, $dataPagination);
		return $dataIn;
		}

	public function updatePagination($tabelName, $dataPagination, $where){
		$dataIn = $this->db->update($tabelName, $dataPagination, $where);
		return $dataIn;
		}

	public function deletePagination($tabelName, $where){
		$dataIn = $this->db->delete($tabelName, $where);
		return $dataIn;
		}


	}

