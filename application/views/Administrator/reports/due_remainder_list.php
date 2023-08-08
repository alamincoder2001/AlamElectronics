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
        height: 188px !important;
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

</style>
<div id="customerListReport">

    <div style="display:none" id="serial-modal" v-bind:style="{display:dueModalStatus?'block':'none'}">
        <transition name="modal">
            <div class="modal-mask">
                <div class="modal-wrapper">
                    <div class="modal-container">
                        <div class="modal-header">
                            <slot name="header">
                                <h3>Due Remainder Date Update</h3>
                            </slot>
                        </div>

                        <div class="modal-body">
                            <slot name="body">
                                <div class="form-group">
                                    <div class="col-sm-12" style="display: flex; margin-bottom: 5px;">
                                        <input type="date" v-model="selectedCustomer.CPayment_date" autocomplete="off" ref="due_date" id="due_date" name="due_date" class="form-control" style="height: 30px;" />
                                    </div>
                                    <div class="col-sm-12" style="display: flex; margin-bottom: 5px;">
                                       <textarea v-model="reason" id="" placeholder="Write Reason" class="form-control" rows="4"></textarea>
                                    </div>
                                </div>
                            </slot>
                        </div>
                        <div class="modal-footer">
                            <slot name="footer">
                                <input type="button" @click="dueUpdate" class="btn btn-sm btn primary" style="border: none; font-size: 20px; line-height: 0.38; background-color: #42b983 !important; padding: 0 15px; height: 29px; margin-right: 10px;" value="Add">
                                <button class="modal-default-button" @click="dueHideModal" style="background: rgb(255, 255, 255);border: none;font-size: 18px;color: #de0000;">
                                    Close
                                </button>
                            </slot>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </div>
    
    <div style="display:none" v-bind:style="{display:reasonModalStatus?'block':'none'}">
        <transition name="modal">
            <div class="modal-mask">
                <div class="modal-wrapper">
                    <div class="modal-container">
                        <div class="modal-header">
                            <slot name="header">
                                <h3>Reasons</h3>
                            </slot>
                        </div>

                        <div class="modal-body">
                            <slot name="body">
                                <table class="table table-bordered table-condensed">
                                    <thead>
                                        <th>Sl</th>
                                        <th>Date</th>
                                        <th>Reason</th>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(r, sl) in reasons">
                                            <td>{{sl+1}}</td>
                                            <td>{{r.created_at}}</td>
                                            <td>{{r.reason}}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </slot>
                        </div>
                        <div class="modal-footer">
                            <slot name="footer">
                                <button class="modal-default-button" @click="closeReason" style="background: rgb(255, 255, 255);border: none;font-size: 18px;color: #de0000;">
                                    Close
                                </button>
                            </slot>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </div>


    <div class="row">
        <div class="col-md-12">
            <form class="form-inline">
                <div class="form-group">
                    <v-select v-bind:options="cus" v-model="selectedCus" label="display_name" placeholder="Select Customer" @input="getDueRemainder"></v-select>
                </div>
            </form>
        </div>
    </div>
    <div style="display:none;" v-bind:style="{display: customers.length > 0 ? '' : 'none'}">
        <div class="row">
            <div class="col-md-12">
                <a href="" @click.prevent="printCustomerDueRemainder"><i class="fa fa-print"></i> Print</a>
            </div>
        </div>

        <div class="row" style="margin-top:15px;">
            <div class="col-md-12">
                <div class="table-responsive" id="printContent">
                    <table class="table table-bordered table-condensed">
                        <thead>
                            <th>Sl</th>
                            <th>Date</th>
                            <th>Transaction</th>
                            <th>Customer Id</th>
                            <th>Customer Name</th>
                            <th>Address</th>
                            <th>Contact No.</th>
                            <th>Reason</th>
                            <th>Paid</th>
                            <th>Discount</th>
                            <th>Due Amount</th>
                            <th>Action</th>
                        </thead>
                        <tbody>
                            <tr v-for="(customer, sl) in customers">
                                <td>{{ sl + 1 }}</td>
                                <td>{{ customer.CPayment_date }}</td>
                                <td>{{ customer.CPayment_invoice }}</td>
                                <td>{{ customer.Customer_Code }}</td>
                                <td>{{ customer.Customer_Name }}</td>
                                <td>{{ customer.Customer_Address }} {{ customer.District_Name }}</td>
                                <td>{{ customer.Customer_Mobile }}</td>
                                <td><i style="cursor: pointer;" v-if="customer.reasons.length > 0" class="fa fa-eye" aria-hidden="true" @click="viewReason(customer.reasons)"></i></td>
                                <td style="text-align:right">{{ customer.previous_paid }}</td>
                                <td style="text-align:right">{{ customer.previous_discount }}</td>
                                <td style="text-align:right">{{ customer.due }}</td>
                                <td><a href="javascript:void(0);" title="Edit Due Date" @click="dueShowModal(customer)"><i class="fa fa-edit"></i></a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div style="display:none;text-align:center;" v-bind:style="{display: customers.length > 0 ? 'none' : ''}">
        No records found
    </div>
