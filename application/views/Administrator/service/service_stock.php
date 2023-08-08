<div id="stocks">
    <div class="row" style="border-bottom: 1px solid #ccc;padding: 3px 0;">
		<div class="col-md-12">
			<form class="form-inline" id="searchForm" @submit.prevent="getServiceStock">
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
    <div style="display:none;" v-bind:style="{display: stocks.length > 0 ? '' : 'none'}">
        <div class="row">
            <div class="col-md-12">
                <a href="" @click.prevent="printServiceList"><i class="fa fa-print"></i> Print</a>
            </div>
        </div>

        <div class="row" style="margin-top:15px;">
            <div class="col-md-12">
                <div class="table-responsive" id="printContent">
                    <table class="table table-bordered table-condensed">
                        <thead>
                            <th>Serial</th>
                            <th>Product Name</th>
                            <th>Model</th>
                            <th>Imei</th>
                            <th>Quantity</th>
                            <th>Save By</th>
                        </thead>
                        <tbody>
                            <tr v-for="(product, sl) in stocks">
                                <td>{{ sl + 1 }}</td>
                                <td>{{ product.product_name }}</td>
                                <td>{{ product.model }}</td>
                                <td>{{ product.imei }}</td>
                                <td>{{ product.quantity }}</td>
                                <td>{{ product.added_by }}</td>
                            </tr>
                        </tbody>
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
    const app = new Vue({
        el: '#stocks',
        data: {
            dateFrom: moment().format('YYYY-MM-DD'),
			dateTo: moment().format('YYYY-MM-DD'),
            stocks: []
        },
        methods: {
            getServiceStock() {
                let filter = {
                    dateForm: this.dateFrom,
                    dateTo: this.dateTo,
                    status: 'p'
                }
                axios.post('/get_service_stock', filter)
                .then(res => {
                    this.stocks = res.data
                })
            },
            async printServiceList() {
                let printContent = `
                    <div class="container">
                        <h4 style="text-align:center">Service Pending Stock</h4 style="text-align:center">
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