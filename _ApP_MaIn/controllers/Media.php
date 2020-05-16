<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Media extends CI_Controller
{
    /**
     * mian controller for manage media
     *
     * @param [type] $maker in set mine get mine media from seesion else
     * @param [type] $id id from type
     * @param [type] $type type of prd exam prd user company and ...
     */
    public function manage($maker = 0, $type = 0, $id = 0)
    {
        $this->permision->check("media_manage", 0, 1);
        $permision = array(
            "add" => $this->permision->check("media_add"),
            "edit" => $this->permision->check("media_edit"),
        );
        $maker = ($maker) ? $maker : $this->input->post("maker", true);
        $id = ($id) ? $id : $this->input->post("id", true);
        $type = ($type) ? $type : $this->input->post("type", true);
        //set zerp
        $maker = intval($maker);
        $id = intval($id);
        $data =array( "permision" => $permision, "maker" => $maker, "id" => $id, "type" => $type );
        if ( $this->input->post("noheader") == true) $this->template->load_popup("media/manage",_MANAGE_MEDIA, $data );
        else $this->template->load("media/manage", $data );
    }

    /**
     * load ajax files
     *
     * @param int $maker
     * @param int $id
     * @param string $type [user,prd]
     * @return void
     */
    public function load($maker = 0, $type = 0, $id = 0)
    {
        $this->load->helper('text');
        $this->permision->check("media_manage", 0, 1);
        $this->load->model("Media_model");
        $res = $this->Media_model->manage($maker, $type, $id);
        foreach ($res as $key => $value) {
            $value->des=word_limiter($value->des,4);
            $this->load->view("media/card", $value);
        }

    }
    // upload to file and database
    public function upload($type = 0, $id = 0)
    {
        $ret = $this->system->upload();
        if ($ret['status'] == true) {
            $this->load->model('Media_model');
            $this->Media_model->add($ret['data'], $type, $id);
            jsonOut(true);
        } else {
            jsonOut($ret);
        }
    }
    //------------------------show full detail about media
    public function show($id, $op = null)
    {
        $this->load->model('Media_model');
        if ($op == "edit") {
            if ($this->Media_model->edit_media($id, $this->input->post())) {
                jsonOut(true);
            } else {
                jsonOut(false);
            }

        }
        $data = $this->Media_model->getDetails($id);
        $relatedDetail = $this->Media_model->get_type($data->type,$data->type_id);
        $this->template->load_popup("media/show", _VIEW_MEDIA . __ . $id, array("data" => $data , "rel" => $relatedDetail));
    }
    //------------------------ manage group of media
    public function manage_group()
    {
        $this->permision->check("media_manage_group", 0, 1);
        $user_id = $this->system->get_user();
        //load for data table
        $this->load->library("Crud");
        $this->crud->table = "media_group";
        $this->crud->title = _MANAGE_GROUP_MEDIA;
        $this->crud->column_order = array("id", "name", "des");
        $this->crud->column_title = array(_ID, _NAME, _DES);
        $this->crud->column_require = array(2, 1, 0);
        $this->crud->column_type = array("hide", "input", "input");
        $this->crud->column_search = array("name", "des");
        $this->crud->render();
    }
}