

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


Using the price view helper
^^^^^^^^^^^^^^^^^^^^^^^^^^^

#. Create a price view helper object:
   
   ::
   
      $priceViewHelper = t3lib_div::makeInstance('tx_oelib_ViewHelper_Price');

#. Set the currency of the price using the ISO 4217 alpha-3 code:
   
   ::
   
      $priceViewHelper->setCurrencyFromIsoAlpha3Code('EUR');

#. Set the value of the price:
   
   ::
   
      $priceViewHelper->setValue(1234.567);

#. Render the price in the currency and the format as defined in ISO
   4217:
   
   ::
   
      // Returns “€ 1.234,57”.
      $priceViewHelper->render();

