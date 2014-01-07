Imports System
Imports System.IO
Imports System.Xml
Imports System.Xml.Serialization

Public Module DefineGlobals
    Public generalProjectString As String                 'General project file
End Module

Public Class Form1

    Private Sub Button1_Click(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles Button1.Click
        cleanUpProject()

        ' Create an instance of the open file dialog box.
        Dim openFileDialog1 As OpenFileDialog = New OpenFileDialog

        ' Set filter options and filter index.
        openFileDialog1.Title = "Select project to manage modules in"
        openFileDialog1.Filter = "Game Maker Studio Project File (*.gmx)|*.gmx|All Files (*.*)|*.*"
        openFileDialog1.FilterIndex = 1

        openFileDialog1.Multiselect = False

        ' Call the ShowDialog method to show the dialogbox.
        Dim UserClickedOK As Boolean = openFileDialog1.ShowDialog

        ' Process input if the user clicked OK.
        If (UserClickedOK = True) Then
            'Open the selected file to read.
            projectFolderTextbox.Text = Path.GetDirectoryName(openFileDialog1.FileName)
            generalProjectString = ReadFile(openFileDialog1.FileName)
            RichTextBox1.Text = generalProjectString
            checkForSubmodules()

        End If
    End Sub

    Private Sub checkForSubmodules()
        Dim submoduleFolder As String
        submoduleFolder = projectFolderTextbox.Text + "\submodules\"

        If (My.Computer.FileSystem.DirectoryExists(submoduleFolder) = True) Then
            If Not (My.Computer.FileSystem.FileExists(submoduleFolder + "modules.gmm")) Then
                MessageBox.Show("modules.gmm file was not found in the submodules directory." + vbNewLine + vbNewLine +
                                "This file keeps track of all changes. It can be possible that you've just made the submodules folder, in that case, you're safe." + vbNewLine + vbNewLine + _
                                "Otherwise, you should manually clean up your Original GMX project (remove all module stuff), or find the modules.gmm file! ", "modules.gmm not found", MessageBoxButtons.OK, MessageBoxIcon.Exclamation)
            End If

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
                    i += 1
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

    Private function ReadFile(ByVal filename As String)
        Return My.Computer.FileSystem.ReadAllText(filename, System.Text.Encoding.UTF8)
    End Function

    Private Function getAssets(ByVal xmlString As String)
        Using reader As XmlReader = XmlReader.Create(New StringReader(xmlString))
        Dim ws As XmlWriterSettings = New XmlWriterSettings()
        ws.Indent = True
            ' Parse the file and display each of the nodes.
            While reader.Read()
                Select Case reader.NodeType
                    Case XmlNodeType.Element
                        MsgBox("Found an element with name "+reader.Name+"!")
                        Select Case (reader.Name)
                            Case "datafiles"
                                MsgBox("Found datafiles!")
                                Dim obj As New asset_datafile
                                While reader.Read()
                                    Select Case reader.NodeType
                                    Case (XmlNodeType.Element)
                                            MsgBox("Found element "+reader.Name+" inside of datafiles")
                                        Select Case (reader.Name)
                                            Case "name"
                                                reader.Read()
                                                obj.name = reader.ReadString
                                                MsgBox("Value: "+obj.name)
                                            Case "size"
                                                reader.Read()
                                                obj.size = reader.ReadString
                                                MsgBox("Value: "+obj.size)
                                        End Select
                                    Case XmlNodeType.EndElement
                                    End Select
                                End While
                        End Select
                End Select
            End While
        End Using
        Return False
    End Function
        

    Private Function writeGMM(ByVal filename As String)
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
                Dim submoduleGMX = ReadFile(submoduleitem.ToolTipText)
                getAssets(submoduleGMX)
                .WriteStartElement("module")
                    .WriteStartElement("location")
                        .WriteString(submoduleitem.ToolTipText)
                    .WriteEndElement()
                    .WriteStartElement("assets")
                        
                        
                        
                    .WriteEndElement()
                .WriteEndElement()
            Next submoduleitem

            .WriteEndElement() 'installed modules

            ' Close the XmlTextWriter.
            .WriteEndDocument()
            .Close()

        End With
        Return 0
    End Function


    Private Sub Button2_Click(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles writeXMLButton.Click
        writeGMM(projectFolderTextbox.Text + "\submodules\modules.gmm")
    End Sub

    Private Sub Button2_Click_1(ByVal sender As System.Object, ByVal e As System.EventArgs)
        'THIS BUTTON DOES NOT EXIST ANYMORE. It was; ADD MODULE.


        ' Create an instance of the open file dialog box.
        Dim openFileDialog1 As OpenFileDialog = New OpenFileDialog

        ' Set filter options and filter index.
        openFileDialog1.Title = "Select module to add to project"
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
                'RichTextBox1.Text = reader.ReadLine
                ' Here we need to read out the whole project :)
            End Using
            fileStream.Close()

        End If
    End Sub

    Private Sub Button2_Click_2(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles Button2.Click
        'Lets open up the first submodule.

    End Sub

End Class

Class asset_datafile
    Public name As String
    Public exists As String
    Public size As String
    Public exportAction As String
    Public exportDir As String
    Public overwrite As String
    Public freeData As String
    Public removeEnd As String
    Public store As String
    Public filename As String
End Class