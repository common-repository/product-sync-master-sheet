=== Sync Master Sheet - Product Sync with Google Sheet for WooCommerce ===
Contributors: codersaiful
Donate link: https://donate.stripe.com/4gw2bB2Pzdjd8mYfYZ
Tags: stock management, bulk editor, woocommerce stock sync, woocommerce stock management, woocommerce inventory management
Requires at least: 4.0.0
Tested up to: 6.7
Stable tag: 1.0.7
Requires PHP: 6.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Help you to connect your WooCommerce website with Google Sheet as well as Manage your Stock easy from one menu with Advance Filter

== Description ==
Easily manage and synchronize your WooCommerce product stock with the power of Google Sheets using our plugin - Product Stock Sync with Google Sheet for WooCommerce. This intuitive solution empowers you to streamline your inventory management effortlessly.

https://www.youtube.com/watch?v=ucmb_1wDRws

**Key Features:**

* **Google Sheet Integration:** Seamlessly connect your WooCommerce store with Google Sheets via the Google Sheets API.
* **Effortless Updates:** Update product stock levels directly from your Google Sheet, ensuring accurate and real-time inventory information on your WooCommerce store.
* **Simplified Stock Management:** Take control of your stock effortlessly within the familiar and user-friendly Google Sheets interface.
* **Automation:** Say goodbye to manual updates! Our plugin automates the synchronization process, saving you time and reducing the risk of errors.

**How It Works:**

* **Connect:** Establish a secure connection between your WooCommerce store and Google Sheets using the Google Sheets API.
* **Update:** Effortlessly update product stock levels, prices, and other details directly from your Google Sheet.
* **Real-time Sync:** Enjoy real-time synchronization between your WooCommerce store and Google Sheets, ensuring your inventory is always up-to-date.

**Why Choose Product Stock Sync**

* **Time-Saving:** Streamline your workflow with automated stock updates, freeing up time for more strategic business activities.
* **Accuracy:** Eliminate discrepancies and maintain accurate stock levels across your WooCommerce store and Google Sheets.
* **User-Friendly:** No technical expertise required! Our plugin is designed for easy setup and hassle-free stock management.

**Getting Started: Setting Up Google Sheet API Connection**

Ensure a smooth setup process by following these steps to connect your WooCommerce store with Google Sheets using our plugin.
Please follow, following steps:

