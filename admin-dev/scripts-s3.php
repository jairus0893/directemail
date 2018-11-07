<?php
?>
<script>
confirm = false;
function attachcompletes3($s3filepath, $filename) {
	var atts = document.getElementById('atts');
	atts.innerHTML = atts.innerHTML + '<a href="'+$s3filepath+'">'+$filename+'</a><br />';
}
function removeattachments3(templateid, attch, prefix, cts3) {
	jQuery.ajax({
		url: 'admin-s3.php?act=removeattachments3&templateid='+templateid+'&attachment='+attch+'&prefix='+prefix,
        success: function(data) {
            jQuery("#divs3_"+cts3).remove();
            cmess(data);
        },
     });
}
function deletefiles3(filename,prefix,pid) {
	jQuery.ajax({
		url: 'admin-s3.php?act=deletefiles3&projectid='+pid+'&filename='+filename+'&prefix='+prefix,
		success: function(data) {
			cmess(data);
			manage_persist(pid);
		}
	});
}
function removesigimages3(sigid, image, prefix, cts3) {
	jQuery.ajax({
		url: 'admin-s3.php?act=removesigimages3&image='+image+'&sigid='+sigid+'&prefix='+prefix,
        success: function(data) {
            jQuery("#divs3_"+cts3).remove();
            cmess(data);
        },
     });
}
function removeexclusions3(eid,prefix) {
    $.ajax({
        url: 'admin-s3.php?act=removeexclusions3&id='+eid+'&prefix='+prefix,
        success: function(resp){
        	cmess("Exclusion list deleted successfully.");
        	listMenu('manageexclusion');
        }
    });
}
function removedispoupdates3(did,prefix) {
     $.ajax({
        url:'admin-s3.php?act=removedispoupdates3&id='+did+'&prefix='+prefix,
        success: function(resp){
        	cmess("Deleted successfully.");
            listMenu('dispoupdate');
        }
    })
}
function removedncs3(dncid,prefix)
{
    $.ajax({
        url: 'admin-s3.php?act=removedncs3&id='+dncid+'&prefix='+prefix,
        success: function(resp){
        	//dialogwindow('manexcl');
        	alert("Do Not Call list deleted successfully.");
        	listMenu('managedonotcall');
        }
    });
}
function validuploads3() {
    if (validlistid == true) {
	    var lp = $("#listproj").val();
	    if (lp < 1) {
            $("#listproj").css("border","red 2px solid");
            $("#listproj").attr("title","Select Campaign");
            $("#listproj").attr("placeholder","Select Campaign");
        } else {
        	uploadFiles3();
        }
    } else {
    	alert('Must use valid listid');
    }
}
function uploadFiles3() {
  var xhr = new XMLHttpRequest();
  var fd = new FormData(document.getElementById('uploadcsv'));
  /* event listners */
  xhr.upload.addEventListener("progress", uploadProgress, false);
  xhr.addEventListener("load", uploadComplete, false);
  xhr.addEventListener("error", uploadFailed, false);
  xhr.addEventListener("abort", uploadCanceled, false);
  /* Be sure to change the url below to the url of your upload server side script */
  xhr.open("POST", "leadsloader-s3.php");
  xhr.send(fd);
}
var submapping = false;
function submitmaps3()
{
    if (!submapping)
        {
        	document.getElementById('loading').style.display = 'inline';
          submapping = true;
          var xhr = new XMLHttpRequest();
          var fd = new FormData($("#mapping")[0]);
          xhr.addEventListener("load",function(resp){
              submapping = false;
              respclose(resp);
              getapp('manlist');
          }, false);
          xhr.open("POST", "leadsloader-s3.php");
          xhr.send(fd);
        }
}
function setListDeleteds3(lid,prefix) {
     $.ajax({
        url:'admin-s3.php?act=setListDeleteds3&lid='+lid+'&prefix='+prefix,
        success: function(resp){
            listMenu('managelist');
        }
    })
}
function validuploads3ForList() {
    if (validlistid == true) {
	    var lp = $("#listproj").val();
	    if (lp < 1) {
            $("#listproj").css("border","red 2px solid");
            $("#listproj").attr("title","Select Campaign");
            $("#listproj").attr("placeholder","Select Campaign");
        } else {
        	uploadFiles3ForList();
        	// jQuery('<div/>', {
			    // id: 'dialog-confirm',
			    // title: 'Upload List',
				// style: 'display:none',
				// html: 'Note: Once submitted, you CANNOT edit the wash date and days. Proceed?'
			// }).appendTo('body');
			// $( "#dialog-confirm" ).dialog({
				// resizable: false,
				// height:140,
				// modal: true,
				// buttons: {
					// "Submit": function() {
						// uploadFiles3ForList();
					// },
					// Cancel: function() {
						// $( this ).dialog( "close" );
					// }
				// }
			// });
        }
    } else {
    	alert('Must use valid listid');
    }
}
function uploadFiles3ForList() {
  var xhr = new XMLHttpRequest();
  var fd = new FormData(document.getElementById('uploadcsv'));
  document.getElementById('loading').style.display = 'inline';
  /* event listners */
  xhr.upload.addEventListener("progress", uploadProgress, false);
  xhr.addEventListener("load", uploadComplete, false);
  xhr.addEventListener("error", uploadFailed, false);
  xhr.addEventListener("abort", uploadCanceled, false);
  /* Be sure to change the url below to the url of your upload server side script */
  xhr.open("POST", "leadsloader-s3.php");
  xhr.send(fd);
}
</script>