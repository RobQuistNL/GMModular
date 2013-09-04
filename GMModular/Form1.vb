Imports System
Imports System.IO
Imports System.Xml

Public Class Form1

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
        If Not (My.Computer.FileSystem.FileExists(submoduleFolder + "modules.gmm")) Then
            MessageBox.Show("modules.gmm file was not found in the submodules directory." + vbNewLine + vbNewLine + _
                            "This file keeps track of all changes. It can be possible that you've just made the submodules folder, in that case, you're safe." + vbNewLine + vbNewLine + _
                            "Otherwise, you should manually clean up your Original GMX project (remove all module stuff), or find the modules.gmm file! ", "modules.gmm not found", MessageBoxButtons.OK, MessageBoxIcon.Exclamation)
        End If

        If (My.Computer.FileSystem.DirectoryExists(submoduleFolder) = True) Then
            'Loop through the submodule folder to search for GMX files.
            writeXMLButton.Enabled = True
            Dim di As New DirectoryInfo(submoduleFolder)
            Dim diArr As DirectoryInfo() = di.GetDirectories()
            Dim i As Integer = 0

            Dim dir As DirectoryInfo
            For Each dir In diArr
                Dim gmxArr As FileInfo() = dir.GetFiles("*.gmx")
                Dim gmxFile As FileInfo


                For Each gmxFile In gmxArr
                    'Fetch parent directory name of GMX file (in case 2 GMX's have the same name.
                    Dim directoryName As String = Path.GetDirectoryName(gmxFile.FullName)
                    Dim parentName As String = Path.GetFileName(directoryName)
                    submoduleListBox.Items.Add("", "check")
                    submoduleListBox.Items(i).SubItems.Add(parentName + "/" + gmxFile.Name)
                    submoduleListBox.Items(i).ToolTipText = gmxFile.FullName
                    i = i + 1
                Next (gmxFile)
            Next dir

        End If
    End Sub

    Private Sub cleanUpProject()
        submoduleListBox.Items.Clear()
    End Sub

    Private Sub Form1_Load(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles MyBase.Load
        'Load images
        ImageList1.Images.Add("check", System.Drawing.SystemIcons.Shield)
        ImageList1.Images.Add("error", System.Drawing.SystemIcons.Error)
        ImageList1.Images.Add("warning", System.Drawing.SystemIcons.Exclamation)

    End Sub

    Private Sub writeGMM(ByVal filename As String)
        Dim settings As New XmlWriterSettings()
        settings.Indent = True
        Dim XmlWrt As XmlWriter = XmlWriter.Create(filename, settings)

        With XmlWrt
            .WriteStartDocument()
            .WriteComment("This is a GMModular file. Please do not mess around with it, as it may f*ck up your GMX project. Yeah I just said the F-word. So what? We're all programmers.")

            ' Write the root element.
            .WriteStartElement("installedmodules")

            Dim submoduleitem As ListViewItem
            For Each submoduleitem In submoduleListBox.Items
                .WriteStartElement("module")
                .WriteStartElement("location")
                .WriteString(submoduleitem.ToolTipText)
                .WriteEndElement()
                .WriteEndElement()
            Next submoduleitem

            .WriteEndElement() 'installed modules

            ' Close the XmlTextWriter.
            .WriteEndDocument()
            .Close()

        End With


    End Sub


    Private Sub Button2_Click(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles writeXMLButton.Click
        writeGMM(projectFolderTextbox.Text + "\submodules\modules.gmm")
    End Sub
End Class
