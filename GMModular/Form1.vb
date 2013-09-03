Public Class Form1

    Private Sub FolderBrowserDialog1_HelpRequest(ByVal sender As System.Object, ByVal e As System.EventArgs)

    End Sub

    Private Sub Button1_Click(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles Button1.Click
        ' Create an instance of the open file dialog box.
        Dim openFileDialog1 As OpenFileDialog = New OpenFileDialog

        ' Set filter options and filter index.
        openFileDialog1.Filter = "Game Maker Studio Project File (*.gmx)|*.gmx|All Files (*.*)|*.*"
        openFileDialog1.FilterIndex = 1

        openFileDialog1.Multiselect = False

        ' Call the ShowDialog method to show the dialogbox.
        Dim UserClickedOK As Boolean = openFileDialog1.ShowDialog

        ' Process input if the user clicked OK.
        If (UserClickedOK = True) Then
            'Open the selected file to read.
            Dim fileStream As System.IO.Stream = openFileDialog1.OpenFile()

            Using reader As New System.IO.StreamReader(fileStream)
                ' Read the first line from the file and write it to the text box.
                RichTextBox1.Text = reader.ReadLine
            End Using
            fileStream.Close()
        End If
    End Sub
End Class
