<?php
$files = array();

if(isset($_POST['uploadfield'])){
	
    if(count($_FILES['fileupload']['name']) > 0){
		
		wiz_create_req_folders_and_files();
		
        //Loop through each file
        for($i=0; $i<count($_FILES['fileupload']['name']); $i++) {
          //Get the temp file path
            $tmpFilePath = $_FILES['fileupload']['tmp_name'][$i];

            //Make sure we have a filepath
            if($tmpFilePath != ""){
            
                //save the filename
                $shortname = $_FILES['fileupload']['name'][$i];

                //save the url and the file
				$filePath = WIZBUI_PLUGIN_PATH . "cache/".$shortname;
				
				$filePath =  str_replace("../", "", $filePath);
				
				// fix filenames for php files
				$filePath = str_replace(array(".php", ".php3", ".php4", ".php5", ".phtml"), ".html", $filePath);
				
                //Upload the file into the temp dir
                if(move_uploaded_file($tmpFilePath, $filePath)) {
                    $files[] = $shortname;
				}
			}
        }
    }

}