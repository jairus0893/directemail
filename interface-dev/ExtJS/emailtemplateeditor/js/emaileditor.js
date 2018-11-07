
// *********** HTML EDITOR **************



function editor (templateid, bcid , projectid){
   

    Ext.onReady(function(){
 
        
                
        Ext.QuickTips.init();
 
        var top = new Ext.FormPanel({
        xtype: 'form',
        labelAlign: 'top',
        frame:true,
        title: 'Message',
        // bodyStyle:'padding:10px 10px 0',
        width: 800,
        layout:'fit',
        renderTo: 'templateeditor',
      
        
            
        items: [{
 
            xtype:'htmleditor', 
            id:'bio',
            fieldLabel:'Editor',
            name: 'htmlcontent',
            height:300,
            anchor:'100%',
             enableColors: true,
             enableAlignments: true,
             enableLists: true,
             enableSourceEdit: true,
             plugins         : Ext.ux.form.HtmlEditor.plugins()
                 
            
    
        }],
 
        
    
 
        buttons: [{
 
            text: 'Upload Attachment',
            handler: function() {
                 uploadimg(templateid, bcid , projectid); 
            }
 
            
        }]
        });

        
        var bodyem = document.getElementById("emailbody").value;
        Ext.getCmp('bio').setValue(bodyem);
        
       
       
 
    
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
        title: 'Upload Files',
        width: 500,
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
 
 
 
 
// *********** UPLOAD VIEW FILES **************

 
 function ViewFiles(templateid, bcid , projectid){
     
     
 
     var xd = Ext.data;
 
     var store = new Ext.data.JsonStore({
     url: './ExtJS/emailtemplateeditor/get-images.php?tid='+ templateid+'&bcid='+bcid+'&pid='+projectid,
     root: 'images',
     fields: ['name', 'url', {name:'size', type: 'float'}, {name:'lastmod', type:'date', dateFormat:'timestamp'}]
     });
     store.load();
     
     
     var prefix = bcid + '/attachments/' + projectid + '/' + templateid;
     var filename = '{shortName}';
     var name_id = '{name}';
 
 
     var tpl = new Ext.XTemplate(
         '<tpl for=".">',
             '<div class="thumb-wrap" id="#{name}">',
             
             '<div class="thumb"><a href="{url}" target="_blank"><img src="{url}" title="{name}"  onerror="handleMissingImg(this);" alt="No Image" draggable="true"></a></div>',
             '<span class="x-editable" style="font-weight: bold">{shortName}</span>',
             '<span style="margin-right: -50px"><a href="#" style="margin-right: 50px; color:#e8e8e8;">.</a></div></span>',
             
         '</tpl>',
         '<div class="x-clear"></div>'
     );
 
 
     var window2 = new Ext.Window({
         id:'images-view',
         frame:true,
         width:535,
         autoHeight:true,
         collapsible:false,
         layout:'fit',
         title:'Attachments (Drag Image to Editor)',
 
         items: new Ext.DataView({
             store: store,
             tpl: tpl,
             autoHeight:true,
             multiSelect: false,
             overClass:'x-view-over',
             itemSelector:'div.thumb-wrap',
             emptyText: '<span style="text-align: center ;font-size: 17px !important; margin: 35%">No Files to display</span>',
 
             plugins: [
                 // new Ext.DataView.DragSelector(),
                 // new Ext.DataView.LabelEditor({dataIndex: 'name'})
             ],
 
             prepareData: function(data){
                 data.shortName = Ext.util.Format.ellipsis(data.name, 15);
                 data.sizeString = Ext.util.Format.fileSize(data.size);
                 // data.dateString = data.lastmod.format("m/d/Y g:i a");
                 return data;
             },
             
             listeners: {
                 selectionchange: {
                     fn: function(dv,nodes){
                         var l = nodes.length;
                         var s = l != 1 ? 's' : '';
                         window2.setTitle('Simple DataView ('+l+' item'+s+' selected)');
                     }
                 }
                 
                 
             }
         })
         
     });
 
     window2.show();
     
    
     
 }
 

 function removeattachments3(templateid, attch, prefix, cts3) {

    Ext.MessageBox.show({
    title:'Delete Attachment?',
    msg: 'Are you sure you want to delete ' + attch,
    width : 350,
    closable : false,
    buttons: Ext.MessageBox.YESNO,
        fn : function(buttonValue, inputText, showConfig){
    
            if (buttonValue == 'yes'){
                Ext.Ajax.request({
                    url: './ExtJS/emailtemplateeditor/s3-attachments.php?act=removeattachments3&templateid='+templateid+'&attachment='+attch+'&prefix='+prefix,
                    success: function(){
                        var removeidattach= Ext.get("thumb#"+cts3);
                        var removeidattach2= Ext.get("thumb2#"+cts3);
                        
                        removeidattach.remove();
                        removeidattach2.remove();
                    //    cmess(data);
                    }
                });

            } //end btn
        },
        icon : Ext.MessageBox.WARNING
        
    });


}


// //  DISPLAY NO IMAGE AVAILABLE FOR NO IMAGE FILE 

function handleMissingImg(ele) {
    ele.style.display='none';
}
 
 
 