* [Tutorial - Setup Guideline](https://codeastrology.com/how-to-setup-product-sync-master-plugin/) - Checkout and follow step by step guideline. How to configure with Google Sheet - explained here.
* [Link Google Sheet](https://docs.google.com/spreadsheets/) - Create new spreadsheet or use existing. In the plugin settings, find the section to link your Google Sheet. Provide the required Google Sheet URL and configure additional settings as needed.
* [Create Google Project](https://console.cloud.google.com/projectcreate?previousPage=/apis/credentials) - Navigate to the "APIs & Services" and select "Dashboard". Click on "+ CREATE PROJECT" and fill in the necessary details. 
* [Credentials to make a Service JSON](https://console.cloud.google.com/apis/credentials) - Still in the "APIs & Services" section, navigate to "Credentials". Click on "Create Credentials" and choose "Service Account Key". Create a new service account, download the JSON key file, and keep it secure.
* [Enable Sheet API](https://console.cloud.google.com/apis/library/sheets.googleapis.com) - In your project dashboard, go to "APIs & Services" > "Library". Search for "Google Sheets API" and enable it for your project.
* [Check Enable Sheet API](https://console.cloud.google.com/apis/api/sheets.googleapis.com/metrics) - Checkout existing API which enabled or not.
* **Authentication** - For generate access tocken, we have sent request to https://oauth2.googleapis.com/token.
* **WooCommerce required** - Ensure that, [WooCommerce](https://wordpress.org/plugins/woocommerce/) is already installed. Then install this plugin.

Take control of your WooCommerce inventory like never before with *Product Stock Sync with Google Sheet for WooCommerce*. Experience seamless integration and efficient stock management today!

**Credits**

We believe in giving credit where it's due. Our plugin utilizes the following third-party resources to enhance its functionality:

* Fontello Icons: We express our gratitude to [Fontello](https://github.com/fontello/fontello#developers-api) for providing the icon font used in our plugin's user interface. Beautiful and customizable icons make the user experience more visually appealing.
* Google Sheet API: A big thank you to the [Google Sheets API](https://developers.google.com/sheets/api/reference/rest) for enabling seamless integration between our plugin and Google Sheets. This API plays a pivotal role in automating the synchronization of product data.
* WooCommerce: Our plugin is built upon the robust foundation of [WooCommerce](https://wordpress.org/plugins/woocommerce/), empowering online businesses with a feature-rich and flexible e-commerce solution for WordPress.
* [WordPress](https://wordpress.org/): We are indebted to the WordPress platform for providing the framework that powers our plugin. Its open-source nature and extensive community support make it an ideal environment for creating powerful and customizable solutions.

These entities have significantly contributed to the functionality, aesthetics, and overall success of our plugin. We are proud to acknowledge and appreciate their role in making our plugin a reality.

**Important Links**

* [Tutorial for Sheet Details](https://codeastrology.com/how-to-setup-product-sync-master-plugin/#sheet-details-here)
* [How to create JSON file and API key](https://codeastrology.com/how-to-setup-product-sync-master-plugin/#service-key-api-help-section)
* [Google Sheets API Connector Overview](https://cloud.google.com/workflows/docs/reference/googleapis/sheets/Overview)
* [Sheet API Method: values.batchUpdate](https://cloud.google.com/workflows/docs/reference/googleapis/sheets/v4/spreadsheets.values/batchUpdate)
* [Sheet API Method: spreadsheets.values.clear](https://developers.google.com/sheets/api/reference/rest/v4/spreadsheets.values/clear)

**Used Request in Code**

* Token Gen: `$token_url = 'https://oauth2.googleapis.com/token';`
* Scope: `['scope' => 'https://www.googleapis.com/auth/spreadsheets']`
* Insert Data in Sheet: `$api_url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheet_id/values/$sheet_name!$range?valueInputOption=RAW&key=$API_KEY";`
* Update Sheet: `$api_url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheet_id/values:batchUpdate?valueInputOption=RAW&key=$API_KEY"`
* Clear Sheet: `$api_url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheet_id/values/$sheet_name:clear?key=$API_KEY"`

== Installation ==

1. Upload 'product-sync-master-sheet' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Connect with Google Sheet by API Key and service json file

== Frequently Asked Questions ==

= Menu Location =

🔅 Dashboard -> Product Sync with Google Sheet

= Where is Bulk edit of Product?  =

🔅 Dashboard -> Product Sync with Google Sheet -> Product Quick Edit

= What type of product's stock will sync with Google Sheet? =

🔅 It's will sync with all tye product with google sheet. Such: simple, variable, variation,grouped etc

== Screenshots ==

1. Setting
2. Connection of Google Sheet

== Changelog ==

= 1.0.7 =
* Added: new action hook(pssg_loaded) added.
* Optimization of code 
* Bug Fixed

= 1.0.6 =
* Bug Fixed: AppsScript code copy issue fixed.
* Optimization of code 
* Bug Fixed

= 1.0.5 =
* Bug Fixed: Variation name issue when 3 or more variation - not showing issue fixed.
* Bug Fixed: preg_replace issue fixed from Quick/Bulk edit table
* Bug Fixed

= 1.0.4 =
* Compatibility with Latest Google Sheet URL policy
* Bug Fixed

= 1.0.3 =
* Compatibility with Latest WordPress
* Compatibility with latest WooCommerce
* Compatibility with Google Sheets API v4
* Speed optimize
* Bug Fixed

= 1.0.2 =
* Setup wizard added, where user will able to setup the plugin with Google sheet.
* Speed optimize 
* Bug Fixed 

= 1.0.1 =
* Column setting issue saving issue has been solved.
* Code Optimization done.

= 1.0.0 =
* Primary released.