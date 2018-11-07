// *********** SCRIPT EDITOR **************
function scriptseditor(scriptid, pid) {

    Ext.onReady(function() {

        Ext.override(Ext.form.HtmlEditor, {
            insertAtCursor: function(text) {
                if (!this.activated) {
                    return;
                }
                if (Ext.isIE) {
                    this.win.focus();
                    var r = this.doc.selection.createRange();
                    if (r) {
                        r.collapse(true);
                        r.pasteHTML(text);
                        this.syncValue();
                        this.deferFocus();
                    }
                } else if (Ext.isGecko || Ext.isOpera || Ext.isWebKit) {
                    this.win.focus();
                    this.execCmd('InsertHTML', text);
                    this.deferFocus();
                }
            }
        });

        $.ajax({

            url: './ExtJS/emailtemplateeditor/scripteditor.php?scriptid=' + scriptid + '&act=getscriptmsg',
            type: 'POST',
            success: function(resp) {
                Ext.getCmp('scrp').setValue(resp);

            }
        });

        var xy = document.getElementById("custom-scr").value;

        if (xy != 'null') {
            var x = JSON.parse(xy);

            function myFunction(x) {
                var out = "";
                var i;
                for (i = 0; i < x.length; i++) {


                    out += '<a href="#" draggable="true" ondragstart="dragstart(event)" ondragend="infofieldsdragdrop(event,\'' + x[i].name + '\',\'' + x[i].value + '\',\'' + pid + '\',\'' + scriptid + '\',\'maininfoplus\')" onclick="infofieldsdragdrop(event,\'' + x[i].name + '\',\'' + x[i].value + '\',\'' + pid + '\',\'' + scriptid + '\',\'maininfoplus\')"   class="default-fields">' + x[i].name + '</a>'
                }

                htmlnew = out;
            }

            myFunction(x);
        } else {

            htmlnew = "";

        }


        var scriptcust = document.getElementById("scriptcust").value;

        if (scriptcust != 'null') {
            var scf = JSON.parse(scriptcust);

            function myFunction(scf) {
                var out = "";
                var i;
                for (i = 0; i < scf.length; i++) {


                    out += '<a href="#" draggable="true" ondragstart="dragstart(event)" ondragend="dragpop(event,\'' + scf[i].name + '\',\'' + scf[i].value + '\',\'' + pid + '\',\'' + scriptid + '\' , \'customfields\')" onclick="dragpop(event,\'' + scf[i].name + '\',\'' + scf[i].value + '\',\'' + pid + '\',\'' + scriptid + '\',\'customfields\')"   class="default-fields">' + scf[i].name + '</a>'
                }

                scriptcustfields = out;
            }

            myFunction(scf);
        } else {

            scriptcustfields = "";

        }

        var sfield = document.getElementById("sfields-scr").value;

        if (sfield != 'null') {
            var sf = JSON.parse(sfield);

            function sfieldrop(sf) {
                var out = "";
                var i;
                for (i = 0; i < sf.length; i++) {

                    out += '<a href="#" draggable="true" ondragstart="dragstart(event)" onclick="dragsavef(\'' + scriptid + '\',\'' + sf[i].name + '\',\'' + pid + '\')"  ondragend="dragsavef(\'' + scriptid + '\',\'' + sf[i].name + '\',\'' + pid + '\')" class="default-fields">' + sf[i].name + '<br/>(dropdown)</a>'
                }

                saveddropdownfield = out;
            }

            sfieldrop(sf);
        } else {

            saveddropdownfield = "";

        }


        
        var scrpdatadd = document.getElementById("scrpdatadd").value;

        if (scrpdatadd != 'null') {
            var dd = JSON.parse(scrpdatadd);

            function sfieldrop(dd) {
                var out = "";
                var i;
                for (i = 0; i < dd.length; i++) {


                    out += '<a href="#" draggable="true" ondragstart="dragstart(event)" onclick="getscrpfield(\'' + scriptid + '\',\'' + dd[i].name + '\',\'' + pid + '\')"  ondragend="insertscrpfield(\'' + scriptid + '\',\'' + dd[i].name + '\',\'' + pid + '\')"  class="default-fields">' + dd[i].name + '</a>'
                }

                scrpdatacapturedd = out;
            }

            sfieldrop(dd);
        } else {

            scrpdatacapturedd  = "";

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
                html: '<a href="#" draggable="true" ondragstart="dragstart(event)" ondragend="infofieldsdragdrop(event,\'cname\',\'Name\',\'' + pid + '\',\'' + scriptid + '\' , \'maininfo\')" onclick="infofieldsdragdrop(event,\'cname\',\'Name\',\'' + pid + '\',\'' + scriptid + '\' , \'maininfo\')" class="default-fields" >Name</a>'
            }, {
                html: '<a href="#" draggable="true" ondragstart="dragstart(event)" ondragend="infofieldsdragdrop(event,\'cfname\',\'FirstName\',\'' + pid + '\',\'' + scriptid + '\' , \'maininfo\')" onclick="infofieldsdragdrop(event,\'cfname\',\'Firstname\',\'' + pid + '\',\'' + scriptid + '\' , \'maininfo\')" class="default-fields">Firstname</a>'
            }, {
                html: '<a href="#" draggable="true" ondragstart="dragstart(event)" ondragend="infofieldsdragdrop(event,\'clname\',\'Surname\',\'' + pid + '\',\'' + scriptid + '\' , \'maininfo\')"  onclick="infofieldsdragdrop(event,\'clname\',\'Surname\',\'' + pid + '\',\'' + scriptid + '\' , \'maininfo\')" class="default-fields"> Surname</a>',
            }, {
                html: '<a href="#" draggable="true" ondragstart="dragstart(event)" ondragend="infofieldsdragdrop(event,\'state\',\'State\',\'' + pid + '\',\'' + scriptid + '\' , \'maininfo\')" onclick="infofieldsdragdrop(event,\'state\',\'State\',\'' + pid + '\',\'' + scriptid + '\' , \'maininfo\')" class="default-fields">State</a>',
            }, {
                html: '<a href="#" draggable="true" ondragstart="dragstart(event)" ondragend="infofieldsdragdrop(event,\'address1\',\'Address\',\'' + pid + '\',\'' + scriptid + '\' , \'maininfo\')" onclick="infofieldsdragdrop(event,\'address1\',\'Address\',\'' + pid + '\',\'' + scriptid + '\' , \'maininfo\')" class="default-fields">Address</a>',
            }, {
                html: '<a href="#" draggable="true" ondragstart="dragstart(event)" ondragend="infofieldsdragdrop(event,\'phone\',\'Phone\',\'' + pid + '\',\'' + scriptid + '\' , \'maininfo\')" onclick="infofieldsdragdrop(event,\'phone\',\'Phone\',\'' + pid + '\',\'' + scriptid + '\' , \'maininfo\')" class="default-fields">Phone</a>',
            }, {
                html: '<div class="custom-fields">MAIN INFORMATION +</div>',
            }, {
                html: htmlnew,
            }, {
                html: '<div class="custom-fields">CUSTOM FIELDS</div>',
            }, {
                html: scriptcustfields,
        
            }, {
                html: '<div class="custom-fields">SCRIPT QUESTIONS</div>',
            }, {
                html: '<a href="#" draggable="true" ondragstart="dragstart(event)" ondragend="dragdatacapture(event,\'' + pid + '\',\'' + scriptid + '\')" onclick="dragdatacapture(event,\'' + pid + '\',\'' + scriptid + '\')" class="data-capture">Type Question</a>',    
          
            },{
                html: scrpdatacapturedd
            
            // }, {
            //     html: '<div class="custom-fields">SAVED FIELDS </div>',
            

            // }, {
            //     html: saveddropdownfield

            }]

        });

        var tabs = new Ext.TabPanel({
            region: 'south',

            buttons: [{
                text: 'Update',
                handler: function() {
                    var scrp = Ext.getCmp('scrp').getValue();

                    Ext.Ajax.request({
                        type: 'post',
                        params: {
                            scriptbody: scrp

                        },
                        url: './ExtJS/emailtemplateeditor/scripteditor.php?scriptid=' + scriptid + '&act=updatescriptmsg',

                        success: function(response) {

                            Ext.MessageBox.alert('Status', 'Successfully Updated');

                        }
                    });

                }
            }]
        });

        Ext.QuickTips.init();

        var top = new Ext.FormPanel({
            title: 'Script Editor',
            renderTo: 'screditor',
            closable: true,
            width: 920,
            height: 400,
            //border : false,
            plain: true,
            layout: 'border',

            items: [{
                region: 'center',
                xtype: 'htmleditor',
                id: 'scrp',
                fieldLabel: 'Editor',
                name: 'htmlcontent',
                height: 400,
                plugins: Ext.ux.form.HtmlEditor.plugins(),

            }, tabs, nav]


        });

    });

}


