> ### This is a Magento2 plugin
# Installation Guide

Add the following to your Magento2 project's `composer.json` file and run `composer install`
```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/vtex/magento"
        }
    ],
    "require": {
        "vtex/magento": "dev-master"
    }
}
```
The module will be available inside the project under the folder `app/code/Vtex/VtexMagento`.

Run `php bin/magento module:enable Vtex_VtexMagento` to make sure the module is correctly installed.

### Magento2 configuration

1. In the magento admin section we navigate to `CONTENT -> VTEX -> Settings`.<br>
Here we fill out and save:
* `Vendor name` - represents the VTEX marketplace account name
* `App key` and `App token` - required for the API calls made to the marketplace
* `Seller ID` - used when configuring the seller in the marketplace


2. Generate a `MAGENTO_APP_TOKEN` by navigating to `SYSTEM -> Extensions -> Integrations`.<br>
Click `Add New Integration`, fill out `Name`, `Your Password` and in the `Basic Settings -> API` tab set `Resource Access` to `All`, then save.


3. Navigate to `STORES -> All stores` and create a new `Store view` with the code `vtex`.<br>
This will represent the source for orders placed in the VTEX marketplace.


4. Map the VTEX payment methods with the available magento payment methods.<br>
   `This mappings will be used when importing orders into magento to set the correct payment method.`<br>
   Import the existing VTEX payment methods from within the `VTEX Connector` module by pressing the `Generate payment methods mapping` button.<br>

This will generate the `PAYMENT METHODS MAPPING` panel.<br>
From this panel you need to map all VTEX payment methods with an available magento payment method and save.

### VTEX configuration

Then we need to set up a new seller in the VTEX marketplace admin section.<br>
We navigate to `Marketplace -> Sellers -> Management` and click de `New seller` button.<br>
When prompted `Choose the type of Seller you want to register`, choose `Others`, fill out the form and save:
* `Seller ID` - is the `Seller ID` configured in magento
* `Other Name` - desired name
* `Admin Email` - an email address
* `Fulfillment EndPoint` - represents the seller endpoint (see bellow)
* make sure `Active?` is checked

The `Fulfillment EndPoint` should be `https://{magentoBaseURl}/rest/fulfillment?token=MAGENTO_APP_TOKEN`<br>
