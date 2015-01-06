

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


Using the testing framework for unit tests
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

While testing new features in the Seminar Manager extension, we were
in need of tools that allow us to easily create and delete dummy
records in the database. We designed a small “testing framework” as we
call it.


Overview
""""""""

The testing framework enables you to easily

- Add and change dummy records with defined record data that uses real
  UIDs

- Remove single records from a database table

- Add dummy relations to an m:n table

- Remove single relations from a database table

- Add dummy FE pages, FE user groups, FE users, system folders, content
  elements, TS templates and page cache entries

- Create a fake front end for testing front-end plugins

- Clean up all the dummy records
  
  - automatically deletes all the dummy records from all allowed tables
  
  - automatically resets the auto\_increment index for each table

- Count records

- Add, change and remove dummy records on foreign extension tables

- Create and delete dummy files in your extension's upload directory

- Create and delete dummy folders in your extension's upload directory


Before you start
""""""""""""""""

You need the following stuff before you start:

- This extension (tx\_oelib) must be installed

- PHPUnit must be installed - we prefer to use the extension tx\_phpunit
  by Kasper Ligaard that integrates PHPUnit in the TYPO3 back end

- All non-system tables of the extension you're about to write unit
  tests need to contain the column “is\_dummy\_record” (needed for easy
  removal of the dummy records) – you can see an example for the SQL
  definition in the ext\_tables.sql file of oelib. You don't need to add
  this column to the system tables  *pages, tt\_content* etc., though,
  as this extension already provides the necessary columns for those
  tables.

- For non-system but foreign extension tables you're about to write unit
  tests need to contain the column built using your extension key as
  prefix followed by “\_is\_dummy\_record” e.g.
  “tx\_seminars\_is\_dummy\_record” if your extension key is
  “tx\_seminars”. The extension key then has to be set via the second
  parameter of the constructor when creating an instance of the testing
  framework.

- You'll have to write your own test suite for your extension


Write a test suite
""""""""""""""""""

Now write your test suite. Keep in mind that the file must be located
under /tests/ in your extension's directory, the filename must end
with “testcase.php” and the names of all test methods must start with
“test”.

The testing framework must be instantiated once per extension that
you're about to write tests. This is mainly for security reasons! If
you instantiate it for “tx\_seminars”, it will not allow you to
add/remove any record on any table outside of the tx\_seminars scope
(this means all table names must start with “tx\_seminars” in that
case).

Here's a very short example that might help you to integrate our
testing framework into your tests. It's taken 1:1 from the tests for
the  *Seminar Manager* extension.We're not able to provide a full
introduction about unit testing and PHPUnit at all. So please read one
of the many good documentations regarding this topic.

::

   <?php
   require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');
   
   class tx_seminars_categoryTest extends tx_phpunit_testcase {
     private $fixture;
     private $testingFramework;
   
     /** UID of the fixture's data in the DB */
     private $fixtureUid = 0;
   
     public function setUp() {
             $this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
             $this->fixtureUid = $this->testingFramework->createRecord(
                     SEMINARS_TABLE_CATEGORIES,
                     array('title' => 'Test category')
                   );
           }
   
     public function tearDown() {
             $this->testingFramework->cleanUp();
             unset($this->fixture, $this->testingFramework);
           }
   
     public function testGetTitle() {
             $this->fixture = new tx_seminars_category($this->fixtureUid);
   
             $this->assertEquals(
                     'Test category',
                     $this->fixture->getTitle()
                   );
           }
   }
   ?>

You can have a deeper look into our example in the subdirectory
“tests/” of oelib: All methods of this testing framework are covered
with at least one unit test. So it will be easy to see how these tools
can be used for your own unit tests.


Known problems with unit testing
""""""""""""""""""""""""""""""""

- The current version of the phpMyAdmin extension sends cookies, which
  breaks the createFakeFrontEnd function in the testing framework. We’re
  aware of this problem and will try to fix it. As a workaround, please
  temporarily  **uninstall phpMyAdmin** for running the tests.

- Currently, the createFakeFrontEnd function in the testing framework
  uses huge amounts of memory. We’re working on reducing this. As a
  workaround, please set the PHP memory limit to at least 256 MB on you
  local development machine. (And don’t run the unit tests on a
  production server, of course.)

- If you want to run the oelib test suite itself, you need to install
  the two test extensions which are provided as T3X files in the
  tests/fixtures/ directory in the oelib extension.Important: If you
  have already had oelib create the dummy record columns in the test
  tables, you need to remove the **user\_oelibtest\_test** and
  **user\_oelibtest\_test\_article\_mm** tables (using phpMyAdmin)
  before installing the two test extensions. Otherwise, the test
  extensions will not be installed correctly.

