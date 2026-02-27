<?php
echo "<pre>"; // Use <pre> tag to preserve formatting  in HTML output
echo file_get_contents("/etc/os-release");// Read and print the contents of the /etc/os-release file
echo "</pre>"; // Close the <pre> tag
?>
