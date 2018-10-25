<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {
function __construct(){
		parent::__construct();	
		$this->load->helper(array('form','url', 'text_helper','date'));
		$this->load->database();	
		$this->load->model('Uploadimage_model', 'uploadimage');
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
	public function tampil_daftar_pasien(){
		
	$data['query'] = $this->m_pasien->m_tampil_pasien();
	$this->load->view('daftar_pasien_harian',$data);

	
}

public function edit()
{
	$id_pasien = $this->uri->segment(3);
	$data['query'] = $this->m_pasien->edit($id_pasien);
	$this->load->view('v_edit_pasien', $data);
}
public function update()
{
	$id_pasien = $this->input->post('id_pasien');
	$data = array('nama_lengkap' => $this->input->post('nama_lengkap'),
	'status'  => $this->input->post('status')
	);
	
	$proses = $this->m_pasien->update($id_pasien, $data);
	if (!$proses) {
		header('Location: tampil');
	} else {
		echo "Data Gagal Diupdate";
		echo "<br>";
		echo "<a href='".base_url('index.php/crud/tampil/')."'>Tampil data</a>";
	}
}


public function simpan_pasien(){
		if($_POST){
		
		$nama_lengkap = $this->input->post('nama_lengkap');
		$tgl_lahir = $this->input->post('tgl_lahir');
		$jenis_kelamin = $this->input->post('jenis_kelamin');
		$status = $this->input->post('status');
		$alamat = $this->input->post('alamat');
		$no_register = $this->input->post('no_register');
		$tgl_masuk = $this->input->post('tgl_masuk');
		$pemeriksaan = $this->input->post('pemeriksaan');
		$pengirim = $this->input->post('pengirim');
		$negara = $this->input->post('negara');
		$no_passport = $this->input->post('no_passport');
		$checkboxes = $this->input->post('check_list');
		$cek = implode(",",$checkboxes);
		$this->m_pasien->m_simpan_pasien(array('nama_lengkap' => $nama_lengkap
		,'tgl_lahir' => $tgl_lahir
		,'jenis_kelamin' => $jenis_kelamin
		,'status' => $status
		,'alamat' => $alamat
		,'no_register' => $no_register
		,'tgl_masuk' => $tgl_masuk
		,'pemeriksaan' => $pemeriksaan
		,'pengirim' => $pengirim
		,'negara' => $negara
		,'no_passport' => $no_passport
		,'pemeriksaan_fisik' => $cek
		
		));
			
		$this->load->view('tes');
	
	}
}
public function insert(){
	if($_POST){
		$checkboxes = $this->input->post('check_list');
		$checkboxes_lab = $this->input->post('check_list_lab');
		$checkboxes_radio = $this->input->post('check_list_radio');
		$nama_lengkap = $this->input->post('nama_lengkap');
		$tgl_lahir = $this->input->post('tgl_lahir');
		$jenis_kelamin = $this->input->post('jenis_kelamin');
		$status = $this->input->post('status');
		$alamat = $this->input->post('alamat');
		$no_register = $this->input->post('no_register');
		$tgl_masuk = $this->input->post('tgl_masuk');
		$pemeriksaan = $this->input->post('pemeriksaan');
		$pengirim = $this->input->post('pengirim');
		$negara = $this->input->post('negara');
		$no_passport = $this->input->post('no_passport');
		$petugas = $this->input->post('petugas');
		$tgl_input = $this->input->post('tgl_input');
		$cek = implode(",",$checkboxes);
		$cek_lab = implode(",",$checkboxes_lab);
		$cek_radio = implode(",",$checkboxes_radio);
		$data = $this->m_pasien->m_insert(array('pemeriksaan_fisik_kejiwaan' => $cek
		,'pemeriksaan_laboratorium' => $cek_lab
		,'pemeriksaan_radiologi' => $cek_radio
		,'nama_lengkap' => $nama_lengkap
		,'tgl_lahir' => $tgl_lahir
		,'jenis_kelamin' => $jenis_kelamin
		,'status' => $status
		,'alamat' => $alamat
		,'no_register' => $no_register
		,'tgl_masuk' => $tgl_masuk
		,'pemeriksaan' => $pemeriksaan
		,'pengirim' => $pengirim
		,'negara' => $negara
		,'no_passport' => $no_passport
		,'petugas' => $petugas
		,'tgl_input' => $tgl_input
		));
		   if($data >= 1) {
         echo $this->session->set_flashdata('message', 'anda gagal menginput data...!!!');
          	$data['kodeunik'] = $this->m_pasien->code_otomatis();
		  $this->load->view('v_insert_pasien', $data);
		 
        } else {
            echo $this->session->set_flashdata('message', '<div class="alert alert-info"><h4>Data Pasien berhasil diinputkan !!</h4></div>');
          $data['kodeunik'] = $this->m_pasien->code_otomatis();
		  $this->load->view('v_insert_pasien', $data);
        }
	
	
		
	}
	
}

public function insert_user(){
	if($_POST){
		$nama = $this->input->post('nama');
		$alamat = $this->input->post('alamat');
		$username = $this->input->post('username');
		$password = $this->input->post('password');
		$status = $this->input->post('status');
		
		$data = $this->m_pasien->m_insert_user(array('nama' => $nama
		,'alamat' => $alamat
		,'username' => $username
		,'password' => $password
		,'status' => $status
		));
		   if($data >= 1) {
         echo $this->session->set_flashdata('message', 'anda gagal menginput data...!!!');
          redirect("index.php/login/data_user");
		 
        } else {
            echo $this->session->set_flashdata('message', '<div class="alert alert-info"><h4>Data Pasien berhasil diinputkan !!</h4></div>');
       redirect("index.php/login/data_user");
        }
	
	
		
	}
	
}

public function insert_laboratorium(){
	if($_POST){
		$id_pasien = $this->input->post('id_pasien');
		$golongan_darah = $this->input->post('golongan_darah');
		$kadar_hb = $this->input->post('kadar_hb');
		$hitung_trombosit = $this->input->post('hitung_trombosit');
		$hitung_eritrosit = $this->input->post('hitung_eritrosit');
		$hitung_leukosit = $this->input->post('hitung_leukosit');
		$warna_bau_kejernihan = $this->input->post('warna_bau_kejernihan');
		$bilirubin = $this->input->post('bilirubin');
		$benda_keton = $this->input->post('benda_keton');
		$sedimen = $this->input->post('sedimen');
		$glukosa = $this->input->post('glukosa');
		$berat_jenis = $this->input->post('berat_jenis');
		$darah_samar = $this->input->post('darah_samar');
		$protein = $this->input->post('protein');
		$urobilinogen = $this->input->post('urobilinogen');
		$ph = $this->input->post('ph');
		$sgot = $this->input->post('sgot');
		$sgpt = $this->input->post('sgpt');
		$hbsag = $this->input->post('hbsag');
		$glukosa_sewaktu = $this->input->post('glukosa_sewaktu');
		$kreatinin= $this->input->post('kreatinin');
		$ureum = $this->input->post('ureum');
		$hiv = $this->input->post('hiv');
		$tpha = $this->input->post('tpha');
		$vdrl = $this->input->post('vdrl');
		$amphetamine= $this->input->post('amphetamine');
		$methamphetamine = $this->input->post('methamphetamine');
		$thc = $this->input->post('thc');
		$morphine= $this->input->post('morphine');
		$benzodizapine = $this->input->post('benzodizapine');
		$cocain = $this->input->post('cocain');
		$tes_kehamilan = $this->input->post('tes_kehamilan');
		$feses = $this->input->post('feses');
		$catatan_keterangan_lab= $this->input->post('catatan_keterangan_lab');
		
		$data = $this->m_pasien->m_insert_lab(array('golongan_darah' => $golongan_darah
		,'id_pasien' => $id_pasien
		,'kadar_hb' => $kadar_hb
		,'hitung_trombosit' => $hitung_trombosit
		,'hitung_eritrosit' => $hitung_eritrosit
		,'hitung_leukosit' => $hitung_leukosit
		,'warna_bau_kejernihan' => $warna_bau_kejernihan
		,'bilirubin' => $bilirubin
		,'benda_keton' => $benda_keton
		,'sedimen' => $sedimen
		,'glukosa' => $glukosa
		,'berat_jenis' => $berat_jenis
		,'darah_samar' => $darah_samar
		,'protein' => $protein
		,'urobilinogen' => $urobilinogen
		,'ph' => $ph
		,'sgot' => $sgot
		,'sgpt' => $sgpt
		,'hbsag' => $hbsag
		,'glukosa_sewaktu' => $glukosa_sewaktu
		,'kreatinin' => $kreatinin
		,'ureum' => $ureum
		,'hiv' => $hiv
		,'tpha' => $tpha
		,'vdrl' => $vdrl
		,'amphetamine' => $amphetamine
		,'methamphetamine' => $methamphetamine
		,'thc' => $thc
		,'morphine' => $morphine
		,'benzodizapine' => $benzodizapine
		,'cocain' => $cocain
		,'tes_kehamilan' => $tes_kehamilan
		,'feses' => $feses
		,'catatan_keterangan_lab' => $catatan_keterangan_lab
		
		));
		   if($data >= 1) {
         echo $this->session->set_flashdata('message', 'anda gagal menginput data...!!!');
		  redirect("index.php/home/edit_proses_pasien");
		 
        } else {
            echo $this->session->set_flashdata('message', '<div class="alert alert-info"><h4>Data Pasien berhasil diinputkan !!</h4></div>');
     redirect("index.php/home/edit_proses_pasien");
        }
	
	
		
	}

		
	
}


public function insert_pemeriksaan(){
	if($_POST){
		$id_pasien = $this->input->post('id_pasien');
		$tinggi_badan = $this->input->post('tinggi_badan');
		$berat_badan = $this->input->post('berat_badan');
		$suhu = $this->input->post('suhu');
		$kepala = $this->input->post('kepala');
		$mata = $this->input->post('mata');
		$telinga = $this->input->post('telinga');
		$hidung = $this->input->post('hidung');
		$gigi = $this->input->post('gigi');
		$tenggorokan = $this->input->post('tenggorokan');
		$leher = $this->input->post('leher');
		$dada = $this->input->post('dada');
		$pernafasan = $this->input->post('pernafasan');
		$tekanan_darah = $this->input->post('tekanan_darah');
		$nadi = $this->input->post('nadi');
		$paru = $this->input->post('paru');
		$jantung = $this->input->post('jantung');
		$abdomen = $this->input->post('abdomen');
		$anus = $this->input->post('anus');
		$genitalia_externa = $this->input->post('genitalia_externa');
		$ekstriminas_atas= $this->input->post('ekstriminas_atas');
		$ekstrimitas_bawah = $this->input->post('ekstrimitas_bawah');
		$kelenjar_getah_bening = $this->input->post('kelenjar_getah_bening');
		$appearance_and_speech = $this->input->post('appearance_and_speech');
		$mood_afek = $this->input->post('mood_afek');
		$thought_and_cognitive= $this->input->post('thought_and_cognitive');
		$perception_disorder = $this->input->post('perception_disorder');
		$impuls_control = $this->input->post('impuls_control');
		$reality_assessment= $this->input->post('reality_assessment');
		$hipertensi = $this->input->post('hipertensi');
		$batuk_kronis = $this->input->post('batuk_kronis');
		$hipertiroid = $this->input->post('hipertiroid');
		$radang_usus_buntu = $this->input->post('radang_usus_buntu');
		$piouri= $this->input->post('piouri');
		$hematochezia = $this->input->post('hematochezia');
		$malaria= $this->input->post('malaria');
		$gangguan_kejiwaan = $this->input->post('gangguan_kejiwaan');
		$stroke = $this->input->post('stroke');
		$hemoptoe= $this->input->post('hemoptoe');
		$diabetes_melitus = $this->input->post('diabetes_melitus');
		$hematuria = $this->input->post('hematuria');
		$eksim = $this->input->post('eksim');
		$ambeien = $this->input->post('ambeien');
		$epilepsi= $this->input->post('epilepsi');
		$tumor= $this->input->post('tumor');
		$penyakit_jantung = $this->input->post('penyakit_jantung');
		$asma = $this->input->post('asma');
		$radang_perut= $this->input->post('radang_perut');
		$urolitiasis = $this->input->post('urolitiasis');
		$alergi = $this->input->post('alergi');
		$kusta = $this->input->post('kusta');
		$malignansi = $this->input->post('malignansi');
		
		$data = $this->m_pasien->m_insert_pemeriksaan(array('tinggi_badan' => $tinggi_badan
		,'berat_badan' => $berat_badan
		,'id_pasien' => $id_pasien
		,'suhu' => $suhu
		,'kepala' => $kepala
		,'mata' => $mata
		,'telinga' => $telinga
		,'hidung' => $hidung
		,'gigi' => $gigi
		,'tenggorokan' => $tenggorokan
		,'leher' => $leher
		,'dada' => $dada
		,'pernafasan' => $pernafasan
		,'tekanan_darah' => $tekanan_darah
		,'nadi' => $nadi
		,'paru' => $paru
		,'jantung' => $jantung
		,'abdomen' => $abdomen
		,'anus' => $anus
		,'genitalia_externa' => $genitalia_externa
		,'ekstriminas_atas' => $ekstriminas_atas
		,'ekstrimitas_bawah' => $ekstrimitas_bawah
		,'kelenjar_getah_bening' => $kelenjar_getah_bening
		,'appearance_and_speech' => $appearance_and_speech
		,'mood_afek' => $mood_afek
		,'thought_and_cognitive' => $thought_and_cognitive
		,'perception_disorder' => $perception_disorder
		,'impuls_control' => $impuls_control
		,'reality_assessment' => $reality_assessment
		,'hipertensi' => $hipertensi
		,'batuk_kronis' => $batuk_kronis
		,'hipertiroid' => $hipertiroid
		,'radang_usus_buntu' => $radang_usus_buntu
		,'piouri' => $piouri
		,'hematochezia' => $hematochezia
		,'malaria' => $malaria
		,'gangguan_kejiwaan' => $gangguan_kejiwaan
		,'stroke' => $stroke
		,'hemoptoe' => $hemoptoe
		,'diabetes_melitus' => $diabetes_melitus
		,'hematuria' => $hematuria
		,'eksim' => $eksim
		,'ambeien' => $ambeien
		,'epilepsi' => $epilepsi
		,'tumor' => $tumor
		,'penyakit_jantung' => $penyakit_jantung
		,'asma' => $asma
		,'radang_perut' => $radang_perut
		,'urolitiasis' => $urolitiasis
		,'alergi' => $alergi
		,'kusta' => $kusta
		,'malignansi' => $malignansi
		
		));
		   if($data >= 1) {
         echo $this->session->set_flashdata('message', 'anda gagal menginput data...!!!');
		  redirect("index.php/home/edit_proses_pasien");
		 
        } else {
            echo $this->session->set_flashdata('message', '<div class="alert alert-info"><h4>Data Pasien berhasil diinputkan !!</h4></div>');
     redirect("index.php/home/edit_proses_pasien");
        }
	
	
		
	}

		
	
}


public function edit_proses_pasien()
{
	
	$id_pasien= $this->uri->segment(3);
	
	$data['query'] = $this->m_pasien->edit_pasien($id_pasien);
	$this->load->view('v_edit_pasien', $data);
}
public function edit_proses_lab()
{
	
	$id_pasien= $this->uri->segment(3);
	
	$data['query'] = $this->m_pasien->edit_lab($id_pasien);
	$this->load->view('v_update_lab', $data);
}
public function update_lab(){
  
	$id_pasien = $this->input->post('id_pasien');
	$data = array ('golongan_darah' => $this->input->post('golongan_darah')
	,'kadar_hb' => $this->input->post('kadar_hb')
	,'hitung_trombosit' => $this->input->post('hitung_trombosit')
	,'hitung_eritrosit' => $this->input->post('hitung_eritrosit')
	,'hitung_leukosit' => $this->input->post('hitung_leukosit')
	,'warna_bau_kejernihan' => $this->input->post('warna_bau_kejernihan')
	,'bilirubin' => $this->input->post('bilirubin')
	,'benda_keton' => $this->input->post('benda_keton')
	,'sedimen' => $this->input->post('sedimen')
	,'glukosa' => $this->input->post('glukosa')
	,'berat_jenis' => $this->input->post('berat_jenis')
	,'darah_samar' => $this->input->post('darah_samar')
	,'protein' => $this->input->post('protein')
	,'urobilinogen' => $this->input->post('urobilinogen')
	,'ph' => $this->input->post('ph')
	,'sgot' => $this->input->post('sgot')
	,'sgpt' => $this->input->post('sgpt')
	,'hbsag' => $this->input->post('hbsag')
	,'glukosa_sewaktu' => $this->input->post('glukosa_sewaktu')
	,'kreatinin' => $this->input->post('kreatinin')
	,'ureum' => $this->input->post('ureum')
	,'hiv' => $this->input->post('hiv')
	,'tpha' => $this->input->post('tpha')
	,'vdrl' => $this->input->post('vdrl')
	,'amphetamine' => $this->input->post('amphetamine')
	,'methamphetamine' => $this->input->post('methamphetamine')
	,'thc' => $this->input->post('thc')
	,'morphine' => $this->input->post('morphine')
	,'benzodizapine' => $this->input->post('benzodizapine')
	,'cocain' => $this->input->post('cocain')
	,'tes_kehamilan' => $this->input->post('tes_kehamilan')
	,'feses' => $this->input->post('feses')
	,'catatan_keterangan_lab' => $this->input->post('catatan_keterangan_lab')
	,'status_pasien_lab' => $this->input->post('status_pasien_lab')
	);
	$proses = $this->m_pasien->m_update_lab($id_pasien,$data);
		if (!$proses) {
			 $this->session->set_flashdata('msg_update', 
                '<div class="alert alert-info"><h4>Data Pasien berhasil diUpdate !!</h4></div>');    
		header('Location: pasien_harian');
	} else {
		echo "Data Gagal Disimpan";
		echo "<br>";
		echo "<a href='".base_url('index.php/controlinput/tampil_permohonan/')."'>tampil data</a>";
	}
}
public function update_pemeriksaan_fisik_kejiwaan(){
  
	$id_pasien = $this->input->post('id_pasien');
	$data = array ('tinggi_badan' => $this->input->post('tinggi_badan')
	,'berat_badan' => $this->input->post('berat_badan')
	,'suhu' => $this->input->post('suhu')
	,'kepala' => $this->input->post('kepala')
	,'mata' => $this->input->post('mata')
	,'telinga' => $this->input->post('telinga')
	,'hidung' => $this->input->post('hidung')
	,'gigi' => $this->input->post('gigi')
	,'tenggorokan' => $this->input->post('tenggorokan')
	,'leher' => $this->input->post('leher')
	,'dada' => $this->input->post('dada')
	,'pernafasan' => $this->input->post('pernafasan')
	,'tekanan_darah' => $this->input->post('tekanan_darah')
	,'nadi' => $this->input->post('nadi')
	,'paru' => $this->input->post('paru')
	,'jantung' => $this->input->post('jantung')
	,'abdomen' => $this->input->post('abdomen')
	,'anus' => $this->input->post('anus')
	,'genitalia_externa' => $this->input->post('genitalia_externa')
	,'ekstriminas_atas' => $this->input->post('ekstriminas_atas')
	,'ekstrimitas_bawah' => $this->input->post('ekstrimitas_bawah')
	,'kelenjar_getah_bening' => $this->input->post('kelenjar_getah_bening')
	,'appearance_and_speech' => $this->input->post('appearance_and_speech')
	,'mood_afek' => $this->input->post('mood_afek')
	,'thought_and_cognitive' => $this->input->post('thought_and_cognitive')
	,'perception_disorder' => $this->input->post('perception_disorder')
	,'impuls_control' => $this->input->post('impuls_control')
	,'reality_assessment' => $this->input->post('reality_assessment')
	,'hipertensi' => $this->input->post('hipertensi')
	,'batuk_kronis' => $this->input->post('batuk_kronis')
	,'hipertiroid' => $this->input->post('hipertiroid')
	,'radang_usus_buntu' => $this->input->post('radang_usus_buntu')
	,'piouri' => $this->input->post('piouri')
	,'hematochezia' => $this->input->post('hematochezia')
	,'malaria' => $this->input->post('malaria')
	,'gangguan_kejiwaan' => $this->input->post('gangguan_kejiwaan')
	,'stroke' => $this->input->post('stroke')
	,'hemoptoe' => $this->input->post('hemoptoe')
	,'diabetes_melitus' => $this->input->post('diabetes_melitus')
	,'hematuria' => $this->input->post('hematuria')
	,'eksim' => $this->input->post('eksim')
	,'ambeien' => $this->input->post('ambeien')
	,'epilepsi' => $this->input->post('epilepsi')
	,'tumor' => $this->input->post('tumor')
	,'penyakit_jantung' => $this->input->post('penyakit_jantung')
	,'asma' => $this->input->post('asma')
	,'radang_perut' => $this->input->post('radang_perut')
	,'urolitiasis' => $this->input->post('urolitiasis')
	,'alergi' => $this->input->post('alergi')
	,'kusta' => $this->input->post('kusta')
	,'malignansi' => $this->input->post('malignansi')
	,'status_pasien' => $this->input->post('status_pasien')
	);
	$proses = $this->m_pasien->m_update_pemeriksaan_kejiwaan($id_pasien,$data);
		if (!$proses) {
			  $this->session->set_flashdata('msg_update', 
                '<div class="alert alert-info"><h4>Data Pasien berhasil diUpdate !!</h4></div>');   
		header('Location: pasien_harian');
	} else {
		echo "Data Gagal Disimpan";
		echo "<br>";
		echo "<a href='".base_url('index.php/controlinput/tampil_permohonan/')."'>tampil data</a>";
	}
}
	 function pasien_harian(){
		 $data['images'] = $this->uploadimage->get_all();
		 	 $data['num_results'] = $this->m_pasien->hitung_pasien_pending();
	 $data['num_results_done'] = $this->m_pasien->hitung_pasien_done();
		 $data['query'] = $this->m_pasien->m_tampil_pasien();
		$this->load->view('v_coba',$data);
			
			
	 }
public function update_pemeriksaan_radiologi(){
  
	$id_pasien = $this->input->post('id_pasien');
	$data = array ('X_RAY' => $this->input->post('X_RAY')
	,'status_pasien_radiologi' => $this->input->post('status_pasien_radiologi')
	,'catatan_keterangan_radiologi' => $this->input->post('catatan_keterangan_radiologi')
	);

	$proses = $this->m_pasien->m_update_pemeriksaan_radiologi($id_pasien,$data);
		if (!$proses) {
		 $this->session->set_flashdata('msg_update', 
                '<div class="alert alert-info"><h4>Data Pasien berhasil diUpdate !!</h4></div>');   
		header('Location: pasien_harian');
	} else {
		echo "Data Gagal Disimpan";
		echo "<br>";
		echo "<a href='".base_url('index.php/controlinput/tampil_permohonan/')."'>tampil data</a>";
	}
}

public function update_profil(){
  
	$id = $this->input->post('id');
	$data = array ('nama' => $this->input->post('nama')
	,'alamat' => $this->input->post('alamat')
	,'username' => $this->input->post('username')
	,'password' => $this->input->post('password')
	,'status' => $this->input->post('status')
	);
	
	$proses = $this->m_pasien->m_update_profil($id,$data);
		if (!$proses) {
		 $this->session->set_flashdata('msg_update', 
                '<div class="alert alert-info"><h4>Data Pasien berhasil diUpdate !!</h4></div>');   
		header('Location: ');
	} else {
		echo "Data Gagal Disimpan";
		echo "<br>";
		echo "<a href='".base_url('index.php/controlinput/tampil_permohonan/')."'>tampil data</a>";
	}
}

public function update_pasien(){
  
	$id_pasien = $this->input->post('id_pasien');
	$data = array ('nama_lengkap' => $this->input->post('nama_lengkap')
	,'tgl_lahir' => $this->input->post('tgl_lahir')
	,'jenis_kelamin' => $this->input->post('jenis_kelamin')
	,'status' => $this->input->post('status')
	,'alamat' => $this->input->post('alamat')
	,'no_register' => $this->input->post('no_register')
	,'tgl_masuk' => $this->input->post('tgl_masuk')
	,'pemeriksaan' => $this->input->post('pemeriksaan')
	,'pengirim' => $this->input->post('pengirim')
	,'negara' => $this->input->post('negara')
	,'no_passport' => $this->input->post('no_passport')
   
	);

	$proses = $this->m_pasien->m_update_pasien($id_pasien,$data);
		if (!$proses) {
			 $this->session->set_flashdata('msg_update', 
                '<div class="alert alert-info"><h4>Data Pasien berhasil diUpdate !!</h4></div>');   
		header('Location: pasien_harian');
	} else {
		echo "Data Gagal Disimpan";
		echo "<br>";
		echo "<a href='".base_url('index.php/controlinput/tampil_permohonan/')."'>tampil data</a>";
	}
}
public function hapus_pasien(){
	$id_pasien = $this->uri->segment(3);
	$proses = $this->m_pasien->m_hapus_pasien($id_pasien);
		if (!$proses) {
			$this->session->set_flashdata('msg_delete', 
                '<div class="alert alert-info"><h4>Data Pasien berhasil Dihapus!!</h4></div>'); 
 redirect("index.php/home/pasien_harian");
	} else {
		echo "Data Gagal Disimpan";
		echo "<br>";
		header('Location: pasien_harian');
	}
}

public function index_profil(){
	$this->load->view("v_profil");
	
}
	public function index(){
	
		$comp = array(
				"menuprofile" => $this->load->view("template/menuprofile",array(),true),
				"sidebar" => $this->load->view("template/sidebar",array(),true),
				"menufooter" => $this->load->view("template/menufooter",array(),true),
				"topnav" => $this->load->view("template/topnav",array(),true),
				"pagecontent" => $this->load->view("template/pagecontent",array(),true),
				"footer" => $this->load->view("template/footer",array(),true)
			);
		$data['num'] = $this->m_pasien->hitung_tkdn();
		$this->load->view("template/index",$comp);

		
		
	}
	public function form_daftar_TKLN(){
		
		$data['kodeunik'] = $this->m_pasien->code_otomatis();
			
	$this->load->view('v_insert_pasien', $data);
	
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
