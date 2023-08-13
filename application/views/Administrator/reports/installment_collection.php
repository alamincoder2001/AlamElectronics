<style>
	.v-select{
		margin-bottom: 5px;
	}
	.v-select.open .dropdown-toggle{
		border-bottom: 1px solid #ccc;
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
	#customerPayment label{
		font-size:13px;
	}
	#customerPayment select{
		border-radius: 3px;
		padding: 0;
	}
	#customerPayment .add-button{
		padding: 2.5px;
		width: 28px;
		background-color: #298db4;
		display:block;
		text-align: center;
		color: white;
	}
	#customerPayment .add-button:hover{
		background-color: #41add6;
		color: white;
	}
</style>
<div id="customerPayment">
	<div class="row" style="border-bottom: 1px solid #ccc;padding-bottom: 15px;margin-bottom: 15px;">
		<div class="col-md-12">
			<form @submit.prevent="saveCustomerPayment">
				<div class="row">
					<div class="col-md-5 col-md-offset-1">
						<div class="form-group">
							<label class="col-md-4 control-label">Payment Type</label>
							<label class="col-md-1">:</label>
							<div class="col-md-7">
								<select class="form-control" v-model="payment.CPayment_Paymentby" required>
									<option value="cash">Cash</option>
									<option value="bank">Bank</option>
								</select>
							</div>
						</div>
						<div class="form-group" style="display:none;" v-bind:style="{display: payment.CPayment_Paymentby == 'bank' ? '' : 'none'}">
							<label class="col-md-4 control-label">Bank Account</label>
							<label class="col-md-1">:</label>
							<div class="col-md-7">
								<v-select v-bind:options="filteredAccounts" v-model="selectedAccount" label="display_text" placeholder="Select account"></v-select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-4 control-label">Customer</label>
							<label class="col-md-1">:</label>
							<div class="col-md-7">
								<select class="form-control" v-if="customers.length == 0"></select>
								<v-select v-bind:options="customers" v-model="selectedCustomer" label="display_name" @input="getCustomerInstallment" v-if="customers.length > 0"></v-select>
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">Installment</label>
							<label class="col-md-1">:</label>
							<div class="col-md-7">
								<select class="form-control" v-if="installments.length == 0"></select>
								<v-select v-bind:options="installments" v-model="payment" label="display_text" v-if="installments.length > 0" @input="changeInstallment"></v-select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-4 control-label">Due</label>
							<label class="col-md-1">:</label>
							<div class="col-md-3">
								<input type="text" class="form-control" v-model="payment.due" readonly>
							</div>
							<div class="col-md-4">
								<input type="text" class="form-control" v-model="customerDue" readonly>
							</div>
						</div>
					</div>

					<div class="col-md-5">
						<div class="form-group">
							<label class="col-md-4 control-label">Payment Date</label>
							<label class="col-md-1">:</label>
							<div class="col-md-7">
								<input type="date" class="form-control" v-model="payment.CPayment_date" required  v-bind:disabled="userType == 'u' ? true : false">
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-4 control-label">Description</label>
							<label class="col-md-1">:</label>
							<div class="col-md-7">
								<input type="text" class="form-control" v-model="payment.CPayment_notes">
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-4 control-label">Amount</label>
							<label class="col-md-1">:</label>
							<div class="col-md-7">
								<input type="number" step="0.01" class="form-control" v-model="payment.paid" required>
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">Discount</label>
							<label class="col-md-1">:</label>
							<div class="col-md-7">
								<input type="number" step="0.01" class="form-control" v-model="payment.discount" required>
							</div>
						</div>
						
						<div class="form-group">
							<div class="col-md-7 col-md-offset-5">
								<input type="submit" class="btn btn-success btn-sm" value="Save" :disabled="paymentOnProgress">
								<input type="button" class="btn btn-danger btn-sm" value="Cancel" @click="resetForm">
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>

<!-- <div class="row">
		<div class="col-sm-12 form-inline">
			<div class="form-group">
				<label for="filter" class="sr-only">Filter</label>
				<input type="text" class="form-control" v-model="filter" placeholder="Filter">
			</div>
		</div>
		<div class="col-md-12">
			<div class="table-responsive">
				<datatable :columns="columns" :data="installments" :filter-by="filter" style="margin-bottom: 5px;">
					<template scope="{ row }">
						<tr>
							<td>{{ row.CPayment_invoice }}</td>
							<td>{{ row.CPayment_date }}</td>
							<td>{{ row.Customer_Name }}</td>
							<td>{{ row.Customer_Mobile }}</td>
							<td>{{ row.Customer_Address }}</td>
							<td>{{ row.CPayment_amount }}</td>
							<td>{{ row.CPayment_Addby }}</td>
							<td>
								
								<?php if($this->session->userdata('accountType') != 'u'){?>
                                    <button type="button" class="button" @click="updatePayment(row.CPayment_id)">
                                        Paid
                                    </button>
								<?php }?>
							</td>
						</tr>
					</template>
				</datatable>
				<datatable-pager v-model="page" type="abbreviated" :per-page="per_page" style="margin-bottom: 50px;"></datatable-pager>
			</div>
		</div>
	</div> -->
</div>

<script src="<?php echo base_url();?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/vuejs-datatable.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/vue-select.min.js"></script>
<script src="<?php echo base_url();?>assets/js/moment.min.js"></script>

