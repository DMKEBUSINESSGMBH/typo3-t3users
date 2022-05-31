Developer Section
=================

There are some useful classes shipped with this extension. Here is a small code snippet:

~~~~ {.sourceCode .php}
// Let's get the current fe_user
$user = tx_t3users_models_feuser::getInstance($uid);

if (!$user) {
   return 'Please login!';
}

// Show some details. With record you have access to the complete db record
echo 'Hello ' . $user->getProperty('username') . '! You are member of these groups: ';

// Now retrieve users fe-groups
$groups = $user->getGroups();
foreach ($groups as $group) {
   // with the group object you can get all members
   $users = $group->getUsers();
   echo $group->getTitle() . ' (' . count($users) . ' members)';
}
~~~~

As you can see, there is not a single sql statements in that code.

Login a FE user manually
------------------------

You only need to call the method tx\_t3users\_services\_feuser::loginFrontendUserByUsernameAndPassword passing the username as first parameter and the password as second one.

~~~~ {.sourceCode .php}
$feUserService = tx_t3users_util_ServiceRegistry::getFeUserService();
$feUserService->loginFrontendUserByUsernameAndPassword('john@doe.com', 'S3cr3t');
~~~~