function infofieldsdragdrop(event, el, label, pid, scriptid,classes){

    var elname    = el + '|textbox';
    var color = '#0cc';
    var color2 = '#FFFFFF';
    htm = '<input type="text" name="' + elname  + '" id="' + el + '" class="' + classes + '" maxlength="20">';
    var html = '<div style= "cursor: pointer;" onmouseover="this.style.backgroundColor=\'' + color + '\'" onmouseout="this.style.backgroundColor=\'' + color2 + '\'"> <label>' + label + '</label>&nbsp;&nbsp' + htm + '</div> </div>';
    Ext.getCmp('scrp').focus();
    Ext.getCmp('scrp').insertAtCursor(html);
}
function dragpop(event, el, label, pid, scriptid,classes) {

    var dragpopform = new Ext.FormPanel({

        frame: true,

        items: [{

            items: {
                xtype: 'fieldset',
                title: 'Choose one(1) field you want to use',
                autoHeight: true,
                defaultType: 'radio',
                items: [{
                    checked: true,
                    fieldLabel: 'Fields',
                    boxLabel: 'Textbox',
                    name: 'sfields',
                    inputValue: 'textbox'
                }, {
                    fieldLabel: '',
                    labelSeparator: '',
                    boxLabel: 'Dropdown',
                    name: 'sfields',
                    inputValue: 'dropdown'
                }, {
                    fieldLabel: '',
                    labelSeparator: '',
                    boxLabel: 'Textarea',
                    name: 'sfields',
                    inputValue: 'textarea'
                }]
            }
        }]

    });

    var dragpopwin = new Ext.Window({

        bodyStyle: 'padding: 10px',
        width: 370,
        autoHeight: true,
        items: dragpopform,
        layout: 'form',
        title: 'Field Selection',

        buttons: [{
            text: 'Add',
            handler: function() {

                var sfields = dragpopform.getForm().getValues()['sfields'];
                renderfield(el, label, sfields, pid, scriptid,classes)
                dragpopwin.close();
            }
        }, {
            text: 'Cancel',

            handler: function() {
                dragpopwin.close();
            }

        }]

    });
    dragpopwin.show();
}

function renderfield(el, label, fields, pid, scriptid,classes) {

    switch (fields) {
        case 'textbox':
            creatextboxselection(el, label, fields, pid, scriptid,classes);
            break;
        case 'dropdown':

            createdropdown(el, label, fields, pid, scriptid,classes);
            break;
        case 'textarea':
            var elname    = el + '|textarea';
            var htm = ' <textarea name="' + elname + '" id="' + el + '" class="' + classes + '" maxlength="50"></textarea>';
            var color = '#0cc';
            var color2 = '#FFFFFF';
            var html = '<div style= "cursor: pointer;" onmouseover="this.style.backgroundColor=\'' + color + '\'" onmouseout="this.style.backgroundColor=\'' + color2 + '\'"> <label>' + label + '</label>&nbsp;&nbsp' + htm + '</div> </div>';
            Ext.getCmp('scrp').focus();
            Ext.getCmp('scrp').insertAtCursor(html);
            break;
    }

}

