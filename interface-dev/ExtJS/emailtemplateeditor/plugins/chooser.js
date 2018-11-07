/*
 * Ext JS Library 2.3.0
 * Copyright(c) 2006-2009, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://extjs.com/license
 */

/*
 * Ext JS Library 2.0
 * Copyright(c) 2006-2007, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://extjs.com/license
 */
 
var ImageChooser = function(config){
	this.config = config;
}

ImageChooser.prototype = {
    // cache data by image name for easy lookup
    lookup : {},
    
	show : function(el, callback){
		 
		bcid	    = this.config.bcid;
		templateid  = this.config.templateid;
		projectid   = this.config.projectid;
		sigid		= this.config.sigid;
		type        = this.config.type;
		

		if(!this.win){
			this.initTemplates();
			
			this.store = new Ext.data.JsonStore({
			    url: this.config.url,
			    root: 'images',
			    fields: [
			        'name', 'url',
			        {name:'size', type: 'float'},
			        {name:'lastmod', type:'date', dateFormat:'timestamp'}
			    ],
			    listeners: {
			    	'load': {fn:function(){ this.view.select(0); }, scope:this, single:true}
			    }
			});
			this.store.load();
			
			var formatSize = function(data){
		        if(data.size < 1024) {
		            return data.size + " bytes";
		        } else {
		            return (Math.round(((data.size*10) / 1024))/10) + " KB";
		        }
		    };
			
			var formatData = function(data){
		    	data.shortName = data.name.ellipse(15);
		    	data.sizeString = formatSize(data);
		    	data.dateString = new Date(data.lastmod).format("m/d/Y g:i a");
		    	this.lookup[data.name] = data;
		    	return data;
		    };
			
		    this.view = new Ext.DataView({
				tpl: this.thumbTemplate,
				singleSelect: true,
				overClass:'x-view-over',
				itemSelector: 'div.thumb-wrap',
				emptyText : '<div style="padding:10px;">No images match the specified filter</div>',
				store: this.store,
				listeners: {
					'selectionchange': {fn:this.showDetails, scope:this, buffer:100},
					'dblclick'       : {fn:this.insertimgtoeditor, scope:this},
					'loadexception'  : {fn:this.onLoadException, scope:this},
					'beforeselect'   : {fn:function(view){
				        return view.store.getRange().length > 0;
				    }}
				},
				prepareData: formatData.createDelegate(this)
			});
		    
			var cfg = {
				title: 'Attachments',
				frame: 'true',
		    	id: 'img-chooser-dlg',
		    	layout: 'border',
				minWidth: 500,
				minHeight: 300,
				modal: false,
				closeAction: 'close',
				border: false,
				items:[{
					id: 'img-chooser-view',
					region: 'center',
					autoScroll: true,
					items: this.view,
                    tbar:[{
                    	text: 'Filter:'
                    },{
                    	xtype: 'textfield',
                    	id: 'filter',
                    	selectOnFocus: true,
                    	width: 200,
                    	listeners: {
                    		'render': {fn:function(){
						    	Ext.getCmp('filter').getEl().on('keyup', function(){
						    		this.filter();
						    	}, this, {buffer:500});
                    		}, scope:this}
                    	}
                    
				    }]
				},{
					id: 'img-detail-panel',
					region: 'east',
					split: true,
					width: 150,
					minWidth: 150,
					maxWidth: 250
				
				}],
				buttons: [{
					id: 'ok-btn',
					text: 'Insert',
					handler: this.insertimgtoeditor,
					scope: this
					
				},{
					text: 'Cancel',
					handler: function(){ this.win.close(); },
					scope: this
				}],
				keys: {
					key: 27, // Esc key
					handler: function(){ this.win.close(); },
					scope: this
				}
			};
			Ext.apply(cfg, this.config);
		    this.win = new Ext.Window(cfg);
		}
		
		this.reset();
	    this.win.show(el);
		this.callback = callback;
		this.animateTarget = el;
	},
	
	
	initTemplates : function(){

		if (type == 'signature') {   
			prefix = bcid + '/emailsignature/' + projectid + '/' + sigid
			idtype = sigid; 
		}else{ 
			prefix = bcid + '/attachments/' + projectid + '/' + templateid;
			idtype = templateid;
		}
		filename = '{name}';
		name_id = '{name}';



		this.thumbTemplate = new Ext.XTemplate(
			'<tpl for=".">',
				'<div id="thumb#{name}"><div class="thumb-wrap" id="{name}">',
				// '<div class="thumb"><img src="{url}"  onerror="handleMissingImg(this);" title="{name}"></div>',
				'<div class="thumb"><img src="{url}" alt="{name}" onerror="handleMissingImg(this);" title="{name}"></div>',
				'<span style="font-weight: bold">{shortName}</span>',
				'</div></div>',
				
			'</tpl>'
		);
		this.thumbTemplate.compile();

	
	
		this.detailsTemplate = new Ext.XTemplate(
			
			'<div class="details">',
				'<tpl for=".">',
					'<div id="thumb2#{name}"><img width="100" height="100" src="{url}" "><div class="details-info">',
					'<b>File Name:</b>',
					'<span>{name}</span>',
					'<span style="margin-right: -50px"><a href="#" onclick="removeattachments3(\'' + idtype + '\',\'' + filename + '\',\'' + prefix + '\',\'' + name_id + '\' )" style="margin-right: 50px; color:#f44242;">Delete</a></span></div>',
					
				'</tpl>',
			'</div>'
		);
		this.detailsTemplate.compile();
	},
	
	showDetails : function(){
	    var selNode = this.view.getSelectedNodes();
	    var detailEl = Ext.getCmp('img-detail-panel').body;
		if(selNode && selNode.length > 0){
			selNode = selNode[0];
			Ext.getCmp('ok-btn').enable();
		    var data = this.lookup[selNode.id];
            detailEl.hide();
            this.detailsTemplate.overwrite(detailEl, data);
            detailEl.slideIn('l', {stopFx:true,duration:.2});
		}else{
		    Ext.getCmp('ok-btn').disable();
		    detailEl.update('');
		}
	},
	
	filter : function(){
		var filter = Ext.getCmp('filter');
		this.view.store.filter('name', filter.getValue());
		this.view.select(0);
	},
	
	sortImages : function(){
		var v = Ext.getCmp('sortSelect').getValue();
    	this.view.store.sort(v, v == 'name' ? 'asc' : 'desc');
    	this.view.select(0);
    },
	
	reset : function(){
		if(this.win.rendered){
			Ext.getCmp('filter').reset();
			this.view.getEl().dom.scrollTop = 0;
		}
	    this.view.store.clearFilter();
		this.view.select(0);
	},
	
	insertimgtoeditor : function(){
		var nodeData = this.view.getSelectedRecords();
		for(var i = 0, len = nodeData.length; i < len; i++){
		var data = nodeData[i].data;
		// alert(data.url)
		
		var imginsert   = '<img src=' + data.url + ' width="100" height="100">';

		if (type == 'signature') {
			var currentbody = Ext.getCmp('sig').getValue();
			var newbody     = currentbody + imginsert;
			Ext.getCmp('sig').setValue(newbody);
			
		}else{
			var currentbody = Ext.getCmp('bio').getValue();
			var newbody     = currentbody + imginsert;
			Ext.getCmp('bio').setValue(newbody);
		}
		
	

	}
	
		
	},
	
	onLoadException : function(v,o){
	    this.view.getEl().update('<div style="padding:10px;">Error loading images.</div>'); 
	}

};

String.prototype.ellipse = function(maxLength){
    if(this.length > maxLength){
        return this.substr(0, maxLength-3) + '...';
    }
    return this;
};
