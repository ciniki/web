<?php
//
// Description
// -----------
// This function will generate the members page for the business.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageSurveys($ciniki, $settings) {

	//
	// Store the content created by the page
	// Make sure everything gets generated ok before returning the content
	//
	$content = '';
	$page_content = '';
	$page_title = 'Surveys';
	$err = array();

	//
	// FIXME: Check if anything has changed, and if not load from cache
	//

	//
	// Check if a survey was requested from a mail invite
	//
	if( isset($ciniki['request']['uri_split'][0]) 
		&& ($ciniki['request']['uri_split'][0] == 'invite' || $ciniki['request']['uri_split'][0] == 'submit') 
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' 
		) {
		$survey_permalink = $ciniki['request']['uri_split'][1];

		//
		// check if the answers were submitted, otherwise pull the survey from the invite
		//
		if( $ciniki['request']['uri_split'][0] == 'submit' ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'surveys', 'web', 'submitAnswers');
			$rc = ciniki_surveys_web_submitAnswers($ciniki, $settings, 
				$ciniki['request']['business_id'], $survey_permalink, $_POST);
		} else {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'surveys', 'web', 'inviteDetails');
			$rc = ciniki_surveys_web_inviteDetails($ciniki, $settings, 
				$ciniki['request']['business_id'], $survey_permalink);
		}
		$display_form = 'yes';
		// Check the response codes
		if( $rc['stat'] == 'expired' ) {
			$page_title = 'Survey Expired';
			$page_content .= "I'm sorry, the requested survey is no longer available.";
			$display_form = 'no';
		} elseif( $rc['stat'] == 'noexist' ) {
			$page_title = "Survey Error";
			$page_content .= "I'm sorry, the requested survey does not exist.";
			$display_form = 'no';
		} elseif( $rc['stat'] == 'closed' ) {
			$page_title = "Survey Closed";
			$page_content .= "I'm sorry, this survey is now closed.";
			$display_form = 'no';
		} elseif( $rc['stat'] == 'used' ) {
			$page_title = "Survey Completed";
			$page_content .= "I'm sorry, you have already completed this survey.";
			$display_form = 'no';
		} elseif( $rc['stat'] != 'ok' ) {
			return $rc;
		} else {
			$survey = $rc['survey'];
			$page_title = $survey['name'];
			
			if( $ciniki['request']['uri_split'][0] == 'submit' ) {
				$page_content .= "Thank you for taking the survey, your answers have been received.";
				$display_form = 'no';
			}
		}

		// If there were no other messages, display the survey form
		if( $display_form == 'yes' && isset($survey) ) {
			$page_content .= "<form action='" . $ciniki['request']['base_url'] . "/surveys/submit/$survey_permalink' method='post'>\n";
			if( isset($survey['instructions']) && $survey['instructions'] != '' ) {
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
				$rc = ciniki_web_processContent($ciniki, $survey['instructions']);	
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content .= $rc['content'];
			}
			foreach($survey['questions'] as $qid => $question) {
				$question = $question['question'];
				$page_content .= "<div class='input'><label for='question-" . $question['id'] . "'>"
					. $question['number'] . '.  ' . $question['question']
					. "</label>";
				$value = '';
				if( isset($answers[$question['id']]) ) {
					$value = $answers[$question['id']];
				}
				if( $question['type'] == 10 ) {
					$page_content .= "<input type='text' class='text' name='question-" . $question['id'] . "' value='$value'>";
				}
				if( isset($err['question-' . $question['id']]) ) {
					$page_content .= "<p class='formerror'>" . $err['question-' . $question['id']] . "</p>";
				}
				$page_content .= "</div>";
			}

			$page_content .= "<div class='submit'><input type='submit' class='submit' name='submit' value='Submit'></div>";
		}
		// $page_content .= "<p class='formhelp'>If you don't have a business name, just use your first and last name.</p></div>";

		$page_content = "<article class='page'>\n"
			. "<header class='entry-title'><h1 id='entry-title' class='entry-title'>$page_title</h1></header>\n"
			. "<div class='entry-content'>\n"
			. $page_content
			. "</div>\n"
			. "</article>\n";
	}

	//
	// Display the list of members if a specific one isn't selected
	//
	else {
		return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1070', 'msg'=>'No surveys available'));
	}

	//
	// Generate the complete page
	//

	//
	// Add the header
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, $page_title, array());
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	$content .= "<div id='content'>\n"
		. $page_content
		. "</div>"
		. "";

	//
	// Add the footer
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageFooter');
	$rc = ciniki_web_generatePageFooter($ciniki, $settings);
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	return array('stat'=>'ok', 'content'=>$content);
}
?>
