# XPayr Gateway for PrestaShop

Official PrestaShop integration for XPayr.

This module enables crypto payments in PrestaShop checkout by creating XPayr payment sessions, redirecting customers to hosted checkout, and receiving secure webhook callbacks for order-state automation.

## Highlights
- Checkout method: **Pay with Crypto (XPayr)**
- Hosted payment redirect flow
- Dynamic network/currency fetch from XPayr account catalog
- Webhook signature verification (`X-XPayr-Signature`)
- Auto webhook URL registration and secret sync (optional)
- Order status mapping for `payment.completed`, `payment.failed`, `payment.expired`

## Compatibility
- PrestaShop 1.7+
- PHP 7.4+

## Installation
1. Ensure module root folder is `xpayr`.
2. In PrestaShop admin, go to **Modules > Module Manager > Upload a module**.
3. Upload package and install.
4. Open module configuration and set:
- `API Base URL` (recommended: `https://xpayr.com/api/v1`)
- `Secret API Key`
- default `Network` and `Currency`
5. Save and run a test payment.

## Webhook
Callback endpoint:
`https://<your-prestashop-domain>/module/xpayr/ipn`

If auto-sync is enabled, module registers callback URL and stores returned webhook secret automatically.

## Security Notes
- Keep API keys and webhook secrets private.
- Always verify `X-XPayr-Signature` before state changes.
- Use HTTPS-only callback URLs.

## Repository Structure
- `xpayr.php` module bootstrap and settings
- `controllers/` module controllers
- `views/` templates/assets
- `translations/` i18n files
- `sql/` schema scripts

## Support
- Website: https://xpayr.com
- API docs: https://xpayr.com/doc-api
