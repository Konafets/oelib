

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


Using the visibility tree for showing and hiding nested subparts
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

oelib provides a class which you can use to automatically hide and
show nested subparts, for example in an editing form where a complete
section should be hidden if all of its parts are hidden.

The HTML template then could look like this:

::

   <!-- ###ParentSubpart1### -->
   <div class="parentFoo1">
    <!-- ###ChildSubpart1### -->
    <div class="childFoo1"> Foo 1 </div>
    <!-- ###ChildSubpart1### -->
    <!-- ###ChildSubpart2### -->
    <div class="childFoo2"> Foo 2 </div>
    <!-- ###ChildSubpart2### -->
   </div>
   <!-- ###ParentSubpart1### -->
   <!-- ###ParentSubpart2### -->
    <div class="parentBar">Bar</div>
   <!-- ###ParentSubpart2### -->

In this case, ParentSubpart1 should be hidden if both ChildSubpart1
and Childsubpart2 are hidden, while parentSubpart2 should always be
visible.

You then provide the visibility tree class with a nested array that
mirrors these requirements:

::

   $tree = t3lib_div::makeInstance(
    ‘tx_oelib_Visibility_Tree’,
    array(
     ‘ParentSubpart1’ => array(
      ‘ChildSubpart1’ => false,
      ‘ChildSubpart2’ => false,
    ),
    ‘ParentSubpart2’ => true,
   );

Depending on your configuration, you then make some subparts visible:

::

   $tree->makeNodesVisible(array(‘ChildSubpart1’));

Then you can pass the list of still-to-be-hidden subparts to the
template:

::

   $template->hideSubpartsArray($tree->getKeysOfHiddenSubparts());
