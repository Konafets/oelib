

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


Measuring performance
^^^^^^^^^^^^^^^^^^^^^

If your application is slow, you can use oelib to measure which parts
of your code take the longest to execute. The timer class allows you
to put the time taken by different parts of your code into separate
“buckets” and display the total statistics and the end. The timer
class uses a singleton object so you can the same timer across
different functions and classes.

In the EM, you can enable two shortcuts in the global scope (which
usually is frowned upon for TYPO3) to save yourself some typing when
measuring performance.

#. In all classes that should be benchmarked, include the timer class:

::

     require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');

#. When you want to measure time e,g, of the initialization process, open
   a bucket and give it a name:

::

        tx_oelib_Timer::getInstance()->openBucket('Initialization process'); 

This will start the timer and pour its time into the bucket named
“Initialization process”.

You can also use the shortcut:

::

        tx_oelib_Timer::oB('Initialization process'); 

#. When you want to measure something different, simply open another
   bucket. This will close the previous bucket:

::

        tx_oelib_Timer::getInstance()->openBucket('Rendering');

#. You can also return to the previous bucket (the used buckets are
   stored on a stack), for example at the end of a function:

::

        tx_oelib_Timer::getInstance()->returnToPreviousBucket(); 

You can also use the shortcut:

::

        tx_oelib_Timer::rB(); 

#. You can also stop the timer for some time in order to not measure some
   parts of your code. This will automatically close the current bucket.

::

        tx_oelib_Timer::getInstance()->stopTimer();

#. When everything is finished, you can retrieve a nicely-formatted HTML
   table with the statistics. This will automatically stop the timer and
   close all buckets.

::

        $this->content .= tx_oelib_Timer::getInstance()->getStatistics();

- If you need the statistics as an array instead for easier processing,
  you can do this:

::

              $this->content .= tx_oelib_Timer::getInstance()->getStatisticsAsRawData();

This will return an array in the following format:

[0] => array(

‘bucketName’ => ... (string),‘absoluteTime’ => ... (float in
seconds),‘relativeTime => ... (float in percent)

),[1] => array(...)

- If you need to start over, you can discard all Buckets:

::

                $this->content .= $timer->destroyAllBuckets();

