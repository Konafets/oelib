

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


Using the template helper and salutation switching
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

If you are an extension developer and wish to make use of this
extension, this is what to do:

#. Install this extension.

#. Enter this extension as a dependency for your extension.

#. In the start of your class files, replace all occurrences ofrequire\_o
   nce(PATH\_tslib.'class.tslib\_pibase.php');withrequire\_once(t3lib\_ex
   tMgm::extPath('oelib') . 'class.tx\_oelib\_Autoloader.php');

#. Replace all occurrences ofextends tslib\_pibasewithextends
   tx\_oelib\_templatehelper

#. Replace all calls to $this->pi\_getLL with $this->translate. (This is
   optional as the first function still exists in the oelib extension,
   but it has been deprecated and replaced with the translate function.)

#. Replace all occurrences of $this->conf['listView.']['
   *configurationValue* '] with the templatehelper's getter according to
   the type to fetch (boolean, integer or string), e.g.
   $this->getListViewConfValueString(' *configurationValue* ').

#. Clear the cache in typo3conf/ and make sure that your extension still
   runs fine at this point.

#. Edit your localization files and look for strings that you want to
   split into formal and informal.

#. In your TS Setup, set the following option for your extension ( *not*
   the  *oelib* extension!):
   
   ::
   
      salutation = formal
   
   or
   
   ::
   
      salutation = informal

#. Add this configuration to your extension's documentation.

You only need to change the strings that actually contain a
salutation. If no key with the desired suffix is found in the current
language, the key without the suffix is tried.


Examples
""""""""

You have the following German string:

::

   'thankyou' => 'Vielen Dank für Ihren Einkauf.',

You split this up, using the suffixes “formal” and “informal”:

::

   'thankyou_formal' => 'Vielen Dank für Ihren Einkauf.',
   'thankyou_informal' => 'Vielen Dank für deinen Einkauf.',

If you have any other code that uses that string and you cannot make
that code use the Salutation Switcher, you might want to keep the
original key, too.

If, for example, you have an English and a formal German localization,
you only need to add the informal German strings. This is for two
reasons:

#. In English, there is only the “you”. So you don't need any new strings
   here. As this extension falls back on the default strings if no keys
   with the “\_formal”/”\_informal” suffix are found, you don't need to
   change the string keys.

#. If the user of your extension has chosen to use the formal language
   (and the German localization), this extension doesn't find localized
   string keys with the “\_formal” suffix, falling back on the keys
   without suffix (which, in your case, use the formal salutation
   anyway). If the user choses to have the informal language for a
   change, you newly added strings with the “\_informal” suffix to their
   keys get used.

