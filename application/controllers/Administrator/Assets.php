<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Assets extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->brunch = $this->session->userdata('BRANCHid');
        $access = $this->session->userdata('userId');
        $this->accountType = $this->session->userdata('accountType');
         if($access == ''){
            redirect("Login");
        }  

        $this->load->model('Model_table', "mt", TRUE);
    }

    public function index() {
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }
        $data['title'] = "Assets Entry";
        $data['content'] = $this->load->view('Administrator/assets/assets_entry', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function addAsset() {
        $res = ['success' => false, 'message' => ''];

        try {
            $assetObj = json_decode($this->input->raw_input_stream);
            $asset = (array)$assetObj;
            unset($asset['as_id']);
            $asset['status'] = 'a';
            $asset['AddBy'] = $this->session->userdata("FullName");
            $asset['AddTime'] = date('Y-m-d H:i:s');
            $asset['branchid'] = $this->brunch;

            $this->db->insert('tbl_assets', $asset);

            $res = ['success' => true, 'message' => 'Asset added'];
        } catch(Exception $ex) {
            $res = ['success' => false, 'message' => $ex->getMessage()];
        }

        echo json_encode($res);
    }

    public function updateAsset() {
        $res = ['success' => false, 'message' => ''];

        try {
            $assetObj = json_decode($this->input->raw_input_stream);
            $asset = (array)$assetObj;
            unset($asset['as_id']);
            $asset['status'] = 'a';
            $asset['branchid'] = $this->brunch;

            $this->db->where(['as_id' => $assetObj->as_id])->update('tbl_assets', $asset);

            $res = ['success' => true, 'message' => 'Asset updated'];
        } catch(Exception $ex) {
            $res = ['success' => false, 'message' => $ex->getMessage()];
        }

        echo json_encode($res);
    }

    public function deleteAsset() {
        $res = ['success' => false, 'message' => ''];

        try {
            $data = json_decode($this->input->raw_input_stream);

            $this->db->set(['status' => 'd'])->where(['as_id' => $data->assetId])->update('tbl_assets');

            $res = ['success' => true, 'message' => 'Asset deleted'];
        } catch(Exception $ex) {
            $res = ['success' => false, 'message' => $ex->getMessage()];
        }

        echo json_encode($res);
    }

    public function getAssets() {
        $assets = $this->db->query("
            select * from tbl_assets where status = 'a' and branchid = ?
        ", $this->brunch)->result();

        echo json_encode($assets);
    }
}
