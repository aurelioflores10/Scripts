CONST strComputer = "."
CONST strReport = "C:\Program Files\Scripts\diskspace.txt"

DIM objWMIService, objItem, colItems
DIM strDriveType, strDiskSize, txt

SET objWMIService = GETOBJECT("winmgmts:\\" & strComputer & "\root\cimv2")
SET colItems = objWMIService.ExecQuery("Select * from Win32_LogicalDisk WHERE DriveType=3")
txt = "Drive" & vbtab & "Size" & vbtab & "Used" & vbtab & "Free" & vbtab & "Free(%)" & vbcrlf
FOR EACH objItem in colItems
	DIM pctFreeSpace,strFreeSpace,strusedSpace
	pctFreeSpace = INT((objItem.FreeSpace / objItem.Size) * 1000)/10
	strDiskSize = Int(objItem.Size /1073741824) & "Gb"
	strFreeSpace = Int(objItem.FreeSpace /1073741824) & "Gb"
	strUsedSpace = Int((objItem.Size-objItem.FreeSpace)/1073741824) & "Gb"
	txt = txt & objItem.Name & vbtab & strDiskSize & vbtab & strUsedSpace & vbTab & strFreeSpace & vbtab & pctFreeSpace & vbcrlf

NEXT

writeTextFile txt, strReport
wscript.echo "Report written to " & strReport & vbcrlf & vbcrlf & txt

' Procedure to write output to a text file
PRIVATE SUB writeTextFile(BYVAL txt,BYVAL strTextFilePath)
	DIM objFSO,objTextFile
	
	SET objFSO = CREATEOBJECT("Scripting.FileSystemObject")

	SET objTextFile = objFSO.CreateTextFile(strTextFilePath)

	objTextFile.Write(txt)

	objTextFile.Close
	SET objTextFile = NOTHING
END SUB

