<script>
function updatetemplate(templateid,test)
	{
       

		// var texts              = mce.getContent();
		// texts                  = encodeURI(texts);
		// texts                  = encodeURIComponent(texts);
		var emailfrom          = document.getElementById('emailfrom').value;
        var emailfromname      = document.getElementById('emailfromname').value;
		var template_disposend = jQuery("#template_disposend").val();
		var template_subject   = document.getElementById('template_subject').value;
		var template_name      = document.getElementById('template_name').value;
		var mailserver         = document.getElementById('mailserver').value;
		var mailcc             = jQuery("#emailcc").val();
        var mailbcc            = jQuery("#emailbcc").val();
		var mailencryption     = jQuery("#mailencryption").val();
		var mailport           = document.getElementById('mailport').value;
		var mailuser           = document.getElementById('mailuser').value;
		var mailpass           = document.getElementById('mailpass').value;
        var deliverymethod     = document.getElementById("deliverymethod").value;
        var mailto             = $("#testmailto").val();
        var editable           = $("#editable").val();
        var sigid              = $("#sigid").val();
        var stored_emailfrom   = document.getElementById('stored_emailfrom').value;
        var activation         = document.getElementById('activationcode').value;
		var data = 'act=updatetemplate&templateid='+templateid+'&emailfrom='+emailfrom+'&emailfromname='+emailfromname+'&mailserver='+mailserver+'&mailencryption='+mailencryption+'&mailport='+mailport+'&mailuser='+mailuser+'&mailpass='+mailpass+'&template_subject='+template_subject+'&template_name='+template_name+'&disposend='+template_disposend+'&emailcc='+mailcc+'&emailbcc='+mailbcc+'&test='+test+'&mailto='+mailto+'&editable='+editable+"&sigid="+sigid+"&delivery="+deliverymethod;
        

        $.ajax({
            url: '../interface-dev/directemail/activationcode.php?tid='+ templateid +'&act=getstoredfrom',
            type: 'POST',
            success: function(resp){
              updatedfrommail(resp);
       
            }
        });


        function updatedfrommail(response) {
            
            var responseArray    = response.split( ',' );  
            var storedemail      = responseArray[ 0 ];
            var storedactivation = responseArray[ 1 ];
            
        
            if ((emailfrom == storedemail) && (storedactivation == 'ACTIVATED'))  {


                jQuery.ajax({   
                type: 'POST',
                success: function(data){
                    if (test == false) {
                        Ext.MessageBox.alert('Status', 'Template Updated!');
                    
                    } else {
            
                        
                        var form = new Ext.form.FormPanel({
                            frame: true,

                            items: [{
                                xtype: 'fieldset',
                                title: 'Email Send To *',
                                autoHeight: true,
                                defaults: {
                                    width: 280
                                },

                                items: [{
                                        xtype: 'textfield',
                                        fieldLabel: 'Send Test Mail to',
                                        name: 'emailto',
                                        id: 'emailto'
                                    
                                    }, {
                                        html: '<div id="p2" style="width:50%;"></div>'
                                    
                                    }

                                ]
                            }]

                        });

                        var sendemailwin = new Ext.Window({

                            bodyStyle: 'padding: 10px',
                            width: 460,
                            autoHeight: true,
                            items: form,
                            modal: true,
                            layout: 'form',
                            title: 'Test Mail',

                            buttons: [ {
                                text: 'Send',

                                handler: function() {
                                    
                                    Ext.MessageBox.show({
                                        title: 'Please wait',
                                        msg: 'Sending Email',
                                        progressText: 'Initializing...',
                                        width:300,
                                        progress:true,
                                        closable:true
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

                                    var mailto = Ext.getCmp('emailto').getValue();  
                                    var sdata = 'to='+mailto+'&from='+emailfrom+'&subject='+template_subject;
                                    $.ajax({
                                        url: 'https://directemail.bluecloudaustralia.com.au/directemail/maildelivery.php?act=defaultsendemail&delivery='+deliverymethod+'&tid='+templateid+'&uid=<?php echo $_SESSION['auth'];  ?>'+'&bcid=<?php echo $_SESSION['bcid'];  ?>',
                                        type: 'POST',
                                        data: sdata,
                                        success: function(resp){    
                                            Ext.MessageBox.show({
                                                title:'Mail Delivery Message',
                                                msg: resp,
                                                minWidth:  800,
                                                closable : true,
                                                buttons: Ext.MessageBox.OK,
                                                icon : Ext.MessageBox.INFO 
                                            });
      
                                        },
                                        error:function(){
                                            alert("Error");
                                        }  
                                    });
                                }

                            }, {
                                text: 'Cancel',

                                handler: function() {
                                    sendemailwin.close();
                                }

                            }]

                        });

                        sendemailwin.show();

                        
                    }
                },
                url: 'admin.php',
                
                data: data
                
                });
            }else{
                var formactivate = new Ext.form.FormPanel({
                frame: true,
                
                items: [{
                        html: '<p style="font-weight: bold !important; color:#ff0000;text-align: center;">Email From Address has been changed or not yet verified:</p><br/>',
                    },{
                        html:  '<p style="font-weight: bold !important; text-align: center;">' + emailfrom + '</p><br/>',
                    
                    
                    },{
                        html: ' <p style="text-align: center;">Please verify <span style="font-weight: bold !important;">Email From Address </span> first before you proceed</p>'
                    
                    }
                ]});

                var sendformactivate = new Ext.Window({

                bodyStyle: 'padding: 10px',
                width: 300,
                autoHeight: true,
                items: formactivate,
                modal: true,
                layout: 'form',
                title: 'Email Verification',

                buttons: [ {
                    text: 'Verify',

                    handler: function() {
                        verifyfrommail(templateid,emailfrom);
                        sendformactivate.close();
                    }

                }, {
                    text: 'Cancel',

                    handler: function() {
                        sendformactivate.close();
                    }

                }],

                });
                
                sendformactivate.show();
            }
        }

        
} //end Main Function

function verifyfrommail(templateid,emailfrom){
   
       
    $.ajax({
        url: '../interface-dev/directemail/activationcode.php?tid='+ templateid +'&act=verification',
        type: 'POST',
        success: function(resp){
        console.log(resp);
       
        }
    });
    
    Ext.MessageBox.show({
           title: 'Please wait',
           msg: 'Sending Email',
           progressText: 'Initializing...',
           width:300,
           progress:true,
           closable:false
    });

    var f = function(v){
            return function(){
                if(v == 12){
                    // Ext.MessageBox.hide();
                }else{
                    var i = v/11;
                    Ext.MessageBox.updateProgress(i, Math.round(100*i)+'% completed');
                }
           };
       };
       for(var i = 1; i < 13; i++){
           setTimeout(f(i), i*2000);
       }


    $.ajax({
        url: '../interface-dev/directemail/verifyfrommail.php?from='+ emailfrom + '&tid='+ templateid,
        type: 'POST',
        success: function(resp){

                Ext.MessageBox.show({
                title:'Mail Delivery Message',
                msg: resp,
                width : 350,
                closable : false,
                buttons: Ext.MessageBox.OK,
                
                fn : function(buttonValue, inputText, showConfig){
                    
                    if (buttonValue == 'ok'){
                        _codeactivate(templateid,emailfrom);
                    } 
                },
                    icon : Ext.MessageBox.INFO 
                });
        } 
    });
   
}


function _codeactivate (templateid,emailfrom){

    var formcodeactivate = new Ext.form.FormPanel({
    frame: true,
    defaults: {
                 width: 280
    },
    
    items: [{
            html: '<p style="text-align: center; font-size: 13px;">Enter your verification code sent to your <br/>email address</p><br/>',
        },{
            
            xtype: 'textfield',
            fieldLabel: 'Code',
            name: 'vercode',
            id: 'vercode',
            width: 150,
            style: 'margin-left: -38px'
        
        }]

    });

    var sendcodeactivate = new Ext.Window({

    bodyStyle: 'padding: 10px',
    width: 320,
    autoHeight: true,
    items: formcodeactivate,
    modal: true,
    layout: 'form',
    title: 'Code Activation',

    buttons: [ {
        text: 'Verify',

        handler: function() {
                var vercode = Ext.getCmp('vercode').getValue();  

                $.ajax({
                    url: '../interface-dev/directemail/activationcode.php?tid='+ templateid +'&act=activate&code=' + vercode + '&emailfrom=' + emailfrom,
                    type: 'POST',
                    success: function(resp){
                    
                        if (resp == 'activated'){

                            Ext.MessageBox.show({
                            title:'Activated',
                            msg: 'Successfully Activated',
                            width : 350,
                            closable : false,
                            buttons: Ext.MessageBox.OK,
                            fn : function(buttonValue, inputText, showConfig){
                
                                if (buttonValue == 'ok'){
                                    $("#verifyfrom").hide();
                                    $("#check_active").show();    //active icon
                                    sendcodeactivate.close();
                                   
                                } 
                            },
                                icon : Ext.MessageBox.INFO 
                            });
                                
                            
                        }else{
                            Ext.MessageBox.show({
                            title:'Invalid',
                            msg: 'Invalid Code. Please Try Again',
                            width : 350,
                            closable : false,
                            buttons: Ext.MessageBox.OK,
                                icon : Ext.MessageBox.ERROR
                            });
                                       
                        }
                    
                    }
                });
            
        }

    }, {
        text: 'Cancel',

        handler: function() {
            sendcodeactivate.close();
        }

    }],
    });
    
    sendcodeactivate.show();

}



//  Direct Mailing 

function changemailmethod($i,tempid){
    if (($i) == 'relay') {
    
        
        Ext.MessageBox.show({
        title:'Switch to Relay Mailing',
        msg: 'Are you sure you want to switch using relay method?',
        width : 350,
        closable : false,
        buttons: Ext.MessageBox.YESNO,
            fn : function(buttonValue, inputText, showConfig){
        
                if (buttonValue == 'yes'){
                    $.ajax({
                        url: 'admin.php?act=updatedelivery&method=relay&tempid='+tempid,
                        success: function(resp){
                            $("#advancedemail").show();
                            $("#check_active").show();    //active icon
                            $("#verifyfrom").hide();      //verify icon
                            Ext.MessageBox.alert('Status', 'Template Updated!');
                        }
                    });
                   

                    
                }else{
                    $("#deliverymethod").val('Direct Mailing');
                } 
            },
            icon : Ext.MessageBox.WARNING
            
        });


        
    }else if (($i) == 'direct') {
        
        
        Ext.MessageBox.show({
        title:'Switch to Direct Mailing',
        msg: 'Are you sure you want to switch to direct mailing?',
        width : 350,
        closable : false,
        buttons: Ext.MessageBox.YESNO,
            fn : function(buttonValue, inputText, showConfig){
        
                if (buttonValue == 'yes'){
                    var emailfrom = $('#emailfrom').val()
                    $.ajax({
                        url: 'admin.php?act=updatedelivery&method=direct&tempid='+tempid+'&emailfrom='+emailfrom,
                        success: function(resp){
                            if (resp == 'direct-deactivated') {
                                $("#check_active").hide();    //active icon
                                $("#verifyfrom").show();    
                            }else{       
                                $("#check_active").show();    //active icon
                                $("#verifyfrom").hide();      //verify icon
                            }
                            $("#advancedemail").hide();
                            Ext.MessageBox.alert('Status', 'Template Updated!');
                        }
                    });

                }else{
                    $('#deliverymethod').val($('#deliverymethod > option:last').val());
                }
            },
            icon : Ext.MessageBox.WARNING
            
        });
    
    }
   
}

</script>
