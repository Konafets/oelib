

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


Using the configuration proxy
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The configuration proxy provides access to the EM configuration, which
is done when installing/updating an extension and defined in an
extension's “ext\_conf\_template.txt”.Apart from getting configuration
values easily, the proxy can fake configuration values. This feature
is supposed to testing purposes.The configuration proxy is a singleton
and is implemented lazily.


Use the proxy in a class
""""""""""""""""""""""""

To use the proxy in your class, first of all load the proxy class
using the autoloader:

::

   require_once(t3lib_extMgm::extPath('oelib').'class.tx_oelib_Autoloader.php');

Then just get the instance of your extension's proxy. Pass the
extension key without the prefix “tx” for this. There is no need for
any further initialization as this is done automatically if necessary:

::

   $proxyInstance = tx_oelib_configurationProxy::getInstance('extension');

Now you can get configuration values. Therefore use the getter for the
expected type of value and pass the configuration value's key:

::

   $proxyInstance->getConfigurationValueString('configurationValue');
   $proxyInstance->getConfigurationValueBoolean('otherConfigurationValue');
   $proxyInstance->getConfigurationValueInteger('againAConfigurationValue');


Use the proxy for unit tests
""""""""""""""""""""""""""""

In addition to the getters described above, the proxy provides setters
to fake the configuration for testing purposes. The setters overwrite
existing configuration values and can also set totally new values
which are not defined in the “ext\_conf\_template.txt” if this is
needed:

::

   $proxyInstance->setConfigurationValueString('configurationValue', 'any string needed for tests');$proxyInstance->setConfigurationValueBoolean('newValue', true);

To ensure an unchanged configuration, you can retrieve the original
configuration. Usually this should not be necessary:

::

   $proxyInstance->retrieveConfiguration();

For debugging, it might be useful to get the complete configuration:

::

   $proxyInstance->getCompleteConfiguration();

