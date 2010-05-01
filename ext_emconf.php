<?php

########################################################################
# Extension Manager/Repository config file for ext "oelib".
#
# Auto generated 09-04-2010 15:33
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
	'version' => '0.7.51',
	'_md5_values_when_last_written' => 'a:82:{s:13:"changelog.txt";s:4:"29ea";s:29:"class.tx_oelib_Attachment.php";s:4:"f260";s:29:"class.tx_oelib_Autoloader.php";s:4:"df52";s:38:"class.tx_oelib_BackEndLoginManager.php";s:4:"3de6";s:32:"class.tx_oelib_Configuration.php";s:4:"943d";s:40:"class.tx_oelib_ConfigurationRegistry.php";s:4:"b236";s:29:"class.tx_oelib_DataMapper.php";s:4:"7082";s:35:"class.tx_oelib_Double3Validator.php";s:4:"5992";s:30:"class.tx_oelib_FakeSession.php";s:4:"1030";s:32:"class.tx_oelib_FileFunctions.php";s:4:"ac36";s:39:"class.tx_oelib_FrontEndLoginManager.php";s:4:"5c19";s:30:"class.tx_oelib_IdentityMap.php";s:4:"e809";s:23:"class.tx_oelib_List.php";s:4:"02bf";s:23:"class.tx_oelib_Mail.php";s:4:"7914";s:33:"class.tx_oelib_MapperRegistry.php";s:4:"7839";s:24:"class.tx_oelib_Model.php";s:4:"f982";s:25:"class.tx_oelib_Object.php";s:4:"6fe6";s:32:"class.tx_oelib_ObjectFactory.php";s:4:"ab5d";s:29:"class.tx_oelib_PageFinder.php";s:4:"b17b";s:31:"class.tx_oelib_PublicObject.php";s:4:"29a2";s:26:"class.tx_oelib_Session.php";s:4:"9f6f";s:27:"class.tx_oelib_Template.php";s:4:"ced3";s:35:"class.tx_oelib_TemplateRegistry.php";s:4:"e256";s:24:"class.tx_oelib_Timer.php";s:4:"c0d5";s:29:"class.tx_oelib_Translator.php";s:4:"2e15";s:37:"class.tx_oelib_TranslatorRegistry.php";s:4:"f9e9";s:38:"class.tx_oelib_abstractHeaderProxy.php";s:4:"70a3";s:33:"class.tx_oelib_abstractMailer.php";s:4:"bdcb";s:30:"class.tx_oelib_configcheck.php";s:4:"ca38";s:37:"class.tx_oelib_configurationProxy.php";s:4:"d558";s:21:"class.tx_oelib_db.php";s:4:"618f";s:33:"class.tx_oelib_emailCollector.php";s:4:"149c";s:34:"class.tx_oelib_headerCollector.php";s:4:"51c9";s:37:"class.tx_oelib_headerProxyFactory.php";s:4:"54e1";s:32:"class.tx_oelib_mailerFactory.php";s:4:"4af9";s:34:"class.tx_oelib_realHeaderProxy.php";s:4:"633a";s:29:"class.tx_oelib_realMailer.php";s:4:"b7d9";s:37:"class.tx_oelib_salutationswitcher.php";s:4:"40be";s:33:"class.tx_oelib_templatehelper.php";s:4:"8da4";s:35:"class.tx_oelib_testingFramework.php";s:4:"0e52";s:16:"ext_autoload.php";s:4:"46d7";s:12:"ext_icon.gif";s:4:"b4bf";s:17:"ext_localconf.php";s:4:"1793";s:14:"ext_tables.php";s:4:"2297";s:14:"ext_tables.sql";s:4:"9109";s:22:"icon_tx_oelib_test.gif";s:4:"bd58";s:16:"locallang_db.xml";s:4:"a70b";s:7:"tca.php";s:4:"9877";s:8:"todo.txt";s:4:"d400";s:28:"tx_oelib_commonConstants.php";s:4:"0182";s:51:"Exception/class.tx_oelib_Exception_AccessDenied.php";s:4:"369f";s:47:"Exception/class.tx_oelib_Exception_Database.php";s:4:"cf4d";s:55:"Exception/class.tx_oelib_Exception_EmptyQueryResult.php";s:4:"3ecd";s:47:"Exception/class.tx_oelib_Exception_NotFound.php";s:4:"693e";s:46:"Interface/class.tx_oelib_Interface_Address.php";s:4:"6250";s:51:"Interface/class.tx_oelib_Interface_LoginManager.php";s:4:"f3d7";s:47:"Interface/class.tx_oelib_Interface_MailRole.php";s:4:"dd45";s:44:"Mapper/class.tx_oelib_Mapper_BackEndUser.php";s:4:"831c";s:49:"Mapper/class.tx_oelib_Mapper_BackEndUserGroup.php";s:4:"779e";s:40:"Mapper/class.tx_oelib_Mapper_Country.php";s:4:"09b2";s:41:"Mapper/class.tx_oelib_Mapper_Currency.php";s:4:"d001";s:45:"Mapper/class.tx_oelib_Mapper_FrontEndUser.php";s:4:"b6f3";s:50:"Mapper/class.tx_oelib_Mapper_FrontEndUserGroup.php";s:4:"c900";s:41:"Mapper/class.tx_oelib_Mapper_Language.php";s:4:"15c4";s:42:"Model/class.tx_oelib_Model_BackEndUser.php";s:4:"b72e";s:47:"Model/class.tx_oelib_Model_BackEndUserGroup.php";s:4:"067f";s:38:"Model/class.tx_oelib_Model_Country.php";s:4:"1d71";s:39:"Model/class.tx_oelib_Model_Currency.php";s:4:"8d9a";s:43:"Model/class.tx_oelib_Model_FrontEndUser.php";s:4:"c431";s:48:"Model/class.tx_oelib_Model_FrontEndUserGroup.php";s:4:"eacc";s:39:"Model/class.tx_oelib_Model_Language.php";s:4:"11c4";s:40:"Resources/Private/Language/locallang.xml";s:4:"0494";s:46:"ViewHelper/class.tx_oelib_ViewHelper_Price.php";s:4:"b417";s:45:"Visibility/class.tx_oelib_Visibility_Node.php";s:4:"b54e";s:45:"Visibility/class.tx_oelib_Visibility_Tree.php";s:4:"b1da";s:21:"contrib/PEAR/PEAR.php";s:4:"9e9a";s:22:"contrib/PEAR/PEAR5.php";s:4:"2107";s:26:"contrib/PEAR/Mail/mime.php";s:4:"29a2";s:30:"contrib/PEAR/Mail/mimePart.php";s:4:"c575";s:30:"contrib/emogrifier/LICENSE.TXT";s:4:"8403";s:33:"contrib/emogrifier/emogrifier.php";s:4:"f03e";s:14:"doc/manual.sxw";s:4:"cc75";}',
	'constraints' => array(
		'depends' => array(
			'php' => '5.2.0-0.0.0',
			'typo3' => '4.3.0-0.0.0',
			'static_info_tables' => '2.0.8-',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
);

?>