function creatextboxselection(el, label, fields, pid, scriptid,classes) {

    var textboxselectionform = new Ext.FormPanel({

        frame: true,

        items: [{

            items: {
                xtype: 'fieldset',
                title: 'Choose type of textbox you want to use*',
                autoHeight: true,
                defaultType: 'radio',
                items: [{
                    checked: true,
                    fieldLabel: 'Type of Textbox',
                    boxLabel: 'Text',
                    name: 'txtfields',
                    inputValue: 'text'
                }, {
                    fieldLabel: '',
                    labelSeparator: '',
                    boxLabel: 'Numbers',
                    name: 'txtfields',
                    inputValue: 'numbers'
                }, {
                    fieldLabel: '',
                    labelSeparator: '',
                    boxLabel: 'Datetime',
                    name: 'txtfields',
                    inputValue: 'datetime'

                }]
            }
        }]

    });

    var textboxselectionwin = new Ext.Window({

        bodyStyle: 'padding: 10px',
        width: 370,
        autoHeight: true,
        items: textboxselectionform,
        layout: 'form',
        title: 'Textbox Selection',
        listeners: {
            initialize: function(scrp) {
                Ext.getCmp('scrp').focus(false, 300);
            }
        },
        buttons: [{
            text: 'Add',
            handler: function() {

                var txtfields = textboxselectionform.getForm().getValues()['txtfields'];
                textboxval(el, label, pid, scriptid, txtfields,classes)

            }

        }, {
            text: 'Cancel',

            handler: function() {
                textboxselectionwin.close();
            }
        }]
    });

    textboxselectionwin.show();
}

function textboxval(el, label, pid, scriptid, txtfields,classes) {

    var elname    = el + '|textbox';
    switch (txtfields) {
        case 'text':

            htm = '<input type="text" name="' + elname  + '" id="' + el + '" class="' + classes + '" >';
            break;
        case 'numbers':

            htm = '<input type="number" name="' + elname  + '" id="' + el + '" class="' + classes + '">';
            break;
        case 'datetime':

            htm = '<input type="date" name="' + elname  + '" id="' + el + '" class="' + classes + '"><input type="time" name="' + elname  + '" id="' + el + '" class="' + classes + '">';
            break;

            return htm;
    }

    var color = '#0cc';
    var color2 = '#FFFFFF';
    var html = '<div style= "cursor: pointer;" onmouseover="this.style.backgroundColor=\'' + color + '\'" onmouseout="this.style.backgroundColor=\'' + color2 + '\'"> <label>' + label + '</label>&nbsp;&nbsp' + htm + '</div> </div>';
    Ext.getCmp('scrp').focus();
    Ext.getCmp('scrp').insertAtCursor(html);

}

function createdropdown(el, label, fields, pid, scriptid,classes) {

    var dropform = new Ext.form.FormPanel({
        frame: true,

        items: [{
            xtype: 'fieldset',
            title: 'Dropdown Label*',
            autoHeight: true,
            defaults: {
                width: 210
            },
            defaultType: 'textfield',
            items: [{
                    fieldLabel: 'Custom Field',
                    name: 'dropname',
                    id: 'dropname',
                    disabled: true,
                    value: el
                }, {
                    xtype:'hidden',
                    fieldLabel: 'Label',
                    name: 'droplabel',
                    id: 'droplabel',
                   
                    value: label
                }

            ]
        }]

    });

    var dropform2 = new Ext.form.FormPanel({
        frame: true,

        items: [{
            xtype: 'fieldset',
            title: 'Add Options*',
            autoHeight: true,
            defaults: {
                width: 400
            },


            items: [{

            }, {
                html: '<div id="opdiv1"><div class="x-form-item " tabindex="-1"><label for="droplabel">Option 1:</label><input type="text"  name="option1" id="option1" class="seloptions x-form-text x-form-field"  style="width: 202px;"></div></div>',
            }, {
                html: '<div id="otheroptions"></div>',

            }, {
                html: '<a href="#" id="addoptionimage" onclick="addoptions(1)" class="default-fields" style="width: 100px;margin: 0 auto; margin-top: 10px;">Add Options <img src="icons/add.gif"></a>',


            }]
        }]

    });

    var dropwin = new Ext.Window({

        bodyStyle: 'padding: 10px',
        width: 460,
        autoHeight: true,
        items: [dropform, dropform2],
        layout: 'form',
        title: 'Dropdown Field',

        buttons: [{
            text: 'New Custom Field',
            handler: function() {
                dropwin.close();
                newcustomfield(pid)
            },
        }, {
            text: 'Add',
            handler: function() {

                var dropname = Ext.getCmp('dropname').getValue();
                var dname    = dropname + '|dropdown';
                var htm = '<select name="' + dname + '" id="' + dropname + '" class="' + classes + '"><option></option>';
                jQuery(".seloptions").each(
                    function() {
                        htm += '<option value="' + $(this).val() + '">' + $(this).val() + '</option>';
                    }
                );
                htm += '</select>';

                var droplabel = Ext.getCmp('droplabel').getValue();
                var color = '#0cc';
                var color2 = '#FFFFFF';
                var htmldrop = '<div style= "cursor: pointer;" onmouseover="this.style.backgroundColor=\'' + color + '\'" onmouseout="this.style.backgroundColor=\'' + color2 + '\'"   > <label>' + droplabel + '</label>&nbsp;&nbsp' + htm + '</div> </div>';
                Ext.getCmp('scrp').focus();
                Ext.getCmp('scrp').insertAtCursor(htmldrop);
            },
        }, {
            text: 'Save',

            handler: function() {

                var htm = [];

                jQuery(".seloptions").each(
                    function() {

                        htm.push($(this).val());

                    }
                );

                htm = htm.toString();

                if (dropform.getForm().isValid()) {
                    var dropname = Ext.getCmp('dropname').getValue();
                    var droplabel = Ext.getCmp('droplabel').getValue();
                    Ext.Ajax.request({
                        type: 'post',
                        params: {
                            slabel: droplabel,
                            sname: dropname,
                            htmlbody: htm
                        },
                        url: './ExtJS/emailtemplateeditor/scripteditor.php?scriptid=' + scriptid + '&act=savefield',

                        success: function(response) {
                            if (response.responseText == 'invalid') {

                                Ext.MessageBox.show({
                                    title: 'Invalid',
                                    msg: 'Name already exist<br>Please enter a different name',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.ERROR
                                });

                            } else {
                                Ext.MessageBox.show({
                                    title: 'Information',
                                    msg: 'Sucessfully Saved',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.INFO
                                });
                                manage_persist(pid);
                            }
                            // alert(response.responseText);

                        }
                    });
                }
            }
        }]

    });

    dropwin.show();
}

