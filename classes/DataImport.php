<?php
require_once ('XmlParser.php');
/**
 *
 */
class DataImport {
	private $xmlFile = false;
	private $data = false;
	private $level = 1;
	function __construct($level, $file,$type) {
		$this -> level = $level;
		$this -> data = array();
		$this -> xmlFile = $file;
		$this -> sanitizeXml($type);
	}

	/**
	 * \brief Calls the correct sanitizor-function
	 * @author P.Welling
	 * \b Values:
	 * - 1 => productsanitizor
	 * - 2 => configuratorsanitizor
	 */
	function sanitizeXml($type){
		switch($type){
			case 1:
				$this->sanitizeProductXml();
				break;
			case 2:
				$this->sanitizeConfiguratorXml();
				break;
		}
	}

	/**
	 * \brief Sanitizes the xml and adds the lines to the product array. This can be either looped through or placed into a database.
	 * @author P.Welling
	 */
	function sanitizeProductXml() {
		$xmlParse = new XmlParser($this -> xmlFile);
		$data = $xmlParse -> returnArray();
		$data = $this -> getLevelData($data);
		$this -> data = array();
		foreach ($data AS $key => $product) {
			if (isset($product['attributes'])) {
				$prod = $product['attributes'];
				if (isset($product['children']) && count($product['children']) > 0) {
					for ($i = 0, $il = count($product['children']); $i < $il; $i++) {
						$tmp = $product['children'][$i];
						if(!isset($prod[$tmp['name']])){
							$prod[$tmp['name']] = array();
						}
						if($tmp['Value'] != ''){
							$prod[$tmp['name']][] = $tmp['Value'];
						}
						if($tmp['Value'] == '' && isset($tmp['children'])){
							foreach($tmp['children'] AS $cKey=>$cData){
								$prod[$tmp['name']][] = $cData['attributes']['Value'];
							}
						}
					}
				}
				if (!isset($this -> data[$product['attributes']['Catid']])) {
					$this -> data[$product['attributes']['Catid']] = array();
				}
				$this -> data[$product['attributes']['Catid']][$product['attributes']['Product_ID']] = $prod;
			}
		}
	}

	/**
	 * \brief Skeleton function fro if the class would have been used to implement the configurator xml
	 * @author P.Welling
	 */
	function sanitizeConfiguratorXml(){
		
	}
	
	/**
	 * \brief Gets the given level of the array that has been passed on
	 * @author P.Welling
	 */
	function getLevelData($data) {
		$return = $data;
		for ($i = 0, $il = $this -> level; $i < $il; $i++) {
			if (isset($return[0]) && isset($return[0]['children'])) {
				$return = $return[0]['children'];
			} else {
				echo 'Error propcessing data';
				exit();
				break;
			}
		}
		return $return;
	}

	/**
	 * \brief Looks in the data and returns the products that belong to the given category
	 * @author P.Welling
	 */
	function filterProductData($catId){
		$return = 'The category (id: '.$catId.') you requested unfortunaely was not found';
		if(isset($this->data[$catId])){
			$mainTpl = file_get_contents('templates/productMain.tpl');
			$detailTpl = file_get_contents('templates/productDetails.tpl');
			$eanTpl = file_get_contents('templates/productEanContainer.tpl');
			$return = str_replace('{catId}',$catId,$mainTpl);
			$products = '';
			foreach($this->data[$catId] AS $key=>$data){
				$tmpProduct = str_replace('{Product_ID}',$data['Product_ID'],$detailTpl);
				$tmpProduct = str_replace('{Model_Name}',$data['Model_Name'],$tmpProduct);
				$tmpProduct = str_replace('{HighPic}',$data['HighPic'],$tmpProduct);
				$tmpProduct = str_replace('{path}',$data['path'],$tmpProduct);
				$tmpEan = '';
				if(isset($data['EAN_UPCS']) && count($data['EAN_UPCS']) > 0){
					foreach($data['EAN_UPCS'] AS $ean){
						$tmpEan .= str_replace('{ean}',$ean,$eanTpl);
					}
				}
				$tmpProduct = str_replace('{eanTable}',$tmpEan,$tmpProduct);
				$products .= $tmpProduct;
			}
			$return = str_replace('{products}',$products,$return);
		}
		return $return;
	}
}
?>