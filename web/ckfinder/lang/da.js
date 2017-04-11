/*
 * CKFinder
 * ========
 * http://ckfinder.com
 * Copyright (C) 2007-2012, CKSource - Frederico Knabben. All rights reserved.
 *
 * The software, this file, and its contents are subject to the CKFinder
 * License. Please read the license.txt file before using, installing, copying,
 * modifying, or distributing this file or part of its contents. The contents of
 * this file is part of the Source Code of CKFinder.
 *
 */

/**
 * @fileOverview Defines the {@link CKFinder.lang} object for the Danish
 *		language.
 */

/**
 * Contains the dictionary of language entries.
 * @namespace
 */
CKFinder.lang['da'] =
{
	appTitle : 'CKFinder',

	// Common messages and labels.
	common :
	{
		// Put the voice-only part of the label in the span.
		unavailable		: '%1<span class="cke_accessibility">, ikke tilgængelig</span>',
		confirmCancel	: 'Nogle af indstillingerne er blevet ændret. Er du sikker på at lukke dialogen?',
		ok				: 'OK',
		cancel			: 'Annuller',
		confirmationTitle	: 'Bekræftelse',
		messageTitle	: 'Information',
		inputTitle		: 'Spørgsmål',
		undo			: 'Fortryd',
		redo			: 'Annuller fortryd',
		skip			: 'Skip',
		skipAll			: 'Skip alle',
		makeDecision	: 'Hvad skal der foretages?',
		rememberDecision: 'Husk denne indstilling'
	},


	// Language direction, 'ltr' or 'rtl'.
	dir : 'ltr',
	HelpLang : 'en',
	LangCode : 'da',

	// Date Format
	//		d    : Day
	//		dd   : Day (padding zero)
	//		m    : Month
	//		mm   : Month (padding zero)
	//		yy   : Year (two digits)
	//		yyyy : Year (four digits)
	//		h    : Hour (12 hour clock)
	//		hh   : Hour (12 hour clock, padding zero)
	//		H    : Hour (24 hour clock)
	//		HH   : Hour (24 hour clock, padding zero)
	//		M    : Minute
	//		MM   : Minute (padding zero)
	//		a    : Firt char of AM/PM
	//		aa   : AM/PM
	DateTime : 'dd-mm-yyyy HH:MM',
	DateAmPm : ['AM', 'PM'],

	// Folders
	FoldersTitle	: 'Mapper',
	FolderLoading	: 'Indlæser...',
	FolderNew		: 'Skriv navnet på den nye mappe: ',
	FolderRename	: 'Skriv det nye navn på mappen: ',
	FolderDelete	: 'Er du sikker på, at du vil slette mappen "%1"?',
	FolderRenaming	: ' (Omdøber...)',
	FolderDeleting	: ' (Sletter...)',

	// Files
	FileRename		: 'Skriv navnet på den nye fil: ',
	FileRenameExt	: 'Er du sikker på, at du vil ændre filtypen? Filen kan muligvis ikke bruges bagefter.',
	FileRenaming	: '(Omdøber...)',
	FileDelete		: 'Er du sikker på, at du vil slette filen "%1"?',
	FilesLoading	: 'Indlæser...',
	FilesEmpty		: 'Tom mappe',
	FilesMoved		: 'Filen %1 flyttet til %2:%3',
	FilesCopied		: 'Filen %1 kopieret til %2:%3',

	// Basket
	BasketFolder		: 'Kurv',
	BasketClear			: 'Tøm kurv',
	BasketRemove		: 'Fjern fra kurv',
	BasketOpenFolder	: 'Åben overordnet mappe',
	BasketTruncateConfirm : 'Er du sikker på at du vil tømme kurven?',
	BasketRemoveConfirm	: 'Er du sikker på at du vil slette filen "%1" fra kurven?',
	BasketEmpty			: 'Ingen filer i kurven, brug musen til at trække filer til kurven.',
	BasketCopyFilesHere	: 'Kopier Filer fra kurven',
	BasketMoveFilesHere	: 'Flyt Filer fra kurven',

	BasketPasteErrorOther	: 'Fil fejl: %e',
	BasketPasteMoveSuccess	: 'Følgende filer blev flyttet: %s',
	BasketPasteCopySuccess	: 'Følgende filer blev kopieret: %s',

	// Toolbar Buttons (some used elsewhere)
	Upload		: 'Upload',
	UploadTip	: 'Upload ny fil',
	Refresh		: 'Opdatér',
	Settings	: 'Indstillinger',
	Help		: 'Hjælp',
	HelpTip		: 'Hjælp',

	// Context Menus
	Select			: 'Vælg',
	SelectThumbnail : 'Vælg thumbnail',
	View			: 'Vis',
	Download		: 'Download',

	NewSubFolder	: 'Ny undermappe',
	Rename			: 'Omdøb',
	Delete			: 'Slet',

	CopyDragDrop	: 'Kopier hertil',
	MoveDragDrop	: 'Flyt hertil',

	// Dialogs
	RenameDlgTitle		: 'Omdøb',
	NewNameDlgTitle		: 'Nyt navn',
	FileExistsDlgTitle	: 'Filen eksisterer allerede',
	SysErrorDlgTitle : 'System fejl',

	FileOverwrite	: 'Overskriv',
	FileAutorename	: 'Auto-omdøb',

	// Generic
	OkBtn		: 'OK',
	CancelBtn	: 'Annullér',
	CloseBtn	: 'Luk',

	// Upload Panel
	UploadTitle			: 'Upload ny fil',
	UploadSelectLbl		: 'Vælg den fil, som du vil uploade',
	UploadProgressLbl	: '(Uploader, vent venligst...)',
	UploadBtn			: 'Upload filen',
	UploadBtnCancel		: 'Annuller',

	UploadNoFileMsg		: 'Vælg en fil på din computer.',
	UploadNoFolder		: 'Venligst vælg en mappe før upload startes.',
	UploadNoPerms		: 'Upload er ikke tilladt.',
	UploadUnknError		: 'Fejl ved upload.',
	UploadExtIncorrect	: 'Denne filtype er ikke tilladt i denne mappe.',

	// Flash Uploads
	UploadLabel			: 'Files to Upload', // MISSING
	UploadTotalFiles	: 'Total Files:', // MISSING
	UploadTotalSize		: 'Total Size:', // MISSING
	UploadSend			: 'Upload', // MISSING
	UploadAddFiles		: 'Add Files', // MISSING
	UploadClearFiles	: 'Clear Files', // MISSING
	UploadCancel		: 'Cancel Upload', // MISSING
	UploadRemove		: 'Remove', // MISSING
	UploadRemoveTip		: 'Remove !f', // MISSING
	UploadUploaded		: 'Uploaded !n%', // MISSING
	UploadProcessing	: 'Processing...', // MISSING

	// Settings Panel
	SetTitle		: 'Indstillinger',
	SetView			: 'Vis:',
	SetViewThumb	: 'Thumbnails',
	SetViewList		: 'Liste',
	SetDisplay		: 'Thumbnails:',
	SetDisplayName	: 'Filnavn',
	SetDisplayDate	: 'Dato',
	SetDisplaySize	: 'Størrelse',
	SetSort			: 'Sortering:',
	SetSortName		: 'efter filnavn',
	SetSortDate		: 'efter dato',
	SetSortSize		: 'efter størrelse',
	SetSortExtension		: 'by Extension', // MISSING

	// Status Bar
	FilesCountEmpty : '<tom mappe>',
	FilesCountOne	: '1 fil',
	FilesCountMany	: '%1 filer',

	// Size and Speed
	Kb				: '%1 kB',
	KbPerSecond		: '%1 kB/s',

	// Connector Error Messages.
	ErrorUnknown	: 'Det var ikke muligt at fuldføre handlingen. (Fejl: %1)',
	Errors :
	{
	 10 : 'Ugyldig handling.',
	 11 : 'Ressourcetypen blev ikke angivet i anmodningen.',
	 12 : 'Ressourcetypen er ikke gyldig.',
	102 : 'Ugyldig fil eller mappenavn.',
	103 : 'Det var ikke muligt at fuldføre handlingen på grund af en begrænsning i rettigheder.',
	104 : 'Det var ikke muligt at fuldføre handlingen på grund af en begrænsning i filsystem rettigheder.',
	105 : 'Ugyldig filtype.',
	109 : 'Ugyldig anmodning.',
	110 : 'Ukendt fejl.',
	115 : 'En fil eller mappe med det samme navn eksisterer allerede.',
	116 : 'Mappen blev ikke fundet. Opdatér listen eller prøv igen.',
	117 : 'Filen blev ikke fundet. Opdatér listen eller prøv igen.',
	118 : 'Originalplacering og destination er ens.',
	201 : 'En fil med det samme filnavn eksisterer allerede. Den uploadede fil er blevet omdøbt til "%1".',
	202 : 'Ugyldig fil.',
	203 : 'Ugyldig fil. Filstørrelsen er for stor.',
	204 : 'Den uploadede fil er korrupt.',
	205 : 'Der er ikke en midlertidig mappe til upload til rådighed på serveren.',
	206 : 'Upload annulleret af sikkerhedsmæssige årsager. Filen indeholder HTML-lignende data.',
	207 : 'Den uploadede fil er blevet omdøbt til "%1".',
	300 : 'Flytning af fil(er) fejlede.',
	301 : 'Kopiering af fil(er) fejlede.',
	500 : 'Filbrowseren er deaktiveret af sikkerhedsmæssige årsager. Kontakt systemadministratoren eller kontrollér CKFinders konfigurationsfil.',
	501 : 'Understøttelse af thumbnails er deaktiveret.'
	},

	// Other Error Messages.
	ErrorMsg :
	{
		FileEmpty		: 'Filnavnet må ikke være tomt.',
		FileExists		: 'Fil %erne eksisterer allerede.',
		FolderEmpty		: 'Mappenavnet må ikke være tomt.',

		FileInvChar		: 'Filnavnet må ikke indeholde et af følgende tegn: \n\\ / : * ? " < > |',
		FolderInvChar	: 'Mappenavnet må ikke indeholde et af følgende tegn: \n\\ / : * ? " < > |',

		PopupBlockView	: 'Det var ikke muligt at åbne filen i et nyt vindue. Kontrollér konfigurationen i din browser, og deaktivér eventuelle popup-blokkere for denne hjemmeside.',
		XmlError		: 'It was not possible to properly load the XML response from the web server.', // MISSING
		XmlEmpty		: 'It was not possible to load the XML response from the web server. The server returned an empty response.', // MISSING
		XmlRawResponse	: 'Raw response from the server: %s' // MISSING
	},

	// Imageresize plugin
	Imageresize :
	{
		dialogTitle		: 'Rediger størrelse %s',
		sizeTooBig		: 'Kan ikke ændre billedets højde eller bredde til en værdi større end dets originale størrelse (%size).',
		resizeSuccess	: 'Størrelsen er nu ændret.',
		thumbnailNew	: 'Opret ny thumbnail',
		thumbnailSmall	: 'Lille (%s)',
		thumbnailMedium	: 'Mellem (%s)',
		thumbnailLarge	: 'Stor (%s)',
		newSize			: 'Rediger størrelse',
		width			: 'Bredde',
		height			: 'Højde',
		invalidHeight	: 'Ugyldig højde.',
		invalidWidth	: 'Ugyldig bredde.',
		invalidName		: 'Ugyldigt filenavn.',
		newImage		: 'Opret nyt billede.',
		noExtensionChange : 'Filtypen kan ikke ændres.',
		imageSmall		: 'Originalfilen er for lille.',
		contextMenuName	: 'Rediger størrelse',
		lockRatio		: 'Lås størrelsesforhold',
		resetSize		: 'Nulstil størrelse'
	},

	// Fileeditor plugin
	Fileeditor :
	{
		save			: 'Gem',
		fileOpenError	: 'Filen kan ikke åbnes.',
		fileSaveSuccess	: 'Filen er nu gemt.',
		contextMenuName	: 'Rediger',
		loadingFile		: 'Henter fil, vent venligst...'
	},

	Maximize :
	{
		maximize : 'Maximér',
		minimize : 'Minimize' // MISSING
	}
};
