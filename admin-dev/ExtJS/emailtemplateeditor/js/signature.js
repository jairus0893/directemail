// *********** SIGNATURE EDITOR **************
function signatureeditor(sigid, bcid, pid) {

    Ext.onReady(function() {
        var tabs = new Ext.TabPanel({
            region: 'center',
            // margins   : '3 3 3 0', 
            activeTab: 0,
            defaultType: 'textfield',
            defaults: {
                autoScroll: true
            },

            items: [{
                title: 'Message',
                region: 'center',
                xtype: 'htmleditor',
                // margin:'0 0 0 0',
                id: 'sig',
            }],

            buttons: [{
                text: 'Update',
                handler: function() {
                    var sig = Ext.getCmp('sig').getValue();
                    var signame = Ext.getCmp('signaturename').getValue();

                    if (signame != '') {
                        Ext.Ajax.request({
                            type: 'post',
                            params: {
                                sigbody: sig,
                                signame: signame
                            },
                            url: './ExtJS/emailtemplateeditor/signatureeditor.php?sigid=' + sigid + '&act=updatesignature',

                            success: function(response) {

                                Ext.MessageBox.alert('Status', 'Successfully Updated');

                            }
                        });
                    } else {
                        Ext.MessageBox.alert('Status', 'Please Input Signature Name');

                    }
                }
            }, {

                text: 'Upload Logo',
                handler: function() {
                    uploadsigimg(sigid, bcid, pid);
                }
            }]
        });

        $.ajax({

            url: './ExtJS/emailtemplateeditor/signatureeditor.php?sigid=' + sigid + '&act=getsignaturebody',
            type: 'POST',
            success: function(resp) {
                Ext.getCmp('sig').setValue(resp);

            }
        });

        var xy = document.getElementById("custom-fsig").value;

        if (xy != 'null') {
            var x = JSON.parse(xy);

            function myFunction(x) {
                var out = "";
                var i;
                for (i = 0; i < x.length; i++) {
                    str = x[i].name;
                    // out +=  '<a href="#" draggable="true" ondragstart="dragmerge(event,\'custom-'+ x[i].name + '\' )" class="default-fields">'+ x[i].name +'</a>'
                    out += '<a href="#" draggable="true" ondragstart="dragmerge(event,\'custom-' + x[i].name + '\' )" class="default-fields">' + x[i].name + '</a>'
                }

                htmlnew = out;
            }

            myFunction(x);
        } else {

            htmlnew = "";

        }

        var nav = new Ext.Panel({
            title: 'Drag Merge Fields',
            region: 'east',
            split: true,
            width: 200,
            collapsible: true,
            autoScroll: true,

            items: [{

                html: '<div class="custom-fields">MAIN INFORMATION FIELDS</div>',
            }, {
                html: '<a href="#" draggable="true" ondragstart="dragmerge(event,\'name\')" class="default-fields">Name</a>'
            }, {
                html: '<a href="#" draggable="true" ondragstart="dragmerge(event,\'cfname\')" class="default-fields">Firstname</a>'
            }, {
                html: '<a href="#" draggable="true" ondragstart="dragmerge(event,\'clname\')" class="default-fields">Surname</a>',
            }, {
                html: '<a href="#" draggable="true" ondragstart="dragmerge(event,\'state\')" class="default-fields">State</a>',
            }, {
                html: '<a href="#" draggable="true" ondragstart="dragmerge(event,\'address1\')" class="default-fields">Address</a>',
            }, {
                html: '<a href="#" draggable="true" ondragstart="dragmerge(event,\'phone\')" class="default-fields">Phone</a>',
            }, {
                html: '<div class="custom-fields">ACCOUNT FIELDS</div>',
            }, {
                html: '<a href="#" draggable="true" ondragstart="dragmerge_a(event,\'afirst\')" class="default-fields">Agent FirstName</a>'
            }, {
                html: '<a href="#" draggable="true" ondragstart="dragmerge_a(event,\'alast\')" class="default-fields">Agent LastName</a>',
            }, {
                html: '<a href="#" draggable="true" ondragstart="dragmerge_a(event,\'email\')" class="default-fields">Agent Email</a>',
            }, {
                html: '<a href="#" draggable="true" ondragstart="dragmerge_a(event,\'phone\')" class="default-fields">Agent Phone</a>',
            }, {
                html: '<div class="custom-fields">CLIENT FIELDS</div>',
            }, {
                html: '<a href="#" draggable="true" ondragstart="dragmerge_cl(event,\'company\')" class="default-fields">Client Company</a>'
            }, {
                html: '<a href="#" draggable="true" ondragstart="dragmerge_cl(event,\'address1\')" class="default-fields">Client Address1</a>'
            }, {
                html: '<a href="#" draggable="true" ondragstart="dragmerge_cl(event,\'address2\')" class="default-fields">Client Address2</a>'
            }, {
                html: '<a href="#" draggable="true" ondragstart="dragmerge_cl(event,\'state\')" class="default-fields">Client State</a>'
            }, {
                html: '<a href="#" draggable="true" ondragstart="dragmerge_cl(event,\'companyurl\')" class="default-fields">Client Website</a>'
            }, {
                html: '<a href="#" draggable="true" ondragstart="dragmerge_cl(event,\'email\')" class="default-fields">Client Email</a>'
            }, {
                html: '<a href="#" draggable="true" ondragstart="dragmerge_cl(event,\'phone\')" class="default-fields">Client Phone</a>'
            }, {
                html: '<div class="custom-fields">CUSTOM FIELDS</div>',
            }, {
                html: htmlnew
            }]
        });


        // Panel for the north
        var nav2 = new Ext.Panel({
            title: 'Signature Name',
            region: 'north',
            autoWidth: true,
            labelWidth: 75,
            height: 60,

            margins: '10 10 10 10',
            cmargins: '3 3 3 3',
            defaults: {
                width: 970,
                height: 40
            },
            defaultType: 'textfield',


            items: [{
                region: 'center',
                fieldLabel: 'Signature Name',
                name: 'first',
                allowBlank: false,
                anchor: '100%',
                emptyText: 'Type Signature Name Here',
                id: 'signaturename',
                validator: function(val) {
                    return (val.trim().length > 0) ? true : "This field may not be empty";
                }

            }]

        });

        $.ajax({

            url: './ExtJS/emailtemplateeditor/signatureeditor.php?sigid=' + sigid + '&act=getsignaturename',
            type: 'POST',
            success: function(resp) {
                Ext.getCmp('signaturename').setValue(resp);

            }
        });

        var win = new Ext.FormPanel({

            title: 'Email Editor',
            closable: true,
            width: 1000,
            height: 500,
            renderTo: 'signaeditor',
            plain: true,
            layout: 'border',
            items: [

                nav2, nav, tabs
            ],
        });


    });

}


