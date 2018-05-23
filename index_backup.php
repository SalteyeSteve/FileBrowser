<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <script>
        function eraseText() {
            document.getElementById("textToSave").value = "";
        }
    </script>
    <!--region Stylesheet-->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Tangerine">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Vollkorn">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin+Sans">
    <link href="Generic.css" rel="stylesheet" type="text/css">
    <!--endregion-->
    <meta charset="UTF-8">
    <title>Browse Me</title>
</head>

<body>
<div class="container-fluid">

    <?php
    setlocale(LC_ALL, 'nld_nld');

    //region *SECURITY SECTION* check if there is an accessible upper level

        $root = getcwd();           // Root directory, upper level is restricted
        $bn = basename(getcwd());   // Name of the root directory

        // Checks if there is a lower level ( if there is a defined path )
            $path = null;
            if (isset($_GET['file'])) {
                $path = $_GET['file'];
                if (!is_in_dir($_GET['file'], $root)) {
                    $path = null;
        // If the path is defined, add a '/'
            } else {
                $path = '/' . $path;
            }
        }
    //endregion

    //region *ICON MANAGEMENT* checks file type, decides which icon to assign

        $type = (mime_content_type($root . $path)); // Mime type

        // Associate mime types with icon name
            function get_icon ($mimeType, $file, $root)
            {
                $icon_classes = array(
                    // Media
                    'image/png' => 'png',
                    'image/jpeg' => 'jpg',
                    'image/gif' => 'gif',
                    'audio/mp3' => 'mp3',
                    'video/mp4' => 'mp4',
                    // Documents
                    'text/css' => 'css',
                    'application/pdf' => 'pdf',
                    'application/msword' => 'doc',
                    'application/vnd.ms-word' => 'doc',
                    'application/vnd.oasis.opendocument.text' => 'doc',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml' => 'doc',
                    'application/vnd.ms-excel' => 'xls',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml' => 'xls',
                    'application/vnd.oasis.opendocument.spreadsheet' => 'xls',
                    'application/vnd.ms-powerpoint' => 'ppt',
                    'application/vnd.openxmlformats-officedocument.presentationml' => 'ppt',
                    'application/vnd.oasis.opendocument.presentation' => 'ppt',
                    'text/plain' => 'txt',
                    'text/html' => 'html',
                    'application/x-javascript' => 'js',
                    'application/html' => 'html',
                    // Archives
                    'application/gzip' => 'zip',
                    'application/zip' => 'zip',

                );
                foreach ($icon_classes as $text => $icon) {
                    if(contains(realpath($file))){
                        $icon = '<img class = "image" src="' . substr($file, strlen($root) + 1) . '">';
                        return $icon;
                    }
                    elseif (strpos($mimeType, $text) === 0) {
                        $icon = '<img class ="icons" src="icons/' . $icon . '.png" width ="100" height = "100">';
                        return $icon;
                    }
                    elseif (strpos(basename($file), '.css')) {
                        $icon = 'css';
                        $icon = '<img class ="icons" src="icons/' . $icon . '.png" width ="100" height = "100">';
                        return $icon;
                    } elseif (strpos(basename(realpath($file)), '.php')) {
                        $icon = 'php';
                        $icon = '<img class ="icons" src="icons/' . $icon . '.png" width ="100" height = "100">';
                        return $icon;
                    }
                }
                return '<img src="icons/unknown.png" width="100" height="100">';
            }

    //endregion

    //region *FILE HANDLING*


        $fl = substr($path, 1);             // File location
        $fn = basename($fl);                      // File name

        // <If file>
        if (is_file($root . $path)) {

            // <If image>
            $imagePath = substr($path, 1);      // Image location
            if (contains(realpath($imagePath))) {
                echo '<img class = "show" src = "' . $imagePath . '" >'; // Display image if mime type is 'images'
                return;
            }

            // <If text>
            elseif ($type == 'text/plain' || $type == 'application/msword' || $type == 'application/rtf') {

                $text = file_get_contents($fl);          // Text file to edit

            // If the file is read - only
                if (!is_writable($fl)){
                    ?>
                    <div id="text">
                        <div id="textEditorBracketLeft"></div>
                            <div id="textReader">
                                <?php echo get_file_info_reader($fl);?><br><br>
                                <div id="textReaderText" title ="<?= basename(substr($path, 1)) ?>"><?php readfile(substr($path, 1)); ?></div>
                            </div>
                        <div id="textEditorBracketRight"></div>
                    </div>
                    <?php
                return;
                } else {

                // *TEXT EDITOR*, edits and saves the chosen text file, then redirects to previous level
                    // If text was edited, save it
                        if (isset($_POST['textToSave'])) {
                            save_file($fl, $_POST['textToSave']);
                        }
        ?>
            <!-- Save edited text with a form -->
    <div id="text">
            <div id="textEditorBracketLeft"></div>
            <div id="textEditor">
                <?php echo get_file_info_reader($fl);?><br><br>
                <form action="<?php redirect($path) ?>" method="post">
                    <div id="leftHeaderDent"></div>
                    <div id="input">
                        <input type="submit" value="Save" title="Save file and exit" onclick="return confirm('Are you sure you want to save and exit this file?');">
                        <input type="reset" value="Undo" title="Undo all the changes made to the file">
                        <input type="button" value="Clear" onclick=" eraseText();" title="Delete all file content">
                    </div>
                    <textarea id="textToSave" name="textToSave" rows=" <?= get_linecount($fl) ?>" cols= '90' title="<?= basename($fl) ?>"><?php echo $text ?></textarea>
                </form>
            </div>
            <div id="textEditorBracketRight"></div>
    </div>
        <?php
                         return;
                }

            // <If none of the above>
            } else {
                    // try to open file
                ?>
    <div id="text">
                 <div id="textEditorBracketLeft"></div>
                    <div id="textReader"><?php echo get_file_info_reader($fl);?><br><br>
                        <div id="textReaderText" title ="<?= basename(substr($path, 1)) ?>"></div>
                    </div>
                 <div id="textEditorBracketRight"></div>
    </div>
                <?php
            return;
            }
        }
        //endregion

    // *URL AND CONTENT SECTION*

    $items = get_sorted_entries($root);

    ?> <div id="main"> <div id="bread"><?php

            // Home button
            echo '<div id="crumb"><a class="bread" href="/' . $bn . '" title="FileBrowser"><img class="bread" src="icons/home.png">Home</a></div>';

           if ($path) {
                $directory = realpath($root . $path); // Get  current opened directory path
                $items = get_sorted_entries($directory); // Get current directory contents
                $path = substr($directory, strlen($root) + 1) . '/'; // Current directory path without root

            // Bread crumb section

                // Return array of current directory path
                $crumbs = explode("\\", substr(dirname($directory), strlen($root) + 1));

                // Array to store breadcrumbs
                $breadcrumbs = array();

                // Array to store temporary array of breadcrumbs
                $keys = array_keys($breadcrumbs);

                // Reset the array every time a new path is opened
                unset($keys);
           }

            // If the folder level above is not root
            if (dirname('/?file=' . $path) !== '\\') {

                // Breadcrumb creation
                foreach($crumbs as $x=>$crumb) {
                    // Get breadcrumb title
                    $title = ucwords(str_replace(array('.php', '_'), array('', ' '), $crumb));

                    // If there is only one crumb
                    if ($x == 0){
                        $breadcrumbs[]= '<div id="crumb"><a class="bread" title="FileBrowser/'.$crumb.'" href="?file='.$crumb.'"><img class="bread" src="icons/closed_folder.png">'.$crumb.'</a></div>';
                    }

                    // If there is more than one crumb
                   elseif($x > 0) {
                       for ($i = 0; $i <= $x; $i++) {
                           $keys[] = $crumbs[$i]; // temporary array of the crumb element
                       }

                       // Add all of teh breadcrumbs to one array and then erase the temporary array
                       $breadcrumbs[] = '<div id="crumb"><a class= "bread" href="?file='.urlencode(implode('\\', $keys)).'" title="FileBrowser/'.implode('\\', $keys).'"><img class="bread" src="icons/closed_folder.png"> '.$title.'</a></div>';
                       unset($keys);
                   }
                }

                // Display breadcrumbs
                echo implode (' ', $breadcrumbs);
            }

            // Display current directory if the above level is not root
            if ($path)
            echo '<div id="crumb"><a class="bread" title = "FileBrowser/'.strtok($path, '/').'" href="?file='.urlencode(strtok($path, '/')).'"><img class="bread" src="icons/open_folder.png">'. basename($path) .'</a></div>';

        ?></div>

        <!--  Display all content in current directory as a link  -->
        <div id="row">
            <div class="col-lg-12">
            <?php display ($items, $path, $root);?>
            </div>
        </div>
    </div>
