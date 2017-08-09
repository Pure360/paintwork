<?php

include "functions.php";

class ajax
{
	protected $wsdlUrl = "http://";
	protected $username = "";
	protected $password = "";
	
	function process($function = null)
	{
		$this->wsdlUrl		= $_POST["wsdl"];
		$this->userName		= $_POST["userName"];
		$this->password		= $_POST["password"];
		$this->contextId	= $_POST["contextId"];

		// Set cookies for next visit
		setcookie("wsdl", $this->wsdlUrl);
		setcookie("userName", $this->userName); 

		try
		{
			switch ($function)
			{
				case "loadMessage" : 
				{
					$doc = new DOMDocument();
					$doc->load($_POST["filename"]);
					$xpath = new DOMXpath($doc);
					
					// Update ContextId
					$nodeList = $xpath->query("//contextId");
					
					if($nodeList->length > 0)
					{
						$nodeList->item(0)->nodeValue = $this->contextId;
					}
					
					// Update Username
					$nodeList = $xpath->query("//entityData/item[key='userName']/value");

					if($nodeList->length > 0)
					{
						$nodeList->item(0)->nodeValue = $this->userName;
					}
					
					// Update Password
					$nodeList = $xpath->query("//entityData/item[key='password']/value");

					if($nodeList->length > 0)
					{
						$nodeList->item(0)->nodeValue = $this->password;
					}

					$message = pretty_print($doc->saveXML());
					
					print $message;
					break;
				}
				case "postMessage" : 
				{
					$doc = new DOMDocument();
					$doc->load($this->wsdlUrl);
					$xpath = new DOMXpath($doc);
					
					$locationNodes = $xpath->query("//soap:address/@location");
					$location = $locationNodes->item(0)->value;
					
					$soap_do = curl_init(); 
					curl_setopt($soap_do, CURLOPT_URL,            $location);   
					curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10); 
					curl_setopt($soap_do, CURLOPT_TIMEOUT,        10); 
					curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
					curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);  
					curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false); 
					curl_setopt($soap_do, CURLOPT_POST,           true ); 
					curl_setopt($soap_do, CURLOPT_POSTFIELDS,    $_REQUEST["message"]); 
					curl_setopt($soap_do, CURLOPT_HTTPHEADER,     array('Content-Type: text/xml; charset=utf-8', 'Content-Length: '.strlen($_REQUEST["message"]) )); 
					
					$result = curl_exec($soap_do);
					$err = curl_error($soap_do);  

					print pretty_print($result);
					break;
				}
				case "saveTemplate" : 
				{
					$doc = new DOMDocument();
					$doc->loadXml($_REQUEST["message"]);
					$xpath = new DOMXpath($doc);
				
					// Update ContextId
					$nodeList = $xpath->query("//contextId");
					
					if($nodeList->length > 0)
					{
						$nodeList->item(0)->nodeValue = "01234567890";
					}
					
					$message = pretty_print($doc->saveXML());
					
					$templateName = $_REQUEST["newTemplateName"];
					$templateName = str_replace(".xml","", $templateName) . ".xml";
					
					file_put_contents ("./templates/" . $templateName, $message );
				
					print $templateName . " saved!";
					
					break;
				}
				case "listTemplates" : 
				{
					$output = "<option value=\"select\">Load Template:</option>";
					
					$templates = get_filenames('templates');
				
					foreach ($templates as $template)
					{
						if(endsWith($template, ".xml"))
						{
							$output .= "<option>" . $template . "</option>";
						}
					}
					print $output;
					
					break;
				}
			}
		}
		catch (Exception $e)
		{
			print $e->getMessage();
		}
	}
}

$function = empty($_REQUEST["function"]) ? null : $_REQUEST["function"];

$ajax = new ajax();
$ajax->process($function);