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
	<iframe id="txtArea1" style="display:none"></iframe>
	<div class="row" style="border-bottom: 1px solid #ccc;padding: 3px 0;">
		<div class="col-md-12">
			<form class="form-inline" id="searchForm" @submit.prevent="getSearchResult">
				<div class="form-group">
					<label>Search Type</label>
					<select class="form-control" v-model="searchType" @change="onChangeSearchType">
						<option value="">All</option>
						<option value="customer">By Customer</option>
						<option value="employee">By Employee</option>
						<option value="user">By User</option>
					</select>
				</div>

				<div class="form-group" style="display:none;" v-bind:style="{display: searchType == 'customer' && customers.length > 0 ? '' : 'none'}">
					<label>Customer</label>
					<v-select v-bind:options="customers" v-model="selectedCustomer" label="display_name"></v-select>
				</div>

				<div class="form-group" style="display:none;" v-bind:style="{display: searchType == 'employee' && employees.length > 0 ? '' : 'none'}">
					<label>Employee</label>
					<v-select v-bind:options="employees" v-model="selectedEmployee" label="Employee_Name"></v-select>
				</div>

				<div class="form-group" style="display:none;" v-bind:style="{display: searchType == 'user' && users.length > 0 ? '' : 'none'}">
					<label>User</label>
					<v-select v-bind:options="users" v-model="selectedUser" label="FullName"></v-select>
				</div>

				<div class="form-group">
					<input type="month" class="form-control" v-model="month">
				</div>

				<div class="form-group" style="margin-top: -5px;">
					<input type="submit" value="Search">
				</div>
			</form>
		</div>
	</div>

	<div class="row" style="margin-top:15px;display:none;" v-bind:style="{display: sales.length > 0 ? '' : 'none'}">
		<div class="col-md-12" style="margin-bottom: 10px;">
			<a href="" @click.prevent="print"><i class="fa fa-print"></i> Print</a>
			<button style="float: right;" id="btnExport" onclick="fnExcelReport();"> EXPORT </button>
		</div>
		<div class="col-md-12">
			<div class="table-responsive" id="reportContent">
				<table 
					id="headerTable"
					class="record-table"
					>
					<thead>
						<tr>
							<th>Customer</th>
							<th>Invoice Date</th>
							<th>Invoice No.</th>
							<th>Employee Name</th>
							<th>Saved By</th>
							<th>Total</th>
							<th>Collect</th>
							<th>Discount</th>
							<th>Due</th>
							<th v-for="month in months">{{month}}</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="sale in sales">
							<td>{{sale.customer}}</td>
							<td>{{ sale.SaleMaster_SaleDate }}</td>
							<td>{{ sale.SaleMaster_InvoiceNo }}</td>
							<td>{{ sale.Employee_Name }}</td>
							<td>{{ sale.AddBy }}</td>
							<td>{{ sale.SaleMaster_TotalSaleAmount }}</td>
							<td>{{ sale.total_paid }}</td>
							<td>{{ sale.installment_discount }}</td>
							<td>{{ sale.total_due }}</td>
							<td v-for="month in months" v-html="getMonthData( sale, month)"></td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<th colspan="5" style="text-align: right;">Total=</th>
							<th>{{ sales.reduce((prev, curr)=>{return prev + parseFloat(curr.SaleMaster_TotalSaleAmount)}, 0).toFixed(2) }}</th>
							<th>{{ sales.reduce((prev, curr)=>{return prev + parseFloat(curr.total_paid)}, 0).toFixed(2) }}</th>
							<th>{{ sales.reduce((prev, curr)=>{return prev + parseFloat(curr.installment_discount)}, 0).toFixed(2) }}</th>
							<th>{{ sales.reduce((prev, curr)=>{return prev + parseFloat(curr.total_due)}, 0).toFixed(2) }}</th>
							<th v-for="month in months">{{ getMonthTotal(month) }}</th>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/vue-select.min.js"></script>
<script src="<?php echo base_url();?>assets/js/moment.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/lodash.min.js"></script>

<script type="text/javascript">
	function fnExcelReport()
	{
	    var tab_text="<table border='2px'><tr bgcolor='#87AFC6'>";
	    var textRange; var j=0;
	    tab = document.getElementById('headerTable'); // id of table

	    for(j = 0 ; j < tab.rows.length ; j++) 
	    {     
	        tab_text=tab_text+tab.rows[j].innerHTML+"</tr>";
	        //tab_text=tab_text+"</tr>";
	    }

	    tab_text=tab_text+"</table>";
	    tab_text= tab_text.replace(/<A[^>]*>|<\/A>/g, "");//remove if u want links in your table
	    tab_text= tab_text.replace(/<img[^>]*>/gi,""); // remove if u want images in your table
	    tab_text= tab_text.replace(/<input[^>]*>|<\/input>/gi, ""); // reomves input params

	    var ua = window.navigator.userAgent;
	    var msie = ua.indexOf("MSIE "); 

	    if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./))      // If Internet Explorer
	    {
	        txtArea1.document.open("txt/html","replace");
	        txtArea1.document.write(tab_text);
	        txtArea1.document.close();
	        txtArea1.focus(); 
	        sa=txtArea1.document.execCommand("SaveAs",true,"installment_sales_record.xls");
	    }  
	    else                 //other browser not tested on IE 11
	        sa = window.open('data:application/vnd.ms-excel,' + encodeURIComponent(tab_text));  

	    return (sa);
	}
