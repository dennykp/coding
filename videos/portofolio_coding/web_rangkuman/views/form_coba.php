<form action="<?php echo base_url('index.php/home/update'); ?>" method="POST">
					
					<?php foreach ($query->result() as $baris){?>
						
                     
					<input type="hidden" name="id_pasien" value="<?php echo $baris->id_pasien; ?>">
                          
                        
                           
                              <input type="text" name="nama_lengkap"  value="<?php echo $baris->nama_lengkap; ?>">
                            
                          
						
						
                     
<input type="submit" value="Update">
                   
					  	<?php } ?>
					</form>
				