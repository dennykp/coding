<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" href="images/favicon.ico" type="image/ico" />
		

    <style>
	.inputGroup {
    background-color: #fff;
    display: block;
    margin: 10px 0;
    position: relative;

    label {
      padding: 12px 30px;
      width: 100%;
      display: block;
      text-align: left;
      color: #3C454C;
      cursor: pointer;
      position: relative;
      z-index: 2;
      transition: color 200ms ease-in;
      overflow: hidden;

      &:before {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        content: '';
        background-color: #5562eb;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%) scale3d(1, 1, 1);
        transition: all 300ms cubic-bezier(0.4, 0.0, 0.2, 1);
        opacity: 0;
        z-index: -1;
      }

      &:after {
        width: 32px;
        height: 32px;
        content: '';
        border: 2px solid #D1D7DC;
        background-color: #fff;
        background-image: url("data:image/svg+xml,%3Csvg width='32' height='32' viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M5.414 11L4 12.414l5.414 5.414L20.828 6.414 19.414 5l-10 10z' fill='%23fff' fill-rule='nonzero'/%3E%3C/svg%3E ");
        background-repeat: no-repeat;
        background-position: 2px 3px;
        border-radius: 50%;
        z-index: 2;
        position: absolute;
        right: 30px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        transition: all 200ms ease-in;
      }
    }

    input:checked ~ label {
      color: #fff;

      &:before {
        transform: translate(-50%, -50%) scale3d(56, 56, 1);
        opacity: 1;
      }

      &:after {
        background-color: #54E0C7;
        border-color: #54E0C7;
      }
    }

    input {
      width: 32px;
      height: 32px;
      order: 1;
      z-index: 2;
      position: absolute;
      right: 30px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      visibility: hidden;
    }
  }


// codepen formatting
.form {
  padding: 0 16px;
  max-width: 550px;
  margin: 50px auto;
  font-size: 18px;
  font-weight: 600;
  line-height: 36px;
}

body {
  background-color: #D1D7DC;
  font-family: 'Fira Sans', sans-serif;
}

*,
*::before,
*::after {
  box-sizing: inherit;
}

html {
  box-sizing: border-box;
}

code {
  background-color: #9AA3AC;
  padding: 0 8px;
}

	</style>
    <title> SILPK | Home</title>
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
	<script>
