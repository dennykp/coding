<!-- page content -->
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<div class="right_col" role="main">
          <div class="">
            <div class="row top_tiles">
            
             
			   <div class="row top_tiles">
                   <?php if($this->session->userdata('akses')=='frontoffice'):?>
			  <div class="animated flipInY col-lg-3 col-md-3 col-sm-6 col-xs-12">
                <a href="<?php echo base_url()."index.php/home/form_daftar_TKLN"; ?>">
                <div class="tile-stats">
                  <div class="icon"><i class="fa fa-plus-square"></i></div>
                  <div class="count">TKLN</div>
                  <h3>Luar Negeri</h3>
                  <p>Tambah Pasien Baru</p>
                  <p>Pemeriksaan Kesehatan</p>
                </div>
                </a>
              </div>
			  
              <div class="animated flipInY col-lg-3 col-md-3 col-sm-6 col-xs-12">
                <a href="<?php echo base_url()."index.php/home/form_daftar_TKDN"; ?>">
                <div class="tile-stats">
                  <div class="icon"><i class="fa fa-plus-square"></i></div>
                  <div class="count">TKDN</div>
                  <h3>Dalam Negeri</h3>
                  <p>Tambah Pasien Baru</p>
                  <p>Pemeriksaan Kesehatan</p>
                </div>
                </a>
              </div>
			  
            </div>
            </div>
			</div>
    
          <div class="">
           
			
  <?php endif;?>
            <div class="row">
			
              <div class="col-md-12">
                <div class="x_panel">
				
                  <div class="x_title">

                    <h2>Transaction Summary <small>Weekly progress</small></h2>
             </br></br>
			 Home Menu Utama
	<?php if ($this->session->flashdata('msg_login')): ?>
        <script>
		
            swal({
                title: "Succesfully Login",
                text: "Data is Update",
                timer: 2500,
                showConfirmButton: false,
                type: 'success',
				icon: 'success'
            });
        </script>
<?php endif; ?>
                        </div>
                      </div>

                    </div>




            
</div></div>
        </div>
        <!-- /page content -->