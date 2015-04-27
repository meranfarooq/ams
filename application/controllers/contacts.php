<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Contacts extends CI_Controller {

	var $ApiContact;

	public function __construct() {
		parent::__construct();
		if (!isUserLoggedIn()) { {
				redirect(site_url());
			}
		}

		$this->controller = strtolower(__CLASS__);
		global $aContactImportConfig;
		$this->load->model('contact_model', 'contact');
		$this->load->model('country_model', 'countries');
		  $this->load->model('list_model','list');
		$this->load->library('upload', $aContactImportConfig);
		$this->load->library('phpexcel');
		$this->load->library('PHPExcel/iofactory');
		$this->ApiContact = new ApiContact();
	}

    public function index()
    {
        redirect($this->controller.'/view');
    }

	public function view($iPage = 0)
    {
		$aParams = array();
		$aParams[ACTION_RECORD_COUNT] = true;
		$aParams['iUserId'] = getLoggedInUserId();

		#   Pagination
		global $gPagination;
		$config = $gPagination;
		$config['base_url'] = site_url($this->controller . '/' . __FUNCTION__);
		$config['total_rows'] = $this->contact->getAllContacts($aParams);
		$config['per_page'] = LISTING_PER_PAGE;
		$this->pagination->initialize($config);

		#### ----------------- ####

		$aParams[ACTION_RECORD_COUNT] = false;
		$aParams[ACTION_PAGE_OFFSET] = $iPage;

		$data = array();
		$aContacts = (array) $this->contact->getAllContacts($aParams); // $this->package->getAllPackages($aParams);

		$data['aContacts'] = $aContacts;

		$sFormAction = $this->controller . '/' . __FUNCTION__;
		$data['sFormAction'] = site_url($sFormAction);
		$data['sDeleteAction'] = site_url($this->controller . '/delete');
		$data['sEditAction'] = site_url($this->controller . '/update');
		$data['sCallFrom'] = $sFormAction;

		$this->layout->template(TEMPLATE_BASIC)->show($this->controller . '/' . __FUNCTION__, $data);
	}

	public function import() {


		$aError = array();
		$sFormAction = $this->controller . '/' . __FUNCTION__;

		if ($_FILES) {
			//d($_FILES);
			if ($_FILES['userfile']) {
				if (!$this->upload->do_upload()) {
					$error = array('error' => $this->upload->display_errors());
					$aError[] = $error['error'];
					$data['sFormAction'] = site_url($sFormAction);
					$data['aMessages'] = $aError;
					$this->layout->template(TEMPLATE_BASIC)->show($sFormAction, $data);

					return;
				} else {
					$data = $this->upload->data();
					$uploadedFilePath = $data['full_path'];
					$aData = $this->ApiContact->excelParser($uploadedFilePath);

					$aContacts = $aData['aContacts'];
					$aContactsTable = $aData['aContactsTable'];

					if ($aContactsTable) {
						//deleting the upoaded excel file
						unlink($uploadedFilePath);
						//setting conatacts data in session. 
                                                
                                                $this->session->unset_userdata(CONTACT_DATA);
						$this->session->set_userdata(CONTACT_DATA, $aContacts);
                                                //d($this->session);
                                                
						$sFormAction = $this->controller . '/' . __FUNCTION__;
						$data['sFormAction'] = site_url($sFormAction);
						$data['aContacts'] = $aContactsTable[0];

						$this->layout->template(TEMPLATE_BASIC)->show($this->controller . '/import_view', $data);
					}
				}
			}
		} else if ($this->input->post('continue_import'))
                    {
                    
			$aContacts = $this->session->userdata(CONTACT_DATA);
                        
			$aResult = array();
                        
			if ($aContacts) {
				$callFrom = $this->controller . '/' . __FUNCTION__;
				$aResult = $this->ApiContact->import($callFrom, $aContacts);
				if ($aResult['status']) {

					return setMessage($aResult['status'], array(
						'message' => getFormValidationSuccessMessage($aResult['message']),
						'redirectUrl' => site_url($this->controller . '/view')
					));
				}
			}

			return setMessage($aResult['status'], array(
				'message' => getFormValidationErrorMessage($aResult['message']),
				'redirectUrl' => site_url($this->controller . '/import')
			));
		} else {

			$sFormAction = $this->controller . '/' . __FUNCTION__;
			$data['sFormAction'] = site_url($sFormAction);
			$data['aMessages'] = $aError;
			$this->layout->template(TEMPLATE_BASIC)->show($sFormAction, $data);
		}
	}

	public function update($iContactId = 0) {
		$sFormAction = $this->controller . '/' . __FUNCTION__ . '/' . $iContactId;
		$aData = array();
		$iUserId = getLoggedInUserId();
		if ($this->input->post()) {


			$aPostedData = $this->input->post('data');
			$aPostedData['isEditMode'] = true;
			$aPostedData['contact_id'] = $iContactId;

			$sFirstName = $aPostedData['first_name'];
			$sLastName = $aPostedData['last_name'];
			$sEmail = trim($aPostedData['email']);
			$sAddress = $aPostedData['address'];
			$sCountry = $aPostedData['country'];
			$sCity = $aPostedData['city'];
			$sState = $aPostedData['state'];
			$sZipCode = $aPostedData['zip_code'];

			$aErrorMessages = array();

			if (!$sAddress) {
				$aErrorMessages[] = ERROR_ADDRESS_MAILING_REQUIRED;
			}
			if (!filter_var($sEmail, FILTER_VALIDATE_EMAIL)) {
				$aErrorMessages[] = ERROR_INVALID_EMAIL;
			}





			if ($aErrorMessages) {

				return setMessage(false, array('message' => $aErrorMessages, 'redirectUrl' => $sFormAction));
			}


			$result = $this->ApiContact->createContact($aPostedData);

			//d($result); 

			if ($result['status']) {
				return setMessage($result['status'], array('message' => getFormValidationSuccessMessage($result['message']),
					'redirectUrl' => site_url($this->controller . '/view')));
			} else {

				return setMessage($result['status'], array('message' => getFormValidationErrorMessage($result['message']),
					'redirectUrl' => $sFormAction));
			}

			//d($result);


			return setMessage(false, array('message' => $result['message'], 'redirectUrl' => $sFormAction));
		}


		$data['sFormAction']   = site_url($sFormAction);
		$data['aCountries']    = $this->countries->getCountriesDropDown();
		$data['aContact']	   = $this->contact->getContactById($iContactId);
		$data['aList']		   = $this->list->getAllListByUserId($iUserId);
		$data['aSelectedList'] = $this->list->getlistByContactId($iContactId);
		
		
		$sCustomJsPath = getAssetsPath() . JS_CREATE_CONTACT;
		$data['custom_js'] = $this->load->view('includes/js_includes.php', array('custom_js' => $sCustomJsPath), true);



		//d($data);
		// $data['aStates']            = $this->states->getStatesDropDown();


		$this->layout->template(TEMPLATE_BASIC)->show($this->controller . '/' . __FUNCTION__, $data);
	}

	public function create() {
		$sFormAction = $this->controller . '/' . __FUNCTION__;
		$aData = array();
		$iUserId = getLoggedInUserId();
		if ($this->input->post()) {
			//$redirectUrlVerify 	= site_url($this->controller.'/verify');

			$aPostedData = $this->input->post('data');

			$aPostedData['isEditMode'] = false;
			$sFirstName = $aPostedData['first_name'];
			$sLastName = $aPostedData['last_name'];
			$sEmail = trim($aPostedData['email']);
			$sAddress = $aPostedData['address'];
			$sCountry = $aPostedData['country'];
			$sCity = $aPostedData['city'];
			$sState = $aPostedData['state'];
			$sZipCode = $aPostedData['zip_code'];

			$aErrorMessages = array();

			if (!$sAddress) {
				$aErrorMessages[] = ERROR_ADDRESS_MAILING_REQUIRED;
			}
			if (!filter_var($sEmail, FILTER_VALIDATE_EMAIL)) {
				$aErrorMessages[] = ERROR_INVALID_EMAIL;
			}





			if ($aErrorMessages) {

				return setMessage(false, array('message' => $aErrorMessages, 'redirectUrl' => $sFormAction));
			}


			$result = $this->ApiContact->createContact($aPostedData);

			//d($result); 

			if ($result['status']) {
				return setMessage($result['status'], array('message' => getFormValidationSuccessMessage($result['message']),
					'redirectUrl' => site_url($this->controller . '/view')));
			} else {

				return setMessage($result['status'], array('message' => getFormValidationErrorMessage($result['message']),
					'redirectUrl' => $sFormAction));
			}

			//d($result);


			return setMessage(false, array('message' => $result['message'], 'redirectUrl' => $sFormAction));
		}


		$data['sFormAction'] = site_url($sFormAction);
		$data['aCountries']  = $this->countries->getCountriesDropDown();
		$data['aList']		 = $this->list->getAllListByUserId($iUserId);
		$sCustomJsPath = getAssetsPath() . JS_CREATE_CONTACT;
		$data['custom_js'] = $this->load->view('includes/js_includes.php', array('custom_js' => $sCustomJsPath), true);

		// $data['aStates']            = $this->states->getStatesDropDown();


		$this->layout->template(TEMPLATE_BASIC)->show($this->controller . '/' . __FUNCTION__, $data);
	}

	public function delete($iContactId = 0) {
		if ($iContactId) {
			$result = $this->ApiContact->deleteContactById($iContactId);

			if ($result['status']) {
				return setMessage($result['status'], array('message' => getFormValidationSuccessMessage($result['message']),
					'redirectUrl' => site_url($this->controller . '/view')));
			} else {
				return setMessage($result['status'], array('message' => getFormValidationErrorMessage($result['message']),
					'redirectUrl' => site_url($this->controller . '/view')));
			}
		}

		redirect(site_url($this->controller . '/view'));
	}

}