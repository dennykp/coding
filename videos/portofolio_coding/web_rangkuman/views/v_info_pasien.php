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
                    <li><a href="<?php echo base_url().'index.php/home/index_profil'?>">Pengaturan Profile</a></li>
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
<div class="right_col" role="main">
          <div class="">
            <div class="row top_tiles">
            
             
			   <div class="row top_tiles">
                 
			
			   <div class="animated flipInY col-lg-3 col-md-3 col-sm-6 col-xs-12">
                <div class="tile-stats">
                  <div class="icon"><i class="fa fa-sort-amount-desc"></i></div>
                  <div class="count"><?php if (isset($num_results_tkln)) {
  echo "Total $num_results_tkln" ;
} ?></div>
                  <h3>TKLN</h3>
                  <p>Pasien Teregister</br>TKLN.</p>
                </div>
              </div>
              <div class="animated flipInY col-lg-3 col-md-3 col-sm-6 col-xs-12">
                <div class="tile-stats">
                  <div class="icon"><i class="fa fa-check-square-o"></i></div>
                  <div class="count"><?php if (isset($num_results)) {
  echo "Total $num_results";
} ?></div>
                  <h3>TKDN</h3>
                  <p>Pasien Teregister</br>TKDN.</p>
                </div>
              </div>
            </div>
            </div>
			</div>
    
         
           
			
  
            <div class="row">
			
              <div class="col-md-12">
                <div class="x_panel">
				
                  <div class="x_title">

                    <h2>Detail Data </h2>
             </br></br>
			 
	
                        </div>
						 <div class="row">
              <div class="col-md-12">
                <div class="x_panel">
                  <div class="x_title">

				

                 
                    <table id="datatable-fixed-header" class="table table-bordered table-responsive-md table-striped text-center">
                      <thead>
                        <tr>
                          <th><center>No.</th>
                          
                          <th><center>Nama</th>
						  <th><center>Alamat</th>
						<th><center>Status</th>
                          <th><center>Jenis Pemeriksaan</th>
      
		<th><center>Tgl_Input</th>
						  <th><center>Petugas</th> 
								
                           
                        </tr>
                      </thead>
                      <tbody>
					  <?php
				 
$no =1;
foreach ($query->result() as $baris){
	echo "<tr>";
	echo "<td>".$no."</td>";
	
	echo "<td><center>".$baris->nama_lengkap."</center></td>";
	echo "<td><center>".$baris->alamat."</center></td>";
		
 if($baris->status_pasien==0 && $baris->status_pasien_lab==0 && $baris->status_pasien_radiologi==0 ){
	echo "<td><center> <div class='w_center w_59'>
                      <div class='progress'> 
                        <div class='progress-bar bg-green' role='progressbar' aria-valuenow='60' aria-valuemin='0' aria-valuemax='100' style='width: 0%;'>
                         <span class='sr-only'>60%Complete</span> <center></center>
                        </div>0%
                     </div>
                    </div></center></td>";
 }
  if($baris->status_pasien==0 && $baris->status_pasien_lab==0 && $baris->status_pasien_radiologi==1 ){
	echo "<td><center> <div class='w_center w_59'>
                      <div class='progress'> 
                        <div class='progress-bar bg-green' role='progressbar' aria-valuenow='60' aria-valuemin='0' aria-valuemax='100' style='width: 50%;'>
                         <span class='sr-only'>60%Complete</span> <center>50%</center>
                        </div>
                     </div>
                    </div></center></td>";
 }
  if($baris->status_pasien==0 && $baris->status_pasien_lab==1 && $baris->status_pasien_radiologi==0 ){
	echo "<td><center> <div class='w_center w_59'>
                      <div class='progress'> 
                        <div class='progress-bar bg-green' role='progressbar' aria-valuenow='60' aria-valuemin='0' aria-valuemax='100' style='width: 30%;'>
                         <span class='sr-only'>60%Complete</span> <center>30%</center>
                        </div>
                     </div>
                    </div></center></td>";
 }
  if($baris->status_pasien==1 && $baris->status_pasien_lab==0 && $baris->status_pasien_radiologi==0 ){
	echo "<td><center> <div class='w_center w_59'>
                      <div class='progress'> 
                        <div class='progress-bar bg-green' role='progressbar' aria-valuenow='60' aria-valuemin='0' aria-valuemax='100' style='width: 30%;'>
                         <span class='sr-only'>60%Complete</span> <center>30%</center>
                        </div>
                     </div>
                    </div></center></td>";
 }
  if($baris->status_pasien==1 && $baris->status_pasien_lab==0 && $baris->status_pasien_radiologi==1 ){
	echo "<td><center> <div class='w_center w_59'>
                      <div class='progress'> 
                        <div class='progress-bar bg-green' role='progressbar' aria-valuenow='60' aria-valuemin='0' aria-valuemax='100' style='width: 60%;'>
                         <span class='sr-only'>60%Complete</span> <center>60%</center>
                        </div>
                     </div>
                    </div></center></td>";
 }
  if($baris->status_pasien==1 && $baris->status_pasien_lab==1 && $baris->status_pasien_radiologi==0 ){
	echo "<td><center> <div class='w_center w_59'>
                      <div class='progress'> 
                        <div class='progress-bar bg-green' role='progressbar' aria-valuenow='60' aria-valuemin='0' aria-valuemax='100' style='width: 60%;'>
                         <span class='sr-only'>60%Complete</span> <center>60%</center>
                        </div>
                     </div>
                    </div></center></td>";
 }
  if($baris->status_pasien==0 && $baris->status_pasien_lab==1 && $baris->status_pasien_radiologi==1 ){
	echo "<td><center> <div class='w_center w_59'>
                      <div class='progress'> 
                        <div class='progress-bar bg-green' role='progressbar' aria-valuenow='60' aria-valuemin='0' aria-valuemax='100' style='width: 60%;'>
                         <span class='sr-only'>60%Complete</span> <center>60%</center>
                        </div>
                     </div>
                    </div></center></td>";
 }
  if($baris->status_pasien==1 && $baris->status_pasien_lab==1 && $baris->status_pasien_radiologi==1 ){
	echo "<td><center> <div class='w_center w_59'>
                      <div class='progress'> 
                        <div class='progress-bar bg-green' role='progressbar' aria-valuenow='60' aria-valuemin='0' aria-valuemax='100' style='width: 100%;'>
                         <span class='sr-only'>60%Complete</span> <center>100%</center>
                        </div>
                     </div>
                    </div></center></td>";
 }
 
		echo "<td><center>".$baris->pemeriksaan."</center></td>";
	
	echo "<td><center>".$baris->tgl_input."</center></td>";
	echo "<td><center>".$baris->petugas."</center></td>";
  
	$no++;	
		
}
				?></div>
        </div></div></div>
        <!-- /page content -->
        <!-- /page content -->
      <!-- page content -->
             <!-- page content -->
        

            
           
		 </div></div></div></div></div>
        <!-- footer content -->
      </div>
	  
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
