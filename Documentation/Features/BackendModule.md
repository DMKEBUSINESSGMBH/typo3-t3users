Backend Module
==============

There are 2 BE modules. One is inserted into the functions module below the web module. The other one must be activated in the extension configuration in the extension manager in the path activateBeModule.

Backend Module in the function module
-------------------------------------

Open the function backend module and choose in the dropdown the FE-User Management module. With this module you can search for FE users. Furthermore you can directly edit those users or their groups and login in the frontend as a user. Especially the login can be very helpful for debugging purposes.

By default non admin users can't search by the uid or the page of FE users. But this can be configured in the extension configuration in the extension manager in the path fullModuleForNonAdmins.

Standalone Backend Module
-------------------------

If it was activated in the extension configuration it can be found below the web module. It's pretty similar to the module in the functions module with some differences. You can create new FE users but you can't login in as them. You can't search for a specific UID. It's only a fulltext search in common. At last you have some more editing options like hiding and deleting a user.