</script>

<script>
	Vue.component('v-select', VueSelect.VueSelect);
	new Vue({
		el: '#salesRecord',
		data(){
			return {
				searchType: '',
				month: moment().format('YYYY-MM'),
				customers: [],
				selectedCustomer: null,
				employees: [],
				selectedEmployee: null,
				users: [],
				selectedUser: null,
				sales: [],
				installment_month: 0,
				months : [],
				monthNames : ["Jan","Feb","Mar","Apr","May","June","July","Aug","Sep","Oct","Nov","Dec"]
			}
		},
		methods: {
			onChangeSearchType(){
				this.sales = [];
				if(this.searchType == 'user'){
					this.getUsers();
				}
				else if(this.searchType == 'customer'){
					this.getCustomers();
				}
				else if(this.searchType == 'employee'){
					this.getEmployees();
				}
			},
			getCustomers(){
				axios.get('/get_customers').then(res => {
					this.customers = res.data;
				})
			},
			getEmployees(){
				axios.get('/get_employees').then(res => {
					this.employees = res.data;
				})
			},
			getUsers(){
				axios.get('/get_users').then(res => {
					this.users = res.data;
				})
			},
			getMonthData( sale, month ){
				let value = '';
				let monthNames = this.monthNames;
				if (sale.installments.length > 0) {
					sale.installments.forEach(function(installment){
						let m = moment(installment.CPayment_date);
						let month_year = monthNames[m.month()] + ", " + m.year();
						if (month_year === month) {
							value += installment.paid + "<br>"; 
						}
					});
				}
				return value;
			},
			getMonthTotal(month){
				return this.sales.reduce((prev, sale)=>{
					if (sale.installments.length > 0) {
						let value = 0;
						let monthNames = this.monthNames;
						sale.installments.forEach(function(installment){
							let m = moment(installment.CPayment_date);
							let month_year = monthNames[m.month()] + ", " + m.year();
							if (month_year === month) {
								value += parseFloat(installment.paid); 
							}
						});
						return prev + value;
					}else{
						return prev;
					}
				}, 0).toFixed(2);
			},
			getSearchResult(){
				if(this.searchType != 'customer'){
					this.selectedCustomer = null;
				}

				if(this.searchType != 'employee'){
					this.selectedEmployee = null;
				}

				if(this.searchType != 'user'){
					this.selectedUser = null;
				}

				let filter = {
					userFullName: this.selectedUser == null || this.selectedUser.FullName == '' ? '' : this.selectedUser.FullName,
					customerId: this.selectedCustomer == null || this.selectedCustomer.Customer_SlNo == '' ? '' : this.selectedCustomer.Customer_SlNo,
					employeeId: this.selectedEmployee == null || this.selectedEmployee.Employee_SlNo == '' ? '' : this.selectedEmployee.Employee_SlNo,
					month: this.month
				}

				let url = '/get_installment_sales_report';

				axios.post(url, filter)
				.then(res => {
					this.sales = res.data;
					this.sales.forEach((value)=>{
						if(parseInt(value.installment_month) > this.installment_month){
							this.installment_month = parseInt(value.installment_month);
						}
					});

					//get month name
					this.months = [];
					let dates = [moment(this.month).add(1, 'months').format('YYYY-MM-DD'), moment(this.month).add(this.installment_month, 'months').format('YYYY-MM-DD')];

					let diff = dates.map(function(i){
						let m = moment(i);
						
						return {
						month : m.month(),
						year : m.year()
						};
					});

					let start = Object.assign({},diff[0]), end= Object.assign({},diff[diff.length-1]);

					if(end.year>=start.year){
						while(start.month < end.month ||  start.year < end.year){
							
							this.months.push(this.monthNames[start.month]+", "+start.year)
							if(start.month < 11){
								start.month ++;
							}else{
								start.month = 0;
								start.year++;
							}
						}
						this.months.push(this.monthNames[start.month]+", "+start.year)
					}
					console.log(this.months);
					//end month name
					this.installment_month = 0;

				})
				.catch(error => {
					if(error.response){
						alert(`${error.response.status}, ${error.response.statusText}`);
					}
				})
			},
			async print(){
				let dateText = '';
				if(this.month != ''){
					dateText = `Statement from <strong>${this.month}</strong></strong>`;
				}

				let userText = '';
				if(this.selectedUser != null && this.selectedUser.FullName != '' && this.searchType == 'user'){
					userText = `<strong>Sold by: </strong> ${this.selectedUser.FullName}`;
				}

				let customerText = '';
				if(this.selectedCustomer != null && this.selectedCustomer.Customer_SlNo != '' && this.searchType == 'customer'){
					customerText = `<strong>Customer: </strong> ${this.selectedCustomer.Customer_Name}<br>`;
				}

				let employeeText = '';
				if(this.selectedEmployee != null && this.selectedEmployee.Employee_SlNo != '' && this.searchType == 'employee'){
					employeeText = `<strong>Employee: </strong> ${this.selectedEmployee.Employee_Name}<br>`;
				}

				let reportContent = `
					<div class="container">
						<div class="row">
							<div class="col-xs-12 text-center">
								<h3>Sales Record</h3>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-6">
								${userText} ${customerText} ${employeeText}
							</div>
							<div class="col-xs-6 text-right">
								${dateText}
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								${document.querySelector('#reportContent').innerHTML}
							</div>
						</div>
					</div>
				`;

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