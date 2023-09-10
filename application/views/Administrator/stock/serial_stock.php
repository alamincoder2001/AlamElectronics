<style>
    .v-select {
        margin-bottom: 5px;
    }

    .v-select .dropdown-toggle {
        padding: 0px;
    }

    .v-select input[type=search],
    .v-select input[type=search]:focus {
        margin: 0px;
    }

    .v-select .vs__selected-options {
        overflow: hidden;
        flex-wrap: nowrap;
    }

    .v-select .selected-tag {
        margin: 2px 0px;
        white-space: nowrap;
        position: absolute;
        left: 0px;
    }

    .v-select .vs__actions {
        margin-top: -5px;
    }

    .v-select .dropdown-menu {
        width: auto;
        overflow-y: auto;
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
.modal-header{
    padding-bottom: 0 !important;
}
.modal-header h3 {
margin-top: 0;
color: #42b983;
}

.modal-body {
margin: 0px 0;
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
width: 700px;
margin: 0px auto;
background-color: #fff;
border-radius: 2px;
box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
transition: all .3s ease;
font-family: Helvetica, Arial, sans-serif;
}
.modal-header{
    padding-bottom: 0 !important;
}
.modal-header h3 {
margin-top: 0;
color: #42b983;
}

.modal-body {
margin: 0px 0;
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
</style>
<div id="stock">
	<!-- Stock product serial modal -->
	<div class="row">
		<div class="col-xs-12 col-md-12 col-lg-12" style="border-bottom:1px #ccc solid;margin-bottom:5px;">

	
			<div class="form-group" style="margin-top:10px;">
				<div class="col-sm-2" style="margin-left:15px;">
					<v-select v-bind:options="categories" v-model="selectedCategory" label="ProductCategory_Name" placeholder="Category" v-on:input="getProducts"></v-select>
				</div>
			</div>
	
			<div class="form-group" style="margin-top:10px;">
				<div class="col-sm-2" style="margin-left:15px;">
					<v-select v-bind:options="products" v-model="selectedProduct" label="display_text" placeholder="Product"></v-select>
				</div>
			</div>
	
			<div class="form-group">
				<div class="col-sm-2"  style="margin-left:15px;">
					<input type="button" class="btn btn-primary" value="Show Report" v-on:click="getStock" style="margin-top:0px;border:0px;height:28px;">
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<a href="" v-on:click.prevent="print"><i class="fa fa-print"></i> Print</a>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div class="table-responsive" id="stockContent">
				<table class="table table-bordered">
					<thead>
						<tr>
							<th>#</th>
							<th>Serial</th>
							<th>Product Id</th>
							<th>Product Name</th>
							<th>Color</th>
							<th>Category</th>
							<th>Value</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(product, sl) in stock">
							<td>{{ ++sl }}</td>
							<td>{{ product.ps_serial_number }}</td>
							<td>{{ product.Product_Code }}</td>
							<td>{{ product.Product_Name }}</td>
							<td>{{ product.color_name }}</td>
							<td>{{ product.ProductCategory_Name }}</td>
							<td>{{ product.purchase_rate }}</td>
						</tr>

						<tr>
							<th style="text-align: right;" colspan="6">Total=</th>
							<th>{{ stock.reduce((prev, curr)=> {return prev + +parseFloat(curr.purchase_total)}, 0) | decimal }}</th>
						</tr>
					</tbody>
					
				</table>
				
			</div>
		</div>
	</div>
</div>


<script src="<?php echo base_url();?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/vue-select.min.js"></script>

<script>
	Vue.component('v-select', VueSelect.VueSelect);
	new Vue({
		el: '#stock',
		data(){
			return {
				categories      : [],
				selectedCategory: null,
				products        : [],
				selectedProduct : null,
				stock           : [],
				selectionText   : ''
			}
		},
		filters: {
			decimal(value) {
				return value == null ? '0.00' : parseFloat(value).toFixed(2);
			}
		},
		created(){
			this.getCategories();
			this.getProducts();
		},
		methods:{
			
			getStock(){
				let parameters = {};

								
				this.selectionText = "";

				if(this.selectedCategory != null) {
					parameters.categoryId = this.selectedCategory.ProductCategory_SlNo;
					this.selectionText = "Category: " + this.selectedCategory.ProductCategory_Name;
				}
				
				if(this.selectedProduct != null) {
					parameters.prod_id = this.selectedProduct.Product_SlNo;
					this.selectionText = "Product: " + this.selectedProduct.display_text;
				}


				axios.post('/get_Serial_By_Prod', parameters).then(res => {
					this.stock = res.data;
				})
			},
			getCategories(){
				axios.get('/get_categories').then(res => {
					this.categories = res.data;
				})
			},
			getProducts(){
				let categoryId = '';
				if(this.selectedCategory != null) {
					categoryId = this.selectedCategory.ProductCategory_SlNo;
				}

				axios.post('/get_products', {categoryId}).then(res => {
					this.products =  res.data;
				})
			},
			async print(){
				let reportContent = `
					<div class="container-fluid">
						<h4 style="text-align:center">Serial Stock Report</h4 style="text-align:center">
						<h6 style="text-align:center">${this.selectionText}</h6>
					</div>
					<div class="container-fluid">
						<div class="row">
							<div class="col-xs-12">
								${document.querySelector('#stockContent').innerHTML}
							</div>
						</div>
					</div>
				`;

				var reportWindow = window.open('', 'PRINT', `height=${screen.height}, width=${screen.width}, left=0, top=0`);
				reportWindow.document.write(`
					<?php $this->load->view('Administrator/reports/reportHeader.php');?>
				`);

				reportWindow.document.body.innerHTML += reportContent;

				reportWindow.focus();
				await new Promise(resolve => setTimeout(resolve, 1000));
				reportWindow.print();
				reportWindow.close();
			}
		}
	})
</script>