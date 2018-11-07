<script>
function agent_sendemail(tid, bcid)
{
	var leadid = document.getElementById("leadid").value;
	var from = document.getElementById("emailfrom").value;
	var to = document.getElementById("emailto").value;
	var cc = document.getElementById("emailcc").value;
	var subject = document.getElementById("subject").value;
	var message = document.getElementById("emailbody").value;
	var attachment = $('.attachments').map(function() { return $(this).text(); }).get().join(",");
	var texts = mce.getContent();
	texts = encodeURI(texts);
	texts = encodeURIComponent(texts);
	message = texts;
	var http = getHTTPObject();
	var params = 'tid='+tid+'&act=sendemail&bcid='+bcid+'&projectid='+$("#switchprojectid").val()+'&from='+from+'&to='+to+'&cc='+cc+'&subject='+subject+'&uid='+userid+'&leadid='+leadid+'&message='+message+'&attachment='+attachment;
	if ( to == "" ) {
		alert("Please fill up the recipient email address!");
	} else {
		$.ajax({
            url: 'emailtemplate/emailtabsendemail.php',
            type: 'POST',
            data: params,
            success: function(resp){
                alert(resp)
            }
        });
	}
}
	function removeattachment(templateid, attch, ct) {
		jQuery("#div_"+ct).remove();
		// jQuery.ajax({
            // success: function(data)
            // {
                // jQuery("#div_"+ct).remove();
            // },
            // url: 'emailer.php?act=removeattachment&attachment='+attch+'&templateid='+templateid
         // });
	}
	function removeadditionalattachment(t) {
		jQuery(t).parent("div").remove();
		// jQuery.ajax({
            // success: function(data)
            // {
                // jQuery("#div_"+ct).remove();
            // },
            // url: 'emailer.php?act=removeattachment&attachment='+attch+'&templateid='+templateid
         // });
	}
	function submitattachment(templateid) {
		var leadid = document.getElementById("leadid").value;
		
		cfile = $("#MAX_FILE_SIZE").val();
		$.ajax({
            url: "emailtemplate/emailtabuploader.php",
            type: 'POST',
	        data: {
	            "templateid" : templateid,
	            "cfile" : cfile,
	            "leadid" : leadid
	        },
            success: function(data){
				alert(data);
            }
        });
	}
</script>