// CUSTOM FIELD
function newcustomfield(pid) {

    var newcust = new Ext.form.FormPanel({
        frame: true,

        items: [{
            xtype: 'fieldset',
            title: 'New Custom Field',
            autoHeight: true,
            defaults: {
                width: 210,
                anchor: '95%',
                allowBlank: false,
                msgTarget: 'side'
            },
            defaultType: 'textfield',
            items: [{
                fieldLabel: 'Name',
                name: 'custname',
                id: 'custname',

            }, {

                xtype: 'label',
                text: '(Internal Name)',
                style: ' font-style: italic; font-size: 10px;',

            }, {
                fieldLabel: 'Label',
                name: 'custlabel',
                id: 'custlabel',

            }, {

                xtype: 'label',
                text: '(This will appear on agent interface)',
                style: ' font-style: italic; font-size: 10px;',
            }]
        }]

    });

    var newcustwin = new Ext.Window({

        bodyStyle: 'padding: 10px',
        width: 460,
        autoHeight: true,
        items: newcust,
        layout: 'form',
        title: 'Custom Field',

        buttons: [{
            text: 'Add',
            handler: function() {

                if (newcust.getForm().isValid()) {
                    var customname = Ext.getCmp('custname').getValue();
                    var customlabel = Ext.getCmp('custlabel').getValue()

                    Ext.Ajax.request({
                        type: 'post',
                        params: {
                            cname: customname,
                            clabel: customlabel
                        },
                        url: './ExtJS/emailtemplateeditor/scripteditor.php?projectid=' + pid + '&act=addcustomfield',

                        success: function(response) {
                            Ext.MessageBox.alert('Status', 'Successfully Added');
                            manage_persist(pid);
                            Ext.getCmp('custname').setValue('');
                            Ext.getCmp('custlabel').setValue('');
                            // alert(response.responseText);


                        }
                    });

                }
            }


        }, {
            text: 'Cancel',
            handler: function() {
                newcustwin.close();
            }
        }]
    });
    newcustwin.show();
}

// RETRIEVE SAVE FIELDS
function dragsavef(scriptid, val, pid) {

    var param = {
        fieldval: val
    };

    $.ajax({

        url: './ExtJS/emailtemplateeditor/scripteditor.php?scriptid=' + scriptid + '&act=getsavefieldval',
        type: 'POST',
        data: param,
        success: function(response) {
            respsavescriptfield(response, val, scriptid, pid);

        }
    });
}

