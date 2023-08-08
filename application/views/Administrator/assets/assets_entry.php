<style>
    #assetsTable .button{
        width: 25px;
        height: 25px;
        border: none;
        color: white;
    }
    #assetsTable .edit{
        background-color: #7bb1e0;
    }
    #assetsTable .delete{
        background-color: #ff6666;
    }
</style>

<div id="assets">
	<form id="assetForm" class="form-horizontal" @submit.prevent="saveAsset">
		<div class="row">
			<div class="col-md-5 col-md-offset-1">
				<div class="form-group clearfix">
					<label class="col-md-4">Invoice No:</label>
					<div class="col-md-7">
						<input type="text" class="form-control" v-model="asset.invoice_no">
					</div>
				</div>
				<div class="form-group clearfix">
					<label class="col-md-4">Work Order No:</label>
					<div class="col-md-7">
						<input type="text" class="form-control" v-model="asset.work_order_no">
					</div>
				</div>
				<div class="form-group clearfix">
					<label class="col-md-4">Date:</label>
					<div class="col-md-7">
						<input type="date" class="form-control" v-model="asset.purchase_date">
					</div>
				</div>
				<div class="form-group clearfix">
					<label class="col-md-4">Supplier Name:</label>
					<div class="col-md-7">
						<input type="text" class="form-control" v-model="asset.supplier_name">
					</div>
				</div>
				<div class="form-group clearfix">
					<label class="col-md-4">Phone:</label>
					<div class="col-md-7">
						<input type="text" class="form-control" v-model="asset.supplier_phone">
					</div>
				</div>
				<div class="form-group clearfix">
					<label class="col-md-4">Address:</label>
					<div class="col-md-7">
						<input type="text" class="form-control" v-model="asset.supplier_address">
					</div>
				</div>
			</div>
			<div class="col-md-5">
				<div class="form-group clearfix">
					<label class="col-md-4">Asset Name:</label>
					<div class="col-md-7">
						<input type="text" class="form-control" v-model="asset.as_name">
					</div>
				</div>
				<div class="form-group clearfix">
					<label class="col-md-4">Quantity:</label>
					<div class="col-md-7">
						<input type="text" class="form-control" v-model="asset.as_qty" @input="calculateAmount">
					</div>
				</div>
				<div class="form-group clearfix">
					<label class="col-md-4">Rate:</label>
					<div class="col-md-7">
						<input type="text" class="form-control" v-model="asset.as_rate" @input="calculateAmount">
					</div>
				</div>
				<div class="form-group clearfix">
					<label class="col-md-4">Amount:</label>
					<div class="col-md-7">
						<input type="text" class="form-control" v-model="asset.as_amount">
					</div>
				</div>
				<div class="form-group clearfix">
					<div class="col-md-11 text-right">
						<input type="submit" class="btn btn-success" value="Save">
					</div>
				</div>
			</div>
		</div>
	</form>
    
    <div class="widget-box">
        <div class="widget-header">
            <h4 class="widget-title">Asset List</h4>
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
                    <div class="col-md-4">
                        <label for="filter" class="sr-only">Filter</label>
                        <input type="text" class="form-control" v-model="filter" placeholder="Filter">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div id="assetsTable" class="table-responsive">
                            <datatable :columns="columns" :data="assets" :filter-by="filter">
                                <template scope="{ row }">
                                    <tr>
                                        <td>{{ row.invoice_no }}</td>
                                        <td>{{ row.work_order_no }}</td>
                                        <td>{{ row.purchase_date }}</td>
                                        <td>{{ row.supplier_name }}</td>
                                        <td>{{ row.supplier_phone }}</td>
                                        <td>{{ row.supplier_address }}</td>
                                        <td>{{ row.as_name }}</td>
                                        <td>{{ row.as_qty }}</td>
                                        <td>{{ row.as_rate }}</td>
                                        <td>{{ row.as_amount }}</td>
                                        <td>
                                            <?php if($this->session->userdata('assetType') != 'u'){?>
                                            <button class="button edit" @click="editAsset(row)">
                                                <i class="fa fa-pencil"></i>
                                            </button>
                                            <button class="button delete" @click="deleteAsset(row.as_id)">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                            <?php }?>
                                        </td>
                                    </tr>
                                </template>
                            </datatable>
                            <datatable-pager v-model="page" type="abbreviated" :per-page="per_page"></datatable-pager>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo base_url();?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url();?>assets/js/moment.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/vuejs-datatable.js"></script>

<script>
    new Vue({
        el: '#assets',
        data(){
            return {
                asset: {
                    as_id: 0,
                    invoice_no: '',
					work_order_no: '',
					purchase_date: moment().format('YYYY-MM-DD'),
					supplier_name: '',
					supplier_phone: '',
					supplier_address: '',
					as_name: '',
					as_qty: '',
					as_rate: '',
					as_amount: ''
                },
                assets: [],

                columns: [
                    { label: 'Invoice No', field: 'invoice_no', align: 'center' },
                    { label: 'Work Order No', field: 'work_order_no', align: 'center' },
                    { label: 'Purchase Date', field: 'purchase_date', align: 'center' },
                    { label: 'Supplier Name', field: 'supplier_name', align: 'center' },
                    { label: 'Phone', field: 'supplier_phone', align: 'center' },
                    { label: 'Address', field: 'supplier_address', align: 'center' },
                    { label: 'Asset Name', field: 'as_name', align: 'center' },
                    { label: 'Quantity', field: 'as_qty', align: 'center' },
                    { label: 'Rate', field: 'as_rate', align: 'center' },
                    { label: 'Amount', field: 'as_amount', align: 'center' },
                    { label: 'Action', align: 'center', filterable: false }
                ],
                page: 1,
                per_page: 10,
                filter: ''
            }
        },

        created() {
            this.getAssets();
        },

        methods: {
            calculateAmount() {
                this.asset.as_amount = this.asset.as_qty * this.asset.as_rate;
            },

            getAssets() {
                axios.get('/get_assets').then(res => {
                    this.assets = res.data;
                })
            },

            saveAsset() {
                let url = '/add_asset';
                if(this.asset.as_id != 0) {
                    url = '/update_asset';
                }

                axios.post(url, this.asset).then(res => {
                    let r = res.data;
                    alert(r.message);
                    if(r.success) {
                        this.resetForm();
                        this.getAssets();
                    }
                }).catch(error => {
                    if(error.response){
                        alert(`${error.response.status}, ${error.response.statusText}`);
                    }
                })
            },

            editAsset(asset) {
                Object.keys(this.asset).forEach(key => {
                    this.asset[key] = asset[key];
                })
            },

            deleteAsset(assetId) {
				let confirmation = confirm("Are you sure?");
				if(confirmation == false){
					return;
				}
				
                axios.post('/delete_asset', {assetId: assetId})
				.then(res => {
					let r = res.data;
                    alert(r.message);
                    if(r.success){
                        this.getAssets();
                    }
                })
                .catch(error => {
                    if(error.response){
                        alert(`${error.response.status}, ${error.response.statusText}`);
                    }
                })
            },

            resetForm() {
                this.asset.as_name = '';
				this.asset.as_qty = '';
				this.asset.as_rate = '';
				this.asset.as_amount = '';
            }
        }
    })
</script>