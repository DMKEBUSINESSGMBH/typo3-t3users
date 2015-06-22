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

   // Let's get the current fe_user
   $user=tx_t3users_models_feuser::getInstance($uid);

   if (!$user) {
      return 'Please login!';
   }

   // Show some details. With record you have access to the complete db record

   echo 'Hello '.$user->record['username'].'! You are member of these groups: ';

   // Now retrieve users fe-groups
   $groups = $user->getGroups();
   foreach ($groupsAs$group) {
      // with group object you can get all members
      $users=$group->getUsers();
      echo$group->getTitle() . ' (' . count($users) . ' members)';
   }

As you can see, there is not a single sql statements in that code.