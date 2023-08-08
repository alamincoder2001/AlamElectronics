<style>
	.v-select{
		margin-bottom: 5px;
	}
	.v-select .dropdown-toggle{
		padding: 0px;
	}
	.v-select input[type=search], .v-select input[type=search]:focus{
		margin: 0px;
	}
	.v-select .selected-tag{
		margin: 0px;
	}
</style>

<div class="row" id="purchaseReturn">
	<div class="col-xs-12 col-md-12 col-lg-12" style="border-bottom:1px #ccc solid;">
		<div class="form-group" style="margin-top:10px;">
			<label class="col-sm-1 col-sm-offset-1 control-label no-padding-right" for="purchaseInvoiceno"> Invoice no </label>
			<div class="col-sm-2">
				<v-select v-bind:options="invoices" label="PurchaseMaster_InvoiceNo" v-model="selectedInvoice" v-on:input="getPurchaseDetailsForReturn"></v-select>
			</div>
		</div>
	</div>
	<div class="col-xs-12 col-md-12 col-lg-12" v-if="cart.length > 0" style="display:none" v-bind:style="{display: cart.length > 0 ? '' : 'none'}">
		<br>
		<div class="table-responsive">
			<br>
			<div class="col-md-6">
				Return date: <input type="date" v-model="purchaseReturn.returnDate" v-bind:disabled="userType == 'u' ? true : false">
			</div>
			<div class="col-md-6 text-right">
				<h4 style="margin:0px;padding:0px;">Supplier Information</h4>
				Name: {{ selectedInvoice.Supplier_Name }}<br>
				Address: {{ selectedInvoice.Supplier_Address }}<br>
				Mobile: {{ selectedInvoice.Supplier_Mobile }}
			</div>
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>Sl</th>
						<th>Product</th>
						<th>Quantity</th>
						<th>Amount</th>
						<th>Already returned quantity</th>
						<th>Already returned amount</th>
                        <th>Serials / Qty</th>
						<th>Return Rate</th>
						<th>Return Amount</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(product, sl) in cart">
						<td>{{ sl + 1 }}</td>
						<td>{{ product.Product_Name }}</td>
						<td>{{ product.PurchaseDetails_TotalQuantity }}</td>
						<td>{{ product.PurchaseDetails_TotalAmount }}</td>
						<td>{{ product.returned_quantity }}</td>
						<td>{{ product.returned_amount }}</td>
						
                        <td>
                            <template v-if="product.serials.length">
                                <template v-for="(serial, ind) in product.serials">
                                    <label :for="serial.ps_serial_number" style="margin-right:7px; user-select:none">
										<template v-if="serial.ps_p_r_status == 'yes'"> <input type="checkbox" checked disabled> </template>
										<template v-else> <input type="checkbox" :id="serial.ps_serial_number" v-model="serial.is_return"> </template>
                                        {{ serial.ps_serial_number }}
                                    </label>
                                </template>
                            </template>
                            <template v-else>
                                <input type="text" v-model="product.return_quantity" v-on:input="productReturnTotal(sl)" style="width:80px">
                            </template>
                        </td>

						<td><input type="text" v-model="product.return_rate" v-on:input="productReturnTotal(sl)"></td>
						<td>{{ product.return_amount }}</td>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="5" style="text-align:right; padding-top:15px;">Note</td>
						<td colspan="2">
							<textarea style="width: 100%" v-model="purchaseReturn.note"></textarea>
						</td>
						<td>
							<button class="btn btn-success pull-left" v-on:click="savePurchaseReturn">Save</button>
						</td>
						<td>Total: {{ purchaseReturn.total }}</td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>assets/js/vue/vue.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/vue-select.min.js"></script>
<script src="<?php echo base_url();?>assets/js/moment.min.js"></script>

<script>
	Vue.component('v-select', VueSelect.VueSelect);
	new Vue({
		el: '#purchaseReturn',
		data() {
			return {
				invoices: [],
				selectedInvoice: null,
				cart: [],
				purchaseReturn: {
					returnDate: moment().format('YYYY-MM-DD'),
					total: 0.00,
					note: ''
				},
				userType: '<?php echo $this->session->userdata("accountType");?>'
			}
		},
        watch: {
            cart: {
                handler(items) {
					if (!items.length) return

					items.forEach(function(product) {
						let returnAmount = 0;
						if (product.serials.length) {
							let returnableSerialQty = 0;
							product.serials.forEach(function(serial) {
								if (serial.is_return) {
									returnableSerialQty += 1;
									returnAmount += +product.return_rate;
								}
							})
							product.return_quantity = returnableSerialQty;
						} else {
							if (product.return_quantity) {
								returnAmount += +product.return_rate * +product.return_quantity;
							}
						}
						product.return_amount = returnAmount;
					});

					this.calculateTotal();
				},

				deep: true
            }
        },
		created() {
			this.getPurchases();
		},
		methods: {
			getPurchases() {
				axios.get('/get_purchases').then(res => {
					this.invoices = res.data.purchases;
				})
			},
			getPurchaseDetailsForReturn() {
				if(this.selectedInvoice == null){
					alert('Select invoice');
					return;
				}
				axios.post('/get_purchasedetails_for_return', {
                    purchaseId: this.selectedInvoice.PurchaseMaster_SlNo
                }).then(res => {
					this.cart = res.data.map(function(purchase) {
						purchase.return_quantity = 0;
                        purchase.serials.map(function(serial) {
                            serial.is_return = false;
                            return serial;
                        });
                        return purchase;
                    });
				})
			},
			async productReturnTotal(ind) {
				let stock = await axios.post('/get_product_stock', {
                    productId: this.cart[ind].Product_IDNo 
                }).then(res => {
					return res.data;
				})

				if(stock < this.cart[ind].return_quantity) {
					alert('Unavailable stock');
					this.cart[ind].return_quantity = '';
					return;
				}

				if(this.cart[ind].return_quantity > (this.cart[ind].PurchaseDetails_TotalQuantity - this.cart[ind].returned_quantity)){
					alert('Return quantity is not valid');
					this.cart[ind].return_quantity = '';
				}

				if(parseFloat(this.cart[ind].return_rate) > parseFloat(this.cart[ind].PurchaseDetails_Rate)){
					alert('Rate is not valid');
					this.cart[ind].return_rate = '';
				}

				this.cart[ind].return_amount = parseFloat(this.cart[ind].return_quantity) * parseFloat(this.cart[ind].return_rate);
				this.calculateTotal();
			},
			calculateTotal(){
				this.purchaseReturn.total = this.cart.reduce((prev, cur) => {return prev + (cur.return_amount ? parseFloat(cur.return_amount) : 0.00)}, 0);
			},
			savePurchaseReturn(){
				let returnableTotalQty = 0;
				this.cart.filter(product => {
					if (product.serials.length) {
						let returnSerial = product.serials.filter(serial => {
							return serial.is_return && product.return_rate;
						});
						returnableTotalQty += +returnSerial.length;
					} else {
						if (product.return_quantity && product.return_rate)
						returnableTotalQty += +product.return_quantity;
					}
				});

				if(returnableTotalQty == 0) {
					alert('No products to return');
					return;
				}

				if(this.purchaseReturn.returnDate == null || this.purchaseReturn.returnDate == ''){
					alert('Enter date');
					return;
				}

				let data = {
					invoice: this.selectedInvoice,
					purchaseReturn: this.purchaseReturn,
					cart: this.cart
				}

				axios.post('/add_purchase_return', data).then(async res => {
					let r = res.data;
					if(r.success) {
						alert(r.message);
						location.reload();
					}
				})
			}
		}
	})
</script>
