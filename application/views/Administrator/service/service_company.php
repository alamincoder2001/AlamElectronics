<div class="row" id="service">
    <div class="col-md-8 col-md-offset-2">
        <form @submit.prevent="saveCompany">
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <div class="form-group">
                        <label for="name" class="col-md-4">Name</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" v-model="company.name" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name" class="col-md-4">Description</label>
                        <div class="col-md-8">
                            <textarea rows="2" class="form-control" v-model="company.description"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name" class="col-md-4"></label>
                        <div class="col-md-8">
                            <input type="submit" class="btn btn-success btn-block" value="Save">
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <hr>
    </div>
    <div class="col-md-10 col-md-offset-1">
        <div class="table-responsive">
            <datatable :columns="columns" :data="companies" :filter-by="filter">
                <template scope="{ row }">
                    <tr>
                        <td>{{ row.sl }}</td>
                        <td>{{ row.name }}</td>
                        <td>{{ row.description }}</td>
                        <td>
                            <?php if($this->session->userdata('accountType') != 'u'){?>
                            <button type="button" class="button edit" @click="editCompany(row)">
                                <i class="fa fa-pencil"></i>
                            </button>
                            <button type="button" class="button" @click="deleteCompany(row.id)">
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
<script src="<?php echo base_url();?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/vuejs-datatable.js"></script>
<script>
    const app = new Vue({
        el: '#service',
        data: {
            company: {
                id: null,
                name: '',
                description: ''
            },
            companies: [],
            columns: [
                { label: 'Serial', field: 'sl', align: 'center', filterable: false },
                { label: 'Company Name', field: 'name', align: 'center' },
                { label: 'Description', field: 'description', align: 'center' },
                { label: 'Action', align: 'center', filterable: false }
            ],
            page: 1,
            per_page: 10,
            filter: ''
        },
        created() {
            this.getCompanies();
        },
        methods: {
            getCompanies() {
                axios.post('/get_company')
                .then(res => {
                    this.companies = res.data.map((item, sl) => {
                        item.sl = sl + 1;
                        return item;
                    })
                })
            },
            saveCompany() {
                if(this.company.name == '') {
                    alert('Name is required');
                    return;
                }

                let url = '';
                if(this.company.id != null) {
                    url = '/update_company';
                } else {
                    url = '/add_company';
                    delete this.company.id;
                }

                axios.post(url, this.company)
                .then(res => {
                    alert(res.data.message);
                    if(res.data.success) {
                        this.resetForm();
                        this.getCompanies();
                    }
                })
                .catch(err => {
                    alert(err.response.data.message)
                })
            },
            editCompany(company) {
                Object.keys(company).forEach(key => {
                    this.company[key] = company[key];
                })
            },
            deleteCompany(id) {
                if(confirm('Are you sure ?')) {
                    axios.post('/delete_company', {id: id})
                    .then(res => {
                        alert(res.data.message);
                        if(res.data.success) {
                            this.getCompanies();
                        }
                    })
                }
            },
            resetForm() {
                this.company.id = null;
                this.company.name = '';
                this.company.description = '';
            }
        }
    })
</script>