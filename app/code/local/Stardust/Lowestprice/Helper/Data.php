<?php
class Stardust_Lowestprice_Helper_Data extends Mage_Core_Helper_Abstract
{
	//function to cycle through products
	public function cycleProductCollection(){
		$collection = Mage::getModel('catalog/product')->getCollection();
		//potential to add filters in the future below
 		//$collection->addAttributeToFilter('type_id', array('eq' => 'simple'));
 		//$collection->addAttributeToFilter('visibility', 4);
 		foreach($collection as $product){
			//$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
			echo $i . "th item <br />";
				// setters calls
				//$product->setTeinte(trim((string)$record->web_teinte));
				
				//SEND PRODUCT TO API SEARCH FUNCTION HERE

			try{
				$product->save();
			}
			catch (Exception $e){
				echo 'error' . $e;
			}
		}//end foreach

	}

	//from given product does semantics3 api search, returns result
	private function semanticsSearch(isobject $product){ //replace isobject with something to check if product is object
		//API Key: SEM31710CB14396C9944D17C297DEF6D9C81
		//API Secret: MGJlMjljZTRjNWMxZThkNTRlNWJjMzFiNTliNzkwNjM
		//To install: 'php composer.phar install' (Github: https://github.com/Semantics3/semantics3-php)
		require('lib/Semantics3.php');

		$key = 'SEM3xxxxxxxxxxxxxxxxxxxxxx';
		$secret = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';

		$requestor = new Semantics3_Products($key,$secret);

		$requestor->products_field("cat_id", 4992);
		$requestor->products_field("brand", "Toshiba");

		$results = $requestor->get_products();

		echo $results;
	}
	//obtains lowest possible price of given product with price and config margin
	//compares lowest price to lowest possible price, returns difference
	//if number is not negative then calculate discount, apply discount to product , save product, return success
	//entry added to db table
	//entry added to db table including link to lowest priced item for admin review
}
	 