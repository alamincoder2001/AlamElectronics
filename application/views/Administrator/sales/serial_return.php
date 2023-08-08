<style>
	.v-select{
		margin-top:-2.5px;
        float: right;
        min-width: 180px;
        margin-left: 5px;
	}
	.v-select .dropdown-toggle{
		padding: 0px;
        height: 25px;
	}
	.v-select input[type=search], .v-select input[type=search]:focus{
		margin: 0px;
	}
	.v-select .vs__selected-options{
		overflow: hidden;
		flex-wrap:nowrap;
	}
	.v-select .selected-tag{
		margin: 2px 0px;
		white-space: nowrap;
		position:absolute;
		left: 0px;
	}
	.v-select .vs__actions{
		margin-top:-5px;
	}
	.v-select .dropdown-menu{
		width: auto;
		overflow-y:auto;
	}
	#searchForm select{
		padding:0;
		border-radius: 4px;
	}
	#searchForm .form-group{
		margin-right: 5px;
	}
	#searchForm *{
		font-size: 13px;
	}
</style>

<div class="row" id="salesReturn">
	<div class="col-md-12" style="border-bottom: 1px solid #ccc;padding: 3px 0;">
		<form class="form-inline" id="searchForm">
			<div class="form-group" style="display:none;" v-bind:style="{display: customers.length > 0 ? '' : 'none'}">
				<label>Customer</label>
				<v-select v-bind:options="customers" label="display_name" v-model="selectedCustomer" v-on:input="getInvoices"></v-select>
			</div>

			<div class="form-group" style="display:none;" v-bind:style="{display: invoices.length > 0 ? '' : 'none'}">
				<label>Invoice</label>
				<v-select v-bind:options="invoices" label="SaleMaster_InvoiceNo" v-model="selectedInvoice" v-on:input="getSaleDetailsForReturn"></v-select>
			</div>
		</form>
	</div>
	<div style="display:none;" v-bind:style="{display: cart.length > 0 ? '' : 'none'}">
		<div class="col-xs-12 col-md-12 col-lg-12">
			<br>
			<div class="col-md-6">
				Return date: <input type="date" v-model="salesReturn.returnDate" v-bind:disabled="userType == 'u' ? true : false"><br><br>
				Invoice Discount: {{ selectedInvoice.SaleMaster_TotalDiscountAmount }}
			</div>
			<div class="col-md-6 text-right">
				<h4 style="margin:0px;padding:0px;">Customer Information</h4>
				Name: {{ selectedInvoice.Customer_Name }}<br>
				Address: {{ selectedInvoice.Customer_Address }}<br>
				Mobile: {{ selectedInvoice.Customer_Mobile }}
			</div>
			<div class="col-md-12">
				<div class="table-responsive">
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
								<td>{{ product.SaleDetails_TotalQuantity }}</td>
								<td>{{ product.SaleDetails_TotalAmount }}</td>
								<td>{{ product.returned_quantity }}</td>
								<td>{{ product.returned_amount }}</td>

                                <td>
                                    <template v-if="product.serials.length">
                                        <template v-for="(serial, ind) in product.serials">
                                            <label :for="serial.ps_serial_number" style="margin-right:7px; user-select:none">
                                                <template v-if="serial.ps_s_r_status == 'yes'"> <input type="checkbox" checked disabled> </template>
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
								<td colspan="5" style="text-align:right;padding-top:15px;">Note</td>
								<td colspan="2">
									<textarea style="width: 100%" v-model="salesReturn.note"></textarea>
								</td>
								<td>
									<button class="btn btn-success pull-left" v-on:click="saveSalesReturn">Save</button>
								</td>
								<td>Total: {{ salesReturn.total }}</td>
							</tr>
						</tfoot>
					</table>
				</div>
	
			</div>
		</div>

	</div>
</div>

<script src="<?php echo base_url();?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/vue-select.min.js"></script>
<script src="<?php echo base_url();?>assets/js/moment.min.js"></script>

<script>
	Vue.component('v-select', VueSelect.VueSelect);
	new Vue({
		el: '#salesReturn',
		data(){
			return {
				customers: [],
				selectedCustomer: null,
				invoices: [],
				selectedInvoice: {
					SaleMaster_InvoiceNo: '',
					SalseCustomer_IDNo: null,
					Customer_Name: '',
					Customer_Mobile: '',
					Customer_Address: '',
					SaleMaster_TotalDiscountAmount: 0
				},
				cart: [],
				salesReturn: {
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
		created(){
			this.getCustomers();
		},
		methods:{
			getCustomers() {
				axios.get('/get_customers').then(res => {
					this.customers = res.data;
					this.customers.unshift({
						Customer_SlNo: null,
						Customer_Name: 'General Customers',
						Customer_Type: 'G',
						display_name: 'General Customers'
					})
				})
			},
			getInvoices(){
				this.selectedInvoice = {
					SaleMaster_InvoiceNo: '',
					SalseCustomer_IDNo: null,
					Customer_Name: '',
					Customer_Mobile: '',
					Customer_Address: '',
					SaleMaster_TotalDiscountAmount: 0
				}

				this.invoices = [];

				if(this.selectedCustomer == null) {
					return;
				}

				if(this.selectedCustomer.Customer_Type == 'G') {
					arg = { customerType: 'G' }
				} else {
					arg = { customerId: this.selectedCustomer.Customer_SlNo }
				}

				axios.post('/get_sales', arg).then(res => {
					this.invoices = res.data.sales;
				})
			},
			getSaleDetailsForReturn(){
				if(this.selectedInvoice.SaleMaster_InvoiceNo == ''){
					return;
				}
				axios.post('/get_saledetails_for_return', {
                    salesId: this.selectedInvoice.SaleMaster_SlNo
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
			productReturnTotal(ind){
				if(this.cart[ind].return_quantity > (this.cart[ind].SaleDetails_TotalQuantity - this.cart[ind].returned_quantity)){
					alert('Return quantity is not valid');
					this.cart[ind].return_quantity = '';
				}

				if(parseFloat(this.cart[ind].return_rate) > parseFloat(this.cart[ind].SaleDetails_Rate)){
					alert('Rate is not valid');
					this.cart[ind].return_rate = '';
				}
				this.cart[ind].return_amount = parseFloat(this.cart[ind].return_quantity) * parseFloat(this.cart[ind].return_rate);
				this.calculateTotal();
			},
			calculateTotal(){
				this.salesReturn.total = this.cart.reduce((prev, cur) => {return prev + (cur.return_amount ? parseFloat(cur.return_amount) : 0.00)}, 0);
			},
			saveSalesReturn(){
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

				if(this.salesReturn.returnDate == null || this.salesReturn.returnDate == ''){
					alert('Enter date');
					return;
				}

				let data = {
					invoice: this.selectedInvoice,
					salesReturn: this.salesReturn,
					cart: this.cart
				}

				axios.post('/add_sales_return', data).then(res=>{
					let r = res.data;
					if(r.success){
					    alert(r.message);
						location.reload();
					}
				})
			}
		}
	})
</script>