var wCode = null ;

var workerForm = new Ext.form.FormPanel({
        title: "Create a worker",
        frame: true,
        autoHeight: true,
        autoWeigth: true,
        items:[
            new Ext.form.TextField({
                    fieldLabel: "Name",
                    emptyText: '',
                    id: "WorkerName",
                    width: 250,
                }),
            new Ext.form.TextArea({
                    fieldLabel: "Worker<br />(PHP code)",
                    emptyText: '',
                    id: "WorkerCode",
                    width: 450,
                    height:250,
                }),
        ],
        buttons:[
            new Ext.Button({
                    text: "Add",
                    handler: function() {
                      var name = Ext.getCmp('WorkerName').getValue();
                      var worker = Ext.getCmp('WorkerCode').getValue();
                      if(worker != "" && name != "") {
                          Ext.Ajax.request({
                                url: "../workers/"+ name,
                                method:"PUT",
                                params: worker,
                                success: function() {Ext.getCmp('WorkersGrid').store.load();
                                                     Ext.getCmp('WorkerCombo').store.load()},
                          });
                      }
                    }
                })
        ]
    });

var workersGrid = new Ext.grid.GridPanel({ 
        width: 800,
        height: 420,
        border: true,
        title: "Manage Workers",
        id: 'WorkersGrid',
        colModel: new Ext.grid.ColumnModel({
                columns: [
                    {id: 'name',header:"Worker",sortable:true,width:180}
                ]
            }),
        store: new Ext.data.JsonStore({
            idProperty: "name",
            root: "data",
            fields: ['name'],
            url: "../workers",
            autoLoad: true
        }),
        sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    rowselect: function(sm, row, rec) { }
                }
        }),
        buttons: [
                 {text: "Reload", handler: function() {Ext.getCmp('WorkersGrid').store.load()}},
                 {text: "Delete",
                    handler: function(){
                      if(!confirm("Confirm delete this item?")) return;
                      var job = Ext.getCmp('WorkersGrid').getSelectionModel().getSelected();
                      if(job == undefined || job == null) return ;
                      Ext.Ajax.request({
                        url: "../workers/"+ job.data.name,
                        method:"DELETE",
                        success: function(response) {
                            Ext.getCmp('WorkersGrid').store.load()
                            Ext.getCmp('WorkerCombo').store.load()
                        }});
                    }},
                 {text: "Code",
                    handler: function(){
                      var job = Ext.getCmp('WorkersGrid').getSelectionModel().getSelected();
                      if(job == undefined || job == null) return ;
                      Ext.Ajax.request({
                        url: "../workers/"+ job.data.name +"/code",
                        success: function(response) {
                          if(wCode != null) {
                            wCode.close();
                          }
                          wCode = new Ext.Window({
                                title: job.data.name+".php",
                                width: 500,
                                height: 450,
                                layout: 'fit',
                                plain: true,
                                items: [ new Ext.form.TextArea({ id:"Code" }) ],
                                buttons: [
                                {text:"Save",handler:function(){
                                  Ext.Ajax.request({
                                    url: "../workers/"+ job.data.name,
                                    method:"PUT",
                                    params: Ext.getCmp("Code").getValue(),
                                    success: function(response) {}
                                  })}},
                                {text:"Reload",handler:function(){
                                  Ext.Ajax.request({
                                    url: "../workers/"+ job.data.name+"/code",
                                    success: function(response) {
                                        wCode.items.get(0).setValue(response.responseText);
                                }})}}]});
                          wCode.show();
                          wCode.items.get(0).setValue(response.responseText);
                        },
                      });
                 }},
             ]
    });

