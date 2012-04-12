<?php


class PostalMethods
{
	private $messageid		=	0;
	private $username;
	private $password;
	private $filename 		=	null;
	private $filedata;
	
	private $attentionline1	=	"";
	private $attentionline2	=	"";
	private $company		=	"";
	private $address1		=	"";
	private $address2		=	"";
	private $city			=	"";
	private $state			=	"";
	private $zip			=	"";
	private $country		=	"";
	
	private $description 	=	"No Description";
	private $mode			=	'Default'; // Default, Production, or Development.
	
	private $apiendpoint = 'https://api.postalmethods.com/2009-02-26/PostalWS.asmx?WSDL';

	public function __construct($username,$password)
	{
		$this->username = $username;
		$this->password = $password;
	}
	
	public function setFile($filename)
	{
		$this->filename = $filename;
		if( !($fp = fopen($filename, "rb")))
		{
			return false;
		}
		$this->filedata = "";
		while (!feof($fp)) $this->filedata .= fread($fp,1024);
		fclose($fp);
	}
	
	public function setMode($mode)
	{
		if($mode == 'Production')
			$this->mode = 'Production';
		else if($mode == 'Development')
			$this->mode = 'Development';
		else
			$this->mode = 'Default';
	}

	public function setDescription($description)
	{
		$this->description = $description;
	}
	
	public function setAddress($attentionline1, $attentionline2="", $company="", $address1, $address2="", $city, $state, $zip, $country ="USA")
	{
		$this->attentionline1 	= $attentionline1;
		$this->attentionline2 	= $attentionline2;
		$this->company 			= $company;
		$this->address1 		= $address1;
		$this->address2 		= $address2;
		$this->city 			= $city;
		$this->state 			= $state;
		$this->zip 				= $zip;
		$this->country 			= $country;
	}
	public function setMessageID($messageid)
	{
		$this->messageid = $messageid;
	}
	public function getMessageID()
	{
		return $this->messageid;
	}
	