function respsavescriptfield(response, val, scriptid, pid) {

    var newasd = response;
    var x = newasd.split(',');

    var savef = "";
    var input = "";
    var i;
    for (i = 0; i < x.length; i++) {

        ino = i + 1;
        savef += '<div id="opdiv' + ino + '"><div class="x-form-item " tabindex="-1"><label for="droplabel">Option ' + ino + ':</label><input type="text"  name="optios" id="optios" class="seloptions x-form-text x-form-field" style="width: 202px;" value=' + x[i] + ' ><img src="icons/delete.gif" onclick="removeoption(\'opdiv' + ino + '\')"/></div></div>'

    }

    ino2 = ino;
    html = savef;


    var dropsaveform = new Ext.form.FormPanel({
        frame: true,

        items: [{
            xtype: 'fieldset',
            title: 'Dropdown Label*',
            autoHeight: true,
            defaults: {
                width: 210
            },
            defaultType: 'textfield',
            items: [{
                fieldLabel: 'Custom Field',
                name: 'savelabel',
                id: 'savelabel',
                disabled: true,
                value: val

            }]
        }]

    });

    var dropsaveform2 = new Ext.form.FormPanel({
        frame: true,

        items: [{
            xtype: 'fieldset',
            title: 'Add Options*',
            autoHeight: true,
            defaults: {
                width: 400
            },


            items: [{

            }, {
                html: html,
            }, {
                html: '<div id="otheroptions"></div>',

            }, {
                html: '<a href="#" id="addoptionimage" onclick="addoptions(\'' + ino + '\')" class="default-fields" style="width: 100px;margin: 0 auto; margin-top: 10px;">Add Options <img src="icons/add.gif"></a>',

            }]
        }]

    });

    var dropsavewin = new Ext.Window({

        bodyStyle: 'padding: 10px',
        width: 460,
        autoHeight: true,
        items: [dropsaveform, dropsaveform2],
        layout: 'form',
        title: 'Dropdown Field',

        buttons: [{
            text: 'Insert',
            handler: function() {

                
                var htm = '<select class="fi" name="' + name + '"><option></option>';
                jQuery(".seloptions").each(
                    function() {
                        htm += '<option value="' + $(this).val() + '">' + $(this).val() + '</option>';
                    }
                );
                htm += '</select>';

                var savelabel = Ext.getCmp('savelabel').getValue();
                getlabelnew = getlabelupdated(savelabel,pid);
                var color = '#0cc';
                var color2 = '#FFFFFF';
                var htmldrop = '<div style= "cursor: pointer;" onmouseover="this.style.backgroundColor=\'' + color + '\'" onmouseout="this.style.backgroundColor=\'' + color2 + '\'"   > <label>' + getlabelnew + '</label>&nbsp;&nbsp' + htm + '</div> </div>';


                Ext.getCmp('scrp').focus();
                Ext.getCmp('scrp').insertAtCursor(htmldrop);

            }

        }, {
            text: 'Save',

            handler: function() {
                var savelabel = Ext.getCmp('savelabel').getValue();
                var htm = [];
                jQuery(".seloptions").each(
                    function() {

                        htm.push($(this).val());

                    }
                );

                htm = htm.toString();

                Ext.Ajax.request({
                    type: 'post',
                    params: {
                        slabel: savelabel,
                        htmlbody: htm
                    },
                    url: './ExtJS/emailtemplateeditor/scripteditor.php?scriptid=' + scriptid + '&act=updatefield',

                    success: function(response) {
                        Ext.MessageBox.show({
                            title: 'Information',
                            msg: 'Sucessfully Updated',
                            width: 250,
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.INFO
                        });
                        manage_persist(pid);

                    }
                });
            }

        }, {
            text: 'Delete',

            handler: function() {
                var savelabel = Ext.getCmp('savelabel').getValue();

                Ext.MessageBox.show({
                    title: 'Delete Saved Fields',
                    msg: 'Are you sure you want to delete this field?',
                    width: 350,
                    closable: false,
                    buttons: Ext.MessageBox.YESNO,
                    fn: function(buttonValue, inputText, showConfig) {
                        if (buttonValue == 'yes') {

                            Ext.Ajax.request({
                                type: 'post',
                                params: {
                                    savelabel: savelabel
                                },
                                url: './ExtJS/emailtemplateeditor/scripteditor.php?scriptid=' + scriptid + '&act=removesavefieldval',

                                success: function(response) {

                                    Ext.MessageBox.show({
                                        title: 'Information',
                                        msg: 'Sucessfully Deleted',
                                        width: 250,
                                        buttons: Ext.MessageBox.OK,
                                        icon: Ext.MessageBox.INFO
                                    });
                                    manage_persist(pid);
                                    dropsavewin.close();
                                }
                            });

                        } //end btn
                    },
                    icon: Ext.MessageBox.WARNING

                });
            }

        }]


    });

    dropsavewin.show();

}


// SCRIPT DATA CAPTURE
function dragdatacapture(event, pid, scriptid) {


    var dragpopform = new Ext.FormPanel({

        frame: true,

        items: [{

            items: {
                xtype: 'fieldset',
                title: 'Choose one(1) field you want to use',
                autoHeight: true,
                defaultType: 'radio',
                items: [{
                    checked: true,
                    fieldLabel: 'Fields',
                    boxLabel: 'Textbox',
                    name: 'sfields',
                    inputValue: 'textbox'
                }, {
                    fieldLabel: '',
                    labelSeparator: '',
                    boxLabel: 'Dropdown',
                    name: 'sfields',
                    inputValue: 'dropdown'
                }]
            }
        }]

    });

    var dragpopwin = new Ext.Window({

        bodyStyle: 'padding: 10px',
        width: 370,
        autoHeight: true,
        items: dragpopform,
        layout: 'form',
        title: 'Field Selection',

        buttons: [{
            text: 'Add',
            handler: function() {

                var sfields = dragpopform.getForm().getValues()['sfields'];
                renderdatacapturefield(event, sfields, pid, scriptid)
                dragpopwin.close();
            }
        }, {
            text: 'Cancel',

            handler: function() {
                dragpopwin.close();
            }

        }]

    });
    dragpopwin.show();
}


function renderdatacapturefield(el, fields, pid, scriptid) {

    switch (fields) {
        case 'textbox':
            newdatacapturetextbox(el, pid, scriptid , fields) 
            break;
        case 'dropdown':
            createdatacapturedropdown(el, pid, scriptid , fields);
            break;
        
    }

}
function newdatacapturetextbox(el, pid, scriptid ,fields) {

    var valreg = /[A-Za-z0-9]/;
        Ext.apply(Ext.form.VTypes, {
            //  vtype validation function
            radius: function(val, field) {
                return valreg.test(val);
            },
        radiusText: 'Special Characters are not Allowed'
    });


    var newcust = new Ext.form.FormPanel({
        frame: true,

        items: [{
            xtype: 'fieldset',
            title: 'Textbox',
            autoHeight: true,
            defaults: {
                width: 210,
                anchor: '95%',
                allowBlank: false,
                msgTarget: 'side'
            },
            defaultType: 'textfield',
            items: [{
                fieldLabel: 'Script Question',
                name: 'question',
                id: 'question',

            }, {
                fieldLabel: 'Export Fieldname',
                maskRe: /[A-Za-z0-9]/,
                maxLength : 20, 
                enforceMaxLength : 20,
                name: 'fieldname',
                id: 'fieldname',
                vtype : 'radius'  
               
               

            }]
        }]

    });

    var newcustwin = new Ext.Window({

        bodyStyle: 'padding: 10px',
        width: 460,
        autoHeight: true,
        items: newcust,
        layout: 'form',
        title: 'Textbox - Script Data Capture',

        buttons: [{
            text: 'Add',
            handler: function() {

                if (newcust.getForm().isValid()) {
                    var question = Ext.getCmp('question').getValue();
                    var fieldname = Ext.getCmp('fieldname').getValue()

                    Ext.Ajax.request({
                        type: 'post',
                        params: {
                            fields : fields,
                            question: question,
                            fieldname: fieldname
                        },
                        url: './ExtJS/emailtemplateeditor/scripteditor.php?projectid=' + pid + '&scriptid='+ scriptid + '&act=addscriptdata',

                        success: function(response) {
                            Ext.MessageBox.alert('Status', 'Successfully Added');
                            manage_persist(pid);
                            newcustwin.close();
                           

                        }
                    });

                }
            }


        }, {
            text: 'Cancel',
            handler: function() {
                newcustwin.close();
            }
        }]
    });
    newcustwin.show();
}




