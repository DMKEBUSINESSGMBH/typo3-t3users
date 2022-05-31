<?php

/*
 * benötigte Klassen einbinden
 */

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
     * @param   \Sys25\RnBase\Domain\Model\BaseModel    $item
     */
    public function format(
        $columnValue,
        $columnName,
        array $record,
        \Sys25\RnBase\Domain\Model\DataInterface $entry
    ) {
        if ('uid' === $columnName) {
            return sprintf(
                '<span title="UID: %s">%s</span>',
                $columnValue,
                \Sys25\RnBase\Backend\Utility\Icons::getSpriteIcon(
                    'status-user-frontend'
                )
            );
        }

        return parent::format($columnValue, $columnName, $record, $entry);
    }
}
