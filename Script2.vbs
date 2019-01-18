PRIVATE SUB writeTextFile(BYVAL txt,BYVAL strTextFilePath)
	DIM objFSO,objTextFile
	
	SET objFSO = CREATEOBJECT("Scripting.FileSystemObject")

	SET objTextFile = objFSO.CreateTextFile(strTextFilePath)

	objTextFile.Write(txt)

	objTextFile.Close
	SET objTextFile = NOTHING
END SUB
