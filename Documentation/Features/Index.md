Features
========

Besides the feature you can configure some additional things in the extension configuration in the extension manager.

**Enable login by email**

With t3users it is not only possible to login with the username but also with the email. You just have to activate it in the path in enableLoginByEmail.

**Extend the FE user TCA**

The TCA can be extended with additional fields. This option adds new fields like first\_name, last\_name and birthday to feusers. You just have to activate it in the path in extendTCA.

**use before last login**

This option adds a new field (beforelastlogin) to feusers. It is filled with the lastlogin timestamp before the lastlogin field was updated at any login! So you can check for example if the user was logged in for the first time. You just have to activate it in the path in useBeforelastLogin.

**server-side FE session timeout before TYPO3 9**
Backported the option to configure $GLOBALS['TYPO3_CONF_VARS']['FE']['sessionTimeout'] before TYPO3 9.
@see https://docs.typo3.org/typo3cms/extensions/core/Changelog/9.0/Feature-78695-SetTheSessionTimeoutForFrontendUsers.html

The only thing you have to do is to configure $GLOBALS['TYPO3_CONF_VARS']['FE']['sessionTimeout'] like you would in TYPO3 9. Since TYPO3 9 t3users will no longer take care of this configuration and let the core handle everything.

Hint: You should put the configuration into AdditionalConfiguration.php as this option is unknown to older TYPO3 versions and would be removed when TYPO3 writes the LocalConfiguration.php.

[Registration](Registration.md)

[Login](Login.md)

[Editing](Editing.md)

[ResetPassword](ResetPassword.md)

[ListFeUser](ListFeUser.md)

[ShowFeUser](ShowFeUser.md)

[RenewSession](RenewSession.md)

[BackendModule](BackendModule.md)

[Logging](Logging.md)
