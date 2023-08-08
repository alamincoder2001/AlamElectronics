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
	.record-table{
		width: 100%;
		border-collapse: collapse;
	}
	.record-table thead{
		background-color: #0097df;
		color:white;
	}
	.record-table th, .record-table td{
		padding: 3px;
		border: 1px solid #454545;
	}
    .record-table th{
        text-align: center;
    }
</style>
<div id="salesRecord">
	<div class="row" style="border-bottom: 1px solid #ccc;padding: 3px 0;">
		<div class="col-md-12">
			<form class="form-inline" id="searchForm" @submit.prevent="getSearchResult">
				<div class="form-group">
					<label>Record Type</label>
					<select class="form-control" v-model="recordType" @change="services = []">
						<option value="without_details">Without Details</option>
						<option value="with_details">With Details</option>
					</select>
				</div>

				<div class="form-group">
					<input type="date" class="form-control" v-model="dateFrom">
				</div>

				<div class="form-group">
					<input type="date" class="form-control" v-model="dateTo">
				</div>

				<div class="form-group" style="margin-top: -5px;">
					<input type="submit" value="Search">
				</div>
			</form>
		</div>
	</div>

	<div class="row" style="margin-top:15px;display:none;" v-bind:style="{display: services.length > 0 ? '' : 'none'}">
		<div class="col-md-12" style="margin-bottom: 10px;">
			<a href="" @click.prevent="print"><i class="fa fa-print"></i> Print</a>
		</div>
		<div class="col-md-12">
			<div class="table-responsive" id="reportContent">
				<table 
					class="record-table" 
					v-if="recordType == 'without_details'" 
					style="display:none" 
					v-bind:style="{display: recordType == 'without_details' ? '' : 'none'}"
				>
					<thead>
						<tr>
							<th>Invoice No.</th>
							<th>Date</th>
							<th>Customer Name</th>
							<th>Quantity</th>
							<th>Total</th>
							<th>Paid</th>
							<th>Due</th>
							<th>Saved By</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
                        <tr v-for="service in services">
                            <td>{{ service.invoice }}</td>
                            <td>{{ service.date }}</td>
                            <td>{{ service.customer_name }}</td>
                            <td>{{ service.quantity }}</td>
                            <td>{{ service.total }}</td>
                            <td>{{ service.paid }}</td>
                            <td>{{ service.due }}</td>
                            <td>{{ service.added_by }}</td>
                            <td class="text-center">
                                <?php if($this->session->userdata('accountType') != 'u'){?>
								<a href="" title="Edit Service" v-bind:href="`/service/${service.id}`"><i class="fa fa-edit"></i></a>
								<a href="" title="Delete Service" @click.prevent="deleteService(service.id)"><i class="fa fa-trash"></i></a>
								<?php }?>
                            </td>
                        </tr>
					</tbody>
				</table>
				<table 
					class="record-table" 
					v-if="recordType == 'with_details'" 
					style="display:none" 
					v-bind:style="{display: recordType == 'with_details' ? '' : 'none'}"
				>
					<thead>
						<tr>
							<th>Invoice No.</th>
							<th>Date</th>
							<th>Customer Name</th>
							<th>Product Name</th>
							<th>Model</th>
							<th>Imei</th>
							<th>Quantity</th>
							<th>Saved By</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						<template v-for="service in services">
							<tr>
								<td>{{ service.invoice }}</td>
								<td>{{ service.date }}</td>
								<td>{{ service.customer_name }}</td>
								<td>{{ service.serviceDetails[0].product_name }}</td>
								<td>{{ service.serviceDetails[0].model }}</td>
								<td>{{ service.serviceDetails[0].imei }}</td>
								<td>{{ service.serviceDetails[0].quantity }}</td>
								<td>{{ service.added_by }}</td>
								<td class="text-center">
									<?php if($this->session->userdata('accountType') != 'u'){?>
									<a href="" title="Edit Service" v-bind:href="`/service/${service.id}`"><i class="fa fa-edit"></i></a>
									<a href="" title="Delete Service" @click.prevent="deleteService(service.id)"><i class="fa fa-trash"></i></a>
									<?php }?>
								</td>
							</tr>
							<tr v-for="product in service.serviceDetails.slice(1)">
								<td colspan="3"></td>
								<td>{{ product.product_name }}</td>
								<td>{{ product.model }}</td>
								<td>{{ product.imei }}</td>
								<td>{{ product.quantity }}</td>
								<td></td>
								<td></td>
							</tr>
						</template>
					</tbody>
				</table>


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
		el: '#salesRecord',
		data(){
			return {
				recordType: 'without_details',
				dateFrom: moment().format('YYYY-MM-DD'),
				dateTo: moment().format('YYYY-MM-DD'),
                services: []
			}
		},
		methods: {
			getSearchResult(){
				let filter = {
					dateFrom: this.dateFrom,
					dateTo: this.dateTo
				}

				let url = '/get_service';
				if(this.recordType == 'with_details'){
					url = '/get_service_record';
				}

				axios.post(url, filter)
				.then(res => {
					if(this.recordType == 'with_details'){
						this.services = res.data;
					} else {
						this.services = res.data.services;
					}
				})
				.catch(error => {
					if(error.response){
						alert(`${error.response.status}, ${error.response.statusText}`);
					}
				})
			},
            deleteService(id) {
                if(confirm('Are you sure?')) {
                    axios.post('/delete_service', { id: id })
                    .then(res => {
                        alert(res.data.message)
                        if(res.data.success) {
                            this.getSearchResult();
                        }
                    })
                }
            },
			async print(){
				var reportWindow = window.open('', 'PRINT', `height=${screen.height}, width=${screen.width}`);
				reportWindow.document.write(`
					<?php $this->load->view('Administrator/reports/reportHeader.php');?>
				`);

				reportWindow.document.head.innerHTML += `
					<style>
						.record-table{
							width: 100%;
							border-collapse: collapse;
						}
						.record-table thead{
							background-color: #0097df;
							color:white;
						}
						.record-table th, .record-table td{
							padding: 3px;
							border: 1px solid #454545;
						}
						.record-table th{
							text-align: center;
						}
					</style>
				`;
				reportWindow.document.body.innerHTML += reportContent;

				if(this.searchType == '' || this.searchType == 'user'){
					let rows = reportWindow.document.querySelectorAll('.record-table tr');
					rows.forEach(row => {
						row.lastChild.remove();
					})
				}


				reportWindow.focus();
				await new Promise(resolve => setTimeout(resolve, 1000));
				reportWindow.print();
				reportWindow.close();
			}
		}
	})
</script>