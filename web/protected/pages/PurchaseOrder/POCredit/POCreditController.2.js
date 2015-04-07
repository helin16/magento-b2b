var PageJs=new Class.create
PageJs.prototype=Object.extend(new POCreateJs,{_isCredit:!0,loadPO:function(e){var r={}
return r.me=this,r.me._po=e,r.me.selectSupplier(r.me._po.supplier),r.me._purchaseOrderItems=r.me._po.purchaseOrderItems,r.me._purchaseOrderItems.each(function(e){r.me._addNewProductRow($$(".new-order-item-input .glyphicon.glyphicon-floppy-saved").first(),e)}),r.me}})