<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" href="images/favicon.ico" type="image/ico" />
    
    <title> SILPK | Home</title>
	<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<style>
#notifications {
    cursor: pointer;
    position: fixed;
    right: 0px;
    z-index: 9999;
    bottom: 2px;
    margin-bottom: 25px;
    margin-right: 15px;
    min-width: 300px; 
    max-width: 800px;
	
}
</style>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <!-- Bootstrap -->
    <link href="<?php echo base_url()."assets";?>/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="<?php echo base_url()."assets";?>/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    <link href="<?php echo base_url()."assets";?>/vendors/nprogress/nprogress.css" rel="stylesheet">
    <!-- iCheck -->
    <link href="<?php echo base_url()."assets";?>/vendors/iCheck/skins/flat/green.css" rel="stylesheet">

    <!-- Datatables -->
    <link href="<?php echo base_url()."assets";?>/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo base_url()."assets";?>/vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo base_url()."assets";?>/vendors/datatables.net-fixedheader-bs/css/fixedHeader.bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo base_url()."assets";?>/vendors/datatables.net-responsive-bs/css/responsive.bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo base_url()."assets";?>/vendors/datatables.net-scroller-bs/css/scroller.bootstrap.min.css" rel="stylesheet">
	
    <!-- bootstrap-progressbar -->
    <link href="<?php echo base_url()."assets";?>/vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet">
    <!-- JQVMap -->
    <link href="<?php echo base_url()."assets";?>/vendors/jqvmap/dist/jqvmap.min.css" rel="stylesheet"/>
    <!-- bootstrap-daterangepicker -->
    <link href="<?php echo base_url()."assets";?>/vendors/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">

     <!-- bootstrap-wysiwyg -->
    <link href="<?php echo base_url()."assets";?>/vendors/google-code-prettify/bin/prettify.min.css" rel="stylesheet">
    <!-- Select2 -->
    <link href="<?php echo base_url()."assets";?>/vendors/select2/dist/css/select2.min.css" rel="stylesheet">
    <!-- Switchery -->
    <link href="<?php echo base_url()."assets";?>/vendors/switchery/dist/switchery.min.css" rel="stylesheet">
    <!-- starrr -->
    <link href="<?php echo base_url()."assets";?>/vendors/starrr/dist/starrr.css" rel="stylesheet">
    <!-- bootstrap-daterangepicker -->
    <link href="<?php echo base_url()."assets";?>/vendors/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">

    <!-- Custom Theme Style -->
    <link href="<?php echo base_url()."assets";?>/build/css/custom.min.css" rel="stylesheet">
			<script type="text/javascript">        
    function tampilkanwaktu(){          
    var waktu = new Date();            
    var sh = waktu.getHours() + "";    
    var sm = waktu.getMinutes() + "";  
    var ss = waktu.getSeconds() + "";  
    document.getElementById("clock").innerHTML = (sh.length==1?"0"+sh:sh) + ":" + (sm.length==1?"0"+sm:sm) + ":" + (ss.length==1?"0"+ss:ss);
    }
</script>
<script>
$(document).ready(function(){
    $('[data-toggle="popover"]').popover();   
});
</script>
	<script>
		function hanyaAngka(evt) {
		  var charCode = (evt.which) ? evt.which : event.keyCode
		   if (charCode > 31 && (charCode < 48 || charCode > 57))
 
		    return false;
		  return true;
		}
	</script>
  </head>

 <body onload="tampilkanwaktu();setInterval('tampilkanwaktu()', 1000);" class="nav-md">
 
    <div class="container body">
	
      <div class="main_container">
        <div class="col-md-3 left_col">
		
          <div class="left_col scroll-view">
            <div class="navbar nav_title" style="border: 0;">
              <a href="index.html" class="site_title"><i class="fa fa-hospital-o"></i> <span>SILPK</span></a>
            </div>

            <div class="clearfix"></div>

            <!-- menu profile quick info -->
         <!-- menu profile quick info -->
            <div class="profile clearfix">
              <div class="profile_pic">
                <img src="<?php echo base_url()."assets/production/";?>images/img.jpg" alt="..." class="img-circle profile_img">
              </div>
              <div class="profile_info">
              	<h2><?php echo $this->session->userdata('ses_status');?></h2>
                <span><?php echo $this->session->userdata('ses_nama');?></span>
              </div>
            </div>
