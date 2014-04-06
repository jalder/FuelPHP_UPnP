<div class="container">
Explorer
<div class="row">
<div class="col-sm-12">
Breadcrumb > Trail > Goes Here
</div>
<div class="row">
<div class="col-sm-3">
<ul>
<li>Sources
	<ul>
		<li>Plex</li>
	</ul>
</li>
<li>Booksmarks
	<ul>
		<li>Bookmart 1</li>
	</ul>
</li>
</ul>
</div>
<div class="col-sm-9">
<div id="explorer" class="" style="height:300px; overflow-y: scroll;">

</div>
</div>
</div>
<div class="row">
<div class="col-sm-12">
Meta Data Details and Filename Information 
<select><option>Play On</option></select> <button>Add</button> <button>Download</button> <button>Enqueue</button> <button>Select</button>
</div>
</div>
</div>
<script type="text/javascript">
var jQueryScriptOutputted = false;
function initJQuery() {
    
    //if the jQuery object isn't available
    if (typeof(jQuery) == 'undefined') {
    
        if (! jQueryScriptOutputted) {
            //only output the script once..
            jQueryScriptOutputted = true;
            
            //output the script (load it from google api)
	    document.write("<scr" + "ipt type=\"text/javascript\" src=\"https://code.jquery.com/jquery.js\"></scr" + "ipt>");
        }
        setTimeout("initJQuery()", 50);
    } else {
                        
        $(function() {  
            // do anything that needs to be done on document.ready
            // don't really need this dom ready thing if used in footer
        });
    }
            
}
initJQuery();
/**

	$.get('/upnp/server/browse?description=http%3A%2F%2F192.168.1.52%3A1751%2FDeviceDescription.xml',{},function(data){

		buildlist(data);

	},'json');
**/
</script>
<script type="text/javascript">
	function buildlist(data){
		//alert(data);
		$('#explorer').html('');
		$.each(data,function(key,val){
			$('#explorer').append('<a href="/upnp/server/browse?directory='+encodeURI(val['parent'])+'&location='+key+'" class="treeitem"><h1 class=""><span class="glyphicon glyphicon-folder-close"></span></h1>..</a>');
			if(val.container){
				$.each(val.container,function(obkey,obval){
					$('#explorer').append('<a href="/upnp/server/browse?directory='+encodeURI(obval['@attributes'].id)+'&location='+key+'" class="treeitem"><h1 class=""><span class="glyphicon glyphicon-folder-close"></span></h1>'+obval['dc:title']+'</a>');
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
					$('#explorer').append('<a href="'+link+'" class="movieitem" data-toggle="modal" data-target="#movieModal"><h1 class=""><span class="glyphicon glyphicon-film"></span></h1>'+obval['dc:title']+'</a>');
				});
				}else{
					console.log(val.item);
					if($.isArray(val.item['res'])){
						var link = val.item['res'][0];
					}
					else{
						var link = val.item['res'];
					}
					$('#explorer').append('<a href="'+link+'" class="treeitem"><h1 class=""><span class="glyphicon glyphicon-file"></span></h1>'+val.item['dc:title']+'</a>');
				}
			}
		});
		$('.treeitem').addClass('col-sm-2');
		$('.movieitem').addClass('col-sm-2');
	}
$(document).ready(function(){
	//$('#movieModal').modal('show');
	$('.treeitem').addClass('col-sm-1');
	var description = 'http%3A%2F%2F192.168.1.20%3A32469%2FDeviceDescription.xml';
	$.get('/upnp/server/browse?description='+description,{},function(data){
		buildlist(data);
		},'json');

	$('#explorer').on('click','a.list-group-item',function(){
		return false;
	});

	$('#explorer').on('click','a',function(){
		return false;
	});

	$('#explorer').on('dblclick','a.treeitem',function(){
		console.log($(this).attr('href'));
		$.get($(this).attr('href'),{},function(data){
			console.log(data);
			buildlist(data);
		},'json');
		return false;
	});

	$('#explorer').on('dblclick','a.movieitem',function(){
		console.log('Playing Movie: ');
		$('#movieModal input[name="url"]').attr('value',$(this).attr('href'));
		$('#movieModal').modal('show');

		//do get
		//var link = $(this).attr('href');
		//var device = //get the device id from somewhere...
		return false;
	});

	$('#movieModal').on('dblclick','.play-movie',function(){
		console.log('Playing Movie');
		//do get
		//var device = $('#movieModal input[name="device"]').val();
		var device = $(this).attr('rel');
		var url = $('#movieModal input[name="url"]').val();
		$.get('/cast/device/play',{'device':device,'url':url},function(data){
			console.log(data);
		},'html');
		return false;
	});

});

</script>
