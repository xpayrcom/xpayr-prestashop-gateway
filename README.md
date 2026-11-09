<div align="center">
  <a href="https://xpayr.com">
    <picture>
      <source media="(prefers-color-scheme: dark)" srcset="https://xpayr.com/public/images/xpayr-logo-white.png" />
      <source media="(prefers-color-scheme: light)" srcset="https://xpayr.com/public/images/xpayr-logo-black.png" />
      <img src="https://xpayr.com/public/images/xpayr-logo-white.png" alt="XPayr Official Logo" width="220" />
    </picture>
  </a>
  <p>
    <a href="https://xpayr.com"><img src="https://xpayr.com/partner-badge.php?type=powered_by&theme=dark" alt="Powered by XPayr" height="28" /></a>
    <a href="https://mcp.xpayr.com"><img src="https://img.shields.io/badge/MCP_Portal-mcp.xpayr.com-029bfd?style=for-the-badge" alt="XPayr MCP Portal" height="28" /></a>
    <a href="https://docs.xpayr.com"><img src="https://img.shields.io/badge/Docs-docs.xpayr.com-10b981?style=for-the-badge" alt="XPayr Docs" height="28" /></a>
    <a href="https://www.npmjs.com/package/@xpayr/mcp-server"><img src="https://img.shields.io/npm/v/@xpayr/mcp-server?style=for-the-badge&color=cb3837&label=MCP%20NPM" alt="NPM @xpayr/mcp-server" height="28" /></a>
  </p>
  <p>
    <a href="https://xpayr.com"><b>Official Website</b></a> •
    <a href="https://mcp.xpayr.com"><b>AI &amp; MCP Portal (21 Tools)</b></a> •
    <a href="https://docs.xpayr.com"><b>Developer Docs</b></a> •
    <a href="https://xpayr.com/developers"><b>Developer Hub</b></a> •
    <a href="https://xpayr.com/tools/crypto-payment-fee-calculator"><b>0.5% Fee Calculator</b></a>
  </p>
</div>

---

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