<!-- /menu profile quick info -->
            <!-- /menu profile quick info -->

            <br />

            <!-- sidebar menu -->
           <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
              <div class="menu_section">
                <h3>General</h3>
                <ul class="nav side-menu">
                               <?php if($this->session->userdata('akses')=='admin'):?>
					<li><a href="<?php echo base_url()."index.php/home"; ?>"><i class="fa fa-home"></i> Home </a>
                  </li>
				  
                  <li><a href="<?php echo base_url()."index.php/data_pasien_harian/pasien_harian"; ?>"><i class="fa fa-table"></i> Daftar Pasien Harian </a>
                  </li>
				 
                  <li><a href="<?php echo base_url()."index.php/database_pasien"; ?>"><i class="fa fa-database"></i> Database Pasien </a>
                  </li>
				   <li><a href="<?php echo base_url()."index.php/c_info_pasien"; ?>"><i class="fa fa-database"></i> Info TKDN/TKLN</a>
                  </li>
                  <li><a href="<?php echo base_url()."index.php/login/data_user"; ?>"><i class="fa fa-users"></i> Data Akun Pengguna </a>
                  </li>
				   <?php elseif($this->session->userdata('akses')=='poliklinik'):?>
				   <li><a href="<?php echo base_url()."index.php/home"; ?>"><i class="fa fa-home"></i> Home </a>
                  </li>
				   <li><a href="<?php echo base_url()."index.php/data_pasien_harian/pasien_harian"; ?>"><i class="fa fa-table"></i> Daftar Pasien Harian </a>
                  </li>
				  <li><a href="<?php echo base_url()."index.php/c_info_pasien"; ?>"><i class="fa fa-database"></i> Info TKDN/TKLN</a>
                  </li>
				  <?php elseif($this->session->userdata('akses')=='lab' || $this->session->userdata('akses')=='radiologi'):?>
				  <li><a href="<?php echo base_url()."index.php/home"; ?>"><i class="fa fa-home"></i> Home </a>
				  </li>
				     <li><a href="<?php echo base_url()."index.php/data_pasien_harian/pasien_harian"; ?>"><i class="fa fa-table"></i> Daftar Pasien Harian </a>
					 </li>
					 <li><a href="<?php echo base_url()."index.php/c_info_pasien"; ?>"><i class="fa fa-database"></i> Info TKDN/TKLN</a>
                  </li>
				     <?php elseif($this->session->userdata('akses')=='frontoffice'):?>
					<li><a href="<?php echo base_url()."index.php/home"; ?>"><i class="fa fa-home"></i> Home </a>
					
                  </li>
				   <li><a href="<?php echo base_url()."index.php/data_pasien_harian/pasien_harian"; ?>"><i class="fa fa-table"></i> Daftar Pasien Harian </a>
                  </li>
				  <li><a href="<?php echo base_url()."index.php/c_info_pasien"; ?>"><i class="fa fa-database"></i> Info TKDN/TKLN</a>
                  </li>
				   <?php endif;?>
                </ul>
              </div>
            </div>
            <!-- /sidebar menu -->

            <!-- /menu footer buttons -->
           
            <!-- /menu footer buttons -->
          </div>
        </div>

		
<!-- page content -->
      <div class="right_col" role="main">
              <div class="x_panel" align="center">
			  <?php if($this->session->userdata('akses')=='frontoffice'):?>
                  <h1>Formulir Pendaftaran</h1>
                  <h2>Pasien Pemeriksaan Kesehatan Tenaga Kerja Luar Negeri</h2>
				  <?php elseif($this->session->userdata('akses')=='lab'):?>
				   <h1>Pemeriksaan LAB</h1>
                  <h2>Pasien Pemeriksaan Kesehatan Tenaga Kerja Luar Negeri</h2>
				  <?php endif;?>
                  <div class="clearfix"></div>
                </div>
              <div class="clearfix"></div>
                <div class="row">
				<?php if($this->session->userdata('akses')=='frontoffice'):?>
                 <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="x_panel"><div align="right"><h4>
					<div class="clearfix"></div><h4><div align="right">
				   <p class="text-primary"><span id="clock"></span> 
  <?php
  
$hari = date('l');

if ($hari=="Sunday") {
 echo "Minggu";
}elseif ($hari=="Monday") {
 echo "Senin";
}elseif ($hari=="Tuesday") {
 echo "Selasa";
}elseif ($hari=="Wednesday") {
 echo "Rabu";
}elseif ($hari=="Thursday") {
 echo("Kamis");
}elseif ($hari=="Friday") {
 echo "Jum'at";
}elseif ($hari=="Saturday") {
 echo "Sabtu";
}
?>,

