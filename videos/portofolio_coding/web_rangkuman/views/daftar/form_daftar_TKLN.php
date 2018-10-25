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
                    <div class="x_panel">
                      <div class="x_title">
                        <h2>Data Diri Pasien</h2>
                        <div class="clearfix"></div>
                      </div>
                      <!-- data diri -->
                      <div class="x_content">
                      <div class="col-md-6 col-xs-12">
                      <br/>
					  <?php echo form_open('index.php/home/insert') ?>
                        <div class="form-horizontal form-label-left">
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Nama Lengkap</label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                              <input type="text" name="nama_lengkap" class="form-control">
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
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <div id="gender" class="btn-group" data-toggle="buttons">
                                <label class="btn btn-default" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                  <input type="radio" name="jenis_kelamin" value="male"> &nbsp; Laki-laki &nbsp;
                                </label>
                                <label class="btn btn-primary" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                  <input type="radio" name="jenis_kelamin" value="female"> Perempuan
                                </label>
                              </div>
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
                              <input type="text" name="no_register" class="form-control">
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
                              <input type="text" name="pemeriksaan" class="form-control"  placeholder="Tenaga Kerja Dalam Negeri">
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
                              <input name="no_passport" type="text" class="form-control">
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
                                  <input type="checkbox" name="check_list_lab[]" value="Kimia Klinik-SGOT" class="flat"> Kimia Klinik - SGOT
                                </label>
                              </div>
                              <div class="checkbox">
                                <label>
                                  <input type="checkbox" name="check_list_lab[]" value="Kimia Klinik-SGPT" class="flat"> Kimia Klinik - SGPT
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
                                  <input type="checkbox" name="check_list_lab[]" value="Serologi - VDRl" class="flat"> Serologi - VDRL
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
                                  <input type="checkbox" name="check_list_lab[]" value="Tes Narkoba" class="flat"> Tes Narkoba - THC
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
                            <div class="col-md-3 col-sm-9 col-xs-12">
                              <div class="checkbox">
                                <label>
                               
											 
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
                          <button type="submit" class="btn btn-primary">Simpan</button>
                        </ul>
					<?php echo form_close()?>
                    </div>
                  </div>
                </div>
      </div>
<?php endif ?>
<!-- /page content -->