function createdatacapturedropdown(el, pid, scriptid , fields){

    var valreg = /[A-Za-z0-9]/;
    Ext.apply(Ext.form.VTypes, {
        //  vtype validation function
        radius: function(val, field) {
            return valreg.test(val);
        },
        radiusText: 'Special Characters are not Allowed'
    });
    
    var dropform = new Ext.form.FormPanel({
        frame: true,

        items: [{
            xtype: 'fieldset',
            title: 'Dropdown',
            autoHeight: true,
            defaults: {
                width: 210,
                msgTarget: 'side'
            },
            defaultType: 'textfield',
            items: [{
                    fieldLabel: 'Script Question',
                    name: 'questiondr',
                    id: 'questiondr',

                },{
                    fieldLabel: 'Export Fieldname',
                    maskRe: /[A-Za-z0-9]/,
                    maxLength : 20, 
                    enforceMaxLength : 20,
                    name: 'fieldnamedr',
                    id: 'fieldnamedr',
                    vtype : 'radius',

                }
            ]
        }]

    });

    var dropform2 = new Ext.form.FormPanel({
        frame: true,

        items: [{
            xtype: 'fieldset',
            title: 'Add Options*',
            autoHeight: true,
            defaults: {
                width: 400
            },


            items: [{

            }, {
                html: '<div id="opdiv1"><div class="x-form-item " tabindex="-1"><label for="droplabel">Option 1:</label><input type="text"  name="option1" id="option1" class="seloptions x-form-text x-form-field"  style="width: 202px;"></div></div>',
            }, {
                html: '<div id="otheroptions"></div>',

            }, {
                html: '<a href="#" id="addoptionimage" onclick="addoptions(1)" class="default-fields" style="width: 100px;margin: 0 auto; margin-top: 10px;">Add Options <img src="icons/add.gif"></a>',


            }]
        }]

    });

    var dropwin = new Ext.Window({

        bodyStyle: 'padding: 10px',
        width: 460,
        autoHeight: true,
        items: [dropform, dropform2],
        layout: 'form',
        title: 'Dropdown - Script Data Capture',

        buttons: [{
            text: 'Add',

            handler: function() {

                var htm = [];

                jQuery(".seloptions").each(
                    function() {

                        htm.push($(this).val());

                    }
                );

                htm = htm.toString();

                if (dropform.getForm().isValid()) {
                    var question = Ext.getCmp('questiondr').getValue();
                    var fieldname = Ext.getCmp('fieldnamedr').getValue()

                    Ext.Ajax.request({
                        type: 'post',
                        params: {
                            fields: fields,
                            question: question,
                            fieldname: fieldname,
                            htmlbody: htm
                        },
                        url: './ExtJS/emailtemplateeditor/scripteditor.php?projectid=' + pid + '&scriptid='+ scriptid + '&act=addscriptdata',

                        success: function(response) {
                            Ext.MessageBox.alert('Status', 'Successfully Added');
                            manage_persist(pid);
                            dropwin.close();

                        }
                    });
                }
            }
        }]

    });

    dropwin.show();


}

function insertscrpfield (sid,val,pid){

    var param = {
        fieldval: val
    };

    $.ajax({

        url: './ExtJS/emailtemplateeditor/scripteditor.php?scriptid=' + sid + '&act=getsscriptdatafield',
        type: 'POST',
        data: param,
        success: function(response) {
            if (response == 'dropdown'){
                dragsavescrpdropdown(sid, val, pid);
           }else{
            
                
           }

        }
    });


}
// INSERT DATA CAPTURE TO EDITOR 
function insertscrpfield (sid,val,pid){

    var param = {
        fieldval: val
    };

    $.ajax({

        url: './ExtJS/emailtemplateeditor/scripteditor.php?scriptid=' + sid + '&act=getsscriptdatafield',
        type: 'POST',
        data: param,
        success: function(resp) {

            $.ajax({

                url: './ExtJS/emailtemplateeditor/scripteditor.php?projectid=' + pid + '&act=getsavedatacapturedval',
                type: 'POST',
                data: param,
                success: function(response) {
                    if (resp == 'dropdown'){             
                        
                        insertdropdown(sid,val,pid,response);
                    }else{
                        var color = '#0cc';
                        var color2 = '#FFFFFF';
                        valname = val + '|textbox';
                        htm = '<input type="text" name="' + valname + '" id="' + val + '" class="scriptdatacapture" maxlength="20">';
                        var html = '<div style= "cursor: pointer;" onmouseover="this.style.backgroundColor=\'' + color + '\'" onmouseout="this.style.backgroundColor=\'' + color2 + '\'"><label>' + response + '</label>&nbsp;&nbsp' + htm + '</div> </div>';
                        Ext.getCmp('scrp').focus();
                        Ext.getCmp('scrp').insertAtCursor(html);
                    }
                }
            });
        }
    });
}

function insertdropdown(sid,val,pid,question){
    var param = {
        fieldval: val
    };

    $.ajax({

        url: './ExtJS/emailtemplateeditor/scripteditor.php?scriptid=' + sid + '&act=getsscriptdatafieldvalue',
        type: 'POST',
        data: param,
        success: function(response) {
            inserdatacapturedd(val,question,response);

        }
    });
}