<?php
$tgl =date('d');
echo $tgl;
$bulan =date('F');
if ($bulan=="January") {
 echo " Januari ";
}elseif ($bulan=="February") {
 echo " Februari ";
}elseif ($bulan=="March") {
 echo " Maret ";
}elseif ($bulan=="April") {
 echo " April ";
}elseif ($bulan=="May") {
 echo " Mei ";
}elseif ($bulan=="June") {
 echo " Juni ";
}elseif ($bulan=="July") {
 echo " Juli ";
}elseif ($bulan=="August") {
 echo " Agustus ";
}elseif ($bulan=="September") {
 echo " September ";
}elseif ($bulan=="October") {
 echo " Oktober ";
}elseif ($bulan=="November") {
 echo " November ";
}elseif ($bulan=="December") {
 echo " Desember ";
}
$tahun=date('Y');
echo $tahun;

?>
 </h4> </p> 
 <div class="container">
 
</div>
<table width=100%>
<tr><td>  <div align="left"><h4>Data diri pasien <b>TKLN</b>&nbsp   <a href="#" title="Petunjuk" data-toggle="popover" data-trigger="hover" data-content="* Massukan Data Diri pasien dengan lengkap,"><img src="<?php echo base_url()."assets/production/";?>images/helpp.png" width="25px" height="26px" alt="..." ></a></h4></div></td>
<td colspan=2>  <div align="right"><b>Petugas,</br> </b>  <a href="#" title="  Status" data-toggle="popover" data-placement="top" data-content="<?php echo $this->session->userdata('ses_status');?>"><mark><?php echo $this->session->userdata('ses_nama');?></mark> </div> </a>  </td>

</tr>
</table>

        
					</div>
					 
                      <div class="x_title">
                       
					 
                        <div class="clearfix"></div>
                      </div>
                      <!-- data diri -->
                      <div class="x_content">
				
