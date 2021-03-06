<?php

// make compat library available for 5.3.7 <= PHP version < 5.5
require_once(Mage::getBaseDir('lib') . DS . 'password_compat'. DS .'password.php');

class EW_NativePasswords_Helper_Data extends Mage_Core_Helper_Abstract
{
    const MINIMUM_REQUIRED_PHP_VERSION = '5.3.7';
    const NATIVE_PHP_VERSION = '5.5.0';
    const COST_MAX = 31;
    const COST_MIN = 4;
    const COST_DEFAULT = 10;

    const CONFIG_PATH_ENABLED = 'customer/password/native_passwords_enabled';
    const CONFIG_PATH_BACKWARDS_COMPATIBLE = 'customer/password/native_passwords_backwards_compatible';
    const CONFIG_PATH_COST = 'customer/password/native_passwords_cost';
    const CONFIG_PATH_FORCE_AUTO_SALT = 'customer/password/native_passwords_force_auto_salt';
    const CONFIG_PATH_REHASH_ENABLED = 'customer/password/native_passwords_rehash_enabled';

    /**
     * Check environment
     *
     * @return bool
     */
    public function isEnvironmentCompatible() {
        return version_compare(phpversion(), self::MINIMUM_REQUIRED_PHP_VERSION, '<') !== true;
    }

    /**
     * Determine if functionality *should* be used
     * (enabled in system configuration) and
     * *can* be used (PHP version).
     *
     * @return bool
     */
    public function isEnabled() {
        return (bool)Mage::getStoreConfig(self::CONFIG_PATH_ENABLED);
    }

    /**
     * Ignore any specified salt and instead always
     * use native auto-generated salt?
     *
     * @return bool
     */
    public function forceNativeSalt() {
        return (bool)Mage::getStoreConfig(self::CONFIG_PATH_FORCE_AUTO_SALT);
    }

    /**
     * Should password hashes created by Magento
     * still be used for verification?
     *
     * @return bool
     */
    public function allowBackwardsCompatibleVerification() {
        return (bool)Mage::getStoreConfig(self::CONFIG_PATH_BACKWARDS_COMPATIBLE);
    }

    /**
     * Validate cost
     *
     * @param $cost
     * @return bool
     */
    public function validateCost($cost) {
        return $cost >= self::COST_MIN && $cost <= self::COST_MAX;
    }

    /**
     * Get configured cost, if valid.
     *
     * @return int
     */
    public function getConfiguredCost() {
        $value = (int)Mage::getStoreConfig(self::CONFIG_PATH_COST);

        return $value;
    }

    /**
     * Should legacy algorithm hashed passwords be rehashed?
     *
     * @return bool
     */
    public function rehashLegacyPasswords() {
        return (bool)Mage::getStoreConfig(self::CONFIG_PATH_REHASH_ENABLED);
    }

    /**
     * Get optimal cost for current machine.
     * Taken from: http://php.net/manual/en/function.password-hash.php#example-923
     *
     * @return int
     */
    public function getOptimalCost() {
        $timeTarget = 0.05; // 50 milliseconds

        $cost = 8;
        do {
            $cost++;
            $start = microtime(true);
            password_hash("test", PASSWORD_BCRYPT, ["cost" => $cost]);
            $end = microtime(true);
        } while (($end - $start) < $timeTarget);

        return $cost;
    }

    /**
     * Get message for admin when installed but not enabled.
     *
     * @return string
     */
    public function getNotEnabledMessage() {
        return $this->__(
            'You have installed the PHP native passwords API module, but have not enabled its functionality. ' .
            'It is strongly recommended to enable this value in your Magento <a href="%s">customer configuration</a>, in the Password Options section.',
            Mage::getUrl('adminhtml/system_config/edit', array('section'=>'customer'))
        );
    }
}