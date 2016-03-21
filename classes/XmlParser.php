<?php
/**
 *
 */
class XmlParser {
	private $return;
	private $xmlFile = false;
	private $fileExists = false;
	private $xml = false;
	private $pathToXmlFiles = 'C:\xampp\htdocs\Guidance\xml\\';
	private $xmlArray = false;
	private $errors = false;
	function __construct($xmlFile) {
		$this -> xmlFile = $xmlFile;
		$this -> checkFileExist();
		$this->parseXml2();
		// $this -> readFile();
		// $this -> parseXml();
	}

	/**
	 * \brief Adds an error to the return-object.
	 * @author P.Welling
	 * \b The following errors are available:
	 * - 1) The file does not exist
	 * - 2) unable to get the file's contents
	 * - 3) unknown error, needs debugging
	 */
	private function addError($error) {
		$this -> errors = ($this -> errors === false) ? array() : $this -> errors;
		$this -> errors[] = $error;
	}

	/**
	 * \brief Checks if the given xml-file exists.
	 * If not, the passes an error on
	 * @author P.Welling
	 * \b Uses:
	 * - XmlParser::addError()
	 */
	private function checkFileExist() {
		$this -> fileExists = file_exists($this -> pathToXmlFiles . $this -> xmlFile);
		if ($this -> fileExists === false) {
			$this -> addError(1);
		}
	}

	/**
	 * \brief If the file exists, it's content is loaded into the global var.
	 * @author P.Welling
	 * \b Uses:
	 * - XmlParser::addError()
	 */
	private function readFile() {
		if ($this -> fileExists === true) {
			$this -> xml = file_get_contents($this -> pathToXmlFiles . $this -> xmlFile);
			if ($this -> xml === false) {
				$this -> addError(2);
			}
		}
	}

	/**
	 * \brief Function that checks if the xml is acutal set. Calls the parser-function
	 * @author P.Welling
	 * \b Uses:
	 * - XmlParser::xmlToObject()
	 */
	private function parseXml() {
		if ($this -> xml !== false) {
			echo '<prE>' . print_r($this -> xmlToObject(), true) . '</prE>';
			$this -> xmlArray = $this -> xmlToObject();
		}
	}

	private function parseXml2() {
		if ($this -> fileExists === true) {
			$xml = new XMLReader();
			$xml -> open($this -> pathToXmlFiles . $this -> xmlFile);
			$this -> xmlArray = $this -> xml2assoc($xml,'root');
		}
	}

	/**
	 * \brief Returns the xml-array as a result of the call
	 * @author P.Welling
	 */
	function returnArray() {
		if ($this -> return === false && $this -> errors === false) {
			$this -> addError(3);
		}
		return ($this -> return === false) ? $this -> errors : $this -> xmlArray;
	}

	/**
	 * \brief The actual parser of the xmlfile's content
	 * Grabbed from php.net and adjusted to pass on the correct content in case of cdata
	 * @author P.Welling
	 */
	function xmlToObject() {
		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, $this -> xml, $nodes);
		xml_parser_free($parser);
		$arr = array();
		$stack = array();
		foreach ($nodes as $node) {
			//Fix for the node TryCData
			if ($node['tag'] == 'TryCData') {
				$node['value'] = '&lt;![CDATA[' . $node['value'] . ']]&gt;';
			}
			//Fix for the cdata-type returned by the parser
			if ($node['type'] == 'cdata') {
				$node['type'] = 'complete';
			}
			$ind = count($arr);
			if ($node['type'] == 'open' || $node['type'] == 'complete') {
				$arr[$ind] = array();
				$arr[$ind]['name'] = $node['tag'];
				$arr[$ind]['attributes'] = isset($node['attributes']) ? $node['attributes'] : array();
				$arr[$ind]['Value'] = isset($node['value']) ? $node['value'] : '';
				if ($node['type'] == 'open') {
					$arr[$ind]['children'] = array();
					$stack[count($stack)] = &$arr;
					$arr = &$arr[$ind]['children'];
				}
			}
			if ($node['type'] == 'close') {
				$arr = &$stack[count($stack) - 1];
				unset($stack[count($stack) - 1]);
			}
		}
		//now checking if there is a node that contained a value but ended up with children (see the cdata-fix)
		$arr[0] = $this -> correctArray($arr[0]);
		return $arr;
	}

	/**
	 * \brief Another function to parse an xml, but this time to read the file while parsing, especialy fro large files. Plucked from php.net and adjusted to my needs
	 */
	function xml2assoc($xml, $name) {
		$tree = null;
		while ($xml -> read()) {
			if ($xml -> nodeType == XMLReader::END_ELEMENT) {
				return $tree;
			} else if ($xml -> nodeType == XMLReader::ELEMENT && $xml -> isEmptyElement === false) {
				$node = array();
				$node['name'] = $xml -> name;
				$node['attributes'] = array();
				$node['Value'] = '';
				if ($xml -> hasAttributes) {
					$attributes = array();
					while ($xml -> moveToNextAttribute()) {
						$attributes[$xml -> name] = $xml -> value;
					}
					$node['attributes'] = $attributes;
				}
				if ($xml -> isEmptyElement === false) {
					$children = $this->xml2assoc($xml, $node['name']);
					$isText = false;
					if (count($children) > 0) {
						foreach ($children AS $key => $subArr) {
							if (isset($subArr['text'])) {
								$isText = true;
								break;
							}
						}
					}
					if ($isText === false) {
						if($xml->nodeType == XMLReader::CDATA){
							
						} else {
							if(is_array($children)){
								$node['children'] = $children;
							}
						}
					} else {
						if (count($children) > 0) {
							$val = '';
							foreach ($children AS $key => $subArr) {
								if (count($subArr) == '1') {
									if (isset($subArr['text'])) {
										$val .= $subArr['text'];
									} else if (isset($subArr['name'])) {
										$val = '<' . $subArr['name'] . ' />';
									}
								} else {
									$val .= '<' . $subArr['name'] . '>' . $subArr['Value'] . '</' . $subArr['name'] . '>';
								}
							}
							$node['Value'] = $val;
						}
					}
				}
				$tree[] = $node;
			} else if ($xml -> nodeType == XMLReader::TEXT) {
				$node = array();
				$node['text'] = $xml -> value;
				$tree[] = $node;
			} else if ($xml -> nodeType == XMLReader::ELEMENT && $xml -> isEmptyElement === true) {
				$node = array();
				$node['name'] = $xml -> name;
				if ($xml -> hasAttributes) {
					$attributes = array();
					while ($xml -> moveToNextAttribute()) {
						$attributes[$xml -> name] = $xml -> value;
					}
					$node['attributes'] = $attributes;
				}
				$tree[] = $node;
			}
		}
		return $tree;
	}

	/**
	 * \brief loops throught the array and checks if there are children while the content node is fille
	 */
	function correctArray($arr) {
		if (isset($arr['children'])) {
			if (trim($arr['Value']) != '') {
				$tmp = $arr['Value'];
				foreach ($arr['children'] AS $key => $subArr) {
					if ($subArr['name'] != $arr['name']) {
						$tmp .= '&lt;' . $subArr['name'] . '&gt;' . $subArr['Value'] . '&lt;/' . $subArr['name'] . '&gt;';
					} else {
						$tmp .= $subArr['Value'];
					}
				}
				unset($arr['children']);
				$arr['Value'] = $tmp;
			} else {
				foreach ($arr['children'] AS $key => $subArr) {
					$arr['children'][$key] = $this -> correctArray($subArr);
				}
			}
		}
		return $arr;
	}

}
?>