	public function getPDF($filename, $messageid = 0)
	{
		//Remember this call needs some time for it to process, you need to wait for this to be ready!
		
		if($messageid==0 && $this->messageid ==0)
		{
			throw new Exception('No message ID specified!');
		}
		else if($messageid==0 && $this->messageid !=0)
		{
			$messageid = $this->messageid;
		}
		else if($messageid !=0)
		{
			$this->setMessageID($messageid);
		}
		$soapclient = new SoapClient($this->apiendpoint);
		$result = $soapclient->GetPDF(array(
				'Username'	=> $this->username,
		        'Password'	=> $this->password,
		        'ID'   		=> $this->messageid
		));
		// Extract returned fields
		$statusCode = $result->GetPDFResult->ResultCode;
		$file_data   = $result->GetPDFResult->FileData;
		if($this->_processStatusCode($statusCode))
		{
			if (!$handle = fopen($filename, 'w'))
				throw new Exception("Cannot open file ($filename)");
	        if (fwrite($handle, $file_data) === FALSE)
	        	throw new Exception("Cannot write to file $filename");
	        fclose($handle);
		}
	
	}
	
	
	private function _processStatusCode($statusCode)
	{
		$errors = array(
			-900	=>	'Received',
			-910	=>	'In Process',
			-990	=>	'Not enough funds available',
			-995	=>	'Waiting For Delivery',
			-1000	=>	'Completed: successfully delivered to the postal agency',
			-1002	=>	'Completed: successfully completed in "Development" work mode',
			-1005	=>	'Actively canceled by user',
			-1010	=>	'Failed: no funds available to process the letter',
			-1018	=>	'Failed: Provided US address cannot be verified',
			-1021	=>	'Failed: Invalid page size',
			-1025	=>	'Failed: A document could not be processed',
			-1045	=>	'Failed: Recipient postal address could not be extracted from the document',
			-1065	=>	'Failed: Too many sheets of paper',
			-1099	=>	'Failed: Internal Error',
			/*-3000	=>	'OK', //Not really an "Error" */
			-3001	=>	'This user is not authorized to access the specified item',
			-3002	=>	'User was actively blocked',
			-3003	=>	'Not Authenticated',
			-3004	=>	'The specified file extension is not supported',
			-3010	=>	'Rejected: no funds available to the account',
			-3020	=>	'Rejected: file specified is currently unavailable',
			-3022	=>	'Cancellation Denied: The letter was physically processed and cannot be cancelled or is already cancelled',
			-3113	=>	'Rejected: city field contains more than 30 characters',
			-3114	=>	'Rejected: state field contains more than 30 characters',
			-3115	=>	'Warning: no data was returned for you query',
			-3116	=>	'Warning: the specified letter is unavailable',
			-3117	=>	'Rejected: Company field contains more than 45 characters',
			-3118	=>	'Rejected: Address1 field contains more than 45 characters',
			-3119	=>	'Rejected: Address2 field contains more than 45 characters',
			-3120	=>	'Rejected: AttentionLine1 field contains more than 45 characters',
			-3121	=>	'Rejected: AttentionLine2 field contains more than 45 characters',
			-3122	=>	'Rejected: AttentionLine3 field contains more than 45 characters',
			-3123	=>	'Rejected: PostalCode/ZIP field contains more than 15 characters',
			-3124	=>	'Rejected: Country field contains more than 30 characters',
			-3125	=>	'Only account administrators are allowed access to this information',
			-3126	=>	'Invalid file name',
			-3127	=>	'File name already exists',
			-3128	=>	'The ImageSideFileType field is empty or missing',
			-3129	=>	'The AddressSideFileType field is empty or missing',
			-3130	=>	'Unsupported file extension in ImageSideFileType',
			-3131	=>	'Unsupported file extension in AddressSideFileType',
			-3132	=>	'The ImageSideBinaryData field is empty or missing',
			-3133	=>	'The AddressSideBinaryData field is empty or missing',
			-3134	=>	'File name provided in ImageSideFileType does not exist for this user',
			-3135	=>	'File name provided in AddressSideFileType does not exist for this user',
			-3136	=>	'Image side: One or more of the fields is missing from the template',
			-3137	=>	'Address side: One or more of the fields is missing from the template',
			-3138	=>	'Image side: The XML merge data is invalid',
			-3139	=>	'Address side: The XML merge data is invalid',
			-3142	=>	'Image side: This file cannot be used as a template',
			-3143	=>	'Address side: This file cannot be used as a template',
			-3144	=>	'The XML merge data is invalid',
			-3145	=>	'One or more of the fields in the XML merge data is missing from the selected template',
			-3146	=>	'Specified pre-uploaded document does not exist',
			-3147	=>	'Uploading a file and a template in the same request is not allowed',
			-3209	=>	'No more users allowed',
			-3210	=>	'Last administrator for account',
			-3211	=>	'User does not exist for this account',
			-3212	=>	'One or more of the parameters are invalid',
			-3213	=>	'Invalid value: General_Username',
			-3214	=>	'Invalid value: General_Description',
			-3215	=>	'Invalid value: General_Timezone',
			-3216	=>	'Invalid value: General_WordMode',
			-3217	=>	'Invalid value: Security_Password',
			-3218	=>	'Invalid value: Security_AdministrativeEmail',
			-3219	=>	'Invalid value: Security_KeepContentOnServer',
			-3220	=>	'Invalid value: Letters_PrintColor',
			-3221	=>	'Invalid value: Letters_PrintSides',
			-3222	=>	'Invalid value: Postcards_DefaultScaling',
			-3223	=>	'Invalid value: Feedback_FeedbackType',
			-3224	=>	'Invalid value: Feedback_Email_WhenToSend_EmailReceived',
			-3225	=>	'Invalid value: Feedback_Email_WhenToSend_Completed',
			-3226	=>	'Invalid value: Feedback_Email_WhenToSend_Error',
			-3227	=>	'Invalid value: Feedback_Email_WhenToSend_BatchErrors',
			-3228	=>	'Invalid value: Feedback_Email_DefaultFeedbackEmail',
			-3229	=>	'Invalid value: Feedback_Email_Authentication',
			-3230	=>	'Invalid value: Feedback_Post_WhenToSend_Completed',
			-3231	=>	'Invalid value: Feedback_Post_WhenToSend_Error',
			-3232	=>	'Invalid value: Feedback_Post_WhenToSend_BatchErrors',
			-3233	=>	'Invalid value: Feedback_Post_FeedbackURL',
			-3234	=>	'Invalid value: Feedback_Post_Authentication',
			-3235	=>	'Invalid value: Feedback_Soap_WhenToSend_Completed',
			-3236	=>	'Invalid value: Feedback_Soap_WhenToSend_Error',
			-3237	=>	'Invalid value: Feedback_Soap_WhenToSend_BatchErrors',
			-3238	=>	'Invalid value: Feedback_Soap_FeedbackURL',
			-3239	=>	'Invalid value: Feedback_Soap_Authentication',
			-3240	=>	'Invalid parameters array',
			-3150	=>	'General System Error',
			-3160	=>	'File does not exist',
			-3161	=>	'Insufficient Permissions',
			-3162	=>	'Too many uploaded files',
			-3163	=>	'No files for the account',
			-3164	=>	'Only Administrator can upload file as account',
			-3165	=>	'User does not have an API key assigned',
			-3500	=>	'Warning: too many attempts were made for this method',
			-4001	=>	'The Username field is empty or missing',
			-4002	=>	'The Password field is empty or missing',
			-4003	=>	'The MyDescription field is empty or missing',
			-4004	=>	'The FileExtension field is empty or missing',
			-4005	=>	'The FileBinaryData field is empty or missing',
			-4006	=>	'The Address1 field is empty or missing',
			-4007	=>	'The City field is empty or missing',
			-4008	=>	'The Attention1 or Company fields are empty or missing',
			-4009	=>	'The ID field is empty or missing',
			-4010	=>	'The MinID field is empty or missing',
			-4011	=>	'The MaxID field is empty or missing',
			-4013	=>	'Invalid ID or IDs',
			-4014	=>	'The MergeData field is empty or missing',
			-4015	=>	'Missing field: APIKey'
		);
		if($statusCode <0 && $statusCode!=-3000)
		{
			//We have an error
			if(array_key_exists($statusCode, $errors))
			{
				throw new Exception($errors[$statusCode],$statusCode);
			
			}
			else
				throw new Exception('Unknown Error: '.$statusCode,$statusCode);		
		}
		return true;
	}
	
	
	
	public function SendLetterAndAddress()
	{
	
		$soapclient = new SoapClient($this->apiendpoint);
		$result = $soapclient->SendLetterAndAddress(array(
		        'Username'	=>	$this->username,
		        'Password'		=>	$this->password,
		        'MyDescription'	=>	$this->description,
		        'FileExtension'	=>	end(explode(".", $this->filename)),
		        'FileBinaryData'=>	$this->filedata,
		        'WorkMode'		=>	$this->mode,
		        'AttentionLine1'=>	$this->attentionline1,
		        'AttentionLine2'=>	$this->attentionline2,
		        'Company'		=>	$this->company,
		        'Address1'		=>	$this->address1,
		        'Address2'		=>	$this->address2,
		        'City'			=>	$this->city,
		        'State'			=>	$this->state,
		        'PostalCode'	=>	$this->zip,
		        'Country'		=>	$this->country
		)); 
		$statusCode = $result->SendLetterAndAddressResult;
		if($this->_processStatusCode($statusCode))
		{
			$this->setMessageID($statusCode);
			return $this->messageid;
		}
	}
	
}