<?php

return array
(
    'https' => substr($_SERVER['HTTP_HOST'], 0, 8) != '192.168.' && $_SERVER['HTTP_HOST'] != 'pi' && $_SERVER['HTTP_HOST'] != 'localhost',
    'preferred_protocol' => 'https',
    'redirect_to_preferred_protocol' => 1,
//  'minify'=> 0
);