</div>
</body>

</html>

<?php
//region *FUNCTIONS*

// display current content
function display ($items, $path, $root)
{
    foreach ($items as $item) {

        // If folder
        if ($item->type == 'dir') {

            echo '<div id = "icon"><a href="?file=' . urlencode($path) . urlencode($item->entry) . '"><img src="icons/folder.png" width="100px" height="100px"><br>' . $item->entry . '</a></div>';
        } // If file
        elseif ($item->type == 'file') {
            echo '<div id = "icon" title="' . get_file_info(realpath($path . $item->entry)) . '"><a href="?file=' . urlencode($path) . urlencode($item->entry) . '">' . get_icon(mime_content_type($item->current), realpath($item->current), $root) . '<br>' . $item->entry . '</a></div>';
        }

    }
}

// Returns file info as value
function get_file_info ($file)
{
    if (file_exists($file) && !is_dir($file)) {

        $fileName = strtok(basename($file), '.');
        $fileType = strtok(mime_content_type($file), '/') . ' of type: ' . get_type(mime_content_type($file), $file);
        $fileSize = get_filesize(filesize($file));
        $fileDate = strftime("%d %B %Y %X", strtotime(date('H:i:s d F Y', filemtime($file))));

        if (is_writable($file)) {

            $readable = "No";
        } else {

            $readable = "Yes";
        }

        // <If file>
        // if (!contains($file)) {
        if (!contains($file)) {

            // Define file information parameters
            $fileInfo =
                'File name:  ' . $fileName . '
File type:  ' . $fileType . '
File size:  ' . $fileSize . '
Read-Only:  ' . $readable . '
Last modified on:  ' . $fileDate;
            return $fileInfo;
        }
        // <If image>
        elseif(contains($file)) {
            $fileInfo =
                'File name:  ' . $fileName . '
File type:  ' . $fileType . '
File size:  ' . $fileSize;
            return $fileInfo;
        }
    }
}

