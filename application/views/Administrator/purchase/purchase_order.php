<style>
	.modal-mask {
		position: fixed;
		z-index: 9998;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background-color: rgba(0, 0, 0, .5);
		display: table;
		transition: opacity .3s ease;
	}
	.modal-wrapper {
		display: table-cell;
		vertical-align: middle;
	}

	.modal-container {
		width: 400px;
		margin: 0px auto;
		background-color: #fff;
		border-radius: 2px;
		box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
		transition: all .3s ease;
		font-family: Helvetica, Arial, sans-serif;
	}
	.modal-header {
		padding-bottom: 0 !important;
	}

	.modal-header h3 {
		margin-top: 0;
		color: #42b983;
	}

	.modal-body{
		overflow-y: auto !important;
		height: 300px !important;
		margin: -8px -14px -44px !important;
	}

	.modal-default-button {
		float: right;
	}

	.modal-enter {
		opacity: 0;
	}

	.modal-leave-active {
		opacity: 0;
	}

	.modal-enter .modal-container,
	.modal-leave-active .modal-container {
		-webkit-transform: scale(1.1);
		transform: scale(1.1);
	}

	.modal-footer {
		padding-top: 14px !important;
		margin-top: 30px !important;
	}

	.v-select{
		margin-bottom: 5px;
	}
	.v-select .dropdown-toggle{
		padding: 0px;
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
	#branchDropdown .vs__actions button{
		display:none;
	}
	#branchDropdown .vs__actions .open-indicator{
		height:15px;
		margin-top:7px;
	}
	
</style>

