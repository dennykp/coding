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
<style>
#notifications {
    cursor: pointer;
    position: fixed;
    right: 0px;
    z-index: 9999;
    bottom: 2px;
    margin-bottom: 0px;
    margin-right: 15px;
    min-width: 300px; 
    max-width: 800px;  
}
</style>
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
		function hanyaAngka(evt) {
		  var charCode = (evt.which) ? evt.which : event.keyCode
		   if (charCode > 31 && (charCode < 48 || charCode > 57))
 
		    return false;
		  return true;
		}
	</script>
  </head>

 <body class="nav-md">
 
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
                  <li><a href="<?php echo base_url()."index.php//login/data_user"; ?>"><i class="fa fa-users"></i> Data Akun Pengguna </a>
                  </li>
				   <?php elseif($this->session->userdata('akses')=='poliklinik'):?>
				   <li><a href="<?php echo base_url()."index.php/home"; ?>"><i class="fa fa-home"></i> Home </a>
                  </li>
				   <li><a href="<?php echo base_url()."index.php/data_pasien_harian/pasien_harian"; ?>"><i class="fa fa-table"></i> Daftar Pasien Harian </a>
                  </li>
				  <?php elseif($this->session->userdata('akses')=='lab' || $this->session->userdata('akses')=='radiologi'):?>
				  <li><a href="<?php echo base_url()."index.php/home"; ?>"><i class="fa fa-home"></i> Home </a></li>
				    <li><a href="<?php echo base_url()."index.php/data_pasien_harian"; ?>"><i class="fa fa-table"></i> Daftar Pasien Harian </a>
                  </li>
				     <?php elseif($this->session->userdata('akses')=='frontoffice'):?>
					<li><a href="<?php echo base_url()."index.php/home"; ?>"><i class="fa fa-home"></i> Home </a>
                  </li>
				   <li><a href="<?php echo base_url()."index.php/data_pasien_harian/pasien_harian"; ?>"><i class="fa fa-table"></i> Daftar Pasien Harian </a>
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
                  <h2>Pasien Pemeriksaan Kesehatan Tenaga Kerja Luar Negeri</h2>
				
				   <h1>Pemeriksaan LAB</h1>
                  <h2>Pasien Pemeriksaan Kesehatan Tenaga Kerja Luar Negeri</h2>
				  
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
					  			   	<div id="notifications">
<?php echo $this->session->flashdata('message'); ?>
</div>
                      <div class="col-md-6 col-xs-12">
                      <br/>
					   <form class="card" action= "<?php echo base_url('index.php/home/update_profil');?>" method ="POST" class="form-group">
                        <div class="form-horizontal form-label-left">
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">ID Akun</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                              <input type="text" readonly name="id" value="<?php echo $this->session->userdata('ses_id');?>" class="form-control">
                            </div>
                          </div>
                            <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Nama Lengkap</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                              <input type="text" name="nama" value="<?php echo $this->session->userdata('ses_nama');?>" class="form-control">
                            </div>
                          </div>
                    
                          <div class="form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12">Alamat</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                            <input type="text" name="alamat" value="<?php echo $this->session->userdata('ses_alamat');?>" class="form-control">
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
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Username</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                              <input type="text" name="username" value="<?php echo $this->session->userdata('ses_username');?>" class="form-control">
                            </div>
                          </div>
   <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Password</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                              <input type="text" name="password" value="<?php echo $this->session->userdata('ses_password');?>" class="form-control">
                            </div>
                          </div>
                        
                   
                      
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Status</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                              <input name="status" readonly value="<?php echo $this->session->userdata('ses_status');?>" type="text" onkeypress="return hanyaAngka(event)" class="form-control">
                            </div>
                          </div>
                       

                        </div>
                      </div>

                      </div>
					   </div>
                      <!-- END data diri -->
				
                          <div class="ln_solid"></div>
						  
                      </div>
					  
                      <!-- END pemeriksaan laboratorium -->
                         <ul class="nav navbar-right panel_toolbox">
                          <button type="button" class="btn btn-danger">Batal</button>
                          <button type="submit" class="btn btn-primary">Simpan</button>
                        </ul>
						
					<?php echo form_close()?>
                    </div>
                  </div>
                </div>
      </div>

<!-- /page content -->