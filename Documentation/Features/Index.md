Features
========

Besides the feature you can configure some additional things in the extension configuration in the extension manager.

**Enable login by email**

With t3users it is not only possible to login with the username but also with the email. You just have to activate it in the path in enableLoginByEmail.

**Extend the FE user TCA**

The TCA can be extended with additional fields. This option adds new fields like first\_name, last\_name and birthday to feusers. You just have to activate it in the path in extendTCA.

**use before last login**

This option adds a new field (beforelastlogin) to feusers. It is filled with the lastlogin timestamp before the lastlogin field was updated at any login! So you can check for example if the user was logged in for the first time. You just have to activate it in the path in useBeforelastLogin.

[Registration](Registration.md)

[Login](Login.md)

[Editing](Editing.md)

[ResetPassword](ResetPassword.md)

[ListFeUser](ListFeUser.md)

[ShowFeUser](ShowFeUser.md)

[RenewSession](RenewSession.md)

[BackendModule](BackendModule.md)

[Logging](Logging.md)