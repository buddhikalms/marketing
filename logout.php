<?php
// 1. Initialize the session
session_start();



// 4. Finally, destroy the session on the server
session_destroy();

// 5. Redirect to the login page or home page
header("location: ./");
exit;
