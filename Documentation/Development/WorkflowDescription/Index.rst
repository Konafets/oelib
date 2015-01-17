

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


Workflow description
^^^^^^^^^^^^^^^^^^^^

The development process looks roughly like the process on `Mozilla.org
<http://www.mozilla.org/>`_ and aims at creating high-quality code and
facilitating learning from other coders.

#. Make sure to always use the latest code and documentation from GIT.

#. Look in the `bug tracker <https://bugs.oliverklee.com/>`_ if your
   specific bug or feature request already has been reported. If this is
   not the case, enter a new bug report/feature request.

#. Set yourself as the bug's assignee to show you'd like to work on this
   bug. At this point, the bug status still is NEW.

#. Assign the bug yourself when you've actually started to work on this
   bug. This will change the bug's status to ASSIGNED.

#. **Use a test-first approach:** When you add a new function or change a
   function, first write some unit tests that fail as long as the bug is
   not fixed and that pass when the bug is fixed.

#. Write the necessary code and test it locally (in addition to the unit
   tests). Make sure it works and doesn't generate any warnings or
   errors.

#. Create a change set and push it to Gerrit..

#. The reviewer might give you a *-*  *1* and list the things that need
   to be changed. In that case, go back to the previous step and create a
   new patch.

#. Or the reviewer might grant you the review, giving you a  *review+*
   (possibly listing some things that need to be changed bug that don't
   require a new review).

#. Resolve the bug report as FIXED.
