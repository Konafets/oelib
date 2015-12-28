

.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. ==================================================
.. DEFINE SOME TEXTROLES
.. --------------------------------------------------
.. role::   underline
.. role::   typoscript(code)
.. role::   ts(typoscript)
   :class:  typoscript
.. role::   php(code)


Deprecated classes and functions
--------------------------------

Functions that are removed in the current released version are marked
as bold.

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Classmethod
         Class::method

   Successor
         Successor

   Deprecated
         Deprecated

   Removed
         Removed


.. container:: table-row

   Classmethod
         tx\_oelib\_templatehelper::substituteMarkerArrayCached

   Successor
         getSubpart

   Deprecated
         2007-08-22

   Removed
         0.5.0


.. container:: table-row

   Classmethod
         tx\_oelib\_templatehelper::readSubpartsToHide

   Successor
         hideSubparts

   Deprecated
         2007-08-22

   Removed
         0.5.0


.. container:: table-row

   Classmethod
         tx\_oelib\_templatehelper::readSubpartsToUnhide

   Successor
         unhideSubparts

   Deprecated
         2007-08-22

   Removed
         0.5.0


.. container:: table-row

   Classmethod
         tx\_oelib\_salutationswitcher::pi\_getLL

   Successor
         translate

   Deprecated
         2007-08-22

   Removed
         0.7.0


.. container:: table-row

   Classmethod
         tx\_oelib\_templatehelper::setMarkerContent

   Successor
         setMarker

   Deprecated
         2007-12-09

   Removed
         0.5.0


.. container:: table-row

   Classmethod
         tx\_oelib\_templatehelper::setSubpartContent

   Successor
         setSubpart

   Deprecated
         2007-12-09

   Removed
         0.5.0


.. container:: table-row

   Classmethod
         tx\_oelib\_templatehelper::addCssToPageHeader

   Successor
         (CSS set via constants)

   Deprecated
         2007-12-14

   Removed
         0.5.0


.. container:: table-row

   Classmethod
         tx\_oelib\_configcheck::checkCssFile

   Successor
         checkCssFileFromConstants

   Deprecated
         2007-12-15

   Removed
         0.7.0


.. container:: table-row

   Classmethod
         tx\_oelib\_templatehelper::securePiVars

   Successor
         ensureIntegerPiVars

   Deprecated
         2008-08-24

   Removed
         0.7.0


.. container:: table-row

   Classmethod
         tx\_oelib\_templatehelper::enableFields

   Successor
         tx\_oelib\_db::enableFields

   Deprecated
         2008-09-21

   Removed
         0.7.0


.. container:: table-row

   Classmethod
         tx\_oelib\_templatehelper::createRecursivePageList

   Successor
         tx\_oelib\_db::createRecursivePageList

   Deprecated
         2008-10-04

   Removed
         0.7.0


.. container:: table-row

   Classmethod
         tx\_oelib\_testingFramework::getAssociativeDatabaseResult

   Successor
         tx\_oelib\_db::selectSingle

   Deprecated
         2009-01-25

   Removed
         0.7.0


.. container:: table-row

   Classmethod
         tx\_oelib\_headerProxyFactory::discardInstance

   Successor
         tx\_oelib\_headerProxyFactory::purgeInstance

   Deprecated
         2009-02-04

   Removed
         0.7.0


.. container:: table-row

   Classmethod
         tx\_oelib\_mailerFactory::discardInstance

   Successor
         tx\_oelib\_mailerFactory::purgeInstance

   Deprecated
         2009-02-04

   Removed
         0.7.0


.. container:: table-row

   Classmethod
         tx\_oelib\_testingFramework::createPageCacheEntry

   Successor


   Deprecated
         2009-03-30

   Removed
         0.7.0


.. container:: table-row

   Classmethod
         tx\_oelib\_templatehelper::isLoggedIn

   Successor
         tx\_oelib\_FrontEndLoginManager::isLoggedIn

   Deprecated
         2009-02-06

   Removed
         0.8.0


.. container:: table-row

   Classmethod
         tx\_oelib\_Mapper\_FrontEndUser::getLoggedInUser

   Successor
         tx\_oelib\_FrontEndLoginManager::getLoggedInUser

   Deprecated
         2009-03-02

   Removed
         0.8.0


.. container:: table-row

   Classmethod
         tx\_oelib\_configurationProxy::getConfigurationValue

   Successor
         tx\_oelib\_configurationProxy::get

   Deprecated
         2009-06-12

   Removed
         0.8.0


.. container:: table-row

   Classmethod
         tx\_oelib\_configurationProxy::setConfigurationValue

   Successor
         tx\_oelib\_configurationProxy::set

   Deprecated
         2009-06-12

   Removed
         0.8.0


.. container:: table-row

   Classmethod
         tx\_oelib\_configurationProxy::getConfigurationValueString

   Successor
         tx\_oelib\_configurationProxy::getAsString

   Deprecated
         2009-06-12

   Removed
         0.8.0


.. container:: table-row

   Classmethod
         tx\_oelib\_configurationProxy::setConfigurationValueString

   Successor
         tx\_oelib\_configurationProxy::setAsString

   Deprecated
         2009-06-12

   Removed
         0.8.0


