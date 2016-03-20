<!DOCTYPE html> 
<html>
<?php
//First lets get the xml-files
$files = scandir('xml');
?>
<body>
<div id="fileSelector">
	<div>Select the xmlfile to parse</div>
	<div>
		<select id="fileSelector">
			<option value="">Please choose</option>
			<?php foreach($files AS $file){
				if($file != '.' && $file != '..'){
					echo '<option value="'.$file.'">'.$file.'</option>'.PHP_EOL;
				}
			} ?>
		</select>
	</div>
</div>


<div id="result">
	
</div>


<script src="https://code.jquery.com/jquery-1.11.3.js"></script>
<script type="text/javascript">
	$(function(){
		$('#fileSelector').change(function(){
			// console.log($('#fileSelector').val());
			var val = $("#fileSelector option:selected" ).val();
			if(val != ''){
				$.ajax({
					url: 'ajax/parse.php',
					//dataType: 'json',
					data: { file : val },
					method: 'post'
				}).done(function(data){
					$('#result').html(data);
				})
			}
		});
	});
</script>
</body>
</html>