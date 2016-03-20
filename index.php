<?php
include('classes/DataImport.php');
$products = new DataImport(2,'test_productindex.xml',1);
?>
<!DOCTYPE html> 
<html>
<head>
	<link href="css/products.css" rel="stylesheet" />
</head>
<body>
	<?php echo $products->filterProductData('587'); ?>
</body>
</html>