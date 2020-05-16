<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by piero.ir.
 * User: pirooz jenabi
 * Date: 6/26/18
 * Time: 3:01 PM
 */
class Factor_sell_pre_model extends CI_Model
{
    public $factor_data=array();
    public function __construct()
    {
        parent::__construct();
    }
    //return all element of some facotr
    public function get_all($id,$print_format)
    {
        $this->load->library("Factor");
        //get level from factor id
        $level_pro=$this->factor->get_level_info_from_factor_id($id);

        //put  factor preview id in here and get propertis
        $print_format=($print_format)?$print_format:$level_pro["factor_preview_id"];
        $factor_pre=$this->factor->get_factor_preview($print_format);
        $this->factor_data=$this->db->get_where("factor", array('id' => $id), 1)->result_array();
        $this->db->order_by("position", "asc");
        return $this->db->get_where("factor_preview_element", array('factor_preview_id' => $factor_pre["id"],'state'=>'1'))->result_array();
    }
    // load user values for state1
    public function load_db($value,$id_factor)
    {



        $select=null;
        $result= array( );
        $propertis=$value["propertis"];
        foreach ($propertis->values as $key => $value2) {
            if (strpos($value2, "+")) {
                $imp=explode("+", $value2);
                foreach ($imp as $key3 ) {
                    $select .= $key3.",";
                }

            } else {
                $select .= $value2.",";
            }
        }
        $select=substr($select, 0, -1);
        $this->db->select($select);
        $this->db->where("id", $this->factor_data[0][$propertis->where]);
        $dbresult=$this->db->get($propertis->data_base)->result();

        if (@$value["propertis"]->factor_role_params) {
            $this->load->library("factor");
            $factor=$this->factor->get($id_factor);
            $tmp=$value["propertis"]->factor_role_params;//define type
            $tmprep=$value["propertis"]->factor_replace;//option for replace
            $tmpreped=json_decode($factor[0]["params"])->$tmp; //replaced
            if($tmpreped) {
                $dbresult[0]->$tmprep=$tmpreped ; // replace in array
            }
        }
        foreach ( $propertis->values as $key => $value2) {

            if (strpos($value2, "+")) {
                $imp=explode("+", $value2);
                $tmp="";
                foreach ($imp as $key3 ) {
                    $tmp .=$dbresult[0]->$key3;
                }
                $result[$key] =$tmp."@@";// dalil '#' font mix estefade she

            } else {
                $result[$key]=$dbresult[0]->$value2;
            }

        }

        return $result;

    }

    // id of factor
    public function factor_num($value,$id_factor)
    {
        $tmp="";
        if ($this->system->get_setting("perfix_factor")) {
            $tmp=$this->system->get_setting("perfix_factor");
        }
        return $tmp.$this->factor_data[0]["factor_id"];

    }
                // date of factor
    public function factor_date($value,$id_factor)
    {

        $this->load->library('Piero_jdate');

        @$type= $value['propertis']->type;
        $temp=$this->factor_data[0]["date"];
        if ($type=="shamsi") {
            return  $this->piero_jdate->jdate("Y/m/d", $temp);
        }

        return date("Y/m/d", $temp);
    }

                // tarikh sarresid factor
    public function expire_factor($value,$id_factor)
    {
        $this->load->library('Piero_jdate');
        $this->piero_jdate->jdate("Y/m/d");
        @$type= $value['propertis']->type;
        $temp=$this->factor_data[0]["expire_date"];
        //if time expire 0
        if ($temp==0) { return "";
        }
        //        if shamsi
        if ($type=="shamsi") {
            return  $this->piero_jdate->jdate("Y/m/d", $temp);
        }
        //        if miladi or other
        return date("Y/m/d", $temp);


    }

    //load text_html element
    public function text_html($value,$id_factor)
    {
        //text_html load preview
        return true;

    }
    // full price load
    public function total_prd($value,$id_factor)
    {



    }
    // description element
    public function des_factor($value,$id_factor)
    {
        return $this->factor_data[0]["des"];


    }
    // ezafat and kosoorat
    public function ext_factor($value,$id_factor)
    {



    }
    // all prd that sell or buy
    public function factor_prd($value,$id_factor)
    {
        $this->load->library('num2word');

        $select=null;//for make query of slelect factor_prd
        $result= array( );//for returns
        $i=1;
        //make select of enable element

        foreach ($value["propertis"]->db as $key => $value) {

            $select .= $key.",";
        }

        // get from factor prd

        $this->db->select($select);
        $this->db->order_by("radif");
        $this->db->where('id_factor', $id_factor);
        $this->db->join('prd', "prd.id=factor_prd.id_prd");
        $this->db->join('mali_prd_unit', "mali_prd_unit.id=prd.vahed_asli");
        $prds=$this->db->get("factor_prd")->result_array();;
        //fill result

        foreach ($prds as $key => $value) {

            //add here to send to view
            @$result['prd'][$i]=array('radif' => $value['radif'],'prd_id' => $value['prd_id'],'name'=>$value['name'],'takhfif'=>$value['takhfif'],'id'=>$value['id'],'num'=>$value['num'],'price'=>$value['price'],'price_client'=>$value['price_client'],'vahed_asli_name'=>$value['vahed_asli_name'],'row_plus'=>$value['row_plus'] );
            $i++;
        }
        //kosoorat
        $this->db->select("factor_kosoorat.radif,factor_kosoorat.price,mali_kosoorat.name");
        $this->db->order_by("factor_kosoorat.radif");
        $this->db->join("mali_kosoorat", "mali_kosoorat.id=factor_kosoorat.id_kosoorat");
        $this->db->where('factor_kosoorat.id_factor', $id_factor);
        $kosoorat=$this->db->get("factor_kosoorat")->result_array();
        $result["kosoorat"]=$kosoorat;
        //ezafat
        $this->db->select("factor_ezafat.radif,factor_ezafat.price,mali_ezafat.name");
        $this->db->order_by("factor_ezafat.radif");
        $this->db->join("mali_ezafat", "mali_ezafat.id=factor_ezafat.id_ezafat");
        $this->db->where('factor_ezafat.id_factor', $id_factor);
        $kosoorat=$this->db->get("factor_ezafat")->result_array();
        $result["ezafat"]=$kosoorat;


        return $result;


    }


}