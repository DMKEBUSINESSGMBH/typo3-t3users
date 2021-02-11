<?php

/*
 * benötigte Klassen einbinden
 */

tx_rnbase::load('tx_t3users_mod_decorator_Base');

/**
 * Es gibt zunächst noch nichts spezifisches zu tun.
 * Alles passiert bisher in tx_t3users_mod_decorator_Base.
 */
class tx_t3users_mod_decorator_FeUser extends tx_t3users_mod_decorator_Base
{
    /**
     * @param   string                  $value
     * @param   string                  $colName
     * @param   array                   $record
     * @param   tx_rnbase_model_base    $item
     */
    public function format(
        $columnValue,
        $columnName,
        array $record,
        \Tx_Rnbase_Domain_Model_DataInterface $entry
    ) {
        if ('uid' === $columnName) {
            return sprintf(
                '<span title="UID: %s">%s</span>',
                $columnValue,
                tx_rnbase_mod_Util::getSpriteIcon(
                    'status-user-frontend'
                )
            );
        }

        return parent::format($columnValue, $columnName, $record, $entry);
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/mod/decorator/class.tx_t3users_mod_decorator_FeUser.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/mod/decorator/class.tx_t3users_mod_decorator_FeUser.php'];
}
