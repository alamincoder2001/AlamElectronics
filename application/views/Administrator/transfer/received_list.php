<style>
    .v-select {
        margin: 0 10px 5px 5px;
        float: right;
        min-width: 180px;
    }

    .v-select .dropdown-toggle {
        padding: 0px;
        height: 25px;
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
</style>

<div id="transferList">
    <div class="row" style="border-bottom: 1px solid #ccc;">
        <div class="col-md-12">
            <form class="form-inline" @submit.prevent="getTransfers">
                <div class="form-group">
                    <label>Transfer from</label>
                    <v-select v-bind:options="branches" v-model="selectedBranch" label="Brunch_name" placeholder="Select Branch"></v-select>
                </div>

                <div class="form-group">
                    <select id="searchDetails" v-model="filter.searchBy" class="form-control no-padding">
                        <option value="without">Without Details</option>
                        <option value="with">With Details</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Date from</label>
                    <input type="date" class="form-control" v-model="filter.dateFrom">
                </div>

                <div class="form-group">
                    <label>to</label>
                    <input type="date" class="form-control" v-model="filter.dateTo">
                </div>

                <div class="form-group">
                    <input type="submit" class="btn btn-info btn-xs" value="Search" style="padding-top:0px;padding-bottom:0px;margin-top:-4px;">
                </div>
            </form>
        </div>
    </div>

    <div class="row" style="margin-top: 15px;">
        <div class="col-md-12">
            <div class="table-responsive">
                <table v-if="filter.searchBy == 'without'" class="table table-bordered" style="display: none;" :style="{display: filter.searchBy == 'without' ? '' : 'none'}">
                    <thead>
                        <tr>
                            <th>Sl</th>
                            <th>Transfer Date</th>
                            <th>Transfer by</th>
                            <th>Transfer From</th>
                            <th>Note</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(transfer, sl) in transfers" :title="transfer.Status == 'p' ? 'Pending' : 'Received'" :style="{background: transfer.Status == 'p' ? '#f29339':''}">
                            <td>{{ sl + 1 }}</td>
                            <td>{{ transfer.transfer_date }}</td>
                            <td>{{ transfer.transfer_by_name }}</td>
                            <td>{{ transfer.transfer_from_name }}</td>
                            <td>{{ transfer.note }}</td>
                            <td>
                                <?php if ($this->session->userdata('accountType') == 'm') { ?>
                                    <a v-if="transfer.Status == 'p'" @click.prevent="updateStatus(transfer.transfer_id)" style="background: rgb(0 193 237);padding: 0px 4px;text-decoration: none;color: white;" href="" target="_blank" title="Received Transfer">Receive</a>
                                <?php } ?>
                                <a href="" v-bind:href="`/transfer_invoice/${transfer.transfer_id}`" target="_blank" title="View invoice"><i class="fa fa-file"></i></a>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table v-else class="table table-bordered" style="display: none;" :style="{display: filter.searchBy == 'with' ? '' : 'none'}">
                    <thead>
                        <tr>
                            <th>Sl</th>
                            <th>Transfer Date</th>
                            <th>Transfer by</th>
                            <th>Transfer From</th>
                            <th>Product Code</th>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="(transfer, sl) in transfers">
                            <tr :style="{background: transfer.Status == 'p' ? '#f29339':''}" :title="transfer.Status == 'p' ? 'Pending' : 'Received'">
                                <td>{{ sl + 1 }}</td>
                                <td>{{ transfer.transfer_date }}</td>
                                <td>{{ transfer.transfer_by_name }}</td>
                                <td>{{ transfer.transfer_from_name }}</td>
                                <td>{{ transfer.transferDetails[0].Product_Code }}</td>
                                <td>{{ transfer.transferDetails[0].Product_Name }}</td>
                                <td>{{ transfer.transferDetails[0].purchase_rate }}</td>
                                <td>{{ transfer.transferDetails[0].quantity }}</td>
                                <td style="text-align:right;">{{ transfer.transferDetails[0].total }}</td>
                                <td>
                                    <?php if ($this->session->userdata('accountType') == 'm') { ?>
                                        <a v-if="transfer.Status == 'p'" @click.prevent="updateStatus(transfer.transfer_id)" style="background: rgb(0 193 237);padding: 0px 4px;text-decoration: none;color: white;" href="" target="_blank" title="Received Transfer">Receive</a>
                                    <?php } ?>
                                    <a href="" v-bind:href="`/transfer_invoice/${transfer.transfer_id}`" target="_blank" title="View invoice"><i class="fa fa-file"></i></a>
                                </td>
                            </tr>
                            <tr v-for="(product, sl) in transfer.transferDetails.slice(1)">
                                <td colspan="4" v-bind:rowspan="transfer.transferDetails.length - 1" v-if="sl == 0"></td>
                                <td>{{ product.Product_Code }}</td>
                                <td>{{ product.Product_Name }}</td>
                                <td style="text-align:center;">{{ product.purchase_rate }}</td>
                                <td style="text-align:center;">{{ product.quantity }}</td>
                                <td style="text-align:right;">{{ product.total }}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <th style="text-align: left;" colspan="8">
                                    Note: {{transfer.note}}
                                </th>
                                <th style="text-align: right;">
                                    Quantity: {{ transfer.transferDetails.reduce((acc, pre) => {return acc + +parseFloat(pre.quantity)},0) }} <br>
                                    Price: {{ transfer.transferDetails.reduce((acc, pre) => {return acc + +parseFloat(pre.total)},0).toFixed(2) }}
                                </th>
                                <th></th>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo base_url(); ?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/vue-select.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/moment.min.js"></script>

<script>
    Vue.component('v-select', VueSelect.VueSelect);
    new Vue({
        el: '#transferList',
        data() {
            return {
                filter: {
                    searchBy: 'without',
                    branch: null,
                    dateFrom: moment().format('YYYY-MM-DD'),
                    dateTo: moment().format('YYYY-MM-DD')
                },
                branches: [],
                selectedBranch: null,
                transfers: []
            }
        },
        created() {
            this.getBranches();
        },
        methods: {
            getBranches() {
                axios.get('/get_branches').then(res => {
                    let thisBranchId = parseInt("<?php echo $this->session->userdata('BRANCHid'); ?>");
                    let ind = res.data.findIndex(branch => branch.brunch_id == thisBranchId);
                    res.data.splice(ind, 1);
                    this.branches = res.data;
                })
            },

            getTransfers() {
                if (this.selectedBranch != null) {
                    this.filter.branch = this.selectedBranch.brunch_id;
                } else {
                    this.filter.branch = null;
                }

                axios.post('/get_receives', this.filter).then(res => {
                    this.transfers = res.data;
                })
            },

            updateStatus(id) {
                if (confirm("Are you sure?")) {
                    axios.post('/transfer_status_update', {
                        transfer_id: id
                    }).then(res => {
                        if (res.data.success == true) {
                            alert(res.data.message)
                            this.getTransfers();
                        } else {
                            console.log(res.data.message);
                        }
                    })
                }
            }
        }
    })
</script>