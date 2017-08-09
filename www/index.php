<?php
	// includes
	include "functions.php";
	
	$templates	= get_filenames('templates');
	$wsdl		= "http://emailapi.co.uk/emailapi.co.uk/ctrlPaint.wsdl";
	$userName	= "";
	$mode		= isset($_REQUEST['mode']) ? $_REQUEST['mode'] : "visual";
	
	// Get wsdl from cookie
	if (isset($_COOKIE['wsdl'])) 
	{ 
		$wsdl = $_COOKIE['wsdl']; 
	}
	
	// Get userName from cookie
	if (isset($_COOKIE['userName'])) 
	{ 
		$userName = $_COOKIE['userName']; 
	}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
		<meta name="description" content=""/>
		<meta name="author" content=""/>
	
		<link href="bootstrap/css/bootstrap.css" rel="stylesheet"/>
		<style type="text/css">
		<!-- 
			body 
			{
				padding-top: 60px;
			}
		  
			.CodeMirror-scroll {
				font-size: 12px;
			  overflow: auto;
			  height: auto !important;
			  position: relative;
			  border: 1px solid #ccc;
			  border: 1px solid rgba(0, 0, 0, 0.15);
			  -webkit-border-radius: 4px;
				 -moz-border-radius: 4px;
					  border-radius: 4px;
					  margin-bottom: 20px;
			}
			
			p.right { float: right; margin: 6px;}

			#beanId { width: 100%; }
			#beanIdContainer { padding-right: 10px; }
		-->
		</style>
		<link href="bootstrap/css/bootstrap-responsive.css" rel="stylesheet"/>
		<link rel="stylesheet" href="lib/codemirror.css"/>

		<script src="lib/jquery.js"></script>
		<script src="lib/codemirror.js"></script>
		<script src="lib/util/overlay.js"></script>
		<script src="mode/xml/xml.js"></script>

		<script type="text/javascript"> 
			
			var requestEditor, responseEditor;
			
			$(document).ready(function() 
			{
				
				
				if("<?= $mode ?>" == "visual")
				{
					requestEditor = CodeMirror.fromTextArea(document.getElementById("paint_request"), {
						lineNumbers: true
					});
					responseEditor = CodeMirror.fromTextArea(document.getElementById("paint_response"), {
						lineNumbers: true
					});
				}
		
				$('#select_template').bind('change', 
					function() 
					{
						if($("#select_template").val() == 'select')
						{
							setRequest("");

						} else
						{
							$("#newTemplateName").val($("#select_template").val());
							
							$.post("ajax.php", 
							{
								function : 'loadMessage', 
								wsdl : $("#wsdl").val(), 
								userName : $("#userName").val(), 
								password : $("#password").val(), 
								contextId : $("#contextId").val(),  
								filename: 'templates/' + $("#select_template").val() 
							},
							function(data) 
							{
								setRequest(data);
								setTimeout(function() {
									 $('#select_template').prop('selectedIndex',0);
								}, 5000);
								
							});
						}
					}
				);

				$("#submit").bind('click', 
					function() 
					{
						setResponse("Waiting for PAINT response ...");

						$("#busy").show();

						$.post("ajax.php", 
						{ 
							function : 'postMessage', 
							wsdl : $("#wsdl").val(), 
							userName : $("#userName").val(), 
							password : $("#password").val(),  
							contextId : $("#contextId").val(),  
							message: getRequest()
						},
						function(data) 
						{
							if($(data).find("item key:contains('bus_entity_context')").length > 0)
							{
								context = $(data).find("item key:contains('bus_entity_context')");
								beanId = $(context).parent().find("item key:contains('beanId')");
								$("#contextId").val(beanId.parent().find("value").html());
							}
							
							// Now check for Bean ID
							$("#beanId").val('No Bean ID found');
							
							var beanId = $(data).find("item key:contains('beanId')");
							
							if(beanId)
							{
								var val = $(beanId).parent().find("value");
								
								if($(val).html() !== null)
								{
									//var matches = $(val).html().match(/\d+/g);
									//if (matches != null) {
										$("#beanId").val('Bean ID: ' + $(val).html());
									//}
								}
							}

							setResponse(data);

							$("#busy").hide();
						});
					}
				);
				
				$("#saveas").bind('click', 
					function() 
					{
						$.post("ajax.php", 
						{ 
							function : 'saveTemplate', 
							newTemplateName : $("#newTemplateName").val(),  
							message: getRequest()
						},
						function(data) 
						{
							alert(data);
							updateTemplates();
							$('#myModal').modal('hide');
						});
						
						return false;			
					}
				);
				
				clearRequest();
			});
			
			function updateTemplates()
			{
				$.post("ajax.php", 
				{ 
					function : 'listTemplates'
				},
				function(data) 
				{
					$("#select_template").html(data);
				});
			}
			
			// Getters
			function getRequest()
			{
				if("<?= $mode ?>" == "visual")
				{
					return requestEditor.getValue();

				} else
				{
					return $("#paint_request").text();
				}
			}
			
			function getResponse()
			{
				if("<?= $mode ?>" == "visual")
				{
					return responseEditor.getValue();

				} else
				{
					return $("#paint_response").text();
				}
			}
			
			// Setters
			function setRequest(data)
			{
				if("<?= $mode ?>" == "visual")
				{
					requestEditor.setValue(data);

				} else
				{
					$("#paint_request").html(data);
					$("#paint_request").val(data);
				}
			}
			
			function setResponse(data)
			{
				if("<?= $mode ?>" == "visual")
				{
					responseEditor.setValue(data);

				} else
				{
					$("#paint_response").html(data);
					$("#paint_response").val(data);
				}
			}
			
			function clearRequest()
			{
				setRequest('');
				$('#select_template').prop('selectedIndex',0);
				$("#beanId").val('No Bean ID found');
			}
		</script>
	</head>

	<body>
		
	<div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
		<a class="brand" href="/" title="Home">
		  <img src="/assets/images/logo.png" alt="Home">
		</a>
          <a class="brand" href="/">Paintwork</a>
          <div class="nav-collapse collapse">
            <ul class="nav">
              <li class="active"><a href="/">reset</a></li>
              <!--li><a href="#about">About</a></li>
              <li><a href="#contact">Contact</a></li-->
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

	<div class="container">
			<form method="post">
				<div class="row">
					<div class="accordion" id="accordion2">
						<div class="accordion-group">
							<div class="accordion-heading">
								<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseOne">
										Session Details 			
								</a>
							</div>
							<div id="collapseOne" class="accordion-body collapse in">
								<div class="accordion-inner">
									<div class="control-group">
										<label class="control-label">WSDL endpoint:</label>
										<div class="controls">
											<input type="text" class="input-xxlarge" name="wsdl" id="wsdl" value="<?= $wsdl ?>"/>
										</div>
									</div>
									<div class="control-group">
										<label class="control-label">Credentials:</label>
										<input placeholder="Username" class="input-medium" name="userName" id="userName" value="<?= $userName ?>" type="text" />
										<input placeholder="Password" class="input-medium" name="password" id="password" value="" type="password" />
									</div>
									<div class="control-group">
										<label class="control-label">Context ID:</label>
										<div class="controls">
											<input placeholder="Please login!" class="input-xlarge uneditable-input" value="" name="contextId" id="contextId" type="text" />
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="span6" style="margin: 0 auto 0px;">
						<div class="well" style="margin: 10 auto 10px; height: 60px;">
							<h4>PAINT Request</h4>
							<div class="control-group">
								<select class="pull-left" id="select_template" name="select_template">
									<option value="select">Load Template:</option>
									<?
									foreach ($templates as $template)
									{
										if(endsWith($template, ".xml"))
										{
											echo "<option>" . $template . "</option>";
										}
									}
									?>
								</select>
								<input id="submit" name="submit" value="submit" class="btn btn-primary pull-right" type="button" />
								<input onclick="clearRequest()" id="clear" name="clear" value="clear" class="btn btn-danger pull-right" type="button" style="margin-right: 10px;" />
								
								<!--
									<input type="button" id="saveas" name="saveas" value="save as" class="btn btn-success pull-right" type="button" style="margin-right: 10px;" />
								-->
								
								<!-- Button to trigger modal -->
								<input type="button" data-target="#myModal" role="button" value="save as" class="btn btn-success pull-right" data-toggle="modal" style="margin-right: 10px;" />
								 
								<!-- Modal -->
								<div id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">�</button>
										<h3 id="myModalLabel">Save PAINT Request:</h3>
									</div>
									<div class="modal-body">
										<label>Please provide a name for your new template:</label><input type="text" id="newTemplateName" name="newTemplateName" value="" />
										<p><i>(Existing templates will be overwritten)</i></p>
									</div>
									<div class="modal-footer">
										<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
										<button id="saveas" name="saveas" class="btn btn-primary">Save changes</button>
									</div>
								</div>
								
								<img class="clearfix" id="busy" src="ajax-loader.gif" style="right: 10px; top: 5px; position: absolute; display: none;" />
							</div>
						</div>
						<textarea id="paint_request" name="paint_request" style="margin-bottom: 10px;" wrap='off'></textarea>
					</div>
					<div class="span6" style="margin: 0 auto 0px; float: right;">
						<div class="well" style="margin: 10 auto 10px; height: 60px;">
							<h4>PAINT Response</h4>
							<div id="beanIdContainer">
								<input placeholder="No Bean ID found" class="input-xlarge uneditable-input" value="" name="beanId" id="beanId" type="text" />
							</div>
						</div>
						<textarea id="paint_response" name="paint_response" class="max" style="margin-bottom: 10px;" wrap='off'></textarea>
					</div>
				</div>
			</form>
		</div>
		<script src="bootstrap/js/bootstrap.min.js"></script>
	</body>
</html>