<?php if ($this->session->flashdata('message')): ?>
        <script>
		
            swal({
                title: "Succesfully Inserted",
                text: "Data is success",
                timer: 2500,
                showConfirmButton: false,
                type: 'success',
				icon: 'success'
            });
        </script>
		<?php endif ?>
                      <div class="col-md-6 col-xs-12">
                      <br/>
					  <?php echo form_open('index.php/home/insert') ?>
                        <div class="form-horizontal form-label-left">
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Nama Lengkap</label>
                              <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" class="form-control has-feedback-left" name="nama_lengkap"  placeholder="Nama Lengkap">
                        <span class="fa fa-user form-control-feedback left" aria-hidden="true"></span>
                      </div>
                          </div>
                          <div class="form-group">
                            <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12">Tanggal Lahir</label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input id="middle-name" name="tgl_lahir" class="form-control col-md-7 col-xs-12" type="date">
                            </div>
                          </div>
                          <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Jenis Kelamin</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                        <table><tr align="center"><td> <div class="radio">
                           <label>
                            <input type="radio" value= "Male" class="flat" name="jenis_kelamin"> Male 
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Female"  class="flat" name="jenis_kelamin"> Female
                            </label>
                          </div></td></tr></table>
									 
                                    </div>
                                  </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Status</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                              <select class="select2_group form-control" name="status">
                                <option value="lajang">Lajang</option>
                                <option value="menikah">Menikah</option>
                                <option value="cerai">Cerai</option>
                              </select>
                            </div>
                          </div>
                          <div class="form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12">Alamat Pasien</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                              <textarea class="form-control" name="alamat" rows="3"></textarea>
                            </div>
                          </div>
						      <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Tanggal Harian</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                              <input type="text" name="tgl_input" class="form-control" value ="<?php $tgl=date('d-m-Y');
			echo $tgl;
			?>" readonly>
                            </div>
                          </div>
                          <br/>
                          <br/>
                        </div>
                      </div>

                      <div class="col-md-6 col-xs-12">
                      <br/>
                        <div class="form-horizontal form-label-left">

                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">No. Register</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                              <input type="text" name="no_register" readonly value="<?= $kodeunik; ?>" class="form-control">
                            </div>
                          </div>

                          <div class="form-group">
                            <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12">Tanggal Masuk</label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input id="middle-name" class="form-control col-md-7 col-xs-12" name="tgl_masuk" type="date">
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Pemeriksaan</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                              <input type="text" name="pemeriksaan" class="form-control" value ="Tenaga Kerja Luar Negeri" readonly placeholder="Tenaga Kerja Dalam Negeri">
                            </div>
                          </div>
			
						   
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Pengirim</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                              <select name="pengirim" class="select2_group form-control">
                                <option value="PT. NAYAKA KALYANA">PT. NAYAKA KALYANA</option>
                                <option value="PT. FLAMBOYAN GEMAJASA">PT. FLAMBOYAN GEMAJASA</option>
                                <option value="PT. HENDRARTA ARGARAYA">PT. HENDRARTA ARGARAYA</option>
                                <option value="PT. MEGAH UTAMA KRIYA NUGRAHA">PT. MEGAH UTAMA KRIYA NUGRAHA</option>
                                <option value="PT. SUKSES BERSAMA YATFUARI">PT. SUKSES BERSAMA YATFUARI</option>
                                <option value="PT. SODO SAKTI JAYA">PT. SODO SAKTI JAYA</option>
                              </select>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Negara</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                              <select name="negara" class="select2_group form-control">
                                <optgroup label="Pemeriksaan Non-Paket">
                                  <option value="TWN">Taiwan</option>
                                  <option value="HKG">Hongkong</option>
                                  <option value="SGP">Singapore</option>
                                  <option value="MLY">Malaysia</option>
                                  <option value="BRD">Brunei D.</option>
                                  <option value="TRT">Timur Tengah</option>
                                </optgroup>
                                <optgroup label="Pemeriksaan Paket">
                                  <option value="TWN-p">Taiwan - Paket</option>
                                  <option value="HKG-p">Hongkong - Paket</option>
                                  <option value="SGP-p">Singapore - Paket</option>
                                  <option value="MLY-p">Malaysia - Paket</option>
                                  <option value="BRD-p">Brunei D. - Paket</option>
                                  <option value="TRT-p">Timur Tengah - Paket</option>
                                </optgroup>
                              </select>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Nomor Passport</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                              <input name="no_passport" placeholder="Nomor Passport" type="text" onkeypress="return hanyaAngka(event)" class="form-control">
							  <input name="petugas" placeholder="Nomor Passport" value="<?php echo $this->session->userdata('ses_nama');?>" type="hidden"  class="form-control">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Paket</label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <div id="gender" class="btn-group" data-toggle="buttons">
                                <label class="btn btn-default" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                  <input type="radio" name="gender" value="male"> &nbsp; Paket &nbsp;
                                </label>
                                <label class="btn btn-primary" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                  <input type="radio" name="gender" value="female"> Non Paket
                                </label>
                              </div>
                            </div>
                          </div>

                        </div>
                      </div>

                      </div>
					   
                      <!-- END data diri -->
					 <?php echo form_open('index.php/home/insert') ?>
                      <div class="x_title">
                        <h2>Permintaan Pemeriksaan  &nbsp</h2> <a href="#" title="Petunjuk" data-toggle="popover" data-trigger="hover" data-content="* Ceklist data permintaan pemeriksaan sesuai dengan kondisi pasien."><img src="<?php echo base_url()."assets/production/";?>images/helpp.png" width="25px" height="26px" alt="..." ></a>
                        <div class="clearfix"></div>
                      </div>
                      <!-- pemeriksaan fisik -->
                      <div class="x_content">
                        <br/>
                        <div class="form-horizontal form-label-left">
                          <div class="form-group">
                            <label class="col-md-3 col-sm-3 col-xs-12 control-label">Pemeriksaan Fisik dan Kejiwaan
                            </label>
                            <div class="col-md-3 col-sm-9 col-xs-12">
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" name="check_list[]" value="Pemeriksaan fisik" class="flat"> Pemeriksaan Fisik
                                </label>
                              </div>
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" name="check_list[]" value="Pemeriksaan kejiwaan"  class="flat"> Pemeriksaan Kejiwaan
                                </label>
                              </div>
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" name="check_list[]" value="Riwayat penyakit" class="flat"> Riwayat Penyakit
                                </label>
                              </div>
                            </div>
                          </div>
                        </div>
                          <div class="ln_solid"></div>
                      </div>
                      <!-- END pemeriksaan fisik -->
                      <!-- pemeriksaan laboratorium -->
                 <div class="x_content">
                        <br/>
                        <div class="form-horizontal form-label-left">
                          <div class="form-group">
                            <label class="col-md-3 col-sm-3 col-xs-12 control-label">Pemeriksaan Laboratorium
                            </label>
                            <div class="col-md-3 col-sm-9 col-xs-12">
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" name="check_list_lab[]" value="Darah Lengkap" class="flat"> Darah Lengkap
                                </label>
                              </div>
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" name="check_list_lab[]" value="Pemeriksaan Urine"  class="flat"> Pemeriksaan Urine
                                </label>
                              </div>
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" name="check_list_lab[]" value="Kimia Klinik - SGOT" class="flat"> Kimia Klinik - SGOT
                                </label>
                              </div>
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" name="check_list_lab[]" value="Kimia Klinik - SGPT" class="flat"> Kimia Klinik - SGPT
                                </label>
                              </div>
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" name="check_list_lab[]" value="Kimia Klinik - Ureum" class="flat"> Kimia Klinik - Ureum
                                </label>
                              </div>
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" name="check_list_lab[]" value="Kimia Klinik - Kreatinin" class="flat"> Kimia Klinik - Kreatinin
                                </label>
                              </div>
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" name="check_list_lab[]" value="Kimia Klinik - Gula Darah Sesaat" class="flat"> Kimia Klinik - Gula Darah Sesaat
                                </label>
                              </div>
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" name="check_list_lab[]" value="Kimia Klinik - HBsAg" class="flat"> Kimia Klinik - HBsAg
                                </label>
                              </div>
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" name="check_list_lab[]" value="Kimia Klinik - HIV" class="flat"> Kimia Klinik - HIV
                                </label>
                              </div>
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" name="check_list_lab[]" value="Tes Kehamilan" class="flat"> Tes Kehamilan
                                </label>
                              </div>
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" name="check_list_lab[]" value="Feses" class="flat"> Feses
                                </label>
                              </div>
                            </div>

                            <div class="col-md-3 col-sm-9 col-xs-12">
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" name="check_list_lab[]" value="Serologi - TPHA" class="flat"> Serologi - TPHA
                                </label>
                              </div>
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" name="check_list_lab[]" value="Serologi - VDRL" class="flat"> Serologi - VDRL
                                </label>
                              </div>
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" name="check_list_lab[]" value="Tes Narkoba - Amphetamine" class="flat"> Tes Narkoba - Amphetamine
                                </label>
                              </div>
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" name="check_list_lab[]" value="Tes Narkoba - Methamphetamine" class="flat"> Tes Narkoba - Methamphetamine
                                </label>
                              </div>
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" name="check_list_lab[]" value="Tes Narkoba - THC" class="flat"> Tes Narkoba - THC
                                </label>
                              </div>
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" name="check_list_lab[]" value="Tes Narkoba - Morphine" class="flat"> Tes Narkoba - Morphine
                                </label>
                              </div>
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" name="check_list_lab[]" value="Tes Narkoba - Benzodizapine" class="flat"> Tes Narkoba - Benzodizapine 
                                </label>
                              </div>
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" name="check_list_lab[]" value="Tes Narkoba-Cocain" class="flat"> Tes Narkoba - Cocain
                                </label>
                              </div>
                            </div>

                          </div>
                        </div>
                          <div class="ln_solid"></div>
                      </div>
                      <!-- END pemeriksaan laboratorium -->
                      <!-- pemeriksaan laboratorium -->
                      <div class="x_content">
                        <br/>
                        <div class="form-horizontal form-label-left">
                          <div class="form-group">
                            <label class="col-md-3 col-sm-3 col-xs-12 control-label">Pemeriksaan Radiologi
                            </label>
                              
                            <div class="checkbox">
                                <label>
                                  <input type="checkbox" name="check_list_radio[]" value="X-RAY" class="flat"> X-RAY
								  
                                </label>
                              </div>
                              </div>
                            </div>
                          </div>
                        </div>
                          <div class="ln_solid"></div>
                      </div>    <ul class="nav navbar-right panel_toolbox">
                          <button type="button" class="btn btn-danger">Batal</button>
                          <button type="submit" class="btn btn-primary">Simpan</button>
                        </ul>
                      <!-- END pemeriksaan laboratorium -->
                     
					<?php echo form_close()?>
                    </div>
                  </div>
                </div>
      </div>
<?php endif ?>