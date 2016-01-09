# Magento Product Gift Module
This module gives you the ability to bind your product with gift product which will be added to the cart with zero price. You can manage gift products on product level or category level.

# Compatibility
* Magento CE 1.8-1.9

# Installation
* Disable compilation
* Merge app/ dir of the module package with app/ dir of your magento installation
* Refresh magento cache, relogin to admin panel

# Usage

## Manage product gift on product level.
After installation go to the product page in admin panel. You will see "Gift for product bought" tab. Place here SKU of the gift product and enable it. Product gift massage will appear on the product page. If you try add this product to cart, gift product will be added with zero price. 

## Manage product gift on category level.
* Go to Easymage/Manage Productgift tab. Select product category you want to add a gift to. Place gift SKU and save config.
* Apply rule button appears. Push it and the gift product will be added to all products of the selected category. After applying rule the row becomes locked and you can't edit it until you remove the rule.
* You can remove gift product from category with remove rule button.

## Gift strategy
Go to Easymage/Configuration. You have two gift strategies:
* One gift per order - you can add only one gift to your shopping cart no matter how many promoted products are in there.
* One gift per item - you get one gift product per promoted product added to shopping cart.

## Manage gift message
By default gift message are rendered in "product.info.extrahint" block.
You can manage the appearance of gift message by editing those files:
* app/design/frontend/base/default/layout/easymage/productgift.xml
* app/design/frontend/base/default/template/easymage/productgift/productgift.phtml

## Pay attention
Gift product have to be enabled, visible and in stock. Otherwise you won't be able to add promoted product to cart.

