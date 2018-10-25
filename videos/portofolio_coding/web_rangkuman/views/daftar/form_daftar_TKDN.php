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
                  <h1>Formulir Pendaftaran</h1>
                  <h2>Pasien Pemeriksaan Kesehatan Tenaga Kerja Dalam Negeri</h2>
                  <div class="clearfix"></div>
                </div>
              <div class="clearfix"></div>
                <div class="row">
                  <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="x_panel">
                      <div class="x_title">
                        <h2>Data Diri Pasien</h2>
                          <div class="clearfix"></div>
                      </div>
                      <!-- data diri -->
                      <div class="x_content">
                      <div class="col-md-6 col-xs-12">
                      <br/>
                        <div class="form-horizontal form-label-left">
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Nama Lengkap</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                              <input type="text" class="form-control">
                            </div>
                          </div>
                          <div class="form-group">
                            <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12">Tgl Lahir / Umur</label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input id="middle-name" class="form-control col-md-7 col-xs-12" type="date">
                            </div>
                            <div class="col-md-2 col-sm-6 col-xs-12">
                              <input id="middle-name" class="form-control col-md-7 col-xs-12" type="text">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Jenis Kelamin</label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <div id="gender" class="btn-group" data-toggle="buttons">
                                <label class="btn btn-default" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                  <input type="radio" name="gender" value="male"> &nbsp; Laki-laki &nbsp;
                                </label>
                                <label class="btn btn-primary" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                  <input type="radio" name="gender" value="female"> Perempuan
                                </label>
                              </div>
                            </div>
                          </div>
                          <div class="form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12">Alamat Pasien</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                              <textarea class="form-control" rows="3"></textarea>
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
                              <input type="text" class="form-control">
                            </div>
                          </div>

                          <div class="form-group">
                            <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12">Tanggal Masuk</label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input id="middle-name" class="form-control col-md-7 col-xs-12" type="date">
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Pemeriksaan</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                              <input type="text" class="form-control" placeholder="Tenaga Kerja Dalam Negeri">
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Perusahaan</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                              <select class="select2_group form-control">
                                  <option value="TWN">PT. INTRIAS MANDIRI SEJATI</option>
                                  <option value="HKG">PT. KEBUN AGUNG</option>
                                  <option value="SGP">PT. ADIRAMA</option>
                                  <option value="MLY">PT. MALANG KUDUS</option>
                                  <option value="BRD">PT. AHMAJA ADI</option>
                                  <option value="TRT">PT. SULIMATDJA</option>
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

                         

                        </div>
                      </div>

                      </div>
                      <!-- END data diri -->
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
                              <br>
                              <small class="text-navy">Normal Bootstrap elements</small>
                            </label>
                            <div class="col-md-3 col-sm-9 col-xs-12">
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" class="flat"> Pemeriksaan Fisik
                                </label>
                              </div>
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" class="flat"> Pemeriksaan Kejiwaan
                                </label>
                              </div>
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" class="flat"> Vaksin Trimovax
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
                              <br>
                              <small class="text-navy">Normal Bootstrap elements</small>
                            </label>
                            <div class="col-md-3 col-sm-9 col-xs-12">
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" class="flat"> Pemeriksaan Plano
                                </label>
                              </div>
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" class="flat"> Pemeriksaan Urine
                                </label>
                              </div>
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" class="flat"> Pemeriksaan Hbs Ag
                                </label>
                              </div>
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" class="flat"> Pemeriksaan VDRL
                                </label>
                              </div>
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" class="flat"> Pemeriksaan TPHA
                                </label>
                              </div>
                            </div>

                            <div class="col-md-3 col-sm-9 col-xs-12">
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" class="flat"> Pemeriksaan Morphine
                                </label>
                              </div>
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" class="flat"> Pemeriksaan Amphetamine
                                </label>
                              </div>
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" class="flat"> Pemeriksaan Mariyuana
                                </label>
                              </div>
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" class="flat"> Pemeriksaan Malaria
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
                              <br>
                              <small class="text-navy">Normal Bootstrap elements</small>
                            </label>
                            <div class="col-md-3 col-sm-9 col-xs-12">
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" class="flat"> Pemeriksaan Thorax
                                </label>
                              </div>
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" class="flat"> Pemeriksaan Kreatinin
                                </label>
                              </div>
                            </div>
                          </div>
                        </div>
                          <div class="ln_solid"></div>
                      </div>
                      <!-- END pemeriksaan laboratorium -->
                        <ul class="nav navbar-right panel_toolbox">
                          <button type="button" class="btn btn-danger">Batal</button>
                          <button type="button" class="btn btn-primary">Simpan</button>
                        </ul>
                    </div>
                  </div>
                </div>
      </div>
<!-- /page content -->