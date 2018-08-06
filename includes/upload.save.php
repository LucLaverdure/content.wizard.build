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

				$ext = substr($_FILES['fileupload']['name'][$i], strrpos($_FILES['fileupload']['name'][$i], '.'));

				if ($ext == ".zip") {
					// unzip if zip file
					
					$zip = new ZipArchive;
					$res = $zip->open($tmpFilePath);
					if ($res === TRUE) {
					  $zip->extractTo(WIZBUI_PLUGIN_PATH . 'cache/');
					  $zip->close();
					}

					// parse extracted files for php filenames
					$dirs_tmp = get_real_dirs();
					
					foreach ($dirs_tmp as $key => $dir) {
						$new_dir = $dir;
						$new_dir = str_replace(array(".php", ".php3", ".php4", ".php5", ".phtml"), ".html", $new_dir);
						
						if ($new_dir != $dir) {
							rename(WIZBUI_PLUGIN_PATH."cache/".$dir, WIZBUI_PLUGIN_PATH."cache/".$new_dir);
						}
					}
					
				} else {
			
					//save the filename
					$shortname = $_FILES['fileupload']['name'][$i];

					//save the url and the file
					$filePath = WIZBUI_PLUGIN_PATH . "cache/".$shortname;
					
					$filePath =  str_replace("../", "", $filePath);
					
					// fix filenames for php files
					$filePath = str_replace(array(".php", ".php3", ".php4", ".php5", ".phtml"), ".html", $filePath);
					
					// Upload the file into the cache dir
					if(move_uploaded_file($tmpFilePath, $filePath)) {
						$files[] = $shortname;
					}
					
				}
			}
        }
    }

}