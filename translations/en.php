<?php
/**
 * English (en) language file for the XPayr PrestaShop payment module.
 * This file provides translations for all user-facing strings used
 * via the $this->l() helper within the Xpayr module class.
 *
 * Auto-loaded by PrestaShop's translation system when the shop language
 * is set to English. Strings are keyed by their original source text.
 */

global $_MODULE;
$_MODULE = [];

/* ------------------------------------------------------------------ */
/* Module meta                                                          */
/* ------------------------------------------------------------------ */
$_MODULE['<{xpayr}prestashop>xpayr_0b2adbed3078f4714b8948d48027c5d4'] = 'XPayr Gateway';
$_MODULE['<{xpayr}prestashop>xpayr_9b1d2c048fceb3e0a0a75fd89e7ffad2'] = 'Accept crypto payments via XPayr checkout.';
$_MODULE['<{xpayr}prestashop>xpayr_d3e2c8a22c9e79bba5d7498b2b17a8a5'] = 'Are you sure you want to uninstall XPayr Gateway?';

/* ------------------------------------------------------------------ */
/* Admin settings form                                                  */
/* ------------------------------------------------------------------ */
$_MODULE['<{xpayr}prestashop>xpayr_ab7b2a97e36f327e4498db01e7ba73a9'] = 'XPayr Gateway Settings';

/* Field labels */
$_MODULE['<{xpayr}prestashop>xpayr_4b2a6a93e52dad00b4b59d6e7e5b2a36'] = 'Enable module';
$_MODULE['<{xpayr}prestashop>xpayr_c06c4b54ab3b14d98be1f5e3af63e7da'] = 'Enabled';
$_MODULE['<{xpayr}prestashop>xpayr_c2c575b59e40e29b083fde80b7f27e3c'] = 'Disabled';
$_MODULE['<{xpayr}prestashop>xpayr_f16e32a0e98ac3ae84e3d8b6e98f2b2b'] = 'Checkout title';
$_MODULE['<{xpayr}prestashop>xpayr_823a511f65e8e85b6a1e50fdf75d6f2a'] = 'Shown on checkout payment option.';
$_MODULE['<{xpayr}prestashop>xpayr_c214c3f5fe0a2ae827bb08d3a6f0bcf4'] = 'Checkout description';
$_MODULE['<{xpayr}prestashop>xpayr_6f18e40f3b5021f1be5da5b8b2e73de3'] = 'API Base URL';
$_MODULE['<{xpayr}prestashop>xpayr_e6c3b3a2f2b9a37e6c4c4e6b3e6c4a12'] = 'Example: https://xpayr.com/api/v1';
$_MODULE['<{xpayr}prestashop>xpayr_d9f8a3c12a4f3c2b5e9f1c7a8d4e2f6b'] = 'Secret API Key';
$_MODULE['<{xpayr}prestashop>xpayr_b3e7f2d41a8c9e6f2c5a1d8b3f4e7c2a'] = 'Use sk_test_... or sk_live_...';
$_MODULE['<{xpayr}prestashop>xpayr_f8b1a4c3d2e9f6a7b5c8e1d4f3a6b9c2'] = 'Network';
$_MODULE['<{xpayr}prestashop>xpayr_c7d3e6f2a9b4c1e8f5a2d7b4e1f8c3a6'] = 'Currency';
$_MODULE['<{xpayr}prestashop>xpayr_a1b4c7d2e5f8a3b6c9d2e7f4a1b6c3e8'] = 'Auto-sync webhook on save';
$_MODULE['<{xpayr}prestashop>xpayr_e5f2a9b6c3d8e1f4a7b2c9d6e3f8a5b2'] = 'Registers webhook URL and stores returned secret automatically.';
$_MODULE['<{xpayr}prestashop>xpayr_b9c2d5e8f1a4b7c4d1e6f3a8b5c2e9f6'] = 'Webhook Secret';
$_MODULE['<{xpayr}prestashop>xpayr_f3a6b9c2d5e8f1a4b7c4d1e6f3a8b5c2'] = 'Used to verify X-XPayr-Signature header.';
$_MODULE['<{xpayr}prestashop>xpayr_c5e8f1a4b7c4d1e6f3a8b5c2e9f6a3b8'] = 'Pending order status';
$_MODULE['<{xpayr}prestashop>xpayr_d1e4f7a2b5c8d3e6f1a4b7c2e5f8a1b6'] = 'Paid order status';
$_MODULE['<{xpayr}prestashop>xpayr_a7b4c1e6f3a8b5c2e9f6d3a8b5c2e9f1'] = 'Debug log';

/* Submit button */
$_MODULE['<{xpayr}prestashop>xpayr_9fd3e2c8a44b17d698b1f5e3af63e7da'] = 'Save';

/* ------------------------------------------------------------------ */
/* Success / error messages                                             */
/* ------------------------------------------------------------------ */
$_MODULE['<{xpayr}prestashop>xpayr_1c3e5a7b9d2f4a6c8e0b2d4f6a8c0e2a'] = 'Settings updated successfully.';
$_MODULE['<{xpayr}prestashop>xpayr_2d4f6a8c0e2b4d6f8a0c2e4b6d8f0a2c'] = 'Settings saved but webhook auto-sync failed. Check API key/base URL.';

/* ------------------------------------------------------------------ */
/* cURL requirement error (shown on install)                            */
/* ------------------------------------------------------------------ */
$_MODULE['<{xpayr}prestashop>xpayr_3e5g7b9d1f3e5g7b9d1f3e5g7b9d1f3e'] = 'cURL extension must be enabled.';
