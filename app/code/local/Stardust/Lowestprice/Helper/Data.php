<?php
class Stardust_Lowestprice_Helper_Data extends Mage_Core_Helper_Abstract
{
	//function to cycle through products
	public function cycleProductCollection(){
		global $model;
		$collection = $model->getCollection();
		//filter only those that are viewable in catalog
		$collection->addAttributeToFilter('visibility', 4);

		$requestor = semanticInstantiate();
		//potential to add filters in the future below
 		//$collection->addAttributeToFilter('type_id', array('eq' => 'simple'));
 		//$collection->addAttributeToFilter('visibility', 4);
 		$i = 0;
 		foreach($collection as $product){
 			if($i==122){
				//$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
				echo "<br/>" . $i . "th item <br />";
					// setters calls
					//$product->setTeinte(trim((string)$record->web_teinte));
					//SEND PRODUCT TO API SEARCH FUNCTION HERE
					$results = semanticsSearch($product,$requestor);

				try{
					//$product->save();
				}
				catch (Exception $e){
					echo 'error' . $e;
				}
			}//end if
			else if($i>122){die('finished');}
			/*else{
				die('finished');
			}*/
			$i++;
		}//end foreach

	}

	private function semanticInstantiate(){
		require('../lib/Semantics/Semantics3.php');
		//require as class http://stackoverflow.com/questions/13433050/best-way-to-include-use-php-classes-in-magento

		$key = '';
		$secret = '';

		$requestor = new Semantics3_Products($key,$secret);
		return $requestor;
	}

	//from given product does semantics3 api search, returns result
	private function semanticsSearch(Mage_Catalog_Model_Product $product, $requestor){ //replace isobject with something to check if product is object
		global $model;
		$upc = $model->load($product->getId())->getUpc();
		$sku = $model->load($product->getId())->getSku();
		$name = $model->load($product->getId())->getName();
		$manufacturer = $model->load($product->getId())->getAttributeText('manufacturer');

		//echo $sku . $upc . $manufacturer . $name;
		//must be done by brand and upc, whether upc is encapsulated in string or not is irrelevant
		if(!empty($upc) && !empty($manufacturer) && $upc=="846073029872" || $upc==846073029872){
			//echo "<h1>searching UPC and Manufacturer : " . $upc . " and " . $manufacturer . "</h1>";
			$requestor->products_field("upc", $upc);
			$requestor->products_field("brand", $manufacturer);
			$results = json_decode($requestor->get_products(),true);

			//echo '<h2>The results for product ' . $product->getId() . ' are </h2>' . $results;
			//if ($results['total_results_count'] > 0){
				//echo '<h2>The results for product ' . $product->getId() . ' are </h2>';
				comparePrices($results, $product);
			//}
		}
		/*else if (!empty($sku) && !empty($manufacturer)){
			echo "<h1>searching sku and Manufacturer : " . $sku . " and " . $manufacturer . "</h1>";
			$requestor->products_field("model", $sku);
			$requestor->products_field("manufacturer", $manufacturer);
			$results = $requestor->get_products();
		}*/
		//else{
			//echo "Could not find manufacturer and UPC or manufacturer and sku combination for product ID " . $product->getId();
	//	}
		//Must add search by queries in admin config (semantics categories and magento attributes)
		//brand, model, upc, manufacturer
		//if($upc != "")

	}
	
	//obtains lowest possible price of given product with price and config margin
	//compares lowest price to lowest possible price, returns difference
	private function comparePrices($results, Mage_Catalog_Model_Product $product){
		global $model;
		$storePrice = $model->load($product->getId())->getPrice();

		//loop through semantics results to obtain lowest price
		for($i=0; $i < $results['total_results_count']; $i++){
			if(!isset($internetPrice) || $internetPrice > $results['results'][$i]['price']){
				$internetPrice = $results['results'][$i]['price'];
			}
		}

		//only move forward if the internet price is lower than our current store price
		if($internetPrice < $storePrice){
			if(comparePotentialPriceAgainstLimits($internetPrice,$storePrice) == true){
				adjustPrice($internetPrice, $product);
			}
		}

	}

	private function comparePotentialPriceAgainstLimits($internetPrice,$storePrice){
		$cost = $storePrice / 2; //later we will have this compare against cost attribute of product
		$lowestPriceProductCanHave = $cost + 4; //this variable will later come from admin settings

		//proceed only if we can lower the price this much
		if ($internetPrice >= $lowestPriceProductCanHave){
			return true;
		}
		else{
			return false;
		}
	}

	private function adjustPrice($internetPrice, Mage_Catalog_Model_Product $product){
		global $model;
		$priceInterval = .25; //This will later be an admin setting, it is the amount lower you want your price to the lowest on the internet
		$thisProduct = $model->load($product->getId());
		$thisProduct -> setPrice($internetPrice - $priceInterval);
		
		//$thisProduct->save(); //put this in a try, catch
	}


	//if number is not negative then calculate discount, apply discount to product , save product, return success
	//entry added to db table including link to lowest priced item for admin review
}
	 