$(document).ready(function(){
    $('[data-toggle="popover"]').popover();   
});
</script>
		<script type="text/javascript">        
    function tampilkanwaktu(){          
    var waktu = new Date();            
    var sh = waktu.getHours() + "";    
    var sm = waktu.getMinutes() + "";  
    var ss = waktu.getSeconds() + "";  
    document.getElementById("clock").innerHTML = (sh.length==1?"0"+sh:sh) + ":" + (sm.length==1?"0"+sm:sm) + ":" + (ss.length==1?"0"+ss:ss);
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

        <!-- top navigation -->
        <!-- top navigation -->
        <div class="top_nav">
          <div class="nav_menu">
            <nav>
              <div class="nav toggle">
                <a id="menu_toggle"><i class="fa fa-bars"></i></a>
              </div>

              <ul class="nav navbar-nav navbar-right">
                <li class="">
                  <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <img src="<?php echo base_url()."assets/production/";?>images/img.jpg" alt=""><?php echo $this->session->userdata('ses_nama');?>
                    <span class=" fa fa-angle-down"></span>
                  </a>
                  <ul class="dropdown-menu dropdown-usermenu pull-right">
                    <li><a href="javascript:;">Pengaturan Profile</a></li>
                    <li><a href="<?php echo base_url().'index.php/login/logout'?>"><i class="fa fa-sign-out pull-right"></i> Log Out</a></li>
                  </ul>
                </li>
              </ul>
            </nav>
          </div>
        </div>
        <!-- /top navigation -->
        <!-- /top navigation -->

        <!-- page content -->
      <!-- page content -->
             <!-- page content -->
       <!-- page content -->
      <div class="right_col" role="main">
              <div class="x_panel" align="center">
			  <?php if($this->session->userdata('akses')=='frontoffice'):?>
                  <h1>Formulir Pendaftaran</h1>
                  <h2>Pasien Pemeriksaan Kesehatan Tenaga Kerja Luar Negeri</h2>
				 
				  
				  <?php endif;?>
                  <div class="clearfix"></div><h4><div align="right">
				   <span id="clock"></span> 
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
 </h4>               </div>
              <div class="clearfix"></div>
			  
                <div class="row">
				<?php if($this->session->userdata('akses')=='poliklinik' || $this->session->userdata('akses')=='lab' || $this->session->userdata('akses')=='radiologi' || $this->session->userdata('akses')=='frontoffice' || $this->session->userdata('akses')=='admin'):?>
                  <div class="col-md-12 col-sm-12 col-xs-12">
             
				  <div class="x_panel">
                      <div class="x_title">
                        <table width=100%>
<tr><td>  <div align="left"><h4>Data diri pasien <b>TKLN</b>&nbsp   <a href="#" title="Petunjuk" data-toggle="popover" data-trigger="hover" data-content="* Massukan Data Diri pasien dengan lengkap,"><img src="<?php echo base_url()."assets/production/";?>images/helpp.png" width="25px" height="26px" alt="..." ></a></h4></div></td>
<td colspan=2>  <div align="right"><b>Petugas,</br> </b>  <a href="#" title="  Status" data-toggle="popover" data-placement="top" data-content="<?php echo $this->session->userdata('ses_status');?>"><mark><?php echo $this->session->userdata('ses_nama');?></mark> </div> </a>  </td>

</tr>
</table>

                        <div class="clearfix"></div>
						
                      </div>
					  
                      <!-- data diri -->
                      <div class="x_content">
                      <div class="col-md-6 col-xs-12">
                      <br/>
				 
<form action="<?php echo base_url('index.php/home/update_pasien'); ?>" method="POST">
					
					<?php foreach ($query->result() as $baris){?>
						
                        <div class="form-horizontal form-label-left">
					<?php if($this->session->userdata('akses')=='frontoffice' || $this->session->userdata('akses')=='admin'): ?>
                          <div class="form-group">
						  		<input type="hidden" name="id_pasien" value="<?php echo $baris->id_pasien; ?>">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Nama Lengkap</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                              <input type="text" name="nama_lengkap"  value="<?php echo $baris->nama_lengkap; ?>" class="form-control">
                            </div>
                          </div>
						<?php endif ?>
						<?php if($this->session->userdata('akses')=='poliklinik' || $this->session->userdata('akses')=='lab' || $this->session->userdata('akses')=='radiologi'): ?>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Nama Lengkap</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                              <input type="text" name="nama_lengkap"  readonly value="<?php echo $baris->nama_lengkap; ?>" class="form-control">
                            </div>
                          </div>
						<?php endif ?>
						<?php if($this->session->userdata('akses')=='frontoffice' || $this->session->userdata('akses')=='admin' ): ?>
                          <div class="form-group">
                            <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12">Tanggal Lahir</label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input id="middle-name" name="tgl_lahir" value="<?php echo $baris->tgl_lahir; ?>" class="form-control col-md-7 col-xs-12" type="date">
                            </div>
                          </div>
						  <?php endif ?>
						  	<?php if($this->session->userdata('akses')=='poliklinik' || $this->session->userdata('akses')=='lab' || $this->session->userdata('akses')=='radiologi'): ?>
                          <div class="form-group">
                            <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12">Tanggal Lahir</label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input id="middle-name" name="tgl_lahir" value="<?php echo $baris->tgl_lahir; ?>" class="form-control col-md-7 col-xs-12" type="date">
                            </div>
                          </div>
						  <?php endif ?>
						 <?php if($this->session->userdata('akses')=='frontoffice' || $this->session->userdata('akses')=='admin'): ?>
                            <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Jenis Kelamin</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                      <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" value= "Male" <?php echo set_value('jenis_kelamin', $baris->jenis_kelamin) == "Male"? "checked": "";  ?> class="flat" name="jenis_kelamin"> Male
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Female" <?php echo set_value('jenis_kelamin', $baris->jenis_kelamin) == "Female"? "checked": "";  ?> class="flat" name="jenis_kelamin"> Female
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
						  <?php endif ?>
						  	 <?php if($this->session->userdata('akses')=='poliklinik' || $this->session->userdata('akses')=='lab' || $this->session->userdata('akses')=='radiologi'): ?>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Jenis Kelamin</label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <div id="gender" class="btn-group" data-toggle="buttons">
                                <label class="btn btn-default" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                  <input type="radio" readonly value="<?php echo $baris->jenis_kelamin; ?>" name="jenis_kelamin" value="male"> &nbsp; Laki-laki &nbsp;
                                </label>
                                <label class="btn btn-primary" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                  <input type="radio" readonly name="jenis_kelamin" value="<?php echo $baris->jenis_kelamin; ?>" value="female"> Perempuan
                                </label>
                              </div>
                            </div>
                          </div>
						  <?php endif ?>
						  <?php if($this->session->userdata('akses')=='frontoffice' || $this->session->userdata('akses')=='admin'): ?>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Status</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                              <select class="select2_group form-control" value="<?php echo $baris->status; ?>" name="status">
                                <option value="lajang">Lajang</option>
                                <option value="menikah">Menikah</option>
                                <option value="cerai">Cerai</option>
                              </select>
                            </div>
                          </div>
						  <?php endif ?>
						    <?php if($this->session->userdata('akses')=='poliklinik' || $this->session->userdata('akses')=='lab' || $this->session->userdata('akses')=='radiologi'): ?>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Status</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                              <select class="select2_group form-control" readonly value="<?php echo $baris->status; ?>" name="status">
                                <option value="lajang">Lajang</option>
                                <option value="menikah">Menikah</option>
                                <option value="cerai">Cerai</option>
                              </select>
                            </div>
                          </div>
						  <?php endif ?>
						    <?php if($this->session->userdata('akses')=='frontoffice' || $this->session->userdata('akses')=='admin'): ?>
                          <div class="form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12">Alamat Pasien</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                             <input type="text"  name="alamat" class="form-control" value="<?php echo $baris->alamat; ?>">
                            </div>
                          </div>
						  <?php endif ?>
						   <?php if($this->session->userdata('akses')=='poliklinik' || $this->session->userdata('akses')=='lab' || $this->session->userdata('akses')=='radiologi'): ?>
                          <div class="form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12">Alamat Pasien</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                             <input type="text" readonly  name="alamat" class="form-control" value="<?php echo $baris->alamat; ?>">
                            </div>
                          </div>
						  <?php endif ?>
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
                              <input type="text" readonly name="no_register" class="form-control" value="<?php echo $baris->no_register; ?>">
                            </div>
                          </div>

                          <div class="form-group">
                            <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12">Tanggal Masuk</label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input id="middle-name" readonly class="form-control col-md-7 col-xs-12" name="tgl_masuk" type="date" value="<?php echo $baris->tgl_masuk; ?>">
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Pemeriksaan</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                              <input type="text" readonly name="pemeriksaan" class="form-control" placeholder="Tenaga Kerja Dalam Negeri" value="<?php echo $baris->pemeriksaan; ?>">
                            </div>
                          </div>
						   <?php if($this->session->userdata('akses')=='frontoffice' ): ?>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Pengirim</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                              <select name="pengirim" class="select2_group form-control" value="<?php echo $baris->pengirim; ?>">
                                <option value="PT. NAYAKA KALYANA">PT. NAYAKA KALYANA</option>
                                <option value="PT. FLAMBOYAN GEMAJASA">PT. FLAMBOYAN GEMAJASA</option>
                                <option value="PT. HENDRARTA ARGARAYA">PT. HENDRARTA ARGARAYA</option>
                                <option value="PT. MEGAH UTAMA KRIYA NUGRAHA">PT. MEGAH UTAMA KRIYA NUGRAHA</option>
                                <option value="PT. SUKSES BERSAMA YATFUARI">PT. SUKSES BERSAMA YATFUARI</option>
                                <option value="PT. SODO SAKTI JAYA">PT. SODO SAKTI JAYA</option>
                              </select>
                            </div>
                          </div><?php endif ?>
						  <?php if($this->session->userdata('akses')=='poliklinik' || $this->session->userdata('akses')=='lab' || $this->session->userdata('akses')=='radiologi'): ?>
						     <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Pengirim</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                              <select name="pengirim" readonly class="select2_group form-control" value="<?php echo $baris->pengirim; ?>">
                                <option value="PT. NAYAKA KALYANA">PT. NAYAKA KALYANA</option>
                                <option value="PT. FLAMBOYAN GEMAJASA">PT. FLAMBOYAN GEMAJASA</option>
                                <option value="PT. HENDRARTA ARGARAYA">PT. HENDRARTA ARGARAYA</option>
                                <option value="PT. MEGAH UTAMA KRIYA NUGRAHA">PT. MEGAH UTAMA KRIYA NUGRAHA</option>
                                <option value="PT. SUKSES BERSAMA YATFUARI">PT. SUKSES BERSAMA YATFUARI</option>
                                <option value="PT. SODO SAKTI JAYA">PT. SODO SAKTI JAYA</option>
                              </select>
                            </div>
                          </div><?php endif ?>
						   <?php if($this->session->userdata('akses')=='frontoffice'): ?>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Negara</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                              <select name="negara" class="select2_group form-control" value="<?php echo $baris->negara; ?>">
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
						  <?php endif ?>
						  	   <?php if($this->session->userdata('akses')=='poliklinik' || $this->session->userdata('akses')=='lab' || $this->session->userdata('akses')=='radiologi'): ?>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Negara</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                              <select name="negara" class="select2_group form-control" readonly value="<?php echo $baris->negara; ?>">
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
						  <?php endif ?>
						  <?php if($this->session->userdata('akses')=='frontoffice'): ?>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Nomor Passport</label>
								<div class="col-md-9 col-sm-9 col-xs-12">
								  <input name="no_passport" type="text" class="form-control" value="<?php echo $baris->no_passport; ?>">
						
							 
                            </div>
                          </div>
						  <?php endif ?>
						   <?php if($this->session->userdata('akses')=='poliklinik' || $this->session->userdata('akses')=='lab' || $this->session->userdata('akses')=='radiologi'): ?>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Nomor Passport</label>
								<div class="col-md-9 col-sm-9 col-xs-12">
								  <input name="no_passport" type="text" class="form-control" readonly value="<?php echo $baris->no_passport; ?>">
						
							 
                            </div>
                          </div>
						  <?php endif ?>
                          <div class="form-group">
                           
                            <div class="col-md-6 col-sm-6 col-xs-12">
                          	
                            </div>
                          </div>

                        </div>
                      </div>
					    <?php if($this->session->userdata('akses')=='admin' || $this->session->userdata('akses')=='frontoffice'):?>
<ul class="nav navbar-right panel_toolbox">
                          <button type="button" class="btn btn-danger">Batal</button>
                          <button type="submit" class="btn btn-primary">Simpan Data LAB</button>
                        </ul>
						<?php endif ?>
                      </div>
					  
					</form>
					   
					      
							  <?php $mark= array('os','Pemeriksaan kejiwaan'); ?>
							  <?php $k;
						
							  $ambil_db= $baris->pemeriksaan_fisik_kejiwaan;
				$ambil_db_lab = $baris->pemeriksaan_laboratorium;
					$ambil_db_radio = $baris->pemeriksaan_radiologi;
					$key = explode(',',$ambil_db);
					$key_lab = explode(',',$ambil_db_lab);
					$key_radio = explode(',',$ambil_db_radio);
				
							 
							 ?>
                      <!-- END data diri -->
					 
					    <div class="x_title">
                        <h2>Hasil Pemeriksaan</h2>
                        <div class="clearfix"></div>
                      </div>
                      <div class="x_content">

                        <div class="" role="tabpanel" data-example-id="togglable-tabs">
                          <ul id="myTab" class="nav nav-tabs bar_tabs" role="tablist">
                           <?php if($this->session->userdata('akses')=='poliklinik' ) :?>
						   <li role="presentation" class="active"><a href="#tab_content1" id="home-tab" role="tab" data-toggle="tab" aria-expanded="true">Pemeriksaan Fisik dan Kejiwaan</a>
                            </li>
							<?php elseif($this->session->userdata('akses')=='lab' ) :?>
                            <li role="presentation" class=""><a href="#tab_content2" role="tab" id="profile-tab2" data-toggle="tab" aria-expanded="false">Pemeriksaan Laboratorium</a>
                            </li>
							<?php elseif($this->session->userdata('akses')=='radiologi' ) :?>
                            <li role="presentation" class=""><a href="#tab_content3" role="tab" id="profile-tab2" data-toggle="tab" aria-expanded="false">Pemeriksaan Radiologi</a>
                            </li>
							<?php endif ?>
                          </ul>
                          <div id="myTabContent" class="tab-content">
                            <div role="tabpanel" class="tab-pane fade active in" id="tab_content1" aria-labelledby="home-tab">
                              <!-- Pemeriksaan Fisik -->
  <?php if($this->session->userdata('akses')=='poliklinik' ) :?>   
<?php if(in_array("Pemeriksaan fisik",$key)):?>
							 <div class="x_panel">
                              <div class="x_content">
							   <div class="panel panel-primary">
      <div class="panel-heading"><h4><center>Pemeriksaan Fisik</center></h4></div>
      <div class="panel-body">
                          
                              <div class="ln_solid"></div>
                              <div class="col-md-6 col-xs-12">
                              <br/>
                                <div class="form-horizontal form-label-left">
								 <?php echo form_open('index.php/home/update_pemeriksaan_fisik_kejiwaan') ?>
								     <input id="middle-name" value="1" name="status_pasien" class="form-control col-md-7 col-xs-12" type="hidden">
									<div class="form-group">
                                    <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12">ID Pasien</label>
                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                      <input id="middle-name" readonly name="id_pasien" value="<?php echo $baris->id_pasien; ?>" class="form-control col-md-7 col-xs-12" type="text">
                                    </div>
                                 
                                  </div>
								 
						   
						  <div class="form-group">
						  </div>
                                  <div class="form-group">
								  
                                    <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12">Tinggi Badan</label>
                                    <div class="col-md-6 col-sm-6 col-xs-12">
									  
                                      <input id="middle-name" value="<?php echo $baris->tinggi_badan; ?>" name="tinggi_badan" class="form-control col-md-7 col-xs-12" type="text">
                                    </div>
                                    <label for="middle-name" class="control-label col-md-1 col-sm-3 col-xs-12">cm</label>
                                  </div>
                                  <div class="form-group">
                                    <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12">Berat Badan</label>
                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                      <input id="middle-name" value="<?php echo $baris->berat_badan; ?>" name="berat_badan" class="form-control col-md-7 col-xs-12" type="text">
                                    </div>
                                    <label for="middle-name" class="control-label col-md-1 col-sm-3 col-xs-12">kg</label>
                                  </div>
						
                                  <div class="form-group">
                                    <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12">Suhu</label>
                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                      <input id="middle-name" value="<?php echo $baris->suhu; ?>" name="suhu" class="form-control col-md-7 col-xs-12" type="text">
                                    </div>
                                    <label for="middle-name" class="control-label col-md-1 col-sm-3 col-xs-12">⁰C</label>
                                  </div>
					
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Kepala</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                       <table ><tr align="center"><td> <div class="radio">
                               <label>
                            <input type="radio" value= "Normal" <?php echo set_value('kepala', $baris->kepala) == "Normal"? "checked": "";  ?> class="flat" name="kepala"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('kepala', $baris->kepala) == "Abnormal"? "checked": "";  ?> class="flat" name="kepala"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Mata</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                         <table><tr align="center"><td> <div class="radio">
                               <label>
                            <input type="radio" value= "Normal" <?php echo set_value('mata', $baris->mata) == "Normal"? "checked": "";  ?> class="flat" name="mata"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('mata', $baris->mata) == "Abnormal"? "checked": "";  ?> class="flat" name="mata"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Telinga</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                      <table><tr align="center"><td> <div class="radio">
                               <label>
                            <input type="radio" value= "Normal" <?php echo set_value('telinga', $baris->telinga) == "Normal"? "checked": "";  ?> class="flat" name="telinga"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('telinga', $baris->telinga) == "Abnormal"? "checked": "";  ?> class="flat" name="telinga"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Hidung</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                     <table><tr align="center"><td> <div class="radio">
                              <label>
                            <input type="radio" value= "Normal" <?php echo set_value('hidung', $baris->hidung) == "Normal"? "checked": "";  ?> class="flat" name="hidung"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('hidung', $baris->hidung) == "Abnormal"? "checked": "";  ?> class="flat" name="hidung"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Gigi</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                     <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('gigi', $baris->gigi) == "Normal"? "checked": "";  ?> class="flat" name="gigi"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('gigi', $baris->gigi) == "Abnormal"? "checked": "";  ?> class="flat" name="gigi"> Abnormal
                            </label>
                          </div></td></tr></table></div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Tenggorokan</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                     <table><tr align="center"><td> <div class="radio">
                               <label>
                            <input type="radio" value= "Normal" <?php echo set_value('tenggorokan', $baris->tenggorokan) == "Normal"? "checked": "";  ?> class="flat" name="tenggorokan"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('tenggorokan', $baris->tenggorokan) == "Abnormal"? "checked": "";  ?> class="flat" name="tenggorokan"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Leher</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                     <table><tr align="center"><td> <div class="radio">
                               <label>
                            <input type="radio" value= "Normal" <?php echo set_value('leher', $baris->leher) == "Normal"? "checked": "";  ?> class="flat" name="leher"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('leher', $baris->leher) == "Abnormal"? "checked": "";  ?> class="flat" name="leher"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Dada</label>
								   <div class="col-md-9 col-sm-9 col-xs-12">
								    <table><tr align="center"><td> <div class="radio">
                               <label>
                            <input type="radio" value= "Normal" <?php echo set_value('dada', $baris->dada) == "Normal"? "checked": "";  ?> class="flat" name="dada"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('dada', $baris->dada) == "Abnormal"? "checked": "";  ?> class="flat" name="dada"> Abnormal
                            </label>
                          </div></td></tr></table></div>
                                  </div>
								  
								              <div class="form-group">
                                    <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12">Pernafasan</label>
                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                      <input id="middle-name"  value="<?php echo $baris->pernafasan; ?>"  name="pernafasan" class="form-control col-md-7 col-xs-12" type="text">
                                    </div>
                                    <label for="middle-name" class="control-label col-md-1 col-sm-3 col-xs-12">X/Min</label>
                                  </div>
                                  <div class="form-group">
                                    <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12">Tekanan Darah</label>
                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                      <input id="middle-name" name="tekanan_darah"  value="<?php echo $baris->tekanan_darah; ?>" class="form-control col-md-7 col-xs-12" type="text">
                                    </div>
                                    <label for="middle-name" class="control-label col-md-1 col-sm-3 col-xs-12">mm/Hg</label>
                                  </div>
                                  <div class="form-group">
                                    <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12">Nadi</label>
                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                      <input id="middle-name"  value="<?php echo $baris->nadi; ?>" name="nadi" class="form-control col-md-7 col-xs-12" type="text">
                                    </div>
                                    <label for="middle-name" class="control-label col-md-1 col-sm-3 col-xs-12">X/Min</label>
                                  </div>
								
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Paru</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                      <table><tr align="center"><td> <div class="radio">
                              <label>
                            <input type="radio" value= "Normal" <?php echo set_value('paru', $baris->paru) == "Normal"? "checked": "";  ?> class="flat" name="paru"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('paru', $baris->paru) == "Abnormal"? "checked": "";  ?> class="flat" name="paru"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Jantung</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                     <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('jantung', $baris->jantung) == "Normal"? "checked": "";  ?> class="flat" name="jantung"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('jantung', $baris->jantung) == "Abnormal"? "checked": "";  ?> class="flat" name="jantung"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Abdomen</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                     <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('abdomen', $baris->abdomen) == "Normal"? "checked": "";  ?> class="flat" name="abdomen"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('abdomen', $baris->abdomen) == "Abnormal"? "checked": "";  ?> class="flat" name="abdomen"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Anus/Rektum</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                      <table><tr align="center"><td> <div class="radio">
                           <label>
                            <input type="radio" value= "Normal" <?php echo set_value('anus', $baris->anus) == "Normal"? "checked": "";  ?> class="flat" name="anus"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('anus', $baris->anus) == "Abnormal"? "checked": "";  ?> class="flat" name="anus"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-13">Genitalia Ex</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                      <table><tr align="center"><td> <div class="radio">
                               <label>
                            <input type="radio" value= "Normal" <?php echo set_value('genitalia_externa', $baris->genitalia_externa) == "Normal"? "checked": "";  ?> class="flat" name="genitalia_externa"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('genitalia_externa', $baris->genitalia_externa) == "Abnormal"? "checked": "";  ?> class="flat" name="genitalia_externa"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Ekstriminas A</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                      <table><tr align="center"><td> <div class="radio">
                              <label>
                            <input type="radio" value= "Normal" <?php echo set_value('ekstriminas_atas', $baris->ekstriminas_atas) == "Normal"? "checked": "";  ?> class="flat" name="ekstriminas_atas"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('ekstriminas_atas', $baris->ekstriminas_atas) == "Abnormal"? "checked": "";  ?> class="flat" name="ekstriminas_atas"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Ekstrimitas B</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                     <table><tr align="center"><td> <div class="radio">
                              <label>
                            <input type="radio" value= "Normal" <?php echo set_value('ekstrimitas_bawah', $baris->ekstrimitas_bawah) == "Normal"? "checked": "";  ?> class="flat" name="ekstrimitas_bawah"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('ekstrimitas_bawah', $baris->ekstrimitas_bawah) == "Abnormal"? "checked": "";  ?> class="flat" name="ekstrimitas_bawah"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Kelenjar Getah Bening</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                      <table><tr align="center"><td> <div class="radio">
                              <label>
                            <input type="radio" value= "Normal" <?php echo set_value('kelenjar_getah_bening', $baris->kelenjar_getah_bening) == "Normal"? "checked": "";  ?> class="flat" name="kelenjar_getah_bening"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('kelenjar_getah_bening', $baris->kelenjar_getah_bening) == "Abnormal"? "checked": "";  ?> class="flat" name="kelenjar_getah_bening"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
								  <?php endif ?>
								  
								   <?php if(in_array("Pemeriksaan kejiwaan",$key)):?> 
								  				 <div class="panel panel-primary">
      <div class="panel-heading"><h4><center>Pemeriksaan Kejiwaan</center></h4></div>
      <div class="panel-body">
								  
								  
								  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Appearance and speech</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                     <table><tr align="center"><td> <div class="radio">
                              <label>
                            <input type="radio" value= "Normal" <?php echo set_value('appearance_and_speech', $baris->appearance_and_speech) == "Normal"? "checked": "";  ?> class="flat" name="appearance_and_speech"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('appearance_and_speech', $baris->appearance_and_speech) == "Abnormal"? "checked": "";  ?> class="flat" name="appearance_and_speech"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
								  
                            <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Mood/Afek</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                     <table><tr align="center"><td> <div class="radio">
                              <label>
                            <input type="radio" value= "Normal" <?php echo set_value('mood_afek', $baris->mood_afek) == "Normal"? "checked": "";  ?> class="flat" name="mood_afek"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('mood_afek', $baris->mood_afek) == "Abnormal"? "checked": "";  ?> class="flat" name="mood_afek"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
								   <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Thought and Cognitive</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                      <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('thought_and_cognitive', $baris->thought_and_cognitive) == "Normal"? "checked": "";  ?> class="flat" name="thought_and_cognitive"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('thought_and_cognitive', $baris->thought_and_cognitive) == "Abnormal"? "checked": "";  ?> class="flat" name="thought_and_cognitive"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
								   <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Perception Disorder</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                     <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('perception_disorder', $baris->perception_disorder) == "Normal"? "checked": "";  ?> class="flat" name="perception_disorder"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('perception_disorder', $baris->perception_disorder) == "Abnormal"? "checked": "";  ?> class="flat" name="perception_disorder"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
								   <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Impuls Control</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                     <table><tr align="center"><td> <div class="radio">
                              <label>
                            <input type="radio" value= "Normal" <?php echo set_value('impuls_control', $baris->impuls_control) == "Normal"? "checked": "";  ?> class="flat" name="impuls_control"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('impuls_control', $baris->impuls_control) == "Abnormal"? "checked": "";  ?> class="flat" name="impuls_control"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
								    <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Reality Assessment</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                    <table><tr align="center"><td> <div class="radio">
                              <label>
                            <input type="radio" value= "Normal" <?php echo set_value('reality_assessment', $baris->reality_assessment) == "Normal"? "checked": "";  ?> class="flat" name="reality_assessment"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('reality_assessment', $baris->reality_assessment) == "Abnormal"? "checked": "";  ?> class="flat" name="reality_assessment"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div></div></div>
								<?php endif ?>  
						<?php if(in_array("Riwayat penyakit",$key)):?>
                              <div class="x_panel">
                              <div class="x_content">
                                <div class="panel panel-primary">
      <div class="panel-heading"><h4><center>Riwayat Penyakit</center></h4></div>
      <div class="panel-body">
                            
                              <div class="ln_solid"></div>
                              <div class="col-md-6 col-xs-12">
                              <br/>
                                <div class="form-horizontal form-label-left">
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Hipertensi</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                      <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('hipertensi', $baris->hipertensi) == "Normal"? "checked": "";  ?> class="flat" name="hipertensi"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('hipertensi', $baris->hipertensi) == "Abnormal"? "checked": "";  ?> class="flat" name="hipertensi"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Batuk Kronis</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                      <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('batuk_kronis', $baris->batuk_kronis) == "Normal"? "checked": "";  ?> class="flat" name="batuk_kronis"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('batuk_kronis', $baris->batuk_kronis) == "Abnormal"? "checked": "";  ?> class="flat" name="batuk_kronis"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Hipertiroid</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                      <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('hipertiroid', $baris->hipertiroid) == "Normal"? "checked": "";  ?> class="flat" name="hipertiroid"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('hipertiroid', $baris->hipertiroid) == "Abnormal"? "checked": "";  ?> class="flat" name="hipertiroid"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Radang Usus Buntu</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                      <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('radang_usus_buntu', $baris->radang_usus_buntu) == "Normal"? "checked": "";  ?> class="flat" name="radang_usus_buntu"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('radang_usus_buntu', $baris->radang_usus_buntu) == "Abnormal"? "checked": "";  ?> class="flat" name="radang_usus_buntu"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Piouri</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                     <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('piouri', $baris->piouri) == "Normal"? "checked": "";  ?> class="flat" name="piouri"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('piouri', $baris->piouri) == "Abnormal"? "checked": "";  ?> class="flat" name="piouri"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Hematochezia</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
<table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('hematochezia', $baris->hematochezia) == "Normal"? "checked": "";  ?> class="flat" name="hematochezia"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('hematochezia', $baris->hematochezia) == "Abnormal"? "checked": "";  ?> class="flat" name="hematochezia"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Malaria</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                     <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('malaria', $baris->malaria) == "Normal"? "checked": "";  ?> class="flat" name="malaria"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('malaria', $baris->malaria) == "Abnormal"? "checked": "";  ?> class="flat" name="malaria"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Gangguan Kejiwaan</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                     <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('gangguan_kejiwaan', $baris->gangguan_kejiwaan) == "Normal"? "checked": "";  ?> class="flat" name="gangguan_kejiwaan"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('gangguan_kejiwaan', $baris->gangguan_kejiwaan) == "Abnormal"? "checked": "";  ?> class="flat" name="gangguan_kejiwaan"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Stroke</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                      <table><tr align="center"><td> <div class="radio">
                              <label>
                            <input type="radio" value= "Normal" <?php echo set_value('stroke', $baris->stroke) == "Normal"? "checked": "";  ?> class="flat" name="stroke"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('stroke', $baris->stroke) == "Abnormal"? "checked": "";  ?> class="flat" name="stroke"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Hemoptoe</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                      <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('hemoptoe', $baris->hemoptoe) == "Normal"? "checked": "";  ?> class="flat" name="hemoptoe"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('hemoptoe', $baris->hemoptoe) == "Abnormal"? "checked": "";  ?> class="flat" name="hemoptoe"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Diabetes Melitus</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                    <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('diabetes_melitus', $baris->diabetes_melitus) == "Normal"? "checked": "";  ?> class="flat" name="diabetes_melitus"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('diabetes_melitus', $baris->diabetes_melitus) == "Abnormal"? "checked": "";  ?> class="flat" name="diabetes_melitus"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                </div>
                              </div>

                              <div class="col-md-6 col-xs-12">
                              <br/>
                                <div class="form-horizontal form-label-left">
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Hematuria</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
<table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" value= "Normal" <?php echo set_value('hematuria', $baris->hematuria) == "Normal"? "checked": "";  ?> class="flat" name="hematuria"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('hematuria', $baris->hematuria) == "Abnormal"? "checked": "";  ?> class="flat" name="hematuria"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Eksim</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                      <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('eksim', $baris->eksim) == "Normal"? "checked": "";  ?> class="flat" name="eksim"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('eksim', $baris->eksim) == "Abnormal"? "checked": "";  ?> class="flat" name="eksim"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Ambeien</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                     <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('ambeien', $baris->ambeien) == "Normal"? "checked": "";  ?> class="flat" name="ambeien"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('ambeien', $baris->ambeien) == "Abnormal"? "checked": "";  ?> class="flat" name="ambeien"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Epilepsi</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                      <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('epilepsi', $baris->epilepsi) == "Normal"? "checked": "";  ?> class="flat" name="epilepsi"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('epilepsi', $baris->epilepsi) == "Abnormal"? "checked": "";  ?> class="flat" name="epilepsi"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Tumor</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                      <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('tumor', $baris->tumor) == "Normal"? "checked": "";  ?> class="flat" name="tumor"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('tumor', $baris->tumor) == "Abnormal"? "checked": "";  ?> class="flat" name="tumor"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Penyakit Jantung</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                      <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('penyakit_jantung', $baris->penyakit_jantung) == "Normal"? "checked": "";  ?> class="flat" name="penyakit_jantung"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('penyakit_jantung', $baris->penyakit_jantung) == "Abnormal"? "checked": "";  ?> class="flat" name="penyakit_jantung"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Asma</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                     <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('asma', $baris->asma) == "Normal"? "checked": "";  ?> class="flat" name="asma"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('asma', $baris->asma) == "Abnormal"? "checked": "";  ?> class="flat" name="asma"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Radang Perut</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                     <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('radang_perut', $baris->radang_perut) == "Normal"? "checked": "";  ?> class="flat" name="radang_perut"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('radang_perut', $baris->radang_perut) == "Abnormal"? "checked": "";  ?> class="flat" name="radang_perut"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Urolitiasis</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                      <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('urolitiasis', $baris->urolitiasis) == "Normal"? "checked": "";  ?> class="flat" name="urolitiasis"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('urolitiasis', $baris->urolitiasis) == "Abnormal"? "checked": "";  ?> class="flat" name="urolitiasis"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Alergi</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                      <table><tr align="center"><td> <div class="radio">
                              <label>
                            <input type="radio" value= "Normal" <?php echo set_value('alergi', $baris->alergi) == "Normal"? "checked": "";  ?> class="flat" name="alergi"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('alergi', $baris->alergi) == "Abnormal"? "checked": "";  ?> class="flat" name="alergi"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Kusta</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                     <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('kusta', $baris->kusta) == "Normal"? "checked": "";  ?> class="flat" name="kusta"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('kusta', $baris->kusta) == "Abnormal"? "checked": "";  ?> class="flat" name="kusta"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Malignansi</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                     <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('malignansi', $baris->malignansi) == "Normal"? "checked": "";  ?> class="flat" name="malignansi"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('malignansi', $baris->malignansi) == "Abnormal"? "checked": "";  ?> class="flat" name="malignansi"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              </div>
                              </div>
							  </div>
							  </div>
							  <?php endif ?>		
		  
								  
								  
								  
								  </div></div></div></div>
								  </div></div>

		


                              <!-- END Pemeriksaan Fisik -->
                              <!-- Pemeriksaan Kejiwaan -->
							 
							  
                               
                              <!-- END Pemeriksaan Kejiwaan -->
                              <!-- Riwayat Penyakit -->
					
                              <!-- END Riwayat Penyakit -->
                              <!-- Catatan/Keterangan -->
                              <div class="x_panel">
                                <div class="x_title">
                                  <h2>Catataan/Keterangan - Pemeriksaan Fisik dan Kejiwaan</h2>
                                  <div class="clearfix"></div>
                                </div>
                                <div class="x_content">
                                  <div class="form-group">
                                      <textarea class="form-control" rows="3"></textarea>
                                  </div>
                                </div>
                              </div>
                              <!-- END Catatan/Keterangan -->
                            </div>
                             <ul class="nav navbar-right panel_toolbox">
                          <button type="button" class="btn btn-danger">Batal</button>
                          <button type="submit" class="btn btn-primary">Simpan Data pemeriksaan</button>
                        </ul>
								  	<?php echo form_close()?>
                            <div role="tabpanel" class="tab-pane fade" id="tab_content2" aria-labelledby="profile-tab">
                              <!-- Pemeriksaan Laboratorium -->
							  
							  
							    <?php elseif($this->session->userdata('akses')=='lab' ) :?>
                            <?php if(in_array("Darah Lengkap",$key_lab)):?>
				<?php echo form_open('index.php/home/update_lab') ?>
							<div class="x_panel">
                              <div class="x_content">
                              <br/>
   <div class="panel panel-primary">
      <div class="panel-heading"><h4><center>Darah <div class="form-group">
                                  
                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                      <input id="middle-name"  readonly name="id_pasien" value="<?php echo $baris->id_pasien; ?>" class="form-control col-md-7 col-xs-12" type="hidden">
									   <input id="middle-name" value="1" name="status_pasien_lab" class="form-control col-md-7 col-xs-12" type="hidden">
                                    </div></div></center></h4></div>
      <div class="panel-body">
	  
	   
                             
                              <div class="col-md-6 col-xs-12">
                              <br/>
                                <div class="form-horizontal form-label-left">
								
                                  <div class="form-group">
                                    <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12">Golongan Darah</label>
                                  
                                   <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio"  value= "Normal" <?php echo set_value('golongan_darah', $baris->golongan_darah) == "Normal"? "checked": "";  ?> class="flat" name="golongan_darah"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" <?php echo set_value('golongan_darah', $baris->golongan_darah) == "Abnormal"? "checked": "";  ?>  value="Abnormal" class="flat" name="golongan_darah"> Abnormal
                            </label>
                          </div></td></tr></table>
									
                                   
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Kadar Hb</label>
                                      <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" value= "Normal" <?php echo set_value('kadar_hb', $baris->kadar_hb) == "Normal"? "checked": "";  ?> class="flat" name="kadar_hb"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" <?php echo set_value('kadar_hb', $baris->kadar_hb) == "Abnormal"? "checked": "";  ?>  value="Abnormal" class="flat" name="kadar_hb"> Abnormal
                            </label>
                          </div></td></tr></table>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Hitung Trombosit</label>
                                   
                                        <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" value= "Normal" <?php echo set_value('hitung_trombosit', $baris->hitung_trombosit) == "Normal"? "checked": "";  ?> class="flat" name="hitung_trombosit"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" <?php echo set_value('hitung_trombosit', $baris->hitung_trombosit) == "Abnormal"? "checked": "";  ?>  value="Abnormal" class="flat" name="hitung_trombosit"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Hitung Eritosit</label>
                                  
                                       <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" value= "Normal" <?php echo set_value('hitung_eritrosit', $baris->hitung_eritrosit) == "Normal"? "checked": "";  ?> class="flat" name="hitung_eritrosit"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" <?php echo set_value('hitung_eritrosit', $baris->hitung_eritrosit) == "Abnormal"? "checked": "";  ?>  value="Abnormal" class="flat" name="hitung_eritrosit"> Abnormal
                            </label>
                          </div></td></tr></table>
                                   
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Hitung Leukosit</label>
                                    
                                       <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" value= "Normal" <?php echo set_value('hitung_leukosit', $baris->hitung_leukosit) == "Normal"? "checked": "";  ?> class="flat" name="hitung_leukosit"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" <?php echo set_value('hitung_leukosit', $baris->hitung_leukosit) == "Abnormal"? "checked": "";  ?>  value="Abnormal" class="flat" name="hitung_leukosit"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    
                                  </div>
                                </div>
                              </div>

                             </div></div>
 
                         
                              </div></div>
							 <?php endif ?>
							 <?php if(in_array("Pemeriksaan Urine",$key_lab)):?>
                              <div class="x_panel">
                              <div class="x_content">
							    <div class="panel panel-primary">
      <div class="panel-heading"><h4><center>Urin</center></h4></div>
      <div class="panel-body">
	                     <div class="ln_solid"></div>
                              <div class="col-md-6 col-xs-12">
                              <br/>
                                <div class="form-horizontal form-label-left">
								
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Warna, Bau, Kejernihan</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                      <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" value= "Normal" <?php echo set_value('warna_bau_kejernihan', $baris->warna_bau_kejernihan) == "Normal"? "checked": "";  ?> class="flat" name="warna_bau_kejernihan"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('warna_bau_kejernihan', $baris->warna_bau_kejernihan) == "Abnormal"? "checked": "";  ?> class="flat" name="warna_bau_kejernihan"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
								  
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Bilirubin</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                       <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" value= "Normal" <?php echo set_value('bilirubin', $baris->bilirubin) == "Normal"? "checked": "";  ?> class="flat" name="bilirubin"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('bilirubin', $baris->bilirubin) == "Abnormal"? "checked": "";  ?> class="flat" name="bilirubin"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Benda Keton</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                      <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" value= "Normal" <?php echo set_value('benda_keton', $baris->benda_keton) == "Normal"? "checked": "";  ?> class="flat" name="benda_keton"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('benda_keton', $baris->benda_keton) == "Abnormal"? "checked": "";  ?> class="flat" name="benda_keton"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Sedimen</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                       <table><tr align="center"><td> <div class="radio">
                         <label>
                            <input type="radio" value= "Normal" <?php echo set_value('sedimen', $baris->sedimen) == "Normal"? "checked": "";  ?> class="flat" name="sedimen"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('sedimen', $baris->sedimen) == "Abnormal"? "checked": "";  ?> class="flat" name="sedimen"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Glukosa</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                       <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" value= "Normal" <?php echo set_value('glukosa', $baris->glukosa) == "Normal"? "checked": "";  ?> class="flat" name="glukosa"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('glukosa', $baris->glukosa) == "Abnormal"? "checked": "";  ?> class="flat" name="glukosa"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                </div>
                              </div>

                              <div class="col-md-6 col-xs-12">
                              <br/>
                                <div class="form-horizontal form-label-left">
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Berat Jenis</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                       <table><tr align="center"><td> <div class="radio">
                           <label>
                            <input type="radio" value= "Normal" <?php echo set_value('berat_jenis', $baris->berat_jenis) == "Normal"? "checked": "";  ?> class="flat" name="berat_jenis"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('benda_keton', $baris->berat_jenis) == "Abnormal"? "checked": "";  ?> class="flat" name="berat_jenis"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Darah Samar</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                       <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('darah_samar', $baris->darah_samar) == "Normal"? "checked": "";  ?> class="flat" name="darah_samar"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('darah_samar', $baris->darah_samar) == "Abnormal"? "checked": "";  ?> class="flat" name="darah_samar"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Protein</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                        <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('protein', $baris->protein) == "Normal"? "checked": "";  ?> class="flat" name="protein"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('protein', $baris->protein) == "Abnormal"? "checked": "";  ?> class="flat" name="protein"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Urobilinogen</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                      <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('urobilinogen', $baris->urobilinogen) == "Normal"? "checked": "";  ?> class="flat" name="urobilinogen"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('urobilinogen', $baris->urobilinogen) == "Abnormal"? "checked": "";  ?> class="flat" name="urobilinogen"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">pH</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                        <table><tr align="center"><td> <div class="radio">
                              <label>
                            <input type="radio" value= "Normal" <?php echo set_value('ph', $baris->ph) == "Normal"? "checked": "";  ?> class="flat" name="ph"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('ph', $baris->ph) == "Abnormal"? "checked": "";  ?> class="flat" name="ph"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              </div>
                              </div>
<?php endif ?>
	  
	  </div></div>
                              
                            
           
                              <div class="x_panel">
                              <div class="x_content">
                              	    <div class="panel panel-primary">
      <div class="panel-heading"><h4><center>Kimia</center></h4></div>
      <div class="panel-body">
	   <div class="ln_solid"></div>
                              <div class="col-md-6 col-xs-12">
                              <br/>
                                <div class="form-horizontal form-label-left">
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">SGOT</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12"><?php if(in_array("Kimia Klinik - SGOT",$key_lab)):?>
                                      <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" value= "Normal" <?php echo set_value('sgot', $baris->sgot) == "Normal"? "checked": "";  ?> class="flat" name="sgot"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('sgot', $baris->sgot) == "Abnormal"? "checked": "";  ?> class="flat" name="sgot"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php elseif(!in_array("Kimia Klinik - SGOT",$key_lab)):?>
									     <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Normal" class="flat" name="sgot"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Abnormal" class="flat" name="sgot"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php endif ?>
                                    </div>
                                  </div>
                               <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">SGPT</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12"><?php if(in_array("Kimia Klinik - SGPT",$key_lab)):?>
                                        <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('sgpt', $baris->sgpt) == "Normal"? "checked": "";  ?> class="flat" name="sgpt"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('sgpt', $baris->sgpt) == "Abnormal"? "checked": "";  ?> class="flat" name="sgpt"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php elseif(!in_array("Kimia Klinik - SGPT",$key_lab)):?>
									       <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Normal" class="flat" name="sgpt"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Abnormal" class="flat" name="sgpt"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php endif ?>
                                    </div>
                                  </div>
                         <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">HBsAg</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12"><?php if(in_array("Kimia Klinik - HBsAg",$key_lab)):?>
                                       <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" value= "Normal" <?php echo set_value('hbsag', $baris->hbsag) == "Normal"? "checked": "";  ?> class="flat" name="hbsag"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('hbsag', $baris->hbsag) == "Abnormal"? "checked": "";  ?> class="flat" name="hbsag"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php elseif(!in_array("Kimia Klinik - HBsAg",$key_lab)):?>
									     <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Normal" class="flat" name="hbsag"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Abnormal" class="flat" name="hbsag"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php endif ?>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Glukosa Sewaktu</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                       <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('glukosa_sewaktu', $baris->glukosa_sewaktu) == "Normal"? "checked": "";  ?> class="flat" name="glukosa_sewaktu"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('glukosa_sewaktu', $baris->glukosa_sewaktu) == "Abnormal"? "checked": "";  ?> class="flat" name="glukosa_sewaktu"> Abnormal
                            </label>
                          </div></td></tr></table>
                                    </div>
                                  </div>
                                </div>
                              </div>

                              <div class="col-md-6 col-xs-12">
                              <br/>
                                <div class="form-horizontal form-label-left">
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Kreatinin</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12"><?php if(in_array("Kimia Klinik - Kreatinin",$key_lab)):?>
                                       <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" value= "Normal" <?php echo set_value('kreatinin', $baris->kreatinin) == "Normal"? "checked": "";  ?> class="flat" name="kreatinin"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('kreatinin', $baris->kreatinin) == "Abnormal"? "checked": "";  ?> class="flat" name="kreatinin"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php elseif(!in_array("Kimia Klinik - Kreatinin",$key_lab)):?>
									     <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Normal" class="flat" name="kreatinin"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Abnormal" class="flat" name="kreatinin"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php endif ?>
                                    </div>
                                  </div>
                              <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Ureum</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12"><?php if(in_array("Kimia Klinik - Ureum",$key_lab)):?>
                                       <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('ureum', $baris->ureum) == "Normal"? "checked": "";  ?> class="flat" name="ureum"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('ureum', $baris->ureum) == "Abnormal"? "checked": "";  ?> class="flat" name="ureum"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php elseif(!in_array("Kimia Klinik - Ureum",$key_lab)):?>
									      <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Normal" class="flat" name="ureum"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Abnormal" class="flat" name="ureum"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php endif ?>
                                    </div>
                                  </div>
                              <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">HIV</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12"><?php if(in_array("Kimia Klinik - HIV",$key_lab)):?>
                                        <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('hiv', $baris->hiv) == "Normal"? "checked": "";  ?> class="flat" name="hiv"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('hiv', $baris->hiv) == "Abnormal"? "checked": "";  ?> class="flat" name="hiv"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php elseif(!in_array("Kimia Klinik - HIV",$key_lab)):?>
									       <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Normal" class="flat" name="hiv"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Abnormal" class="flat" name="hiv"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php endif ?>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              </div>
                              </div>
	  
	  </div></div>
                             
                             

                              <div class="x_panel">
                              <div class="x_content">
                                	    <div class="panel panel-primary">
      <div class="panel-heading"><h4><center>Sereologi</center></h4></div>
      <div class="panel-body">
	   <div class="ln_solid"></div>
                              <div class="col-md-6 col-xs-12">
                              <br/>
                                <div class="form-horizontal form-label-left">
                               <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">TPHA</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12"><?php if(in_array("Serologi - TPHA",$key_lab)):?>
                                        <table><tr align="center"><td> <div class="radio">
                           <label>
                            <input type="radio" value= "Normal" <?php echo set_value('tpha', $baris->tpha) == "Normal"? "checked": "";  ?> class="flat" name="tpha"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('tpha', $baris->tpha) == "Abnormal"? "checked": "";  ?> class="flat" name="tpha"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php elseif(!in_array("Serologi - TPHA",$key_lab)):?>
									      <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Normal" class="flat" name="tpha"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Abnormal" class="flat" name="tpha"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php endif ?>
                                    </div>
                                  </div>
                                </div>
                              </div>

                              <div class="col-md-6 col-xs-12">
                              <br/>
                                <div class="form-horizontal form-label-left">
                             <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">VDRL</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12"><?php if(in_array("Serologi - VDRL",$key_lab)):?>
                                       <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" value= "Normal" <?php echo set_value('vdrl', $baris->vdrl) == "Normal"? "checked": "";  ?> class="flat" name="vdrl"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('vdrl', $baris->vdrl) == "Abnormal"? "checked": "";  ?> class="flat" name="vdrl"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php elseif(!in_array("Serologi - VDRL",$key_lab)):?>
									     <table><tr align="center"><td> <div class="radio">
                            <label> 
                            <input type="radio" disabled value="Normal" class="flat" name="vdrl"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Abnormal" class="flat" name="vdrl"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php endif ?>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              </div>
                              </div>
	  </div></div>
                            
                             

                              <div class="x_panel">
                              <div class="x_content">
							       	    <div class="panel panel-primary">
      <div class="panel-heading"><h4><center>NAPZA</center></h4></div>
      <div class="panel-body">
	    <div class="ln_solid"></div>
                              <div class="col-md-6 col-xs-12">
                              <br/>
                                <div class="form-horizontal form-label-left">
                                 <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Amphetamine</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12"><?php if(in_array("Tes Narkoba - Amphetamine",$key_lab)):?>
                                        <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('amphetamine', $baris->amphetamine) == "Normal"? "checked": "";  ?> class="flat" name="amphetamine"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('amphetamine', $baris->amphetamine) == "Abnormal"? "checked": "";  ?> class="flat" name="amphetamine"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php elseif(!in_array("Tes Narkoba - Amphetamine",$key_lab)):?>
									     <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Normal" class="flat" name="amphetamine"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Abnormal" class="flat" name="amphetamine"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php endif ?>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Methamphetamine</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12"><?php if(in_array("Tes Narkoba - Methamphetamine",$key_lab)):?>
                                        <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('methamphetamine', $baris->methamphetamine) == "Normal"? "checked": "";  ?> class="flat" name="methamphetamine"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('methamphetamine', $baris->methamphetamine) == "Abnormal"? "checked": "";  ?> class="flat" name="methamphetamine"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php elseif(!in_array("Tes Narkoba - Methamphetamine",$key_lab)):?>
									      <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Normal" class="flat" name="methamphetamine"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Abnormal" class="flat" name="methamphetamine"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php endif ?>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">THC</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12"><?php if(in_array("Tes Narkoba - THC",$key_lab)):?>
                                        <table><tr align="center"><td> <div class="radio">
                              <label>
                            <input type="radio" value= "Normal" <?php echo set_value('thc', $baris->thc) == "Normal"? "checked": "";  ?> class="flat" name="thc"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('thc', $baris->thc) == "Abnormal"? "checked": "";  ?> class="flat" name="thc"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php elseif(!in_array("Tes Narkoba - THC",$key_lab)):?>
									      <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Normal" class="flat" name="thc"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Abnormal" class="flat" name="thc"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php endif ?>
                                    </div>
                                  </div>
                                </div>
                              </div>

                              <div class="col-md-6 col-xs-12">
                              <br/>
                                <div class="form-horizontal form-label-left">
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Morphine</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12"><?php if(in_array("Tes Narkoba - Morphine",$key_lab)):?>
                                        <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('morphine', $baris->morphine) == "Normal"? "checked": "";  ?> class="flat" name="morphine"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('morphine', $baris->morphine) == "Abnormal"? "checked": "";  ?> class="flat" name="morphine"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php elseif(!in_array("Tes Narkoba - Morphine",$key_lab)):?>
									      <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Normal" class="flat" name="morphine"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Abnormal" class="flat" name="morphine"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php endif ?>
                                    </div>
                                  </div>
                                 <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Benzodizapine</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12"><?php if(in_array("Tes Narkoba - Benzodizapine",$key_lab)):?>
                                        <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" value= "Normal" <?php echo set_value('benzodizapine', $baris->benzodizapine) == "Normal"? "checked": "";  ?> class="flat" name="benzodizapine"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('benzodizapine', $baris->benzodizapine) == "Abnormal"? "checked": "";  ?> class="flat" name="benzodizapine"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php elseif(!in_array("Tes Narkoba - Benzodizapine",$key_lab)):?>
									       <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Normal" class="flat" name="benzodizapine"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Abnormal" class="flat" name="benzodizapine"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php endif ?>
                                    </div>
                                  </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Cocain</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12"><?php if(in_array("Tes Narkoba-Cocain",$key_lab)):?>
                                        <table><tr align="center"><td> <div class="radio">
                             <label>
                            <input type="radio" value= "Normal" <?php echo set_value('cocain', $baris->cocain) == "Normal"? "checked": "";  ?> class="flat" name="cocain"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('cocain', $baris->cocain) == "Abnormal"? "checked": "";  ?> class="flat" name="cocain"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php elseif(!in_array("Tes Narkoba-Cocain",$key_lab)):?>
									       <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Normal" class="flat" name="cocain"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Abnormal" class="flat" name="cocain"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php endif ?>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              </div>
                              </div>

	  
                            </div></div>
                              
                            
                              <div class="x_panel">
                              <div class="x_content">
                                 <div class="panel panel-primary">
      <div class="panel-heading"><h4><center>Tes Kehamilan & Feses</center></h4></div>
      <div class="panel-body">
                             
                              <div class="ln_solid"></div>
                              <div class="col-md-6 col-xs-12">
                              <br/>
                                <div class="form-horizontal form-label-left">
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Tes Kehamilan</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12"><?php if(in_array("Tes Kehamilan",$key_lab)):?>
                                       <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" value= "Normal" <?php echo set_value('tes_kehamilan', $baris->tes_kehamilan) == "Normal"? "checked": "";  ?> class="flat" name="tes_kehamilan"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('tes_kehamilan', $baris->tes_kehamilan) == "Abnormal"? "checked": "";  ?> class="flat" name="tes_kehamilan"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php elseif(!in_array("Tes Kehamilan",$key_lab)):?>
									       <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Normal" class="flat" name="tes_kehamilan"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Abnormal" class="flat" name="tes_kehamilan"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php endif ?>
                                    </div>
                                  </div>
                                </div>
                              </div>

                              <div class="col-md-6 col-xs-12">
                              <br/>
                                <div class="form-horizontal form-label-left">
                                 <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Feses</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12"><?php if(in_array("Feses",$key_lab)):?>
                                        <table><tr align="center"><td> <div class="radio">
                           <label>
                            <input type="radio" value= "Normal" <?php echo set_value('feses', $baris->feses) == "Normal"? "checked": "";  ?> class="flat" name="feses"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('feses', $baris->feses) == "Abnormal"? "checked": "";  ?> class="flat" name="feses"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php elseif(!in_array("Feses",$key_lab)):?>
									     <table><tr align="center"><td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Normal" class="flat" name="feses"> Normal
                            </label>
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" disabled value="Abnormal" class="flat" name="feses"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php endif ?>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              </div>
                              </div>
							  </div></div>
                              <!-- END Pemeriksaan Laboratorium -->
                              <!-- Catatan/Keterangan -->
                              <div class="x_panel">
                                <div class="x_title">
                                  <h2>Catataan/Keterangan - Pemeriksaan Laboratorium</h2>
                                  <div class="clearfix"></div>
                                </div>
                                <div class="x_content">
                                  <div class="form-group">
                                      <textarea class="form-control" rows="3"></textarea>
                                  </div>
                                </div>
                              </div>
							
							</div></div>
							
   <ul class="nav navbar-right panel_toolbox">
                          <button type="button" class="btn btn-danger">Batal</button>
                          <button type="submit" class="btn btn-primary">Simpan Data LAB</button>
                        </ul>
                              <!-- END Catatan/Keterangan -->
                            </div>
							
							<?php echo form_close()?>
							
                            <div role="tabpanel" class="tab-pane fade" id="tab_content3" aria-labelledby="profile-tab">
                             
                              <!-- Pemeriksaan Radiologi -->
							    <?php elseif($this->session->userdata('akses')=='radiologi') :?>
								 <?php echo form_open('index.php/home/update_pemeriksaan_radiologi') ?>
                              <div class="x_content">
                              <br/>
                              <center><label><h2>Radiologi</h2></label></center>
                              <div class="ln_solid"></div>
                              <div class="col-md-6 col-xs-12">
                              <br/>
							  
                                <div class="form-horizontal form-label-left">
                                  <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">X-RAY</label>
                                    <div class="col-md-9 col-sm-9 col-xs-12"><?php if(in_array("X-RAY",$key_radio)):?>
                                     <table><tr align="center"><td> <div class="radio">
                           <label>
						   		<input type="hidden" name="id_pasien" value="<?php echo $baris->id_pasien; ?>">
                            <input type="radio" value= "Normal" <?php echo set_value('X_RAY', $baris->X_RAY) == "Normal"? "checked": "";  ?> class="flat" name="X_RAY"> Normal
                            </label>
							 <input id="middle-name" value="1" name="status_pasien_radiologi" class="form-control col-md-7 col-xs-12" type="hidden">
                          </div></td>
                         <td> <div class="radio">
                            <label>
                            <input type="radio" value= "Abnormal" <?php echo set_value('X_RAY', $baris->X_RAY) == "Abnormal"? "checked": "";  ?> class="flat" name="X_RAY"> Abnormal
                            </label>
                          </div></td></tr></table>
									  <?php elseif(!in_array("X-RAY",$key_radio)):?>
									     <select disabled class="select2_group form-control" >
                                          <option value="01">Normal</option>
                                          <option value="00">Abnormal</option>
                                      </select>
									  <?php endif ?>
									   
                                    </div>
                                  </div>
                                </div>
                              </div>
                              </div>
                             
                              <!-- END Pemeriksaan Radiologi -->
							 
                              <!-- Catatan/Keterangan -->
                              <div class="x_panel">
                                <div class="x_title">
                                  <h2>Catataan/Keterangan - Pemeriksaan Radiologi</h2>
								  <input id="middle-name" value="1" name="status_pasien_radiologi" class="form-control col-md-7 col-xs-12" type="hidden">
                                  <div class="clearfix"></div>
                                </div>
                                <div class="x_content">
                                  <div class="form-group">
                                      <textarea class="form-control" rows="3"></textarea>
                                  </div>
                                </div> <ul class="nav navbar-right panel_toolbox">
                          <button type="button" class="btn btn-danger">Batal</button>
                          <button type="submit" class="btn btn-primary">Simpan Data radiologi</button>
                        </ul>
						 <?php echo form_close()?>
                              </div>
							  
                              <!-- END Catatan/Keterangan -->
					 <?php endif ?>
					
					 </div>
					 
					
					 
					 
					 </div>
					 
					 <!------ ---------------------------------------------------------->
					 
					 
				
                      <div class="x_title">
                        <h2>Permintaan Pemeriksaan</h2>
                        <div class="clearfix"></div>
                      </div>
                      <!-- pemeriksaan fisik -->
                      <div class="x_content">
                        <br/>
                        <div class="form-horizontal form-label-left">
                          <div class="form-group">
                            <label class="col-md-3 col-sm-3 col-xs-12 control-label">Pemeriksaan Fisik dan Kejiwaan
                            </label>
							
						  							 
                            <div class="col-md-4 col-sm-9 col-xs-30">
                                   <div class="checkbox">
							  
                                <label>  <?php if(in_array("Pemeriksaan fisik",$key)):?>
                                     <input type="checkbox" name="check_list[]" class="flat" value="<?php if(in_array("Pemeriksaan fisik",$key))
								  { ?>  <?php ?>" checked <?php }?>> Pemeriksaan Fisik
                                </label>
								<label>  <?php else : ?>
                                     <input type="checkbox" name="check_list[]" class="flat" value=""> Pemeriksaan Fisik
                                </label>
								<?php endif ?>
                              </div>
                              <div class="checkbox">
							  
                                <label>  <?php if(in_array("Pemeriksaan kejiwaan",$key)):?>
                                     <input type="checkbox" name="check_list[]" class="flat" value="<?php if(in_array("Pemeriksaan kejiwaan",$key))
								  { ?>  <?php ?>" checked <?php }?>> Pemeriksaan Kejiwaan
                                </label>
								<label>  <?php else : ?>
                                     <input type="checkbox" name="check_list[]" class="flat" value=""> Pemeriksaan Kejiwaan
                                </label>
								<?php endif ?>
                              </div>
							  
                         <div class="checkbox">
							  
                                <label>  <?php if(in_array("Riwayat penyakit",$key)):?>
                                     <input type="checkbox" name="check_list[]" class="flat" value="<?php if(in_array("Riwayat penyakit",$key))
								  { ?>  <?php ?>" checked <?php }?>> Riwayat Penyakit
                                </label>
								<label>  <?php else : ?>
                                     <input type="checkbox" name="check_list[]" class="flat" value=""> Riwayat Penyakit
                                </label>
								<?php endif ?>
                              </div>
							  
                            </div>
	
                                 </br>
                           
                          
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
							  
                                <label>  <?php if(in_array("Darah Lengkap",$key_lab)):?>
                                     <input type="checkbox" name="check_list[]" class="flat" value="<?php if(in_array("Darah Lengkap",$key_lab))
								  { ?>  <?php ?>" checked <?php }?>> Darah Lengkap
                                </label>
								<label>  <?php else : ?>
                                     <input type="checkbox" name="check_list[]" class="flat" value=""> Darah Lengkap
                                </label>
								<?php endif ?>
                              </div>
                                    <div class="checkbox">
							  
                                <label>  <?php if(in_array("Pemeriksaan Urine",$key_lab)):?>
                                     <input type="checkbox" name="check_list[]" class="flat" value="<?php if(in_array("Pemeriksaan Urine",$key_lab))
								  { ?>  <?php ?>" checked <?php }?>> Pemeriksaan Urine
                                </label>
								<label>  <?php else : ?>
                                     <input type="checkbox" name="check_list[]" class="flat" value=""> Pemeriksaan Urine
                                </label>
								<?php endif ?>
                              </div>
                                   <div class="checkbox">
							  
                                <label>  <?php if(in_array("Kimia Klinik - SGOT",$key_lab)):?>
                                     <input type="checkbox" name="check_list[]" class="flat" value="<?php if(in_array("Kimia Klinik - SGOT",$key_lab))
								  { ?>  <?php ?>" checked <?php }?>> Kimia Klinik - SGOT
                                </label>
								<label>  <?php else : ?>
                                     <input type="checkbox" name="check_list[]" class="flat" value=""> Kimia Klinik - SGOT
                                </label>
								<?php endif ?>
                              </div>
                             <div class="checkbox">
							  
                                <label>  <?php if(in_array("Kimia Klinik - SGPT",$key_lab)):?>
                                     <input type="checkbox" name="check_list[]" class="flat" value="<?php if(in_array("Kimia Klinik - SGPT",$key_lab))
								  { ?>  <?php ?>" checked <?php }?>> Kimia Klinik - SGPT
                                </label>
								<label>  <?php else : ?>
                                     <input type="checkbox" name="check_list[]" class="flat" value=""> Kimia Klinik - SGPT
                                </label>
								<?php endif ?>
                              </div>
                             <div class="checkbox">
							  
                                <label>  <?php if(in_array("Kimia Klinik - Ureum",$key_lab)):?>
                                     <input type="checkbox" name="check_list[]" class="flat" value="<?php if(in_array("Kimia Klinik - Ureum",$key_lab))
								  { ?>  <?php ?>" checked <?php }?>> Kimia Klinik - Ureum
                                </label>
								<label>  <?php else : ?>
                                     <input type="checkbox" name="check_list[]" class="flat" value=""> Kimia Klinik - Ureum
                                </label>
								<?php endif ?>
                              </div>
                              <div class="checkbox">
							  
                                <label>  <?php if(in_array("Kimia Klinik - Kreatinin",$key_lab)):?>
                                     <input type="checkbox" name="check_list[]" class="flat" value="<?php if(in_array("Kimia Klinik - Kreatinin",$key_lab))
								  { ?>  <?php ?>" checked <?php }?>> Kimia Klinik - Kreatinin
                                </label>
								<label>  <?php else : ?>
                                     <input type="checkbox" name="check_list[]" class="flat" value=""> Kimia Klinik - Kreatinin
                                </label>
								<?php endif ?>
                              </div>
                               <div class="checkbox">
							  
                                <label>  <?php if(in_array("Kimia Klinik - Gula Darah Sesaat",$key_lab)):?>
                                     <input type="checkbox" name="check_list[]" class="flat" value="<?php if(in_array("Kimia Klinik - Gula Darah Sesaat",$key_lab))
								  { ?>  <?php ?>" checked <?php }?>> Kimia Klinik - Gula Darah Sesaat
                                </label>
								<label>  <?php else : ?>
                                     <input type="checkbox" name="check_list[]" class="flat" value=""> Kimia Klinik - Gula Darah Sesaat
                                </label>
								<?php endif ?>
                              </div>
                              <div class="checkbox">
							  
                                <label>  <?php if(in_array("Kimia Klinik - HBsAg",$key_lab)):?>
                                     <input type="checkbox" name="check_list[]" class="flat" value="<?php if(in_array("Kimia Klinik - HBsAg",$key_lab))
								  { ?>  <?php ?>" checked <?php }?>> Kimia Klinik - HBsAg
                                </label>
								<label>  <?php else : ?>
                                     <input type="checkbox" name="check_list[]" class="flat" value=""> Kimia Klinik - HBsAg
                                </label>
								<?php endif ?>
                              </div>
                             <div class="checkbox">
							  
                                <label>  <?php if(in_array("Kimia Klinik - HIV",$key_lab)):?>
                                     <input type="checkbox" name="check_list[]" class="flat" value="<?php if(in_array("Kimia Klinik - HIV",$key_lab))
								  { ?>  <?php ?>" checked <?php }?>> Kimia Klinik - HIV
                                </label>
								<label>  <?php else : ?>
                                     <input type="checkbox" name="check_list[]" class="flat" value=""> Kimia Klinik - HIV
                                </label>
								<?php endif ?>
                              </div>
                              <div class="checkbox">
							  
                                <label>  <?php if(in_array("Tes Kehamilan",$key_lab)):?>
                                     <input type="checkbox" name="check_list[]" class="flat" value="<?php if(in_array("Tes Kehamilan",$key_lab))
								  { ?>  <?php ?>" checked <?php }?>> Tes Kehamilan
                                </label>
								<label>  <?php else : ?>
                                     <input type="checkbox" name="check_list[]" class="flat" value=""> Tes Kehamilan
                                </label>
								<?php endif ?>
                              </div>
                              <div class="checkbox">
							  
                                <label>  <?php if(in_array("Feses",$key_lab)):?>
                                     <input type="checkbox" name="check_list[]" class="flat" value="<?php if(in_array("Feses",$key_lab))
								  { ?>  <?php ?>" checked <?php }?>> Feses
                                </label>
								<label>  <?php else : ?>
                                     <input type="checkbox" name="check_list[]" class="flat" value=""> Feses
                                </label>
								<?php endif ?>
                              </div>
                            </div>

                            <div class="col-md-3 col-sm-9 col-xs-12">
                               <div class="checkbox">
							  
                                <label>  <?php if(in_array("Serologi - TPHA",$key_lab)):?>
                                     <input type="checkbox" name="check_list[]" class="flat" value="<?php if(in_array("Serologi - TPHA",$key_lab))
								  { ?>  <?php ?>" checked <?php }?>> Serologi - TPHA
                                </label>
								<label>  <?php else : ?>
                                     <input type="checkbox" name="check_list[]" class="flat" value=""> Serologi - TPHA
                                </label>
								<?php endif ?>
                              </div>
                            <div class="checkbox">
							  
                                <label>  <?php if(in_array("Serologi - VDRL",$key_lab)):?>
                                     <input type="checkbox" name="check_list[]" class="flat" value="<?php if(in_array("Serologi - VDRL",$key_lab))
								  { ?>  <?php ?>" checked <?php }?>> Serologi - VDRL
                                </label>
								<label>  <?php else : ?>
                                     <input type="checkbox" name="check_list[]" class="flat" value=""> Serologi - VDRL
                                </label>
								<?php endif ?>
                              </div>
                              <div class="checkbox">
							  
                                <label>  <?php if(in_array("Tes Narkoba - Amphetamine",$key_lab)):?>
                                     <input type="checkbox" name="check_list[]" class="flat" value="<?php if(in_array("Tes Narkoba - Amphetamine",$key_lab))
								  { ?>  <?php ?>" checked <?php }?>> Tes Narkoba - Amphetamin
                                </label>
								<label>  <?php else : ?>
                                     <input type="checkbox" name="check_list[]" class="flat" value=""> Tes Narkoba - Amphetamin
                                </label>
								<?php endif ?>
                              </div>
                                  <div class="checkbox">
							  
                                <label>  <?php if(in_array("Tes Narkoba - Methamphetamine",$key_lab)):?>
                                     <input type="checkbox" name="check_list[]" class="flat" value="<?php if(in_array("Tes Narkoba - Methamphetamine",$key_lab))
								  { ?>  <?php ?>" checked <?php }?>> Tes Narkoba - Methamphetamine
                                </label>
								<label>  <?php else : ?>
                                     <input type="checkbox" name="check_list[]" class="flat" value=""> Tes Narkoba - Methamphetamine
                                </label>
								<?php endif ?>
                              </div>
							        <div class="checkbox">
							  
                                <label>  <?php if(in_array("Tes Narkoba - THC",$key_lab)):?>
                                     <input type="checkbox" name="check_list[]" class="flat" value="<?php if(in_array("Tes Narkoba - THC",$key_lab))
								  { ?>  <?php ?>" checked <?php }?>> Tes Narkoba - THC
                                </label>
								<label>  <?php else : ?>
                                     <input type="checkbox" name="check_list[]" class="flat" value=""> Tes Narkoba - THC
                                </label>
								<?php endif ?>
                              </div>
                                   <div class="checkbox">
							  
                                <label>  <?php if(in_array("Tes Narkoba - Morphine",$key_lab)):?>
                                     <input type="checkbox" name="check_list[]" class="flat" value="<?php if(in_array("Tes Narkoba - Morphine",$key_lab))
								  { ?>  <?php ?>" checked <?php }?>> Tes Narkoba - Morphine
                                </label>
								<label>  <?php else : ?>
                                     <input type="checkbox" name="check_list[]" class="flat" value=""> Tes Narkoba - Morphine
                                </label>
								<?php endif ?>
                              </div>
                                    <div class="checkbox">
							  
                                <label>  <?php if(in_array("Tes Narkoba - Benzodizapine",$key_lab)):?>
                                     <input type="checkbox" name="check_list[]" class="flat" value="<?php if(in_array("Tes Narkoba - Benzodizapine",$key_lab))
								  { ?>  <?php ?>" checked <?php }?>> Tes Narkoba - Benzodizapine 
                                </label>
								<label>  <?php else : ?>
                                     <input type="checkbox" name="check_list[]" class="flat" value=""> Tes Narkoba - Benzodizapine 
                                </label>
								<?php endif ?>
                              </div>
                                <div class="checkbox">
							  
                                <label>  <?php if(in_array("Tes Narkoba-Cocain",$key_lab)):?>
                                     <input type="checkbox" name="check_list[]" class="flat" value="<?php if(in_array("Tes Narkoba-Cocain",$key_lab))
								  { ?>  <?php ?>" checked <?php }?>> Tes Narkoba - Cocain 
                                </label>
								<label>  <?php else : ?>
                                     <input type="checkbox" name="check_list[]" class="flat" value=""> Tes Narkoba-Cocain
                                </label>
								<?php endif ?>
                              </div>
                            </div>

                          </div> <?php if($this->session->userdata('akses')=='lab'):?>
						  	       <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Hasil Pemeriksaan</label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <div id="gender" class="btn-group" data-toggle="buttons">
                                <label class="btn btn-default btn-xs" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                  <input type="radio" value="<?php echo $baris->jenis_kelamin; ?>" name="jenis_kelamin" value="Normal"> &nbsp; NORMAL &nbsp;
                                </label>
                                <label class="btn btn-default btn-xs" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                  <input type="radio"  name="jenis_kelamin" value="<?php echo $baris->jenis_kelamin; ?>" value="Ubnormal"> UBNORMAL
                                </label>
                              </div>
                            </div>
                          </div>
						  <?php endif; ?>
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
                            <div class="col-md-3 col-sm-9 col-xs-12">
                                    <div class="checkbox">
							  
                                <label>  <?php if(in_array("X-RAY",$key_radio)):?>
                                     <input type="checkbox" name="check_list[]" class="flat" value="<?php if(in_array("X-RAY",$key_radio))
								  { ?>  <?php ?>" checked <?php }?>> X-RAY
                                </label>
								<label>  <?php else : ?>
                                     <input type="checkbox" name="check_list[]" class="flat" value=""> X-RAY 
                                </label>
								<?php endif ?>
                              </div>
                            </div>
							
                          </div>
                        </div>
                          <div class="ln_solid"></div>
						  
                      </div>
                      <!-- END pemeriksaan laboratorium -->
                       
						
                    </div>
                  </div>
                </div>
      </div>
	  <?php }

?>
	   <?php endif;?>
	   
<!-- /page content -->
        <!-- /page content -->
                      </div>

                    </div>




            
</div></div>
        </div>
        <!-- /page content -->

        <!-- /page content -->

        <!-- footer content -->
      
        <!-- /footer content -->
      </div>
    </div>
        <!-- footer content -->
        <footer>
          <div class="pull-right">
            Gentelella - Bootstrap Admin Template by <a href="https://colorlib.com">Colorlib</a>
          </div>
          <div class="clearfix"></div>
        </footer>
        <!-- /footer content -->
    <!-- jQuery -->
    <script src="<?php echo base_url()."assets";?>/vendors/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="<?php echo base_url()."assets";?>/vendors/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- FastClick -->
    <script src="<?php echo base_url()."assets";?>/vendors/fastclick/lib/fastclick.js"></script>
    <!-- NProgress -->
    <script src="<?php echo base_url()."assets";?>/vendors/nprogress/nprogress.js"></script>
    <!-- Chart.js -->
    <script src="<?php echo base_url()."assets";?>/vendors/Chart.js/dist/Chart.min.js"></script>
    <!-- gauge.js -->
    <script src="<?php echo base_url()."assets";?>/vendors/gauge.js/dist/gauge.min.js"></script>
    <!-- bootstrap-progressbar -->
    <script src="<?php echo base_url()."assets";?>/vendors/bootstrap-progressbar/bootstrap-progressbar.min.js"></script>
    <!-- iCheck -->
    <script src="<?php echo base_url()."assets";?>/vendors/iCheck/icheck.min.js"></script>
    <!-- Skycons -->
    <script src="<?php echo base_url()."assets";?>/vendors/skycons/skycons.js"></script>
    <!-- Flot -->
    <script src="<?php echo base_url()."assets";?>/vendors/Flot/jquery.flot.js"></script>
    <script src="<?php echo base_url()."assets";?>/vendors/Flot/jquery.flot.pie.js"></script>
    <script src="<?php echo base_url()."assets";?>/vendors/Flot/jquery.flot.time.js"></script>
    <script src="<?php echo base_url()."assets";?>/vendors/Flot/jquery.flot.stack.js"></script>
    <script src="<?php echo base_url()."assets";?>/vendors/Flot/jquery.flot.resize.js"></script>
    <!-- Flot plugins -->
    <script src="<?php echo base_url()."assets";?>/vendors/flot.orderbars/js/jquery.flot.orderBars.js"></script>
    <script src="<?php echo base_url()."assets";?>/vendors/flot-spline/js/jquery.flot.spline.min.js"></script>
    <script src="<?php echo base_url()."assets";?>/vendors/flot.curvedlines/curvedLines.js"></script>
    <!-- DateJS -->
    <script src="<?php echo base_url()."assets";?>/vendors/DateJS/build/date.js"></script>
    <!-- JQVMap -->
    <script src="<?php echo base_url()."assets";?>/vendors/jqvmap/dist/jquery.vmap.js"></script>
    <script src="<?php echo base_url()."assets";?>/vendors/jqvmap/dist/maps/jquery.vmap.world.js"></script>
    <script src="<?php echo base_url()."assets";?>/vendors/jqvmap/examples/js/jquery.vmap.sampledata.js"></script>
    <!-- bootstrap-daterangepicker -->
    <script src="<?php echo base_url()."assets";?>/vendors/moment/min/moment.min.js"></script>
    <script src="<?php echo base_url()."assets";?>/vendors/bootstrap-daterangepicker/daterangepicker.js"></script>

    <!-- bootstrap-wysiwyg -->
    <script src="<?php echo base_url()."assets";?>/vendors/bootstrap-wysiwyg/js/bootstrap-wysiwyg.min.js"></script>
    <script src="<?php echo base_url()."assets";?>/vendors/jquery.hotkeys/jquery.hotkeys.js"></script>
    <script src="<?php echo base_url()."assets";?>/vendors/google-code-prettify/src/prettify.js"></script>
    <!-- jQuery Tags Input -->
    <script src="<?php echo base_url()."assets";?>/vendors/jquery.tagsinput/src/jquery.tagsinput.js"></script>
    <!-- Switchery -->
    <script src="<?php echo base_url()."assets";?>/vendors/switchery/dist/switchery.min.js"></script>
    <!-- Select2 -->
    <script src="<?php echo base_url()."assets";?>/vendors/select2/dist/js/select2.full.min.js"></script>
    <!-- Parsley -->
    <script src="<?php echo base_url()."assets";?>/vendors/parsleyjs/dist/parsley.min.js"></script>
    <!-- Autosize -->
    <script src="<?php echo base_url()."assets";?>/vendors/autosize/dist/autosize.min.js"></script>
    <!-- jQuery autocomplete -->
    <script src="<?php echo base_url()."assets";?>/vendors/devbridge-autocomplete/dist/jquery.autocomplete.min.js"></script>
    <!-- starrr -->
    <script src="<?php echo base_url()."assets";?>/vendors/starrr/dist/starrr.js"></script>

        <!-- Datatables -->
    <script src="<?php echo base_url()."assets";?>/vendors/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="<?php echo base_url()."assets";?>/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script src="<?php echo base_url()."assets";?>/vendors/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="<?php echo base_url()."assets";?>/vendors/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>
    <script src="<?php echo base_url()."assets";?>/vendors/datatables.net-buttons/js/buttons.flash.min.js"></script>
    <script src="<?php echo base_url()."assets";?>/vendors/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="<?php echo base_url()."assets";?>/vendors/datatables.net-buttons/js/buttons.print.min.js"></script>
    <script src="<?php echo base_url()."assets";?>/vendors/datatables.net-fixedheader/js/dataTables.fixedHeader.min.js"></script>
    <script src="<?php echo base_url()."assets";?>/vendors/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
    <script src="<?php echo base_url()."assets";?>/vendors/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script src="<?php echo base_url()."assets";?>/vendors/datatables.net-responsive-bs/js/responsive.bootstrap.js"></script>
    <script src="<?php echo base_url()."assets";?>/vendors/datatables.net-scroller/js/dataTables.scroller.min.js"></script>
    <script src="<?php echo base_url()."assets";?>/vendors/jszip/dist/jszip.min.js"></script>
    <script src="<?php echo base_url()."assets";?>/vendors/pdfmake/build/pdfmake.min.js"></script>
    <script src="<?php echo base_url()."assets";?>/vendors/pdfmake/build/vfs_fonts.js"></script>

    <!-- Custom Theme Scripts -->
    <script src="<?php echo base_url()."assets";?>/build/js/custom.min.js"></script>
	
  </body>
</html>
<?php if (isset($num_results)) {
  echo ''. $num_results .'';} ?>