// Get file information for non - tooltip usage
function get_file_info_reader ($file){

    if (file_exists($file) && !is_dir($file)) {

        $fileName = strtok(basename($file), '.');
        $fileType = strtok(mime_content_type($file), '/') . ' of type: ' . get_type(mime_content_type($file), $file);
        $fileSize = get_filesize(filesize($file));

        if (is_writable($file)) {

            $readable = "No";
        } else {

            $readable = "Yes";
        }

        $fileTime = strftime("%d %B %Y %X", strtotime (date('H:i:s d F Y',filemtime($file))));

        // <If file>
        if (!contains($file)) {

            // Define file information parameters
            $fileInfo =
                '<table>
                            <tr><td>File name: </td><td>' . $fileName . '</td></tr>
                            <tr><td> File type:  </td><td>' . $fileType . '</td></tr>
                            <tr><td>File size:  </td><td>' . $fileSize . '</td></tr>
                            <tr><td>Read-Only:  </td><td>' . $readable . '</td></tr>
                            <tr><td>Last modified on:  </td><td>' . $fileTime.'</td></tr>
                        </table>';
            return $fileInfo;
        }

        // <If image>
        elseif (contains($file)) {
            $fileInfo =
                '<table>
                            <tr><td>File name:  </td><td>' . $fileName . '</td></tr>
                            <tr><td> File type:  </td><td>' . $fileType . '</td></tr>
                            <tr><td>File size:  </td><td>' . $fileSize . '</td></tr>
                        </table>';
            return $fileInfo;
        }
        // Return something if folder
        return null;
    }
    return null;
}