function inserdatacapturedd(val,question,response){
    
    var newasd = response;
    var x = newasd.split(',');

    var savef = "";
    var input = "";
    var i;
    for (i = 0; i < x.length; i++) {
        savef += '<option value="' + x[i] + '">' + x[i] + '</option>';
    }

    valname = val + '|dropdown';
    var htm = '<select class="scriptdatacapture" name="' + valname + '" id="' + val + '" ><option></option>';
    htm +=  savef;
    htm += '</select>';

    var color = '#0cc';
    var color2 = '#FFFFFF';
    var htmldrop = '<div style= "cursor: pointer;" onmouseover="this.style.backgroundColor=\'' + color + '\'" onmouseout="this.style.backgroundColor=\'' + color2 + '\'"   > <label>' + question + '</label>&nbsp;&nbsp' + htm + '</div> </div>';
    Ext.getCmp('scrp').focus();
    Ext.getCmp('scrp').insertAtCursor(htmldrop);
}
// END


// 
function getscrpfield (sid,val,pid){

    var param = {
        fieldval: val
    };

    $.ajax({

        url: './ExtJS/emailtemplateeditor/scripteditor.php?scriptid=' + sid + '&act=getsscriptdatafield',
        type: 'POST',
        data: param,
        success: function(response) {
            if (response == 'dropdown'){

                $.ajax({

                    url: './ExtJS/emailtemplateeditor/scripteditor.php?projectid=' + pid + '&act=getsavedatacapturedval',
                    type: 'POST',
                    data: param,
                    success: function(response) {
                       window.ques = response;
                    }
            
                });
            
                dragsavescrpdropdown(sid, val, pid);
           }else{
                dragsavescrp(sid, val, pid);
           }

        }
    });


}





// RETRIEVE SAVE SCRIPT CAPTURE
function dragsavescrp(scriptid, val, pid) {

    var param = {
        fieldval: val
    };

    $.ajax({

        url: './ExtJS/emailtemplateeditor/scripteditor.php?projectid=' + pid + '&act=getsavedatacapturedval',
        type: 'POST',
        data: param,
        success: function(response) {
            savedscriptcapture(response, val, scriptid, pid);

        }
    });
}

function savedscriptcapture(response, val, scriptid, pid){

    var newcust = new Ext.form.FormPanel({
        frame: true,

        items: [{
            xtype: 'fieldset',
            title: 'Textbox',
            autoHeight: true,
            defaults: {
                width: 210,
                anchor: '95%',
                allowBlank: false,
                msgTarget: 'side'
            },
            defaultType: 'textfield',
            items: [{
                fieldLabel: 'Script Question',
                name: 'question',
                id: 'question',
                value: response
               

            }, {
                fieldLabel: 'Export Fieldname',
                name: 'fieldname',
                id: 'fieldname',
                disabled: true,
                value: val

            }]
        }]

    });



    var newcustwin = new Ext.Window({

        bodyStyle: 'padding: 10px',
        width: 460,
        autoHeight: true,
        items: newcust,
        layout: 'form',
        modal: true,
        title: 'Textbox - Script Data Capture',

        buttons: [{
            text: 'Update',
            handler: function() {

                if (newcust.getForm().isValid()) {
                    
                                    
                    var question = Ext.getCmp('question').getValue();
                    var fieldname = Ext.getCmp('fieldname').getValue()

                    Ext.Ajax.request({
                        type: 'post',
                        params: {
                            fields:   'textbox',
                            question: question,
                            fieldname: fieldname
                        },
                        url: './ExtJS/emailtemplateeditor/scripteditor.php?scriptid=' + scriptid + '&projectid=' + pid + '&act=updatescriptdata',

                        success: function(response) {
                            Ext.MessageBox.alert('Status', 'Successfully Updated');
                            savingeditor(scriptid);
                            manage_persist(pid);
                            newcustwin.close();


                        }
                    });

                }
            }


        }, {
            text: 'Delete',
            handler: function() {
                var fieldname = Ext.getCmp('fieldname').getValue()

                Ext.MessageBox.show({
                    title: 'Delete Script Data',
                    msg: 'Are you sure you want to delete fieldname <strong>' + fieldname + '</strong>?' ,
                    width: 350,
                    closable: false,
                    buttons: Ext.MessageBox.YESNO,
                    fn: function(buttonValue, inputText, showConfig) {
                        if (buttonValue == 'yes') {

                            Ext.Ajax.request({
                                type: 'post',
                                params: {
                                    fields: 'dropdown',
                                    fieldname: fieldname
                                },
                                url: './ExtJS/emailtemplateeditor/scripteditor.php?scriptid=' + scriptid + '&projectid=' + pid + '&act=removescriptdata',

                                success: function(response) {

                                    Ext.MessageBox.show({
                                        title: 'Information',
                                        msg: 'Sucessfully Deleted',
                                        width: 250,
                                        buttons: Ext.MessageBox.OK,
                                        icon: Ext.MessageBox.INFO
                                    });
                                    manage_persist(pid);
                                    newcustwin.close();
                                }
                            });

                        } //end btn
                    },
                    icon: Ext.MessageBox.WARNING

                });
            }
        }]
    });
    newcustwin.show();

    

}



// RETRIEVE SCRIPT DATA DROPDOWN
function dragsavescrpdropdown(scriptid, val, pid) {

    var param = {
        fieldval: val
    };

    $.ajax({

        url: './ExtJS/emailtemplateeditor/scripteditor.php?scriptid=' + scriptid + '&act=getsscriptdatafieldvalue',
        type: 'POST',
        data: param,
        success: function(response) {
            savescriptfielddropdown(response, val, scriptid, pid);

        }
    });
}