.. container:: table-row

   Classmethod
         tx\_oelib\_configurationProxy::getConfigurationValueBoolean

   Successor
         tx\_oelib\_configurationProxy::getAsBoolean

   Deprecated
         2009-06-12

   Removed
         0.8.0


.. container:: table-row

   Classmethod
         tx\_oelib\_configurationProxy::setConfigurationValueBoolean

   Successor
         tx\_oelib\_configurationProxy::setAsBoolean

   Deprecated
         2009-06-12

   Removed
         0.8.0


.. container:: table-row

   Classmethod
         tx\_oelib\_configurationProxy::getConfigurationValueInteger

   Successor
         tx\_oelib\_configurationProxy::getAsInteger

   Deprecated
         2009-06-12

   Removed
         0.8.0


.. container:: table-row

   Classmethod
         tx\_oelib\_configurationProxy::setConfigurationValueInteger

   Successor
         tx\_oelib\_configurationProxy::setAsInteger

   Deprecated
         2009-06-12

   Removed
         0.8.0


.. container:: table-row

   Classmethod
         tx\_oelib\_List::appendUnique

   Successor
         tx\_oelib\_List::append

   Deprecated
         2010-05-27

   Removed
         0.9.0


.. container:: table-row

   Classmethod
         tx\_oelib\_FileFunctions::rmdir

   Successor
         t3lib\_div::rmdir

   Deprecated
         2010-07-22

   Removed
         0.9.0


.. container:: table-row

   Classmethod
         tx\_oelib\_templatehelper::setLocaleConvention

   Successor


   Deprecated
         2010-09-23

   Removed
         0.9.0


.. container:: table-row

   Classmethod
         tx\_oelib\_templatehelper::pi\_getPidList

   Successor
         tx\_oelib\_db::createRecursivePageList

   Deprecated
         2013-02-09

   Removed
         0.9.0


.. container:: table-row

   Classmethod
         tx\_oelib\_templatehelper::getFeUserUid

   Successor
         tx\_oelib\_FrontEndLoginManager::getLoggedInUser

   Deprecated
         2010-10-11

   Removed
         0.9.0 + 1


.. container:: table-row

   Classmethod
         Tx\_Oelib\_ObjectFactory::make

   Successor
         t3lib\_div::makeInstance

   Deprecated
         2014-04-11

   Removed
         0.9.0 + 1


.. container:: table-row

   Classmethod
         Tx\_Oelib\_Timer

   Successor


   Deprecated
         2014-04-12

   Removed
         0.9.0 + 1


.. container:: table-row

   Classmethod
         Tx\_Oelib\_AbstractMailer::sendEmail

   Successor
         Tx\_Oelib\_AbstractMailer::send

   Deprecated
         2014-08-28

   Removed
         0.9.0 + 1


.. container:: table-row

   Classmethod
         Tx\_Oelib\_AbstractMailer::mail

   Successor
         Tx\_Oelib\_AbstractMailer::send

   Deprecated
         2014-08-28

   Removed
         0.9.0 + 1


.. container:: table-row

   Classmethod
         Tx\_Oelib\_AbstractMailer::checkParameters

   Successor


   Deprecated
         2014-08-28

   Removed
         0.9.0 + 1


.. container:: table-row

   Classmethod
         Tx\_Oelib\_TemplateHelper::createRestrictedImage

   Successor
         tslib\_cObj::IMAGE

   Deprecated
         2014-09-01

   Removed
         0.9.0 + 1


.. container:: table-row

   Classmethod
         Tx\_Oelib\_TemplateHelper::getFeUserUid

   Successor
         Tx\_Oelib\_FrontEndLoginManager::getLoggedInUser

   Deprecated
         2014-09-01

   Removed
         0.9.0 + 1


.. container:: table-row

   Classmethod
         Tx\_Oelib\_ConfigCheck::checkLocale

   Successor

   Deprecated
         2015-01-12

   Removed
         0.9.0 + 1

.. container:: table-row

   Classmethod
         Tx\_Oelib\_TemplateHelper::checkCss

   Successor

   Deprecated
         2015-02-28

   Removed
         0.9.0 + 1

.. container:: table-row

   Classmethod
         Tx\_Oelib\_ConfigCheck::checkCssClassNames

   Successor

   Deprecated
         2015-03-01

   Removed
         0.9.0 + 1

.. container:: table-row

   Classmethod
         Tx\_Oelib\_ConfigCheck::checkCssStyledContent

   Successor

   Deprecated
         2015-12-28

   Removed
         0.9.0 + 2

.. container:: table-row

   Classmethod
         Tx\_Oelib\_Template::getPrefixedMarkers

   Successor

   Deprecated
         2015-03-01

   Removed
         0.9.0 + 2

.. container:: table-row

   Classmethod
         Tx\_Oelib\_TemplateHelper::getPrefixedMarkers

   Successor

   Deprecated
         2015-03-01

   Removed
         0.9.0 + 2

.. container:: table-row

   Classmethod
         Tx\_Oelib\_TemplateHelper::getStoragePid

   Successor

   Deprecated
         2015-12-28

   Removed
         0.9.0 + 2

.. container:: table-row

   Classmethod
         Tx\_Oelib\_TemplateHelper::hasStoragePid

   Successor

   Deprecated
         2015-12-28

   Removed
         0.9.0 + 2

.. ###### END~OF~TABLE ######
