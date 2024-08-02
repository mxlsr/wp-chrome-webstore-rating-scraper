# Chrome Web Store Rating

Display your Chrome extension's rating from the Chrome Web Store on your WordPress site.

## Description

The Chrome Web Store Rating plugin allows you to showcase your Chrome extension's rating directly on your WordPress website. It fetches the rating data from the Chrome Web Store and displays it in an attractive, customizable format.

### Key Features:

- Displays the current rating and star visualization of your Chrome extension
- Updates rating data daily at 12 PM (noon) to ensure accuracy without impacting site performance
- Fully customizable display text
- GDPR compliant with server-side data fetching
- Easy to set up and use with a simple shortcode

### How it Works:

1. The plugin fetches your extension's rating data from the Chrome Web Store once a day at 12 PM.
2. It stores this data locally in your WordPress database.
3. When a user visits your site, the plugin displays the most recently fetched data.
4. This approach ensures fast page load times while keeping the rating information up-to-date.

## Installation

1. Upload the php file to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Chrome Store Rating to configure the plugin

## Configuration

1. Navigate to the plugin settings page (Settings > Chrome Store Rating)
2. Enter the full URL of your extension on the Chrome Web Store
3. Customize the display text if desired
4. Save your changes

## Usage

Use the following shortcode to display the rating on any post or page:

```
[chrome_store_rating]
```

## Frequently Asked Questions

### How often does the rating update?

The plugin fetches new rating data once per day at 12 PM (noon). This ensures that your displayed rating is up-to-date without unnecessarily taxing your server or the Chrome Web Store.

### Is this plugin GDPR compliant?

Yes, all data fetching occurs server-side. No user data is sent to the Chrome Web Store or any third-party services.

### Can I customize the appearance of the rating display?

The plugin comes with a default style. If you're comfortable with CSS, you can further customize the appearance by adding your own CSS rules to your theme.

## Donations
If you find this bot useful and want to support me you can do so here:
[![Buy Me A Coffee](https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png)](https://buymeacoffee.com/mxlsr)

## Additional Information

For support, feature requests, or to contribute to the plugin, please visit our [GitHub repository](https://github.com/mxlsr/wp-chrome-webstore-rating-scraper/).

Enjoy using Chrome Web Store Rating Plugin!
