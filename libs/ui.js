Ext.onReady(function(){

    var workersLoading = new Ext.LoadMask(Ext.getBody(),
        {msg: "Loading Workers...", store: Ext.getCmp('WorkerCombo').store});

    var jobsLoading = new Ext.LoadMask(Ext.getBody(),
        {msg: "Loading Jobs...", store: Ext.getCmp('JobsGrid').store});

    var workersgridLoading = new Ext.LoadMask(Ext.getBody(),
        {msg: "Loading workers...", store: Ext.getCmp('WorkersGrid').store});

   Ext.Ajax.request({
        url: "../workers/Foobar/code",
        success: function(r) {Ext.getCmp("WorkerCode").setValue(r.responseText)},
   });

    var container = new Ext.TabPanel({
            height: 500,
            activeTab: 0,
            width: "98%",
            style: {
                marginTop: 10,
                marginLeft: 10,
            },
            renderTo: Ext.getBody(),
            items:[jobsGrid,jobForm,workersGrid,workerForm]
        });

});
