<table border="1">
		<tr>
			<th>No.</th>
			<th>Merek Kendaraan</th>
		
			<th colspan="2">Opsi</th>
		</tr>
		<?php 
 
			$no = 1;
			foreach ($query->result() as $baris) {
				echo "<tr>";
				echo "<td>".$no."</td>";
				echo "<td>".$baris->nama_lengkap."</td>";
			
				echo "<td><a href=".base_url('index.php/home/edit/').$baris->id_pasien.">Edit</a></td>";
				echo "<td><a href=".base_url('index.php/crud/hapus/').$baris->id_pasien.">Hapus</a></td>";
				echo "</tr>";
			$no++; } 
 
 
		?>
	</table> 