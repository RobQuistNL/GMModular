Imports System
Imports System.IO

Public Class Form1

    Private Sub FolderBrowserDialog1_HelpRequest(ByVal sender As System.Object, ByVal e As System.EventArgs)

    End Sub

    Private Sub Button1_Click(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles Button1.Click
        cleanUpProject()

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
            projectFolderTextbox.Text = Path.GetDirectoryName(openFileDialog1.FileName)

            Dim fileStream As Stream = openFileDialog1.OpenFile()

            Using reader As New StreamReader(fileStream)
                ' Read the first line from the file and write it to the text box.
                RichTextBox1.Text = reader.ReadLine
            End Using
            fileStream.Close()

            checkForSubmodules()

        End If
    End Sub

    Private Sub checkForSubmodules()
        'Dim s As String
        'Dim colFolders As Collection
        Dim submoduleFolder As String
        submoduleFolder = projectFolderTextbox.Text + "\submodules\"

        If (My.Computer.FileSystem.DirectoryExists(submoduleFolder) = True) Then
            'Loop through the submodule folder to search for GMX files.

            Dim di As New DirectoryInfo(submoduleFolder)
            Dim diArr As DirectoryInfo() = di.GetDirectories()

            Dim dir As DirectoryInfo
            For Each dir In diArr
                Dim gmxArr As FileInfo() = dir.GetFiles("*.gmx")
                Dim gmxFile As FileInfo

                For Each gmxFile In gmxArr
                    'Fetch parent directory name of GMX file (in case 2 GMX's have the same name.
                    Dim directoryName As String = Path.GetDirectoryName(gmxFile.FullName)
                    Dim parentName As String = Path.GetFileName(directoryName)
                    submoduleListBox.Items.Add(parentName + "/" + gmxFile.Name)
                Next (gmxFile)
            Next dir

        End If
    End Sub

    Private Sub cleanUpProject()
        submoduleListBox.Items.Clear()
    End Sub

End Class
