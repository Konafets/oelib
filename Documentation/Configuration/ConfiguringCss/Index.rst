

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


Configuring CSS
^^^^^^^^^^^^^^^

If needed, you can add classes to these parts by adding them to your
TypoScript setup (in the template). Let's look at how the  *Seminar
Manager* extension uses this librarie's functions:

By default, the organizer field in the ListView has no class added to
keep the HTML-Output as clean as possible. It looks like this:

::

   [...]<td>[name of the organizer]<td>[...]

If you add the following line to your setup,

::

   plugin.tx_seminars_pi1.class_listorganizers  = organizers

the output shows us the following:

::

   [...]<td class="tx-seminars-pi1-organizers">[name of the organizer]</td>[...]

So the resulting class name will be tx-seminars-pi1 and the value from
the corresponding TS rule appended. Please not that the last part of
the name in the TS setup (“class\_listorganizers”) needs to match the
string in the HTML template (“###CLASS\_LISTORGANIZERS###”). The only
difference lies in the capitalization and the “###”.

Then you can add a rule like this to your CSS file:

::

   .tx-seminars-pi1-organizers {
    ...;
   }
