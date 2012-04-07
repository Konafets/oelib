<?php

########################################################################
# Extension Manager/Repository config file for ext "oelib".
#
# Auto generated 08-04-2012 00:20
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'One is Enough Library',
	'description' => 'This extension provides useful stuff for extension development: helper functions for unit testing, templating, automatic configuration checks and performance benchmarking.',
	'category' => 'services',
	'author' => 'Oliver Klee',
	'author_email' => 'typo3-coding@oliverklee.de',
	'shy' => 0,
	'dependencies' => 'static_info_tables',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 1,
	'createDirs' => '',
	'modify_tables' => 'be_users,be_groups,fe_groups,fe_users,pages,sys_template,tt_content',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'author_company' => '',
	'version' => '0.7.64',
	'_md5_values_when_last_written' => 'a:99:{s:8:"Time.php";s:4:"28a0";s:13:"changelog.txt";s:4:"ca32";s:29:"class.tx_oelib_Attachment.php";s:4:"97ea";s:29:"class.tx_oelib_Autoloader.php";s:4:"b4bb";s:38:"class.tx_oelib_BackEndLoginManager.php";s:4:"8e23";s:32:"class.tx_oelib_Configuration.php";s:4:"1a59";s:40:"class.tx_oelib_ConfigurationRegistry.php";s:4:"e459";s:29:"class.tx_oelib_DataMapper.php";s:4:"3cf8";s:35:"class.tx_oelib_Double3Validator.php";s:4:"5281";s:30:"class.tx_oelib_FakeSession.php";s:4:"f74e";s:32:"class.tx_oelib_FileFunctions.php";s:4:"e7bc";s:39:"class.tx_oelib_FrontEndLoginManager.php";s:4:"e53f";s:30:"class.tx_oelib_IdentityMap.php";s:4:"9a03";s:23:"class.tx_oelib_List.php";s:4:"e30c";s:23:"class.tx_oelib_Mail.php";s:4:"67a8";s:33:"class.tx_oelib_MapperRegistry.php";s:4:"bc9c";s:24:"class.tx_oelib_Model.php";s:4:"c345";s:25:"class.tx_oelib_Object.php";s:4:"1780";s:32:"class.tx_oelib_ObjectFactory.php";s:4:"a302";s:29:"class.tx_oelib_PageFinder.php";s:4:"0444";s:31:"class.tx_oelib_PublicObject.php";s:4:"1f97";s:26:"class.tx_oelib_Session.php";s:4:"9030";s:27:"class.tx_oelib_Template.php";s:4:"d0d5";s:35:"class.tx_oelib_TemplateRegistry.php";s:4:"4465";s:42:"class.tx_oelib_TestingFrameworkCleanup.php";s:4:"1b35";s:24:"class.tx_oelib_Timer.php";s:4:"6968";s:29:"class.tx_oelib_Translator.php";s:4:"bb4c";s:37:"class.tx_oelib_TranslatorRegistry.php";s:4:"2fba";s:38:"class.tx_oelib_abstractHeaderProxy.php";s:4:"88f4";s:33:"class.tx_oelib_abstractMailer.php";s:4:"d42e";s:30:"class.tx_oelib_configcheck.php";s:4:"a631";s:37:"class.tx_oelib_configurationProxy.php";s:4:"1585";s:21:"class.tx_oelib_db.php";s:4:"8ad8";s:33:"class.tx_oelib_emailCollector.php";s:4:"f9cd";s:34:"class.tx_oelib_headerCollector.php";s:4:"ef16";s:37:"class.tx_oelib_headerProxyFactory.php";s:4:"d9aa";s:32:"class.tx_oelib_mailerFactory.php";s:4:"7b3f";s:34:"class.tx_oelib_realHeaderProxy.php";s:4:"1bbb";s:29:"class.tx_oelib_realMailer.php";s:4:"6403";s:37:"class.tx_oelib_salutationswitcher.php";s:4:"c790";s:33:"class.tx_oelib_templatehelper.php";s:4:"ea45";s:35:"class.tx_oelib_testingFramework.php";s:4:"969b";s:16:"ext_autoload.php";s:4:"a7e0";s:12:"ext_icon.gif";s:4:"b4bf";s:17:"ext_localconf.php";s:4:"dce8";s:14:"ext_tables.php";s:4:"4839";s:14:"ext_tables.sql";s:4:"3065";s:22:"icon_tx_oelib_test.gif";s:4:"bd58";s:16:"locallang_db.xml";s:4:"a70b";s:7:"tca.php";s:4:"6a0e";s:8:"todo.txt";s:4:"d400";s:28:"tx_oelib_commonConstants.php";s:4:"7913";s:32:"Classes/Domain/Model/Country.php";s:4:"937d";s:47:"Classes/Domain/Repository/CountryRepository.php";s:4:"964c";s:44:"Classes/ViewHelpers/GoogleMapsViewHelper.php";s:4:"a86f";s:45:"Classes/ViewHelpers/ImageSourceViewHelper.php";s:4:"4167";s:43:"Classes/ViewHelpers/UppercaseViewHelper.php";s:4:"2a66";s:38:"Configuration/TypoScript/constants.txt";s:4:"d41d";s:34:"Configuration/TypoScript/setup.txt";s:4:"50d6";s:51:"Exception/class.tx_oelib_Exception_AccessDenied.php";s:4:"0670";s:47:"Exception/class.tx_oelib_Exception_Database.php";s:4:"bf6b";s:55:"Exception/class.tx_oelib_Exception_EmptyQueryResult.php";s:4:"59eb";s:47:"Exception/class.tx_oelib_Exception_NotFound.php";s:4:"56aa";s:24:"Geocoding/Calculator.php";s:4:"3ab8";s:19:"Geocoding/Dummy.php";s:4:"b2fb";s:20:"Geocoding/Google.php";s:4:"5047";s:29:"Interface/GeocodingLookup.php";s:4:"d902";s:22:"Interface/Identity.php";s:4:"8a35";s:22:"Interface/MapPoint.php";s:4:"b747";s:22:"Interface/Sortable.php";s:4:"f347";s:46:"Interface/class.tx_oelib_Interface_Address.php";s:4:"d329";s:42:"Interface/class.tx_oelib_Interface_Geo.php";s:4:"0f98";s:51:"Interface/class.tx_oelib_Interface_LoginManager.php";s:4:"82af";s:47:"Interface/class.tx_oelib_Interface_MailRole.php";s:4:"35a0";s:44:"Mapper/class.tx_oelib_Mapper_BackEndUser.php";s:4:"2752";s:49:"Mapper/class.tx_oelib_Mapper_BackEndUserGroup.php";s:4:"f1d7";s:40:"Mapper/class.tx_oelib_Mapper_Country.php";s:4:"ebc4";s:41:"Mapper/class.tx_oelib_Mapper_Currency.php";s:4:"cca9";s:45:"Mapper/class.tx_oelib_Mapper_FrontEndUser.php";s:4:"585f";s:50:"Mapper/class.tx_oelib_Mapper_FrontEndUserGroup.php";s:4:"5b33";s:41:"Mapper/class.tx_oelib_Mapper_Language.php";s:4:"d023";s:42:"Model/class.tx_oelib_Model_BackEndUser.php";s:4:"f7ec";s:47:"Model/class.tx_oelib_Model_BackEndUserGroup.php";s:4:"addb";s:38:"Model/class.tx_oelib_Model_Country.php";s:4:"5575";s:39:"Model/class.tx_oelib_Model_Currency.php";s:4:"0968";s:43:"Model/class.tx_oelib_Model_FrontEndUser.php";s:4:"9965";s:48:"Model/class.tx_oelib_Model_FrontEndUserGroup.php";s:4:"ddb3";s:39:"Model/class.tx_oelib_Model_Language.php";s:4:"a79d";s:40:"Resources/Private/Language/locallang.xml";s:4:"0494";s:46:"ViewHelper/class.tx_oelib_ViewHelper_Price.php";s:4:"7306";s:45:"Visibility/class.tx_oelib_Visibility_Node.php";s:4:"a1d0";s:45:"Visibility/class.tx_oelib_Visibility_Tree.php";s:4:"f949";s:21:"contrib/PEAR/PEAR.php";s:4:"1fc7";s:22:"contrib/PEAR/PEAR5.php";s:4:"3605";s:26:"contrib/PEAR/Mail/mime.php";s:4:"55c7";s:30:"contrib/PEAR/Mail/mimePart.php";s:4:"692d";s:30:"contrib/emogrifier/LICENSE.TXT";s:4:"8403";s:33:"contrib/emogrifier/emogrifier.php";s:4:"b39c";s:14:"doc/manual.sxw";s:4:"1035";}',
	'constraints' => array(
		'depends' => array(
			'php' => '5.2.0-0.0.0',
			'typo3' => '4.4.0-0.0.0',
			'static_info_tables' => '2.0.8-',
		),
		'conflicts' => array(
		),
		'suggests' => array(
			'extbase' => '1.3.0-0.0.0',
			'fluid' => '1.3.0-0.0.0',
		),
	),
	'suggests' => array(
		'extbase' => '1.3.0-0.0.0',
		'fluid' => '1.3.0-0.0.0',
	),
);

?>