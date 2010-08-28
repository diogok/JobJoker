var wResponse , wLog= null;

var jobForm = new Ext.form.FormPanel({
        title: "Create a Job",
        frame: true,
        autoHeight: true,
        autoWeigth: true,
        items:[
            new Ext.form.ComboBox({
                    mode: 'local',
                    fieldLabel:"Worker",
                    valueField: 'name',
                    displayField: 'name',
                    forceSelection: true,
                    id: 'WorkerCombo',
                    width: 250,
                    emptyText: "Worker",
                    store: new Ext.data.JsonStore({
                        idProperty: "name",
                        root: "data",
                        fields: ['name'],
                        url: "../workers",
                        autoLoad: true
                    }),
                }),
            new Ext.form.TextArea({
                    fieldLabel: "Parameters<br />(json string)",
                    emptyText: '{"key":"value"}',
                    id: "Parameters",
                    width: 450,
                    height:250,
                }),
        ],
        buttons:[
            new Ext.Button({
                    text: "Add",
                    handler: function() {
                      var worker = Ext.getCmp('WorkerCombo').getRawValue();
                      var params = Ext.getCmp('Parameters').getValue();
                      if(params == null || params == "") {
                        params = "null";
                      }
                      var url    = "../jobs";
                      var json   = '{"worker":"'+ worker+ '","parameters":'+ params +'}';
                      if(worker != "") {
                          Ext.Ajax.request({
                                url: url,
                                params: json,
                                success: function() {Ext.getCmp('JobsGrid').store.load()},
                          });
                      }
                    }
                })
        ]
    });

var jobsGrid = new Ext.grid.GridPanel({ 
        width: 800,
        height: 420,
        border: true,
        title: "Manage Jobs",
        id: 'JobsGrid',
        colModel: new Ext.grid.ColumnModel({
                columns: [
                    {id: 'worker',header:"Worker",sortable:true,width:180},
                    {id: 'status',header:"Status",sortable:true,width:80},
                    new Ext.grid.DateColumn({
                        id: 'starttime',header:"Start time",sortable:true,width:130,
                        format: "m/d/y H:i:s" }),
                    new Ext.grid.DateColumn({
                        id: 'stoptime',header:"Stop time",sortable:true,width:130,
                        format: "m/d/y H:i:s" }),
                    {id: 'parameters',header:"Parameters",sortable:true,width:500},
                ]
            }),
        store: new Ext.data.JsonStore({
            idProperty: "id",
            root: "data",
            fields: ['worker','status','starttime','stoptime','parameters','id'],
            url: "../jobs",
            autoLoad: true
        }),
        sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    rowselect: function(sm, row, rec) { }
                }
        }),
        buttons: [
                 {text: "Reload", handler: function() {Ext.getCmp('JobsGrid').store.load()}},
                 {text: "Start",
                    handler: function(){
                      var job = Ext.getCmp('JobsGrid').getSelectionModel().getSelected();
                      if(job == undefined || job == null) return ;
                      Ext.Ajax.request({
                        url: "../jobs/"+ job.id+"/status",
                        method:"PUT",
                        params: "start",
                        success: function(response) {
                            Ext.getCmp('JobsGrid').store.load()
                        }});
                    }},
                 {text: "Stop",
                    handler: function(){
                      if(!confirm("Confirm stop this item?")) return;
                      var job = Ext.getCmp('JobsGrid').getSelectionModel().getSelected();
                      if(job == undefined || job == null) return ;
                      Ext.Ajax.request({
                        url: "../jobs/"+ job.id+"/status",
                        method:"PUT",
                        params: "stop",
                        success: function(response) {
                            Ext.getCmp('JobsGrid').store.load()
                        }});
                    }},
                 {text: "Kill",
                    handler: function(){
                      if(!confirm("Confirm kill this item?")) return;
                      var job = Ext.getCmp('JobsGrid').getSelectionModel().getSelected();
                      if(job == undefined || job == null) return ;
                      Ext.Ajax.request({
                        url: "../jobs/"+ job.id+"/status",
                        method:"PUT",
                        params: "kill",
                        success: function(response) {
                            Ext.getCmp('JobsGrid').store.load()
                        }});
                    }},
                 {text: "Delete",
                    handler: function(){
                      if(!confirm("Confirm delete this item?")) return;
                      var job = Ext.getCmp('JobsGrid').getSelectionModel().getSelected();
                      if(job == undefined || job == null) return ;
                      Ext.Ajax.request({
                        url: "../jobs/"+ job.id,
                        method:"DELETE",
                        success: function(response) {
                            Ext.getCmp('JobsGrid').store.load()
                        }});
                    }},
             {text: "Log",
                handler: function(){
                  var job = Ext.getCmp('JobsGrid').getSelectionModel().getSelected();
                  if(job == undefined || job == null) return ;
                  Ext.Ajax.request({
                    url: "../jobs/"+ job.id+"/log",
                    success: function(response) {
                      var response = Ext.util.JSON.decode(response.responseText);
                      if(wLog != null) {
                        wLog.close();
                      }
                      wLog = new Ext.Window({
                            title: "Job log",
                            width: 500,
                            height: 450,
                            layout: 'fit',
                            plain: true,
                            items: [ new Ext.form.TextArea({ }) ],
                            buttons: [{text:"Reload",handler:function(){
                              Ext.Ajax.request({
                                url: "../jobs/"+ job.id+"/log",
                                success: function(response) {
                                    var response = Ext.util.JSON.decode(response.responseText);
                                    wLog.items.get(0).setValue(response.data.map(function(r) {return r.message}).join("\n"));
                            }})}}]});
                      wLog.show();
                      wLog.items.get(0).setValue(response.data.map(function(r) {return r.message}).join("\n"));
                    },
                  });
                 }},
             {text: "Response",
                handler: function(){
                  var job = Ext.getCmp('JobsGrid').getSelectionModel().getSelected();
                  if(job == undefined || job == null) return ;
                  Ext.Ajax.request({
                    url: "../jobs/"+ job.id+"/response",
                    success: function(response) {
                      var response = Ext.util.JSON.decode(response.responseText);
                      if(wResponse != null) {
                        wResponse.close();
                      }
                      wResponse = new Ext.Window({
                            title: "Job response",
                            width: 500,
                            height: 450,
                            layout: 'fit',
                            plain: true,
                            items: [ new Ext.form.TextArea({ }) ],
                            buttons: [{text:"Reload",handler:function(){
                              Ext.Ajax.request({
                                url: "../jobs/"+ job.id+"/response",
                                success: function(response) {
                                    var response = Ext.util.JSON.decode(response.responseText);
                                    wResponse.items.get(0).setValue(response.data.map(function(r) {return r.message}).join("\n"));
                            }})}}]});
                      wResponse.show();
                      wResponse.items.get(0).setValue(response.data.map(function(r) {return r.message}).join("\n"));
                    },
                  });
                 }},
             ]
    });
