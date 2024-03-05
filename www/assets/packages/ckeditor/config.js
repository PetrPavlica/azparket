/**
 * @license Copyright (c) 2003-2022, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	config.defaultLanguage = 'cs';
	config.language = 'cs';
	config.autoGrow_onStartup = true;
	config.autoGrow_maxHeight = 850;
	
	config.filebrowserBrowseUrl = PACKAGES + '/elFinder/elfinder-cke.html';
    config.allowedContent = true;
    config.entities  = false;
    config.basicEntities = true;
    config.entities_greek = false;
    config.entities_latin = false;
    config.baseHref = BASE_PATH + '/';
    config.contentsCss = [ BASE_PATH + '/assets/css/app.css', CKEDITOR.basePath + 'contents.css'];
};