// *********** FILE UPLOAD **************





function uploadsigimg(sigid, bcid, pid) {
    
    

    
    var form = new Ext.form.FormPanel({
        frame: true,
        fileUpload: true,

        items: [ {
            html: ' <p style="text-align: center; font-size: 13px; margin-bottom: 12px;">Do you want to load from the library? <br/> or upload from your local files</p>',
    
        }, {
            xtype: 'fileuploadfield',
            id: 'form-file',
            emptyText: 'Please select file to upload',
            fieldLabel: 'File',
            name: 'photo-path',
            buttonOnly: true,
            hideLabel: true,
            buttonText: 'Load From Files',
            width: 120,

            listeners: {
                        'fileselected': function(fb, v){

                            form.getForm().submit({
                                url: './ExtJS/emailtemplateeditor/file-upload_s3.php?sigid=' + sigid + '&bcid=' + bcid + '&pid=' + pid + '&act=signatureattachment&imageupload=false',
                                waitMsg: 'Inserting your logo...',
                                success: function(form, action) {
            
                                    var dec = encodeURI(action.result.localsrc);
                                    var dec = "<img src="+dec+" width=150 height= 150>"
                                    var currentbody = Ext.getCmp('sig').getValue();
                                    var newbody     = currentbody + dec;
                                    Ext.getCmp('sig').setValue(newbody);
                                    confirmsavelogo();
                                }
                                
                            });
                            function confirmsavelogo(){
                                Ext.Msg.show({
                                    title : 'Confirm',
                                    msg : 'Do you want to save this logo in the library?',
                                    buttons : Ext.Msg.YESNO,
                                    close: false,
                                    fn : function(buttonValue, inputText, showConfig){
                                
                                        if (buttonValue == 'yes'){
                                            Ext.MessageBox.prompt('Logo', 'Please enter the name of your logo:', showResultText);
                                            function showResultText(btn, text){
                                                if(btn =='ok'){
                                                    
                                                    form.getForm().submit({
                                                        params: {nameval: text},
                                                        url: './ExtJS/emailtemplateeditor/file-upload_s3.php?sigid=' + sigid + '&bcid=' + bcid + '&pid=' + pid + '&act=signatureattachment&imageupload=true',
                                                        waitMsg: 'Uploading your logo...',
                                                        success: function(form, action) {
                                                            Ext.Msg.alert('Success', 'Your file "' + action.result.file + '" is successfully uploaded');
                                                        },
                                                        failure: function(form, action) {
                                    
                                                            Ext.Msg.alert('Failure!', 'Error info: ' + action.result.error);
                                                        }
                                    
                                                    });
                                                }else{
                                                    form.getForm().reset();
                                                }
                                                
                                            };
                                        }else{
                                            
                                        }
                                    },
                                    icon : Ext.MessageBox.QUESTION
                                });
                            }
                            
                            
                        }
                    }
        
        },{
            html: '<div id="fi-button" class="loadlibrary"></div>',
        }]

        
    });

    var window = new Ext.Window({
        title: 'Logo(s)',
        bodyStyle: 'padding: 10px',
        width: 300,
        autoHeight: true,
        items: form,
        modal: true,
        layout: 'form'

        // buttons: [{
        //     iconCls: 'x-upload-folder',
        //     text: 'Load From Files',
        //     handler: function() {
        //         uploadsigimg2(sigid, bcid, pid);
                
        //     }
           

        // }, {
        //     iconCls: 'x-edit-image',
        //     text: 'Load From Library',

        //     handler: function() {
        //         window.close();


        //         chooser = new ImageChooser({
        //             url: './ExtJS/emailtemplateeditor/get-images.php?sigid=' + sigid + '&bcid=' + bcid + '&pid=' + pid + '&act=getsigfiles',
        //             sigid: sigid,
        //             bcid: bcid,
        //             projectid: pid,
        //             type: 'signature',
        //             width: 500,
        //             height: 350
        //         });

        //         chooser.show();

        //     }
        // }]
    });

   

    

    window.show();

    function loadlibrary(){
        alert('tset');
    }

    var btnform = new Ext.Button({
        renderTo: 'fi-button',
        text: 'Load From Library',
        handler: function() {
            window.close();


            chooser = new ImageChooser({
                url: './ExtJS/emailtemplateeditor/get-images.php?sigid=' + sigid + '&bcid=' + bcid + '&pid=' + pid + '&act=getsigfiles',
                sigid: sigid,
                bcid: bcid,
                projectid: pid,
                type: 'signature',
                width: 500,
                height: 350
            });

            chooser.show();

        }
    });
}





