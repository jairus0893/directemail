<script>

function agent_sendemail(tid, bcid)
{
	var leadid = document.getElementById("leadid").value;
	var from = document.getElementById("emailfrom").value;
	var to = document.getElementById("emailto").value;
	var cc = document.getElementById("emailcc").value;
	var subject = document.getElementById("subject").value;
	var message = document.getElementById("emailbody").value;
	var deliverymethod = document.getElementById("delivery").value;  // Direct Mailing
	var activationcode = document.getElementById("activationcode").value;
	var attachment = $('.attachments').map(function() { return $(this).text(); }).get().join(",");
	texts = encodeURI(message);
	texts = encodeURIComponent(texts);
	// var texts = mce.getContent();
	// message = texts;
	var http = getHTTPObject();

	// Direct Mailing
	var params = 'tid='+tid+'&act=agentsendemail&bcid='+bcid+'&projectid='+$("#switchprojectid").val()+'&from='+from+'&to='+to+'&cc='+cc+'&subject='+subject+'&uid='+userid+'&leadid='+leadid+'&message='+message+'&attachment='+attachment+'&delivery='+deliverymethod+'&body='+texts+'&level=agent';
	if ( to == "" ) {
		Ext.MessageBox.show({
			title:'Incomplete',
			msg: 'Please fill up the recipient email address!',
			width : 350,
			closable : false,
			buttons: Ext.MessageBox.OK,
			icon : Ext.MessageBox.WARNING
            
        });
	} else {


		if (activationcode == 'ACTIVATED'){ 
			
			Ext.MessageBox.show({
				title: 'Please wait',
				msg: 'Sending Email',
				progressText: 'Initializing...',
				width:300,
				progress:true,
				closable:false
			});

			var qw = function(v){
				return function(){
					if(v == 12){
						Ext.MessageBox.hide();
					}else{
						var i = v/11;
						Ext.MessageBox.updateProgress(i, Math.round(100*i)+'% completed');
					}
				};
			};
			for(var i = 1; i < 11; i++){
				setTimeout(qw(i), i*2000);
			}

			$.ajax({
				url: 'https://directemail.bluecloudaustralia.com.au/directemail/maildelivery.php',
				type: 'POST',
				data: params,	
				success: function(resp){ 
					Ext.MessageBox.show({
						title:'Mail Delivery Message',
						msg: resp,
						width : 350,
						closable : false,
						buttons: Ext.MessageBox.OK,
						icon : Ext.MessageBox.INFO 
					});
      
				}
			});
		
		}else{
			Ext.MessageBox.show({
				title:'Information',
				msg: 'Email From is not yet verified. Please contact your administrator!',
				width : 350,
				closable : false,
				buttons: Ext.MessageBox.OK,
				icon : Ext.MessageBox.WARNING
				
			});

		}





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
