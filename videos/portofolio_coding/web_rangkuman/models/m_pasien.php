<?php 

class M_pasien extends CI_Model{
	function __construct()
  {
   parent::__construct();
  }
function code_otomatis(){
            $this->db->select('Right(pemeriksaan_fisik.no_register,3) as kode ',false);
            $this->db->order_by('id_pasien', 'desc');
            $this->db->limit(1);
            $query = $this->db->get('pemeriksaan_fisik');
            if($query->num_rows()<>0){
                $data = $query->row();
                $kode = intval($data->kode)+1;
            }else{
                $kode = 1;

            }
            $kodemax = str_pad($kode,3,"0",STR_PAD_LEFT);
            $kodejadi  = "REG-".$kodemax;
            return $kodejadi;

        }



 function m_tampil_pasien(){
  $this->db->select('*');
	  $this->db->from('pemeriksaan_fisik');
	  $this->db->join('tb_pemeriksaan_fisik_kejiwaan','pemeriksaan_fisik.id_pasien=tb_pemeriksaan_fisik_kejiwaan.id_pasien');
	   $this->db->join('tb_pemeriksaan_laboratorium','pemeriksaan_fisik.id_pasien=tb_pemeriksaan_laboratorium.id_pasien');
	   $this->db->join('pemeriksaan_radiologi','pemeriksaan_fisik.id_pasien=pemeriksaan_radiologi.id_pasien');
	   $this->db->join('image','pemeriksaan_fisik.id_pasien=image.id_pasien');
$query = $this->db->get();
return $query;
} 
  function m_tampil_lab(){
	  
$query = $this->db->query('select * from tb_pemeriksaan_laboratorium');
return $query;
} 
 function m_tampil_user(){
	  
$query = $this->db->query('select * from user');
return $query;
} 
public function update($id_pasien, $data)
{
$this->db->update('pemeriksaan_fisik',$data, array('id_pasien' => $id_pasien));
}


public function edit($id_pasien)
{
	$query = $this->db->get_where('pemeriksaan_fisik', array('id_pasien' => $id_pasien
	));
	return $query;
}
 function m_tampil_contoh(){
	  
$query = $this->db->get('contoh');
return $query;
} 

public function m_hapus_pasien($id_pasien){
	$this->db->where('id_pasien',$id_pasien);
	$query =$this->db->delete('pemeriksaan_fisik');
}
public function m_insert($data){
	  $this->db->insert('pemeriksaan_fisik',$data);
        $last_insret_id=  $this->db->query("select id_pasien from pemeriksaan_fisik order by id_pasien desc")->row_array();
        $this->db->query("insert tb_pemeriksaan_fisik_kejiwaan set id_pasien='".$last_insret_id['id_pasien']."'");
         $this->db->query("insert tb_pemeriksaan_laboratorium set id_pasien='".$last_insret_id['id_pasien']."'");
			$this->db->query("insert pemeriksaan_radiologi set id_pasien='".$last_insret_id['id_pasien']."'");
			$this->db->query("insert image set id_pasien='".$last_insret_id['id_pasien']."'");
		}
		
public function m_insert_user($data){
			$this->db->insert('user',$data);
		}
		
public function m_insert_pemeriksaan($data){
			$this->db->insert('tb_pemeriksaan_fisik_kejiwaan',$data);
		}		
	public function m_insert_lab($data){
			$this->db->insert('tb_pemeriksaan_laboratorium',$data);
		}	
function hitung_pasien() {	

$query = $this->db->query('select * from pemeriksaan_fisik' );
$total = $query->num_rows();
return $total;
}
function hitung_tkdn() {	

$query = $this->db->query('select * from pemeriksaan_fisik where pemeriksaan="Tenaga Kerja Dalam Negeri"');
$total = $query->num_rows();
return $total;
}
function hitung_tkln() {	

$query = $this->db->query('select * from pemeriksaan_fisik where pemeriksaan="Tenaga Kerja Luar Negeri"');
$total = $query->num_rows();
return $total;
}
public function edit_pasien($id_pasien)
{
    $this->db->select('*');
    $this->db->from('pemeriksaan_fisik');
    $this->db->join('tb_pemeriksaan_laboratorium', 'pemeriksaan_fisik.id_pasien=tb_pemeriksaan_laboratorium.id_pasien', 'left');
	 $this->db->join('tb_pemeriksaan_fisik_kejiwaan', 'pemeriksaan_fisik.id_pasien=tb_pemeriksaan_fisik_kejiwaan.id_pasien', 'left');
	  $this->db->join('pemeriksaan_radiologi', 'pemeriksaan_fisik.id_pasien=pemeriksaan_radiologi.id_pasien', 'left');
    $this->db->where('pemeriksaan_fisik.id_pasien', $id_pasien);  
    $query = $this->db->get();    
   
        return $query;
}
function hitung_pasien_pending() {	

$query = $this->db->query('select * from tb_pemeriksaan_fisik_kejiwaan where status_pasien="0"');
$total = $query->num_rows();
return $total;
}
function hitung_pasien_done() {	

$query = $this->db->query('select * from tb_pemeriksaan_fisik_kejiwaan where status_pasien="1"');
$total = $query->num_rows();
return $total;
}
public function edit_lab($id_pasien){

	$query = $this->db->get_where('tb_pemeriksaan_laboratorium', array('id_pasien' => $id_pasien));
	
	return $query;
}
		public function m_update_pasien($id_pasien, $data)
	{
		$this->db->where('id_pasien', $id_pasien);
		$query = $this->db->update('pemeriksaan_fisik', $data);
	}
		public function m_update_lab($id_pasien, $data)
	{
		$this->db->where('id_pasien', $id_pasien);
		$query = $this->db->update('tb_pemeriksaan_laboratorium', $data);
	}
		public function m_update_pemeriksaan_kejiwaan($id_pasien, $data)
	{
		$this->db->where('id_pasien', $id_pasien);
		$query = $this->db->update('tb_pemeriksaan_fisik_kejiwaan', $data);
	}
			public function m_update_pemeriksaan_radiologi($id_pasien, $data)
	{
		$this->db->where('id_pasien', $id_pasien);
		$query = $this->db->update('pemeriksaan_radiologi', $data);
	}
			public function m_update_profil($id, $data)
	{
		$this->db->where('id', $id);
		$query = $this->db->update('user', $data);
	}
	
	
	
}