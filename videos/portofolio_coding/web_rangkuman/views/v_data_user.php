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
	 <script type="javascript">
  // Get the modal
var modal = document.getElementById('myModal');

// Get the button that opens the modal
var btn = document.getElementById("myBtn");

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

// When the user clicks on the button, open the modal 
btn.onclick = function() {
    modal.style.display = "block";
}

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
    modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
  </script>
  <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
  
</head> 
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>


/* The Modal (background) */
.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1; /* Sit on top */
    padding-top: 90px; /* Location of the box */
    left: 0;
    top: 1;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
 
    background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
}

/* Modal Content */
.modal-content {
    position: relative;
    background-color: #fefef3;
    margin: auto;
    padding: 0;
  
    width: 38%;
    box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19);
    -webkit-animation-name: animatetop;
    -webkit-animation-duration: 0.4s;
    animation-name: animatetop;
    animation-duration: 0.6s
}

/* Add Animation */
@-webkit-keyframes animatetop {
    from {top:-300px; opacity:0} 
    to {top:0; opacity:1}
}

@keyframes animatetop {
    from {top:-300px; opacity:0}
    to {top:0; opacity:1}
}

/* The Close Button */
.close {
	padding: 10px 20px 7px 9px;
    color: white;
    float: right;
    font-size: 30px;
    font-weight: bold;
}

.close:hover,
.close:focus {
	padding: 12px 10px 7px 9px;

    text-decoration: none;
    cursor: pointer;
}

.modal-header {
    padding: 20px 20px 7px 9px;
    background-color: #cc6666;
    color: white;
}

.modal-body {padding: 1px 15px;}

.modal-footer {
    padding: 20px 20px 7px 9px;
    background-color: #cc6666;
    color: white;
}
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
			   <?php if ($this->session->flashdata('message')): ?>
        <script>
		
            swal({
                title: "Data Inserted",
                text: "Data is Succesfully",
                timer: 2500,
                showConfirmButton: false,
                type: 'success',
				icon: 'success'
            });
        </script>
<?php endif; ?>
        <div class="right_col" role="main">
          <div class="">

            
            <div class="clearfix"></div>

            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
				  <div id="myModal" class="modal">

  <!-- Modal content -->
  <div class="modal-content">
    <div class="modal-header">
     
      <div align="center"><h2>ADD USER</h2></div>
	   <span class="close">x</span>
    </div>
   <div class="modal-body">
     	<form action="<?php echo base_url('index.php/home/insert_user'); ?>" method ="POST">
		</br>
	
<div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
                        <input type="text" class="form-control has-feedback-left" name="nama" id="inputSuccess2" placeholder="First Name">
                        <span class="fa fa-user form-control-feedback left"  aria-hidden="true"></span>
                      </div>

<div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
                        <input type="text" class="form-control has-feedback-left" name="alamat" id="inputSuccess2" placeholder="Address">
                        <span class="fa fa-toggle-down form-control-feedback left" aria-hidden="true"></span>
                      </div>
<div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
                        <input type="text" class="form-control has-feedback-left" name="username" id="inputSuccess2" placeholder="Username">
                        <span class="fa fa-user form-control-feedback left" aria-hidden="true"></span>
                      </div>					  
<div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
                        <input type="text" class="form-control has-feedback-left" name="password" id="inputSuccess2" placeholder="Password">
                           <span class="fa fa-key form-control-feedback left "  aria-hidden="true"></span>
                      </div>
<div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
                        <input type="text"  class="form-control has-feedback-left" name="status" id="inputSuccess2" placeholder="HakAkses">
                        <span class="fa fa-users form-control-feedback left "  aria-hidden="true"></span>
                      </div>					  
 <center><button type="submit" class="btn btn-primary">Simpan Data</button>
  </form>
    </div></br>
    <div class="modal-footer">
      <h5>@Admin TKDN/TKLN</h5>
    </div>
  </div>

</div>

                    <h2> Data Akun Pengguna &nbsp  </h2> <button type="submit" id="myBtn"  class="btn btn-primary"></a><i class="fa fa-edit"></i> Tambah User</i></button>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                     <table id="datatable-fixed-header" class="table table-striped jambo_table bulk_action">
                      <thead align="center">
                        <tr>
                          <th>No.</th>
                          <th>ID-User</th>
                          <th>Nama</th>
						  <th>Alamat</th>
                          <th>Username</th>
                          <th>Hak Akses/Jabatan</th>
                         <th><center>Aksi/Kelola User</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
$no =1;
foreach ($query->result() as $baris){
	echo "<tr>";
	echo "<td>".$no."</td>";
	echo "<td><strong>".$baris->id."</td>";
	echo "<td>".$baris->nama."</td>";
	echo "<td>".$baris->alamat."</td>";
  
		echo "<td>".$baris->username."</td>";
		echo "<td><button type='button' class='btn btn-primary btn-xs'>".$baris->status."</button></td>";
	echo "<td><center><a href=".base_url('c_admin/hapus_magangpenelitian/').$baris->id." class='btn btn-success btn-xs'><i class='fa fa-folder'></i> Update </i></button></a>&nbsp";
echo "<a href=".base_url('c_admin/klik_detail_mhs/').$baris->id." class='btn btn-danger btn-xs'><i class='fa fa-trash-o'></i> Hapus</a></button></a>&nbsp";
$no++;		
}
				?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>




          </div>
        </div>
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
<script>
// Get the modal
var modal = document.getElementById('myModal');

// Get the button that opens the modal
var btn = document.getElementById("myBtn");

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

// When the user clicks the button, open the modal 
btn.onclick = function() {
    modal.style.display = "block";
}

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
    modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>
