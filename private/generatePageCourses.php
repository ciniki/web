<?php
//
// Description
// -----------
// This function will generate the courses page for the business.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageCourses($ciniki, $settings) {

	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
	//
	// Check if a file was specified to be downloaded
	//
	$download_err = '';
	if( isset($ciniki['business']['modules']['ciniki.courses'])
		&& isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'download'
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'courses', 'web', 'fileDownload');
		$rc = ciniki_courses_web_fileDownload($ciniki, $ciniki['request']['business_id'], $ciniki['request']['uri_split'][1]);
		if( $rc['stat'] == 'ok' ) {
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
			header('Cache-Control: no-cache, must-revalidate');
			header('Pragma: no-cache');
			$file = $rc['file'];
			if( $file['extension'] == 'pdf' ) {
				header('Content-Type: application/pdf');
			}
			header('Content-Disposition: attachment;filename="' . $file['filename'] . '"');
			header('Content-Length: ' . strlen($file['binary_content']));
			header('Cache-Control: max-age=0');

			print $file['binary_content'];
			exit;
		}
		
		//
		// If there was an error locating the files, display generic error
		//
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1103', 'msg'=>'Unable to locate file'));
	}

	//
	// Store the content created by the page
	// Make sure everything gets generated ok before returning the content
	//
	$content = '';
	$page_content = '';
	$page_title = 'Exhibitors';

	//
	// FIXME: Check if anything has changed, and if not load from cache
	//

	//
	// Check if there should be a submenu
	//
	$submenu = array();
	$first_course_type = '';
	if( isset($ciniki['business']['modules']['ciniki.courses']) ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'courses', 'web', 'courseTypes');
		$rc = ciniki_courses_web_courseTypes($ciniki, $settings, $ciniki['request']['business_id']);
		if( $rc['stat'] == 'ok' ) {
			if( count($rc['types']) > 1 ) {
				foreach($rc['types'] as $cid => $type) {
					if( $first_course_type == '' ) {
						$first_course_type = $type['name'];
					}
					if( $type != '' ) {
						$submenu[$cid] = array('name'=>$type['name'], 'url'=>$ciniki['request']['base_url'] . "/courses/" . urlencode($type['name']));
					}
				}
			} elseif( count($rc['types']) == 1 ) {
				$first_type = array_pop($rc['types']);
				$first_course_type = $first_type['name'];
			}
		}
		if( ($ciniki['business']['modules']['ciniki.courses']['flags']&0x02) == 0x02 ) {
			$submenu['instructors'] = array('name'=>'Instructors', 'url'=>$ciniki['request']['base_url'] . '/courses/instructors');
		}
		if( isset($settings['page-courses-registration-active']) && $settings['page-courses-registration-active'] == 'yes' ) {
			$submenu['registration'] = array('name'=>'Registration', 'url'=>$ciniki['request']['base_url'] . '/courses/registration');
		}
	}

	//
	// Check if we are to display the gallery image for an members
	//
	//
	// Check if we are to display an image, from the gallery, or latest images
	//
	if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'instructor' 
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' 
		&& isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] == 'gallery' 
		&& isset($ciniki['request']['uri_split'][3]) && $ciniki['request']['uri_split'][3] != '' 
		) {
		$instructor_permalink = $ciniki['request']['uri_split'][1];
		$image_permalink = $ciniki['request']['uri_split'][3];

		//
		// Load the member to get all the details, and the list of images.
		// It's one query, and we can find the requested image, and figure out next
		// and prev from the list of images returned
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'courses', 'web', 'instructorDetails');
		$rc = ciniki_courses_web_instructorDetails($ciniki, $settings, 
			$ciniki['request']['business_id'], $instructor_permalink);
		if( $rc['stat'] != 'ok' ) {
			return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1310', 'msg'=>"I'm sorry, but we can't seem to find the image you requested.", $rc['err']));
		}
		$instructor = $rc['instructor'];

		if( !isset($instructor['images']) || count($instructor['images']) < 1 ) {
			return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1104', 'msg'=>"I'm sorry, but we can't seem to find the image you requested."));
		}

		$first = NULL;
		$last = NULL;
		$img = NULL;
		$next = NULL;
		$prev = NULL;
		foreach($instructor['images'] as $iid => $image) {
			if( $first == NULL ) {
				$first = $image;
			}
			if( $image['permalink'] == $image_permalink ) {
				$img = $image;
			} elseif( $next == NULL && $img != NULL ) {
				$next = $image;
			} elseif( $img == NULL ) {
				$prev = $image;
			}
			$last = $image;
		}

		if( count($instructor['images']) == 1 ) {
			$prev = NULL;
			$next = NULL;
		} elseif( $prev == NULL ) {
			// The requested image was the first in the list, set previous to last
			$prev = $last;
		} elseif( $next == NULL ) {
			// The requested image was the last in the list, set previous to last
			$next = $first;
		}
		
		$page_title = $instructor['name'] . ' - ' . $img['title'];

		if( $img == NULL ) {
			return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1311', 'msg'=>"I'm sorry, but we can't seem to find the image you requested."));
		}
		//
		// Load the image
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
		$rc = ciniki_web_getScaledImageURL($ciniki, $img['image_id'], 'original', 0, 600);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$img_url = $rc['url'];

		//
		// Set the page to wide if possible
		//
		$ciniki['request']['page-container-class'] = 'page-container-wide';

		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateGalleryJavascript');
		$rc = ciniki_web_generateGalleryJavascript($ciniki, $next, $prev);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$ciniki['request']['inline_javascript'] = $rc['javascript'];

		$ciniki['request']['onresize'] = "gallery_resize_arrows();";
		$ciniki['request']['onload'] = "scrollto_header();";
		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 id='entry-title' class='entry-title'>$page_title</h1></header>\n"
			. "<div class='entry-content'>\n"
			. "";
		$page_content .= "<div id='gallery-image' class='gallery-image'>";
		$page_content .= "<div id='gallery-image-wrap' class='gallery-image-wrap'>";
		if( $prev != null ) {
			$page_content .= "<a id='gallery-image-prev' class='gallery-image-prev' href='" . $prev['permalink'] . "'><div id='gallery-image-prev-img'></div></a>";
		}
		if( $next != null ) {
			$page_content .= "<a id='gallery-image-next' class='gallery-image-next' href='" . $next['permalink'] . "'><div id='gallery-image-next-img'></div></a>";
		}
		$page_content .= "<img id='gallery-image-img' title='" . $img['title'] . "' alt='" . $img['title'] . "' src='" . $img_url . "' onload='javascript: gallery_resize_arrows();' />";
		$page_content .= "</div><br/>"
			. "<div id='gallery-image-details' class='gallery-image-details'>"
			. "<span class='image-title'>" . $img['title'] . '</span>'
			. "<span class='image-details'></span>";
		if( $img['description'] != '' ) {
			$page_content .= "<span class='image-description'>" . preg_replace('/\n/', '<br/>', $img['description']) . "</span>";
		}
		$page_content .= "</div></div>";
		$page_content .= "</div></article>";
	}

	//
	// Check if we are to display an instructor page
	//
	elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'instructor'
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' ) {

		ciniki_core_loadMethod($ciniki, 'ciniki', 'courses', 'web', 'instructorDetails');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
		//
		// Get the instructor information
		//
		$instructor_permalink = $ciniki['request']['uri_split'][1];
		$rc = ciniki_courses_web_instructorDetails($ciniki, $settings, 
			$ciniki['request']['business_id'], $instructor_permalink);
		if( $rc['stat'] != 'ok' ) {
			return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1312', 'msg'=>"I'm sorry, but we can't find the instructor you requested.", $rc['err']));
		}
		$instructor = $rc['instructor'];
		$page_title = $instructor['name'];
		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>" . $instructor['name'] . "</h1></header>\n"
			. "";

		//
		// Add primary image
		//
		if( isset($instructor['image_id']) && $instructor['image_id'] > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $instructor['image_id'], 'original', '500', 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<aside><div class='image-wrap'><div class='image'>"
				. "<img title='' alt='" . $instructor['name'] . "' src='" . $rc['url'] . "' />"
				. "</div></div></aside>";
		}
		
		//
		// Add description
		//
		if( isset($instructor['full_bio']) ) {
			$rc = ciniki_web_processContent($ciniki, $instructor['full_bio']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		}

		if( isset($instructor['url']) ) {
			$rc = ciniki_web_processURL($ciniki, $instructor['url']);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$url = $rc['url'];
			$display_url = $rc['display'];
		} else {
			$url = '';
		}

		if( $url != '' ) {
			$page_content .= "<br/>Website: <a class='members-url' target='_blank' href='" . $url . "' title='" . $instructor['name'] . "'>" . $display_url . "</a>";
		}
		$page_content .= "</article>";

		if( isset($instructor['images']) && count($instructor['images']) > 0 ) {
			$page_content .= "<article class='page'>"	
				. "<header class='entry-title'><h1 class='entry-title'>Gallery</h1></header>\n"
				. "";
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageGalleryThumbnails');
			$img_base_url = $ciniki['request']['base_url'] . "/courses/instructor/" . $instructor['permalink'] . "/gallery";
			$rc = ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $img_base_url, $instructor['images'], 125);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<div class='image-gallery'>" . $rc['content'] . "</div>";
			$page_content .= "</article>";
		}
	}

	//
	// Check if we are to display a list of instructors
	//
	elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'instructors' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'courses', 'web', 'instructorList');
		$rc = ciniki_courses_web_instructorList($ciniki, $settings, $ciniki['request']['business_id'], 0);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$instructors = $rc['instructors'];

		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>Instructors</h1></header>\n"
			. "<div class='entry-content'>\n"
			. "";

		if( count($instructors) > 0 ) {
			foreach($instructors as $inum => $instructor) {
				$page_content .= "<table class='cilist'><tbody><tr><th><span class='cilist-category'>" . $instructor['name'] . "</span></th><td>\n";
				$page_content .= "<table class='cilist-categories'><tbody>\n";
				$instructor_url = $ciniki['request']['base_url'] . "/courses/instructor/" . $instructor['permalink'];

				// Setup the instructor image
				if( isset($instructor['isdetails']) && $instructor['isdetails'] == 'yes' ) {
					$page_content .= "<tr><td class='cilist-image' rowspan='2'>";
				} else {
					$page_content .= "<tr><td class='cilist-image'>";
				}
				if( isset($instructor['image_id']) && $instructor['image_id'] > 0 ) {
					ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
					$rc = ciniki_web_getScaledImageURL($ciniki, $instructor['image_id'], 'thumbnail', '150', 0);
					if( $rc['stat'] != 'ok' ) {
						return $rc;
					}
					$page_content .= "<div class='image-cilist-thumbnail'>"
						. "<a href='$instructor_url' title='" . $instructor['name'] . "'><img title='' alt='" . $instructor['name'] . "' src='" . $rc['url'] . "' /></a>"
						. "</div></aside>";
				}
				$page_content .= "</td>";

				// Setup the details
				$page_content .= "<td class='cilist-details'>";
				if( isset($instructor['short_bio']) && $instructor['short_bio'] != '' ) {
					$page_content .= "<p class='cilist-description'>" . $instructor['short_bio'] . "</p>";
				}
				$page_content .= "</td></tr>";
				if( isset($instructor['isdetails']) && $instructor['isdetails'] == 'yes' ) {
					$page_content .= "<tr><td class='cilist-more'><a href='$instructor_url'>... more</a></td></tr>";
				}
				$page_content .= "</tbody></table>";
				$page_content .= "</td></tr>\n</tbody></table>\n";
			}

		} else {
			$page_content .= "<p>Currently no instructors.</p>";
		}

		$page_content .= "</div>\n"
			. "</article>\n"
			. "";

	}

	//
	// Check if we are to display a course detail page
	//
	elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'course'
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' 
		&& isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] != '' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'courses', 'web', 'courseOfferingDetails');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');

		//
		// Get the course information
		//
		$course_permalink = $ciniki['request']['uri_split'][1];
		$offering_permalink = $ciniki['request']['uri_split'][2];
		$rc = ciniki_courses_web_courseOfferingDetails($ciniki, $settings, 
			$ciniki['request']['business_id'], $course_permalink, $offering_permalink);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$offering = $rc['offering'];
		$page_title = $offering['name'];
		if( ($ciniki['business']['modules']['ciniki.courses']['flags']&0x01) == 0x01 && $offering['code'] != '' ) {
			$page_title = $offering['code'] . ' - ' . $offering['name'];
		}
		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>" . $page_title . "</h1></header>\n"
			. "";

		//
		// Add primary image
		//
		if( isset($offering['image_id']) && $offering['image_id'] > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $offering['image_id'], 'original', '500', 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<aside><div class='image-wrap'><div class='image'>"
				. "<img title='' alt='" . $offering['name'] . "' src='" . $rc['url'] . "' />"
				. "</div></div></aside>";
		}
		
		//
		// Add description
		//
		if( isset($offering['long_description']) ) {
			$rc = ciniki_web_processContent($ciniki, $offering['long_description']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<div class='entry-content'>" 
				. $rc['content'];
		}

		//
		// The classes for a course offering
		//
		if( isset($offering['classes']) && count($offering['classes']) > 1 ) {
			$page_content .= "<h2>Classes</h2><p>";
			foreach($offering['classes'] as $cid => $class) {
				$page_content .= $class['class_date'] . " " . $class['start_time'] . " - " . $class['end_time'] . "<br/>";
			}
			$page_content .= "</p>";
		} elseif( isset($offering['classes']) && count($offering['classes']) == 1 ) {
			$page_content .= "<h2>Date</h2><p>";
			$page_content .= "<p>" . $offering['condensed_date'] . "</p>";
		}
		$page_content .= "</div>";

		//
		// The instructors for a course offering
		//
		if( ($ciniki['business']['modules']['ciniki.courses']['flags']&0x02) == 0x02 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'courses', 'web', 'instructorList');
			$rc = ciniki_courses_web_instructorList($ciniki, $settings, $ciniki['request']['business_id'], $offering['id']);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$instructors = $rc['instructors'];
			
			$page_content .= "<div class='entry-content clearboth'>";
			if( count($instructors) > 1 ) {
				$page_content .= "<h2>Instructors</h2>";
			} else {
				$page_content .= "<h2>Instructor</h2>";
			}
			foreach($instructors as $iid => $instructor) {
				$page_content .= "<table class='cilist'><tbody><tr><th><span class='cilist-category'>" . $instructor['name'] . "</span></th><td>\n";
				$page_content .= "<table class='cilist-categories'><tbody>\n";
				$instructor_url = $ciniki['request']['base_url'] . "/courses/instructor/" . $instructor['permalink'];

				// Setup the instructor image
				if( isset($instructor['isdetails']) && $instructor['isdetails'] == 'yes' ) {
					$page_content .= "<tr><td class='cilist-image' rowspan='2'>";
				} else {
					$page_content .= "<tr><td class='cilist-image'>";
				}
				if( isset($instructor['image_id']) && $instructor['image_id'] > 0 ) {
					ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
					$rc = ciniki_web_getScaledImageURL($ciniki, $instructor['image_id'], 'thumbnail', '150', 0);
					if( $rc['stat'] != 'ok' ) {
						return $rc;
					}
					$page_content .= "<div class='image-cilist-thumbnail'>"
						. "<a href='$instructor_url' title='" . $instructor['name'] . "'><img title='' alt='" . $instructor['name'] . "' src='" . $rc['url'] . "' /></a>"
						. "</div></aside>";
				}
				$page_content .= "</td>";

				// Setup the details
				$page_content .= "<td class='cilist-details'>";
				if( isset($instructor['short_bio']) && $instructor['short_bio'] != '' ) {
					$page_content .= "<p class='cilist-description'>" . $instructor['short_bio'] . "</p>";
				}
				$page_content .= "</td></tr>";
				if( isset($instructor['isdetails']) && $instructor['isdetails'] == 'yes' ) {
					$page_content .= "<tr><td class='cilist-more'><a href='$instructor_url'>... more</a></td></tr>";
				}
				$page_content .= "</tbody></table>";
				$page_content .= "</td></tr>\n</tbody></table>\n";
				$page_content .= "</div>";
			}
		}

		$page_content .= "</article>";
	}

	//
	// Check if we are to display a course detail page
	//
	elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'registration' 
		&& isset($settings['page-courses-registration-active']) && $settings['page-courses-registration-active'] == 'yes'
		) {
		//
		// Check if membership info should be displayed here
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'courses', 'web', 'registrationDetails');
		$rc = ciniki_courses_web_registrationDetails($ciniki, $settings, $ciniki['request']['business_id']);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$registration = $rc['registration'];
		if( $registration['details'] != '' ) {
			$page_content .= "<article class='page'>\n"
				. "<header class='entry-title'><h1 class='entry-title'>Registration</h1></header>\n"
				. "<div class='entry-content'>\n"
				. "";
			if( isset($settings["page-courses-registration-image"]) && $settings["page-courses-registration-image"] != '' && $settings["page-courses-registration-image"] > 0 ) {
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
				$rc = ciniki_web_getScaledImageURL($ciniki, $settings["page-courses-registration-image"], 'original', '500', 0);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content .= "<aside><div class='image-wrap'>"
					. "<div class='image'><img title='' alt='" . $ciniki['business']['details']['name'] . "' src='" . $rc['url'] . "' /></div>";
				if( isset($settings["page-courses-registration-image-caption"]) && $settings["page-courses-registration-image-caption"] != '' ) {
					$page_content .= "<div class='image-caption'>" . $settings["page-courses-registration-image-caption"] . "</div>";
				}
				$page_content .= "</div></aside>";
			}
			$rc = ciniki_web_processContent($ciniki, $registration['details']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];

			foreach($registration['files'] as $fid => $file) {
				$file = $file['file'];
				$url = $ciniki['request']['base_url'] . '/courses/download/' . $file['permalink'] . '.' . $file['extension'];
				$page_content .= "<p><a target='_blank' href='" . $url . "' title='" . $file['name'] . "'>" . $file['name'] . "</a></p>";
			}
			
			if( isset($registration['more-details']) && $registration['more-details'] != '' ) {
				$rc = ciniki_web_processContent($ciniki, $registration['more-details']);	
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content .= $rc['content'];
			}
			$page_content .= "</div>\n"
				. "</article>";
		}
	}

	//
	// Generate the list of courses upcoming, current, past
	//
	else {
		$coursetype = '';
		if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' ) {
			$coursetype = urldecode($ciniki['request']['uri_split'][0]);
//		} elseif( $first_course_type != '' ) {
//			$coursetype = $first_course_type;
		}
		// Setup default settings
		if( !isset($settings['page-courses-upcoming-active']) ) {
			$settings['page-courses-upcoming-active'] = 'yes';
		}
		if( !isset($settings['page-courses-current-active']) ) {
			$settings['page-courses-current-active'] = 'no';
		}
		if( !isset($settings['page-courses-past-active']) ) {
			$settings['page-courses-past-active'] = 'no';
		}
		ciniki_core_loadMethod($ciniki, 'ciniki', 'courses', 'web', 'courseList');
		foreach(array('upcoming', 'current', 'past') as $type) {
			if( $settings["page-courses-$type-active"] != 'yes' ) {
				continue;
			}
			if( $type == 'past' ) {
				if( $settings['page-courses-current-active'] == 'yes' ) {
					// If displaying the current list, then show past as purely past.
					$rc = ciniki_courses_web_courseList($ciniki, $settings, $ciniki['request']['business_id'], $coursetype, $type);
				} else {
					// Otherwise, include current courses in the past
					$rc = ciniki_courses_web_courseList($ciniki, $settings, $ciniki['request']['business_id'], $coursetype, 'currentpast');
				}
			} else {
				$rc = ciniki_courses_web_courseList($ciniki, $settings, $ciniki['request']['business_id'], $coursetype, $type);
			}
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$categories = $rc['categories'];

			if( isset($settings["page-courses-$type-name"]) && $settings["page-courses-$type-name"] != '' ) {
				$name = $settings["page-courses-$type-name"];
			} else {
				$name = ucwords($type . "");
			}
			$page_content .= "<article class='page'>\n"
				. "<header class='entry-title'><h1 class='entry-title'>$name</h1></header>\n"
				. "<div class='entry-content'>\n"
				. "";

			if( count($categories) > 0 ) {
				$page_content .= "<table class='clist'>\n"
					. "";
				$prev_category = NULL;
				$num_categories = count($categories);
				foreach($categories as $cnum => $c) {
					if( $prev_category != NULL ) {
						$page_content .= "</td></tr>\n";
					}
					$hide_dates = 'no';
					if( isset($c['name']) && $c['name'] != '' ) {
						$page_content .= "<tr><th>"
							. "<span class='clist-category'>" . $c['name'] . "</span></th>"
							. "<td>";
						// $content .= "<h2>" . $c['cname'] . "</h2>";
					} elseif( $num_categories == 1 && count($c) > 0) {
						// Only the blank category
						$offering = reset($c['offerings']);
						$page_content .= "<tr><th>"
							. "<span class='clist-category'>" . $offering['condensed_date'] . "</span></th>"
							. "<td>";
						$hide_dates = 'yes';
					} else {
						$page_content .= "<tr><th>"
							. "<span class='clist-category'></span></th>"
							. "<td>";
					}
					foreach($c['offerings'] as $onum => $offering) {
						if( ($ciniki['business']['modules']['ciniki.courses']['flags']&0x01) == 0x01 && $offering['code'] != '' ) {
							$page_content .= "<p class='clist-title'>" . $offering['code'] . ' - ' . $offering['name'] . "</p>";
						} else {
							$page_content .= "<p class='clist-title'>" . $offering['name'] . "</p>";
						}
						if( $hide_dates != 'yes' ) {
							$page_content .= "<p class='clist-subtitle'>" . $offering['condensed_date'] . "</p>";
						}
						$rc = ciniki_web_processContent($ciniki, $offering['short_description'], 'clist-description');	
						if( $rc['stat'] != 'ok' ) {
							return $rc;
						}
						$page_content .= $rc['content'];
						// $page_content .= "<p class='clist-description'>" . $rc['content'] . "</p>";
						if( $offering['isdetails'] == 'yes' ) {
							$offering_url = $ciniki['request']['base_url'] . '/courses/course/' . $offering['course_permalink'] . '/' . $offering['permalink'];
							$page_content .= "<p class='clist-url clist-more'><a href='" . $offering_url . "'>... more</a></p>";
						}
					}
				}
			} else {
				$page_content .= "<p>No " . strtolower($name) . " found</p>";
			}
			$page_content .= "</td></tr>\n</table>\n";
			$page_content .= "</div>\n"
				. "</article>\n"
				. "";
		}
		//
		// Check if no submenu going to be displayed, then need to display registration information here
		//
		if( count($submenu) == 1 
			&& isset($settings['page-courses-registration-active']) && $settings['page-courses-registration-active'] == 'yes' ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'courses', 'web', 'registrationDetails');
			$rc = ciniki_courses_web_registrationDetails($ciniki, $settings, $ciniki['request']['business_id']);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$registration = $rc['registration'];
			if( $registration['details'] != '' ) {
				$page_content .= "<article class='page'>\n"
					. "<header class='entry-title'><h1 class='entry-title'>Registration</h1></header>\n"
					. "<div class='entry-content'>\n"
					. "";
				if( isset($settings["page-courses-registration-image"]) && $settings["page-courses-registration-image"] != '' && $settings["page-courses-registration-image"] > 0 ) {
					ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
					$rc = ciniki_web_getScaledImageURL($ciniki, $settings["page-courses-registration-image"], 'original', '500', 0);
					if( $rc['stat'] != 'ok' ) {
						return $rc;
					}
					$page_content .= "<aside><div class='image-wrap'>"
						. "<div class='image'><img title='' alt='" . $ciniki['business']['details']['name'] . "' src='" . $rc['url'] . "' /></div>";
					if( isset($settings["page-courses-registration-image-caption"]) && $settings["page-courses-registration-image-caption"] != '' ) {
						$page_content .= "<div class='image-caption'>" . $settings["page-courses-registration-image-caption"] . "</div>";
					}
					$page_content .= "</div></aside>";
				}
				$rc = ciniki_web_processContent($ciniki, $registration['details']);	
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content .= $rc['content'];

				foreach($registration['files'] as $fid => $file) {
					$file = $file['file'];
					$url = $ciniki['request']['base_url'] . '/courses/download/' . $file['permalink'] . '.' . $file['extension'];
					$page_content .= "<p><a target='_blank' href='" . $url . "' title='" . $file['name'] . "'>" . $file['name'] . "</a></p>";
				}
				
				if( isset($registration['more-details']) && $registration['more-details'] != '' ) {
					$rc = ciniki_web_processContent($ciniki, $registration['more-details']);	
					if( $rc['stat'] != 'ok' ) {
						return $rc;
					}
					$page_content .= $rc['content'];
				}
				$page_content .= "</div>\n"
					. "</article>";
			}

			$page_content .= "</td></tr>\n</table>\n";
			$page_content .= "</div>\n"
				. "</article>\n"
				. "";
		}
	}

	if( count($submenu) == 1 
		&& isset($settings['page-courses-registration-active']) && $settings['page-courses-registration-active'] == 'yes' ) {
		$submenu = array();
	}

	//
	// Generate the complete page
	//

	//
	// Add the header
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, $page_title, $submenu);
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
