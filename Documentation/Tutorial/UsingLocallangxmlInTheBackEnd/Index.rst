

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


Using locallang.xml in the back end
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

If you have classes that need to use a locallang.xml file in the back
end although it was originally used in the front end, you need to
include it in the corresponding class like this:

::

   // If we are in the back end, we include the extension's locallang.xml.
   if ((TYPO3_MODE == 'BE') && is_object($LANG)) {
       $LANG->includeLLFile('EXT:seminars/locallang.xml');
   }

