

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


Send e-mails and test them instead of sending
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The mailer factory class provides a mailer object with which e-mails
can be sent or stored for testing.


Send e-mails
""""""""""""

To send e-mails using the mailer's sendEmail() function in a class,
you need to get the singleton mailer factory instance first:

::

   tx_oelib_mailerFactory::getInstance()->getMailer()->sendEmail($recipient, $subject, $message);

In non-test mode, sendEmail() behaves similar to
t3lib\_div::plainMailEncoded().


Test what would usually be sent
"""""""""""""""""""""""""""""""

For testing, the mailer factory's test mode needs to be enabled. Then
the mailer instance returned by the factory is another object which
does not send e-mails when its sendEmail() function is called, but
collects the submitted data. This data can be accessed with various
getter functions.

The following lines show how to enable the test mode for unit testing
and what might an e-mail test look like:

::

   public function setUp() {
     tx_oelib_mailerFactory::getInstance()->enableTestMode();
     $this->fixture = new classToTest();
   }

   public function tearDown() {
     tx_oelib_mailerFactory::getInstance()->discardInstance();
     unset($this->fixture);
   }

   public function testSendAnEmail() {
     $this->fixture->sendAnEmail(
                  'recipient@valid-email-address.org',
             'any subject',
             'this is a test message'
     );
     $this->assertEquals(
             array(
                          'recipient' => 'recipient@valid-email-address.org',
                     'subject' => 'any subject',
                     'message' => 'this is a test message',
                     'headers' => ''
               ),
             tx_oelib_mailerFactory::getInstance()->getMailer()->getLastEmail()
     );
   }

If you do not need to check the whole e-mail, there are also more
special getters:

::

   tx_oelib_mailerFactory::getInstance()->getMailer()->getLastRecipient();
   tx_oelib_mailerFactory::getInstance()->getMailer()->getLastSbject();
   tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody();
   tx_oelib_mailerFactory::getInstance()->getMailer()->getLastHeaders();

In addition, the mailer instance can return all e-mails “sent” with it
since the last clean-up:

::

   tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail();

For testing a class' behavior when sending e-mails succeeds/fails, the
return value can be faked in test mode:

tx\_oelib\_mailerFactory::getInstance()->getMailer()->setFakedReturnVa
lue( **true** );
