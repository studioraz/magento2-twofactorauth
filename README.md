[![Total Downloads](https://poser.pugx.org/studioraz/magento2-twofactorauth/downloads)](https://packagist.org/packages/studioraz/magento2-twofactorauth)
[![Latest Stable Version](https://poser.pugx.org/studioraz/magento2-twofactorauth/v/stable)](https://packagist.org/packages/studioraz/magento2-twofactorauth)

<a href="http://www.studioraz.co.il"><img src="https://image.ibb.co/cRGMFy/logostudioraz.jpg" width="100" align="right" /></a>
# Magento 2 - Customer Login Two Factor Authentication

## Installation

#### Step 1

##### Using Composer (recommended)

```
composer require studioraz/magento2-twofactorauth
```

##### Manual Installation (less recommended)
 * Download the extension
 * Unzip the file
 * Create a folder {Magento root}/app/code/SR/TwoFactorAuth
 * Copy the content from the unzip folder
 * Flush cache

#### Step 2 -  Enable the module
```
 php -f bin/magento module:enable --clear-static-content SR_TwoFactorAuth
 php -f bin/magento setup:upgrade
 php -f bin/magento cache:flush
```

#### Step 3 - Configuration
Log into your Magento Admin, then goto Stores -> Configuration -> Studio Raz -> Two Factor Authentication


Support
---
Before reporting an issue, check if you can reproduce it on the clean Magento instance.
If that is the cases, please open an issue on [GitHub](https://github.com/studioraz/magento2-twofactorauth/issues).

Contribution
---
Want to contribute to this extension? The quickest way is to open a [pull request on GitHub](https://help.github.com/articles/using-pull-requests).

Need help setting up or want to customize this extension to meet your business needs? Please email us to support@studioraz.co.il.

© Studio Raz | [www.studio-raz.com](http:/www.studio-raz.com)