function savescriptfielddropdown(response, val, scriptid, pid) {

    var newasd = response;
    var x = newasd.split(',');

    var savef = "";
    var input = "";
    var i;
    for (i = 0; i < x.length; i++) {

        ino = i + 1;
        savef += '<div id="opdiv' + ino + '"><div class="x-form-item " tabindex="-1"><label for="droplabel">Option ' + ino + ':</label><input type="text"  name="optios" id="optios" class="seloptions x-form-text x-form-field" style="width: 202px;" value=' + x[i] + ' ><img src="icons/delete.gif" onclick="removeoption(\'opdiv' + ino + '\')"/></div></div>'

    }

    ino2 = ino;
    html = savef;

    var dropsaveform = new Ext.form.FormPanel({
        frame: true,

        items: [{
            xtype: 'fieldset',
            title: 'Dropdown',
            autoHeight: true,
            defaults: {
                width: 210
            },
            defaultType: 'textfield',
            items: [{
                fieldLabel: 'Script Question',
                name: 'question',
                id: 'question',
                value: window.ques
                
            },{
                    fieldLabel: 'Export Fieldname',
                    name: 'fldname',
                    id: 'fldname',
                    disabled: true,
                    value: val
    
                

            }]
        }]

    });
    

    var dropsaveform2 = new Ext.form.FormPanel({
        frame: true,

        items: [{
            xtype: 'fieldset',
            title: 'Add Options*',
            autoHeight: true,
            defaults: {
                width: 400
            },


            items: [{

            }, {
                html: html,
            }, {
                html: '<div id="otheroptions"></div>',

            }, {
                html: '<a href="#" id="addoptionimage" onclick="addoptions(\'' + ino + '\')" class="default-fields" style="width: 100px;margin: 0 auto; margin-top: 10px;">Add Options <img src="icons/add.gif"></a>',

            }]
        }]

    });





    var dropsavewin = new Ext.Window({

        bodyStyle: 'padding: 10px',
        width: 460,
        autoHeight: true,
        items: [dropsaveform, dropsaveform2],
        layout: 'form',
        modal: true,
        title: 'Dropdown Field',


        buttons: [ {
            text: 'Update',

            handler: function() {
            
                var question = Ext.getCmp('question').getValue();
                var fieldname = Ext.getCmp('fldname').getValue()
                var htm = [];
                jQuery(".seloptions").each(
                    function() {

                        htm.push($(this).val());

                    }
                );

                htm = htm.toString();

                Ext.Ajax.request({
                    type: 'post',
                    params: {
                        fields:   'dropdown',
                        question: question,
                        fieldname: fieldname,
                        htmlbody: htm
                    },
                    url: './ExtJS/emailtemplateeditor/scripteditor.php?scriptid=' + scriptid + '&projectid=' + pid + '&act=updatescriptdata',

                    success: function(response) {
                        Ext.MessageBox.show({
                            title: 'Information',
                            msg: 'Sucessfully Updated',
                            width: 250,
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.INFO
                        });
                        savingeditor(scriptid);
                        manage_persist(pid);
                        dropsavewin.close();

                    }
                });

                

            }

        }, {
            text: 'Delete',

            handler: function() {
                var fieldname = Ext.getCmp('fldname').getValue()

                Ext.MessageBox.show({
                    title: 'Delete Script Data',
                    msg: 'Are you sure you want to delete fieldname <strong>' + fieldname + '</strong>?' ,
                    width: 350,
                    closable: false,
                    buttons: Ext.MessageBox.YESNO,
                    fn: function(buttonValue, inputText, showConfig) {
                        if (buttonValue == 'yes') {

                            Ext.Ajax.request({
                                type: 'post',
                                params: {
                                    fields: 'dropdown',
                                    fieldname: fieldname
                                },
                                url: './ExtJS/emailtemplateeditor/scripteditor.php?scriptid=' + scriptid + '&projectid=' + pid + '&act=removescriptdata',

                                success: function(response) {

                                    Ext.MessageBox.show({
                                        title: 'Information',
                                        msg: 'Sucessfully Deleted',
                                        width: 250,
                                        buttons: Ext.MessageBox.OK,
                                        icon: Ext.MessageBox.INFO
                                    });
                                    manage_persist(pid);
                                    dropsavewin.close();
                                }
                            });

                        } //end btn
                    },
                    icon: Ext.MessageBox.WARNING

                });
            }

        }]


    });

    dropsavewin.show();

}

function savingeditor(scriptid){

    var scrp = Ext.getCmp('scrp').getValue();
    Ext.Ajax.request({
        type: 'post',
        params: {
            scriptbody: scrp

        },
        url: './ExtJS/emailtemplateeditor/scripteditor.php?scriptid=' + scriptid + '&act=updatescriptmsg',
        success: function(response) {}
    });
}

// END SCRIPT DATA CAPTURE





function addoptions(ct) {

    ct++;
    $("#addoptionimage").remove();
    var options = '<div id="opdiv' + ct + '"><div class="x-form-item " tabindex="-1"><label for="droplabel">Option ' + ct + ': </label><input type="text"   name="option' + ct + '" id="option' + ct + '" class="seloptions x-form-text x-form-field"  style="width: 202px;">' +
        '<img src="icons/delete.gif" onclick="removeoption(\'opdiv' + ct + '\')"/></div></div>' +
        '<a href="#" id="addoptionimage" onclick="addoptions(' + ct + ')" class="default-fields" style="width: 100px;margin: 0 auto; margin-top: 10px;">Add Options <img src="icons/add.gif"></a>' +
        '';
    jQuery("#otheroptions").append(options);

}

function getlabelupdated(savelabel,pid){

   
    switch (savelabel){

        case 'cname':
            savelabel = 'Name';
        break;

        case 'cfname':
            savelabel = 'FirstName';
        break;

        case 'state':
            savelabel = 'State';
        break;

        case 'phone':
            savelabel = 'Phone';
        break;

        case 'address1':
            savelabel = 'Address';
        break;

        default:

            var data;
            var param = {
                savelabel: savelabel
            };
            $.ajax({
                type: 'post',
                
                url: './ExtJS/emailtemplateeditor/scripteditor.php?projectid=' + pid + '&act=getcustomlabel',
                data: param,
                async: false,
                success: function (resp) {
                    data = resp;
                    
                },

            });
            savelabel = data;
            
    }

    return savelabel;
}


