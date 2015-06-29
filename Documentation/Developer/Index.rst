.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _developer:

Developer Section
=================

There are some useful classes shipped with this extension. Here is a small code snippet:

.. code-block:: php

   require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
   tx_rnbase::load('tx_t3users_models_feuser);

   // Let's get the current fe_user
   $user = tx_t3users_models_feuser::getInstance($uid);

   if (!$user) {
      return 'Please login!';
   }

   // Show some details. With record you have access to the complete db record
   echo 'Hello ' . $user->record['username'] . '! You are member of these groups: ';

   // Now retrieve users fe-groups
   $groups = $user->getGroups();
   foreach ($groups as $group) {
      // with the group object you can get all members
      $users = $group->getUsers();
      echo $group->getTitle() . ' (' . count($users) . ' members)';
   }

As you can see, there is not a single sql statements in that code.
