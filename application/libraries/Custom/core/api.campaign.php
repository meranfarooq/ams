<?php

class ApiCampaign
{
	# Initializing Class Variables
	public $data = array();
	public $result = array('status' => false, 'message' => MSG_INVALID_ATTEMPT);

	function __construct($Data = array())
    {
		$this->data = $Data;
	}

	function create($aData= array())
    {
        if($aData)      $aPostedData = $aData;
        else            $aPostedData = (array) $this->data['data'];

		# Must Required Fields
		$sCampaignTitle             = $aPostedData['title'];
        $sCampaignDescription       = $aPostedData['description'];

		$aErrorMessages         = array();

		if (!$sCampaignTitle)
        {
			$aErrorMessages[] = CAMPAIGN.' '.ERROR_TITLE_REQUIRED;
		}

		if (!$sCampaignDescription)
        {
			$aErrorMessages[] = CAMPAIGN.' '.ERROR_DESC_REQUIRED;
		}

        if($aErrorMessages)
        {
            $this->result['message'] = $aErrorMessages;
            return $this->result;
        }

        $CI = & get_instance();
        $CI->load->model('campaign_model','campaign');

        if(getUserRoleById() == ROLE_ID_SUBSCRIBER or getUserRoleById() == ROLE_ID_ADMINISTRATOR)
        {
            $aDataToSave                    = array('aData' => $aPostedData,'isEditMode' => false);
            $iCampaignId                    = $CI->campaign->createCampaign(__FUNCTION__,$aDataToSave );

            if($iCampaignId)
            {
                $this->result['status']         = true;
                $this->result['iCampaignId']    = $iCampaignId;
                $this->result['message']        = CAMPAIGN.' '.'created successfully.';
            }
        }

		return $this->result;
	}

    function UpdateBasicInfo($CoreBasicInfo = array())
    {
        $CI = & get_instance();
        $CI->load->model('user_model','users');

        $SessionData                = getLoggedInUserData();
        $SessionData['first_name']  = $CoreBasicInfo['first_name'];
        $SessionData['last_name']   = $CoreBasicInfo['last_name'];
        $CI->session->set_userdata(SESS_USERDATA, $SessionData);
        $SessionData = getLoggedInUserData();

        return $CI->users->UpdateBasicInfo(__FUNCTION__, $CoreBasicInfo);
    }


    function SaveUserProfile($aData= array())
    {
        $CI = & get_instance();
        $CI->load->model('user_model','users');

        if($CI->users->SaveUserInfo(__FUNCTION__,$aData))
        {
            $this->result['status']     = true;
            $this->result['message']    = lang('ApiAdmin_ProfileUpdateSuccessfully');
        }

        return $this->result;
    }

    function getAll($aData= array())
    {
        $CI = & get_instance();
        $CI->load->model('package_model','package');
        
        return $CI->package->getAllPackages($aData);
        
    }

    function delete($aData= array())
    {
        $CI = & get_instance();
        $CI->load->model('campaign_model','campaign');

        if($CI->campaign->deleteCampaignById($aData['iCampaignId']))
        {
            $this->result['status']     = true;
            $this->result['message']    = CAMPAIGN.' deleted successfully.';
        }
        else
        {
            $this->result['message']    = CAMPAIGN.' delete failed.';
        }
        return $this->result;
    }


    function updateCampaign($aPostedData = array())
    {
        # Must Required Fields
        $sCampaignTitle             = $aPostedData['title'];
        $sCampaignDescription       = $aPostedData['description'];

        $aErrorMessages             = array();

        if (!$sCampaignTitle)
        {
            $aErrorMessages[] = CAMPAIGN.' '.ERROR_TITLE_REQUIRED;
        }

        if (!$sCampaignDescription)
        {
            $aErrorMessages[] = CAMPAIGN.' '.ERROR_DESC_REQUIRED;
        }

        if($aErrorMessages)
        {
            $this->result['message'] = $aErrorMessages;
            return json_encode($this->result);
        }

        $CI = & get_instance();
        $CI->load->model('campaign_model','campaign');

        if(getUserRoleById() == ROLE_ID_SUBSCRIBER or getUserRoleById() == ROLE_ID_ADMINISTRATOR)
        {
            $aDataToSave                    = array('aData' => $aPostedData,'isEditMode' => true);
            $bUpdated                       = $CI->campaign->createCampaign(__FUNCTION__,$aDataToSave );

            if($bUpdated)
            {
                $this->result['status']         = true;
                $this->result['message']        = CAMPAIGN.' '.'updated successfully.';
                $this->result['aData']          = $aPostedData;
            }
        }

        return json_encode($this->result);
    }
}