

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


Code style guidelines
^^^^^^^^^^^^^^^^^^^^^

Our extensions follow the following code style guidelines:

#. `Code Conventions for the Java Programming Language
   <http://java.sun.com/docs/codeconv/html/CodeConvTOC.doc.html>`_

#. on top of that: `phpDocumentor documentation <http://manual.phpdoc.org
   /HTMLSmartyConverter/HandS/phpDocumentor/tutorial_phpDocumentor.pkg.ht
   ml>`_

#. on top of that: `TYPO3 Coding Guidelines
   <http://typo3.org/documentation/document-library/core-
   documentation/doc_core_cgl/current/>`_

#. on top of that: `FLOW3 Coding Guidelines
   <http://flow3.typo3.org/documentation/coding-guidelines/>`_

We then have added a few modifications, additions and clarifications:

- Don’t check in commented-out code to SVN. If you absolutely need to do
  so anyway, you *must* include a comment (including a reference to a
  bug) stating when this code can be removed or reactivated.


Formatting
""""""""""

- Use UTF-8 and Unix line endings.

- Remove all trailing spaces and trailing tabs.

- Lines must not be longer than 80 characters. This also includes
  comments and documentation comments (also for @param and @return). The
  only exception are include\_once statements (which should be on one
  line, overriding the 80-character rule) and the declarations of unit
  tests.Concerning line wrapping, have a look at the `line-wrapping
  guidelines <http://java.sun.com/docs/codeconv/html/CodeConventions.doc
  3.html#262>`_ .

- File names *are* allowed to be longer than 31 characters.

- Don’t add blank lines after { or before }.

- Use a single space after @return, @var, @param, @var, @throws etc.,
  not tabs. (This still needs to be changed in the existing code.)


Comments
""""""""

- Don't use end-line comments.

- // comments should be indented exactly as far as the following line,
  not one additional tab as it is done in the TYPO3 core.


**Code structure**
""""""""""""""""""

- Use early returns only for `guard clauses
  <http://c2.com/cgi/wiki?GuardClause>`_ and only if they greatly
  improve readability.

- Functions that return void don't need to have a  *return* statement.


Documentation
"""""""""""""

- Use @var for member variables.

- @var should always be in multi-line style:/\*\*\* @var type
  description\*/

- Functions that return void don't need to have a @return.

- @param should include the type, the parameter name and a description.

::

   @param array $types the allowed types

If the description doesn’t fit on the first line, start on a new line
and indent the description to align with the type:

::

   @param     array $types
                   the types that are allowed, must always include at least one non-green type and must not
                   contain any aliens with smelly feet

- Sentences in comments (including @param and @return) should either be
  
  - incomplete sentences that start lowercase and don’t have a full stop
    at the end, or
  
  - complete sentences that have a capital letter at the start and a full
    stop at the end.

- Comments should be written in the third person (or in the first person
  in some cases).

- Put the documentation comment for a class directly above the *class*
  statement. *Include* statements should be *above* the class comment so
  that PhpDocumentor doesn’t complain.

- Use @throws for documenting exceptions that need to be caught by the
  caller (checked exceptions).

- Don’t use @throws when the exception does not need to be caught by the
  caller (unchecked exceptions). Examples of this are exceptions for a
  database failure or exceptions that are thrown only when the contract
  is violated, , e.g. when a UID for which the @param states that it
  must be >= 0 is -42.


Naming
""""""

- Use strict camelCase for function and variable names. Good:
  createIndex, readIsoImage. Bad: create\_index, readISOImage.

- Class names need to contain the path so that the autoloader can work:
  EXT:foo/Model/class.tx\_foo\_Model\_Chicken.php

- Private and protected function and variable names must *not* begin
  with an underscore.

- **Testing classes:**
  
  - Testing classes that are basically a fully-fledged subclass of another
    class should be prefixed “testing”. Example: A testing subclass of
    *foo* should be named *testingFoo* .
  
  - Testing classes that fake some of their behavior to reduce
    dependencies or API calls should be prefixed with “fake”. Example:
    Such a testing subclass of *foo* should be named *fakeFoo* .
  
  - Test case classes should be suffixed with \*Test. Example: A testcase
    for the *seminar* class should be named *seminarTest* (if there is
    only one testcase for that class).

- Mark test functions with an @test annotation.

- Test functions that refer to one particular function should be named
  *test<function name><parameters or preconditions><expected result or
  behavior>* (without any underscores). Example:
  getAsStringWithInexistentKeyReturnsEmptyString.

- Test functions that do *not* refer to a particular function should
  read like a sentence. Example: configurationIsReadAfterInitialization


Language features
"""""""""""""""""

- We already require PHP5 and don't need to be PHP4-compliant anymore.
  So please use type hinting, exceptions and access modifiers like
  *public* or *private* and don't use @access anymore. Also use
  *public/private/protected* instead of *var* for member variables.

