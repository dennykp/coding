Imports MySql.Data.MySqlClient
Imports System.Xml
Public Class databarang

    Private Sub databarang_FormClosing(ByVal sender As Object, ByVal e As System.Windows.Forms.FormClosingEventArgs) Handles Me.FormClosing

    End Sub

    Private Sub databarang_Load(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles MyBase.Load
        awal()
        isiGrid()
        ComboBox1.Items.Add("Biji")
        ComboBox1.Items.Add("Rim")
        ComboBox1.Items.Add("Lusin")
        RichTextBox1.Enabled = False
        RichTextBox2.Enabled = False
        RichTextBox3.Enabled = False
        ComboBox1.Enabled = False
    End Sub
    Sub isiGrid()
        connection.bukaDB()
        DA = New MySqlDataAdapter("SELECT * from databarang", Conn)
        DS = New DataSet
        DA.Fill(DS, "databarang")
        DataGridView1.DataSource = DS.Tables("databarang")
        DataGridView1.ReadOnly = True
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
        ComboBox1.Text = ""
    End Sub

    Private Sub RichTextBox1_TextChanged(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles RichTextBox1.TextChanged

    End Sub

    Private Sub DataGridView1_CellContentClick(ByVal sender As System.Object, ByVal e As System.Windows.Forms.DataGridViewCellEventArgs) Handles DataGridView1.CellContentClick

    End Sub
    Sub datagridcek()
        Button1.Enabled = False
        Button2.Enabled = False
        Button3.Enabled = True
        Button4.Enabled = True
        Button5.Enabled = True
    End Sub
    Private Sub DataGridView1_CellMouseDoubleClick(ByVal sender As Object, ByVal e As System.Windows.Forms.DataGridViewCellMouseEventArgs) Handles DataGridView1.CellMouseDoubleClick
        Try
            RichTextBox1.Text = DataGridView1.CurrentRow.Cells(0).Value
            RichTextBox2.Text = DataGridView1.CurrentRow.Cells(1).Value
            RichTextBox3.Text = DataGridView1.CurrentRow.Cells(2).Value
            ComboBox1.Text = DataGridView1.CurrentRow.Cells(3).Value
            RichTextBox1.Enabled = False
            RichTextBox2.Enabled = True
            RichTextBox3.Enabled = True
            ComboBox1.Enabled = True
            datagridcek()
        Catch ex As Exception
            MsgBox("Tidak ada data")
        End Try

    End Sub
    Sub autoid()
        Dim conn As MySqlConnection
        Dim SQL As String
        Dim cmd As MySqlCommand
        Dim datareader As MySqlDataReader
        Try
            conn = New MySqlConnection()
            conn.ConnectionString = "server=localhost;user id=root;" & _
                                    "password=;database=atk"
            conn.Open()

            cmd = New MySqlCommand("select * from databarang order by kd_barang desc", conn)
            datareader = cmd.ExecuteReader
            datareader.Read()

            If Not datareader.HasRows Then
                RichTextBox1.Text = "kd01"
                RichTextBox1.Refresh()
            Else
                RichTextBox1.Text = Val(Microsoft.VisualBasic.Mid(datareader.Item("kd_barang").ToString, 3, 2)) + 1
                If Len(RichTextBox1.Text) = 1 Then
                    RichTextBox1.Text = "kd0" & RichTextBox1.Text & ""
                ElseIf Len(RichTextBox1.Text) = 2 Then
                    RichTextBox1.Text = "kd" & RichTextBox1.Text & ""
                ElseIf Len(RichTextBox1.Text) = 3 Then
                    RichTextBox1.Text = "k" & RichTextBox1.Text & ""
                End If
            End If
        Catch ex As Exception
            MessageBox.Show("Terjadi Kesalahan : " & ex.Message, "Pesan Peringatan",
            MessageBoxButtons.OK, MessageBoxIcon.Warning)
        End Try
    End Sub

    Private Sub Button1_Click(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles Button1.Click
        autoid()

        RichTextBox2.Enabled = True
        RichTextBox3.Enabled = True
        ComboBox1.Enabled = True
        Button1.Enabled = False
        Button2.Enabled = True
        Button3.Enabled = True

        Button4.Enabled = False
        Button5.Enabled = False
    End Sub

    Private Sub Button2_Click(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles Button2.Click
        If Button4.Text = "Tambah" Then

        Else
            Try
                Call bukaDB()
                CMD = New MySqlCommand("SELECT nama_barang,jumlah,satuan from databarang WHERE kd_barang= '" & RichTextBox1.Text & "'", Conn)
                RD = CMD.ExecuteReader
                RD.Read()
                If RD.HasRows Then
                    MsgBox("Maaf, Data dengan kode tersebut telah ada",
MsgBoxStyle.Exclamation, "Peringatan")
                Else
                    Call bukaDB()

                    simpan = "INSERT INTO databarang(kd_barang, nama_barang, jumlah, satuan) VALUES('" & RichTextBox1.Text & "','" & RichTextBox2.Text & "','" & RichTextBox3.Text & "','" & ComboBox1.Text & "')"
                    CMD = New MySqlCommand(simpan, Conn)
                    CMD.ExecuteNonQuery()
                    MsgBox("Data baru tersimpan")
                    Button1.Enabled = True
                    Button4.Enabled = False
                    Button2.Enabled = False
                    Button3.Enabled = False
                    RichTextBox1.Text = ""
                    RichTextBox2.Text = ""
                    RichTextBox3.Text = ""
                    ComboBox1.Text = ""
                    Call isiGrid()



                End If
            Catch ex As Exception
                MsgBox(ex.ToString, MsgBoxStyle.Critical, "Terjadi Kesalahan")
            End Try
        End If

    End Sub

    Private Sub Button3_Click(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles Button3.Click
        Button1.Enabled = True
        Button2.Enabled = False
        Button3.Enabled = False

        Button4.Enabled = False
        Button5.Enabled = False
        RichTextBox1.Text = ""
        RichTextBox2.Text = ""
        RichTextBox3.Text = ""
        ComboBox1.Text = ""
    End Sub

    Private Sub Button4_Click(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles Button4.Click
        Try
            Call bukaDB()
            hapus = "DELETE FROM databarang WHERE kd_barang='" & RichTextBox1.Text & "'"
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

    Private Sub Button5_Click(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles Button5.Click
        Try
            Call bukaDB()
            ubah = "UPDATE databarang SET nama_barang='" & RichTextBox2.Text & "',jumlah='" & RichTextBox3.Text & "',satuan='" & ComboBox1.Text & " ' WHERE kd_barang = '" & RichTextBox1.Text & "'"
            MsgBox("Data berhasil di Update !")
            CMD = New MySqlCommand(ubah, Conn)
            CMD.ExecuteNonQuery()
            Call bersih()
            Call awal()
            Button1.Enabled = True

            Call isiGrid()

        Catch ex As Exception
            MsgBox(ex.ToString, MsgBoxStyle.Critical, "Terjadi Kesalahan")
        End Try

    End Sub
End Class