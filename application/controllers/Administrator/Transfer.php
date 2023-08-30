<?php
class Transfer extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $access = $this->session->userdata('userId');
        if ($access == '') {
            redirect("Login");
        }
        $this->load->model('Model_table', "mt", TRUE);
    }

    public function productTransfer()
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }

        $data['transferId'] = 0;
        $data['title'] = "Product Transfer";
        $data['content'] = $this->load->view('Administrator/transfer/product_transfer', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function transferEdit($transferId)
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }

        $data['transferId'] = $transferId;
        $data['title'] = "Product Transfer";
        $data['content'] = $this->load->view('Administrator/transfer/product_transfer', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function addProductTransfer()
    {
        $res = ['success' => false, 'message' => ''];
        try {
            $data = json_decode($this->input->raw_input_stream);
            $transfer = array(
                'transfer_date'  => $data->transfer->transfer_date,
                'transfer_by'    => $data->transfer->transfer_by,
                'transfer_from'  => $this->session->userdata('BRANCHid'),
                'transfer_to'    => $data->transfer->transfer_to,
                'note'           => $data->transfer->note,
                'total_amount'   => $data->transfer->total_amount,
                'added_by'       => $this->session->userdata('userId'),
                'added_datetime' => date("Y-m-d H:i:s"),
                'Status'         => 'p',
            );

            $this->db->insert('tbl_transfermaster', $transfer);
            $transferId = $this->db->insert_id();

            foreach ($data->cart as $cartProduct) {
                $transferDetails = array(
                    'transfer_id'   => $transferId,
                    'product_id'    => $cartProduct->product_id,
                    'quantity'      => $cartProduct->quantity,
                    'purchase_rate' => $cartProduct->purchase_rate,
                    'total'         => $cartProduct->total
                );
                $this->db->insert('tbl_transferdetails', $transferDetails);
                $d_id = $this->db->insert_id();

                if (count($cartProduct->SerialStore) > 0) {
                    foreach ($cartProduct->SerialStore as $value) {
                        $serial = array(
                            'transferdetail_id' => $d_id,
                            'product_id'        => $cartProduct->product_id,
                            'ps_id'             => $value->ps_id,
                            'ps_serial_number'  => $value->ps_serial_number,
                            'from_branchId'     => $this->session->userdata('BRANCHid'),
                        );
                        $this->db->insert('tbl_transfer_productserial', $serial);
                    }
                }
            }
            $res = ['success' => true, 'message' => 'Transfer success'];
        } catch (Exception $ex) {
            $res = ['success' => false, 'message' => $ex->getMessage];
        }

        echo json_encode($res);
    }

    public function updateProductTransfer()
    {
        $res = ['success' => false, 'message' => ''];
        try {
            $data = json_decode($this->input->raw_input_stream);
            $transferId = $data->transfer->transfer_id;
            $oldTransfer = $this->db->query("select * from tbl_transfermaster where transfer_id = ?", $transferId)->row();

            $transfer = array(
                'transfer_date'    => $data->transfer->transfer_date,
                'transfer_by'      => $data->transfer->transfer_by,
                'transfer_from'    => $this->session->userdata('BRANCHid'),
                'transfer_to'      => $data->transfer->transfer_to,
                'note'             => $data->transfer->note,
                'updated_by'       => $this->session->userdata('userId'),
                'updated_datetime' => date("Y-m-d H:i:s"),
                'Status'           => $oldTransfer->Status
            );

            $this->db->where('transfer_id', $data->transfer->transfer_id)->update('tbl_transfermaster', $transfer);

            $oldTransferDetails = $this->db->query("select * from tbl_transferdetails where transfer_id = ?", $transferId)->result();
            $this->db->query("delete from tbl_transferdetails where transfer_id = ?", $transferId);
            foreach ($oldTransferDetails as $oldDetails) {
                $this->db->query("
                        update tbl_currentinventory 
                        set transfer_from_quantity = transfer_from_quantity - ? 
                        where product_id = ?
                        and branch_id = ?
                    ", [$oldDetails->quantity, $oldDetails->product_id, $this->session->userdata('BRANCHid')]);

                $this->db->query("
                        update tbl_currentinventory 
                        set transfer_to_quantity = transfer_to_quantity - ? 
                        where product_id = ?
                        and branch_id = ?
                    ", [$oldDetails->quantity, $oldDetails->product_id, $oldTransfer->transfer_to]);

                $serials = $this->db->query("
                        select * from tbl_product_serial_numbers 
                        where transfer_details_id = $oldDetails->transferdetails_id
                    ")->result();

                foreach ($serials as $serial) {
                    $product_serial = array(
                        'ps_brunch_id'          => $oldTransfer->transfer_from,
                        'transfer_details_id'   => null
                    );
                    $this->db->where('ps_serial_number', $serial->ps_serial_number);
                    $this->db->update('tbl_product_serial_numbers', $product_serial);
                }
            }

            foreach ($data->cart as $cartProduct) {
                $transferDetails = array(
                    'transfer_id' => $transferId,
                    'product_id' => $cartProduct->product_id,
                    'quantity' => $cartProduct->quantity
                );

                $this->db->insert('tbl_transferdetails', $transferDetails);
                $d_id = $this->db->insert_id();
                $currentBranchInventoryCount = $this->db->query("select * from tbl_currentinventory where product_id = ? and branch_id = ?", [$cartProduct->product_id, $this->session->userdata('BRANCHid')])->num_rows();
                if ($currentBranchInventoryCount == 0) {
                    $currentBranchInventory = array(
                        'product_id' => $cartProduct->product_id,
                        'transfer_from_quantity' => $cartProduct->quantity,
                        'branch_id' => $this->session->userdata('BRANCHid')
                    );

                    $this->db->insert('tbl_currentinventory', $currentBranchInventory);
                } else {
                    $this->db->query("
                                update tbl_currentinventory 
                                set transfer_from_quantity = transfer_from_quantity + ? 
                                where product_id = ? 
                                and branch_id = ?
                            ", [$cartProduct->quantity, $cartProduct->product_id, $this->session->userdata('BRANCHid')]);
                }

                $transferToBranchInventoryCount = $this->db->query("select * from tbl_currentinventory where product_id = ? and branch_id = ?", [$cartProduct->product_id, $data->transfer->transfer_to])->num_rows();
                if ($transferToBranchInventoryCount == 0) {
                    $transferToBranchInventory = array(
                        'product_id'           => $cartProduct->product_id,
                        'transfer_to_quantity' => $cartProduct->quantity,
                        'branch_id'            => $data->transfer->transfer_to
                    );

                    $this->db->insert('tbl_currentinventory', $transferToBranchInventory);
                } else {
                    $this->db->query("
                                update tbl_currentinventory
                                set transfer_to_quantity = transfer_to_quantity + ?
                                where product_id = ?
                                and branch_id = ?
                            ", [$cartProduct->quantity, $cartProduct->product_id, $data->transfer->transfer_to]);
                }

                foreach ($cartProduct->SerialStore as $value) {
                    $serial = array(
                        'ps_brunch_id'          =>  $data->transfer->transfer_to,
                        'transfer_details_id'   =>  $d_id
                    );
                    $this->db->where('ps_id', $value->ps_id)->update('tbl_product_serial_numbers', $serial);
                }
            }
            $res = ['success' => true, 'message' => 'Transfer updated'];
        } catch (Exception $ex) {
            $res = ['success' => false, 'message' => $ex->getMessage()];
        }

        echo json_encode($res);
    }

    public function updateStatus()
    {
        try {
            $data = json_decode($this->input->raw_input_stream);
            $oldTransfer = $this->db->query("select * from tbl_transfermaster where transfer_id = ?", $data->transfer_id)->row();
            $transferData = array(
                'Status' => 'a'
            );
            $this->db->where('transfer_id', $data->transfer_id);
            $this->db->update('tbl_transfermaster', $transferData);

            $transferDetails = $this->db->query("SELECT td.* FROM tbl_transferdetails td WHERE td.transfer_id = ?", $data->transfer_id)->result();
            foreach ($transferDetails as $cartProduct) {
                $currentBranchInventoryCount = $this->db->query("select * from tbl_currentinventory where product_id = ? and branch_id = ?", [$cartProduct->product_id, $oldTransfer->transfer_from])->num_rows();
                if ($currentBranchInventoryCount == 0) {
                    $currentBranchInventory = array(
                        'product_id' => $cartProduct->product_id,
                        'transfer_from_quantity' => $cartProduct->quantity,
                        'branch_id' => $oldTransfer->transfer_from
                    );

                    $this->db->insert('tbl_currentinventory', $currentBranchInventory);
                } else {
                    $this->db->query("
                            update tbl_currentinventory 
                            set transfer_from_quantity = transfer_from_quantity + ? 
                            where product_id = ? 
                            and branch_id = ?
                        ", [$cartProduct->quantity, $cartProduct->product_id, $oldTransfer->transfer_from]);
                }

                $transferToBranchInventoryCount = $this->db->query("select * from tbl_currentinventory where product_id = ? and branch_id = ?", [$cartProduct->product_id, $oldTransfer->transfer_to])->num_rows();
                if ($transferToBranchInventoryCount == 0) {
                    $transferToBranchInventory = array(
                        'product_id' => $cartProduct->product_id,
                        'transfer_to_quantity' => $cartProduct->quantity,
                        'branch_id' => $oldTransfer->transfer_to
                    );

                    $this->db->insert('tbl_currentinventory', $transferToBranchInventory);
                } else {
                    $this->db->query("
                            update tbl_currentinventory
                            set transfer_to_quantity = transfer_to_quantity + ?
                            where product_id = ?
                            and branch_id = ?
                        ", [$cartProduct->quantity, $cartProduct->product_id, $oldTransfer->transfer_to]);
                }

                $transferproductserial = $this->db->query("select * from tbl_transfer_productserial where transferdetail_id = ?", [$cartProduct->transferdetails_id])->result();
                if (count($transferproductserial) > 0) {
                    foreach ($transferproductserial as $value) {
                        $serial = array(
                            'ps_brunch_id'          =>  $oldTransfer->transfer_to,
                            'transfer_details_id'   =>  $value->transferdetail_id
                        );
                        $this->db->where('ps_id', $value->ps_id)->update('tbl_product_serial_numbers', $serial);
                        $transferhistory_serial = array(
                            'ps_serial_number'   => $value->ps_serial_number,
                            'transferdetails_id' => $value->transferdetail_id,
                            'transfer_from'      => $oldTransfer->transfer_from,
                            'transfer_to'        => $oldTransfer->transfer_to,
                        );
                        $this->db->insert('tbl_transferserial_history', $transferhistory_serial);

                        $this->db->query("DELETE FROM tbl_transfer_productserial WHERE id = ?", $value->id);
                    }
                }
            }
            $res = ['success' => true, 'message' => 'Product Transfer Received'];
            echo json_encode($res);
        } catch (Exception $ex) {
            $res = ['success' => false, 'message' => $ex->getMessage()];
            echo json_encode($res);
        }
    }

    public function transferList()
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }
        $data['title'] = "Transfer List";
        $data['content'] = $this->load->view('Administrator/transfer/transfer_list', $data, true);
        $this->load->view('Administrator/index', $data);
    }

    public function receivedList()
    {
        $access = $this->mt->userAccess();
        if (!$access) {
            redirect(base_url());
        }
        $data['title'] = "Received List";
        $data['content'] = $this->load->view('Administrator/transfer/received_list', $data, true);
        $this->load->view('Administrator/index', $data);
    }

    public function getTransfers()
    {
        $data = json_decode($this->input->raw_input_stream);

        $clauses = "";
        if (isset($data->branch) && $data->branch != '') {
            $clauses .= " and tm.transfer_to = '$data->branch'";
        }

        if ((isset($data->dateFrom) && $data->dateFrom != '') && (isset($data->dateTo) && $data->dateTo != '')) {
            $clauses .= " and tm.transfer_date between '$data->dateFrom' and '$data->dateTo'";
        }

        if (isset($data->transferId) && $data->transferId != '') {
            $clauses .= " and tm.transfer_id = '$data->transferId'";
        }

        $transfers = $this->db->query("
                select
                    tm.*,
                    b.Brunch_name as transfer_to_name,
                    e.Employee_Name as transfer_by_name
                from tbl_transfermaster tm
                join tbl_brunch b on b.brunch_id = tm.transfer_to
                join tbl_employee e on e.Employee_SlNo = tm.transfer_by
                where tm.transfer_from = ? $clauses
            ", $this->session->userdata('BRANCHid'))->result();

        if (isset($data->searchBy) && $data->searchBy == 'with') {
            foreach ($transfers as $key => $item) {
                $item->transferDetails = $this->db->query("SELECT
                                                    td.*,
                                                    p.Product_Name,
                                                    p.Product_Code,
                                                    p.Product_Purchase_Rate,
                                                    p.Product_SellingPrice
                                                FROM tbl_transferdetails td
                                                LEFT JOIN tbl_product p ON p.Product_SlNo = td.product_id
                                                WHERE td.transfer_id = ?", $item->transfer_id)->result();
            }
        }

        echo json_encode($transfers);
    }

    public function getTransferDetails()
    {
        $data = json_decode($this->input->raw_input_stream);
        $transferDetails = $this->db->query("
                select 
                    td.*,
                    p.Product_Code,
                    p.Product_Name,
                    pc.ProductCategory_Name
                from tbl_transferdetails td
                join tbl_product p on p.Product_SlNo = td.product_id
                left join tbl_productcategory pc on pc.ProductCategory_SlNo = p.ProductCategory_ID
                where td.transfer_id = ?
            ", $data->transferId)->result();

        $transferDetails = array_map(function ($saleDetail) {
            $saleDetail->serial = $this->db->query("SELECT * FROM tbl_product_serial_numbers WHERE transfer_details_id=?", $saleDetail->transferdetails_id)->result();
            return $saleDetail;
        }, $transferDetails);

        echo json_encode($transferDetails);
    }

    public function getReceives()
    {
        $data = json_decode($this->input->raw_input_stream);

        $branchClause = "";
        if ($data->branch != null && $data->branch != '') {
            $branchClause = " and tm.transfer_from = '$data->branch'";
        }

        $dateClause = "";
        if (($data->dateFrom != null && $data->dateFrom != '') && ($data->dateTo != null && $data->dateTo != '')) {
            $dateClause = " and tm.transfer_date between '$data->dateFrom' and '$data->dateTo'";
        }

        $transfers = $this->db->query("
                select
                    tm.*,
                    b.Brunch_name as transfer_from_name,
                    e.Employee_Name as transfer_by_name
                from tbl_transfermaster tm
                join tbl_brunch b on b.brunch_id = tm.transfer_from
                join tbl_employee e on e.Employee_SlNo = tm.transfer_by
                where tm.transfer_to = ? $branchClause $dateClause
            ", $this->session->userdata('BRANCHid'))->result();

        if (isset($data->searchBy) && $data->searchBy == 'with') {
            foreach ($transfers as $key => $item) {
                $item->transferDetails = $this->db->query("SELECT
                                                td.*,
                                                p.Product_Name,
                                                p.Product_Code,
                                                p.Product_Purchase_Rate,
                                                p.Product_SellingPrice
                                            FROM tbl_transferdetails td
                                            LEFT JOIN tbl_product p ON p.Product_SlNo = td.product_id
                                            WHERE td.transfer_id = ?", $item->transfer_id)->result();
            }
        }

        echo json_encode($transfers);
    }

    public function transferInvoice($transferId)
    {
        $data['title'] = 'Transfer Invoice';

        $data['transfer'] = $this->db->query("
                select
                    tm.*,
                    b.Brunch_name as transfer_to_name,
                    e.Employee_Name as transfer_by_name
                from tbl_transfermaster tm
                join tbl_brunch b on b.brunch_id = tm.transfer_to
                join tbl_employee e on e.Employee_SlNo = tm.transfer_by
                where tm.transfer_id = ?
            ", $transferId)->row();

        $transferDetails = $this->db->query("
                select
                    td.*,
                    p.Product_Code,
                    p.Product_Name,
                    pc.ProductCategory_Name
                from tbl_transferdetails td
                join tbl_product p on p.Product_SlNo = td.product_id
                join tbl_productcategory pc on pc.ProductCategory_SlNo = p.ProductCategory_ID
                where td.transfer_id = ?
            ", $transferId)->result();

        $data['transferDetails'] = array_map(function ($saleDetail) {
            $saleDetail->serial = $this->db->query("SELECT * FROM tbl_product_serial_numbers WHERE transfer_details_id=?", $saleDetail->transferdetails_id)->result();
            return $saleDetail;
        }, $transferDetails);

        $data['content'] = $this->load->view('Administrator/transfer/transfer_invoice', $data, true);
        $this->load->view('Administrator/index', $data);
    }

    public function deleteTransfer()
    {
        $res = ['success' => false, 'message' => ''];
        try {
            $data = json_decode($this->input->raw_input_stream);
            $transferId = $data->transferId;

            $oldTransfer = $this->db->query("select * from tbl_transfermaster where transfer_id = ?", $transferId)->row();
            $oldTransferDetails = $this->db->query("select * from tbl_transferdetails where transfer_id = ?", $transferId)->result();

            foreach ($oldTransferDetails as $oldDetails) {
                $serials = $this->db->query("
                            select * from tbl_product_serial_numbers 
                            where transfer_details_id = $oldDetails->transferdetails_id
                        ")->result();
                foreach ($serials as $serial) {
                    $product_serial = array(
                        'ps_brunch_id'          => $oldTransfer->transfer_from,
                        'transfer_details_id'   => null
                    );
                    $this->db->where('ps_serial_number', $serial->ps_serial_number);
                    $this->db->update('tbl_product_serial_numbers', $product_serial);
                    $this->db->query("DELETE FROM tbl_transfer_productserial WHERE ps_id = ?", $serial->ps_id);
                }
                $this->db->query("
                        update tbl_currentinventory 
                        set transfer_from_quantity = transfer_from_quantity - ? 
                        where product_id = ?
                        and branch_id = ?
                    ", [$oldDetails->quantity, $oldDetails->product_id, $this->session->userdata('BRANCHid')]);

                $this->db->query("
                        update tbl_currentinventory 
                        set transfer_to_quantity = transfer_to_quantity - ? 
                        where product_id = ?
                        and branch_id = ?
                    ", [$oldDetails->quantity, $oldDetails->product_id, $oldTransfer->transfer_to]);
            }

            $this->db->query("delete from tbl_transfermaster where transfer_id = ?", $transferId);
            $this->db->query("delete from tbl_transferdetails where transfer_id = ?", $transferId);

            $res = ['success' => true, 'message' => 'Transfer deleted'];
        } catch (Exception $ex) {
            $res = ['success' => false, 'message' => $ex->getMessage];
        }

        echo json_encode($res);
    }
}
