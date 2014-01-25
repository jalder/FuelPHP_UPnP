<?php
if(isset($description)){
	$initial_pop = '/upnp/server/browse?description='.urlencode($description);
}else{
	$initial_pop = '/upnp/server/browse';
}

?>
<div id="tree" class="list-group">

</div>


<div class="modal fade" id="movieModal" tabindex="-1" role="dialog" aria-labelledby="movieModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">

			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="movieModalLabel">Movie Controls</h4>
			</div>
			<div class="modal-body">
				<input type="hidden" name="device" value="52c8a32c260495702ae8944b" />
				<input type="hidden" name="url" value="" />
			...	<span class="glyphicon glyphicon-play play-movie"></span>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">

/**

	$.get('<?php echo $initial_pop; ?>',{},function(data){

		buildlist(data);

	},'json');
**/
	function buildlist(data){
		//alert(data);
		$('#tree').html('');
		$.each(data,function(key,val){
			$('#tree').append('<a href="/upnp/server/browse?directory='+encodeURI(val['parent'])+'&location='+key+'" class="treeitem list-group-item"><h4 class="list-group-item-heading">..</h4></a>');
			if(val.container){
				$.each(val.container,function(obkey,obval){
					$('#tree').append('<a href="/upnp/server/browse?directory='+encodeURI(obval['@attributes'].id)+'&location='+key+'" class="treeitem list-group-item"><h4 class="list-group-item-heading"><span class="glyphicon glyphicon-folder-close"></span>'+obval['dc:title']+'</h4></a>');
				});
			}
			if(val.item){
				if($.isArray(val.item)){
				$.each(val.item,function(obkey,obval){
					console.log(obval);
					if($.isArray(obval['res'])){
						var link = obval['res'][0];
					}
					else{
						var link = obval['res'];
					}
					$('#tree').append('<a href="'+link+'" class="movieitem list-group-item" data-toggle="modal" data-target="#movieModal"><h4 class="list-group-item-heading"><span class="glyphicon glyphicon-film"></span> '+obval['dc:title']+'</h4></a>');
				});
				}else{
					console.log(val.item);
					if($.isArray(val.item['res'])){
						var link = val.item['res'][0];
					}
					else{
						var link = val.item['res'];
					}
					$('#tree').append('<a href="'+link+'" class="treeitem list-group-item"><h4 class="list-group-item-heading"><span class="glyphicon glyphicon-file"></span>'+val.item['dc:title']+'</h4></a>');
				}
			}
		});
	}
$(document).ready(function(){
//$('#movieModal').modal('show');


	$('#tree').on('click','a.list-group-item',function(){
		return false;
	});

	$('#tree').on('click','a.treeitem',function(){
		console.log($(this).attr('href'));
		$.get($(this).attr('href'),{},function(data){
			console.log(data);
			buildlist(data);
		},'json');
		return false;
	});

	$('#tree').on('click','a.movieitem',function(){
		console.log('Playing Movie: ');
		$('#movieModal input[name="url"]').attr('value',$(this).attr('href'));
		$('#movieModal').modal('show');

		//do get
		//var link = $(this).attr('href');
		//var device = //get the device id from somewhere...
		return false;
	});

	$('#movieModal').on('click','.play-movie',function(){
		console.log('Playing Movie');
		//do get
		var device = $('#movieModal input[name="device"]').val();
		var url = $('#movieModal input[name="url"]').val();
		$.get('/cast/device/play',{'device':device,'url':url},function(data){
			console.log(data);
		},'html');
		return false;
	});

});

</script>

