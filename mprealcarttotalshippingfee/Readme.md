# Real total cart total for correct calculation of shipping fees

This module will create an override file of the Cart.php on the fly on installation. 
The override is based on the Cart.php of your Prestashop installation/version.

* Compatibility : Prestashop 1.6.*, Prestashop 1.7.X, Prestashop 8.x

##What does it do:
It will prevent the wrong shipping fee being added to the cart/order because of discounted and/or virtual products.

***
##Example 1:
Shipping fee is $7 dollar if the order is below $50. From $50 and above it's free

####Cart contents:
* 1x physical product $30 
* 1x virtual product $25
* Cart Total $55

####Default Prestashop behaviour: 
Shipping fee : Free (wrong)

####Behaviour after installing this module:
Shipping fee : $7 (right)

***
##Example 2:
Shipping fee is $15 dollar if the order is below $25. From $25 and above it's $10

####Cart contents:
* 1x physical product with a special/discount price $15 (normal price $25)
* 1x physical product $8
* Cart Total $23

####Default Prestashop behaviour:
Shipping fee : 10 (wrong)

####Behaviour after installing this module:
Shipping fee : $15 (right)
