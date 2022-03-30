> ### This is a Magento2 plugin
# VTEX seller connector for Magento2

Using this custom connector will allow VTEX stores to become marketplaces themselves and sell products from an external magento seller.

## [Installation Guide](installation-guide.md)

## How does it work?

Following the [external seller connector](https://developers.vtex.com/vtex-rest-api/docs/external-seller-integration-connector) documentation, there are nine different API requests. Five of these are calls that the seller should make to the marketplace. The other four are requests that the marketplace will need to make to the seller.

### 1. Catalog notification, registration, update

The seller, in this case magento, is responsible for suggesting new SKUs to be sold in the marketplace and also for informing the marketplace about changes in their SKUs that already exist in the marketplace.

When changing product information, in magento, the `catalog_product_save_commit_after` event is triggered.<br>

Here the first step is to check if the sku exists in the marketplace by calling the [Change Notification endpoint](https://developers.vtex.com/vtex-rest-api/reference/catalog-api-sku-seller#catalog-api-get-seller-sku-notification) with two possible responses:
* Status 404, means that the SKU does not yet exist in the marketplace. The seller will push the SKU offer to the marketplace using [Send SKU Suggestion](https://developers.vtex.com/vtex-rest-api/reference/manage-suggestions-1)
* Status 200, means that the SKU exist in the marketplace. The marketplace will make a request to the seller to get updated sku information using [Fulfillment Simulation](https://developers.vtex.com/vtex-rest-api/reference/external-seller#fulfillment-simulation)

All SKUs provided by the [Send SKU Suggestion](https://developers.vtex.com/vtex-rest-api/reference/manage-suggestions-1) endpoint can be found in the store admin area under `Marketplace -> Sellers -> Received SKUs`.<br>
Once approved, the SKUs will become available in the `Products -> Catalog` section and from here on out only price and inventory will be updated via [Fulfillment Simulation](https://developers.vtex.com/vtex-rest-api/reference/external-seller#fulfillment-simulation).

During the order flow, the marketplace storefront needs to be constantly fetching the updated price and inventory of each SKU in the cart. This is essential to guarantee that the customer will always be presented with the most updated information possible. 
This information is provided by the seller through the [Fulfillment Simulation](https://developers.vtex.com/vtex-rest-api/reference/external-seller#fulfillment-simulation) endpoint.

### 2. Order placement and dispatching

Once the customer finishes their checkout, the marketplace needs to let the seller know there is a newly placed order through the [Order Placement](https://developers.vtex.com/vtex-rest-api/reference/external-seller#order-placement) endpoint. The marketplace will send all data required for the seller to be able to create the order in their own store.

After the payment is finished and approved, the marketplace sends a request to the seller through the [Ready For Handling](https://developers.vtex.com/vtex-rest-api/reference/external-seller#order-dispatching) endpoint, to notify it that the fulfillment process can be started.

### 3. Order invoicing and tracking

The invoice is issued by the seller, the invoice data must be sent to the marketplace. 
The seller sends this information through the [Order Invoice Notification](https://developers.vtex.com/vtex-rest-api/reference/invoice#invoicenotification) request.<br>
In magento, the call will be triggered on the `sales_order_invoice_save_after` event.

When sending order tracking information the [Update Order's partial invoice](https://developers.vtex.com/vtex-rest-api/reference/invoice#updatepartialinvoicesendtrackingnumber) endpoint is called to update existing invoice with the tracking information.<br>
In magento, the call will be triggered on the `sales_order_shipment_save_after` event.

### 4. Order cancellation 

The order can be cancelled by either the seller or the marketplace.

When the marketplace cancels an order, a request to the seller is triggered through the [Marketplace Order Cancellation](https://developers.vtex.com/vtex-rest-api/reference/external-seller#mkp-order-cancellation) endpoint.

When the seller (magento) cancels an order, the `sales_order_save_after` event is triggered.<br>
If the order state is `canceled`, the seller makes a request to the seller through the [Cancel Order](https://developers.vtex.com/vtex-rest-api/reference/orders#cancelorder) endpoint.

## How do they communicate?

### Configuration

First we configure our extension in magento.<br>
In the magento admin section we navigate to `Content -> VTEX -> Settings`.<br>
Here we fill out and save:
* `Vendor name` - represents the VTEX marketplace account name
* `App key` and `App token` - required for the API calls made to the marketplace
* `Seller ID` - used when configuring the seller in the marketplace

Navigate to `STORES -> All stores` and create a new `Store view` with the code `vtex`.<br>
This will represent the source for orders placed in the VTEX marketplace.

Then we need to set up a new seller in the VTEX marketplace admin section.<br>
We navigate to `Marketplace -> Sellers -> Management` and click de `New seller` button.<br>
When prompted `Choose the type of Seller you want to register`, choose `Others`, fill out the form and save:
* `Seller ID` - is the `Seller ID` configured in magento
* `Other Name` - desired name
* `Admin Email` - an email address
* `Fulfillment EndPoint` - represents the seller endpoint (see bellow)
* make sure `Active?` is checked

### Fulfillment EndPoint and security

The magento default rest endpoint is `http://{magentoBaseURl}/rest`.<br>
The following endpoints where added to magento in order to accept request from the marketplace:
* `/fulfillment/pvt/orderForms/simulation` - [Fulfillment Simulation](https://developers.vtex.com/vtex-rest-api/reference/external-seller#fulfillment-simulation)
* `/fulfillment/pvt/orders` - [Order Placement](https://developers.vtex.com/vtex-rest-api/reference/external-seller#order-placement)
* `/fulfillment/pvt/orders/:orderId/fulfill` -  [Ready For Handling](https://developers.vtex.com/vtex-rest-api/reference/external-seller#order-dispatching)
* `/fulfillment/pvt/orders/:orderId/cancel` - [Cancel Order](https://developers.vtex.com/vtex-rest-api/reference/orders#cancelorder)

The `Fulfillment EndPoint` should be `http://{magentoBaseURl}/rest/fulfillment`.

For security reasons, `fulfillment` was added to the endpoint routes in order to implement custom authentication by accepting magento integration api token as a param in the `Fulfillment EndPoint`.

The final `Fulfillment EndPoint` should be `http://{magentoBaseURl}/rest/fulfillment?token=MAGENTO_APP_TOKEN`<br>

The `MAGENTO_APP_TOKEN` is generated in magento by navigating to `System -> Extensions -> Integrations`.<br>
Click `Add New Integration`, fill out `Name`, `Your Password` and in the `Basic Settings -> API` tab set `Resource Access` to `All`, then save.

## Other important configurations

### Marketplace trade policies
In the VTEX marketplace got to `Store Setup -> Trade policies`, edit a policy, and next to `Sellers` check the newly created seller so that the policy will also apply to them.<br>
Here you can also set the currency to match the seller store, or change it in the seller store to match the policy.

### Shipping Rates

In magento Set up flat rate shipping:
* On the Admin sidebar, go to `Stores > Settings > Configuration`.
* In the left panel, expand `Sales` and choose `Delivery Methods`.
* Expand the `Flat Rate` section and change `Price`.

### Products specifications

In order to correctly retrieve product specifications from Magento2 into VTEX, the following conditions must be met before approving new products:
* In magento, all product specification can be found under `STORES -> Attributes -> Product`<br>
Each relevant specification needs to have `Visible on Catalog Pages on Storefront` set to `true`.<br>
You can do this by editing the specification, selecting `Storefront Properties` from the left navigation pane, then set<br>
`Visible on Catalog Pages on Storefront` to `true` and save.
* in VTEX, under `MARKETPLACE -> Sellers -> Categories and Brands` the `Specifications mapping` needs to be filled out.<br>
On the `Seller` side will be the specification's label from Magento and on the `Marketplace` side will be the specification's VTEX name.<br>
* When you approve a new product with specifications from magento, you need to make sure that those specifications exists on the VTEX category where you approve the product.<br>
* In VTEX, category specifications can be found under `PRODUCTS -> Catalog -> Categories`, select the category in question
and from the `ACTIONS` tab select `Field (Sku)`.<br>
Here you can edit or add a new specification.<br>
Always make sure `Active` is checked, even after editing/creating a specification.
* The specification name is used in the `Marketplace` side when doing `Specifications mapping`.

### Importing Brands, Categories and Products

In the magento admin section, `Content -> VTEX -> Settings`, after filling out all required fields and saving, you will
have access to brands, categories and product imports from magento into VTEX.

* Brands are imported by name, so only those that don't exist in VTEX will be imported.
* Categories and their hierarchy, is based on an `ID` and `ParentId` system, so their import will be based on the magento `ID`.
  If the category `ID` already exists in VTEX, the import will update the VTEX category with the magento category.
* In the case of products, in magento it is possible for a product to have no `brand` or `category` set. In VTEX it is not posible.
  For products that don't have a brand or category set in magento, in VTEX their category/brand will be `NONAME`.<br>
  Product specifications will be imported if the product has a valid `category`.