// Return array of files and directories
function get_sorted_entries($path){

    $dir_handle = @opendir($path) ;
    $items = array();

    while (false !== ($item = readdir($dir_handle))) {
        $dir = $path.'/'.$item;
        $prev = substr(dirname($dir), strlen(getcwd()));

        if ( $item == '.' || $item =='..' || $item =='.idea')
            continue;

        if(is_dir($dir)) {
            $ext = 'aadirectory';
            $items[] = (object) array('type'=>'dir','entry'=>$item, 'current'=>$dir, 'previous'=>$prev, 'extension'=>$ext);

        } else {
            $ext = get_type(mime_content_type($dir), $item);
            $items[] = (object) array('type'=>'file','entry'=>$item, 'current'=>$dir, 'previous'=>$prev, 'extension'=>$ext);
        }
    }
    closedir($dir_handle);
    usort($items,'sort_files');
    return $items;
}

// Sort file by type
function sort_files($a, $b){
    if ($a->extension != $b->extension)
        return strcmp($a->extension, $b->extension);

    return strcmp($a->extension,$b->extension);
}

// Get file type
function get_type ($mimeType, $file){
    $icon_classes = array(
        // Media
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'image/gif' => 'gif',
        'audio/mp3' => 'mp3',
        'video/mp4' => 'mp4',
        // Documents
        'text/css' => 'css',
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.ms-word' => 'doc',
        'application/vnd.oasis.opendocument.text' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml' => 'doc',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml' => 'xls',
        'application/vnd.oasis.opendocument.spreadsheet' => 'xls',
        'application/vnd.ms-powerpoint' => 'ppt',
        'application/vnd.openxmlformats-officedocument.presentationml' => 'ppt',
        'application/vnd.oasis.opendocument.presentation' => 'ppt',
        'text/plain' => 'txt',
        'text/html' => 'html',
        'application/x-javascript' => 'js',
        'application/html' => 'html',
        // Archives
        'application/gzip' => 'zip',
        'application/zip' => 'zip',

    );
    foreach ($icon_classes as $text => $icon) {
        if (strpos($mimeType, $text) === 0) {
            return $icon;
        } elseif (strpos(basename($file), '.css')) {
            $icon = 'css';
            return $icon;
        } elseif (strpos(basename(realpath($file)), '.php')) {
            $icon = 'php';
            return $icon;
        }
    }
    return 'unknown';
}

// Check if the string contains a certain string (used to check if the mime type is image)
function contains ($image){
    $arr = explode('/', mime_content_type($image));
    if (in_array("image", $arr)) {
        return true;
    }
    return false;
}

// Return array of folder contents
function get_contents ($directory, $root){
    if(dirname($directory) == $root) {
        $directory = $root;
    }
    $content = scandir($directory);
    $removeKeys = array('0', '1', '2');
    foreach ($removeKeys as $key) {
        unset($content[$key]);
    }
    return $content;
}

// Count text lines to match the text area, if
function get_linecount ($file)
{
    $linecountCurrent = 0;
    $linecount = 20;
    $handle = fopen($file, "r");
    while (!feof($handle)) {
        $line = fgets($handle);
        $linecountCurrent++;
    }
    fclose($handle);

    // If the text has more than 40 lines, extend
    if ($linecount < $linecountCurrent) {
        $linecount = $linecountCurrent + 1;
    }
    return $linecount;
}

// Redirect to previous page
function redirect ($path)
{
    $goBack = substr(dirname($path), 1);
    if (isset($_POST['textToSave'])) {
        if (dirname('/?file=' . $path) == '/?file=') {
            header('Location: http://localhost/FileBrowser');
        } else {
            header('Location: http://localhost/FileBrowser/?file=' . $goBack);
        }
    }
}

// Checks if the file is not in root directory
function is_in_dir($file, $directory){
    $directory = realpath($directory);
    $parent = realpath($file);
    while ($parent) {
        if ($directory == $parent) return true;
        if ($parent == dirname($parent)) break;
        $parent = dirname($parent);
    }
    return false;
}

// Save file
function save_file($txt, $newText)
{
    file_put_contents($txt, $newText);
}

// Get human sized file info
function get_filesize($bytes, $decimals = 2) {
    $ext = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    $output =  sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$ext[$factor];
    if ($bytes > 999){
        $output = $output.'B';
    }
    return $output;
}

//endregion
?>
