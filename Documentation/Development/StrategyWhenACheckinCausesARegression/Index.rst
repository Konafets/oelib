

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


Strategy when a checkin causes a regression
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Generally, regressions should be avoided by using unit tests. As some
parts of the code are not covered by unit tests (or some cases are not
covered yet), regressions might be undetected by the existing unit
test. Those regressions than need to be fixed and covered by unit
tests. We have agreed on the following strategy for this:

#. If there have not been any checkinsafter the checkin that has caused
   the regressions, we will directly revert the checkin. We then need a
   fixed version of the patch (including unit tests for the regression).
   Otherwise:

#. If a developer can fix the problem within 48 hours (including unit
   tests for the regression), we will do that. Otherwise:

#. If we can reverse-apply the patch that has caused the problem within
   48 hours, we will do that. We then need a fixed patch. Otherwise:

#. If all else fails, we will just do our best to fix the problem as fast
   as possible.
