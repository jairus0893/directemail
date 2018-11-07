// *********** HTML EDITOR **************
function editor(templateid, bcid, projectid) {

    Ext.onReady(function() {

        $.ajax({

            url: './ExtJS/emailtemplateeditor/htmleditor.php?tid=' + templateid + '&act=geteditormsg',
            type: 'POST',
            success: function(resp) {
                Ext.getCmp('bio').setValue(resp);

            }
        });
        var xy = document.getElementById("custom-f").value;

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
            width: 180,
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

        var tabs = new Ext.TabPanel({
            region: 'south',

            buttons: [{
                text: 'Update',
                handler: function() {
                    var notes = Ext.getCmp('bio').getValue();
                    Ext.Ajax.request({
                        type: 'post',
                        params: {
                            values: notes
                        },
                        url: './ExtJS/emailtemplateeditor/htmleditor.php?tid=' + templateid + '&act=updateeditor',


                        success: function(response) {

                            Ext.MessageBox.alert('Status', 'Successfully Updated');

                        }
                    });
                }
            }, {

                text: 'Upload Attachment',
                handler: function() {
                    uploadimg(templateid, bcid, projectid);
                }

            }, {

                text: 'Signature',
                handler: function() {
                    signature(templateid, bcid, projectid);
                }


            }]
        });

        Ext.QuickTips.init();

        var top = new Ext.FormPanel({
            title: 'Email Editor',
            renderTo: 'templateeditor',
            closable: true,
            width: 920,
            height: 400,
            //border : false,
            plain: true,
            layout: 'border',



            items: [{
                region: 'center',
                xtype: 'htmleditor',
                id: 'bio',
                fieldLabel: 'Editor',
                name: 'htmlcontent',
                height: 400,
                readOnly: false,
                plugins: Ext.ux.form.HtmlEditor.plugins()



            }, tabs, nav]


        });

    });

}

// *********** FILE UPLOAD **************

function uploadimg(templateid, bcid, projectid) {


    var form = new Ext.form.FormPanel({
        baseCls: 'x-plain',
        labelWidth: 55,
        defaultType: 'textfield',
        fileUpload: true,
        defaults: {
            anchor: '95%',
            allowBlank: false,
            msgTarget: 'side'
        },

        items: [{

            xtype: 'textfield',
            fieldLabel: 'Name',
            name: 'name-file'

        }, {
            xtype: 'fileuploadfield',
            id: 'form-file',
            emptyText: 'Please select file to upload',
            fieldLabel: 'File',
            name: 'photo-path',
            buttonCfg: {
                text: '',
                iconCls: 'x-upload-folder'
            }


        }]
    });

    var window = new Ext.Window({
        title: 'Upload Attachments',
        width: 500,
        width:350,
        height:140,
        layout: 'fit',
        plain: true,
        bodyStyle: 'padding:5px;',
        buttonAlign: 'center',
        items: form,


        buttons: [{
            text: 'Upload',
            handler: function() {
                if (form.getForm().isValid()) {
                    form.getForm().submit({
                        url: './ExtJS/emailtemplateeditor/file-upload_s3.php?tid=' + templateid + '&bcid=' + bcid + '&pid=' + projectid + '&act=emailattachment',
                        waitMsg: 'Uploading your file...',
                        success: function(form, action) {

                            Ext.Msg.alert('Success', 'Your file "' + action.result.file + '" is successfully uploaded');
                        },

                        failure: function(form, action) {

                            Ext.Msg.alert('Failure!', 'Error info: ' + action.result.error);
                        }

                    });
                }
            }

        }, {
            text: 'Cancel',
            handler: function() {
                form.getForm().reset();
            }
        }, {
            text: 'View Files',
            iconCls: 'x-edit-image',
            id: 'viewfiles',

            handler: function() {
                window.close();


                chooser = new ImageChooser({
                    url: './ExtJS/emailtemplateeditor/get-images.php?tid=' + templateid + '&bcid=' + bcid + '&pid=' + projectid + '&act=getemailfiles',
                    templateid: templateid,
                    bcid: bcid,
                    projectid: projectid,
                    width: 500,
                    height: 350
                });

                chooser.show();

            }

        }]
    });

    window.show();
}

// *********** SIGNATURE **************

function signature(templateid, bcid , projectid){
    var form3 = new Ext.form.FormPanel({
        baseCls: 'x-plain',
        labelWidth: 55,

    });
    
    var store= new Ext.data.JsonStore({

        url: './ExtJS/emailtemplateeditor/get-signature.php?bcid='+ bcid,
        autoLoad: true,
        fields: ['name', 'sigid']
    });
        

    var comboLocal =new Ext.form.ComboBox({
        fieldLabel:'Signature',
        name:'cmb-data',
        forceSelection:true,
        store:store,
        emptyText:'Select a Signature...',
        triggerAction: 'all',
        editable:false,
        displayField:'name',
        valueField:'sigid',
        id: 'combo-sig'
        
    });

     var window3 = new Ext.Window({

        bodyStyle:'padding: 10px',//adding padding to the components
        width:350,
        height:140,
        items: [comboLocal,form3], //adding the combo to the window
        layout:'form',
        title:'Signature',

        buttons: [{
            text: 'Create New Signature',
            handler: function() {
                emailsig(projectid,0);
                window3.close();
            }
            
        },{
            text: 'Save',
            handler: function() {
                var signa =  Ext.getCmp('combo-sig').getValue();

                
                Ext.Ajax.request({

                    type: 'post',
                    params: {
                        values: signa
                    },  
                    url: './ExtJS/emailtemplateeditor/htmleditor.php?tid='+ templateid +'&act=updatesignature',
                    
            
                    success: function(response){
                        
                        Ext.MessageBox.alert('Status', 'Successfully Updated');
                    }
                });
           }
        
        },{
            text: 'Cancel',

            handler: function(){
                window3.close();
            }
        }]
        
    });

    
    window3.show();
       
}


// //  DISPLAY NO IMAGE AVAILABLE FOR NO IMAGE FILE 

function handleMissingImg(ele) {
    ele.style.display='none';
}