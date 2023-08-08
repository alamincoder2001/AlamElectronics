<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Service extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $access = $this->session->userdata('userId');
            if($access == '' ){
            redirect("Login");
        }

        $this->load->model('Model_table', 'mt', true);
    }

    public function index() {
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }

        $invoice = $this->mt->generateServiceInvoice();
        $data['serviceId'] = 0;
        $data['invoice'] = $invoice;
        $data['title'] = "Service Entry";
        $data['content'] = $this->load->view('Administrator/service/service', $data, true);
        $this->load->view('Administrator/index', $data);
    }

    public function addService() {
        $res = new stdClass();
        try {
            $data = json_decode($this->input->raw_input_stream);

            $duplicateInvoice = $this->db->query("select * from tbl_servicemaster where status = 'a' and invoice = ?", $data->service->invoice);

            if($duplicateInvoice->num_rows() > 0) {
                $res->message = "This invoice already exists";
                echo json_encode($res);
                exit;
            }

            $service = array(
                'date' => $data->service->date,               
                'invoice' => $data->service->invoice,               
                'customer_name' => $data->service->customerName,               
                'customer_mobile' => $data->service->customerMobile,               
                'customer_address' => $data->service->customerAddress,               
                'total' => $data->service->total,      
                'paid' => $data->service->paid,      
                'due' => $data->service->due,           
                'status' => 'a',               
                'added_by' =>  $this->session->userdata("FullName"),               
                'added_time' => date("Y-m-d H:i:s"),               
                'branch_id' => $this->session->userdata("BRANCHid")              
            );

            // service entry
            $this->db->insert('tbl_servicemaster', $service);
            $serviceId = $this->db->insert_id();

            // service details entry
            if(isset($data->product)) {
                foreach($data->product as $product) {
                    
                    $product = array(
                        'service_invoice' => $data->service->invoice,
                        'product_name' => $product->product_name,
                        'model' => $product->model,
                        'imei' => $product->imei,
                        'quantity' => $product->quantity,
                        'service_status' => $product->status,
                        'company_id' => $product->company_id,
                        'transfer_date' => $product->status == 't' ? date('Y-m-d')  : '',
                        'receive_date' => $product->status == 'r' ? date('Y-m-d')  : '',
                        'status' => 'a',
                        'added_by' =>  $this->session->userdata("FullName"),               
                        'added_time' => date("Y-m-d H:i:s"),               
                        'branch_id' => $this->session->userdata("BRANCHid") 
                    );

                    $this->db->insert('tbl_servicedetails', $product);
                }
            }

            // expense entry

            if(isset($data->costing)) {
                foreach($data->costing as $cost) {
                    $expense = array(
                        'service_invoice' => $data->service->invoice,
                        'expense' => $cost->expense,
                        'price' => $cost->price,
                        'quantity' => $cost->unit,
                        'amount' => $cost->amount,
                        'status' => 'a',
                        'added_by' =>  $this->session->userdata("FullName"),               
                        'added_time' => date("Y-m-d H:i:s"),               
                        'branch_id' => $this->session->userdata("BRANCHid")
                    );

                    $this->db->insert('tbl_serviceexpense', $expense);
                }
            }

            $res->message = "Service save successfully";
            $res->success = true;
            $res->serviceId = $serviceId;

        } catch (\Exception $e) {
            $res->message = 'Service save failed'. $e->getMessage();
        }

        echo json_encode($res);
    }

    public function updateService() {
        $res = new stdClass();
        try {
            $data = json_decode($this->input->raw_input_stream);

            $duplicateInvoice = $this->db->query("select * from tbl_servicemaster where status = 'a' and invoice = ? and id != ? ", [$data->service->invoice, $data->service->serviceId]);

            if($duplicateInvoice->num_rows() > 0) {
                $res->message = "This invoice already exists";
                echo json_encode($res);
                exit;
            }

            $service = array(
                'date' => $data->service->date,               
                'customer_name' => $data->service->customerName,               
                'customer_mobile' => $data->service->customerMobile,               
                'customer_address' => $data->service->customerAddress,               
                'total' => $data->service->total,      
                'paid' => $data->service->paid,      
                'due' => $data->service->due,           
                'update_by' =>  $this->session->userdata("FullName"),               
                'update_time' => date("Y-m-d H:i:s"),               
                'branch_id' => $this->session->userdata("BRANCHid")              
            );

            // service entry
            $this->db->where('id', $data->service->serviceId)->update('tbl_servicemaster', $service);

            $serviceMaster = $this->db->query("select invoice from tbl_servicemaster where status = 'a' and id = ?", $data->service->serviceId)->row();
            $serviceInvoice = $serviceMaster->invoice;
            $this->db->where('service_invoice', $serviceInvoice)->delete('tbl_servicedetails');
            $this->db->where('service_invoice', $serviceInvoice)->delete('tbl_serviceexpense');

            // service details entry
            if(isset($data->product)) {
                foreach($data->product as $product) {
                    $product = array(
                        'service_invoice' => $serviceInvoice,
                        'product_name' => $product->product_name,
                        'model' => $product->model,
                        'imei' => $product->imei,
                        'quantity' => $product->quantity,
                        'service_status' => $product->status,
                        'company_id' => $product->company_id,
                        'transfer_date' => $product->status == 't' ? date('Y-m-d')  : '',
                        'receive_date' => $product->status == 'r' ? date('Y-m-d')  : '',
                        'status' => 'a',
                        'added_by' =>  $this->session->userdata("FullName"),               
                        'added_time' => date("Y-m-d H:i:s"),               
                        'branch_id' => $this->session->userdata("BRANCHid") 
                    );

                    $this->db->insert('tbl_servicedetails', $product);
                }
            }

            // expense entry

            if(isset($data->costing)) {
                foreach($data->costing as $cost) {
                    $expense = array(
                        'service_invoice' => $data->service->invoice,
                        'expense' => $cost->expense,
                        'price' => $cost->price,
                        'quantity' => $cost->unit,
                        'amount' => $cost->amount,
                        'status' => 'a',
                        'added_by' =>  $this->session->userdata("FullName"),               
                        'added_time' => date("Y-m-d H:i:s"),               
                        'branch_id' => $this->session->userdata("BRANCHid")
                    );

                    $this->db->insert('tbl_serviceexpense', $expense);
                }
            }

            $res->message = "Service update successfully";
            $res->success = true;
            $res->serviceId = $data->service->serviceId;

        } catch (\Exception $e) {
            $res->message = 'Service update failed'. $e->getMessage();
        }

        echo json_encode($res);
    }
    public function serviceRecord() {
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }
        $data['title'] = "Service Record";
        $data['content'] = $this->load->view('Administrator/service/service_record', $data, true);
        $this->load->view('Administrator/index', $data); 
    }

    public function editService($id) {
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }

        $service = $this->db->query("select * from  tbl_servicemaster where status = 'a' and id = ?", $id)->row();
        $data['serviceId'] = $id;
        $data['invoice'] = $service->invoice;
        $data['title'] = "Service Entry";
        $data['content'] = $this->load->view('Administrator/service/service', $data, true);
        $this->load->view('Administrator/index', $data);
    }

    public function deleteService() {
        $res = new stdClass;
        try {
            $data = json_decode($this->input->raw_input_stream);

            $service = $this->db->query("select * from  tbl_servicemaster where status = 'a' and id = ?", $data->id)->row();

            // service master update
            $this->db->query("update tbl_servicemaster set status = 'd' where id = ?", $data->id);
            
            // service details
            $serviceDetail = $this->db->query("select * from tbl_servicedetails where service_invoice = ?", $service->invoice)->result();
            foreach($serviceDetail as $detail) {
                $this->db->query("update tbl_servicedetails set status = 'd' where service_invoice = ?", $detail->service_invoice);
            }

            // expense details
            $expenseDetail = $this->db->query("select * from tbl_serviceexpense where service_invoice = ?", $service->invoice)->result();
            foreach($expenseDetail as $expense) {
                $this->db->query("update tbl_serviceexpense set status = 'd' where service_invoice = ?", $expense->service_invoice);
            }

            $res->message = "Service deleted successfully";
            $res->success = true;
        } catch (\Exception $e) {
            $res->message = "Failed ".$e->getMessage();
        }
        echo json_encode($res);
    }

    public function checkImei() {
        $res = new stdClass;
        $data = json_decode($this->input->raw_input_stream);
        $check = $this->db->query("select * from tbl_servicedetails where status = 'a' and imei = ?", $data->imei);
        if($check->num_rows() > 0) {
            $res->message = "This product imei no already exists";
            $res->success = true;
        }
        echo json_encode($res);
    }

    public function getServices() {
        $res = new stdClass;
        $data = json_decode($this->input->raw_input_stream);

        $clauses = "";
        if(isset($data->dateFrom) && $data->dateFrom != '' && isset($data->dateTo) && $data->dateTo != '') {
            $clauses .= " and sm.date between '$data->dateFrom' and '$data->dateTo'";
        } 
        if(isset($data->id) && $data->id != 0) {
            $clauses .= " and sm.id = $data->id";
        }

        if(isset($data->invoice) && $data->invoice) {
            $clauses .= " and sm.invoice = $data->invoice";
           
            $res->serviceDetails = $this->db->query("
                select 
                    sd.*
                from tbl_servicedetails sd 
                where sd.status = 'a'
                and sd.service_invoice = ?
            ", $data->invoice)->result();

            $res->expenseDetails = $this->db->query("
                select 
                    se.*
                from tbl_serviceexpense se 
                where se.status = 'a'
                and se.service_invoice = ?
            ", $data->invoice)->result();
        }
        
        $res->services = $this->db->query("
            select 
                sm.*,
                (
                    select 
                        ifnull(sum(sd.quantity), 0) 
                    from tbl_servicedetails sd
                    where sd.status = 'a'
                    and sd.service_invoice = sm.invoice
                )as quantity,
                (
                    select 
                        ifnull(sum(se.amount), 0)
                    from tbl_serviceexpense se
                    where se.status = 'a'
                    and se.service_invoice = sm.invoice
                )as total
            from tbl_servicemaster sm 
            where sm.status = 'a'
            and sm.branch_id = ?
            $clauses
        ", $this->session->userdata("BRANCHid"))->result();


        echo json_encode($res);
    }

    // company entry
    public function serviceCompany() {
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }
        $data['title'] = "Service Company Entry";
        $data['content'] = $this->load->view('Administrator/service/service_company', $data, true);
        $this->load->view('Administrator/index', $data);
    }

    public function getServiceCompany() {
        $company = $this->db->query("select * from  tbl_servicecompany where status = 'a'")->result();
        echo json_encode($company);
    }

    public function addServiceCompany() {
        $res = new stdClass;
        try {
            $data = json_decode($this->input->raw_input_stream);

            $duplicateName = $this->db->query("select * from tbl_servicecompany where status = 'a' and name = ? and branch_id = ?", [$data->name, $this->session->userdata('BRANCHid')]);
            if($duplicateName->num_rows() > 0) {
                $res->message = 'This company name already exists';
                echo json_encode($res);
                exit;
            }

            $company = array(
                'name' => $data->name,
                'description' => $data->description,
                'status' => 'a',
                'branch_id' => $this->session->userdata('BRANCHid'),
            );

            $this->db->insert('tbl_servicecompany', $company);
            $res->message = 'Company added successfully';
            $res->success = true;
        } catch (\Exception $e) {
            $res->message = 'failed '. $e->getMessage();
        }

        echo json_encode($res);
    }
    
    public function updateServiceCompany() {
        $res = new stdClass;
        try {
            $data = json_decode($this->input->raw_input_stream);

            $duplicateName = $this->db->query("select * from tbl_servicecompany where status = 'a' and name = ? and branch_id = ? and id != ?", [$data->name, $this->session->userdata('BRANCHid'), $data->id]);
            if($duplicateName->num_rows() > 0) {
                $res->message = 'This company name already exists';
                echo json_encode($res);
                exit;
            }

            $company = array(
                'name' => $data->name,
                'description' => $data->description,
                'status' => 'a',
                'branch_id' => $this->session->userdata('BRANCHid'),
            );

            $this->db->where('id', $data->id)->update('tbl_servicecompany', $company);
            $res->message = 'Company update successfully';
            $res->success = true;
        } catch (\Exception $e) {
            $res->message = 'failed '. $e->getMessage();
        }

        echo json_encode($res);
    }

    public function deleteServiceCompany() {
        $res = new stdClass;
        try {
            $data = json_decode($this->input->raw_input_stream);
            $service = array('status' => 'd');
            $this->db->where('id', $data->id)->update('tbl_servicecompany', $service);
            $res->message = "Company delete successfully";
            $res->success = true;
        } catch (\Exception $e) {
            $res->message = "Failed ". $e->getMessage();
        }
        echo json_encode($res);
    }

    public function getServiceRecord() {
        $data = json_decode($this->input->raw_input_stream);

        $clauses = "";
        if(isset($data->dateFrom) && $data->dateFrom != '' && isset($data->dateTo) && $data->dateTo != '') {
            $clauses .= " and sm.date between '$data->dateFrom' and '$data->dateTo'";
        } 
        
        $services = $this->db->query("
            select 
                sm.*,
                (
                    select 
                        ifnull(sum(sd.quantity), 0) 
                    from tbl_servicedetails sd
                    where sd.status = 'a'
                    and sd.service_invoice = sm.invoice
                )as quantity,
                (
                    select 
                        ifnull(sum(se.amount), 0)
                    from tbl_serviceexpense se
                    where se.status = 'a'
                    and se.service_invoice = sm.invoice
                )as total
            from tbl_servicemaster sm 
            where sm.status = 'a'
            and sm.branch_id = ?
            $clauses
        ", $this->session->userdata("BRANCHid"))->result();

        foreach($services as $service) {

            $service->serviceDetails = $this->db->query("
                select 
                    sd.*
                from tbl_servicedetails sd 
                where sd.status = 'a'
                and sd.service_invoice = ?
            ", $service->invoice)->result();

            $service->expenseDetails = $this->db->query("
                select 
                    se.*
                from tbl_serviceexpense se 
                where se.status = 'a'
                and se.service_invoice = ?
            ", $service->invoice)->result();
        }
        echo json_encode($services);
    }

    public function serviceStock() {
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }
        $data['title'] = "Service Stock";
        $data['content'] = $this->load->view('Administrator/service/service_stock', $data, true);
        $this->load->view('Administrator/index', $data);
    }

    public function getServiceStock() {
        $data = json_decode($this->input->raw_input_stream);
        $dateFrom = $data->dateForm;
        $dateTo = $data->dateTo;
        $clauses = "";

        if(isset($data->status) && $data->status != '') {
            $clauses .= " and sd.service_status = '$data->status'";
        }
        if(isset($dateFrom) && isset($dateTo) && $data->status == 'p') {
            $clauses .= " and date(sd.added_time) between '$dateFrom' and '$dateTo'";
        }
        if($data->status == 't' && $dateFrom != '' && $dateTo != '') {
            $clauses .= " and sd.transfer_date between '$dateFrom' and '$dateTo'";
        }
       
        $stock = $this->db->query("
            select 
                sd.*,
                sc.name
            from tbl_servicedetails sd 
            left join tbl_servicecompany sc on sc.id = sd.company_id
            where sd.status = 'a'
            and sd.branch_id = ?
            $clauses
        ", $this->session->userdata('BRANCHid'))->result();
        echo json_encode($stock);
    }

    public function serviceOutStock() {
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }
        $data['title'] = "Service  Out Stock";
        $data['content'] = $this->load->view('Administrator/service/service_out_stock', $data, true);
        $this->load->view('Administrator/index', $data);
    }
}   
