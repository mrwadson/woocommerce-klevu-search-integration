# WooCommerce Klevu Search Integration

WooCommerce WordPress **not official** plugin for [Klevu AI](https://www.klevu.com/) search integration on your website. 
This plugin allows you to integrate Klevu AI for quick search info widget for your site, 
which will show a list of products on a popup window when interacting with the input field.

And also track [Klevu Analytics](https://docs.klevu.com/apis/smart-search-analytics-events) events 
in the Klevu Merchant Centre ([KMC](https://box.klevu.com/)):
- Reporting product searches
- Reporting product clicks from search results
- Reporting multiple order data

This plugin is **FREE** and is **provided as an example** of tracking implementation AI Klevu Search Platform for WooCommerce shop.

![dev17_2023-07-06_12-44](https://github.com/mrwadson/woocommerce-klevu-search-integration/assets/21111066/bd564feb-2a67-4928-8987-756bcfd1a9cf)

## Requirements

- PHP >= 7.2

## Installation

Just clone (or download as ZIP archive and unzip) this plugin in `wp-content/plugins` directory in your WordPress instance.

## Settings

WordPress Dashboard (WooCommerce Integration) plugin settings:
- `enabled` - enable or disable this plugin
- `klevu_search_url` - Klevu APIv2 Search URL
- `klevu_js_api_key` - Klevu JS API Key
- `klevu_search_min_chars` - Klevu search min chars
- `klevu_search_selector` - Klevu search input selector

![dev17_2023-07-08_16-06](https://github.com/mrwadson/woocommerce-klevu-search-integration/assets/21111066/725dd8d5-2c33-48e9-baa3-1ad5d1b86e29)

## Generating Klevu feed

To synchronize your product catalog and Klevu catalog database, you need to create feed following format:
https://help.klevu.com/support/solutions/articles/5000871297-feed-format

This can be done with [CTX Feed – WooCommerce Product Feed Manager](https://wordpress.org/plugins/webappick-product-feed-for-woocommerce/) plugin.

## Links
- https://www.klevu.com/
