<?php
// This file is only used if someone accesses the root directory.
// In production with Docker, Apache serves from /public/ directly,
// so this should never be reached. For safety, redirect to /public/index.php
header('Location: /public/index.php');
exit;