<script>
	Vue.component('v-select', VueSelect.VueSelect);
	new Vue({
		el: '#customerPayment',
		data(){
			return {
				payment: {
					CPayment_id: 0,
					CPayment_customerID: null,
					CPayment_TransactionType: 'CR',
					CPayment_Paymentby: 'cash',
					account_id: null,
					CPayment_date: moment().format('YYYY-MM-DD'),
					CPayment_amount: '',
					CPayment_notes: '',
					CPayment_previous_due: 0,
					due : 0,
					paid: 0,
					discount: 0,
				},
				payments: [],
				customerDue: 0,
				customers: [],
                installments: [],
				selectedCustomer: {
					display_name: 'Select Customer',
					Customer_Name: ''
				},
				accounts: [],
                selectedAccount: null,
				userType: '<?php echo $this->session->userdata("accountType");?>',
				
				columns: [
                    { label: 'Transaction Id', field: 'CPayment_invoice', align: 'center' },
                    { label: 'Installment Date', field: 'CPayment_date', align: 'center' },
                    { label: 'Customer Name', field: 'Customer_Name', align: 'center' },
                    { label: 'Mobile', field: 'Customer_Mobile', align: 'center' },
                    { label: 'Address', field: 'Customer_Address', align: 'center' },
                    { label: 'Installment Amount', field: 'CPayment_amount', align: 'center' },
                    { label: 'Saved By', field: 'CPayment_Addby', align: 'center' },
                    { label: 'Action', align: 'center', filterable: false }
                ],
                page: 1,
                per_page: 10,
                filter: '',
				paymentOnProgress: false,
			}
		},
		computed: {
            filteredAccounts(){
                let accounts = this.accounts.filter(account => account.status == '1');
                return accounts.map(account => {
                    account.display_text = `${account.account_name} - ${account.account_number} (${account.bank_name})`;
                    return account;
                })
            },
        },
		created(){
			this.getCustomers();
			this.getAccounts();
			this.payment.CPayment_notes = this.payment.CPayment_Paymentby;
		},
		methods:{
			getCustomers(){
				axios.get('/get_customers').then(res => {
					this.customers = res.data;
				})
			},
			async getCustomerInstallment(){
				if(event.type == "click"){
					return;
				}
				if(this.selectedCustomer == null || this.selectedCustomer.Customer_SlNo == undefined){
					return;
				}

				await axios.post('/get_customer_due', {customerId: this.selectedCustomer.Customer_SlNo}).then(res => {
					this.customerDue = res.data[0].dueAmount;
				})

                await axios.post('/get_installment_due', {customerId: this.selectedCustomer.Customer_SlNo})
                .then(res => {
                    this.installments = res.data;
                })
			},
			getAccounts(){
                axios.get('/get_bank_accounts')
                .then(res => {
                    this.accounts = res.data;
                })
            },
            changeInstallment(){
            	this.payment.CPayment_previous_due = this.customerDue;
            	this.payment.paid = this.payment.due;
            	this.payment.CPayment_Paymentby = 'cash';
				this.payment.CPayment_date = moment().format('YYYY-MM-DD')
            },
            async saveCustomerPayment(){
            	if(this.selectedCustomer == null || this.selectedCustomer.Customer_SlNo == undefined){
            		alert('Please Select Customer');
					return;
				}

				if(this.payment == null || this.payment.CPayment_id == undefined){
            		alert('Please Select Invoice');
					return;
				}

				if(parseFloat(this.payment.due) < parseFloat(this.payment.paid)){
            		alert('Payment amount must not more than due.');
					return;
				}

				let deleteConfirm = confirm('Are you sure?');
				if(deleteConfirm == false){
					return;
				}

				if(this.selectedAccount != null) {
					this.payment.account_id = this.selectedAccount.account_id
				}

				this.paymentOnProgress = true;

				axios.post('/update_installment_payment', this.payment).then(async res => {
					let r = res.data;

					if(r.success){
						this.resetForm();
						let conf = confirm('Installment Save success, Do you want to view Receipt?');
						if(conf){
							window.open('/installment_collection_print/'+ r.collectionId, '_blank');
							await new Promise(r => setTimeout(r, 1000));
							window.location = '/installment_collection';
						} else {
							// window.location = this.sales.isService == 'false' ? '/sales/product' : '/sales/service';
						}
					} else {
						alert(r.message);
					}
					this.paymentOnProgress = false;

					// if(r.success){
					// 	this.resetForm();
					// }
				});
            },
			// async updatePayment(paymentId){
			// 	let deleteConfirm = confirm('Are you sure?');
			// 	if(deleteConfirm == false){
			// 		return;
			// 	}

   //              this.payment.CPayment_id = paymentId;
				
			// 	if(this.selectedAccount != null) {
			// 		this.payment.account_id = this.selectedAccount.account_id
			// 	}

			// 	axios.post('/update_installment_payment', this.payment).then(res => {
			// 		let r = res.data;
			// 		alert(r.message);
			// 		if(r.success){
			// 			this.getCustomerInstallment();
			// 			this.resetForm();
			// 		}
			// 	})
			// },
			resetForm(){
				this.payment = {
					CPayment_id: 0,
					CPayment_customerID: null,
					CPayment_TransactionType: 'CR',
					CPayment_Paymentby: 'cash',
					account_id: null,
					CPayment_date: moment().format('YYYY-MM-DD'),
					CPayment_amount: '',
					CPayment_notes: '',
					CPayment_previous_due: 0,
					due : 0,
					paid: 0
				};

				this.selectedCustomer = {
					display_name: 'Select Customer',
					Customer_Name: ''
				};

				this.customerDue = 0;
			}
		}
	})
</script>