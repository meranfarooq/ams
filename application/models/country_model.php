<?php

class Country_Model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

  

    public function getCountriesDropDown()
    {
        $this->db->select('country_id,title');
        $query = $this->db->get('countries');
        return $query->result_array();
        
    }    
    
  
    
//    public function deletePackagById($sPackageId = array())
//    {
//        $data = array('is_deleted' => '1');
//        $this->db->where('package_id', $sPackageId);
//        $this->db->update('packages', $data);
//        return $this->db->affected_rows();
//    }
}
