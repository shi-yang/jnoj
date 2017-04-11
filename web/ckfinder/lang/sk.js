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
 * @fileOverview Defines the {@link CKFinder.lang} object for the Slovak
 *		language.
 */

/**
 * Contains the dictionary of language entries.
 * @namespace
 */
CKFinder.lang['sk'] =
{
	appTitle : 'CKFinder',

	// Common messages and labels.
	common :
	{
		// Put the voice-only part of the label in the span.
		unavailable		: '%1<span class="cke_accessibility">, unavailable</span>', // MISSING
		confirmCancel	: 'Niektore možnosti boli zmenené. Naozaj chcete zavrieť okno?',
		ok				: 'OK',
		cancel			: 'Zrušiť',
		confirmationTitle	: 'Confirmation', // MISSING
		messageTitle	: 'Information', // MISSING
		inputTitle		: 'Question', // MISSING
		undo			: 'Späť',
		redo			: 'Znovu',
		skip			: 'Skip', // MISSING
		skipAll			: 'Skip all', // MISSING
		makeDecision	: 'What action should be taken?', // MISSING
		rememberDecision: 'Remember my decision' // MISSING
	},


	// Language direction, 'ltr' or 'rtl'.
	dir : 'ltr',
	HelpLang : 'en',
	LangCode : 'sk',

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
	DateTime : 'mm/dd/yyyy HH:MM',
	DateAmPm : ['AM', 'PM'],

	// Folders
	FoldersTitle	: 'Adresáre',
	FolderLoading	: 'Nahrávam...',
	FolderNew		: 'Zadajte prosím meno nového adresára: ',
	FolderRename	: 'Zadajte prosím meno nového adresára: ',
	FolderDelete	: 'Skutočne zmazať adresár "%1"?',
	FolderRenaming	: ' (Prebieha premenovanie adresára...)',
	FolderDeleting	: ' (Prebieha zmazanie adresára...)',

	// Files
	FileRename		: 'Zadajte prosím meno nového súboru: ',
	FileRenameExt	: 'Skutočne chcete zmeniť príponu súboru? Upozornenie: zmenou prípony sa súbor môže stať nepoužiteľným, pokiaľ prípona nie je podporovaná.',
	FileRenaming	: 'Prebieha premenovanie súboru...',
	FileDelete		: 'Skutočne chcete odstrániť súbor "%1"?',
	FilesLoading	: 'Nahrávam...',
	FilesEmpty		: 'The folder is empty.', // MISSING
	FilesMoved		: 'File %1 moved to %2:%3.', // MISSING
	FilesCopied		: 'File %1 copied to %2:%3.', // MISSING

	// Basket
	BasketFolder		: 'Basket', // MISSING
	BasketClear			: 'Clear Basket', // MISSING
	BasketRemove		: 'Remove from Basket', // MISSING
	BasketOpenFolder	: 'Open Parent Folder', // MISSING
	BasketTruncateConfirm : 'Do you really want to remove all files from the basket?', // MISSING
	BasketRemoveConfirm	: 'Do you really want to remove the file "%1" from the basket?', // MISSING
	BasketEmpty			: 'No files in the basket, drag and drop some.', // MISSING
	BasketCopyFilesHere	: 'Copy Files from Basket', // MISSING
	BasketMoveFilesHere	: 'Move Files from Basket', // MISSING

	BasketPasteErrorOther	: 'File %s error: %e', // MISSING
	BasketPasteMoveSuccess	: 'The following files were moved: %s', // MISSING
	BasketPasteCopySuccess	: 'The following files were copied: %s', // MISSING

	// Toolbar Buttons (some used elsewhere)
	Upload		: 'Prekopírovať na server (Upload)',
	UploadTip	: 'Prekopírovať nový súbor',
	Refresh		: 'Znovunačítať (Refresh)',
	Settings	: 'Nastavenia',
	Help		: 'Pomoc',
	HelpTip		: 'Pomoc',

	// Context Menus
	Select			: 'Vybrať',
	SelectThumbnail : 'Select Thumbnail', // MISSING
	View			: 'Náhľad',
	Download		: 'Stiahnuť',

	NewSubFolder	: 'Nový podadresár',
	Rename			: 'Premenovať',
	Delete			: 'Zmazať',

	CopyDragDrop	: 'Copy File Here', // MISSING
	MoveDragDrop	: 'Move File Here', // MISSING

	// Dialogs
	RenameDlgTitle		: 'Rename', // MISSING
	NewNameDlgTitle		: 'New Name', // MISSING
	FileExistsDlgTitle	: 'File Already Exists', // MISSING
	SysErrorDlgTitle : 'System Error', // MISSING

	FileOverwrite	: 'Overwrite', // MISSING
	FileAutorename	: 'Auto-rename', // MISSING

	// Generic
	OkBtn		: 'OK',
	CancelBtn	: 'Zrušiť',
	CloseBtn	: 'Zatvoriť',

	// Upload Panel
	UploadTitle			: 'Nahrať nový súbor',
	UploadSelectLbl		: 'Vyberte súbor, ktorý chcete prekopírovať na server',
	UploadProgressLbl	: '(Prebieha kopírovanie, čakajte prosím...)',
	UploadBtn			: 'Prekopírovať vybratý súbor',
	UploadBtnCancel		: 'Zrušiť',

	UploadNoFileMsg		: 'Vyberte prosím súbor na Vašom počítači!',
	UploadNoFolder		: 'Please select a folder before uploading.', // MISSING
	UploadNoPerms		: 'File upload not allowed.', // MISSING
	UploadUnknError		: 'Error sending the file.', // MISSING
	UploadExtIncorrect	: 'File extension not allowed in this folder.', // MISSING

	// Flash Uploads
	UploadLabel			: 'Files to Upload', // MISSING
	UploadTotalFiles	: 'Total Files:', // MISSING
	UploadTotalSize		: 'Total Size:', // MISSING
	UploadSend			: 'Prekopírovať na server',
	UploadAddFiles		: 'Add Files', // MISSING
	UploadClearFiles	: 'Clear Files', // MISSING
	UploadCancel		: 'Cancel Upload', // MISSING
	UploadRemove		: 'Remove', // MISSING
	UploadRemoveTip		: 'Remove !f', // MISSING
	UploadUploaded		: 'Uploaded !n%', // MISSING
	UploadProcessing	: 'Processing...', // MISSING

	// Settings Panel
	SetTitle		: 'Nastavenia',
	SetView			: 'Náhľad:',
	SetViewThumb	: 'Miniobrázky',
	SetViewList		: 'Zoznam',
	SetDisplay		: 'Zobraziť:',
	SetDisplayName	: 'Názov súboru',
	SetDisplayDate	: 'Dátum',
	SetDisplaySize	: 'Veľkosť súboru',
	SetSort			: 'Zoradenie:',
	SetSortName		: 'podľa názvu súboru',
	SetSortDate		: 'podľa dátumu',
	SetSortSize		: 'podľa veľkosti',
	SetSortExtension		: 'by Extension', // MISSING

	// Status Bar
	FilesCountEmpty : '<Prázdny adresár>',
	FilesCountOne	: '1 súbor',
	FilesCountMany	: '%1 súborov',

	// Size and Speed
	Kb				: '%1 kB',
	KbPerSecond		: '%1 kB/s',

	// Connector Error Messages.
	ErrorUnknown	: 'Server nemohol dokončiť spracovanie požiadavky. (Chyba %1)',
	Errors :
	{
	 10 : 'Neplatný príkaz.',
	 11 : 'V požiadavke nebol špecifikovaný typ súboru.',
	 12 : 'Nepodporovaný typ súboru.',
	102 : 'Neplatný názov súboru alebo adresára.',
	103 : 'Nebolo možné dokončiť spracovanie požiadavky kvôli nepostačujúcej úrovni oprávnení.',
	104 : 'Nebolo možné dokončiť spracovanie požiadavky kvôli obmedzeniam v prístupových právach ku súborom.',
	105 : 'Neplatná prípona súboru.',
	109 : 'Neplatná požiadavka.',
	110 : 'Neidentifikovaná chyba.',
	115 : 'Zadaný súbor alebo adresár už existuje.',
	116 : 'Adresár nebol nájdený. Aktualizujte obsah adresára (Znovunačítať) a skúste znovu.',
	117 : 'Súbor nebol nájdený. Aktualizujte obsah adresára (Znovunačítať) a skúste znovu.',
	118 : 'Source and target paths are equal.', // MISSING
	201 : 'Súbor so zadaným názvom už existuje. Prekopírovaný súbor bol premenovaný na "%1".',
	202 : 'Neplatný súbor.',
	203 : 'Neplatný súbor - súbor presahuje maximálnu povolenú veľkosť.',
	204 : 'Kopírovaný súbor je poškodený.',
	205 : 'Server nemá špecifikovaný dočasný adresár pre kopírované súbory.',
	206 : 'Kopírovanie prerušené kvôli nedostatočnému zabezpečeniu. Súbor obsahuje HTML data.',
	207 : 'Prekopírovaný súbor bol premenovaný na "%1".',
	300 : 'Moving file(s) failed.', // MISSING
	301 : 'Copying file(s) failed.', // MISSING
	500 : 'Prehliadanie súborov je zakázané kvôli bezpečnosti. Kontaktujte prosím administrátora a overte nastavenia v konfiguračnom súbore pre CKFinder.',
	501 : 'Momentálne nie je zapnutá podpora pre generáciu miniobrázkov.'
	},

	// Other Error Messages.
	ErrorMsg :
	{
		FileEmpty		: 'Názov súbor nesmie prázdny.',
		FileExists		: 'File %s already exists.', // MISSING
		FolderEmpty		: 'Názov adresára nesmie byť prázdny.',

		FileInvChar		: 'Súbor nesmie obsahovať žiadny z nasledujúcich znakov: \n\\ / : * ? " < > |',
		FolderInvChar	: 'Adresár nesmie obsahovať žiadny z nasledujúcich znakov: \n\\ / : * ? " < > |',

		PopupBlockView	: 'Nebolo možné otvoriť súbor v novom okne. Overte nastavenia Vášho prehliadača a zakážte všetky blokovače popup okien pre túto webstránku.',
		XmlError		: 'It was not possible to properly load the XML response from the web server.', // MISSING
		XmlEmpty		: 'It was not possible to load the XML response from the web server. The server returned an empty response.', // MISSING
		XmlRawResponse	: 'Raw response from the server: %s' // MISSING
	},

	// Imageresize plugin
	Imageresize :
	{
		dialogTitle		: 'Resize %s', // MISSING
		sizeTooBig		: 'Cannot set image height or width to a value bigger than the original size (%size).', // MISSING
		resizeSuccess	: 'Image resized successfully.', // MISSING
		thumbnailNew	: 'Create a new thumbnail', // MISSING
		thumbnailSmall	: 'Small (%s)', // MISSING
		thumbnailMedium	: 'Medium (%s)', // MISSING
		thumbnailLarge	: 'Large (%s)', // MISSING
		newSize			: 'Set a new size', // MISSING
		width			: 'Šírka',
		height			: 'Výška',
		invalidHeight	: 'Invalid height.', // MISSING
		invalidWidth	: 'Invalid width.', // MISSING
		invalidName		: 'Invalid file name.', // MISSING
		newImage		: 'Create a new image', // MISSING
		noExtensionChange : 'File extension cannot be changed.', // MISSING
		imageSmall		: 'Source image is too small.', // MISSING
		contextMenuName	: 'Resize', // MISSING
		lockRatio		: 'Zámok',
		resetSize		: 'Pôvodná veľkosť'
	},

	// Fileeditor plugin
	Fileeditor :
	{
		save			: 'Uložiť',
		fileOpenError	: 'Unable to open file.', // MISSING
		fileSaveSuccess	: 'File saved successfully.', // MISSING
		contextMenuName	: 'Edit', // MISSING
		loadingFile		: 'Loading file, please wait...' // MISSING
	},

	Maximize :
	{
		maximize : 'Maximalizovať',
		minimize : 'Minimalizovať'
	}
};
