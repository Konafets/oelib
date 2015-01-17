

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


Using the session abstraction
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

This extension provides an abstraction for reading and writing session
data that facilitates unit-testing the session data.

In the code, reading and writing session data looks like this:

::

   require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');
   ...
   tx_oelib_session::getInstance(tx_oelib_Session::TYPE_TEMPORARY)->setAsString(
           'foo-sessionkey',
           'some data'
   );
   ...
   $value = tx_oelib_Session::getInstance(tx_oelib_session::TYPE_TEMPORARY)->getAsString('foo-sessionkey');

The session abstraction provides the types TYPE\_TEMPORARY (which will
cause the data to get lost once the browser is closed) and TYPE\_USER
(which will cause the data to get stored in the FE user session).

In the unit tests, you can replace real session handling with a fake
session:

::

   require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');

   public function setUp() {
           $this->session = new tx_oelib_FakeSession();
           tx_oelib_session::setInstance(
           tx_oelib_Session::TYPE_TEMPORARY, $this->session
           );
   }

   public function testAddToFavoritesWithNewItemCanAddItemToNonEmptySession() {
           $this->session->setAsInteger(
                   tx_realty_pi1::FAVORITES_SESSION_KEY, $this->firstRealtyUid
           );

           $this->fixture->addToFavorites(array($this->secondRealtyUid));

           $this->assertEquals(
                   array($this->firstRealtyUid, $this->secondRealtyUid),
                   $this->session->getAsIntegerArray(
                           tx_realty_pi1::FAVORITES_SESSION_KEY
                   )
           );
   }
