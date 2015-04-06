/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new POCreateJs(), {
	_isCredit: true
	,loadPO: function(po) {
		var tmp = {};
		tmp.me = this;
		tmp.me._po = po;
		tmp.me.selectSupplier(tmp.me._po.supplier);
		tmp.me._purchaseOrderItems = tmp.me._po.purchaseOrderItems;
		tmp.me._purchaseOrderItems.each(function(purchaseOrderItem){
			tmp.me._addNewProductRow($$('.new-order-item-input .glyphicon.glyphicon-floppy-saved').first(), purchaseOrderItem);
		});
		return tmp.me;
	}
});