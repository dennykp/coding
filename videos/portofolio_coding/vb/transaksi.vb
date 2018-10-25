Imports MySql.Data.MySqlClient
Public Class transaksi

    Private Sub RadioButton2_CheckedChanged(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles RadioButton2.CheckedChanged

    End Sub
    Sub isiGrid()
        connection.bukaDB()
        DA = New MySqlDataAdapter("SELECT * from transaksi", Conn)
        DS = New DataSet
        DA.Fill(DS, "transaksi")
        dgvmutasi.DataSource = DS.Tables("transaksi")
        dgvmutasi.ReadOnly = True
    End Sub
    Sub awal()
        Button2.Enabled = False
        Button3.Enabled = False
        Button4.Enabled = False
        Button5.Enabled = False
    End Sub
    Sub bersih()
        RichTextBox1.Text = ""
        RichTextBox2.Text = ""
        RichTextBox3.Text = ""
        RichTextBox4.Text = ""
        RichTextBox6.Text = ""
        RichTextBox5.Text = ""
    End Sub

    Private Sub transaksi_FormClosed(ByVal sender As Object, ByVal e As System.Windows.Forms.FormClosedEventArgs) Handles Me.FormClosed

    End Sub

    Private Sub transaksi_FormClosing(ByVal sender As Object, ByVal e As System.Windows.Forms.FormClosingEventArgs) Handles Me.FormClosing

    End Sub
   
    Private Sub transaksi_Load(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles MyBase.Load
        RichTextBox1.Hide()

        RichTextBox1.Enabled = False
        RichTextBox2.Enabled = False
        RichTextBox3.Enabled = False
        RichTextBox4.Enabled = False
        RichTextBox5.Enabled = False
        RichTextBox6.Enabled = False
        awal()
        isiGrid()
    End Sub

    Private Sub Button1_Click(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles Button1.Click
        RichTextBox5.Enabled = True


        Button1.Enabled = False
        Button2.Enabled = True
        Button3.Enabled = True

        Button4.Enabled = False
        Button5.Enabled = False
    End Sub

    Private Sub PictureBox1_Click(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles PictureBox1.Click
        caribarang.ShowDialog()
    End Sub

    Private Sub Button2_Click(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles Button2.Click
        Dim ee As String

        Dim vTanggaltransaksi As String
        Dim vTgl As String
        Dim vBln As String
        Dim vThn As String
        Dim pesan As Integer
        vTgl = DateTimePicker1.Value.Day
        vBln = DateTimePicker1.Value.Month
        vThn = DateTimePicker1.Value.Year
        Dim myAdapter As New MySqlDataAdapter
        Dim myData As New DataTable
        Dim datareader As MySqlDataReader
        vTanggaltransaksi = vThn & "-" & vBln & "-" & vTgl

        Try






            Call bukaDB()
            CMD = New MySqlCommand("SELECT kd_barang,tgl_transaksi,keluar,masuk,kode_bagian from transaksi WHERE kd_transaksi= '" & RichTextBox1.Text & "'", Conn)
            RD = CMD.ExecuteReader
            RD.Read()
            If RD.HasRows Then
                MsgBox("Maaf, Data dengan kode tersebut telah ada",
MsgBoxStyle.Exclamation, "Peringatan")
            Else
                If RadioButton1.Checked Then
                    If RichTextBox4.Text > RichTextBox5.Text Then
                        Call bukaDB()

                        simpan = "INSERT INTO transaksi(kd_barang, tgl_transaksi, keluar, kode_bagian) VALUES('" & RichTextBox2.Text & "','" & vTanggaltransaksi & "','" & RichTextBox5.Text & "','" & RichTextBox6.Text & "')"
                        CMD = New MySqlCommand(simpan, Conn)
                        CMD.ExecuteNonQuery()
                        MsgBox("Data baru tersimpan")
                        Call isiGrid()
                        Call bersih()
                    Else
                        MsgBox("stok terbatas")
                    End If
                ElseIf RadioButton2.Checked Then
                    Call bukaDB()

                    simpan = "INSERT INTO transaksi(kd_barang, tgl_transaksi, masuk, kode_bagian) VALUES('" & RichTextBox2.Text & "','" & vTanggaltransaksi & "','" & RichTextBox5.Text & "','" & RichTextBox6.Text & "')"
                    CMD = New MySqlCommand(simpan, Conn)
                    CMD.ExecuteNonQuery()
                    MsgBox("Data baru tersimpan")
                    Call isiGrid()
                    Call bersih()
                End If
            End If

        Catch ex As Exception
            MsgBox("terjadi kesalahan")
        End Try




    End Sub

    Private Sub PictureBox2_Click(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles PictureBox2.Click
        caribagian.ShowDialog()
    End Sub

    Private Sub Button3_Click(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles Button3.Click
        Button1.Enabled = True
        Button2.Enabled = False
        Button3.Enabled = False
        RichTextBox5.Enabled = False
        Button4.Enabled = False
        Button5.Enabled = False
        RichTextBox1.Text = ""
        RichTextBox2.Text = ""
        RichTextBox3.Text = ""
        RichTextBox4.Text = ""
        RichTextBox5.Text = ""
        RichTextBox6.Text = ""
    End Sub

    Private Sub dgvmutasi_CellContentClick(ByVal sender As System.Object, ByVal e As System.Windows.Forms.DataGridViewCellEventArgs) Handles dgvmutasi.CellContentClick

    End Sub
    Sub datagridcek()
        Button1.Enabled = False
        Button2.Enabled = False
        Button3.Enabled = True
        Button4.Enabled = True
        Button5.Enabled = True
    End Sub
    Private Sub dgvmutasi_CellMouseDoubleClick(ByVal sender As Object, ByVal e As System.Windows.Forms.DataGridViewCellMouseEventArgs) Handles dgvmutasi.CellMouseDoubleClick
        Try
            RichTextBox1.Text = dgvmutasi.CurrentRow.Cells(0).Value
            RichTextBox2.Text = dgvmutasi.CurrentRow.Cells(1).Value

            DateTimePicker1.Text = dgvmutasi.CurrentRow.Cells(2).Value
            If RadioButton1.Checked Then
                RichTextBox5.Text = dgvmutasi.CurrentRow.Cells(3).Value
                RichTextBox5.Update()
                RadioButton1.Update()
                RadioButton1.Refresh()
                dgvmutasi.Refresh()
            ElseIf RadioButton2.Checked Then
                RichTextBox5.Text = dgvmutasi.CurrentRow.Cells(4).Value
                RichTextBox5.Update()
                dgvmutasi.Refresh()
                RadioButton2.Update()
                RadioButton2.Refresh()
            End If

            RichTextBox6.Text = dgvmutasi.CurrentRow.Cells(5).Value

            RichTextBox1.Enabled = False
            RichTextBox2.Enabled = False
            RichTextBox3.Enabled = False

            datagridcek()
        Catch ex As Exception
            MsgBox("Tidak ada data")
        End Try

    End Sub

    Private Sub Button4_Click(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles Button4.Click
        Try
            Call bukaDB()
            hapus = "DELETE FROM transaksi WHERE kd_transaksi='" & RichTextBox1.Text & "'"
            CMD = New MySqlCommand(hapus, Conn)
            CMD.ExecuteNonQuery()
            Call bersih()

            Call isiGrid()
            MsgBox("Data Berhasil Dihapus!")
            Call awal()
            Button1.Enabled = True
        Catch ex As Exception
            MsgBox(ex.ToString, MsgBoxStyle.Critical, "Terjadi Kesalahan")
        End Try
    End Sub
End Class