</div>

<script src="<?php echo base_url(); ?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/vue-select.min.js"></script>

<script>
    Vue.component('v-select', VueSelect.VueSelect);
    new Vue({
        el: '#customerListReport',
        data() {
            return {
                cus: [],
                selectedCus: null,
                customers: [],
                dueModalStatus: false,
                reasonModalStatus: false,
                selectedCustomer: {
                    CPayment_date : '',
                    reason : '',
                },
                reason: '',
                reasons : [],
            }
        },
        created() {
            this.getCustomers();
            this.getDueRemainder();
        },
        methods: {
            viewReason(reasons){
                this.reasons = reasons;
                this.reasonModalStatus = true;
            },
            closeReason(){
                this.reasonModalStatus = false;
                this.reasons = [];
            },
            getCustomers(){
                axios.get('/get_customers').then(res=>{
                    this.cus = res.data;
                })
            },
            async getDueRemainder() {
                let customer_id = '';
                if(this.selectedCus != null){
                    customer_id = this.selectedCus.Customer_SlNo;
                }
                await axios.post('/get_due_remainder', {customer_id}).then(res => {
                    this.customers = res.data;
                })
            },
            dueShowModal(customer) {
                this.dueModalStatus = true;
                this.selectedCustomer = customer;
                this.reason = '';
            },
            async dueHideModal() {
                await this.getDueRemainder();
                this.dueModalStatus = false;
                this.selectedCustomer = {
                    CPayment_date : '',
                };
                this.reason = '';
            },
            async dueUpdate(){

                if (this.selectedCustomer.CPayment_id == null) {
                    alert('Invalid Request!')
                    return;
                }

                if(this.reason == ''){
                    alert('Reason Required!');
                    return;
                }

                let customer = {
                    payment_id: this.selectedCustomer.CPayment_id,
                    payment_date: this.selectedCustomer.CPayment_date,
                    reason: this.reason
                }

                await axios.post('/due_remainder_update', {customer}).then( async res => {
                    let r = res.data;
                    alert(r.message);
                    if (r.success) {
                        await this.dueHideModal();
                    }
                    
                });

                

            },
            async printCustomerDueRemainder() {
                let printContent = `
                    <div class="container">
                        <h4 style="text-align:center">Customer Remainder Due List</h4 style="text-align:center">
						<div class="row">
							<div class="col-xs-12">
								${document.querySelector('#printContent').innerHTML}
							</div>
						</div>
                    </div>
                `;

                let printWindow = window.open('', '', `width=${screen.width}, height=${screen.height}`);
                printWindow.document.write(`
                    <?php $this->load->view('Administrator/reports/reportHeader.php'); ?>
                `);

                printWindow.document.body.innerHTML += printContent;
                printWindow.focus();
                await new Promise(r => setTimeout(r, 1000));
                printWindow.print();
                printWindow.close();
            }
        }
    })
</script>