# Worldcore.eu - WooCommerce Gateway
Extends WooCommerce by Adding the Worldcore.eu Gateway.

## Project status and features
- [x] implements worldcore.eu SCI API
- [x] lets customers checkout via worldcore.eu
- [x] checks if orders have actually been paid via API
- [ ] not logging
- [ ] no sandbox mode

## How to use
1. Copy the folder "woocommerce-worldcore-gateway" into the wp-content/plugins/ directory of your Wordpress installation
2. In the plugin admin panel of your webshop, activate the plugin
2. Log in to your Worldcore account via [worldcore.eu](https://worldcore.eu)
3. In your Worldcore account, go to "Settings" -> "API" and generate an API password
4. In your Worldcore account, go to "Settings" -> "SCI/Merchant" and make sure to only allow internal transfers (due to high fees for bank wire transactions)
5. In the "SCI/Merchant" tab, set the "Status URL" to http://yourwebshop.com/?wc-api=mkt_worldcore and set both "Success URL" and "Failure URL" to http://yourwebshop.com/checkout/order-received/
6. In the plugin admin panel of your webshop, go to "WooCommerce" -> "Settings" -> "Checkout" -> "Worldcore" to enable the payment method and enter the worldcore.eu API credentials as well as the Worldcore account number that shall receive the payments, click "Save changes"
7. Your customers now can pay via worldcore.eu

## Why?
Worldcore.eu is a new and innovative, EU-regulated payment institution that challenges the settled PayPal and Card based payment landscape:
- Internal payments between worldcore.eu accounts are free
- Transfers from your own bank account to your worldcore account are also free
- International and multi-currency money transfer
- Traditional outgoing bank wire for worldcore.eu credit to any bank account on the planet (fee: 0.75%)
- Funding via Bitcoin
- Prepaid MasterCard
- API for eCommerce
- No funding/withdrawal limits for corporate accounts

## Disclaimer
- use this plugin at own risk