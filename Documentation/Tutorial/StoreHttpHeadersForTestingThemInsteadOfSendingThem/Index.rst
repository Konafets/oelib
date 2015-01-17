

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


Store HTTP headers for testing them instead of sending them
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The header proxy factory class provides a header proxy object with
which HTTP headers can be sent like in PHP's header() function or
collected for testing.


Add real headers
""""""""""""""""

To a add real header, get the singleton header proxy factory instance
first:

::

   tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->addHeader($header);

In non-test mode, addHeader() behaves similar to the PHP function
header().


Testing the headers
"""""""""""""""""""

For testing, the header proxy factory's test mode needs to be enabled.
Then the header proxy instance returned by the factory is another
object which collects the headers that were meant to be sent. This
data can be accessed with getter functions.

The following lines show what a test might look like:

::

   public function setUp() {
     tx_oelib_headerProxyFactory::getInstance()->enableTestMode();
     $this->fixture = new classToTest();
   }

   public function tearDown() {
     tx_oelib_headerProxyFactory::getInstance()->discardInstance();
     unset($this->fixture);
   }

   public function testHeaderWasSentWhenTheUserHasNoAccess() {
     $this->fixture->showPageIfUserHasAccess();
     $this->assertEquals(
             'Status: 403 Forbidden',
             tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->getLastAddedHeader()
     );
   }

If necessary, the instance can return all headers that were added
since the last clean-up:

::

   tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->getAllAddedHeaders();


Send HTML mails
"""""""""""""""

To send e-mails in HTML Format simply add the HTML body to the mails
by usingtx\_oelib\_mailerFactory::getInstance()->getMailer()->setHTMLM
essage($htmlMessage)


Add CSS to these HTML e-mails
"""""""""""""""""""""""""""""

To add CSS to these HTML e-mails simply user the addCssFile function
of the oelib mailer.
