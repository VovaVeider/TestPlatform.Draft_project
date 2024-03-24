<?php
$api_errors =[
    'LOGIN_EXISTS'=>'This login already exists',
    'LOGIN_INCORR' =>'Login has incorrect format.Login length must be >=2',
    'EMAIL_EXISTS' => 'This email already used by another login',
    'EMAIL_INCORR' =>'Email has incorrect format',
    'WEAK_PASSWD' => 'Password length must be >= 8',
    'AUTH_FAIL' => 'Password incorrect or user with this login not exist',
    'JSON_INV' => 'Not correct JSON format',
    'SERVER_ERR' => 'server error',
    'INVALID_PARAMS' =>'Not exists or not correct query string parametres',
    'JWT_SIGN_FAIL' => 'JWT Web Token sign is  failied',
    'USER_NOT_EXISTS' => 'User in jwt not exists.May be account was deleted',
    'NOT_RIGHTS' => 'Your role hasnt access',
    'TEST_NOT_FOUND' => 'Test not found',
    'NOT_AUTH'=>'Not authorized.In authorization header not jwt, but guest acsess forbidden',
    'TEST_INV' => 'Invalid test structure',
    'СAT_NOTFND'=>'Category not found',
    'INVALID_TYPE'=>'Ivalid type of key value in JSON',
    'TST_RES_INV'=>'Test result incorrect for selected test',
    'CAT_EXISTS'=> 'Category with this name already exists',
    'INVALID_IMAGE'=>'IMAGE MUST BE JPG  with max size 100 * 100'
];