<div class="row" id="purchase">
	<!-- purchase product serial modal -->

	<div style="display:none" id="serial-modal" v-if="" v-bind:style="{display:serialModalStatus?'block':'none'}">
		<transition name="modal">
			<div class="modal-mask">
				<div class="modal-wrapper">
					<div class="modal-container">
						<div class="modal-header">
							<slot name="header">
								<h3>Serial Number Add</h3>
							</slot>
						</div>

						<div class="modal-body">
							<slot name="body">
								<form @submit.prevent="serial_add_action">
									<div class="form-group">
										<div class="col-sm-12" style="display: flex; margin-bottom: 5px;">
											<input type="text" autocomplete="off" ref="serialnumberadd" id="serial_number" name="serial_number" v-model="get_serial_number" class="form-control" placeholder="Please Enter Serial Number" style="height: 30px;" />

											<input type="submit" class="btn btn-sm btn primary" style="border: none; font-size: 13px; line-height: 0.38; margin-left: -1px; background-color: #42b983 !important; width: 7px; height: 29px; margin-left: 2px;" value="Add">

										</div>
									</div>
								</form>
							</slot>
							<table class="table" style="margin-top: 15px;">
								<thead>
									<tr>
										<th scope="col">Serial Number</th>
										<th scope="col">Action</th>
									</tr>
								</thead>
								<tbody>
	
									<tr v-if="serial_cart">
										<td>{{serial_cart}}</td>
										<td @click="remove_serial_item()"> <span class="badge badge-danger badge-pill" style="cursor:pointer"><i class="fa fa-times"></i></td>
									</tr>
	
								</tbody>
							</table>
						</div>
						<div class="modal-footer">
							<slot name="footer">
								<button class="modal-default-button" @click="serialHideModal" style="background: #59b901;border: none;font-size: 18px;color: white;">
									OK
								</button>
								<button class="modal-default-button" @click="serialHideModal" style="background: rgb(255, 255, 255);border: none;font-size: 18px;color: #de0000;margin-right: 6px;">
									Close
								</button>
							</slot>
						</div>
					</div>
				</div>
			</div>
		</transition>
	</div>
	<!-- purchase product serial modal -->
	<div class="col-xs-12 col-md-12 col-lg-12" style="border-bottom:1px #ccc solid;margin-bottom:5px;">
		<div class="row">
			<div class="form-group">
				<label class="col-sm-1 control-label no-padding-right"> Invoice no </label>
				<div class="col-sm-2">
					<input type="text" id="invoice" name="invoice" v-model="purchase.invoice" readonly style="height:26px;"/>
				</div>
			</div>

			<div class="form-group">
				<label class="col-sm-2 control-label no-padding-right"> Purchase For </label>
				<div class="col-sm-3">
					<v-select id="branchDropdown" v-bind:options="branches" v-model="selectedBranch" label="Brunch_name" disabled></v-select>
				</div>
			</div>

			<div class="form-group">
				<label class="col-sm-1 control-label no-padding-right"> Date </label>
				<div class="col-sm-3">
					<input class="form-control" id="purchaseDate" name="purchaseDate" type="date" v-model="purchase.purchaseDate" v-bind:disabled="userType == 'u' ? true : false" style="border-radius: 5px 0px 0px 5px !important;padding: 4px 6px 4px !important;width: 230px;" />
				</div>
			</div>
		</div>
	</div>

	<div class="col-xs-9 col-md-9 col-lg-9">
		<div class="widget-box">
			<div class="widget-header">
				<h4 class="widget-title">Supplier & Product Information</h4>
				<div class="widget-toolbar">
					<a href="#" data-action="collapse">
						<i class="ace-icon fa fa-chevron-up"></i>
					</a>

					<a href="#" data-action="close">
						<i class="ace-icon fa fa-times"></i>
					</a>
				</div>
			</div>

			<div class="widget-body">
				<div class="widget-main">
					<div class="row">
						<div class="col-sm-6">
							<div class="form-group">
								<label class="col-sm-4 control-label no-padding-right"> Supplier </label>
								<div class="col-sm-7">
									<v-select v-bind:options="suppliers" v-model="selectedSupplier" v-on:input="onChangeSupplier" label="display_name"></v-select>
								</div>
								<div class="col-sm-1" style="padding: 0;">
									<a href="<?= base_url('supplier') ?>" title="Add New Supplier" class="btn btn-xs btn-danger" style="height: 25px; border: 0; width: 27px; margin-left: -10px;" target="_blank"><i class="fa fa-plus" aria-hidden="true" style="margin-top: 5px;"></i></a>
								</div>
							</div>

							<div class="form-group" style="display:none;" v-bind:style="{display: selectedSupplier.Supplier_Type == 'G' ? '' : 'none'}">
								<label class="col-sm-4 control-label no-padding-right"> Name </label>
								<div class="col-sm-8">
									<input type="text" placeholder="Supplier Name" class="form-control" v-model="selectedSupplier.Supplier_Name" />
								</div>
							</div>

							<div class="form-group">
								<label class="col-sm-4 control-label no-padding-right"> Mobile No </label>
								<div class="col-sm-8">
									<input type="text" placeholder="Mobile No" class="form-control" v-model="selectedSupplier.Supplier_Mobile" v-bind:disabled="selectedSupplier.Supplier_Type == 'G' ? false : true" />
								</div>
							</div>

							<div class="form-group">
								<label class="col-sm-4 control-label no-padding-right"> Address </label>
								<div class="col-sm-8">
									<textarea class="form-control" v-model="selectedSupplier.Supplier_Address" v-bind:disabled="selectedSupplier.Supplier_Type == 'G' ? false : true"></textarea>
								</div>
							</div>
						</div>

						<div class="col-sm-6">
							<form v-on:submit.prevent="addToCart">
								<div class="form-group">
									<label class="col-sm-4 control-label no-padding-right"> Product </label>
									<div class="col-sm-7">
										<v-select v-bind:options="products" v-model="selectedProduct" label="display_text" v-on:input="onChangeProduct"></v-select>
									</div>
									<div class="col-sm-1" style="padding: 0;">
										<a href="<?= base_url('product') ?>" title="Add New Product" class="btn btn-xs btn-danger" style="height: 25px; border: 0; width: 27px; margin-left: -10px;" target="_blank"><i class="fa fa-plus" aria-hidden="true" style="margin-top: 5px;"></i></a>
									</div>
								</div>

								<div class="form-group" style="display:none;">
									<label class="col-sm-4 control-label no-padding-right"> Group Name</label>
									<div class="col-sm-8">
										<input type="text" id="group" name="group" class="form-control" placeholder="Group name" readonly />
									</div>
								</div>

								<div class="form-group">
									<label class="col-sm-4 control-label no-padding-right"> Pur. Rate </label>
									<div class="col-sm-8">
										<input type="text" id="purchaseRate" name="purchaseRate" class="form-control" placeholder="Pur. Rate" v-model="selectedProduct.Product_Purchase_Rate" v-on:input="productTotal" required/>
									</div>

									<!-- <label class="col-sm-2 control-label no-padding-right"> Quantity </label>
									<div class="col-sm-3">
										<input type="text" step="0.01" id="quantity" name="quantity" class="form-control" placeholder="Quantity" ref="quantity" v-model="selectedProduct.quantity" v-on:input="productTotal" required/>
									</div> -->
								</div>

								<div class="form-group">
									<label class="col-sm-4 control-label no-padding-right"> Serial Qty </label>
									<div class="col-sm-8" style="display: flex;">
										<input type="text" step="0.01" id="quantity" autocomplete="off" name="quantity" class="form-control" placeholder="Serial Quantity" ref="quantity" v-model="selectedProduct.quantity" v-on:input="productTotal" />
										<button type="button" id="show-modal" @click="serialShowModal" style="background: rgb(210, 0, 0); color: white; border: none; font-size: 15px; height: 24px; margin-left: 1px;"><i class="fa fa-plus"></i></button>
									</div>
								</div>

								<div class="form-group">
									<label class="col-sm-4 control-label no-padding-right"> Color </label>
									<div class="col-sm-7">
										<v-select v-bind:options="colors" v-model="selectedColor" label="color_name"></v-select>
									</div>
									<div class="col-sm-1" style="padding: 0;">
										<a href="<?= base_url('color') ?>" title="Add New Color" class="btn btn-xs btn-danger" style="height: 25px; border: 0; width: 27px; margin-left: -10px;" target="_blank"><i class="fa fa-plus" aria-hidden="true" style="margin-top: 5px;"></i></a>
									</div>
								</div>

								<div class="form-group" style="display:none;">
									<label class="col-sm-4 control-label no-padding-right"> Cost </label>
									<div class="col-sm-3">
										<input type="text" id="cost" name="cost" class="form-control" placeholder="Cost" readonly />
									</div>
								</div>

								<div class="form-group">
									<label class="col-sm-4 control-label no-padding-right"> Total Amount </label>
									<div class="col-sm-8">
										<input type="text" id="productTotal" name="productTotal" class="form-control" readonly v-model="selectedProduct.total"/>
									</div>
								</div>

								<div class="form-group">
									<label class="col-sm-4 control-label no-padding-right"> Selling Price </label>
									<div class="col-sm-8">
										<input type="text" id="sellingPrice" name="sellingPrice" class="form-control" v-model="selectedProduct.Product_SellingPrice"/>
									</div>
								</div>

								<div class="form-group">
									<label class="col-sm-4 control-label no-padding-right"> Warranty <small>(Month)</small> </label>
									<div class="col-sm-8">
										<input type="number" v-model.number="warranty_month" min="0" class="form-control" />
									</div>
								</div>

								<div class="form-group">
									<label class="col-sm-4 control-label no-padding-right"> </label>
									<div class="col-sm-8">
										<button type="submit" class="btn btn-default pull-right">Add Cart</button>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>


		<div class="col-xs-12 col-md-12 col-lg-12" style="padding-left: 0px;padding-right: 0px;">
			<div class="table-responsive">
				<table class="table table-bordered" style="color:#000;margin-bottom: 5px;">
					<thead>
						<tr>
							<th style="width:4%;color:#000;">SL</th>
							<th style="width:20%;color:#000;">Product Name</th>
							<th style="width:13%;color:#000;">Category</th>
							<th style="width:13%;color:#000;">Color</th>
							<th style="width:8%;color:#000;">Purchase Rate</th>
							<th style="width:5%;color:#000;">Quantity</th>
							<th style="width:13%;color:#000;">Total Amount</th>
							<th style="width:20%;color:#000;">Action</th>
						</tr>
					</thead>
					<tbody style="display:none;" v-bind:style="{display: cart.length > 0 ? '' : 'none'}">
						<tr v-for="(product, sl) in cart">
							<td>{{ sl + 1}}</td>
							<td>{{ product.name }} <br>
							<span v-if="product.SerialCartStore">({{ product.SerialCartStore }})</span>
							</td>
							<td>{{ product.categoryName }}</td>
							<td>{{ product.color_name }}</td>
							<td>{{ product.purchaseRate }}</td>
							<td>{{ product.quantity }}</td>
							<td>{{ product.total }}</td>
							<td><a href="" v-on:click.prevent="removeFromCart(sl)"><i class="fa fa-trash"></i></a></td>
						</tr>

						<tr>
							<td colspan="7"></td>
						</tr>

						<tr style="font-weight: bold;">
							<td colspan="4">Note</td>
							<td colspan="3">Sub Total</td>
						</tr>

						<tr>
							<td colspan="4"><textarea style="width: 100%;font-size:13px;" placeholder="Note" v-model="purchase.note"></textarea></td>
							<td colspan="3" style="padding-top: 15px;font-size:18px;">{{ purchase.subTotal }}</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>


	<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
		<div class="widget-box">
			<div class="widget-header">
				<h4 class="widget-title">Amount Details</h4>
				<div class="widget-toolbar">
					<a href="#" data-action="collapse">
						<i class="ace-icon fa fa-chevron-up"></i>
					</a>

					<a href="#" data-action="close">
						<i class="ace-icon fa fa-times"></i>
					</a>
				</div>
			</div>

			<div class="widget-body">
				<div class="widget-main">
					<div class="row">
						<div class="col-sm-12">
							<div class="table-responsive">
								<table style="color:#000;margin-bottom: 0px;">
									<tr>
										<td>
											<div class="form-group">
												<label class="col-sm-12 control-label no-padding-right">Sub Total</label>
												<div class="col-sm-12">
													<input type="number" id="subTotal" name="subTotal" class="form-control" v-model="purchase.subTotal" readonly />
												</div>
											</div>
										</td>
									</tr>

									<tr>
										<td>
											<div class="form-group">
												<label class="col-sm-12 control-label no-padding-right"> Vat </label>
												<div class="col-sm-12">
													<input type="number" id="vatPercent" name="vatPercent" v-model="vatPercent" v-on:input="calculateTotal" style="width:50px;height:25px;" />
													<span style="width:20px;"> % </span>
													<input type="number" id="vat" name="vat" v-model="purchase.vat" readonly style="width:140px;height:25px;" />
												</div>
											</div>
										</td>
									</tr>

									<tr>
										<td>
											<div class="form-group">
												<label class="col-sm-12 control-label no-padding-right">Discount</label>
												<div class="col-sm-12">
													<input type="number" id="discount" name="discount" class="form-control" v-model="purchase.discount" v-on:input="calculateTotal" />
												</div>
											</div>
										</td>
									</tr>

									<tr>
										<td>
											<div class="form-group">
												<label class="col-sm-12 control-label no-padding-right">Transport / Labour Cost</label>
												<div class="col-sm-12">
													<input type="number" id="freight" name="freight" class="form-control" v-model="purchase.freight" v-on:input="calculateTotal" />
												</div>
											</div>
										</td>
									</tr>

									<tr>
										<td>
											<div class="form-group">
												<label class="col-sm-12 control-label no-padding-right">Total</label>
												<div class="col-sm-12">
													<input type="number" id="total" class="form-control" v-model="purchase.total" readonly />
												</div>
											</div>
										</td>
									</tr>

									<tr>
										<td>
											<div class="form-group">
												<label class="col-sm-12 control-label no-padding-right">Paid</label>
												<div class="col-sm-12">
													<input type="number" id="paid" class="form-control" v-model="purchase.paid" v-on:input="calculateTotal" v-bind:disabled="selectedSupplier.Supplier_Type == 'G' ? true : false"/>
												</div>
											</div>
										</td>
									</tr>

									<tr>
										<td>
											<div class="form-group">
												<label class="col-sm-12 control-label no-padding-right">Due</label>
												<div class="col-sm-6">
													<input type="number" id="due" name="due" class="form-control" v-model="purchase.due" readonly />
												</div>
												<div class="col-sm-6">
													<input type="number" id="previousDue" name="previousDue" class="form-control" v-model="purchase.previousDue" readonly style="color:red;" />
												</div>
											</div>
										</td>
									</tr>

									<tr>
										<td>
											<div class="form-group">
												<div class="col-sm-6">
													<input type="button" class="btn btn-success" value="Purchase" v-on:click="savePurchase" v-bind:disabled="purchaseOnProgress == true ? true : false" style="background:#000;color:#fff;padding:3px;width:100%;">
												</div>
												<div class="col-sm-6">
													<input type="button" class="btn btn-info" onclick="window.location = '<?php echo base_url(); ?>purchase'" value="New Purch.." style="background:#000;color:#fff;padding:3px;width:100%;">
												</div>
											</div>
										</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
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
		el: '#purchase',
		data(){
			return{
				purchase: {
					purchaseId: parseInt('<?php echo $purchaseId;?>'),
					invoice: '<?php echo $invoice;?>',
					purchaseFor: '',
					purchaseDate: moment().format('YYYY-MM-DD'),
					supplierId: '',
					subTotal: 0.00,
					vat: 0.00,
					discount: 0.00,
					freight: 0.00,
					total: 0.00,
					paid: 0.00,
					due: 0.00,
					previousDue: 0.00,
					note: ''
				},
				vatPercent: 0.00,
				branches: [],
				selectedBranch: {
					brunch_id: "<?php echo $this->session->userdata('BRANCHid'); ?>",
					Brunch_name: "<?php echo $this->session->userdata('Brunch_name'); ?>"
				},
				suppliers: [],
				selectedSupplier: {
					Supplier_SlNo: null,
					Supplier_Code: '',
					Supplier_Name: '',
					display_name: 'Select Supplier',
					Supplier_Mobile: '',
					Supplier_Address: '',
					Supplier_Type: ''
				},
				oldSupplierId: null,
                oldPreviousDue: 0,
				products: [],
				selectedProduct: {
					Product_SlNo: '',
					Product_Code: '',
					display_text: 'Select Product',
					Product_Name: '',
					Unit_Name: '',
					quantity: '',
					Product_Purchase_Rate: '',
					Product_SellingPrice: 0.00,
					total: ''
				},
				colors: [],
				selectedColor: null,
				cart: [],
				purchaseOnProgress: false,
				userType: '<?php echo $this->session->userdata("accountType")?>',
				serialModalStatus: false,
				get_serial_number: "",
				is_sequence: false,
				serial_cart: '',
				SerialCartStore: '',
				warranty_month: 0
			}
		},
		created(){
			this.getBranches();
			this.getSuppliers();
			this.getProducts();
			this.getColors();

			if(this.purchase.purchaseId != 0){
				this.getPurchase();
			}
		},
		methods:{
			getColors(){
				axios.get('/get_colors').then(res=>{
					this.colors = res.data;
				})
			},
			serialShowModal() {
				this.serialModalStatus = true;
			},
			serialHideModal() {
				this.serialModalStatus = false;
			},
			async serial_add_action() {

				if (this.selectedProduct.Product_SlNo == '') {
					alert("Please select a product");
					return false;
				} else {
					if (this.selectedProduct.quantity <= 0 || this.selectedProduct.quantity == '' || this.selectedProduct.quantity == undefined) {
						alert("Please enter valid Quantity");
						return false;
					}

					let serial_num = this.get_serial_number.trim();

					let serial_arr = serial_num.split(",");

					if (this.selectedProduct.quantity != serial_arr.length) {
						alert(`Invalid Quantity !! Your quantity is ${this.selectedProduct.quantity}.`);
						this.get_serial_number = '';
						return false;
					}

					if (serial_num == '') {
						alert("Serial Number is Required.");
						return false;
					}

					if (this.serial_cart != '') {
						alert('Serial Number already exists in Serial List');
						return false;
					} else {

						this.serial_cart = serial_num;

						this.get_serial_number = '';
					}
				}

				this.$refs.serialnumberadd.focus();
			},
			async isSerialNumberUnique(serial_number) {
				let hasSerialNumber = false;

				await axios.post('/check_serial_number', {
					get_serial_number: serial_number
				}).then(res => {
					if (res.data > 0)
						hasSerialNumber = true;
				})

				return hasSerialNumber;
			},
			async remove_serial_item() {
				this.serial_cart = '';
			},
			async removeFromCart(ind, prod_id, product) {

				if ((this.purchase.purchaseId != 0)) {
					await axios.post('/check_soldSerial', {
						invoice: this.purchase.invoice,
						prodId: prod_id
					}).then(res => {
						if (res.data.trim() == 'no') {
							this.cart.splice(ind, 1);

							axios.post(`/delete_serials`, {
								product: product.SerialCartStore
							}).then(res => {

							})


							var newImeiCartStore = this.SerialCartStore.filter((el) => {
								return el.productId != prod_id;
							});
							this.SerialCartStore = newImeiCartStore;

						} else {
							alert('You have already some Serial Sold')
							return false;
						}
					});
				} else {
					this.cart.splice(ind, 1);

					var newImeiCartStore = this.SerialCartStore.filter((el) => {
						return el.productId != prod_id;
					});
					this.SerialCartStore = newImeiCartStore;
				}

				this.calculateTotal();
			},
			getBranches(){
				axios.get('/get_branches').then(res => {
					this.branches = res.data;
				})
			},
			getSuppliers(){
				axios.get('/get_suppliers').then(res => {
					this.suppliers = res.data;
					this.suppliers.unshift({
						Supplier_SlNo: 'S01',
						Supplier_Code: '',
						Supplier_Name: '',
						display_name: 'General Supplier',
						Supplier_Mobile: '',
						Supplier_Address: '',
						Supplier_Type: 'G'
					})
				})
			},
			getProducts(){
				axios.post('/get_products', {isService: 'false'}).then(res=>{
					this.products = res.data;
				})
			},
			onChangeSupplier(){
				if(this.selectedSupplier.Supplier_SlNo == null){
					return;
				}

				if(event.type == 'readystatechange'){
					return;
				}

                if(this.purchase.purchaseId != 0 && this.oldSupplierId != parseInt(this.selectedSupplier.Supplier_SlNo)){
					let changeConfirm = confirm('Changing supplier will set previous due to current due amount. Do you really want to change supplier?');
					if(changeConfirm == false){
						return;
					}
				} else if(this.purchase.purchaseId != 0 && this.oldSupplierId == parseInt(this.selectedSupplier.Supplier_SlNo)){
					this.purchase.previousDue = this.oldPreviousDue;
					return;
				}

				axios.post('/get_supplier_due', {supplierId: this.selectedSupplier.Supplier_SlNo}).then(res => {
					if(res.data.length > 0){
						this.purchase.previousDue = res.data[0].due;
					} else {
						this.purchase.previousDue = 0;
					}
				})

				this.calculateTotal();
			},
			onChangeProduct(){
				this.$refs.quantity.focus();
			},
			productTotal(){
				this.selectedProduct.total = this.selectedProduct.quantity * this.selectedProduct.Product_Purchase_Rate;
			},
			async addToCart(){

				if(this.selectedColor == null){
					alert('Select a color');
					return;
				}

				let cartInd = this.cart.findIndex(p => p.productId == this.selectedProduct.Product_SlNo && p.color_id == this.selectedColor.color_SiNo);
				if(cartInd > -1){
					alert('Product exists in cart');
					return;
				}

				let serial_find = false;

				let serial_array = this.serial_cart.split(",");
				serial_array = serial_array.map(Function.prototype.call, String.prototype.trim);

				serial_array.forEach((serial)=>{
					this.cart.forEach((product)=>{
						if(product['SerialCartStore'].includes(serial)){
							serial_find = true;
						}
					})
				})

				if(serial_find){
					alert('Serial exists in cart');
					return;
				}
				
				let check_serial = await axios.post('check_serial_on_purchase', {purchase_invoice : this.purchase.invoice, serials : serial_array}).then(res=>{
					return res;
				});

				if(check_serial.data == 1){
					alert('Serial number already found in database!');
					return;
				}

				let product = {
					productId: this.selectedProduct.Product_SlNo,
					name: this.selectedProduct.Product_Name,
					categoryId: this.selectedProduct.ProductCategory_ID,
					categoryName: this.selectedProduct.ProductCategory_Name,
					color_id: this.selectedColor.color_SiNo,
					color_name: this.selectedColor.color_name,
					purchaseRate: this.selectedProduct.Product_Purchase_Rate,
					salesRate: this.selectedProduct.Product_SellingPrice,
					quantity: this.selectedProduct.quantity,
					total: this.selectedProduct.total,
					// discount: this.selectedProduct.discount,
					warranty_month: this.warranty_month ?? 0,
					SerialCartStore: serial_array
				}


				this.cart.push(product);
				this.serial_cart = '';
				this.get_serial_number = '';
				this.clearSelectedProduct();
				this.calculateTotal();
			},
			async removeFromCart(ind){
				if(this.cart[ind].id) {
					let stock = await axios.post('/get_product_stock', { productId: this.cart[ind].productId }).then(res => res.data);
					if(this.cart[ind].quantity > stock) {
						alert('Stock unavailable');
						return;
					}
				}
				this.cart.splice(ind, 1);
				this.calculateTotal();
			},
			clearSelectedProduct(){
				this.selectedProduct = {
					Product_SlNo: '',
					Product_Code: '',
					display_text: 'Select Product',
					Product_Name: '',
					Unit_Name: '',
					quantity: '',
					Product_Purchase_Rate: '',
					Product_SellingPrice: 0.00,
					total: ''
				};
				this.warranty_month = 0;
				this.selectedColor = null;
			},
			calculateTotal(){
				this.purchase.subTotal = this.cart.reduce((prev, curr) => { return prev + parseFloat(curr.total); }, 0);
				this.purchase.vat = (this.purchase.subTotal * this.vatPercent) / 100;
				this.purchase.total = (parseFloat(this.purchase.subTotal) + parseFloat(this.purchase.vat) + parseFloat(this.purchase.freight)) - this.purchase.discount;
				if(this.selectedSupplier.Supplier_Type == 'G'){
					this.purchase.paid = this.purchase.total;
					this.purchase.due = 0;
				} else {
					if(event.target.id != 'paid') {
						this.purchase.paid = 0;
					}
						
					this.purchase.due = (parseFloat(this.purchase.total) - parseFloat(this.purchase.paid)).toFixed(2);
				}
			},
			savePurchase(){
				if(this.selectedSupplier.Supplier_SlNo == null){
					alert('Select supplier');
					return;
				}

				if(this.purchase.purchaseDate == ''){
					alert('Enter purchase date');
					return;
				}

				if(this.cart.length == 0){
					alert('Cart is empty');
					return;
				}

				this.purchase.supplierId = this.selectedSupplier.Supplier_SlNo;
				this.purchase.purchaseFor = this.selectedBranch.brunch_id;

				this.purchaseOnProgress = true;

				let data = {
					purchase: this.purchase,
					cartProducts: this.cart
				}

				if(this.selectedSupplier.Supplier_Type == 'G'){
					data.supplier = this.selectedSupplier;
				}

				let url = '/add_purchase';
				if(this.purchase.purchaseId != 0){
					url = '/update_purchase';
				}

				axios.post(url, data).then(async res => {
					let r = res.data;
					alert(r.message);
					if(r.success){
						let conf = confirm('Do you want to view invoice?');
						if(conf){
							window.open(`/purchase_invoice_print/${r.purchaseId}`, '_blank');
							await new Promise(r => setTimeout(r, 1000));
							window.location = '/purchase';
						} else {
							window.location = '/purchase';
						}
					}
				})
			},
			getPurchase(){
				axios.post('/get_purchases', {purchaseId: this.purchase.purchaseId}).then(res => {
					let r = res.data;
					let purchase = r.purchases[0];

					this.selectedSupplier.Supplier_SlNo = purchase.Supplier_SlNo;
					this.selectedSupplier.Supplier_Code = purchase.Supplier_Code;
					this.selectedSupplier.Supplier_Name = purchase.Supplier_Name;
					this.selectedSupplier.Supplier_Mobile = purchase.Supplier_Mobile;
					this.selectedSupplier.Supplier_Address = purchase.Supplier_Address;
					this.selectedSupplier.Supplier_Type = purchase.Supplier_Type;
					this.selectedSupplier.display_name = purchase.Supplier_Type == 'G' ? 'General Supplier' : `${purchase.Supplier_Code} - ${purchase.Supplier_Name}`;
					
					this.purchase.invoice = purchase.PurchaseMaster_InvoiceNo;
					this.purchase.purchaseFor = purchase.PurchaseMaster_PurchaseFor;
					this.purchase.purchaseDate = purchase.PurchaseMaster_OrderDate;
					this.purchase.supplierId = purchase.Supplier_SlNo;
					this.purchase.subTotal = purchase.PurchaseMaster_SubTotalAmount;
					this.purchase.vat = purchase.PurchaseMaster_Tax;
					this.purchase.discount = purchase.PurchaseMaster_DiscountAmount;
					this.purchase.freight = purchase.PurchaseMaster_Freight;
					this.purchase.total = purchase.PurchaseMaster_TotalAmount;
					this.purchase.paid = purchase.PurchaseMaster_PaidAmount;
					this.purchase.due = purchase.PurchaseMaster_DueAmount;
					this.purchase.previousDue = purchase.previous_due;
					this.purchase.note = purchase.PurchaseMaster_Description;

					this.oldSupplierId = purchase.Supplier_SlNo;
                	this.oldPreviousDue = purchase.previous_due;

					this.vatPercent = (this.purchase.vat * 100) / this.purchase.subTotal;

					r.purchaseDetails.forEach(product => {
						let cartProduct = {
							id: product.PurchaseDetails_SlNo,
							productId: product.Product_IDNo,
							name: product.Product_Name,
							categoryId: product.ProductCategory_ID,
							categoryName: product.ProductCategory_Name,
							purchaseRate: product.PurchaseDetails_Rate,
							salesRate: product.Product_SellingPrice,
							quantity: product.PurchaseDetails_TotalQuantity,
							total: product.PurchaseDetails_TotalAmount,
							color_id: product.color_id,
							color_name: product.color_name,
							warranty_month: product.warranty_month,
							SerialCartStore: ''
						}

						let serial_cart_str = '';

						let total_serial = product.serial.length;
						let i = 0;

						product.serial.forEach((obj) => {
							serial_cart_str += obj.ps_serial_number;

							if (i < (total_serial - 1)) {
								serial_cart_str += ', ';
							}
							i++;
						})

						cartProduct.SerialCartStore = serial_cart_str;

						this.cart.push(cartProduct);
					})
				})
			}
		}
	})
</script>