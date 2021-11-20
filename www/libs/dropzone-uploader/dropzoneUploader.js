
//window.Dropzone;
var myDropzone = new Dropzone('#frm-uploader-uploadForm',
{
	url: $(this).attr('action'),
	maxFilesize: 5000,
	maxFiles: null,
	timeout: 900000,
	//acceptedFiles: null,
	createImageThumbnails: true,
	//createImageThumbnails: false,
	dictDefaultMessage: "Sem přetáhněte soubory, nebo klikněte na <span class='btn btn-primary'>UPLOAD</span>",
	dictFallbackMessage: "Váš prohlížeč nepodporuje nahrávání souborů drag'n'drop.",
	dictFallbackText: "Použijte níže uvedený záložní formulář a nahrajte soubory jako za starých časů.",
	dictFileTooBig: "Soubor je příliš velký ({{filesize}} MB). Maximální velikost souboru: {{maxFilesize}} MB.",
	dictInvalidFileType: "Nelze nahrávat soubory tohoto typu.",
	dictResponseError: "CHYBA: Server odpověděl kódem {{statusCode}}.",
	dictCancelUpload: "Zrušit nahrávání",
	dictUploadCanceled: "Nahrávání zrušeno.",
	dictCancelUploadConfirmation: "Opravdu chcete toto nahrávání zrušit?",
	dictRemoveFile: "Odstranit soubor",
	dictRemoveFileConfirmation: "Opravdu chcete tento soubor odstranit?",
	dictMaxFilesExceeded: "Nemůžete nahrát žádné další soubory.",
});

myDropzone.on('addedfile', function(file) {
    var ext = file.name.split('.').pop().toLowerCase();

	// KNOWN FILETYPE ICONS
	var aAvailableIcons = [ "3ds","ai","asp","avi","bin","com","css","csv","dbf","dll","doc","dwg",
							"eml","eps","exe","fla","gif","htm","ico","ini","iso","jar","jpg","js",
							"mkv","mov","mp3","mp4","nfo","obj","otf","pdf","pkg","png","ppt","psd",
							"rtf","svg","text","ttf","txt","vcf","wav","wmv","xls","xml","zip" ];

	var iconName = "unknown";

	if(aAvailableIcons.includes(ext)) {
		iconName = ext;
	} else {
		// ALTERNATIVE FILETYPE ICONS
		switch(ext) {
			case "docx": iconName = "doc"; break;
			case "html": iconName = "htm"; break;
			case "odt":  iconName = "doc-alt"; break;
			case "7z":   iconName = "zip-alt"; break;
			case "rar":  iconName = "zip-alt"; break;
			case "m2t":  iconName = "mov"; break;
			case "jpeg": iconName = "jpg"; break;
			default:     iconName = "unknown"; break;
		}
	}

	// SHOW FILETYPE ICON
	$(file.previewElement).find(".dz-image img").attr("style", "width:120px; height:120px;").attr("src", "/img/file-types/"+iconName+".png");

	// CLOSE WINDOW ALERT
	window.onbeforeunload = function() { return confirm("Při opuštění stránky bude právě probíhající upload zrušen.\nChcete pokračovat?"); };
});

// PO DOKONČENÍ UPLOADU ODSTRANÍ POLOŽKY Z FRONTY (timeout)
myDropzone.on("complete", function(file) {
	setTimeout(function () { myDropzone.removeFile(file); }, 500);
});

// PO DOKONČENÍ UPLOADU ODSTRANÍ VŠECHNY POLOŽKY Z FRONTY
myDropzone.on("queuecomplete", function(file) {
	window.onbeforeunload = function() { };
	//setTimeout(function () { myDropzone.removeAllFiles(); location.reload(); }, 1700);
	location.reload();
});

/*myDropzone.on("success", function(file) {
	var ext = checkFileExt(file.name); // Get extension
	var newimage = "";

	// Check extension
	if(ext != 'png' && ext != 'jpg' && ext != 'jpeg'){
		newimage = "data/folder.png"; // default image path
		myDropzone.createThumbnailFromUrl(file, newimage);
	}

	//this.createThumbnailFromUrl(file, newimage);
});*/