// function uploadsigimg2(sigid, bcid, pid) {

//     var form = new Ext.form.FormPanel({
//         baseCls: 'x-plain',
//         labelWidth: 55,
//         defaultType: 'textfield',
//         fileUpload: true,
//         defaults: {
//             anchor: '95%',
//             allowBlank: false,
//             msgTarget: 'side'
//         },

//         items: [{

//             xtype: 'textfield',
//             fieldLabel: 'Logo Name',
//             name: 'name-file',

//         }, {
//             xtype: 'fileuploadfield',
//             id: 'form-file',
//             emptyText: 'Please select file to upload',
//             fieldLabel: 'File',
//             name: 'photo-path',
//             buttonCfg: {
//                 text: '',
//                 iconCls: 'x-upload-folder'
//             },
//             listeners: {
//                         'fileselected': function(fb, v){
//                             form.getForm().submit({
            
//                                 url: './ExtJS/emailtemplateeditor/file-upload_s3.php?sigid=' + sigid + '&bcid=' + bcid + '&pid=' + pid + '&act=signatureattachment',
//                                 waitMsg: 'Uploading your file...',
//                                 success: function(form, action) {
            
//                                     Ext.Msg.alert('Success', 'Your file "' + action.result.file + '" is successfully uploaded');
//                                 },
//                                 failure: function(form, action) {
            
//                                     Ext.Msg.alert('Failure!', 'Error info: ' + action.result.error);
//                                 }
            
//                             });
//                         }
//                     }
        

//         }]
//     });

//     var window = new Ext.Window({
//         title: 'Logo(s)',
//         width: 500,
//         minWidth: 300,
//         minHeight: 200,
//         layout: 'fit',
//         plain: true,
//         bodyStyle: 'padding:5px;',
//         buttonAlign: 'center',
//         items: form,


//         buttons: [{
//             text: 'Upload',
//             handler: function() {
//                 if (form.getForm().isValid()) {
//                     form.getForm().submit({
//                         url: './ExtJS/emailtemplateeditor/file-upload_s3.php?sigid=' + sigid + '&bcid=' + bcid + '&pid=' + pid + '&act=signatureattachment',
//                         waitMsg: 'Uploading your file...',
//                         success: function(form, action) {
                              
             
                            
//                             Ext.Msg.show({
//                                 title : 'Confirm',
//                                 msg : 'Do you want to save this logo in the library?',
//                                 buttons : Ext.Msg.YESNO,
//                                 close: false,
//                                 fn : function(buttonValue, inputText, showConfig){
                                
//                                     if (buttonValue == 'yes'){
                                        
//                                         var imginsert   = '<img src=' + action.result.url + ' width="150" height="150">';
//                                         var currentbody = Ext.getCmp('sig').getValue();
//                                         var newbody     = currentbody + imginsert;
//                                         Ext.getCmp('sig').setValue(newbody);
//                                         Ext.Msg.alert('Success', 'Your file "' + action.result.file + '" is successfully uploaded');
//                                     }else{
//                                         var dec = encodeURI(action.result.localsrc);
//                                         Ext.getCmp('sig').setValue("<img src="+dec+" width=150 height= 150>");
//                                           removeattachmentssrc( sigid , action.result.file, action.result.s3prefix , action.result.file);
//                                     }
//                                 },
//                                 icon : Ext.MessageBox.QUESTION
//                             });
                            

                         
//                         },

//                         failure: function(form, action) {

//                             Ext.Msg.alert('Failure!', 'Error info: ' + action.result.error);
//                         }

//                     });
//                 }
//             }

//         }, {
//             text: 'Reset',
//             handler: function() {
//                 form.getForm().reset();
//             }
//         }, {
//             text: 'Cancel',
//             handler: function() {
//                 window.close();   

//             }

//         }]
//     });

//     window.show();

// }