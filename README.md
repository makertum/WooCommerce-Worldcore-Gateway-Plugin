# Payment Gateway for Worldcore.eu and WooCommerce
Extends WooCommerce by adding the Worldcore.eu payment Gateway.

## Project status and features
- [x] implements worldcore.eu SCI API
- [x] lets customers checkout via worldcore.eu
- [x] provides WC_API endpoint for the SCI status callback
- [x] checks if orders have actually been paid
- [ ] not logging
- [ ] no sandbox mode

## How to use
1. Upload the plugin files to the `/wp-content/plugins/woocommerce-worldcore-gateway` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Log in to your Worldcore account via [worldcore.eu](https://worldcore.eu)
4. In your Worldcore account, go to "Settings" -> "API" and generate an API password
5. In your Worldcore account, go to "Settings" -> "SCI/Merchant" and make sure to only allow internal transfers (due to high fees for bank wire transactions)
6. In the "SCI/Merchant" tab, set the "Status URL" to http://yourwebshop.com/?wc-api=mkt_worldcore and set both "Success URL" and "Failure URL" to http://yourwebshop.com/checkout/order-received/
7. Use the WooCommerce->Settings->Checkout->Worldcore screen to configure the plugin: Enable the payment method and enter the worldcore.eu API credentials as well as the Worldcore account number that shall receive the payments, you may also want to set the icon to https://worldcore.eu/images/pink_logo.png, click 'Save changes'
8. Your customers now can pay via Worldcore.eu

## Why?
Worldcore.eu is a new and innovative, EU-regulated payment institution that challenges the settled PayPal and card based payment landscape:

- Internal payments between worldcore.eu accounts are free
- Transfers from your own bank account to your worldcore account are also free
- Funding via Bitcoin
- Prepaid MasterCard
- Fast verification process

Because of its strong focus on credit card use, where all the money in the Worldcore ecosystem is eventually spent via credit card, Worldcore is primarily a payment solution for customers and between individuals and yet has to grow on the corporate side. Nevertheless, it has already some good arguments over PayPal and alike:

- API for eCommerce
- No funding/withdrawal limits for corporate accounts

Get your Worldcore account for free on [worldcore.eu](https://worldcore.eu/).

## Disclaimer
Use this plugin at your own risk.

## License
Licensed under [GPLv3](http://www.gnu.org/licenses/gpl-3.0.html)
